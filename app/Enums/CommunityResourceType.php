<?php

namespace App\Enums;

enum CommunityResourceType: string
{
    case Document = 'document';
    case Image = 'image';
    case Link = 'link';
    case Guide = 'guide';
    case Template = 'template';
    case VideoLink = 'video_link';
    case OfficialLink = 'official_link';
    case CareerResource = 'career_resource';
    case LearningNote = 'learning_note';

    public function label(): string
    {
        return match ($this) {
            self::Document => 'Tài liệu',
            self::Image => 'Hình ảnh',
            self::Link => 'Đường dẫn',
            self::Guide => 'Hướng dẫn',
            self::Template => 'Mẫu/Template',
            self::VideoLink => 'Video (đường dẫn)',
            self::OfficialLink => 'Đường dẫn chính thức',
            self::CareerResource => 'Tài nguyên nghề nghiệp',
            self::LearningNote => 'Ghi chú học tập',
        };
    }

    /** Whether this type requires a URL instead of a file upload. */
    public function requiresUrl(): bool
    {
        return true;
    }

    /** Whether this type requires a file upload. */
    public function requiresFile(): bool
    {
        return false;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $case) => [$case->value => $case->label()]
        )->all();
    }
}
