<p align="center">
  <img src="docs\04-design\primary-logo.png" alt="UEConnect Logo" width="760">
</p>

<h1 align="center">UEConnect</h1>

<p align="center">
  <strong>Kết nối cộng đồng HCMUE</strong><br>
  Verified campus social platform for HCMUE students, mentors, alumni, advisors, clubs and communities.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4">
  <img src="https://img.shields.io/badge/Blade-Server%20Rendered-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Blade">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="TailwindCSS 3">
  <img src="https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 8">
  <img src="https://img.shields.io/badge/SQL%20Server-Database-CC2927?style=for-the-badge&logo=microsoftsqlserver&logoColor=white" alt="SQL Server">
  <img src="https://img.shields.io/badge/Reverb-Realtime-7C3AED?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Reverb">
  <img src="https://img.shields.io/badge/Sanctum-Auth%20Tokens-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Sanctum">
  <img src="https://img.shields.io/badge/Boost-AI%20Assisted-111827?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Boost">
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

UEConnect **không phải dating app**, **không phải Facebook clone**, và **không phải super app sinh viên**. Phạm vi hiện tại tập trung vào một social layer đáng tin cậy cho đời sống sinh viên HCMUE. Thế là đủ đau rồi, khỏi nhét thêm thanh toán học phí vào cho vũ trụ thêm hỗn loạn.

---

## 2. Current Implementation Status

Repository hiện đã có nền Laravel application và bộ tài liệu enterprise-style cho toàn bộ sản phẩm.

| Area | Status |
|---|---|
| Laravel foundation | Initialized |
| Breeze / Livewire auth starter | Installed |
| Reverb package | Installed |
| Laravel Boost | Installed |
| Spatie Laravel Permission | Installed |
| Sanctum | Installed |
| Dev scripts | `dev` and `dev:laragon` configured |
| Local setup guide | Available |
| Product / Design / Architecture / DB / API docs | Available |
| Production feature implementation | In progress |

> Local environment must still be verified on each developer machine because PHP PATH, SQL Server drivers, ODBC, `.env`, queue and Reverb are local-machine dependent. Máy tính mỗi người một tính, rất dân chủ và rất phiền.

---

## 3. Architecture Direction

UEConnect follows a **Modular Monolith** architecture:

```txt
Laravel-first backend
+ Server-rendered PWA direction
+ Blade / Livewire interactive UI
+ Event-driven internal workflows
+ Queue-based async processing
+ Realtime WebSocket layer
+ SQL Server as source of truth
```

MVP intentionally avoids unnecessary complexity such as microservices, Kubernetes-first deployment, separate React SPA, or event-sourcing everything. The project is already ambitious; no need to summon infrastructure demons before the login page earns its rent.

---

## 4. Tech Stack

### 4.1 Runtime and Backend

| Layer | Technology |
|---|---|
| Language | PHP `^8.3` |
| Framework | Laravel `^13.8` |
| Auth starter | Laravel Breeze + Livewire customization |
| API token foundation | Laravel Sanctum |
| ORM | Laravel Eloquent |
| Authorization | Laravel Policies / Gates |
| Global roles & permissions | Spatie Laravel Permission |
| Scoped permissions | Custom `permission_grants` table planned |
| Queue | Laravel Queue |
| Scheduler | Laravel Scheduler |
| Code style | Laravel Pint |
| AI-assisted dev | Laravel Boost |

### 4.2 Frontend

| Layer | Technology |
|---|---|
| Rendering | Blade |
| Reactive UI | Livewire `^4.3` + Volt |
| Interaction | Alpine.js direction |
| Styling | TailwindCSS |
| Forms | `@tailwindcss/forms` |
| Build tool | Vite |
| Realtime client | Laravel Echo + Pusher JS protocol client |
| Icon direction | Lucide Icons / SVG components |

### 4.3 Data, Realtime and Operations

| Layer | Technology |
|---|---|
| Database | SQL Server |
| Database source of truth | SQL Server + Eloquent models |
| Realtime | Laravel Reverb + Laravel Echo |
| Notifications | Laravel Notifications + browser push direction |
| Storage | Laravel Storage |
| Private file access | Protected routes / signed URLs |
| Search MVP | SQL Server indexed search / full-text where available |
| Analytics MVP | Internal analytics event tables |
| Observability | Laravel logs, failed jobs, audit logs |

