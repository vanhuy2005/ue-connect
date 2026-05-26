---
title: "Architecture Overview"
module: "05-system-architecture"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "System Architecture / Backend / Frontend"
depends_on:
  - "../00-overview/product-vision.md"
  - "../01-business/business-context.md"
  - "../02-requirements/requirements-overview.md"
  - "../02-requirements/functional-requirements.md"
  - "../02-requirements/non-functional-requirements.md"
  - "../02-requirements/role-permission-matrix.md"
  - "../02-requirements/traceability-matrix.md"
  - "../03-product/product-overview.md"
  - "../03-product/feature-list.md"
  - "../03-product/feature-priority.md"
  - "../03-product/sitemap.md"
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
  - "../04-design/19-design-token-documentation.md"
  - "../04-design/20-agent-prompt-guide.md"
related:
  - "techstack.md"
  - "system-context.md"
  - "container-diagram.md"
  - "component-diagram.md"
  - "deployment-architecture.md"
  - "sequence-diagrams.md"
  - "architecture-decision-records/adr-001-tech-stack.md"
  - "architecture-decision-records/adr-002-auth-strategy.md"
  - "architecture-decision-records/adr-003-database-choice.md"
---

# Architecture Overview

## 1. Purpose

File này mô tả tổng quan kiến trúc hệ thống UEConnect.

Mục tiêu:

- Chốt hướng kiến trúc tổng thể.
- Giải thích các layer chính của hệ thống.
- Xác định boundary giữa frontend, backend, database, realtime, queue, storage, notification và admin operations.
- Làm nền cho các file chi tiết như `system-context.md`, `container-diagram.md`, `component-diagram.md`, `deployment-architecture.md`, `sequence-diagrams.md` và ADR.
- Đảm bảo architecture bám sát product requirements, design system, state machine và permission model đã khóa trước đó.

UEConnect không phải một CRUD app đơn giản.

UEConnect là một verified campus social platform có:

- Identity verification.
- Profile management.
- Home feed.
- Post/comment/media.
- Discovery.
- Greeting/connection.
- Realtime messaging.
- Notification.
- Mentor matching.
- Community/club.
- Career pathway.
- Safety reporting.
- Moderation.
- Admin operations.
- Scoped permission.
- Audit log.
- PWA behavior.

Nếu architecture không tính mấy thứ này từ đầu, sau này sẽ phải vá bằng middleware, observer, event listener và vài lời cầu nguyện lúc deploy. Không nên.

---

# 2. Architecture Goals

## 2.1. Product Goals

Architecture phải hỗ trợ:

