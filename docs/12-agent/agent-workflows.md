---
title: "Agent Workflows"
module: "12-agent"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "AI Engineering / Product / Backend / QA"
depends_on:
  - "agent-overview.md"
  - "1. source-truth-map.md"
  - "2. agent-change-protocol.md"
  - "3. agent-task-checklist.md"
  - "4. rag-knowledge-base.md"
  - "5. prompt-design.md"
  - "ai-safety.md"
related:
  - "../03-product/feature-specs"
  - "../06-database"
  - "../07-api"
  - "../08-security"
  - "../09-quality"
---

# Agent Workflows

## 1. Purpose

File này định nghĩa các workflow chuẩn để AI Agent hỗ trợ xây dựng UEConnect.

Mục tiêu:

- Agent luôn đọc đúng docs trước khi làm.
- Agent biết khi nào phải update docs.
- Agent biết workflow riêng cho feature, API, database, UI, test, security.
- Agent không làm lệch MVP scope.
- Agent không bịa business rule.
- Agent không quên audit, privacy, permission, state machine.

Nếu `agent-overview.md` là hiến pháp, file này là quy trình làm việc. Vì không có quy trình thì agent sẽ “giúp” bằng cách tạo thêm việc, một truyền thống phần mềm lâu đời.

---

# 2. Universal Workflow

Mọi task không tầm thường đều đi theo workflow này:

```txt
1. Classify task
2. Locate source of truth
## 3. Retrieve related docs
## 4. Extract constraints
## 5. Create short plan
## 6. Execute
## 7. Validate against docs
8. Update impacted docs
## 9. Produce final summary
### 2.1. Step 1: Classify Task

Agent phân loại task:

feature_change
api_change
database_change
ui_change
security_change
business_rule_change
bug_fix
refactor
test_change
documentation_only
deployment_change

Một task có thể thuộc nhiều loại.

Example:

"Add message attachments"
= feature_change + api_change + database_change + security_change + test_change
### 2.2. Step 2: Locate Source of Truth

Agent phải đọc:

12-agent/1. source-truth-map.md

Sau đó xác định docs bắt buộc.

### 2.3. Step 3: Retrieve Related Docs

Minimum retrieval:

feature spec
state machine
API docs if endpoint involved
DB docs if data involved
design docs if UI involved
security docs if privacy/admin/moderation involved
quality docs if testing involved
### 2.4. Step 4: Extract Constraints

Agent ghi nhận:

business rules
states
roles
permissions
privacy
input validation
side effects
audit requirements
notification requirements
realtime requirements
edge cases
### 2.5. Step 5: Create Short Plan

Plan nên ngắn:

Files to change
Implementation steps
Docs to update
Tests to add
Risks
### 2.6. Step 6: Execute

Agent làm code/docs theo plan.

Không mở rộng scope khi chưa cần.

### 2.7. Step 7: Validate Against Docs

Check:

implementation matches feature spec
state matches state machine
API matches api.yaml
DB matches schema/table specs
UI matches design docs
security matches policy
tests cover acceptance criteria
### 2.8. Step 8: Update Impacted Docs

Theo:

12-agent/2. agent-change-protocol.md
### 2.9. Step 9: Final Summary

Output bắt buộc:

Summary
Files changed
Docs consulted
Docs updated
Tests needed
Risks
## 3. Feature Implementation Workflow

Use when building a feature like:

verification
profile
home feed
messaging
mentor matching
community club
safety reporting
moderation
admin operations
### 3.1. Required Docs

Read:

12-agent/1. source-truth-map.md
03-product/feature-specs/{feature}.md
03-product/state-machines/*
02-requirements/acceptance-criteria.md
02-requirements/edge-cases.md
06-database/schema.md
06-database/table-specifications.md
07-api/api.yaml
08-security/* if sensitive
09-quality/* if test planning
### 3.2. Workflow
1. Identify feature module.
## 2. Read feature spec.
## 3. Extract MVP/P0 scope.
## 4. Extract state machine.
5. Check DB tables.
6. Check API endpoints.
7. Check UI/page specs.
8. Check permission/privacy rules.
## 9. Implement feature.
## 10. Add tests.
11. Update docs/changelog.
### 3.3. Required Output
Feature behavior implemented
State transitions covered
API endpoints used/changed
DB tables used/changed
Permissions enforced
Audit side effects implemented
Notifications/realtime implemented
Tests added/planned
Docs updated
## 4. API Change Workflow

Use when:

adding endpoint
changing request body
changing response schema
adding error code
changing auth/permission behavior
changing pagination/filter/sort
### 4.1. Required Docs
07-api/api-overview.md
07-api/api.yaml
07-api/error-codes.md
related feature spec
06-database/schema.md if resource shape changes
08-security/* if auth/private data involved
### 4.2. Workflow
1. Identify API consumer.
2. Confirm endpoint belongs in API, not just Livewire.
## 3. Define route path and HTTP method.
## 4. Define auth and permission.
## 5. Define request schema.
## 6. Define response schema.
## 7. Define error codes.
## 8. Define audit/notification/realtime side effects.
9. Update api.yaml.
10. Update module API doc if exists.
## 11. Add API tests.
### 4.3. API Design Rules
Use /api/v1 prefix.
Use plural resources.
Use snake_case fields.
Use standard success/error envelope.
Use 422 for validation.
Use 403 for permission/account gate.
Use 409 for state conflict.
Use 404 for hidden/private not found.
Use cursor pagination for feed/messages/notifications.
Use page pagination for admin lists.
### 4.4. Done Criteria
[ ] api.yaml updated
[ ] error-codes.md updated if new error
[ ] Form Request validation defined
[ ] Policy/Gate defined
[ ] Feature test planned/added
[ ] Response redaction handled
## 5. Database Change Workflow

Use when:

new table
new column
new index
new relation
new enum
new migration
new seed/factory
### 5.1. Required Docs
06-database/database-overview.md
06-database/schema.md
06-database/table-specifications.md
06-database/migration-strategy.md
06-database/seed-data.md
06-database/erd.mmd
related feature spec
### 5.2. Workflow
1. Identify domain module.
2. Check existing schema.
## 3. Determine if new table/column is necessary.
## 4. Define table/column purpose.
## 5. Define nullable rules.
## 6. Define enum values.
## 7. Define FK behavior.
## 8. Define indexes and unique constraints.
## 9. Define soft delete/audit rule.
## 10. Write migration.
## 11. Write model/factory/seeder if needed.
12. Update schema.md.
13. Update table-specifications.md.
14. Update erd.mmd.
15. Update seed-data.md if seed needed.
### 5.3. Database Rules
Do not use nullable without business reason.
Do not use integer magic enum.
Do not cascade delete audit/history tables.
Do not store core relational data in JSON.
Do not expose private media path.
Do not add table without table spec.
### 5.4. Done Criteria
[ ] Migration created
[ ] Model updated
[ ] Factory/seeder updated if needed
[ ] schema.md updated
[ ] table-specifications.md updated
[ ] erd.mmd updated
[ ] api.yaml updated if API payload changed
[ ] Tests planned/added
## 6. UI / Page Workflow

Use when:

new page
new component
new UI state
new route
new navigation
responsive behavior
accessibility behavior
### 6.1. Required Docs
04-design/design-system.md
04-design/page-specs/*
04-design/ui-states/*
04-design/component-primitives.md
04-design/component-variants.md
04-design/interaction-states.md
04-design/responsive-rules.md
04-design/accessibility-rules.md
03-product/sitemap.md
related feature spec
### 6.2. Workflow
1. Identify page/route.
2. Check sitemap.
3. Check page spec.
4. Check feature behavior.
5. Check UI states.
6. Check design tokens.
## 7. Build Blade/Livewire/Alpine UI.
## 8. Ensure Vietnamese UI copy.
## 9. Ensure responsive behavior.
## 10. Ensure accessibility.
11. Update page spec/sitemap if changed.
### 6.3. UI Rules
Code names in English.
UI copy in Vietnamese.
Use TailwindCSS.
Use Blade components.
Use Livewire for interactive server state.
Use Alpine.js for lightweight client interaction.
Use design tokens.
Support empty/loading/error/offline/permission/moderation states.
### 6.4. Done Criteria
[ ] Page matches sitemap
[ ] Page matches page spec
[ ] UI states covered
[ ] Accessibility covered
[ ] Responsive behavior covered
[ ] Vietnamese copy used
[ ] Design tokens respected
## 7. Security / Privacy Workflow

Use when task touches:

auth
authorization
account gate
verification evidence
messages
reports
moderation
admin
private files
analytics
browser push
personal data
### 7.1. Required Docs
08-security/*
03-product/feature-specs/settings-privacy.md
03-product/feature-specs/safety-reporting.md
03-product/feature-specs/moderation.md
07-api/api.yaml
06-database/table-specifications.md
12-agent/ai-safety.md
### 7.2. Workflow
1. Identify sensitive data.
2. Identify actor and permission.
3. Check policy/gate needed.
4. Check privacy redaction.
5. Check block/moderation rules.
6. Check file access control.
7. Check audit requirement.
8. Check notification preview safety.
9. Check analytics payload safety.
## 10. Add tests for denied access.
### 7.3. Required Tests
unauthenticated rejected
unverified rejected where needed
profile incomplete rejected where needed
suspended/banned rejected
permission denied
blocked user cannot interact
private file cannot be accessed
hidden content redacted
admin action writes audit
## 8. Testing Workflow

Use when adding or updating tests.

### 8.1. Required Docs
09-quality/*
02-requirements/acceptance-criteria.md
02-requirements/edge-cases.md
03-product/state-machines/*
related feature spec
07-api/api.yaml if API test
06-database/schema.md if DB test
### 8.2. Test Types
feature tests
unit tests
policy tests
validation tests
state transition tests
API contract tests
browser/UI tests if needed
security tests
regression tests
### 8.3. Test Planning

For each feature, cover:

happy path
invalid input
permission denied
wrong state
privacy redaction
blocked user
moderated content
audit side effect
notification side effect
realtime side effect if applicable
### 8.4. Done Criteria
[ ] Acceptance criteria covered
[ ] Edge cases covered
[ ] State transitions covered
[ ] API errors covered
[ ] Permission tests included
[ ] Regression risks documented
## 9. Documentation Update Workflow

Use when updating docs only or syncing docs after code.

### 9.1. Required Docs
DOCUMENTATION-STANDARDS.md
12-agent/2. agent-change-protocol.md
12-agent/3. agent-task-checklist.md
related source docs
### 9.2. Workflow
1. Identify doc purpose.
2. Identify source-of-truth dependencies.
3. Check related docs for conflicts.
4. Update content.
5. Update frontmatter if needed.
6. Update cross references.
7. Update changelog if significant.
## 8. Validate links and names.
### 9.3. Done Criteria
[ ] File has clear purpose
[ ] References are correct
[ ] Source-of-truth priority respected
[ ] No duplicated conflicting rules
[ ] Changelog updated if needed
## 10. Bug Fix Workflow

Use when fixing bug.

### 10.1. Required Steps
## 1. Reproduce or describe bug.
2. Identify expected behavior from docs.
3. Identify actual behavior.
4. Locate source of truth.
5. Fix code.
## 6. Add regression test.
7. Update docs if docs were wrong or incomplete.
8. Update changelog if user-visible or important.
### 10.2. Bug Fix Rule

If code and docs conflict:

docs do not automatically win
code does not automatically win
source-of-truth priority decides
human review if business rule unclear
### 10.3. Done Criteria
[ ] Bug root cause identified
[ ] Expected behavior cited from docs
[ ] Regression test added
[ ] Related docs updated if needed
## 11. Refactor Workflow

Use when changing structure without changing behavior.

### 11.1. Required Steps
1. Confirm behavior must not change.
2. Identify affected modules.
## 3. Run/read tests.
## 4. Refactor small pieces.
5. Preserve API contract.
6. Preserve DB schema unless explicitly changing.
7. Preserve UI behavior.
8. Update architecture/docs only if structure changed.
### 11.2. Done Criteria
[ ] No behavior change
[ ] Tests still pass
[ ] API contract unchanged
[ ] DB schema unchanged
[ ] Docs updated only if architecture changed
## 12. Release Preparation Workflow

Use before release.

### 12.1. Required Docs
14-release-management/*
CHANGELOG.md
03-product/feature-priority.md
09-quality/*
10-devops/*
11-operations/*
### 12.2. Workflow
1. Confirm release scope.
2. Check MVP/P0 features.
3. Check migrations.
4. Check seed/reference data.
5. Check API contract.
6. Check security review.
7. Check test status.
8. Check known issues.
9. Update changelog.
10. Prepare rollback notes.
### 12.3. Done Criteria
[ ] Release scope clear
[ ] Migrations reviewed
[ ] API contract stable
[ ] Security-sensitive flows tested
[ ] Changelog updated
[ ] Rollback plan exists
## 13. AI-assisted Documentation Sync Workflow

Use when agent is asked to automatically update docs after code changes.

### 13.1. Input

Agent needs:

git diff
changed files
task description
related docs
### 13.2. Workflow
1. Parse changed files.
2. Classify change type.
3. Map code files to docs using source-truth-map.
4. Check if docs mention changed behavior.
5. Update impacted docs.
6. Update changelog if needed.
## 7. Report what changed and why.
### 13.3. Example Mapping
routes/api.php changed
→ docs/07-api/api.yaml
→ docs/07-api/error-codes.md

database/migrations/* changed
→ docs/06-database/schema.md
→ docs/06-database/table-specifications.md
→ docs/06-database/erd.mmd

app/Policies/* changed
→ docs/08-security/*
→ role-permission matrix

resources/views/app/messages/*
→ docs/04-design/page-specs/messaging.md
→ docs/03-product/feature-specs/messaging.md
## 14. Conflict Resolution Workflow

Use when docs disagree.

### 14.1. Workflow
1. Identify conflicting statements.
2. List files and sections.
3. Apply source-of-truth priority.
4. Propose resolution.
## 5. Mark docs needing update.
6. Request human decision if business impact exists.
### 14.2. Conflict Note Format
Conflict:
- File A says ...
- File B says ...

Priority:
- Source-of-truth rule gives priority to ...

Recommended resolution:
- Update File B to match File A.

Impact:
- API
- DB
- UI
- Tests
## 15. Final Rule

Agent workflow phải giúp UEConnect giữ được 4 thứ:

consistency
traceability
security
maintainability
