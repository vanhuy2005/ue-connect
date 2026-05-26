---
title: "Documentation Standards"
product: "UEConnect"
version: "1.0"
status: "active"
last_updated: "2026-05-26"
owner: "Project Team"
---

# Documentation Standards

## 1. Purpose

Tài liệu này định nghĩa chuẩn viết docs cho UEConnect.

Mục tiêu:

- Tài liệu dễ đọc.
- Tài liệu dễ tìm.
- Tài liệu không mâu thuẫn.
- Tài liệu bám source of truth.
- Tài liệu đủ tốt để dev, designer, tester và agent cùng dùng được.

Docs không phải đồ trang trí trong repo. Docs là bản đồ. Không có bản đồ thì cả team sẽ đi theo cảm xúc, và cảm xúc thì rất tệ trong software architecture.

---

# 2. Folder Rule

Mỗi folder trong `docs/` có trách nhiệm riêng:

```txt
00-overview              Tổng quan dự án
01-business              Business context
02-requirements          Requirement, scope, acceptance criteria
03-product               Product docs, feature specs, sitemap, state machine
04-design                Design system, page specs, UI states
05-system-architecture   Architecture, ADR, deployment view
06-database              Database schema, ERD, migration, seed
07-api                   API overview, api.yaml, endpoint docs
08-security              Security, privacy, auth, permission
09-quality               Testing, QA, acceptance validation
10-devops                DevOps, environment, CI/CD
11-operations            Operations, monitoring, support
12-agent                 AI agent rules and workflows
13-analytics             Analytics strategy
14-release-management    Release process
15-governance-and-compliance Governance/compliance
16-legal                 Legal notes
17-localization          Localization/i18n
18-docs-assets           Images/assets for docs
99-appendix              Appendix, references
## 3. File Naming

Use lowercase kebab-case for new docs:

api-overview.md
feature-priority.md
state-machine-source-of-truth.md
table-specifications.md

Avoid:

APIOverview.md
Final Doc.md
new_file_2.md
document.md

Một file tên document.md là cách nói “tôi cũng không biết nó là gì”.

## 4. Markdown Format

Use:

# H1 for document title
## H2 for major sections
### H3 for details

Keep sections short.

Prefer tables for structured comparison.

Use code blocks for exact values:

pending
approved
rejected

## 5. Frontmatter

Important docs should start with frontmatter:

---
title: "Document Title"
module: "03-product"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product / Backend"
depends_on:
  - "../path/to/source.md"
related:
  - "../path/to/related.md"
---

### 5.1. Status Values

Use:

draft
approved-draft
active
deprecated
archived

### 5.2. Priority Values

Use:

P0
P1
P2
P3

## 6. Source of Truth Rule

When docs conflict, use this priority:

1. State machine source of truth
2. Feature spec
3. API contract
4. Database schema/table spec
5. Architecture ADR
6. Design system/page spec
7. Overview docs
8. Old notes/examples

If conflict is found, do not silently edit around it.

Write a conflict note or update the lower-priority doc.

## 7. Required Updates

Update docs when changing:

| Change | Docs to update |
| --- | --- |
| New feature | 03-product/feature-list.md, feature spec |
| MVP scope change | 03-product/feature-priority.md |
| New page/route | 03-product/sitemap.md, 04-design/page-specs/* |
| New API endpoint | 07-api/api.yaml, related API doc |
| New error code | 07-api/error-codes.md |
| New table/column | 06-database/schema.md, table-specifications.md, erd.mmd |
| New state | State machine docs, feature spec, API enum, DB enum |
| New permission | Role/permission docs, security docs |
| New notification | Notification spec, API schema |
| New admin action | Admin spec, audit rules |
| New design component | Design system/component docs |

## 8. Writing Style

Write clearly.

Prefer:

User can submit a verification request only when the request is draft or needs_more_information.

Avoid:

Basically users maybe can resubmit things depending on the situation.

Be specific with:

states
roles
permissions
routes
tables
columns
API endpoints
error codes
side effects

## 9. Code and UI Language

| Area | Language |
| --- | --- |
| Code identifiers | English |
| API fields | English snake_case |
| Database names | English snake_case |
| UI text | Vietnamese |
| User-facing error messages | Vietnamese |
| Internal docs | Vietnamese or English, but consistent per file |

Example:

```json
{
  "error": {
    "code": "ACCOUNT_NOT_VERIFIED",
    "message": "Bạn cần xác thực tài khoản trước khi tiếp tục."
  }
}
```

## 10. State and Enum Rule

All states/enums must be written exactly and consistently.

Good:

pending_review
needs_more_information
approved
rejected

Bad:

pending review
need-more-info
NeedMoreInformation

If enum changes, update:

feature spec
state machine
database docs
api.yaml
tests

## 11. API Documentation Rule

Every API doc should include:

- purpose
- auth requirement
- permission requirement
- endpoint list
- request schema
- response schema
- error codes
- side effects
- privacy rules
- test checklist

`api.yaml` is the contract. If endpoint changes, update `api.yaml`.

## 12. Database Documentation Rule

Every database table should answer:

- What does it store?
- Who creates it?
- Who can read it?
- Who can update it?
- What state does it have?
- What table does it relate to?
- Does it need audit?
- Does it contain sensitive data?
- Does it need soft delete?
- What indexes are required?

If DB changes, update:

- schema.md
- table-specifications.md
- erd.mmd
- migration-strategy.md if needed
- seed-data.md if needed

## 13. Design Documentation Rule

Every page spec should include:

- purpose
- route
- main users
- entry points
- layout
- sections
- components
- states
- permissions
- empty/error/loading/offline behavior
- responsive behavior
- accessibility notes
- source-of-truth references

Design must follow:

- design-system.md
- component-primitives.md
- component-variants.md
- interaction-states.md
- responsive-rules.md
- accessibility-rules.md

## 14. Agent Documentation Rule

Agent-related docs must help AI:

- find source of truth
- avoid hallucination
- update docs correctly
- respect safety/privacy
- follow project workflow

Important files:

- 12-agent/1. source-truth-map.md
- 12-agent/2. agent-change-protocol.md
- 12-agent/3. agent-task-checklist.md
- 12-agent/4. rag-knowledge-base.md
- 12-agent/5. prompt-design.md
- 12-agent/agent-overview.md
- 12-agent/agent-workflows.md
- 12-agent/ai-safety.md

## 15. Changelog Rule

Update CHANGELOG.md when changing:

- feature behavior
- API contract
- database schema
- state machine
- security/privacy rule
- admin workflow
- release scope

No changelog needed for typo-only edits.

## 16. Link Rule

Use relative links when possible.

Good:

See [API Overview](./07-api/api-overview.md)

Avoid broken references.

## 17. Review Checklist

Before approving a doc:

- [ ] Purpose is clear.
- [ ] Source-of-truth dependencies are listed.
- [ ] Terminology is consistent.
- [ ] States/enums match source of truth.
- [ ] API references match api.yaml.
- [ ] DB references match schema/table specs.
- [ ] Security/privacy rules are not missing.
- [ ] Links are valid.
- [ ] No real private data is included.
- [ ] Changelog updated if needed.

## 18. Final Rule

Good documentation should let a new developer answer:

- What is this?
- Why does it exist?
- Who uses it?
- How does it work?
- What can go wrong?
- Which files are source of truth?
- What must be updated if it changes?