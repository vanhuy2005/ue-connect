<p align="center">
  <img src="public/images/brand/primary-logo-nobg.png" alt="UEConnect Logo" width="760">
</p>

<p align="center">
  <strong>Verified campus social platform for HCMUE students, mentors, alumni, advisors, clubs and communities.</strong><br>
</p>

<p align="center">
  <!-- Backend -->
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4">
  <img src="https://img.shields.io/badge/AlpineJS-3.x-77C1D2?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js 3">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="TailwindCSS 3">
  <img src="https://img.shields.io/badge/Vite-Build%20Tool-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
</p>

<p align="center">
  <!-- Data & Infra -->
  <img src="https://img.shields.io/badge/SQL%20Server-Database-CC2927?style=for-the-badge&logo=microsoftsqlserver&logoColor=white" alt="SQL Server">
  <img src="https://img.shields.io/badge/Redis-Cache%20%26%20Queue-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis">
  <img src="https://img.shields.io/badge/Reverb-Realtime%20WS-7C3AED?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Reverb">
  <img src="https://img.shields.io/badge/Nginx-Reverse%20Proxy-009639?style=for-the-badge&logo=nginx&logoColor=white" alt="Nginx">
  <img src="https://img.shields.io/badge/Docker-Container-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
</p>

<p align="center">
  <!-- Services & AI -->
  <img src="https://img.shields.io/badge/Cloudinary-Media%20CDN-3448C5?style=for-the-badge&logo=cloudinary&logoColor=white" alt="Cloudinary">
  <img src="https://img.shields.io/badge/Cloudflare%20R2-Object%20Storage-F38020?style=for-the-badge&logo=cloudflare&logoColor=white" alt="Cloudflare R2">
  <img src="https://img.shields.io/badge/Gemini-AI%20Provider-4285F4?style=for-the-badge&logo=googlegemini&logoColor=white" alt="Google Gemini">
  <img src="https://img.shields.io/badge/Ollama-Local%20LLM-000000?style=for-the-badge&logo=ollama&logoColor=white" alt="Ollama">
  <img src="https://img.shields.io/badge/Qdrant-Vector%20DB-DC244C?style=for-the-badge&logo=qdrant&logoColor=white" alt="Qdrant">
</p>

<p align="center">
  <!-- Auth & Tooling -->
  <img src="https://img.shields.io/badge/Microsoft%20SSO-Azure%20Entra%20ID-0078D4?style=for-the-badge&logo=microsoftazure&logoColor=white" alt="Microsoft SSO">
  <img src="https://img.shields.io/badge/Sanctum-API%20Auth-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Sanctum">
  <img src="https://img.shields.io/badge/Spatie-Permissions-EF4444?style=for-the-badge&logo=laravel&logoColor=white" alt="Spatie Permissions">
  <img src="https://img.shields.io/badge/PHPUnit-Testing-6E329D?style=for-the-badge&logo=phpunit&logoColor=white" alt="PHPUnit">
  <img src="https://img.shields.io/badge/Pint-Code%20Style-111827?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Pint">
</p>

---

## 1. Project Overview

**UEConnect** là một nền tảng mạng xã hội nội bộ dành riêng cho cộng đồng **HCMUE**. Sản phẩm được thiết kế như một **verified campus social platform**, nơi người dùng cần được xác thực danh tính trước khi tham gia các hoạt động chính trong hệ thống.

UEConnect tập trung vào các nhu cầu cốt lõi của sinh viên trong trường:

- Đăng bài, bình luận và tương tác trong môi trường sinh viên có kiểm soát.
- Tìm kiếm và khám phá sinh viên cùng khoa, cùng khóa, cùng ngành, cùng định hướng học tập.
- Gửi lời chào, tạo kết nối và nhắn tin 1:1 theo trạng thái quan hệ hợp lệ.
- Tham gia cộng đồng, câu lạc bộ và hoạt động sinh viên.
- Kết nối mentor, alumni hoặc advisor để được định hướng học tập và nghề nghiệp.
- Báo cáo, chặn, kiểm duyệt và audit để giữ môi trường an toàn.
- Quản trị xác thực, người dùng, nội dung, vai trò, quyền hạn và vận hành hệ thống.
- Chatbot AI được huấn luyện trên dữ liệu học thuật HCMUE (chương trình đào tạo, chuẩn đầu ra của các khoa, ngành qua các khóa, và sổ tay sinh viên).

