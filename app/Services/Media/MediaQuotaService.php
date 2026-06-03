<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaQuotaService
{
    public function assertCanUpload(User $user, int $incomingBytes): void
    {
        $violations = $this->uploadViolations($user, $incomingBytes);

        if ($violations !== []) {
            throw ValidationException::withMessages([
                'media' => $violations,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    public function uploadViolations(User $user, int $incomingBytes): array
    {
        $usage = $this->usageForUser($user);
        $globalUsage = $this->globalUsage();
        $violations = [];

        if ($this->userDailyUploadCountLimit() < $usage['daily_upload_count'] + 1) {
            $violations[] = 'Bạn đã đạt giới hạn số lượt tải media trong ngày.';
        }

        if ($this->userDailyUploadBytesLimit() < $usage['daily_upload_bytes'] + $incomingBytes) {
            $violations[] = 'Bạn đã đạt giới hạn dung lượng media trong ngày.';
        }

        if ($this->userMonthlyUploadBytesLimit() < $usage['monthly_upload_bytes'] + $incomingBytes) {
            $violations[] = 'Bạn đã đạt giới hạn dung lượng media trong tháng.';
        }

        if ($this->globalDailyUploadBytesLimit() < $globalUsage['daily_upload_bytes'] + $incomingBytes) {
            $violations[] = 'Hệ thống đã đạt giới hạn dung lượng upload media trong ngày. Vui lòng thử lại sau.';
        }

        return $violations;
    }

    public function canSyncCloudinary(): bool
    {
        $limit = $this->cloudinaryDailySyncLimit();

        if ($limit <= 0) {
            return false;
        }

        return $this->cloudinarySyncedToday() < $limit;
    }

    public function cloudinaryLimitReason(): ?string
    {
        return $this->canSyncCloudinary() ? null : 'cloudinary_daily_sync_limit_reached';
    }

    /**
     * @return array<string, mixed>
     */
    public function usageForUser(User $user): array
    {
        $day = $this->todayRange();
        $month = $this->monthRange();

        return [
            'user_id' => $user->id,
            'daily_upload_count' => Media::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', $day)
                ->count(),
            'daily_upload_bytes' => $this->mediaBytesForUser($user, $day),
            'monthly_upload_bytes' => $this->mediaBytesForUser($user, $month),
            'storage_bytes' => $this->storageBytesForUser($user),
            'limits' => [
                'daily_upload_count' => $this->userDailyUploadCountLimit(),
                'daily_upload_bytes' => $this->userDailyUploadBytesLimit(),
                'monthly_upload_bytes' => $this->userMonthlyUploadBytesLimit(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function globalUsage(): array
    {
        $day = $this->todayRange();

        return [
            'daily_upload_count' => Media::query()->whereBetween('created_at', $day)->count(),
            'daily_upload_bytes' => (int) Media::query()->whereBetween('created_at', $day)->sum('size_bytes'),
            'storage_bytes' => (int) Media::query()->sum('size_bytes') + (int) MediaVariant::query()->sum('size_bytes'),
            'cloudinary_synced_today' => $this->cloudinarySyncedToday(),
            'limits' => [
                'global_daily_upload_bytes' => $this->globalDailyUploadBytesLimit(),
                'cloudinary_daily_sync_limit' => $this->cloudinaryDailySyncLimit(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function report(?User $user = null): array
    {
        return [
            'global' => $this->globalUsage(),
            'user' => $user ? $this->usageForUser($user) : null,
            'top_users_today' => $this->topUsersToday(),
        ];
    }

    public function disableCloudinaryWhenLimitReached(): bool
    {
        return (bool) config('media.quota.disable_cloudinary_when_limit_reached', true);
    }

    public function cloudinaryDailySyncLimit(): int
    {
        return (int) config('media.quota.cloudinary_daily_sync_limit', 1000);
    }

    protected function userDailyUploadCountLimit(): int
    {
        return (int) config('media.quota.user_daily_upload_count', 100);
    }

    protected function userDailyUploadBytesLimit(): int
    {
        return $this->mbToBytes((int) config('media.quota.user_daily_upload_mb', 100));
    }

    protected function userMonthlyUploadBytesLimit(): int
    {
        return $this->mbToBytes((int) config('media.quota.user_monthly_upload_mb', 1000));
    }

    protected function globalDailyUploadBytesLimit(): int
    {
        return $this->mbToBytes((int) config('media.quota.global_daily_upload_mb', 5000));
    }

    protected function cloudinarySyncedToday(): int
    {
        return MediaVariant::query()
            ->where('cloudinary_sync_status', 'synced')
            ->whereBetween('cloudinary_synced_at', $this->todayRange())
            ->count();
    }

    /**
     * @param  array{0: CarbonImmutable, 1: CarbonImmutable}  $range
     */
    protected function mediaBytesForUser(User $user, array $range): int
    {
        return (int) Media::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', $range)
            ->sum('size_bytes');
    }

    protected function storageBytesForUser(User $user): int
    {
        $mediaBytes = (int) Media::query()
            ->where('user_id', $user->id)
            ->sum('size_bytes');

        $variantBytes = (int) MediaVariant::query()
            ->whereHas('media', fn ($query) => $query->where('user_id', $user->id))
            ->sum('size_bytes');

        return $mediaBytes + $variantBytes;
    }

    /**
     * @return array<int, object>
     */
    protected function topUsersToday(): array
    {
        return DB::table('media')
            ->select('user_id', DB::raw('COUNT(*) as upload_count'), DB::raw('SUM(size_bytes) as upload_bytes'))
            ->whereBetween('created_at', $this->todayRange())
            ->groupBy('user_id')
            ->orderByDesc('upload_bytes')
            ->limit(10)
            ->get()
            ->all();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function todayRange(): array
    {
        $now = CarbonImmutable::now();

        return [$now->startOfDay(), $now->endOfDay()];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function monthRange(): array
    {
        $now = CarbonImmutable::now();

        return [$now->startOfMonth(), $now->endOfMonth()];
    }

    protected function mbToBytes(int $mb): int
    {
        return $mb * 1024 * 1024;
    }
}
