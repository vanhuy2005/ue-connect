<?php

namespace App\Enums;

enum DataQualityIssueType: string
{
    case UNRESOLVED_SEMESTER_STRUCTURE = 'unresolved_semester_structure';
    case EMPTY_MARKDOWN = 'empty_markdown';
    case MISSING_CURRICULUM_PDF = 'missing_curriculum_pdf';
    case PARTIAL_SEMESTER_EXTRACTION = 'partial_semester_extraction';
    case MISSING_COURSE_DESCRIPTIONS = 'missing_course_descriptions';
    case INVALID_COURSE_ROW = 'invalid_course_row';
    case INVALID_SEMESTER_NUMBER = 'invalid_semester_number';
    case MISSING_PROGRAM_METADATA = 'missing_program_metadata';
    case DUPLICATE_COURSE = 'duplicate_course';
    case UNKNOWN = 'unknown';
}