UEConnect **không phải dating app**, **không phải Facebook clone**, và **không phải super app sinh viên**. Phạm vi hiện tại tập trung vào một social layer đáng tin cậy cho đời sống sinh viên HCMUE.

---

## 2. Current Implementation Status

| Area | Status |
|---|---|
| Laravel foundation | ✅ Initialized |
| Breeze / Livewire auth starter | ✅ Installed |
| Reverb realtime | ✅ Installed |
| Laravel Boost (AI dev) | ✅ Installed |
| Spatie Laravel Permission | ✅ Installed |
| Sanctum | ✅ Installed |
| Microsoft SSO (Azure Entra ID) | ✅ Configured |
| Docker + Nginx reverse proxy | ✅ Configured |
| Supervisord process management | ✅ Configured |
| HCMUE Chatbot (RAG + LLM) | ✅ Implemented |
| AI Identity Verification (OCR) | ✅ Implemented |
| Cloudinary + R2 media pipeline | ✅ Configured |
| Dev scripts | ✅ `dev` and `dev:laragon` configured |
| Product / Design / Architecture / DB / API docs | ✅ Available |
| Production feature implementation | 🔄 In progress |

> Local environment must still be verified on each developer machine because PHP PATH, SQL Server drivers, ODBC, `.env`, queue and Reverb are local-machine dependent.

---

## 3. Architecture Direction

UEConnect follows a **Modular Monolith** architecture:

```
                    ┌────────────────────────────────┐
                    │       Nginx (Port 10000)        │
                    │  /app/* → Reverb WS (8080)      │
                    │  /*     → PHP-FPM               │
                    └───────────────┬────────────────┘
                                    │
         ┌──────────────────────────┼─────────────────────────┐
         │                          │                         │
┌────────┴────────┐       ┌─────────┴────────┐    ┌──────────┴──────────┐
│  Laravel App    │       │  Laravel Reverb   │    │   Queue Worker      │
│  (PHP-FPM)      │       │  (WebSocket)      │    │   (Supervisord)     │
│                 │       │                   │    │                     │
│  Blade/Livewire │       │  Private Channels │    │  Notifications      │
│  Admin Panel    │       │  Presence Channels│    │  Media Jobs         │
│  Chatbot API    │       │                   │    │  Email / Push       │
└────────┬────────┘       └───────────────────┘    └─────────────────────┘
         │
┌────────┴──────────────────────────────────────────────────┐
│            SQL Server (Production) / SQLite (Dev)         │
│  users · profiles · messages · posts · communities        │
│  notifications · audit_logs · chatbot · knowledge_base    │
└───────────────────────────────────────────────────────────┘
         │
┌────────┴──────────────────┐    ┌────────────────────────────┐
│   Redis (Cache & Queue)   │    │  Cloudinary + R2 (Media)   │
│   CACHE_STORE=redis       │    │  Avatar · Post · Evidence  │
│   QUEUE_CONNECTION=redis  │    │  CDN delivery + variants   │
└───────────────────────────┘    └────────────────────────────┘
```

**Key decisions:**
- **No microservices** — one deployable unit, lower operational complexity for campus-scale MVP.
- **Reverb behind Nginx** — Nginx proxies `/app` to Reverb on port 8080, everything else goes to PHP-FPM. One service, one port on Render.
- **Redis for cache & queue** (production), **database** driver on free-tier hosting (Render free plan).
- **Database is source of truth** — realtime is delivery only; clients always recover via HTTP refresh.

See [`docs/05-system-architecture/`](docs/05-system-architecture/architecture-overview.md) for full C4 diagrams and ADRs.

---

## 4. Tech Stack

### 4.1 Runtime & Backend

