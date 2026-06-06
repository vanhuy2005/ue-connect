<?php

return [
    'enabled' => env('AI_VERIFICATION_ENABLED', false),

    'provider' => env('AI_VERIFICATION_PROVIDER', 'local_hybrid'),

    'student_card_only' => true,

    'camera_capture_required_for_ai' => false,

    'auto_approve' => false,

    'face_recognition_enabled' => false,

    'thresholds' => [
        'likely_match' => (float) env('AI_VERIFICATION_LIKELY_MATCH_THRESHOLD', 0.85),
        'manual_review' => (float) env('AI_VERIFICATION_MANUAL_REVIEW_THRESHOLD', 0.65),
        'suspicious' => (float) env('AI_VERIFICATION_SUSPICIOUS_THRESHOLD', 0.45),
    ],

    'capture' => [
        'session_ttl_minutes' => (int) env('AI_CAPTURE_SESSION_TTL_MINUTES', 10),
        'max_attempts' => (int) env('AI_CAPTURE_MAX_ATTEMPTS', 5),
        'min_width' => (int) env('AI_CAPTURE_MIN_WIDTH', 640),
        'min_height' => (int) env('AI_CAPTURE_MIN_HEIGHT', 360),
        'jpeg_quality' => (float) env('AI_CAPTURE_JPEG_QUALITY', 0.9),
    ],

    'local_hybrid' => [
        'ocr_engine' => env('AI_OCR_ENGINE', 'ocr_space'),
        'ocr_space_api_key' => env('OCR_SPACE_API_KEY'),
        'ocr_space_api_url' => env('OCR_SPACE_API_URL', 'https://api.ocr.space/parse/image'),
        'paddleocr_service_url' => env('AI_PADDLEOCR_SERVICE_URL'),
        'ollama_enabled' => env('AI_OLLAMA_ENABLED', true),
        'ollama_base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'ollama_model' => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
        'ollama_timeout_seconds' => (int) env('OLLAMA_TIMEOUT_SECONDS', 20),
        'tesseract_binary' => env('TESSERACT_BINARY', 'tesseract'),
        'tesseract_langs' => env('AI_TESSERACT_LANGS', 'vie+eng'),
        'tesseract_psm' => env('AI_TESSERACT_PSM', '6'),
    ],

    'fallback' => [
        'enabled' => env('AI_EXTERNAL_FALLBACK_ENABLED', false),
        'providers' => array_filter(array_map('trim', explode(',', env('AI_EXTERNAL_FALLBACK_PROVIDERS', '')))),
        'min_confidence_to_skip' => (float) env('AI_FALLBACK_SKIP_CONFIDENCE', 0.75),
    ],

    'privacy' => [
        'store_raw_ocr_text' => env('AI_STORE_RAW_OCR_TEXT', true),
        'allow_external_provider' => env('AI_ALLOW_EXTERNAL_PROVIDER', false),
        'redact_sensitive_fields_in_logs' => true,
    ],

    'providers' => [
        'mock' => [
            'model' => 'mock-student-card-analyzer-v1',
        ],

        'gemini_flash' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'timeout_seconds' => (int) env('GEMINI_TIMEOUT_SECONDS', 30),
        ],

        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY'),
            'model' => env('OPENROUTER_VISION_MODEL'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'timeout_seconds' => (int) env('OPENROUTER_TIMEOUT_SECONDS', 30),
        ],
    ],
];
