<?php

namespace App\Enums;

enum CommunityResourceStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Rejected = 'rejected';
    case HiddenByModeration = 'hidden_by_moderation';
    case RemovedByModeration = 'removed_by_moderation';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::PendingReview => 'Chờ kiểm duyệt',
            self::Published => 'Đã đăng',
            self::Rejected => 'Bị từ chối',
            self::HiddenByModeration => 'Ẩn (kiểm duyệt)',
            self::RemovedByModeration => 'Đã xóa (kiểm duyệt)',
            self::Archived => 'Lưu trữ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Published => 'green',
            self::PendingReview => 'yellow',
            self::Rejected => 'red',
            self::HiddenByModeration => 'orange',
            self::RemovedByModeration => 'red',
            self::Draft => 'gray',
            self::Archived => 'slate',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Published;
    }
}
