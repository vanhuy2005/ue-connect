<?php

namespace App\Support\Media;

use App\Actions\Media\GenerateMediaUrlAction;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MediaUrlResolver
{
    /**
     * Resolve a public URL for a Media object, preferring Cloudinary if synced.
     */
    public static function publicUrl(?Media $media, ?string $variant = 'original'): ?string
    {
        if (! $media) {
            return null;
        }

        return app(GenerateMediaUrlAction::class)->execute($media, $variant, Auth::user());
    }

    /**
     * Resolve a thumbnail URL for a Media object.
     */
    public static function thumbnailUrl(?Media $media): ?string
    {
        return self::publicUrl($media, 'thumb');
    }

    /**
     * Resolve a temporary URL (signed URL) for a private Media object.
     */
    public static function temporaryUrl(?Media $media, int $ttlMinutes = 60, ?string $variant = null): ?string
    {
        if (! $media) {
            return null;
        }

        return app(GenerateMediaUrlAction::class)->execute($media, $variant, Auth::user());
    }

    /**
     * Resolve the avatar URL for a given User model.
     */
    public static function avatarUrl(?User $user, ?string $variant = 'thumb'): ?string
    {
        if (! $user || ! $user->profile) {
            return null;
        }

        $avatarMedia = $user->profile->relationLoaded('media')
            ? $user->profile->media->firstWhere('collection', 'avatar')
            : $user->profile->avatar()->with('variants')->first();

        if ($avatarMedia) {
            return self::publicUrl($avatarMedia, $variant);
        }

        return null;
    }

    /**
     * Resolve the cover URL for a given User model.
     */
    public static function coverUrl(?User $user, ?string $variant = 'desktop'): ?string
    {
        if (! $user || ! $user->profile) {
            return null;
        }

        $coverMedia = $user->profile->relationLoaded('media')
            ? $user->profile->media->firstWhere('collection', 'profile_cover')
            : $user->profile->cover()->with('variants')->first();

        if ($coverMedia) {
            return self::publicUrl($coverMedia, $variant);
        }

        return null;
    }
}