```txt
verified HCMUE-only social platform
trust-first identity system
safe community interaction
mobile-first PWA
realtime messaging and notification
moderation and reporting
mentor/community/career growth
admin operations and auditability
````

## 2.2. Engineering Goals

Architecture phải đạt:

```txt
maintainable
modular
secure
testable
observable
scalable enough for campus usage
clear permission boundary
clear data ownership
low accidental complexity
```

## 2.3. UX / System Goals

System phải đảm bảo:

```txt
fast page response
clear loading/error/offline states
realtime where needed
safe fallback when realtime fails
privacy-aware data access
consistent account gate flow
reliable notification delivery
admin actions traceable
```

---

# 3. High-level Architecture Style

## 3.1. Selected Architecture Style

UEConnect sử dụng:

```txt
Modular Monolith
+ Laravel-first backend
+ Server-rendered PWA frontend
+ Realtime channel layer
+ Queue-based async processing
+ Event-driven internal workflow
```

## 3.2. Why Modular Monolith

Lý do chọn modular monolith:

* Phù hợp MVP và team sinh viên/dev nhỏ.
* Laravel hỗ trợ rất tốt auth, policy, queue, notification, storage, validation, scheduler.
* Giảm độ phức tạp so với microservices.
* Dễ maintain, dễ deploy, dễ debug.
* Vẫn có thể tách module/service sau này nếu scale tăng.
* Phù hợp với domain có nhiều module liên kết chặt như social platform trường học.

Không chọn microservices cho MVP vì:

```txt
domain chưa đủ ổn định
team chưa cần distributed complexity
deployment phức tạp hơn
observability khó hơn
transaction boundary khó hơn
debug realtime/permission/moderation mệt hơn
```

Microservices ở giai đoạn này giống mua xe tải để đi mua bánh mì. Có thể chạy, nhưng hơi bi kịch.

---

# 4. System Context

## 4.1. Main Actors

UEConnect phục vụ các actor:

```txt
Guest
Registered User
Verified Student
Alumni
Academic Advisor
Mentor
Club Owner
Club Manager
Moderator
Admin
Super Admin
```

## 4.2. External Systems

Các hệ thống ngoài có thể tích hợp:

| External System               | Purpose                                       |
| ----------------------------- | --------------------------------------------- |
| Microsoft Azure / Outlook Edu | HCMUE email login / identity direction        |
| Email Provider                | Password reset, verification updates, support |
| Browser Push Service          | PWA browser push notification                 |
| Object Storage                | Evidence, avatar, post image, chat attachment |
| Realtime Server               | WebSocket events for messaging/notification   |
| Queue Worker                  | Async jobs                                    |
| Scheduler / Cron              | Expiry, cleanup, notification retention       |
| Analytics Store               | Basic product event tracking                  |

## 4.3. User Entry Points

```txt
Public landing
Auth
Verification
Onboarding
Home feed
Discovery
Messaging
Community
Mentor
Career pathway
Admin dashboard
```

---

# 5. Major Containers

## 5.1. Web Application

Primary app:

```txt
Laravel Application
Blade
Livewire
Alpine.js
TailwindCSS
Vite
PWA assets
```

Responsibilities:

* Render pages.
* Handle forms.
* Manage auth sessions.
* Enforce policies.
* Serve user-facing and admin-facing UI.
* Trigger domain actions.
* Dispatch jobs/events.
* Return JSON for selected dynamic interactions if needed.

## 5.2. Database

Primary relational database:

```txt
SQL Server
```

Responsibilities:

* Store users.
* Store profiles.
* Store verification requests.
* Store posts/comments.
* Store connections.
* Store conversations/messages.
* Store communities.
* Store mentor requests.
* Store reports/moderation cases.
* Store notifications.
* Store permissions.
* Store audit logs.
* Store analytics events.

## 5.3. Realtime Layer

Recommended default:

```txt
Laravel Reverb + Laravel Echo
```

Responsibilities:

* Messaging events.
* Typing indicators.
* Read receipts.
* Notification updates.
* Community chat updates.

Realtime is not source of truth.

```txt
Database = source of truth
WebSocket = transport / delivery channel
```

## 5.4. Queue Workers

Responsibilities:

* Send notification jobs.
* Process media-related jobs.
* Generate thumbnails if needed.
* Handle report/moderation side effects.
* Send email/browser push.
* Cleanup expired records.
* Execute scheduled background tasks.

## 5.5. Object Storage

Responsibilities:

* Store verification evidence.
* Store avatars.
* Store post media.
* Store message attachments.
* Store community resources.

Storage rules:

```txt
no raw public path for private files
protected preview route
permission check before file access
virus/mime validation extension future
```

## 5.6. Admin Console

Admin console is part of Laravel app but logically separated.

Responsibilities:

* Verification queue.
* User management.
* Report queue.
* Moderation.
* Community management.
* Mentor access management.
* Role/permission management.
* Audit log.
* System announcements.

---

# 6. Application Layering

## 6.1. Layer Overview

Recommended layers:

```txt
Presentation Layer
Application Layer
Domain Layer
Infrastructure Layer
Persistence Layer
```

## 6.2. Presentation Layer

Includes:

```txt
Blade views
Livewire components
Alpine components
Form components
Admin pages
PWA shell
```

Responsibilities:

* Render UI.
* Collect user input.
* Show validation errors.
* Show loading/empty/error/permission states.
* Call application actions.
* Never contain complex business rules.

## 6.3. Application Layer

Includes:

```txt
Actions
Services
Use Case classes
Form Requests
DTOs
Jobs
Commands
```

Responsibilities:

* Orchestrate business workflows.
* Validate use case input.
* Call policies.
* Update domain entities.
* Dispatch events/jobs.
* Return application result.

Example actions:

```txt
SubmitVerificationRequest
ApproveVerificationRequest
CompleteProfileSetup
CreatePost
SendGreeting
AcceptGreeting
SendMessage
CreateMentorRequest
ReviewReport
SuspendCommunity
GrantScopedPermission
```

## 6.4. Domain Layer

Includes domain concepts:

```txt
Account
Verification
Profile
Post
Comment
Connection
Conversation
Message
Community
Mentor
Report
ModerationCase
Notification
PermissionGrant
AuditLog
```

Responsibilities:

* Represent business state.
* Define state transitions.
* Protect invariants.
* Encapsulate domain decisions.

## 6.5. Infrastructure Layer

Includes:

```txt
Realtime broadcasting
File storage
Email notification
Browser push
Queue
Scheduler
External auth provider
Logging
Cache
```

Responsibilities:

* Talk to external systems.
* Implement adapters.
* Keep external concerns outside domain logic.

## 6.6. Persistence Layer

Includes:

```txt
Eloquent models
Repositories if needed
Migrations
Query builders
Database indexes
Soft deletes
Audit tables
```

Responsibilities:

* Store and query data.
* Enforce DB constraints.
* Support indexing for feed/search/admin.
* Preserve auditability.

---

# 7. Core Modules

## 7.1. Identity & Authentication Module

Responsibilities:

* Login/logout.
* Password reset.
* HCMUE email domain enforcement.
* Microsoft/Azure edu login direction.
* Account gate redirection.
* Account status checks.

Key states:

```txt
guest
registered
not_verified
verified_profile_incomplete
active
restricted
suspended
banned
```

## 7.2. Verification Module

Responsibilities:

* Collect verification information.
* Enforce MSSV uniqueness.
* Store evidence.
* Admin review.
* Approve/reject/need more information.
* Mark conflict.
* Suspend suspicious.
* Audit all actions.

## 7.3. Profile Module

Responsibilities:

* Profile setup.
* Avatar.
* Public profile.
* Role-specific fields.
* Privacy controls.
* Discovery visibility.

## 7.4. Feed Module

Responsibilities:

* Create posts.
* Render feed.
* Media in posts.
* Comments.
* Visibility.
* Moderation placeholders.
* Admin/system announcements.

## 7.5. Discovery & Connection Module

Responsibilities:

* Recommend UEers.
* Send greeting.
* Accept/decline greeting.
* Create connection.
* Create conversation after accepted connection.
* Hide blocked users.

## 7.6. Messaging Module

Responsibilities:

* Conversations.
* Direct messages.
* Message requests.
* Attachments.
* Edit/delete message.
* Read receipts.
* Typing indicator.
* Block/report message.
* Realtime broadcasting.

## 7.7. Notification Module

Responsibilities:

* In-app notification.
* Browser push notification.
* Notification retention.
* Mark as read.
* Privacy-safe preview.

## 7.8. Mentor Module

Responsibilities:

* Mentor access request.
* Admin approve/grant mentor role.
* Mentor profile.
* Availability.
* Mentor request from student.
* Mentor accept/decline/ask more info.
* Conversation after accepted request.
* Scheduling foundation.

## 7.9. Community / Club Module

Responsibilities:

* Community listing.
* Club/community detail.
* Join request.
* Membership.
* Owner/manager scoped permissions.
* Community posts.
* Community chat.
* Resources.
* Suspended/archived states.

## 7.10. Career Pathway Module

Responsibilities:

* Curated pathways.
* Faculty/program mapping.
* Mentor/alumni insights.
* Resource linking.
* Save pathway.
* Search/filter by major/topic/year.

## 7.11. Safety Reporting Module

Responsibilities:

* Report target content.
* Prevent duplicate pending report.
* Optional auto-block.
* Create moderation case.
* Support multiple target types.

## 7.12. Moderation Module

Responsibilities:

* Report queue.
* Priority.
* Auto-hide.
* Moderator actions.
* Warning/suspend/ban.
* Appeal.
* Placeholder rendering.
* Audit reason.

## 7.13. Admin Operations Module

Responsibilities:

* Dashboard.
* User management.
* Verification queue.
* Report/moderation queue.
* Community management.
* Mentor access.
* Role/permission.
* Audit log.
* System announcements.

## 7.14. Analytics Event Module

Responsibilities:

* Track basic product events.
* Store structured event schema.
* Support future dashboard.
* Respect privacy.
* Avoid sensitive payload.

---

# 8. Authentication & Account Gate

## 8.1. Auth Stack Direction

Recommended:

```txt
Laravel Breeze / Fortify as auth foundation
Laravel Policies / Gates for authorization
Custom account gate middleware
Microsoft Azure / Outlook edu login direction
```

## 8.2. Email Rule

Only allow:

```txt
hcmue.edu.vn
```

## 8.3. Account Gate Flow

After login:

```txt
banned/suspended
→ account restricted screen

