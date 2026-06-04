<?php

namespace App\Enums;

enum CommunitySuggestionStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedMoreInformation = 'need_more_information';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ConvertedToCommunity = 'converted_to_community';
    case Duplicate = 'duplicate';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Đã gửi',
            self::UnderReview => 'Đang xem xét',
            self::NeedMoreInformation => 'Cần thêm thông tin',
            self::Approved => 'Đã chấp thuận',
            self::Rejected => 'Đã từ chối',
            self::ConvertedToCommunity => 'Đã tạo cộng đồng',
            self::Duplicate => 'Trùng lặp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'blue',
            self::UnderReview => 'yellow',
            self::NeedMoreInformation => 'orange',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::ConvertedToCommunity => 'teal',
            self::Duplicate => 'gray',
        };
    }
}
