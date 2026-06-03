<?php

$storageStrategy = env('MEDIA_STORAGE_STRATEGY', 'local_only');
$r2Enabled = env('MEDIA_R2_ENABLED', false);

return [
    'default_strategy' => $storageStrategy,

    'storage' => [
        'strategy' => $storageStrategy,
    ],

    'public_disk' => env('MEDIA_DISK', 'public'),
    'private_disk' => env('PRIVATE_MEDIA_DISK', 'private'),

    'r2' => [
        'enabled' => $r2Enabled,
    ],

    'providers' => [
        'local' => [
            'enabled' => true,
        ],

        'r2' => [
            'enabled' => $r2Enabled,
            'public_disk' => env('MEDIA_R2_PUBLIC_DISK', 'r2_public'),
            'private_disk' => env('MEDIA_R2_PRIVATE_DISK', 'r2_private'),
        ],

        'cloudinary' => [
            'enabled' => env('MEDIA_CLOUDINARY_ENABLED', false),
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
            'secure' => env('CLOUDINARY_SECURE', true),
            'upload_folder' => env('CLOUDINARY_UPLOAD_FOLDER', 'ueconnect'),
            'delivery_transformations' => env('CLOUDINARY_DELIVERY_TRANSFORMATIONS', true),
            'sync_public_variants' => env('CLOUDINARY_SYNC_PUBLIC_VARIANTS', true),
            'fail_open' => env('CLOUDINARY_FAIL_OPEN', true),
            'public_only' => true,
        ],

        'firebase' => [
            'enabled' => env('MEDIA_FIREBASE_ENABLED', false),
            'bucket' => env('FIREBASE_STORAGE_BUCKET'),
            'credentials' => env('FIREBASE_CREDENTIALS'),
            'optional' => true,
        ],
    ],

    'routing' => [
        'avatar' => env('MEDIA_ROUTE_AVATAR', 'public_with_optional_cloudinary'),
        'profile_cover' => env('MEDIA_ROUTE_PROFILE_COVER', 'public_with_optional_cloudinary'),
        'post_image' => env('MEDIA_ROUTE_POST_IMAGE', 'public_optimized'),
        'comment_image' => env('MEDIA_ROUTE_COMMENT_IMAGE', 'public_optimized'),
        'message_attachment' => env('MEDIA_ROUTE_MESSAGE_ATTACHMENT', 'private_protected'),
        'verification_evidence' => env('MEDIA_ROUTE_VERIFICATION_EVIDENCE', 'private_protected'),
        'report_evidence' => env('MEDIA_ROUTE_REPORT_EVIDENCE', 'private_protected'),
    ],

    'limits' => [
        'avatar_mb' => env('MEDIA_MAX_AVATAR_MB', 5),
        'cover_mb' => env('MEDIA_MAX_COVER_MB', 8),
        'post_image_mb' => env('MEDIA_MAX_POST_IMAGE_MB', 10),
        'message_image_mb' => env('MEDIA_MAX_MESSAGE_IMAGE_MB', 10),
        'verification_evidence_mb' => env('MEDIA_MAX_VERIFICATION_EVIDENCE_MB', 10),
        'post_max_images' => env('MEDIA_POST_MAX_IMAGES', 4),
        'verification_max_files' => env('MEDIA_VERIFICATION_MAX_FILES', 3),
    ],

    'quota' => [
        'user_daily_upload_count' => (int) env('MEDIA_USER_DAILY_UPLOAD_COUNT', 100),
        'user_daily_upload_mb' => (int) env('MEDIA_USER_DAILY_UPLOAD_MB', 100),
        'user_monthly_upload_mb' => (int) env('MEDIA_USER_MONTHLY_UPLOAD_MB', 1000),
        'global_daily_upload_mb' => (int) env('MEDIA_GLOBAL_DAILY_UPLOAD_MB', 5000),
        'cloudinary_daily_sync_limit' => (int) env('MEDIA_CLOUDINARY_DAILY_SYNC_LIMIT', 1000),
        'disable_cloudinary_when_limit_reached' => (bool) env('MEDIA_DISABLE_CLOUDINARY_WHEN_LIMIT_REACHED', true),
    ],

    'processing' => [
        'temp_ttl_minutes' => (int) env('MEDIA_TEMP_TTL_MINUTES', 60),
        'output_format' => env('MEDIA_IMAGE_OUTPUT_FORMAT', 'webp'),
        'quality' => (int) env('MEDIA_IMAGE_QUALITY', 82),
        'strip_exif' => (bool) env('MEDIA_STRIP_EXIF', true),
        'keep_original_public' => (bool) env('MEDIA_KEEP_ORIGINAL_PUBLIC', false),
        'keep_original_private' => (bool) env('MEDIA_KEEP_ORIGINAL_PRIVATE', true),
        'sync' => (bool) env('MEDIA_PROCESSING_SYNC', false),
    ],
];