---

## 5. Core Product Modules

UEConnect is organized around these main product domains:

| Domain | Purpose |
|---|---|
| Authentication | Register, login, logout, password reset, account gate |
| Identity Verification | Verify student / alumni / advisor identity before app access |
| Profile Management | Public identity surface, profile editing, metadata, privacy controls |
| Onboarding | First-time guided setup and activation checklist |
| Settings & Privacy | Visibility, blocked users, account settings |
| Media Upload | Avatar, post media, private verification evidence |
| Home Feed | Main social feed for verified users |
| Posts & Comments | Post detail, comments, visibility, moderation hooks |
| Safety Reporting | Report, block and safety workflow |
| Moderation | Moderation queue, content/user actions, evidence review |
| Admin Operations | Verification queue, user management, roles, audit, policy controls |
| Discovery Profile | Search and discover UEers safely |
| Greeting & Connection | Send greeting, accept/decline, create connection state |
| Messaging | Realtime 1:1 conversation after valid connection |
| Notification | In-app and browser push notification flows |
| Mentor Matching | Mentor profile, mentor request and guided support |
| Career Pathway | HCMUE-aware career exploration layer |
| Community & Club | Club/community listing, governance and participation |
| Search & Filter | Cross-module search/filter behavior |
| Analytics Events | Product metrics and usage event tracking |

---

## 6. Documentation Map

The `docs/` folder is the project brain. Do not code by vibes when a source-of-truth document already exists. Shocking concept, apparently.

| Folder | Purpose |
|---|---|
| [`docs/00-overview`](docs/00-overview) | Product vision, scope, principles, stakeholders, assumptions, glossary, roadmap |
| [`docs/01-business`](docs/01-business) | Problem statement, value proposition, domain overview, personas, user journey, KPIs, business rules |
| [`docs/02-requirements`](docs/02-requirements) | Functional / non-functional requirements, role-permission matrix, acceptance criteria, edge cases, traceability |
| [`docs/03-product`](docs/03-product) | Product overview, feature list, feature priority, sitemap, feature specs, state machines, use cases, user flows |
| [`docs/04-design`](docs/04-design) | Brand system, design tokens, components, interaction states, accessibility, responsive rules, page specs, UI states |
| [`docs/05-system-architecture`](docs/05-system-architecture) | Architecture overview, ADRs, context/container/component diagrams, deployment, sequence diagrams, tech stack |
| [`docs/06-database`](docs/06-database) | Database overview, schema, ERD, migration strategy, seed data, table specifications |
| [`docs/07-api`](docs/07-api) | API overview, OpenAPI YAML, auth/user/community/event/job/mentorship APIs, error codes |
| [`docs/08-security`](docs/08-security) | Security architecture, auth rules, access control, data privacy, audit and safety-related policies |
| [`docs/09-quality`](docs/09-quality) | Testing strategy, QA direction, acceptance validation |
| [`docs/10-devops`](docs/10-devops) | Local setup, deployment, environment and developer operations |
| [`docs/11-operations`](docs/11-operations) | Admin operations, support, monitoring and runtime workflows |
| [`docs/12-agent`](docs/12-agent) | AI agent source-truth map, change protocol, task checklist, RAG knowledge base and AI safety |
| [`docs/13-analytics`](docs/13-analytics) | Analytics event strategy and product metrics |
| [`docs/14-release-management`](docs/14-release-management) | Release planning, versioning and deployment readiness |
| [`docs/15-governance-and-compliance`](docs/15-governance-and-compliance) | Governance, compliance and institutional constraints |
| [`docs/16-legal`](docs/16-legal) | Legal notes, consent, content and data responsibilities |
| [`docs/17-localization`](docs/17-localization) | Vietnamese-first language, localization and i18n direction |
| [`docs/18-docs-assets`](docs/18-docs-assets) | Documentation images, brand/logo assets and supporting visuals |
| [`docs/99-appendix`](docs/99-appendix) | References, appendix and supporting notes |

Full generated documentation index: [`docs/README.md`](docs/README.md)

---

## 7. Source of Truth Rules

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

For broad or cross-module work, start with:

