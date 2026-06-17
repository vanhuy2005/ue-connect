# Environment Variables

Status: Source of truth

Summary: Biến môi trường cần thiết cho local development và Azure VM production.

---

## 1. Rules

- Local development uses `.env`.
- Azure VM production uses `/opt/ueconnect/.env.production`.
- `.env.production` is loaded into Docker via `--env-file .env.production`.
- Never commit real secrets.
- Keep `.env.example` as placeholder documentation only.

Laravel loads environment values through configuration files. After deployment changes, run or rely on container startup to run `php artisan optimize:clear` so stale cached config does not hide env changes.

---

## 2. Application

```env
APP_NAME=UEConnect
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://ueconnect.io.vn
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
```

Production must use a stable `APP_KEY`. Do not regenerate it on an existing production database unless you understand the encrypted-data impact.

---

## 3. Database

Production uses SQL Server through the PHP SQL Server drivers installed in the Docker image.

```env
DB_CONNECTION=sqlsrv
DB_HOST=
DB_PORT=1433
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false
```

For a self-hosted SQL Server with trusted local certificates, `DB_ENCRYPT=false` and `DB_TRUST_SERVER_CERTIFICATE=true` may be used only when that matches the database server policy.

---

## 4. Cache, Queue, and Session

Recommended production shape when Redis is available:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

Safe fallback when Redis is unavailable:

```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

The container already starts a queue worker through Supervisord. Do not use `QUEUE_CONNECTION=sync` for normal production because notifications, media, broadcasts, and AI jobs can block requests.

---

## 5. Reverb and Broadcasting

Production realtime requires Laravel Reverb:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=ueconnect.io.vn
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

The server process listens inside the container on `0.0.0.0:8080`; public clients connect through host HTTPS on `ueconnect.io.vn:443`.

---

## 6. Mail

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=no-reply@send.ueconnect.io.vn
MAIL_FROM_NAME="${APP_NAME}"
```

SMTP fallback:

```env
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
```

Use `MAIL_MAILER=log` only for local/dev verification.

---

## 7. Media Storage

UEConnect supports public media through Cloudinary and Cloudflare R2, while private media must stay protected.

```env
MEDIA_STORAGE_STRATEGY=hybrid_public_cloudinary
MEDIA_DISK=r2_public
PRIVATE_MEDIA_DISK=r2_private

MEDIA_R2_ENABLED=true
MEDIA_CLOUDINARY_ENABLED=true
MEDIA_FIREBASE_ENABLED=false
```

Cloudflare R2:

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

Cloudinary:

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

Do not serve private message attachments, verification evidence, or report evidence directly from a public CDN.

---

## 8. Microsoft SSO

```env
MICROSOFT_LOGIN_ENABLED=true
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_REDIRECT_URI="${APP_URL}/auth/microsoft/callback"
MICROSOFT_TENANT_ID=
MICROSOFT_ALLOWED_DOMAINS=student.hcmue.edu.vn,teacher.hcmue.edu.vn
```

If Microsoft login is disabled, keep `MICROSOFT_LOGIN_ENABLED=false`.

---

## 9. Web Push

The deploy workflow checks that VAPID keys are configured:

```env
VAPID_SUBJECT=https://ueconnect.io.vn/
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

Generate keys outside the repository and store only in `.env.production`.

---

## 10. AI and RAG

Enable only the providers needed in production.

```env
LLM_PROVIDER=gemini
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com
GEMINI_TIMEOUT_SECONDS=30

QDRANT_URL=
QDRANT_API_KEY=
QDRANT_COLLECTION=hcmue_knowledge
QDRANT_VECTOR_SIZE=1024

EMBEDDING_PROVIDER=bge_m3
BGE_EMBEDDING_URL=
BGE_EMBEDDING_TIMEOUT=120
```

For AI identity verification:

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock
AI_STORE_RAW_OCR_TEXT=false
```

Enable production OCR/AI only after privacy and resource impact are reviewed.

---

## 11. Local Defaults

Local `.env.example` intentionally boots safely with:

```env
DB_CONNECTION=sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
```

Switch local services one at a time when SQL Server, Redis, Reverb, or external providers are installed.
