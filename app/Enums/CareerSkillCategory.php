<?php

namespace App\Enums;

enum CareerSkillCategory: string
{
    case TECHNICAL = 'technical';
    case PEDAGOGY = 'pedagogy';
    case LANGUAGE = 'language';
    case RESEARCH = 'research';
    case SOFT_SKILL = 'soft_skill';
    case DOMAIN_KNOWLEDGE = 'domain_knowledge';
    case TOOL = 'tool';
    case CAREER = 'career';
}
