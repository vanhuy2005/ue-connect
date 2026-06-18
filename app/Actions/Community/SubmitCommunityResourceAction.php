<?php

namespace App\Actions\Community;

use App\Enums\CommunityResourceStatus;
use App\Enums\CommunityResourceType;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\PermissionGrant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitCommunityResourceAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Community $community, array $data): CommunityResource
    {
        // Owner is treated as an active participant even if no membership row exists yet.
        $isMember = $community->isOwnedBy($user)
            || CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

        if (! $isMember) {
            throw ValidationException::withMessages([
                'community' => 'Chỉ thành viên đang hoạt động mới có thể đăng tải tài nguyên.',
            ]);
        }

        if (! $community->isActive()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này không đang hoạt động.',
            ]);
        }

        $type = CommunityResourceType::from($data['resource_type']);

        // Validate that file_id or URL is provided based on type
        if ($type->requiresFile() && empty($data['file_id'])) {
            throw ValidationException::withMessages([
                'file_id' => 'Loại tài nguyên này yêu cầu tải lên tệp.',
            ]);
        }

        if ($type->requiresUrl() && empty($data['url'])) {
            throw ValidationException::withMessages([
                'url' => 'Loại tài nguyên này yêu cầu đường dẫn URL.',
            ]);
        }

        if (! ($data['copyright_attestation'] ?? false)) {
            throw ValidationException::withMessages([
                'copyright_attestation' => 'Bạn phải xác nhận quyền sở hữu/quyền chia sẻ tài nguyên này.',
            ]);
        }

        $status = CommunityResourceStatus::PendingReview->value;
        $hasReviewPrivilege = $user->hasRole('admin')
            || $user->can('manage_communities')
            || $community->isOwnedBy($user)
            || PermissionGrant::where('user_id', $user->id)
                ->whereIn('permission_key', ['manage_community_resources', 'manage_community', 'manage_communities'])
                ->where('scope_type', 'community')
                ->where('scope_id', $community->id)
                ->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->exists();

        if ($hasReviewPrivilege) {
            $status = CommunityResourceStatus::Published->value;
        }

        $resource = CommunityResource::create([
            'community_id' => $community->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'resource_type' => $data['resource_type'],
            'file_id' => $data['file_id'] ?? null,
            'url' => $data['url'] ?? null,
            'category' => $data['category'] ?? null,
            'copyright_attestation' => true,
            'status' => $status,
            'submitted_by' => $user->id,
        ]);

        if ($status === CommunityResourceStatus::Published->value) {
            $community->increment('resource_count');
        }

        return $resource;
    }
}
