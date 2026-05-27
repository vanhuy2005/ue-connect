<?php

namespace App\Support\Auth;

use Illuminate\Support\Str;

class AllowedEmailDomain
{
    /**
     * Check if the given email ends with one of the allowed domains.
     *
     * @param  array<string>  $allowedDomains
     */
    public static function check(string $email, array $allowedDomains): bool
    {
        $normalizedEmail = Str::lower(trim($email));

        foreach ($allowedDomains as $domain) {
            $domain = Str::lower(ltrim(trim($domain), '@'));

            if (Str::endsWith($normalizedEmail, '@'.$domain)) {
                return true;
            }
        }

        return false;
    }
}
