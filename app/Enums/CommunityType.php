<?php

namespace App\Enums;

enum CommunityType: string
{
    case Club = 'club';
    case AcademicGroup = 'academic_group';
    case FacultyGroup = 'faculty_group';
    case CourseGroup = 'course_group';
    case CareerGroup = 'career_group';
    case MentorGroup = 'mentor_group';
    case InterestGroup = 'interest_group';
    case OfficialAnnouncementGroup = 'official_announcement_group';
    case ProjectGroup = 'project_group';
    case SupportGroup = 'support_group';

    public function label(): string
    {
        return match ($this) {
            self::Club => 'Câu lạc bộ',
            self::AcademicGroup => 'Nhóm học thuật',
            self::FacultyGroup => 'Nhóm khoa/bộ môn',
            self::CourseGroup => 'Nhóm học phần',
            self::CareerGroup => 'Nhóm định hướng nghề',
            self::MentorGroup => 'Nhóm mentor/alumni',
            self::InterestGroup => 'Nhóm sở thích',
            self::OfficialAnnouncementGroup => 'Thông báo chính thức',
            self::ProjectGroup => 'Nhóm dự án',
            self::SupportGroup => 'Nhóm hỗ trợ sinh viên',
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
