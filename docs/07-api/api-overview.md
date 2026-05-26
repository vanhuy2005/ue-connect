---
title: "API Overview"
module: "07-api"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Backend / API / Frontend"
depends_on:
  - "../02-requirements/requirements-overview.md"
  - "../02-requirements/functional-requirements.md"
  - "../02-requirements/non-functional-requirements.md"
  - "../02-requirements/role-permission-matrix.md"
  - "../03-product/product-overview.md"
  - "../03-product/feature-list.md"
  - "../03-product/feature-priority.md"
  - "../03-product/sitemap.md"
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
  - "../05-system-architecture/architecture-overview.md"
  - "../05-system-architecture/techstack.md"
  - "../06-database/database-overview.md"
  - "../06-database/schema.md"
  - "../06-database/table-specifications.md"
related:
  - "api.yaml"
  - "authentication-api.md"
  - "user-api.md"
  - "mentorship-api.md"
  - "community-api.md"
  - "event-api.md"
  - "job-api.md"
  - "error-codes.md"
---

# API Overview

## 1. Purpose

File này định nghĩa chuẩn API tổng quan cho UEConnect.

Mục tiêu:

- Khóa cách thiết kế endpoint.
- Khóa format request/response.
- Khóa error format.
- Khóa auth/session/API access rule.
- Khóa pagination/filter/sort/search convention.
- Khóa upload/media API rule.
- Khóa realtime event API boundary.
- Khóa admin API rule.
- Làm nền cho `api.yaml` và các file API detail.

UEConnect dùng Laravel + Blade + Livewire là chính, nên không phải mọi interaction đều cần REST API công khai.

API trong UEConnect phục vụ:

