<?php

namespace App\Enums;

enum DataQualitySeverity: string
{
    case P0_BLOCKER = 'p0';
    case P1_WARNING = 'p1';
    case P2_INFO = 'p2';
}