| Layer | Technology |
|---|---|
| Language | PHP `^8.3` |
| Framework | Laravel `^13` |
| Auth starter | Laravel Breeze + Livewire customization |
| API token foundation | Laravel Sanctum |
| ORM | Laravel Eloquent |
| Authorization | Laravel Policies / Gates |
| Global roles & permissions | Spatie Laravel Permission |
| Scoped permissions | Custom `permission_grants` table |
| Queue | Laravel Queue (`database` / `redis`) |
| Scheduler | Laravel Scheduler |
| Code style | Laravel Pint |
| AI-assisted dev | Laravel Boost |
| Testing | PHPUnit `^12` |

### 4.2 Frontend

| Layer | Technology |
|---|---|
| Rendering | Blade |
| Reactive UI | Livewire `^4` + Volt (single-file components) |
| Interaction | Alpine.js `^3` |
| Styling | TailwindCSS `^3` |
| Build tool | Vite |
| Realtime client | Laravel Echo + Pusher-JS |
| Icon system | Lucide Icons (custom Blade component) |
| PWA | Vite PWA Plugin |

### 4.3 Data, Realtime & Infrastructure

| Layer | Technology |
|---|---|
| Database | SQL Server (production) / SQLite (local dev) |
| Cache | Redis / File (local) |
| Queue driver | Redis (production) / Database (free-tier) |
| Realtime | Laravel Reverb `^1` + Laravel Echo `^2` |
| Reverse proxy | Nginx |
| Container | Docker |
| Process management | Supervisord |
| Deployment | Render (auto-deploy from `main`) |

### 4.4 Media & Storage

| Layer | Technology |
|---|---|
| Public media CDN | Cloudinary (images/video delivery + transforms) |
| Object storage | Cloudflare R2 (S3-compatible, public + private buckets) |
| Private file access | Protected Laravel routes + signed URLs |
| Avatar/Post/Evidence | Hybrid: R2 origin → Cloudinary CDN delivery |

### 4.5 AI & Machine Learning

| Layer | Technology |
|---|---|
| LLM Provider | Google Gemini (`gemini-2.0-flash`) |
| Local LLM | Ollama (`gemma4:e2b`, `qwen2.5:1.5b`) |
| LLM fallback | OpenRouter |
| Embedding | BGE-M3 (via HuggingFace Space endpoint) / Gemini Embedding |
| Vector database | Qdrant (RAG knowledge retrieval) |
| OCR engine | Tesseract + PaddleOCR |
| Identity verification | AI OCR → score → admin review workflow |
| Chatbot | HCMUE RAG Chatbot (curriculum, faculty, program data) |

### 4.6 Auth & External Services

| Layer | Technology |
|---|---|
| SSO | Microsoft Azure Entra ID (Outlook HCMUE) |
| Social auth | Laravel Socialite `^5` |
| Email | Resend / SMTP (Office365) |
| Push notifications | Browser Push API |

---

## 5. Core Product Modules

| Domain | Purpose |
|---|---|
| Authentication | Register, login, logout, password reset, Microsoft SSO, account gate |
| Identity Verification | AI-assisted + admin review of student/alumni/advisor identity |
| Profile Management | Public identity, profile editing, metadata, privacy controls |
| Onboarding | First-time guided setup and activation checklist |
| Settings & Privacy | Visibility, blocked users, notification preferences |
| Media Upload | Avatar, post media, message attachments, verification evidence |
| Home Feed | Main social feed for verified users |
| Posts & Comments | Post detail, comments, visibility rules, moderation hooks |
| Safety Reporting | Report, block, keyword flagging and safety workflow |
| Moderation | Moderation queue, content/user actions, evidence review |
| Admin Operations | Verification queue, user management, roles, audit, policy controls |
| Discovery Profile | Search and discover UEers safely with faculty/interest filters |
| Greeting & Connection | Send greeting, accept/decline, manage connection state |
| Messaging | Real-time 1:1 conversation with pin, reply, forward, recall |
| Notification | In-app and browser push notifications with preference control |
| Mentor Matching | Mentor profiles, request system, feedback loop |
| Career Pathway | HCMUE-aware career exploration and opportunity discovery |
| Community & Club | Admin-approved clubs, posts, events, resources, join requests |
| Search & Filter | Cross-module search and filter behavior |
| Analytics Events | Product metrics and usage event tracking |
| HCMUE Chatbot | AI RAG chatbot trained on faculty/program/curriculum data |