```txt
Livewire / internal app actions
AJAX interactions where useful
Realtime client synchronization
PWA notification/browser push
Admin operations
Future mobile/native client
Future integration boundary
````

Không thiết kế API kiểu “vì app nào cũng cần REST cho ngầu”. API không có consumer rõ ràng thì chỉ là thêm bề mặt bug có URL.

---

# 2. API Design Decision

## 2.1. API Style

UEConnect sử dụng:

```txt
REST-like HTTP API
+ Laravel web/session auth
+ JSON response standard
+ Private internal endpoints where needed
+ OpenAPI documentation
+ WebSocket event layer for realtime
```

## 2.2. Primary Rendering Model

Primary UI rendering:

```txt
Blade
Livewire
Alpine.js
```

API không thay thế server-rendered UI trong MVP.

API chủ yếu dùng cho:

```txt
search autocomplete
notification actions
message send/fetch
media upload
admin async actions
browser push subscription
realtime recovery
future mobile client
```

## 2.3. Source of Truth

```txt
Database = source of truth
HTTP API = state mutation/query boundary
Realtime event = delivery/update signal
Frontend state = temporary view
```

Không được để WebSocket event tự tạo state mà không có database record. Đường ống không được tự nhận là chính phủ.

---

# 3. API Scope

## 3.1. In Scope

API docs trong folder này bao phủ:

```txt
authentication
account status
current user
profile
verification
media upload
feed/post/comment
discovery
greeting/connection
messaging
notifications
mentor matching
community/club
career pathway
search/filter
safety reporting
moderation
admin operations
analytics events
browser push subscription
realtime event contracts
error codes
```

## 3.2. Out of Scope for MVP

Không cần API public cho:

```txt
third-party developers
public partner integrations
recruiter portal
payment/billing
native mobile-only endpoints
open data export
external marketplace
```

Future có thể thêm, nhưng MVP không cần biến một social platform trường học thành mini Salesforce. Loài người đã có đủ dashboard rồi.

---

# 4. API Consumer Types

## 4.1. Web App UI

Primary consumer:

```txt
Laravel Blade / Livewire / Alpine
```

Use cases:

```txt
form submit
partial update
search
filters
notifications
messaging
admin queue actions
```

## 4.2. PWA Client

PWA-related consumers:

```txt
service worker
browser push subscription
offline fallback
notification permission flow
```

## 4.3. Realtime Client

Realtime consumer:

```txt
Laravel Echo client
private channels
presence channels where needed
```

## 4.4. Admin Console

Admin API surfaces are used by:

```txt
verification queue
moderation queue
user management
permission management
community management
mentor access management
audit log view
```

## 4.5. Future Mobile Client

API should be consistent enough to support future mobile app, but MVP should not overbuild for it.

---

# 5. API Base Path

## 5.1. Recommended API Prefix

Internal app API:

```txt
/api/v1
```

Examples:

```txt
/api/v1/me
/api/v1/notifications
/api/v1/messages
/api/v1/search
```

Admin API:

```txt
/api/v1/admin
```

Examples:

```txt
/api/v1/admin/verification-requests
/api/v1/admin/moderation-cases
/api/v1/admin/users
```

## 5.2. Web Routes vs API Routes

Use web routes for:

```txt
full page rendering
Livewire pages
auth pages
admin pages
protected file preview pages
```

Use API routes for:

```txt
JSON data
async actions
realtime recovery
browser push subscription
search autocomplete
file upload metadata response
```

## 5.3. Versioning

MVP uses:

```txt
v1
```

Do not version every tiny internal route unless it is documented and expected to be stable.

Public/future client APIs must use explicit version:

```txt
/api/v1/...
```

---

# 6. Authentication & Session

## 6.1. Primary Auth

Primary auth for web/PWA:

```txt
Laravel session authentication
CSRF protection
same-site cookies
```

## 6.2. API Auth for Web App

For same-origin web API calls:

```txt
session cookie
CSRF token
authenticated middleware
```

## 6.3. Future Token Auth

Future mobile/public client can use:

```txt
Laravel Sanctum token auth
```

Not required for MVP unless external/mobile client is built.

## 6.4. Auth Rules

All `/api/v1/app/*` or app-private endpoints require:

```txt
authenticated user
valid account_status
verification/profile gate where applicable
permission/policy check
```

## 6.5. Account Gate API Behavior

If user is authenticated but not allowed into target resource:

| Condition          |  HTTP | Error Code             |
| ------------------ | ----: | ---------------------- |
| Not authenticated  | `401` | `AUTH_UNAUTHENTICATED` |
| Not verified       | `403` | `ACCOUNT_NOT_VERIFIED` |
| Profile incomplete | `403` | `PROFILE_INCOMPLETE`   |
| Suspended          | `403` | `ACCOUNT_SUSPENDED`    |
| Banned             | `403` | `ACCOUNT_BANNED`       |
| Permission denied  | `403` | `PERMISSION_DENIED`    |

---

# 7. Authorization

## 7.1. Authorization Layers

Every sensitive endpoint must enforce:

```txt
route middleware
Laravel policy/gate
scoped permission check if needed
business state validation
privacy/block/moderation rule
```

## 7.2. Frontend Is Not Security

Frontend hiding button is not authorization.

API must reject unauthorized actions even if UI normally hides them.

Vâng, user có thể mở DevTools. Không, điều này không phải hacking thiên tài.

## 7.3. Scoped Permission

Scoped permissions apply to:

```txt
community management
community posts/resources
club manager actions
moderation scope
mentor access admin
verification review
```

Example:

```txt
permission_key = manage_community_members
scope_type = community
scope_id = 123
```

## 7.4. Admin API Rule

Admin endpoints require:

```txt
authenticated
verified if required by policy
admin/moderator role or permission
specific permission for action
audit log for important mutation
reason for sensitive actions
```

---

# 8. Request Format

## 8.1. JSON Request

Default content type:

```txt
application/json
```

Headers:

```http
Accept: application/json
Content-Type: application/json
X-CSRF-TOKEN: <csrf-token>
```

## 8.2. Multipart Request

Use `multipart/form-data` for upload endpoints.

Examples:

```txt
verification evidence upload
avatar upload
post image upload
message attachment upload
community resource upload
```

## 8.3. Naming Convention

Request fields use:

```txt
snake_case
```

Good:

```json
{
  "display_name": "Nguyễn Văn Demo",
  "faculty_id": 1,
  "academic_program_id": 12
}
```

Bad:

```json
{
  "displayName": "Nguyễn Văn Demo"
}
```

Laravel thích snake_case, database thích snake_case, vậy ta đừng bắt frontend sống đời camelCase nếu không cần.

---

# 9. Response Format

## 9.1. Success Response

Standard success response:

```json
{
  "success": true,
  "data": {},
  "message": "Thao tác thành công.",
  "meta": {}
}
```

## 9.2. List Response

Paginated list response:

```json
{
  "success": true,
  "data": [
    {}
  ],
  "meta": {
    "pagination": {
      "page": 1,
      "per_page": 20,
      "total": 125,
      "last_page": 7,
      "has_more": true
    }
  }
}
```

## 9.3. Cursor Response

For feed/messages where cursor is better:

```json
{
  "success": true,
  "data": [
    {}
  ],
  "meta": {
    "cursor": {
      "next": "eyJpZCI6MTIzfQ",
      "previous": null,
      "has_more": true
    }
  }
}
```

## 9.4. Empty Response

Use data empty, not null, for collection:

```json
{
  "success": true,
  "data": [],
  "message": "Chưa có dữ liệu.",
  "meta": {
    "empty_state": {
      "title": "Chưa có thông tin",
      "description": "Nội dung sẽ xuất hiện tại đây khi có dữ liệu mới."
    }
  }
}
```

## 9.5. Mutation Response

For create/update/delete actions:

```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "pending_review"
  },
  "message": "Đã gửi yêu cầu xác thực."
}
```

---

# 10. Error Response Format

## 10.1. Standard Error

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Dữ liệu không hợp lệ.",
    "details": {}
  },
  "meta": {}
}
```

## 10.2. Validation Error

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Vui lòng kiểm tra lại thông tin.",
    "details": {
      "email": [
        "Email phải thuộc tên miền hcmue.edu.vn."
      ],
      "password": [
        "Mật khẩu phải có ít nhất 8 ký tự."
      ]
    }
  }
}
```

## 10.3. Permission Error

```json
{
  "success": false,
  "error": {
    "code": "PERMISSION_DENIED",
    "message": "Bạn không có quyền thực hiện thao tác này.",
    "details": {
      "required_permission": "review_verification"
    }
  }
}
```

## 10.4. Business State Error

```json
{
  "success": false,
  "error": {
    "code": "GREETING_ALREADY_DECLINED",
    "message": "Bạn không thể gửi lại lời chào cho người này.",
    "details": {
      "current_status": "declined"
    }
  }
}
```

## 10.5. Error Code Source

All error codes must be documented in:

```txt
error-codes.md
```

---

# 11. HTTP Status Code Rules

| HTTP Status | Meaning           | Example                          |
| ----------: | ----------------- | -------------------------------- |
|       `200` | Success           | Fetch data, update successful    |
|       `201` | Created           | Created post/message/report      |
|       `202` | Accepted          | Async processing started         |
|       `204` | No content        | Delete success with no body      |
|       `400` | Bad request       | Invalid business request         |
|       `401` | Unauthenticated   | Not logged in                    |
|       `403` | Forbidden         | No permission/account restricted |
|       `404` | Not found         | Resource not found or hidden     |
|       `409` | Conflict          | State conflict/stale action      |
|       `422` | Validation failed | Form validation                  |
|       `423` | Locked            | Resource/account locked          |
|       `429` | Too many requests | Rate limit                       |
|       `500` | Server error      | Unexpected server error          |

## 11.1. Hidden Resource Rule

For privacy-sensitive resources, API may return:

```txt
404
```

instead of `403` to avoid revealing existence.

Example:

```txt
private profile
private community
message in conversation user is not part of
verification evidence
```

---

# 12. Pagination, Filtering, Sorting

## 12.1. Page Pagination

Use for admin lists and stable lists:

```txt
?page=1&per_page=20
```

Rules:

```txt
default per_page = 20
max per_page = 100
```

## 12.2. Cursor Pagination

Use for:

```txt
home feed
messages
notifications
comments
community chat
```

Format:

```txt
?cursor=<cursor>&limit=20
```

## 12.3. Filtering

Use bracket convention:

```txt
?filter[status]=pending_review
?filter[role]=student
?filter[faculty_id]=1
```

## 12.4. Sorting

Use:

```txt
?sort=-created_at
?sort=display_name
```

Rules:

```txt
- prefix means descending
allowed sort fields must be whitelisted
```

## 12.5. Search Query

Use:

```txt
?q=keyword
```

Example:

```txt
/api/v1/search?q=mentor&filter[type]=users
```

---

# 13. API Naming Convention

## 13.1. Resource Names

Use plural resource names:

```txt
/users
/profiles
/posts
/comments
/messages
/communities
/mentor-requests
/reports
```

## 13.2. Nested Resources

Use nesting when resource is clearly owned by parent:

```txt
/communities/{community_id}/members
/posts/{post_id}/comments
/conversations/{conversation_id}/messages
```

Avoid excessive nesting:

```txt
/users/{id}/communities/{id}/channels/{id}/messages/{id}
```

Đường dẫn API không nên như địa chỉ nhà ở khu quy hoạch thất bại.

## 13.3. Action Endpoints

Use action endpoints only for domain actions that are not simple CRUD.

Examples:

```txt
/greetings/{id}/accept
/greetings/{id}/decline
/verification-requests/{id}/submit
/admin/verification-requests/{id}/approve
/admin/moderation-cases/{id}/resolve
/mentor-requests/{id}/ask-more-info
```

## 13.4. Method Rules

| Method   | Usage                       |
| -------- | --------------------------- |
| `GET`    | Read                        |
| `POST`   | Create/action               |
| `PUT`    | Full update                 |
| `PATCH`  | Partial update/state change |
| `DELETE` | Delete/archive/remove       |

Use `POST` for actions with side effects and reasons:

```txt
approve
reject
accept
decline
block
report
```

---

# 14. Resource Representation

## 14.1. User Summary

```json
{
  "id": 123,
  "display_name": "Nguyễn Văn Demo",
  "avatar_url": "/media/avatars/demo.png",
  "role_type": "student",
  "verification_badge": true,
  "headline": "Sinh viên Công nghệ thông tin"
}
```

## 14.2. Profile Detail

```json
{
  "id": 123,
  "display_name": "Nguyễn Văn Demo",
  "role_type": "student",
  "avatar_url": "/media/avatars/demo.png",
  "bio": "Yêu thích backend và thiết kế hệ thống.",
  "faculty": {
    "id": 1,
    "name": "Khoa Công nghệ Thông tin"
  },
  "academic_program": {
    "id": 10,
    "name": "Công nghệ thông tin"
  },
  "privacy_context": {
    "viewer_relationship": "hcmue_user",
    "limited": false
  }
}
```

## 14.3. Post Summary

```json
{
  "id": 456,
  "author": {},
  "body": "Nội dung bài viết...",
  "visibility": "public_hcmue",
  "status": "published",
  "media": [],
  "comment_count": 3,
  "created_at": "2026-05-26T12:00:00Z"
}
```

## 14.4. Message

```json
{
  "id": 789,
  "conversation_id": 10,
  "sender": {},
  "body": "Chào bạn, mình muốn hỏi thêm về CLB.",
  "message_type": "text",
  "status": "sent",
  "attachments": [],
  "created_at": "2026-05-26T12:00:00Z",
  "edited_at": null
}
```

---

# 15. Privacy & Data Redaction

## 15.1. API Must Redact By Context

API response must depend on viewer context.

Factors:

```txt
viewer role
viewer verification status
connection status
community membership
profile privacy setting
block relationship
moderation state
permission grants
```

## 15.2. Redacted Profile Example

```json
{
  "id": 123,
  "display_name": "Nguyễn Văn Demo",
  "avatar_url": "/media/avatars/demo.png",
  "role_type": "student",
  "bio": null,
  "faculty": null,
  "privacy_context": {
    "viewer_relationship": "not_connected",
    "limited": true,
    "reason": "profile_privacy"
  }
}
```

## 15.3. Do Not Expose

Never expose through API:

```txt
password
remember_token
verification evidence raw path
browser push auth token to other users
report description to reported user
admin internal note
private profile fields
message content outside conversation
audit snapshots to unauthorized admin
```

---

# 16. Upload API Rules

## 16.1. Upload Categories

Upload APIs include:

```txt
avatar
verification evidence
post image
message attachment
community resource
report attachment future
```

## 16.2. Upload Response

```json
{
  "success": true,
  "data": {
    "media_file_id": 123,
    "file_category": "verification_evidence",
    "original_name": "student-card.pdf",
    "mime_type": "application/pdf",
    "size_bytes": 320000,
    "preview_url": "/app/media/123/preview"
  },
  "message": "Tải tệp lên thành công."
}
```

## 16.3. Private Upload Rule

Private file `preview_url` must be protected.

Do not return:

```txt
raw storage path
public S3 URL for private files
```

## 16.4. Evidence Upload Rules

```txt
max files = 3
max size = 5MB each
allowed = jpg/jpeg/png/pdf/webp
note per evidence required
```

## 16.5. Upload Errors

Examples:

```txt
FILE_TOO_LARGE
FILE_TYPE_NOT_ALLOWED
UPLOAD_LIMIT_EXCEEDED
PRIVATE_FILE_ACCESS_DENIED
```

---

# 17. Realtime API Boundary

## 17.1. HTTP Creates State

Use HTTP/API to create durable state:

```txt
send message
accept greeting
mark notification read
submit report
create post
```

## 17.2. WebSocket Broadcasts State Change

Use realtime event to notify clients:

```txt
MessageSent
MessageEdited
MessageDeleted
TypingStarted
NotificationCreated
GreetingReceived
GreetingAccepted
ConversationUpdated
CommunityMessageSent
```

## 17.3. Realtime Recovery

Client must be able to refetch:

```txt
GET /api/v1/conversations/{id}/messages?cursor=...
GET /api/v1/notifications
GET /api/v1/conversations
```

## 17.4. Channel Auth

Private channel authorization must check:

```txt
authenticated user
conversation participant
community member
not blocked
account active
permission if admin channel
```

---

# 18. Admin API Rules

## 18.1. Admin Endpoint Prefix

```txt
/api/v1/admin
```

## 18.2. Reason Required

Admin mutation endpoints must require `reason` when action affects trust/safety.

Examples:

```txt
approve verification
reject verification
need more information
suspend user
ban user
hide content
delete content
grant permission
revoke permission
suspend community
```

## 18.3. Admin Action Response

```json
{
  "success": true,
  "data": {
    "target_type": "verification_request",
    "target_id": 123,
    "new_status": "approved",
    "audit_log_id": 999
  },
  "message": "Đã duyệt yêu cầu xác thực."
}
```

## 18.4. Stale Conflict

If record changed since admin loaded it:

```json
{
  "success": false,
  "error": {
    "code": "STALE_RESOURCE_CONFLICT",
    "message": "Dữ liệu đã thay đổi. Vui lòng tải lại trước khi xử lý.",
    "details": {
      "current_status": "approved"
    }
  }
}
```

HTTP:

```txt
409 Conflict
```

---

# 19. API Module Ownership

| API File                | Responsibility                            |
| ----------------------- | ----------------------------------------- |
| `api-overview.md`       | Global API design rules                   |
| `api.yaml`              | OpenAPI contract                          |
| `authentication-api.md` | Login/register/session/account gate       |
| `user-api.md`           | Me/profile/settings/privacy/user search   |
| `mentorship-api.md`     | Mentor profile/request/access             |
| `community-api.md`      | Communities, members, resources, channels |
| `event-api.md`          | Realtime/domain event contract            |
| `job-api.md`            | Async job/status APIs                     |
| `error-codes.md`        | Error code catalog                        |

## 19.1. Missing API Files To Add Later

Given UEConnect scope, consider adding:

```txt
feed-api.md
messaging-api.md
notification-api.md
verification-api.md
moderation-api.md
admin-api.md
media-api.md
search-api.md
```

Hiện folder của bạn chưa có mấy file này. Không phải thảm họa, nhưng nếu không thêm thì `user-api.md` sẽ bị nhồi như vali du lịch của người không biết chọn đồ.

---

# 20. Initial Endpoint Map

## 20.1. Authentication

```txt
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/account-status
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

## 20.2. Current User

```txt
GET    /api/v1/me
GET    /api/v1/me/profile
PATCH  /api/v1/me/profile
PATCH  /api/v1/me/privacy
GET    /api/v1/me/notifications
```

## 20.3. Verification

```txt
GET    /api/v1/verification/current
POST   /api/v1/verification/requests
PATCH  /api/v1/verification/requests/{id}
POST   /api/v1/verification/requests/{id}/submit
POST   /api/v1/verification/requests/{id}/evidences
DELETE /api/v1/verification/evidences/{id}
```

## 20.4. Feed

```txt
GET    /api/v1/feed
POST   /api/v1/posts
GET    /api/v1/posts/{id}
PATCH  /api/v1/posts/{id}
DELETE /api/v1/posts/{id}
GET    /api/v1/posts/{id}/comments
POST   /api/v1/posts/{id}/comments
```

## 20.5. Discovery / Connection

```txt
GET    /api/v1/discovery
POST   /api/v1/discovery/{user_id}/pass
POST   /api/v1/greetings
GET    /api/v1/greetings
POST   /api/v1/greetings/{id}/accept
POST   /api/v1/greetings/{id}/decline
GET    /api/v1/connections
DELETE /api/v1/connections/{id}
```

## 20.6. Messaging

```txt
GET    /api/v1/conversations
GET    /api/v1/conversations/{id}
GET    /api/v1/conversations/{id}/messages
POST   /api/v1/conversations/{id}/messages
PATCH  /api/v1/messages/{id}
DELETE /api/v1/messages/{id}
POST   /api/v1/messages/{id}/read
POST   /api/v1/messages/{id}/report
```

## 20.7. Notifications

```txt
GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
POST   /api/v1/notifications/read-all
POST   /api/v1/browser-push/subscriptions
DELETE /api/v1/browser-push/subscriptions/{id}
```

## 20.8. Mentor

```txt
GET    /api/v1/mentors
GET    /api/v1/mentors/{id}
POST   /api/v1/mentors/{id}/requests
GET    /api/v1/mentor-requests
GET    /api/v1/mentor-requests/{id}
POST   /api/v1/mentor-requests/{id}/accept
POST   /api/v1/mentor-requests/{id}/decline
POST   /api/v1/mentor-requests/{id}/ask-more-info
```

## 20.9. Community

```txt
GET    /api/v1/communities
POST   /api/v1/communities
GET    /api/v1/communities/{id}
POST   /api/v1/communities/{id}/join-requests
GET    /api/v1/communities/{id}/members
GET    /api/v1/communities/{id}/resources
POST   /api/v1/communities/{id}/resources
GET    /api/v1/communities/{id}/channels
GET    /api/v1/communities/{id}/channels/{channel_id}
```

## 20.10. Safety / Reporting

```txt
POST   /api/v1/reports
GET    /api/v1/reports/{id}
POST   /api/v1/users/{id}/block
DELETE /api/v1/users/{id}/block
```

## 20.11. Admin

```txt
GET    /api/v1/admin/dashboard
GET    /api/v1/admin/users
GET    /api/v1/admin/verification-requests
POST   /api/v1/admin/verification-requests/{id}/approve
POST   /api/v1/admin/verification-requests/{id}/reject
POST   /api/v1/admin/verification-requests/{id}/need-more-info
GET    /api/v1/admin/reports
GET    /api/v1/admin/moderation-cases
POST   /api/v1/admin/moderation-cases/{id}/actions
GET    /api/v1/admin/audit-logs
```

---

# 21. API Documentation Standard

Each API detail file must include:

```txt
1. Purpose
2. Auth requirements
3. Permission requirements
4. Endpoint list
5. Request examples
6. Response examples
7. Error examples
8. State transitions
9. Privacy rules
10. Audit rules
11. Rate limit rules if any
12. QA checklist
```

## 21.1. OpenAPI Contract

`api.yaml` should include:

```txt
servers
security schemes
schemas
error models
pagination models
all stable endpoints
```

Do not document unstable internal Livewire endpoints in OpenAPI unless they are meant as API contract.

---

# 22. Rate Limiting

## 22.1. MVP Decision

Login rate limit is not required yet by product decision.

However, API should be designed to support rate limiting later.

## 22.2. Recommended Rate Limit Future

Sensitive endpoints:

```txt
auth login
forgot password
verification submit
evidence upload
greeting send
message send
report submit
browser push subscription
```

Example future error:

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMITED",
    "message": "Bạn thao tác quá nhanh. Vui lòng thử lại sau.",
    "details": {
      "retry_after_seconds": 60
    }
  }
}
```

---

# 23. Security Rules

## 23.1. Required

```txt
CSRF for session-auth requests
policy check for every resource
private file route for protected media
input validation through Form Requests
output redaction by viewer context
no raw exception in production
no sensitive data in logs
no sensitive data in analytics
```

## 23.2. XSS

User content must be escaped by default.

If rich text is introduced later:

```txt
sanitize server-side
restrict allowed tags
store sanitized version or render safely
```

## 23.3. File Upload

Validate:

```txt
mime type
extension
size
category
owner
permission
```

Future:

```txt
virus scan
image dimension validation
OCR safety scan
```

## 23.4. Mass Assignment

Laravel models must define:

```txt
fillable
guarded
casts
```

Do not allow client to set:

```txt
account_status
role
permission
reviewed_by
approved_by
audit fields
moderation status
```

---

# 24. API Testing Strategy

## 24.1. Required API Tests

Feature tests must cover:

```txt
unauthenticated request returns 401
unverified user blocked where needed
profile incomplete user blocked where needed
permission denied returns 403
validation error returns 422
not found/private returns 404
state conflict returns 409
successful mutation writes audit where needed
successful mutation creates notification where needed
private data redacted
blocked user cannot interact
```

## 24.2. Critical Flow Tests

```txt
submit verification
admin approve verification
send greeting
accept greeting creates connection/conversation
send message
mark message read
submit report
moderation hide content
grant scoped permission
join community
mentor request accepted creates conversation
```

---

# 25. API QA Checklist

Before approving any endpoint:

```txt
[ ] Endpoint has clear consumer.
[ ] Endpoint path follows convention.
[ ] HTTP method is correct.
[ ] Auth requirement is defined.
[ ] Permission requirement is defined.
[ ] Request validation is defined.
[ ] Response format follows standard.
[ ] Error format follows standard.
[ ] Status codes are correct.
[ ] Privacy/redaction is handled.
[ ] Block rule is handled if user-user interaction.
[ ] Moderation state is handled if content endpoint.
[ ] Audit is written for sensitive action.
[ ] Notification side effect is defined if needed.
[ ] Realtime event side effect is defined if needed.
[ ] Pagination/filter/sort are defined for list.
[ ] Tests are planned.
```

---

# 26. Final Rule

API của UEConnect phải nhất quán hơn cảm xúc con người lúc deadline.

Mỗi endpoint phải trả lời rõ:

```txt
Ai được gọi?
Gọi để làm gì?
Input là gì?
Output là gì?
Nếu sai thì lỗi gì?
Có đổi state không?
Có audit không?
Có notification không?
Có broadcast realtime không?
Có lộ dữ liệu riêng tư không?
```

Nếu endpoint không trả lời được các câu đó, nó chưa phải API spec. Nó chỉ là route đang chờ tạo bug.
