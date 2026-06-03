# Environment Variables

Status: Draft

Summary: Environment variables and their purposes per environment.

## Media Storage

UEConnect defaults to local storage for development and tests. External providers are optional and must be disabled unless credentials are present.

```env
MEDIA_STORAGE_STRATEGY=hybrid_public_cloudinary
# Supported: local_only, r2_primary, hybrid_public_cloudinary
MEDIA_DISK=r2_public
PRIVATE_MEDIA_DISK=r2_private

MEDIA_R2_ENABLED=true
MEDIA_CLOUDINARY_ENABLED=true
MEDIA_FIREBASE_ENABLED=false

MEDIA_MAX_AVATAR_MB=5
MEDIA_MAX_COVER_MB=8
MEDIA_MAX_POST_IMAGE_MB=10
MEDIA_MAX_MESSAGE_IMAGE_MB=10
MEDIA_POST_MAX_IMAGES=4

MEDIA_TEMP_TTL_MINUTES=60
MEDIA_IMAGE_OUTPUT_FORMAT=webp
MEDIA_IMAGE_QUALITY=82
MEDIA_STRIP_EXIF=true
MEDIA_KEEP_ORIGINAL_PUBLIC=false
MEDIA_KEEP_ORIGINAL_PRIVATE=true
```

Cloudflare R2 is configured through `r2_public` and `r2_private` disks. Keep `MEDIA_R2_ENABLED=false` for local development unless all R2 credentials are configured.

```env
R2_ACCOUNT_ID=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_REGION=auto
R2_PUBLIC_BUCKET=ueconnect-public-media
R2_PRIVATE_BUCKET=ueconnect-private-media
R2_ENDPOINT=
R2_PUBLIC_URL=
R2_USE_PATH_STYLE_ENDPOINT=false
```

`R2_ENDPOINT` must be a complete endpoint value from Cloudflare R2. Do not rely on `${R2_ACCOUNT_ID}` interpolation inside `.env`.

Public media collections (`avatar`, `profile_cover`, `post_image`, `comment_image`) use `r2_public` in `r2_primary` and `hybrid_public_cloudinary` modes. Private collections (`message_attachment`, `verification_evidence`, `report_evidence`) use `r2_private`.

Cloudinary is public-delivery only. It may serve avatar, profile cover, and post image variants, but must not serve message attachments, verification evidence, or report evidence. R2 remains the source of truth when Cloudinary delivery is enabled.

```env
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_SECURE=true
CLOUDINARY_UPLOAD_FOLDER=ueconnect
CLOUDINARY_DELIVERY_TRANSFORMATIONS=true
CLOUDINARY_SYNC_PUBLIC_VARIANTS=true
CLOUDINARY_FAIL_OPEN=true
```

Firebase is reserved as optional/future-ready storage configuration and is disabled by default.

```env
FIREBASE_STORAGE_BUCKET=
FIREBASE_CREDENTIALS=
```

Backblaze B2 is intentionally not part of UEConnect media storage configuration.