not verified
→ verification status / verification flow

verified but profile incomplete
→ onboarding / profile setup

verified and ready
→ /app/home

admin
→ /admin only when accessing admin route and has permission
```

## 8.4. Session Features

MVP:

```txt
email + password
remember me
password minimum 8 characters
no 2FA yet
no login rate limit yet
```

Future:

```txt
2FA
login rate limiting
device/session management
suspicious login detection
```

---

# 9. Authorization Model

## 9.1. Authorization Layers

UEConnect uses multiple authorization layers:

```txt
Route middleware
Policy / Gate
Scoped permission grant
Resource ownership
Business state check
Admin role check
```

## 9.2. Role-Based Access

Base roles:

```txt
student
alumni
academic_advisor
mentor
club_owner
club_manager
moderator
admin
super_admin
```

## 9.3. Scoped Permissions

Some permissions require scope.

Example:

```txt
manage_community_posts scoped by community_id
manage_community_members scoped by community_id
review_verification global
moderate_reports global or category-scoped
```

## 9.4. Permission Storage Direction

Recommended:

```txt
Use Laravel Policies/Gates for enforcement.
Use Spatie Laravel Permission for simple global roles/permissions if helpful.
Use custom permission_grants table for scoped permissions.
```

Custom scoped permission is required because:

```txt
club manager permission depends on community_id
owner permission depends on community_id
moderator permission may depend on scope
```

---

# 10. Data Architecture Principles

## 10.1. Relational-first

UEConnect data is highly relational:

```txt
users
profiles
roles
permissions
verification requests
posts
comments
connections
conversations
messages
communities
memberships
mentor requests
reports
moderation cases
notifications
audit logs
```

Therefore architecture prefers relational DB.

## 10.2. Soft Delete Policy

Use soft delete for user-generated and moderation-sensitive data:

```txt
posts
comments
messages
communities
resources
reports
moderation cases
profile records if needed
```

Do not soft delete everything blindly.

Some records should be immutable or append-only:

```txt
audit logs
permission grant history
verification action history
moderation action history
analytics events
```

## 10.3. Enum Strategy

Use string enums for business state.

Examples:

```txt
verification_status
account_status
post_status
message_status
connection_status
mentor_request_status
community_status
report_status
moderation_case_status
notification_type
```

Reason:

```txt
readable in DB
stable for logs
easier debugging
clearer audit trail
```

## 10.4. Auditability

Audit required for:

```txt
verification approve/reject/need more info
account suspend/ban
permission grant/revoke
community suspend/archive
moderation action
mentor access approve/reject/grant
admin profile edits
```

Audit log must capture:

```txt
actor_id
action
target_type
target_id
before_snapshot optional
after_snapshot optional
reason
metadata
created_at
ip/user_agent optional
```

---

# 11. Realtime Architecture

## 11.1. Realtime Decision

Recommended for MVP:

```txt
Laravel Reverb + Laravel Echo
```

Reason:

* Laravel-native.
* Good fit with Laravel broadcasting.
* Easier local development.
* Lower dependency on paid external realtime provider.
* Suitable for campus-scale MVP.
* Can migrate to Pusher/Ably later if needed.

## 11.2. Realtime Use Cases

Realtime required for:

```txt
direct message received
community chat message
typing indicator
read receipt
notification received
conversation update
```

Realtime not required for:

```txt
feed ranking
verification review
admin dashboard widgets
career pathway content
profile static data
```

## 11.3. Realtime Rule

```txt
WebSocket event is not source of truth.
Client must be able to refresh from database.
```

## 11.4. Channel Authorization

Use private channels for:

```txt
user notifications
conversation messages
community chat
admin queue updates
```

Every channel must enforce:

```txt
authenticated user
resource membership
block status
permission check
account status
```

---

# 12. Notification Architecture

## 12.1. Notification Types

P0 notification types:

```txt
verification approved
verification rejected
verification need more info
greeting received
greeting accepted
message received
mentor request update
moderation action
```

## 12.2. Channels

MVP:

```txt
in-app notification
browser push notification
```

Not MVP:

```txt
SMS
mobile native push
email for every event
```

## 12.3. Notification Storage

Notifications stored in DB.

Fields:

```txt
id
user_id
type
title
body
preview
target_type
target_id
read_at
expires_at
created_at
```

Retention:

```txt
7 days for normal notifications
longer retention possible for moderation/account-critical records via audit logs
```

## 12.4. Privacy Rule

Notification preview must not include:

```txt
full private message if sensitive
verification evidence
report description
admin internal note
private profile field
```

---

# 13. Media & File Architecture

## 13.1. File Categories

```txt
avatar
verification_evidence
post_image
message_attachment
community_resource
admin_export future
```

## 13.2. Storage Visibility

| File Type             | Visibility                  |
| --------------------- | --------------------------- |
| Avatar                | public or controlled public |
| Post image            | public to allowed viewers   |
| Message attachment    | private                     |
| Verification evidence | private                     |
| Community resource    | community/member controlled |
| Admin export          | private                     |

## 13.3. Upload Validation

Evidence MVP:

```txt
max 3 files
max 5MB each
jpg/jpeg/png/pdf/webp
link allowed
note per file
```

## 13.4. Access Pattern

Private file preview must use:

```txt
protected route
policy check
signed temporary URL optional
stream response
```

Never expose raw storage path.

---

# 14. Feed Architecture

## 14.1. Feed Source

Feed can include:

```txt
posts from connected users
posts from joined communities
admin/system announcements
recommended campus content
```

## 14.2. MVP Ranking

MVP can start with simple ranking:

```txt
recent posts
connection/community relevance
official announcements boost
moderation-safe content only
```

Future ranking:

```txt
engagement score
interest matching
mentor/career relevance
freshness decay
community activity
```

## 14.3. Feed Read Model

For MVP, query-based feed is acceptable.

Future optimization:

```txt
feed fanout table
cached feed
materialized ranking table
```

## 14.4. Moderation Filter

Feed must exclude or placeholder:

```txt
hidden_by_moderation
removed
blocked_user_content
private_unavailable_content
suspended_community_content
```

---

# 15. Search Architecture

## 15.1. Search Scope

Search across:

```txt
users / profiles
posts
communities
mentors
career pathways
resources
```

## 15.2. MVP Search

MVP can use SQL Server full-text / indexed LIKE strategy depending setup.

Must enforce:

```txt
privacy
block rules
moderation status
account status
community membership
permission
```

## 15.3. Future Search

Future:

```txt
Meilisearch
Typesense
OpenSearch
Elasticsearch
semantic search for career/resource
```

Do not add search engine until product needs justify it. Search engines are useful, but also another beast to feed.

---

# 16. Admin & Moderation Architecture

## 16.1. Admin Safety Rule

Every important admin action must be:

```txt
authorized
validated
reason-required
audited
state-safe
conflict-aware
```

## 16.2. Moderation Case Flow

```txt
report created
→ moderation case opened
→ priority assigned
→ optional auto-hide
→ moderator reviews
→ action taken
→ audit log written
→ target notified if applicable
→ appeal possible
```

## 16.3. Admin Conflict Handling

Use optimistic locking or stale state check for:

```txt
verification review
moderation case action
permission grant
community suspension
mentor access approval
```

If stale:

```txt
show conflict message
refresh record
prevent duplicate action
```

---

# 17. Event & Queue Architecture

## 17.1. Domain Events

Use internal events for:

```txt
VerificationApproved
VerificationRejected
GreetingSent
GreetingAccepted
MessageSent
MentorRequestAccepted
ReportCreated
ModerationActionTaken
CommunitySuspended
PermissionGranted
```

## 17.2. Jobs

Use queued jobs for:

```txt
SendInAppNotification
SendBrowserPushNotification
ProcessUploadedMedia
CleanupExpiredNotifications
ExpireOldVerificationRequests
GenerateAnalyticsEvent
```

## 17.3. Event Rule

Domain action should complete DB transaction first where needed.

Then dispatch after commit for side effects:

```txt
notifications
broadcasts
emails
analytics
```

---

# 18. Analytics Architecture

## 18.1. MVP Analytics

Track basic events in DB.

Examples:

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

## 18.2. Event Schema

Basic event fields:

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

## 18.3. Privacy Rule

Do not store sensitive payload:

```txt
message body
report description
verification evidence content
private notes
password/token
```

---

# 19. Security Principles

## 19.1. Core Security Requirements

```txt
auth required for app routes
HCMUE email/domain enforcement
CSRF protection
policy checks for every resource
private file access protected
rate limit future for sensitive flows
no raw exception output
audit admin actions
validate upload mime/size/type
prevent duplicate pending reports
prevent blocked users from interacting
```

## 19.2. Trust Boundary

Untrusted input:

```txt
all user form input
uploaded files
message content
post content
comment content
report content
external auth payload
browser push subscription
```

Must validate and sanitize.

## 19.3. XSS Protection

All user-generated content must be:

```txt
escaped by default
sanitized if rich text future
never rendered as raw HTML unless explicitly trusted
```

## 19.4. Authorization Rule

Do not trust frontend visibility.

```txt
UI hiding button != permission
Backend policy decides
```

---

# 20. Deployment Architecture Direction

## 20.1. MVP Deployment Components

Minimum deployment:

```txt
Web app server
SQL Server database
Queue worker
Scheduler
Realtime server
Object storage
Cache
```

## 20.2. Process Separation

Recommended processes:

```txt
php-fpm / web process
queue worker process
scheduler process
reverb websocket process
```

## 20.3. Environment Separation

```txt
local
staging
production
```

## 20.4. Config Management

Use `.env` for:

```txt
database
mail
storage
broadcasting
queue
cache
azure auth
browser push
app url
```

Never commit secrets.

Thật kỳ lạ là vẫn phải nhắc điều này vào năm 2026, nhưng lịch sử GitHub chứng minh con người thích công khai API key như một nghi lễ hiến tế.

---

# 21. Observability

## 21.1. Logging

Log:

```txt
auth failures suspicious
verification admin actions
moderation actions
permission changes
queue failures
broadcast failures
file upload failures
system errors
```

Do not log:

```txt
passwords
tokens
private message body
verification evidence content
sensitive report details
```

## 21.2. Monitoring

MVP should monitor:

```txt
error rate
queue failures
database connection
realtime server status
storage errors
notification job failures
slow queries
```

## 21.3. Audit vs Log

```txt
Audit log = product/legal/admin action history
System log = technical debugging
```

Do not mix them.

---

# 22. Performance Principles

## 22.1. Performance Targets

```txt
fast app shell
paginated feeds
cursor pagination where useful
lazy load media
optimize DB indexes
avoid N+1 queries
cache stable reference data
queue heavy side effects
```

## 22.2. Pagination

Use pagination/cursor for:

```txt
feed
comments
messages
notifications
reports
admin tables
community resources
search results
```

## 22.3. Caching

Cache candidates:

```txt
faculty/program reference data
career pathway published content
community counts with care
permission map with invalidation
static config
```

Do not cache sensitive permission decisions carelessly.

---

# 23. Architecture Constraints

## 23.1. Must Have

```txt
Laravel backend
HCMUE-only identity direction
SQL Server relational database
PWA frontend
realtime messaging
queue workers
private file storage access
scoped permission model
audit log
moderation architecture
```

## 23.2. Must Not

```txt
Do not build microservices for MVP.
Do not make WebSocket source of truth.
Do not expose private files publicly.
Do not rely only on frontend permission.
Do not store sensitive content in analytics events.
Do not use random OAuth provider assumptions for HCMUE email.
Do not skip audit for admin actions.
```

---

# 24. Architecture Quality Attributes

| Attribute            | Architecture Support                                         |
| -------------------- | ------------------------------------------------------------ |
| Security             | Auth, policy, private file routes, audit                     |
| Privacy              | Profile visibility, notification preview limits, block rules |
| Reliability          | Queue, retry, DB source of truth                             |
| Maintainability      | Modular monolith, actions/services, clear modules            |
| Scalability          | Queue, pagination, future read models                        |
| Accessibility        | Server-rendered UI, design system rules                      |
| Performance          | indexes, pagination, lazy media, cache                       |
| Auditability         | audit log, immutable histories                               |
| Moderation readiness | report queue, state machine, placeholders                    |
| Realtime readiness   | Reverb/Echo, private channels                                |

---

# 25. File Responsibility Map

| File                         | Responsibility                             |
| ---------------------------- | ------------------------------------------ |
| `architecture-overview.md`   | High-level system architecture             |
| `system-context.md`          | Actors, external systems, context boundary |
| `container-diagram.md`       | Runtime containers and relationships       |
| `component-diagram.md`       | Internal modules/components                |
| `deployment-architecture.md` | Deployment topology and processes          |
| `sequence-diagrams.md`       | Key flow sequences                         |
| `techstack.md`               | Final technology stack                     |
| `adr-001-tech-stack.md`      | Why this stack                             |
| `adr-002-auth-strategy.md`   | Auth/login/identity decision               |
| `adr-003-database-choice.md` | DB decision                                |

---

# 26. Architecture QA Checklist

Before approving architecture:

```txt
[ ] Architecture supports all P0 features.
[ ] Auth gate flow is clear.
[ ] Verification flow is auditable.
[ ] Scoped permission model is defined.
[ ] Realtime is DB-backed and not source of truth.
[ ] Private file access is protected.
[ ] Notification privacy is addressed.
[ ] Moderation/reporting flow is supported.
[ ] Admin actions require reason and audit.
[ ] Queue jobs are identified.
[ ] Deployment processes are separated.
[ ] Analytics avoids sensitive payload.
[ ] Search respects privacy and moderation.
[ ] Soft delete and audit rules are clear.
[ ] Architecture maps to state machine docs.
```

---

# 27. Final Rule

Architecture của UEConnect phải phục vụ trust.

Trust ở đây không chỉ là “có login”.

Trust nghĩa là:

```txt
đúng người được vào
đúng người được thấy dữ liệu
đúng người được thao tác
mọi action quan trọng có dấu vết
realtime không làm sai state
file riêng tư không bị lộ
admin không thể xử lý tùy tiện
moderation có quy trình
người dùng hiểu chuyện gì đang xảy ra
```

Nếu architecture chỉ làm được CRUD nhanh nhưng không làm được trust, thì nó không phù hợp với UEConnect.

Nó chỉ là một cái database có giao diện.

```

Tiếp theo nên làm **`techstack.md`**, rồi mới viết 3 ADR để “khóa quyết định”. Viết ADR trước khi overview cũng được, nhưng hơi giống ký biên bản họp trước khi biết mình họp chuyện gì.
```
