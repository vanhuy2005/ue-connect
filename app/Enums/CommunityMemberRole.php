<?php

namespace App\Enums;

enum CommunityMemberRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Moderator = 'moderator';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Chủ sở hữu',
            self::Manager => 'Quản lý cộng đồng',
            self::Moderator => 'Kiểm duyệt viên',
            self::Member => 'Thành viên',
        };
    }

    public function isCommunityStaff(): bool
    {
        return in_array($this, [self::Owner, self::Manager, self::Moderator]);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $case) => [$case->value => $case->label()]
        )->all();
    }
}
