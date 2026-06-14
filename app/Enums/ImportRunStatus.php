<?php

namespace App\Enums;

enum ImportRunStatus: string
{
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case ABORTED = 'aborted';
}
