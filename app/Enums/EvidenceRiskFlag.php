<?php

namespace App\Enums;

enum EvidenceRiskFlag: string
{
    case NotCameraCapture = 'not_camera_capture';
    case CaptureSessionExpired = 'capture_session_expired';
    case LowResolution = 'low_resolution';
    case BlurredImage = 'blurred_image';
    case CroppedDocument = 'cropped_document';
    case DocumentNotDetected = 'document_not_detected';
    case DocumentTypeMismatch = 'document_type_mismatch';
    case PortraitMissing = 'portrait_missing';
    case StudentCodeMissing = 'student_code_missing';
    case StudentCodeMismatch = 'student_code_mismatch';
    case MissingName = 'missing_name';
    case NameMismatch = 'name_mismatch';
    case SchoolMismatch = 'school_mismatch';
    case OcrUnavailable = 'ocr_unavailable';
    case OllamaUnavailable = 'ollama_unavailable';
    case ExternalProviderUnavailable = 'external_provider_unavailable';
    case ExternalProviderDisabled = 'external_provider_disabled';
    case ManualReviewRequired = 'manual_review_required';
    case UnsupportedDocumentType = 'unsupported_document_type';
}
