<?php

namespace App\Enums;

enum CommunityStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Archived = 'archived';
    case HiddenByModeration = 'hidden_by_moderation';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Nháp',
            self::PendingReview->value => 'Chờ duyệt',
            self::Active->value => 'Hoạt động',
            self::Inactive->value => 'Không hoạt động',
            self::Suspended->value => 'Bị tạm khóa',
            self::Archived->value => 'Đã lưu trữ',
            self::HiddenByModeration->value => 'Ẩn do kiểm duyệt',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Suspended => 'red',
            self::Archived => 'slate',
            self::Draft => 'yellow',
            self::PendingReview => 'blue',
            self::HiddenByModeration => 'orange',
        };
    }

    public function isOperational(): bool
    {
        return $this === self::Active || $this === self::Inactive;
    }
}