---

## 6. HCMUE AI Chatbot System

UEConnect tích hợp một **HCMUE Academic Chatbot** được xây dựng theo kiến trúc **Retrieval-Augmented Generation (RAG)** — cho phép sinh viên đặt câu hỏi bằng tiếng Việt về chương trình đào tạo, học phần, quy chế học vụ và sổ tay sinh viên của HCMUE và nhận câu trả lời có trích dẫn nguồn.

**Tổng quan hệ thống:**

| Thành phần | Công nghệ |
|---|---|
| LLM chính | Google Gemini `gemini-2.0-flash` |
| LLM local | Ollama `gemma4:e2b` (8GB RAM) |
| LLM fallback | OpenRouter |
| Embedding model | BGE-M3 (1024-dim, multilingual) |
| Vector database | Qdrant (`hcmue_knowledge` collection) |
| Structured retrieval | SQL Server (CTĐT, courses, cohorts, faculties) |
| Pipeline | 10-step: Normalize → Route → Retrieve → Compose → Verify → Guard |
| Multi-turn memory | Session context via `ConversationContextService` |
| Hallucination guard | `CitationVerifierService` + `HallucinationGuardService` |
| Ingestion pipeline | PDF → Chunking → BGE-M3 → Qdrant + SQL Server |

Hệ thống hỗ trợ 3 route chính: **`structured_db`** (query SQL Server về CTĐT cụ thể), **`rag`** (tìm kiếm vector trong tài liệu PDF) và **`hybrid`** (kết hợp cả hai), với cơ chế tự động fallback và multi-API-key rotation.

📄 **Tài liệu kỹ thuật chi tiết:** [`docs/12-agent/6. hcmue-chatbot-system.md`](docs/12-agent/6.%20hcmue-chatbot-system.md)

---

## 7. Environment Variables

Key variables required for local and production setup. Full reference: [`docs/10-devops/environment-variables.md`](docs/10-devops/environment-variables.md).

### Application

```env
APP_NAME=UEConnect
APP_ENV=local                    # production on server
APP_KEY=                         # generate via php artisan key:generate
APP_DEBUG=false                  # true only for debugging
APP_URL=https://your-domain.com
APP_LOCALE=vi
```

### Database

```env
# SQLite (local dev / safe mode)
DB_CONNECTION=sqlite

# SQL Server (production / full setup)
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=ue_connect
DB_USERNAME=ue_connect_user
DB_PASSWORD=YourStrongPassword123
DB_ENCRYPT=false
DB_TRUST_SERVER_CERTIFICATE=true
```

### Cache, Queue & Session

```env
# Free-tier hosting (Render) — use database driver
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Production with Redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database          # keep database; free Redis may not persist

# Redis connection
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

> **⚠️ Render Free Tier:** External Redis connections may be blocked. Use `database` driver for `CACHE_STORE` and `QUEUE_CONNECTION` on free-tier hosting.

### Laravel Reverb (WebSocket)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=ueconnect-local
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=your-domain.com     # 127.0.0.1 for local
REVERB_PORT=443                 # 8080 for local
REVERB_SCHEME=https             # http for local

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
```

### Microsoft SSO (Outlook HCMUE)

```env
MICROSOFT_LOGIN_ENABLED=true
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_REDIRECT_URI="${APP_URL}/auth/microsoft/callback"
MICROSOFT_TENANT_ID=
MICROSOFT_ALLOWED_DOMAINS=student.hcmue.edu.vn,teacher.hcmue.edu.vn
```

### Media Storage (Cloudinary + Cloudflare R2)

```env
MEDIA_STORAGE_STRATEGY=hybrid_public_cloudinary
MEDIA_DISK=r2_public
PRIVATE_MEDIA_DISK=r2_private
MEDIA_R2_ENABLED=true
MEDIA_CLOUDINARY_ENABLED=true

# Cloudflare R2
R2_ACCOUNT_ID=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_PUBLIC_BUCKET=ueconnect-public-media
R2_PRIVATE_BUCKET=ueconnect-private-media
R2_ENDPOINT=
R2_PUBLIC_URL=

# Cloudinary
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_UPLOAD_FOLDER=ueconnect
```

