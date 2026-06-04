<?php

namespace App\Enums;

enum CommunityJoinPolicy: string
{
    case Open = 'open';
    case ApprovalRequired = 'approval_required';
    case InviteOnly = 'invite_only';
    case AdminOnly = 'admin_only';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Mở (tham gia ngay)',
            self::ApprovalRequired => 'Yêu cầu xét duyệt',
            self::InviteOnly => 'Chỉ theo lời mời',
            self::AdminOnly => 'Chỉ admin thêm',
            self::Closed => 'Không nhận thành viên',
        };
    }

    public function requiresRequest(): bool
    {
        return $this === self::ApprovalRequired;
    }

    public function allowsDirectJoin(): bool
    {
        return $this === self::Open;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $case) => [$case->value => $case->label()]
        )->all();
    }
}
