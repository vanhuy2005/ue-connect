<?php

namespace App\Actions\Auth;

use App\Enums\IdentityType;
use App\Support\Auth\AllowedEmailDomain;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class ValidateMicrosoftIdentity
{
    /**
     * Validate the Microsoft SSO identity (tenant, email domain, etc.) and resolve user data.
     *
     * @return array{email: string, provider_user_id: string, tenant_id: string, intended_identity_type: ?IdentityType}
     *
     * @throws ValidationException
     */
    public function execute(SocialiteUser $socialiteUser): array
    {
        // 1. Safe id_token decode
        $idToken = $socialiteUser->accessTokenResponseBody['id_token'] ?? null;
        $idTokenPayload = $this->decodeJwtPayload($idToken);

        // 2. Resolve tenant ID
        $actualTenant = $socialiteUser->user['tid']
            ?? $socialiteUser->user['tenantId']
            ?? $idTokenPayload['tid']
            ?? null;

        // 3. Tenant validation
        $expectedTenant = config('services.microsoft.tenant');

        if (empty($expectedTenant) || in_array($expectedTenant, ['common', 'organizations'])) {
            Log::warning('Microsoft SSO tenant ID not configured or invalid', [
                'expected_tenant' => $expectedTenant,
                'received_tenant' => $actualTenant,
                'raw_profile_keys' => array_keys($socialiteUser->user),
                'has_id_token' => filled($idToken),
            ]);
            throw ValidationException::withMessages([
                'sso' => ['Không thể xác thực tổ chức Microsoft HCMUE. Vui lòng thử lại hoặc liên hệ quản trị viên.'],
            ]);
        }

        if (empty($actualTenant) || in_array($actualTenant, ['common', 'organizations']) || $actualTenant !== $expectedTenant) {
            Log::warning('Microsoft SSO tenant validation failed', [
                'expected_tenant' => $expectedTenant,
                'received_tenant' => $actualTenant,
                'raw_profile_keys' => array_keys($socialiteUser->user),
                'has_id_token' => filled($idToken),
            ]);
            throw ValidationException::withMessages([
                'sso' => ['Không thể xác thực tổ chức Microsoft HCMUE. Vui lòng thử lại hoặc liên hệ quản trị viên.'],
            ]);
        }

        // 4. Resolve email
        $email = $socialiteUser->getEmail()
            ?? $socialiteUser->user['email'] ?? null
            ?? $socialiteUser->user['preferred_username'] ?? null
            ?? $socialiteUser->user['upn'] ?? null
            ?? $socialiteUser->user['userPrincipalName'] ?? null
            ?? $idTokenPayload['email'] ?? null
            ?? $idTokenPayload['preferred_username'] ?? null
            ?? $idTokenPayload['upn'] ?? null;

        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy địa chỉ email trong tài khoản Microsoft của bạn.'],
            ]);
        }

        $normalizedEmail = Str::lower(trim($email));

        // 5. Domain validation
        $allowedDomains = config('services.microsoft.allowed_domains');
        if (empty($allowedDomains)) {
            $singleDomain = config('services.microsoft.allowed_domain');
            $allowedDomains = $singleDomain ? [$singleDomain] : ['hcmue.edu.vn', 'student.hcmue.edu.vn', 'teacher.hcmue.edu.vn'];
        }

        if (! AllowedEmailDomain::check($normalizedEmail, $allowedDomains)) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản Microsoft của bạn không thuộc tổ chức được phép.'],
            ]);
        }

        // 6. Resolve provider user ID
        $providerUserId = $socialiteUser->user['oid']
            ?? $idTokenPayload['oid']
            ?? $socialiteUser->getId()
            ?? $socialiteUser->user['id'] ?? null
            ?? $socialiteUser->user['sub'] ?? null
            ?? $idTokenPayload['sub'] ?? null;

        if (empty($providerUserId)) {
            throw ValidationException::withMessages([
                'sso' => ['Không tìm thấy mã định danh người dùng từ tài khoản Microsoft.'],
            ]);
        }

        // 7. Infer likely identity type
        $intendedIdentityType = null;
        if (AllowedEmailDomain::check($normalizedEmail, ['student.hcmue.edu.vn'])) {
            $intendedIdentityType = IdentityType::CURRENT_STUDENT;
        } elseif (AllowedEmailDomain::check($normalizedEmail, ['teacher.hcmue.edu.vn', 'hcmue.edu.vn'])) {
            $intendedIdentityType = IdentityType::TEACHER_ADVISOR;
        }

        return [
            'email' => $normalizedEmail,
            'provider_user_id' => $providerUserId,
            'tenant_id' => $actualTenant,
            'intended_identity_type' => $intendedIdentityType,
        ];
    }

    /**
     * Safely decode the payload part of a JWT.
     */
    private function decodeJwtPayload(?string $jwt): array
    {
        if (! $jwt || substr_count($jwt, '.') !== 2) {
            return [];
        }

        [, $payload] = explode('.', $jwt);

        $payload = strtr($payload, '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        return json_decode(base64_decode($payload), true) ?: [];
    }
}