- [`docs/README.md`](docs/README.md)
- [`docs/DOCUMENTATION-STANDARDS.md`](docs/DOCUMENTATION-STANDARDS.md)
- [`docs/03-product/1. product-overview.md`](docs/03-product/1.%20product-overview.md)
- [`docs/03-product/2. feature-list.md`](docs/03-product/2.%20feature-list.md)
- [`docs/03-product/3. feature-priority.md`](docs/03-product/3.%20feature-priority.md)
- [`docs/03-product/4. sitemap.md`](docs/03-product/4.%20sitemap.md)
- [`docs/03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md`](docs/03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md)
- [`docs/05-system-architecture/architecture-overview.md`](docs/05-system-architecture/architecture-overview.md)
- [`docs/05-system-architecture/techstack.md`](docs/05-system-architecture/techstack.md)

---

## 8. Local Development Setup

Read the full setup guide first:

- [`UEConnect-Local-Setup-From-Scratch.md`](UEConnect-Local-Setup-From-Scratch.md)

### 8.1 Requirements

```txt
PHP 8.3+
Composer 2.x+
Node.js 22+
npm 10+
Laragon
SQL Server Express / Developer
SQL Server Management Studio or Azure Data Studio
Microsoft ODBC Driver for SQL Server
Microsoft Drivers for PHP for SQL Server: sqlsrv + pdo_sqlsrv
```

### 8.2 Safe Foundation Setup

```bash
composer install
npm install
copy .env.example .env
```

Before deep Artisan commands, configure `.env` in safe mode:

```env
DB_CONNECTION=sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
```

Then run:

```bash
type nul > database\database.sqlite
php artisan key:generate
php artisan package:discover
php artisan route:list
```

### 8.3 Development Server

Use Laravel's built-in server:

```bash
composer run dev
```

Open:

```txt
http://127.0.0.1:8000
```

Or use Laragon virtual host:

```bash
composer run dev:laragon
```

Open:

```txt
http://ue-connect.test
```

Expected dev logs:

```txt
[vite] VITE ready
[reverb] Starting server on 0.0.0.0:8080
[server] Server running on http://127.0.0.1:8000
```

---

## 9. Useful Commands

```bash
# Laravel
php artisan route:list
php artisan migrate:status
php artisan migrate
php artisan optimize:clear
php artisan storage:link

# Frontend
npm run dev
npm run build

# Queue / Realtime
php artisan queue:work
php artisan reverb:start

# Code style
composer run format

# Test
composer test
```

---

## 10. Development Order

Recommended implementation order:

```txt
1. Auth UEConnect
2. Email domain rule @hcmue.edu.vn
3. Account status enum
4. Account gate middleware
5. Verification request base
6. Profile setup / onboarding
7. Base layout + design tokens
8. Seed roles / faculties / programs
9. Admin bootstrap
10. Home feed skeleton
```

Do not jump straight into messaging or community features before authentication, verification, permission and profile foundations are stable. That is not speed. That is architecture debt wearing running shoes.

---

## 11. Repository Rules

Never commit:

```txt
.env
vendor/
node_modules/
temp-laravel/
real user data
real verification evidence
API keys or cloud credentials
```

Commit examples:

```bash
git commit -m "chore: initialize Laravel application foundation"
git commit -m "docs: add local setup runbook"
git commit -m "feat: implement HCMUE email registration rule"
git commit -m "fix: prevent unverified users from accessing app shell"
```

---

## 12. AI Agent Workflow

UEConnect is designed to be AI-agent friendly, but agents must read source-of-truth docs before writing code. The main agent entry points are:

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

## 13. Logo Asset

This README expects the primary logo at:

```txt
docs/18-docs-assets/brand/primary-logo-nobg.png
```

If the logo does not render, copy the exported logo file into that path and commit it:

```bash
git add docs/18-docs-assets/brand/primary-logo-nobg.png
git commit -m "docs: add UEConnect primary logo asset"
```

---

## 14. License and Academic Context

UEConnect is currently a student-led academic/product engineering project for HCMUE-oriented social platform design and implementation. Licensing and deployment policy should be finalized before any public production release.

---

<p align="center">
  <strong>UEConnect</strong> · Verified community layer for HCMUE students
</p>