### AI Identity Verification

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock   # local_hybrid for production

AI_OCR_ENGINE=tesseract
TESSERACT_BINARY=tesseract
AI_TESSERACT_LANGS=vie+eng

AI_OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:1.5b
OLLAMA_TIMEOUT_SECONDS=20

AI_VERIFICATION_LIKELY_MATCH_THRESHOLD=0.85
AI_VERIFICATION_MANUAL_REVIEW_THRESHOLD=0.65
AI_VERIFICATION_SUSPICIOUS_THRESHOLD=0.45
```

### HCMUE Chatbot (RAG + LLM)

```env
# LLM Provider: gemini | openrouter | ollama
LLM_PROVIDER=gemini

GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com
GEMINI_TIMEOUT_SECONDS=30

OPENROUTER_API_KEY=
OPENROUTER_VISION_MODEL=
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1

# Qdrant vector database
QDRANT_URL=http://localhost:6333
QDRANT_API_KEY=
QDRANT_COLLECTION=hcmue_knowledge
QDRANT_VECTOR_SIZE=1024

# Embedding: gemini | bge_m3
EMBEDDING_PROVIDER=bge_m3
BGE_EMBEDDING_URL=https://ntkhoi2005-hcmue-bge-m3-embedding.hf.space
BGE_EMBEDDING_TIMEOUT=120

# Local Ollama for chatbot (separate from AI verification)
OLLAMA_CHAT_MODEL=gemma4:e2b
OLLAMA_TEMPERATURE=0.2
OLLAMA_NUM_CTX=4096
OLLAMA_FALLBACK_ENABLED=true
OLLAMA_FALLBACK_PROVIDER=gemini

# Retrieval settings
AI_RETRIEVAL_TOP_K=8
AI_RERANK_TOP_K=5
AI_MIN_RETRIEVAL_SCORE=0.55
```

### Email

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=no-reply@send.ueconnect.io.vn
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 7. Local Development Setup

Read the full setup guide first: [`UEConnect-Local-Setup-From-Scratch.md`](UEConnect-Local-Setup-From-Scratch.md)

### Requirements

```txt
PHP 8.3+
Composer 2.x+
Node.js 22+ / npm 10+
Laragon (recommended on Windows)
SQL Server Express / Developer
Microsoft ODBC Driver for SQL Server
Microsoft Drivers for PHP (php_sqlsrv + php_pdo_sqlsrv)
```

### Quick Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure environment (safe SQLite mode first)
copy .env.example .env
php artisan key:generate

# 3. Create SQLite database for local
type nul > database\database.sqlite

# 4. Run migrations and seeds
php artisan migrate
php artisan db:seed --class=AcademicStructureSeeder
php artisan db:seed

# 5. Start all services (Laravel + Vite + Queue + Reverb)
composer run dev
```

Open `http://127.0.0.1:8000` or `http://ue-connect.test` (Laragon).

---

## 8. Useful Commands

```bash
# Application
php artisan route:list
php artisan migrate:status
php artisan migrate
php artisan optimize:clear
php artisan storage:link

# Seed academic structure from database/AI folders
php artisan db:seed --class=AcademicStructureSeeder

# Frontend
npm run dev
npm run build

# Queue / Realtime
php artisan queue:work
php artisan reverb:start

# Code style (run before every commit)
vendor/bin/pint --dirty

# Tests
php artisan test --compact

# Chatbot
php artisan hcmue:ollama:test
```

---

## 9. Deployment

UEConnect is deployed via **Docker on Render**. On each push to `main`, Render automatically builds the Docker image and deploys.

Container startup sequence (`docker/start.sh`):
1. Substitute `$PORT` into the Nginx config template
2. `php artisan migrate --force`
3. `php artisan optimize:clear`
4. Fix storage permissions
5. Supervisord → manages Nginx, PHP-FPM, Queue Worker, Reverb

