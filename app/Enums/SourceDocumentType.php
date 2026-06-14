<?php

namespace App\Enums;

enum SourceDocumentType: string
{
    case CHUANDAURA = 'chuandaura';
    case CHUONGTRINHKHUNG = 'chuongtrinhkhung';
    case QUYETDINH = 'quyetdinh';
    case HANDBOOK = 'handbook';
    case OTHER = 'other';
}
