<?php

namespace App\Enums;

enum MentorAccessStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case NeedMoreInfo = 'need_more_info';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Submitted => 'Đã gửi',
            self::UnderReview => 'Đang xem xét',
            self::Approved => 'Đã duyệt',
            self::Rejected => 'Từ chối',
            self::NeedMoreInfo => 'Cần thêm thông tin',
            self::Revoked => 'Đã thu hồi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Submitted => 'blue',
            self::UnderReview => 'amber',
            self::Approved => 'emerald',
            self::Rejected => 'red',
            self::NeedMoreInfo => 'orange',
            self::Revoked => 'red',
        };
    }

    /** @return array<self> */
    public static function activeStatuses(): array
    {
        return [self::Submitted, self::UnderReview, self::NeedMoreInfo];
    }
}