See [`docs/10-devops/deployment.md`](docs/10-devops/deployment.md) for step-by-step instructions.

---

## 10. Documentation Map

The `docs/` folder is the project brain. Do not code by vibes when a source-of-truth document already exists.

| Folder | Purpose |
|---|---|
| [`docs/00-overview`](docs/00-overview) | Product vision, scope, principles, stakeholders, assumptions, glossary, roadmap |
| [`docs/01-business`](docs/01-business) | Problem statement, value proposition, domain overview, personas, user journey, KPIs |
| [`docs/02-requirements`](docs/02-requirements) | Functional / non-functional requirements, role-permission matrix, acceptance criteria |
| [`docs/03-product`](docs/03-product) | Feature list, feature specs, state machines, use cases, user flows |
| [`docs/04-design`](docs/04-design) | Brand system, design tokens, components, accessibility, responsive rules, page specs |
| [`docs/05-system-architecture`](docs/05-system-architecture) | Architecture overview, ADRs, C4 diagrams, deployment, tech stack |
| [`docs/06-database`](docs/06-database) | Database overview, schema, ERD, migration strategy, seed data |
| [`docs/07-api`](docs/07-api) | API overview, OpenAPI YAML, per-domain API docs, error codes |
| [`docs/08-security`](docs/08-security) | Auth rules, RBAC, data privacy, audit log, abuse prevention |
| [`docs/09-quality`](docs/09-quality) | Testing strategy, test cases, QA checklist, accessibility |
| [`docs/10-devops`](docs/10-devops) | Local setup, environment variables, CI/CD, deployment, monitoring |
| [`docs/11-operations`](docs/11-operations) | Admin guide, moderation guide, incident response, SLOs |
| [`docs/12-agent`](docs/12-agent) | AI agent source-truth map, change protocol, RAG knowledge base, AI safety |
| [`docs/15-governance-and-compliance`](docs/15-governance-and-compliance) | Governance, compliance matrix, data governance |
| [`docs/16-legal`](docs/16-legal) | Terms of service, privacy policy, data processing agreement |

Full generated documentation index: [`docs/README.md`](docs/README.md)

---

## 11. Source of Truth Rules

When documents conflict, follow this priority:

```txt
1. State machine source of truth
2. Feature spec
3. API contract
4. Database schema / table specification
5. Architecture ADR
6. Design system / page spec
7. Older overview files
```

---

## 12. AI Agent Workflow

UEConnect is designed to be AI-agent friendly. Agents must read source-of-truth docs before writing code.

Main agent entry points:

- [`AGENTS.md`](AGENTS.md)
- [`GEMINI.md`](GEMINI.md)
- [`docs/12-agent/1. source-truth-map.md`](docs/12-agent/1.%20source-truth-map.md)
- [`docs/12-agent/2. agent-change-protocol.md`](docs/12-agent/2.%20agent-change-protocol.md)
- [`docs/12-agent/3. agent-task-checklist.md`](docs/12-agent/3.%20agent-task-checklist.md)

Agent rule:

```txt
Read source of truth first.
Do not invent business rules.
Do not bypass privacy, moderation, verification, audit or permission constraints.
Update docs when behavior changes.
```

---

## 13. Repository Rules

Never commit:

```txt
.env
vendor/
node_modules/
real user data / verification evidence
API keys or cloud credentials
```

Commit convention:

```bash
git commit -m "feat: implement HCMUE email registration rule"
git commit -m "fix: prevent unverified users from accessing app shell"
git commit -m "chore: update supervisord queue connection"
git commit -m "docs: add deployment runbook"
```

Branch strategy:

```txt
main                     ← production, protected
fix/<issue-slug>         ← bug fixes
feat/<feature-slug>      ← new features
chore/<task-slug>        ← maintenance / refactoring
```

---

## 14. License and Academic Context

UEConnect is currently a student-led academic/product engineering project for HCMUE-oriented social platform design and implementation. Licensing and deployment policy should be finalized before any public production release.

---

<p align="center">
  <strong>UEConnect</strong> · Verified community layer for HCMUE students
</p>
