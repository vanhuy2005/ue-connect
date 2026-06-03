<?php

namespace App\Enums;

enum MediaStorageStrategy: string
{
    case LocalOnly = 'local_only';
    case PublicOptimized = 'public_optimized';
    case PrivateProtected = 'private_protected';
    case PublicWithOptionalCloudinary = 'public_with_optional_cloudinary';
    case R2Primary = 'r2_primary';
    case HybridPublicCloudinary = 'hybrid_public_cloudinary';
    case R2WithCloudinaryDelivery = 'r2_with_cloudinary_delivery';
    case CloudinaryPublicLocalPrivate = 'cloudinary_public_local_private';
}
