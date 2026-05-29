<?php

namespace App\Enums;

enum EvidenceCaptureMethod: string
{
    case Camera = 'camera';
    case UploadFallback = 'upload_fallback';
    case AdminUpload = 'admin_upload';
}
