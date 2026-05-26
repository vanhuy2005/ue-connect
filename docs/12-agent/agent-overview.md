---
title: "Agent Overview"
module: "12-agent"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "AI Engineering / Product / Architecture"
depends_on:
  - "1. source-truth-map.md"
  - "2. agent-change-protocol.md"
  - "3. agent-task-checklist.md"
  - "4. rag-knowledge-base.md"
  - "5. prompt-design.md"
  - "../DOCUMENTATION-STANDARDS.md"
  - "../03-product/feature-priority.md"
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
  - "../05-system-architecture/techstack.md"
related:
  - "agent-workflows.md"
  - "ai-safety.md"
---

# Agent Overview

## 1. Purpose

File này định nghĩa vai trò, nguyên tắc và phạm vi sử dụng AI Agent trong dự án UEConnect.

Agent trong UEConnect được dùng để hỗ trợ:

- Phân tích requirement.
- Viết và cập nhật tài liệu.
- Thiết kế feature spec.
- Thiết kế API contract.
- Thiết kế database schema.
- Hỗ trợ viết code.
- Hỗ trợ viết test.
- Review consistency giữa code và docs.
- Tạo checklist triển khai.
- Phát hiện conflict giữa tài liệu.
- Gợi ý cập nhật changelog, ADR, API, DB docs.

Agent không phải người ra quyết định nghiệp vụ cuối cùng.

Agent không được tự bịa business rule.

Agent không được sửa logic quan trọng mà không kiểm tra source of truth.

Nói ngắn gọn: Agent là trợ lý kỹ thuật có kỷ luật, không phải pháp sư đoán ý product owner.

---

# 2. Agent Operating Model

UEConnect dùng mô hình:

```txt
Docs as Source of Truth
+ Retrieval First
+ Agent-assisted Implementation
+ Human-reviewed Business Decisions
+ Documentation Sync
+ CI Validation

Ý nghĩa:

Docs quyết định luật.
Agent phải đọc docs trước khi làm.
Code thay đổi thì docs phải được kiểm tra.
Business rule quan trọng cần human review.
CI kiểm tra tài liệu và contract không bị vỡ.
## 3. Agent Responsibility
### 3.1. Agent Can Do

Agent được phép hỗ trợ:

write documentation
update markdown docs
generate feature specs
generate API specs
generate database plans
generate migration plans
generate seed plans
write implementation plan
write Laravel code drafts
write Blade/Livewire component drafts
write tests
review code against docs
review docs against source-of-truth
summarize technical decisions
detect missing docs
detect inconsistent state enum
detect API/DB mismatch
### 3.2. Agent Must Not Do

Agent không được:

invent business rules
ignore source-of-truth docs
change security behavior silently
change role/permission logic without audit
change database schema without updating docs
change API contract without updating api.yaml
change state machine without updating source-of-truth
expose private data
generate fake real user data
store sensitive data in analytics
bypass verification/account gate
bypass moderation/safety rules

Nếu thiếu thông tin, agent phải ghi rõ:

Missing information
Conflict found
Decision required
Recommended next action

Không được lấp lỗ hổng bằng sự tự tin. AI đã làm điều đó đủ nhiều, nhân loại chưa cần thêm.

## 4. Agent Source-of-Truth Rule

Agent phải đọc:

12-agent/1. source-truth-map.md

trước khi làm task liên quan đến:

feature
API
database
UI
security
testing
deployment
operations
analytics
legal/compliance

Nếu task chỉ là sửa typo nhỏ, có thể không cần retrieval sâu.

Nếu task thay đổi behavior, API, DB, UI, permission, security hoặc state machine, bắt buộc retrieval.

## 5. Source-of-Truth Priority

Khi docs conflict, ưu tiên:

## 1. State machine source of truth
## 2. Feature spec
## 3. API contract
## 4. Database schema/table spec
## 5. Architecture ADR
## 6. Design system/page spec
## 7. Overview docs
## 8. Old notes / examples

Nếu conflict vẫn chưa giải quyết được, agent phải dừng và tạo conflict note.

Không được tự chọn file mình thích. Đây là engineering, không phải buffet.

## 6. Agent Types
### 6.1. Documentation Agent

Focus:

write docs
sync docs
update changelog
create ADR draft
improve documentation structure

Must read:

DOCUMENTATION-STANDARDS.md
12-agent/source-truth-map.md
related source docs
### 6.2. Product Agent

Focus:

feature specs
acceptance criteria
edge cases
state machines
traceability
MVP scope

Must read:

02-requirements/*
03-product/*
### 6.3. Design Agent

Focus:

page specs
UI state
design system
responsive behavior
accessibility
content tone

Must read:

04-design/*
03-product/sitemap.md
related feature specs
### 6.4. Backend Agent

Focus:

Laravel models
controllers
form requests
policies
services
queues
notifications
broadcasting

Must read:

03-product/feature-specs/*
05-system-architecture/*
06-database/*
07-api/*
08-security/*
### 6.5. Database Agent

Focus:

schema
migration
seed
factory
ERD
constraints
indexes

Must read:

06-database/database-overview.md
06-database/schema.md
06-database/table-specifications.md
06-database/migration-strategy.md
06-database/seed-data.md
### 6.6. API Agent

Focus:

OpenAPI
endpoint design
error code
request/response schema
auth boundary
pagination/filter/sort

Must read:

07-api/api-overview.md
07-api/api.yaml
07-api/error-codes.md
related feature specs
### 6.7. QA Agent

Focus:

test cases
acceptance criteria
edge cases
regression
security scenarios

Must read:

09-quality/*
02-requirements/acceptance-criteria.md
02-requirements/edge-cases.md
03-product/state-machines/*
### 6.8. Security Agent

Focus:

auth
authorization
privacy
moderation
file access
safe logging
analytics privacy
admin audit

Must read:

08-security/*
03-product/feature-specs/safety-reporting.md
03-product/feature-specs/moderation.md
07-api/api.yaml
06-database/table-specifications.md
## 7. Agent Execution Lifecycle

Every non-trivial agent task follows this lifecycle:

1. Classify task
## 2. Retrieve docs
## 3. Extract constraints
4. Identify impacted files
## 5. Plan changes
## 6. Execute changes
7. Check consistency
8. Update docs if needed
## 9. Report output
### 7.1. Classify Task

Task types:

feature_change
api_change
database_change
ui_change
security_change
business_rule_change
bug_fix
refactor
documentation_only
test_change
deployment_change
### 7.2. Retrieve Docs

Use:

12-agent/1. source-truth-map.md
12-agent/4. rag-knowledge-base.md
### 7.3. Extract Constraints

Agent must identify:

MVP scope
role/permission rules
account gate rules
privacy rules
state machine rules
API contracts
DB constraints
UI requirements
audit requirements
notification/realtime side effects
test requirements
### 7.4. Execute Changes

Agent must follow project stack:

Laravel
Blade
Livewire
Alpine.js
TailwindCSS
SQL Server
Laravel Echo/Reverb if realtime
Laravel Queues
Laravel Notifications
Laravel Policies/Gates
### 7.5. Check Consistency

Agent must check:

feature spec vs implementation
API spec vs route/controller
DB docs vs migration/model
state enum vs source-of-truth
UI page vs sitemap/design docs
permission rules vs policy
error codes vs error-codes.md
## 8. Agent Output Standard

Every implementation-oriented agent output should include:

Summary
Files changed
Docs consulted
Docs updated
Tests needed
Risks
Next required human decision, if any

Example:

Summary:
Implemented mentor request accept flow.

Files changed:
- app/Actions/Mentor/AcceptMentorRequest.php
- app/Events/MentorRequestAccepted.php
- tests/Feature/MentorRequestTest.php

Docs consulted:
- docs/03-product/feature-specs/mentor-matching.md
- docs/06-database/schema.md
- docs/07-api/api.yaml

Docs updated:
- docs/07-api/api.yaml
- docs/CHANGELOG.md

Tests needed:
- mentor accepts request
- conversation created
- notification sent
- invalid state rejected

Risks:
- Pending request limit needs stress test.
## 9. Agent Documentation Sync Rule

Agent must update docs when changes affect:

API contract
database schema
business logic
state machine
permission
security
privacy
admin workflow
UI route/page
notification type
analytics event
deployment behavior

Docs sync policy is defined in:

12-agent/2. agent-change-protocol.md
## 10. Agent Safety Rule

Agent must follow:

12-agent/ai-safety.md

for:

private user data
verification evidence
reports
moderation cases
messages
admin audit
browser push subscription
analytics event
AI-assisted evidence review
## 11. Human Review Required

Human review is required for:

MVP scope change
new role/permission
state machine change
verification policy change
moderation policy change
privacy policy change
security architecture change
database architecture change
realtime architecture change
AI evidence analysis decision
legal/compliance policy
production deployment change

Agent can propose, but cannot finalize these alone.

Một agent tự quyết legal/compliance là cách nhanh nhất để biến đồ án thành vụ án.

## 12. Recommended Agent Workflow in Development

For each task:

## 1. Developer writes task description.
## 2. Agent classifies task.
## 3. Agent retrieves required docs.
## 4. Agent gives short implementation plan.
## 5. Agent creates or edits files.
## 6. Agent updates impacted docs.
## 7. Agent produces final summary.
## 8. Human reviews diff.
## 9. CI validates docs/contracts/tests.
## 13. Required CI Checks for Agent Work

Recommended:

markdownlint docs/**/*.md
redocly lint docs/07-api/api.yaml
spectral lint docs/07-api/api.yaml
mermaid validation for *.mmd
markdown link check
frontmatter validation
test suite
static analysis

Agent-generated work must not bypass CI.

Không có “AI viết nên chắc đúng”. Đó là câu thần chú gọi bug.

## 14. Agent Anti-patterns

Avoid:

writing code without reading docs
changing enum in code only
adding route without api.yaml
adding migration without schema docs
adding admin action without audit
adding notification type without notification spec
adding UI page without sitemap
adding private file access without security review
using real user data in examples
writing Vietnamese variable names in code
writing English UI copy in Vietnamese UI
## 15. Final Rule

UEConnect Agent phải làm việc như một technical teammate có kỷ luật:

retrieve before reasoning
document before changing
validate before finishing
sync docs after implementation
escalate conflicts instead of guessing

Agent tốt không phải agent viết nhiều code nhất.

Agent tốt là agent không âm thầm làm lệch hệ thống khỏi source of truth.
