# Source Of Truth for tech stack which is really used in our projects
---
title: "Technology Stack"
module: "05-system-architecture"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "System Architecture / Backend / Frontend / DevOps"
depends_on:
  - "architecture-overview.md"
  - "system-context.md"
  - "container-diagram.md"
  - "architecture-decision-records/adr-001-tech-stack.md"
  - "architecture-decision-records/adr-002-auth-strategy.md"
  - "architecture-decision-records/adr-003-database-choice.md"
  - "../02-requirements/non-functional-requirements.md"
  - "../02-requirements/role-permission-matrix.md"
  - "../03-product/feature-priority.md"
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
related:
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
  - "../03-product/feature-specs/media-upload.md"
  - "../03-product/feature-specs/messaging.md"
  - "../03-product/feature-specs/notification.md"
  - "../03-product/feature-specs/moderation.md"
  - "../03-product/feature-specs/admin-operations.md"
  - "../03-product/feature-specs/analytics-events.md"
---

# Technology Stack

## 1. Purpose

File này chốt technology stack chính thức cho UEConnect.

Mục tiêu:

- Xác định công nghệ dùng cho frontend, backend, database, realtime, queue, storage, notification, auth, admin, analytics và AI-assisted verification.
- Giữ stack đủ mạnh để làm social platform hoàn chỉnh cho HCMUE.
- Tránh over-engineering kiểu microservices khi MVP chưa cần.
- Tránh chọn công nghệ chỉ vì “nghe enterprise”.
- Đảm bảo stack dễ học, dễ code, dễ deploy, dễ debug, dễ mở rộng.

UEConnect là một verified campus social platform, không phải landing page, không phải CRUD demo và càng không phải nơi để thử 15 framework cho vui. Cái đó gọi là tự phá hoại có kế hoạch.

---

# 2. Final Tech Stack Summary

## 2.1. Recommended Stack

| Layer | Selected Technology |
|---|---|
| Backend Framework | Laravel |
| Auth Foundation | Laravel Fortify or Laravel Breeze with customization |
| Frontend Rendering | Blade + Livewire |
| Frontend Interaction | Alpine.js |
| Styling | TailwindCSS |
| Build Tool | Vite |
| Icon System | Lucide Icons |
| PWA | Vite PWA plugin / Laravel PWA integration |
| Database | SQL Server |
| ORM | Eloquent |
| Authorization | Laravel Policies / Gates |
| Global Permissions | Spatie Laravel Permission, optional |
| Scoped Permissions | Custom `permission_grants` table |
| Queue | Laravel Queue |
| Scheduler | Laravel Scheduler |
| Realtime | Laravel Reverb + Laravel Echo |
| Notification | Laravel Notifications + Browser Push |
| File Storage | Laravel Storage |
| Private File Access | Protected controller route / signed URL |
| Search MVP | SQL Server indexed search / full-text where available |
| Analytics MVP | Internal DB event table |
| Admin UI | Laravel Blade / Livewire admin pages |
| AI-assisted Evidence Review | Optional P1/P2 service using OCR / Document AI |
| Testing | PHPUnit / Pest, Laravel Feature Tests, Browser/UI tests later |
| Deployment | Web process + queue worker + scheduler + realtime process |
| Observability | Laravel logs, failed jobs, audit logs, error monitoring future |

---

# 3. Architecture Style

## 3.1. Selected Style

UEConnect uses:

```txt
Modular Monolith
+ Laravel-first backend
+ Server-rendered PWA
+ Livewire interactive UI
+ Event-driven internal workflows
+ Queue-based async processing
+ Realtime WebSocket layer
````

## 3.2. Why Modular Monolith

Modular monolith is selected because:

* The product has many connected domains.
* The team likely needs fast iteration.
* Laravel supports the whole stack well.
* Deployment is simpler than microservices.
* Debugging permission, verification, feed, realtime and moderation is easier inside one app.
* Future extraction is still possible if modules are clean.

Not selected for MVP:

```txt
microservices
serverless-first architecture
separate React SPA + API-only backend
event-sourcing everything
CQRS everywhere
Kubernetes-first deployment
```

Those are powerful, sure. Also a delightful way to spend three months configuring infrastructure before the login page works.

---

# 4. Backend Stack

## 4.1. Framework

Selected:

```txt
Laravel
```

Responsibilities:

* Auth.
* Routing.
* Middleware.
* Policies / Gates.
* Form Requests.
* Eloquent ORM.
* Queue.
* Scheduler.
* Notifications.
* Storage.
* Broadcasting.
* Admin operations.
* API endpoints if needed.

## 4.2. Laravel Version Direction

Use:

```txt
Latest stable Laravel version supported by the deployment environment.
```

Do not lock the documentation to a minor version unless the project repository has already selected it.

## 4.3. Backend Coding Style

Use:

```txt
Controllers for HTTP boundary
Form Requests for validation
Actions / Services for use cases
Policies for authorization
Events for domain events
Listeners / Jobs for side effects
DTOs when payload becomes complex
Enums for business states
```

Avoid:

```txt
fat controllers
business logic in Blade
permission checks only in frontend
random helper functions for domain logic
giant God service class
```

## 4.4. Suggested Backend Structure

```txt
app/
├── Actions/
│   ├── Auth/
│   ├── Verification/
│   ├── Profile/
│   ├── Feed/
│   ├── Messaging/
│   ├── Community/
│   ├── Mentor/
│   ├── Moderation/
│   └── Admin/
├── Enums/
├── Events/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
├── Services/
│   ├── Realtime/
│   ├── Storage/
│   ├── Search/
│   ├── Analytics/
│   └── EvidenceIntelligence/
└── Support/
```

---

# 5. Authentication Stack

## 5.1. Selected Direction

Use one of:

```txt
Laravel Fortify
Laravel Breeze with custom authentication flow
```

Recommended:

```txt
Laravel Fortify for auth backend foundation
Custom Blade/Livewire UI for UEConnect
```

Fortify is suitable because it provides backend authentication features such as login, registration, password reset and email verification while staying frontend-agnostic.

## 5.2. Auth Requirements

MVP:

```txt
email + password login
only hcmue.edu.vn email allowed
remember me
password reset
minimum password length: 8
account gate after login
no username
no 2FA
no login rate limit yet
```

Future:

```txt
2FA
rate limiting
device/session management
suspicious login detection
login audit
```

## 5.3. Microsoft / Outlook Edu Login

UEConnect should support a Microsoft / Azure identity direction for HCMUE Outlook/edu login if the school email system is Microsoft-based.

Important:

```txt
Do not assume Google OAuth is enough for HCMUE edu identity.
```

Implementation direction:

```txt
Use Microsoft Entra ID / Azure OAuth integration if school tenant/app registration is available.
Fallback to email/password with hcmue.edu.vn domain verification.
```

## 5.4. Account Gate

After login:

```txt
banned/suspended
→ /account/restricted

not verified
→ /app/verification

verified but profile incomplete
→ /app/profile/setup or /app/onboarding

verified and ready
→ /app/home

