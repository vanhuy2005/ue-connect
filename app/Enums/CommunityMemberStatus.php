<?php

namespace App\Enums;

enum CommunityMemberStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
    case Left = 'left';
    case Removed = 'removed';
    case BannedFromCommunity = 'banned_from_community';
    case Muted = 'muted';
    case Restricted = 'restricted';
    case Invited = 'invited';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ duyệt',
            self::Active => 'Thành viên',
            self::Rejected => 'Đã từ chối',
            self::Left => 'Đã rời cộng đồng',
            self::Removed => 'Đã bị xóa',
            self::BannedFromCommunity => 'Bị cấm trong cộng đồng',
            self::Muted => 'Đang bị tắt tiếng',
            self::Restricted => 'Đang bị hạn chế',
            self::Invited => 'Đã được mời',
        };
    }

    public function canPost(): bool
    {
        return $this === self::Active;
    }

    public function canViewChat(): bool
    {
        return in_array($this, [self::Active, self::Muted]);
    }

    public function canSendChat(): bool
    {
        return $this === self::Active;
    }

    public function isParticipant(): bool
    {
        return $this === self::Active;
    }
}
