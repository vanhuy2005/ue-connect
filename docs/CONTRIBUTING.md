title: "Contributing Guide"
product: "UEConnect"
version: "1.0"
status: "active"
last_updated: "2026-05-26"
owner: "Project Team"
---

# Contributing Guide

## 1. Purpose

Tài liệu này hướng dẫn cách đóng góp code, tài liệu, thiết kế và cấu trúc hệ thống cho UEConnect.

Mục tiêu là giữ dự án:

- Dễ hiểu.
- Dễ review.
- Dễ maintain.
- Không lệch khỏi source of truth.
- Không tạo bug chỉ vì mỗi người code theo một hệ tư tưởng riêng.

---

# 2. Basic Rules

## 2.1. Language Rule

| Area | Language |
|---|---|
| Source code | English |
| Variable / function / class | English |
| Comments in code | English, nếu cần |
| UI copy | Vietnamese |
| Documentation | Vietnamese hoặc English tùy file, nhưng nên thống nhất trong cùng file |
| Commit message | English hoặc Vietnamese đều được, miễn rõ nghĩa |

Ví dụ:

```php
public function submitVerificationRequest(): void
{
    // Validate before submitting request.
}

UI:

Gửi yêu cầu xác thực

Không viết code kiểu:

public function guiYeuCauXacThuc()

Nhìn thân thiện thật, cho tới khi dự án lớn lên và bắt đầu khóc.

3. Branch Naming

Use simple branch names:

feature/messaging
feature/verification-flow
fix/login-account-gate
docs/api-overview
refactor/profile-service

Recommended format:

type/short-description

Types:

feature
fix
docs
refactor
test
chore
design
4. Commit Message

Use clear commit messages.

Good:

feat: add verification request state machine
fix: prevent suspended users from sending messages
docs: update API overview
refactor: extract profile privacy service

Bad:

update
fix bug
done
final
final 2
real final

Nếu commit tên final_final_really_final, repo sẽ tự mất phẩm giá.

5. Development Workflow
5.1. Before Working

Before coding or writing docs:

1. Identify the module.
2. Read related source-of-truth docs.
3. Check MVP scope.
4. Check state machine if the feature has state.
5. Check API and DB docs if affected.
6. Create a small plan.

Important docs:

docs/03-product/feature-priority.md
docs/03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md
docs/05-system-architecture/techstack.md
docs/06-database/schema.md
docs/07-api/api.yaml
docs/12-agent/1. source-truth-map.md
6. Code Contribution Rules
6.1. Laravel

Follow Laravel conventions:

Controllers for HTTP handling
Form Requests for validation
Policies/Gates for authorization
Actions/Services for business workflow
Events for domain events
Jobs for async work
Notifications for notification delivery
Resources/DTOs for API output if needed
6.2. Frontend

Use the agreed frontend stack:

Blade
Livewire
Alpine.js
TailwindCSS
Lucide Icons
Vite
PWA support where needed
6.3. Security

Every sensitive feature must check:

authentication
account status
verification status
profile completion
authorization
privacy
block state
moderation state
audit requirement

Never rely only on frontend hiding buttons. Users have DevTools. Shocking, apparently.

7. Documentation Contribution Rules

Update docs when changing:

feature behavior
API endpoint
database schema
state machine
permission
security rule
UI route/page
notification type
analytics event
deployment behavior

Use:

docs/DOCUMENTATION-STANDARDS.md
docs/12-agent/2. agent-change-protocol.md
8. Pull Request Checklist

Before opening a PR:

[ ] Code follows project conventions.
[ ] Related source-of-truth docs were checked.
[ ] API changes update docs/07-api/api.yaml.
[ ] DB changes update docs/06-database/schema.md.
[ ] State changes update state machine docs.
[ ] UI route/page changes update sitemap/page specs.
[ ] Security/privacy rules are respected.
[ ] Tests were added or planned.
[ ] CHANGELOG.md updated if needed.
9. Review Checklist

Reviewer should check:

[ ] Does this match the feature spec?
[ ] Does this match MVP scope?
[ ] Does this break API contract?
[ ] Does this break DB assumptions?
[ ] Does this bypass authorization?
[ ] Does this expose private data?
[ ] Does this need audit log?
[ ] Does this need notification/realtime event?
[ ] Are docs updated?
10. Testing Rule

For important flows, add tests for:

happy path
validation error
permission denied
wrong state
blocked user
moderated content
privacy redaction
audit side effect
notification side effect

Critical flows:

authentication
verification
profile setup
messaging
greeting/connection
mentor request
community join
reporting
moderation
admin actions
11. What Not To Do

Do not:

hardcode secrets
commit .env
commit real user data
commit real evidence files
skip policy checks
skip validation
use raw private file URLs
change API without updating api.yaml
change database without updating schema docs
invent business rules
12. Final Rule

A contribution is good when it is:

correct
small enough to review
documented
tested or testable
safe
consistent with source of truth