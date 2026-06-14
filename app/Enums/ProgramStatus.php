<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case READY = 'ready';
    case READY_WITH_MISSING_DESCRIPTIONS = 'ready_with_missing_descriptions';
    case PARTIAL_SEMESTER_EXTRACTION = 'partial_semester_extraction';
    case UNRESOLVED_SEMESTER_STRUCTURE = 'unresolved_semester_structure';
    case EMPTY_EXTRACTION = 'empty_extraction';
    case MISSING_CURRICULUM_PDF = 'missing_curriculum_pdf';
    case EXCLUDED_NON_PROGRAM_DOCUMENT = 'excluded_non_program_document';
}
