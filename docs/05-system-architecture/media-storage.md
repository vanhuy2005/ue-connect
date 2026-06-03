# Media Storage Architecture

Status: Draft

Summary: Source-of-truth storage rules for UEConnect media uploads and delivery.

## Strategies

`MEDIA_STORAGE_STRATEGY` supports:

- `local_only`: development and tests. Public media uses the configured public disk, private media uses the configured private disk.
- `r2_primary`: production source of truth. Public media uses `r2_public`; private media uses `r2_private`.
- `hybrid_public_cloudinary`: R2 remains source of truth. Cloudinary may provide public delivery URLs for public image variants only.

Legacy strategy names may be accepted by code for backwards compatibility, but new deployment configuration must use the three names above.

## Collection Routing

Public media collections:

- `avatar`
- `profile_cover`
- `post_image`
- `comment_image`

Private media collections:

- `message_attachment`
- `verification_evidence`
- `report_evidence`

Public media in R2 modes must persist with `primary_provider = r2` and `primary_disk = r2_public`. Private media in R2 modes must persist with `primary_provider = r2` and `primary_disk = r2_private`.

## Delivery Rules

Public media may render from the public R2 URL when `R2_PUBLIC_URL` is configured. If `R2_PUBLIC_URL` is empty, public media must render through the application media controller route.

Private media must never use public bucket URLs or Cloudinary. Access must go through signed/controller routes and authorization policies.

Cloudinary is optional public delivery only. It is not a source of truth and must not receive message attachments, verification evidence, report evidence, raw documents, or any media marked `visibility = private`.

Cloudinary sync is allowlisted to:

- `avatar`
- `profile_cover`
- `post_image`

Public upload flow:

1. User uploads media.
2. Server validates and creates optimized variants.
3. Canonical original/variants are stored in R2 public.
4. Eligible public variants are synced to Cloudinary.
5. UI prefers synced Cloudinary URLs.
6. If Cloudinary is unavailable, UI falls back to R2/controller URLs.

Private upload flow:

1. User uploads media.
2. Server validates and optimizes image preview variants when applicable.
3. Canonical original/variants are stored in R2 private.
4. UI receives signed `MediaController` URLs only.
5. Cloudinary is never called for private media.

Cloudinary public IDs must not contain original filenames, emails, student IDs, or personal data. UEConnect uses deterministic IDs in this shape:

```text
ueconnect/{app_env}/{collection}/{media_uuid}/{variant_name}
```

## Deployment Checks

Before enabling R2 in production-like environments:

1. Set `MEDIA_STORAGE_STRATEGY=r2_primary` or `hybrid_public_cloudinary`.
2. Set `MEDIA_R2_ENABLED=true`.
3. Set full `R2_ENDPOINT` directly; do not rely on `.env` interpolation.
4. Configure both `R2_PUBLIC_BUCKET` and `R2_PRIVATE_BUCKET`.
5. Configure `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, and `CLOUDINARY_API_SECRET` when using `hybrid_public_cloudinary`.
6. Run `php artisan config:clear`, `php artisan optimize:clear`, `php artisan media:debug-config`, and `php artisan media:health-check`.
7. Run `php artisan media:sync-cloudinary --failed-only` after credential fixes or transient Cloudinary outages.
8. Verify public uploads do not create new files under `public/storage/post_images` or `public/storage/avatars`.
9. Verify R2 public bucket receives avatar/post image variants.
10. Verify Cloudinary Media Library receives avatar/post image variants only.
11. Verify message attachments land in R2 private and do not appear in Cloudinary.

## Quotas

Media quota guards run before cloud writes. If an upload would exceed configured user or global limits, the upload fails closed with a validation error and does not write to R2 or local storage.

```env
MEDIA_USER_DAILY_UPLOAD_COUNT=100
MEDIA_USER_DAILY_UPLOAD_MB=100
MEDIA_USER_MONTHLY_UPLOAD_MB=1000
MEDIA_GLOBAL_DAILY_UPLOAD_MB=5000
MEDIA_CLOUDINARY_DAILY_SYNC_LIMIT=1000
MEDIA_DISABLE_CLOUDINARY_WHEN_LIMIT_REACHED=true
```

Cloudinary sync quota is enforced per synced variant. When the daily Cloudinary cap is reached and `MEDIA_DISABLE_CLOUDINARY_WHEN_LIMIT_REACHED=true`, public variants remain stored in R2 and Cloudinary sync is marked `skipped`, so delivery falls back to R2/controller URLs.

Operators can inspect current usage with:

```bash
php artisan media:quota-check
php artisan media:quota-check --user=1
```

Admin media usage is available at `/admin/media-usage` for users with the report management permission.