admin
→ /admin only if user accesses admin route and has permission
```

---

# 6. Frontend Stack

## 6.1. Selected Frontend

```txt
Blade
Livewire
Alpine.js
TailwindCSS
Vite
```

## 6.2. Why Not React SPA for MVP

React SPA is not selected as default because:

* Laravel Blade/Livewire is faster for this stack.
* Auth/session/permission rendering is easier.
* Server-rendered pages work well for form-heavy product.
* Admin pages are easier.
* SEO/public pages can be handled simply.
* Less frontend complexity.

React can be introduced later for very interactive surfaces if needed, but MVP does not require it.

## 6.3. Livewire Usage

Use Livewire for:

```txt
verification forms
profile setup/edit
feed composer
comment composer
settings forms
admin tables
moderation queues
mentor request forms
community management
notification center
search/filter interactions
```

Do not use Livewire for:

```txt
high-frequency realtime message transport itself
large complex drag/drop interactions
heavy client-side canvas
```

Messaging can use Blade/Livewire for shell and REST/actions for sending, while realtime updates come from Echo/Reverb.

## 6.4. Alpine.js Usage

Use Alpine.js for:

```txt
dropdowns
modals
bottom sheets
tabs
accordion
menus
local UI toggles
notification panels
mobile nav
```

Avoid turning Alpine into a mini frontend framework from the shadow realm.

## 6.5. Styling

Use:

```txt
TailwindCSS
UEConnect design tokens
Blade components
Lucide icons
```

Rules:

```txt
UI text is Vietnamese
code/component names are English
avoid arbitrary Tailwind values
use semantic component variants
support focus-visible
mobile-first responsive
```

---

# 7. PWA Stack

## 7.1. PWA Direction

UEConnect is a mobile-first PWA.

Use:

```txt
Vite PWA plugin or Laravel-compatible PWA setup
manifest.json
service worker
app icons
offline fallback page
browser push support
```

## 7.2. MVP PWA Features

MVP should include:

```txt
installable app
app icon
splash screen basics
offline fallback
cached static assets
browser push permission flow
safe-area aware layout
```

## 7.3. Not MVP

Not required immediately:

```txt
full offline posting
offline message queue
background sync
native app bridge
deep native notification actions
```

Offline MVP should be honest:

```txt
show cached content if available
disable actions requiring network
show offline banner
```

Do not fake success offline. That is how trust dies wearing a loading spinner.

---

# 8. Database Stack

## 8.1. Selected Database

```txt
SQL Server
```

## 8.2. Why SQL Server

SQL Server fits because:

* Relational data model is strong.
* Good for university/enterprise environment.
* Supports structured records and constraints.
* Works for accounts, verification, feed, community, permissions, audit, moderation.
* Familiar in many academic/enterprise contexts.

## 8.3. ORM

Use:

```txt
Laravel Eloquent
```

## 8.4. DB Design Rules

Use:

```txt
string enums for business status
foreign keys for important relationships
indexes for feed/search/admin queries
soft deletes for user-generated content
append-only audit logs
separate tables for action histories
```

## 8.5. Sensitive Tables

Sensitive data includes:

```txt
verification evidence metadata
private messages
reports
moderation notes
permission grants
audit logs
browser push subscriptions
```

These must have strict policy checks.

---

# 9. Authorization Stack

## 9.1. Selected Direction

Use:

```txt
Laravel Policies / Gates
```

Optional:

```txt
Spatie Laravel Permission for global roles and permissions
```

Required:

```txt
Custom scoped permission grants
```

## 9.2. Why Custom Scoped Permissions

Because UEConnect has permissions like:

```txt
manage community A
review reports globally
moderate community B
own club C
manage mentor access
review verification
```

Global role alone is not enough.

## 9.3. Suggested Tables

```txt
roles
permissions
model_has_roles
model_has_permissions
permission_grants
```

Custom `permission_grants` fields:

```txt
id
user_id
permission_key
scope_type nullable
scope_id nullable
granted_by
reason nullable
starts_at nullable
expires_at nullable
revoked_at nullable
created_at
updated_at
```

## 9.4. Enforcement Rule

Every sensitive action must pass:

```txt
route middleware
policy/gate
business state validation
scope check if applicable
```

UI hiding is never authorization. A button hidden in Blade is just a shy button, not security.

---

# 10. Realtime Stack

## 10.1. Selected Realtime

```txt
Laravel Reverb
Laravel Echo
Private channels
Presence channels where useful
```

Laravel broadcasting supports Reverb, Pusher and Ably configuration, while Echo provides a client library to subscribe to channels and listen for server-side broadcast events.

## 10.2. Why Laravel Reverb for MVP

Reverb is selected because:

* First-party Laravel direction.
* Works naturally with Laravel broadcasting.
* Compatible with Laravel Echo.
* Self-hosted option.
* Good for campus-scale MVP.
* Avoids early dependency on paid third-party realtime services.

## 10.3. Realtime Use Cases

Use realtime for:

```txt
direct messages
community chat messages
typing indicators
read receipts
notification received
conversation updates
```

Do not require realtime for:

```txt
feed initial load
verification review
admin reports list initial load
career pathway content
static profile view
```

## 10.4. Realtime Rule

```txt
Database is source of truth.
Realtime event is delivery mechanism.
Client must be able to refresh and recover.
```

## 10.5. Realtime Fallback

If realtime disconnects:

```txt
show reconnecting state
allow manual refresh
continue sending through HTTP if available
sync from DB after reconnect
```

---

# 11. Queue & Scheduler Stack

## 11.1. Queue

Use:

```txt
Laravel Queue
```

Queue driver options:

```txt
database queue for MVP/simple deployment
Redis queue for higher performance future
```

## 11.2. Jobs

Queue jobs for:

```txt
send notification
send browser push
send email
process uploaded media
run evidence intelligence analysis
generate thumbnails
cleanup expired notifications
expire old verification requests
write analytics if async
```

## 11.3. Scheduler

Use:

```txt
Laravel Scheduler
```

Scheduled tasks:

```txt
delete/expire notifications after 7 days
expire old verification requests
cleanup temporary uploads
retry failed lightweight jobs if appropriate
generate daily admin summaries future
```

---

# 12. Storage & Media Stack

## 12.1. Storage

Use:

```txt
Laravel Storage
```

Supported disks:

```txt
local for development
S3-compatible storage for production
Azure Blob Storage optional if Microsoft ecosystem is preferred
```

## 12.2. File Categories

```txt
avatar
verification_evidence
post_image
message_attachment
community_resource
admin_export
```

## 12.3. File Visibility

| File Type             | Visibility                  |
| --------------------- | --------------------------- |
| Avatar                | Public or controlled public |
| Post image            | Visible to allowed viewers  |
| Message attachment    | Private                     |
| Verification evidence | Private                     |
| Community resource    | Community/member controlled |
| Admin export          | Private                     |

## 12.4. Evidence Upload Rule

MVP verification evidence:

```txt
max files: 3
max size: 5MB each
allowed: jpg/jpeg/png/pdf/webp
link supported
note per file
```

## 12.5. Private File Access

Use:

```txt
protected route
policy check
temporary signed URL if needed
stream response
```

Never expose raw storage path.

---

# 13. Notification Stack

## 13.1. Selected Notification Stack

Use:

```txt
Laravel Notifications
Database notifications
Browser Push notification
Realtime broadcast for in-app updates
```

## 13.2. Notification Channels

MVP:

```txt
in-app
browser push
```

Optional:

```txt
email for account/security/support-critical events
```

Not MVP:

```txt
SMS
native mobile push
Zalo notification
Telegram bot
```

## 13.3. Notification Retention

```txt
normal notification retention: 7 days
critical admin/audit history: stored in audit logs, not only notifications
```

## 13.4. Privacy-safe Preview

Do not put sensitive data in notification preview:

```txt
message full body if sensitive
report description
verification evidence
admin internal note
private profile field
```

---

# 14. Search Stack

## 14.1. MVP Search

Use:

```txt
SQL Server indexed search
SQL Server full-text search if configured
carefully optimized LIKE search for small scope MVP
```

## 14.2. Search Scope

```txt
users/profiles
posts
communities
mentors
career pathways
resources
```

## 14.3. Search Rules

Search must enforce:

```txt
privacy setting
block relationship
moderation state
account status
community membership
permission scope
```

## 14.4. Future Search Stack

Future options:

```txt
Meilisearch
Typesense
OpenSearch
Elasticsearch
semantic search with vector database
```

Do not introduce search engine before MVP needs it. Search engines are like pets: useful, but now you have to feed them indexes forever.

---

# 15. Analytics Stack

## 15.1. MVP Analytics

Use:

```txt
internal analytics_events table
```

## 15.2. Event Schema

```txt
id
event_name
actor_id nullable
target_type nullable
target_id nullable
context_type nullable
context_id nullable
properties_json
occurred_at
created_at
```

## 15.3. Events

Track:

```txt
user_registered
verification_submitted
verification_approved
profile_completed
post_created
comment_created
greeting_sent
greeting_accepted
message_sent
mentor_request_created
community_join_requested
report_submitted
moderation_action_taken
```

## 15.4. Privacy Rule

Never store:

```txt
message body
report description
verification evidence content
password
token
private note
```

---

# 16. AI-assisted Evidence Intelligence

## 16.1. Idea

UEConnect can use AI/computer vision to assist identity verification by reading uploaded evidence and extracting useful signals.

This feature is called:

```txt
Evidence Intelligence
```

It is not automatic verification.

It is admin assistance.

## 16.2. What It Can Do

AI can help:

```txt
OCR text from student card / PDF / image
extract MSSV-like number
extract name-like text
detect HCMUE keywords
detect document type
detect file readability
detect blurry/low-quality image
detect duplicate-looking evidence
detect mismatch between submitted MSSV and OCR result
detect possible tampering signs, future
classify evidence confidence
pre-fill admin review fields
```

## 16.3. What It Must Not Do in MVP

AI must not:

```txt
auto-approve user
auto-reject user
make final identity decision
store raw sensitive extracted text in analytics
expose OCR output to other users
replace admin audit reason
act without human review
```

## 16.4. Feasibility

This is feasible with current OCR/document AI technology.

Possible approaches:

| Approach                        | Feasibility |        Cost |                         Accuracy | Recommendation          |
| ------------------------------- | ----------: | ----------: | -------------------------------: | ----------------------- |
| Manual admin review only        |   Very high |         Low |                  Human-dependent | MVP baseline            |
| Basic OCR with open-source tool |      Medium |         Low |                            Mixed | Good for experiment     |
| Azure Document Intelligence     |        High |      Medium |             Strong for documents | Best enterprise path    |
| Azure Vision OCR                |        High |      Medium |          Good for general images | Good for image evidence |
| Custom ML model                 | Low for MVP |        High |               Depends on dataset | Not recommended now     |
| LLM vision model                | Medium/High | Medium/High | Good but needs policy guardrails | Optional later          |

## 16.5. Recommended Product Decision

For MVP:

```txt
Do not include AI-assisted verification in required MVP scope.
Design database and workflow so it can be added later.
```

For P1:

```txt
Add Evidence Intelligence as admin-assist feature.
```

For P2:

```txt
Add confidence scoring, duplicate detection and quality checks.
```

Final verification decision remains admin-owned.

## 16.6. Why Not MVP

Reasons:

```txt
verification is trust-core
false positive can approve wrong user
false negative can unfairly block real student
OCR may fail on blurry images
student cards/forms may vary
privacy and data retention must be handled carefully
AI vendor cost exists
human review is still required
```

Machine confidence is not truth. It is a number wearing a suit.

## 16.7. Recommended Workflow

```txt
User uploads evidence
→ System validates file type/size
→ Verification request created
→ Queue job runs OCR/document analysis
→ AI extracts signals
→ System stores structured analysis result
→ Admin sees AI hints in review screen
→ Admin approves/rejects/needs more info
→ Audit log stores admin decision, not AI as final actor
```

## 16.8. Evidence Intelligence Output

Example fields:

```txt
document_type_guess
ocr_text_excerpt_safe
detected_name
detected_student_id
detected_school_keywords
matched_submitted_mssv
matched_submitted_name
readability_score
blur_score
tamper_risk_score
confidence_score
analysis_status
analysis_provider
analysis_started_at
analysis_completed_at
analysis_error
```

## 16.9. Suggested Tables

### `evidence_analysis_jobs`

```txt
id
verification_evidence_id
provider
status
started_at
completed_at
error_message
created_at
updated_at
```

### `evidence_analysis_results`

```txt
id
verification_evidence_id
document_type_guess
detected_name
detected_student_id
detected_school_keywords_json
matched_submitted_mssv
matched_submitted_name
readability_score
tamper_risk_score
confidence_score
raw_result_path nullable
created_at
updated_at
```

## 16.10. Privacy Rules

AI analysis must follow strict privacy:

```txt
only run after user consents in verification flow
do not expose result outside admin review
do not store unnecessary raw OCR text forever
do not send evidence to third party unless privacy notice allows it
delete temporary processing artifacts
log provider and processing status
do not use evidence for model training unless explicitly allowed, preferably never
```

## 16.11. Admin UI Direction

Admin review page should show:

```txt
AI analysis status
confidence badge
detected MSSV
submitted MSSV
match/mismatch indicator
detected HCMUE keywords
readability warning
possible issue warning
raw evidence preview
admin decision controls
```

Example copy:

```txt
AI chỉ hỗ trợ đọc minh chứng. Admin vẫn là người quyết định kết quả xác thực.
```

## 16.12. Decision Matrix

| Question                             | Answer                                            |
| ------------------------------------ | ------------------------------------------------- |
| Có khả thi không?                    | Có                                                |
| Có nên đưa vào MVP không?            | Không bắt buộc                                    |
| Có nên thiết kế chỗ mở rộng không?   | Có                                                |
| Có nên auto approve không?           | Không                                             |
| Có nên dùng để gợi ý admin không?    | Có                                                |
| Có nên dùng Azure nếu đi enterprise? | Có, đặc biệt nếu đã dùng Microsoft/Azure identity |
| Có nên tự train model lúc đầu không? | Không                                             |

## 16.13. Recommended Roadmap

### MVP

```txt
manual verification
protected evidence preview
admin action reason
audit log
store evidence metadata cleanly
```

### P1

```txt
OCR extraction
MSSV/name/school keyword detection
readability warning
admin-assist panel
```

### P2

```txt
confidence scoring
duplicate evidence detection
tampering risk indicators
batch analysis
analytics on verification bottlenecks
```

### P3

```txt
custom model trained on approved internal template data
semi-automated routing
risk-based review priority
```

---

# 17. Testing Stack

## 17.1. Backend Tests

Use:

```txt
PHPUnit or Pest
Laravel Feature Tests
Laravel Unit Tests
Policy Tests
Form Request Tests
Queue Job Tests
```

## 17.2. Critical Test Areas

```txt
auth gate
verification state transition
MSSV uniqueness
permission policies
scoped permission grants
greeting accept flow
conversation creation
message sending
report duplicate prevention
moderation action audit
private file access
notification creation
```

## 17.3. Frontend/UI Tests

Use later:

```txt
Laravel Dusk
Playwright
or Pest browser testing if project adopts it
```

Test:

```txt
auth flow
verification form
profile setup
feed composer
messaging
report modal
admin verification review
responsive navigation
```

---

# 18. Deployment Stack

## 18.1. Process Types

Production should run:

```txt
web process
queue worker process
scheduler process
reverb websocket process
database
storage
cache
```

## 18.2. Environment

```txt
local
staging
production
```

## 18.3. Deployment Requirements

```txt
.env managed securely
APP_KEY set
database migrations controlled
queue worker supervised
scheduler running
storage linked/configured
reverb configured
HTTPS required
browser push keys configured if enabled
```

## 18.4. Hosting Options

Possible:

```txt
VPS
Render-like PaaS with worker support
Azure App Service
Docker-based deployment
school/private server
```

For production, choose a host that supports:

```txt
long-running queue worker
scheduler/cron
WebSocket process
SQL Server connectivity
persistent storage or external object storage
HTTPS
```

---

# 19. Observability Stack

## 19.1. MVP Observability

Use:

```txt
Laravel logs
failed_jobs table
audit_logs table
admin activity log
basic health check route
database query log in local only
```

## 19.2. Future Observability

Add:

```txt
Sentry
Laravel Pulse
Telescope for local/staging only
centralized logs
uptime monitoring
queue monitoring
realtime monitoring
```

## 19.3. Logging Rules

Never log:

```txt
passwords
tokens
verification evidence content
private message body
report description
raw OCR full text if sensitive
```

---

# 20. Development Tooling

## 20.1. Backend

```txt
PHP
Composer
Laravel Artisan
PHPStan/Larastan optional
Laravel Pint
PHPUnit/Pest
```

## 20.2. Frontend

```txt
Node.js
pnpm or npm
Vite
TailwindCSS
Alpine.js
Livewire assets
```

## 20.3. Database

```txt
SQL Server
SQL Server Management Studio or Azure Data Studio
Laravel migrations
Laravel seeders
```

## 20.4. Git Workflow

Recommended:

```txt
feature branches
pull request review
commit by module
no secrets committed
migration review required
```

---

# 21. Stack Decision Summary

| Decision           | Final                                  |
| ------------------ | -------------------------------------- |
| Architecture style | Modular monolith                       |
| Backend            | Laravel                                |
| Auth               | Fortify/Breeze custom                  |
| Frontend           | Blade + Livewire + Alpine              |
| CSS                | TailwindCSS                            |
| Database           | SQL Server                             |
| Realtime           | Laravel Reverb + Echo                  |
| Queue              | Laravel Queue                          |
| Notification       | Laravel Notifications + Browser Push   |
| Storage            | Laravel Storage + private routes       |
| Search             | SQL Server search MVP                  |
| Analytics          | DB event table                         |
| AI Evidence        | P1 admin-assist, not MVP auto decision |
| Admin              | Blade/Livewire admin                   |
| PWA                | Vite/Laravel PWA setup                 |

---

# 22. Must-not-use List

Do not use in MVP:

```txt
microservices
Kubernetes-first deployment
separate React SPA unless specifically justified
auto-approve AI identity verification
public raw file URLs for evidence
frontend-only authorization
third-party realtime as source of truth
analytics with sensitive payload
random OAuth assumptions
unscoped admin permissions for community management
```

---

# 23. Tech Stack QA Checklist

Before implementation:

```txt
[ ] Laravel project initialized.
[ ] Auth strategy selected: Fortify or Breeze.
[ ] HCMUE email rule implemented.
[ ] Account gate middleware defined.
[ ] SQL Server connection works.
[ ] Eloquent models use string enum states.
[ ] Policies created for sensitive modules.
[ ] Scoped permission table planned.
[ ] Queue worker configured.
[ ] Scheduler configured.
[ ] Reverb/Echo configured for realtime.
[ ] Storage disks configured.
[ ] Private file route designed.
[ ] Notification table/channel designed.
[ ] Browser push decision confirmed.
[ ] Analytics event schema planned.
[ ] Audit log schema planned.
[ ] Evidence Intelligence kept outside MVP critical path.
```

---

# 24. Final Rule

Tech stack của UEConnect phải phục vụ trust, not trend.

Một công nghệ được chọn khi nó giúp:

```txt
xây nhanh hơn
ít bug hơn
dễ kiểm soát permission hơn
dễ audit hơn
dễ maintain hơn
dễ deploy hơn
phù hợp product scope hơn
```

Không chọn công nghệ vì:

```txt
nó đang hot
nhìn enterprise
nghe giống công ty lớn
AI bảo thế
muốn thử cho biết
```
