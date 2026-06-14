<?php

namespace App\Enums;

enum CareerContributionType: string
{
    case SKILL = 'skill';
    case EXPERIENCE = 'experience';
    case PROJECT_IDEA = 'project_idea';
    case RESOURCE = 'resource';
    case DIFFICULTY_NOTE = 'difficulty_note';
    case PREREQUISITE_SUGGESTION = 'prerequisite_suggestion';
    case CAREER_RELEVANCE = 'career_relevance';
    case EXAM_NOTE = 'exam_note';
    case PORTFOLIO_ADVICE = 'portfolio_advice';
    case COURSE_UPDATE_PROPOSAL = 'course_update_proposal';
}
