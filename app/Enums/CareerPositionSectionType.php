<?php

namespace App\Enums;

enum CareerPositionSectionType: string
{
    case RECOMMENDED_COURSES = 'recommended_courses';
    case REQUIRED_SKILLS = 'required_skills';
    case PROJECTS = 'projects';
    case RESOURCES = 'resources';
    case CERTIFICATES = 'certificates';
    case EXPERIENCE = 'experience';
    case ADVICE = 'advice';
    case ROADMAP_STEPS = 'roadmap_steps';
}
