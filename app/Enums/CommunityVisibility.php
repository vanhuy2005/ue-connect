<?php

namespace App\Enums;

enum CommunityVisibility: string
{
    case Public = 'public';
    case Restricted = 'restricted';
    case Private = 'private';
    case Hidden = 'hidden';
    case OfficialOnly = 'official_only';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Công khai',
            self::Restricted => 'Hạn chế (yêu cầu tham gia)',
            self::Private => 'Riêng tư',
            self::Hidden => 'Ẩn',
            self::OfficialOnly => 'Chỉ quản trị viên',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $case) => [$case->value => $case->label()]
        )->all();
    }
}
