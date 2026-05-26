---
title: "Feature Spec Template"
module: "03-product/feature-specs"
product: "UEConnect"
version: "1.0"
status: "template"
last_updated: "2026-05-26"
owner: "Product / BA / UX / Frontend / Backend / QA"
applies_to:
  - "authentication.md"
  - "verification-identity.md"
  - "onboarding.md"
  - "profile-management.md"
  - "home-feed.md"
  - "post-comment.md"
  - "discovery-profile.md"
  - "greeting-connection.md"
  - "messaging.md"
  - "mentor-matching.md"
  - "career-pathway.md"
  - "community-club.md"
  - "notification.md"
  - "safety-reporting.md"
  - "moderation.md"
  - "admin-operations.md"
  - "settings-privacy.md"
  - "search-filter.md"
  - "media-upload.md"
  - "analytics-events.md"
depends_on:
  - "../product-overview.md"
  - "../feature-list.md"
  - "../feature-priority.md"
  - "../sitemap.md"
  - "../../02-requirements/functional-requirements.md"
  - "../../02-requirements/non-functional-requirements.md"
  - "../../02-requirements/role-permission-matrix.md"
  - "../../02-requirements/acceptance-criteria.md"
  - "../../02-requirements/edge-cases.md"
  - "../../02-requirements/traceability-matrix.md"
related:
  - "../use-cases"
  - "../user-flow"
  - "../state-machines"
  - "../../04-design/page-specs"
  - "../../05-system-architecture"
  - "../../06-database"
  - "../../07-security"
  - "../../08-testing"
---

# Feature Spec Template

## 1. Purpose

File này là template chuẩn để viết toàn bộ tài liệu trong folder:

```txt
docs/03-product/feature-specs/
````

Mục tiêu của feature spec:

* Mô tả rõ feature dùng để làm gì.
* Xác định actor nào dùng feature.
* Xác định flow chính, flow phụ, edge cases và trạng thái lỗi.
* Map feature tới requirement, acceptance criteria, user flow, page spec, route, database, API/service, permission, audit và test.
* Giúp frontend, backend, database, QA và UX hiểu cùng một version của feature.
* Tránh tình trạng feature được build bằng trí tưởng tượng tập thể, thứ nghe có vẻ nghệ thuật nhưng thường sinh ra bug có tính triết học.

Feature spec là tài liệu trung gian giữa:

```txt
Business / Requirements
→ Product Feature
→ UX Flow / Page Spec
→ System Architecture
→ Database
→ Implementation
→ Testing
```

---

## 2. How to Use This Template

Khi tạo một feature spec mới:

1. Copy toàn bộ template này.
2. Đổi `title`, `status`, `priority`, `feature_id`.
3. Xóa phần hướng dẫn không cần thiết.
4. Điền đầy đủ các section bắt buộc.
5. Không viết chung chung kiểu “user có thể quản lý thông tin”. Quản lý là gì? Xem, sửa, xóa, gửi, duyệt, ẩn, chặn, khôi phục? Máy tính không hiểu thơ tự do, thật đáng tiếc.

---

## 3. Required Frontmatter

Mỗi feature spec nên có frontmatter như sau:

```yaml
---
title: "Feature Name"
module: "03-product/feature-specs"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0 / P1 / P2 / P3"
feature_id: "FEATURE-XXX"
last_updated: "YYYY-MM-DD"
owner: "Product / BA / UX / Frontend / Backend / QA"
depends_on:
  - "../product-overview.md"
  - "../feature-list.md"
  - "../feature-priority.md"
  - "../sitemap.md"
  - "../../02-requirements/functional-requirements.md"
  - "../../02-requirements/acceptance-criteria.md"
  - "../../02-requirements/edge-cases.md"
related:
  - "../use-cases/..."
  - "../user-flow/..."
  - "../state-machines/..."
  - "../../04-design/page-specs/..."
  - "../../05-system-architecture/..."
  - "../../06-database/..."
  - "../../08-testing/..."
---
```

---

# [Feature Name]

## 1. Feature Summary

### 1.1. One-line Description

```txt
Mô tả feature trong 1 câu ngắn.
```

Example:

```txt
Verification Identity cho phép user chứng minh mình thuộc HCMUE để được mở quyền truy cập đầy đủ vào UEConnect.
```

### 1.2. Feature Goal

Feature này tồn tại để:

```txt
- Goal 1
- Goal 2
- Goal 3
```

### 1.3. Product Value

| Value              | Description                                       |
| ------------------ | ------------------------------------------------- |
| User value         | User nhận được lợi ích gì                         |
| Product value      | Product đạt mục tiêu gì                           |
| Business value     | Stakeholder/trường/portfolio nhận được giá trị gì |
| Trust/safety value | Feature bảo vệ trust/safety/privacy thế nào       |

### 1.4. Non-goals

Feature này không làm:

```txt
- Non-goal 1
- Non-goal 2
- Non-goal 3
```

Non-goals rất quan trọng. Nếu không ghi, sẽ có người thêm “chắc tiện thì làm luôn”. Câu đó từng phá nhiều project hơn cả bug null pointer.

---

## 2. Scope

### 2.1. In Scope

```txt
- Capability 1
- Capability 2
- Capability 3
```

### 2.2. Out of Scope

```txt
- Out-of-scope item 1
- Out-of-scope item 2
- Out-of-scope item 3
```

### 2.3. Future-ready

```txt
- Future extension 1
- Future extension 2
```

---

## 3. Priority

| Item              | Priority | Reason          |
| ----------------- | -------- | --------------- |
| Core capability A | P0       | Vì sao bắt buộc |
| Capability B      | P1       | Vì sao nên có   |
| Capability C      | P2       | Vì sao để sau   |
| Capability D      | P3       | Vì sao future   |

### Priority Rule

```txt
P0 = không có thì MVP không chạy đúng hoặc không an toàn.
P1 = nên có để MVP đáng dùng.
P2 = chuẩn bị mở rộng, chưa cần build sâu.
P3 = để roadmap sau.
OOS = không làm trong MVP.
```

---

## 4. Actors & Permissions

### 4.1. Actors

| Actor            | Can Use? | Notes |
| ---------------- | -------: | ----- |
| Guest            |   Yes/No |       |
| Registered user  |   Yes/No |       |
| Pending user     |   Yes/No |       |
| Verified student |   Yes/No |       |
| Alumni           |   Yes/No |       |
| Advisor / Mentor |   Yes/No |       |
| Club Manager     |   Yes/No |       |
| Moderator        |   Yes/No |       |
| Admin            |   Yes/No |       |

### 4.2. Required Permissions

| Action    | Required Role | Required Permission | Scope                 |
| --------- | ------------- | ------------------- | --------------------- |
| View page | role          | permission          | global / own / scoped |
| Create    | role          | permission          |                       |
| Edit      | role          | permission          |                       |
| Delete    | role          | permission          |                       |
| Approve   | role          | permission          |                       |
| Moderate  | role          | permission          |                       |

### 4.3. Access Rules

```txt
- Rule 1
- Rule 2
- Rule 3
```

Important UEConnect rule:

```txt
Account status overrides role and permission.
```

---

## 5. Related Requirements

| Requirement ID | Requirement Name | Notes |
| -------------- | ---------------- | ----- |
| REQ-XXX-001    | Requirement name |       |
| REQ-XXX-002    | Requirement name |       |

### Related Business Rules

| Business Rule | Description      |
| ------------- | ---------------- |
| BR-XXX        | Rule description |

### Related Acceptance Criteria

| AC ID      | Description              |
| ---------- | ------------------------ |
| AC-XXX-001 | Acceptance criteria name |

### Related Edge Cases

| EC ID      | Description    |
| ---------- | -------------- |
| EC-XXX-001 | Edge case name |

---

## 6. User Stories

Viết user story theo format:

```txt
As a [actor],
I want to [action],
So that [value].
```

| ID     | User Story                        | Priority |
| ------ | --------------------------------- | -------- |
| US-001 | As a ..., I want ..., so that ... | P0       |
| US-002 | As a ..., I want ..., so that ... | P1       |

---

## 7. Main User Flow

### 7.1. Happy Path

```txt
Step 1
→ Step 2
→ Step 3
→ Step 4
→ Success state
```

### 7.2. Detailed Flow

| Step | Actor Action         | System Response             | State      |
| ---: | -------------------- | --------------------------- | ---------- |
|    1 | User opens page      | System loads data           | loading    |
|    2 | User performs action | System validates            | validating |
|    3 | User submits         | System saves                | submitting |
|    4 | Success              | System redirects/updates UI | success    |

### 7.3. Alternative Flows

| Flow               | Condition | Expected Behavior |
| ------------------ | --------- | ----------------- |
| Alternative flow 1 | Condition | Behavior          |
| Alternative flow 2 | Condition | Behavior          |

### 7.4. Failure Flows

| Failure            | Expected Handling          |
| ------------------ | -------------------------- |
| Validation error   | Show field error           |
| Permission denied  | Show safe 403 or redirect  |
| Network error      | Show retry                 |
| Resource not found | Show unavailable state     |
| Account restricted | Redirect to account status |

---

## 8. Page / Route Mapping

| Page       | Route          | Layout           | Access     | Priority |
| ---------- | -------------- | ---------------- | ---------- | -------- |
| Page name  | `/route`       | `AppShellLayout` | Verified   | P0       |
| Admin page | `/admin/route` | `AdminLayout`    | Permission | P0       |

### 8.1. Navigation Placement

| Surface           | Placement                         |
| ----------------- | --------------------------------- |
| Desktop nav       | Sidebar / topbar / user menu      |
| Mobile nav        | Bottom tab / profile menu / modal |
| Admin nav         | Sidebar section                   |
| Contextual action | Button / dropdown / more menu     |

---

## 9. UI States

Mỗi feature spec phải định nghĩa state. UI không chỉ có “đẹp lúc có data”. Đời thật có loading, empty, lỗi mạng và user bấm lung tung.

### 9.1. Required States

| State              | Required? | Description              |
| ------------------ | --------: | ------------------------ |
| Default            |       Yes | Normal loaded state      |
| Loading            |       Yes | Data loading             |
| Empty              |    Yes/No | No data available        |
| Error              |       Yes | Generic error            |
| Validation error   |    Yes/No | Form invalid             |
| Permission denied  |    Yes/No | User lacks access        |
| Offline            |    Yes/No | Network unavailable      |
| Restricted account |    Yes/No | Suspended/banned/pending |
| Success            |       Yes | Action completed         |

### 9.2. State Copy Guidelines

| State             | Copy Direction                |
| ----------------- | ----------------------------- |
| Empty             | Helpful, suggests next action |
| Error             | Clear, not dramatic           |
| Permission denied | Explain access limitation     |
| Restricted        | Explain status and next step  |
| Success           | Confirm action briefly        |

---

## 10. Components

### 10.1. Component List

| Component   | Purpose | Reusable? |
| ----------- | ------- | --------- |
| Component A |         | Yes/No    |
| Component B |         | Yes/No    |

### 10.2. Component Rules

```txt
- Rule 1
- Rule 2
- Rule 3
```

### 10.3. Design System Notes

```txt
- Use neutral-first UI.
- Use HCMUE blue only for primary CTA, selected state, verified/trust accent.
- Avoid gradient-heavy UI.
- Use icon system consistently.
- Support mobile-first layouts.
```

---

## 11. Data Model Impact

### 11.1. Entities / Tables

| Entity     | Purpose     | Required? |
| ---------- | ----------- | --------- |
| table_name | Description | Yes/No    |

### 11.2. Key Fields

| Field      | Type        | Notes         |
| ---------- | ----------- | ------------- |
| id         | bigint/uuid | Primary key   |
| user_id    | bigint/uuid | Owner/actor   |
| status     | string/enum | State machine |
| created_at | timestamp   |               |
| updated_at | timestamp   |               |

### 11.3. Relationships

```txt
Entity A belongs to User
Entity A has many Entity B
Entity B belongs to Entity A
```

### 11.4. Constraints

```txt
- Unique constraint
- Foreign key
- Soft delete rule
- Status enum rule
```

### 11.5. Data Retention Notes

```txt
- What data is retained?
- What data is sensitive?
- What data should not be public?
- What data should be auditable?
```

---

## 12. API / Controller / Service Direction

UEConnect dùng Laravel Blade-first, nên không phải feature nào cũng cần public JSON API. Nhưng mỗi feature vẫn cần mô tả backend direction.

### 12.1. Routes / Controllers

| Method | Route           | Controller/Action           | Purpose       |
| ------ | --------------- | --------------------------- | ------------- |
| GET    | `/example`      | `ExampleController@index`   | View page     |
| POST   | `/example`      | `ExampleController@store`   | Submit action |
| PATCH  | `/example/{id}` | `ExampleController@update`  | Update        |
| DELETE | `/example/{id}` | `ExampleController@destroy` | Delete        |

### 12.2. Services / Actions

| Service / Action             | Responsibility          |
| ---------------------------- | ----------------------- |
| `CreateSomethingAction`      | Creates entity          |
| `UpdateSomethingAction`      | Updates entity          |
| `AuthorizeSomethingAction`   | Checks access           |
| `RecordSomethingEventAction` | Records analytics/audit |

### 12.3. Form Requests

| Form Request             | Purpose              |
| ------------------------ | -------------------- |
| `StoreSomethingRequest`  | Validate create form |
| `UpdateSomethingRequest` | Validate update form |

### 12.4. Policies / Gates

| Policy / Gate | Purpose                  |
| ------------- | ------------------------ |
| `view`        | Can view resource        |
| `create`      | Can create               |
| `update`      | Can update               |
| `delete`      | Can delete               |
| `moderate`    | Can moderate             |
| `adminAction` | Can perform admin action |

---

## 13. Validation Rules

| Field      | Rule                    | Error Message Direction    |
| ---------- | ----------------------- | -------------------------- |
| field_name | required / max / unique | Friendly copy              |
| file       | mimes / max size        | Explain allowed file types |
| status     | in enum                 | Internal validation        |

Validation rules must exist server-side. Client-side validation is convenience, not security. Dù UI có đẹp đến đâu, Postman vẫn không biết xấu hổ.

---

## 14. Security & Privacy

### 14.1. Security Rules

```txt
- Server-side authorization is required.
- Never rely only on hidden UI.
- Validate every user input.
- Protect against ID guessing.
- Rate limit risky actions if needed.
- Use private storage for sensitive files.
```

### 14.2. Privacy Rules

```txt
- Do not expose full MSSV publicly.
- Do not expose evidence files publicly.
- Do not log private message body.
- Do not send sensitive data to analytics.
- Do not expose report details to target user.
```

### 14.3. Sensitive Data

| Data          | Sensitive? | Handling                          |
| ------------- | ---------: | --------------------------------- |
| MSSV          |        Yes | Admin-only/full, masked elsewhere |
| Evidence file |        Yes | Private storage                   |
| Message body  |        Yes | Participant-only                  |
| Report detail |        Yes | Reporter/admin only               |
| Admin note    |        Yes | Admin-only                        |
| Audit log     |        Yes | Permission-only                   |

---

## 15. Audit Requirements

### 15.1. Audit Needed?

| Action                     | Audit Required? |              Reason Required? |
| -------------------------- | --------------: | ----------------------------: |
| Create normal user content |     No/Optional |                            No |
| Admin approval             |             Yes | Optional/Yes depending action |
| Admin rejection            |             Yes |                           Yes |
| Permission grant/revoke    |             Yes |                           Yes |
| Hide/delete content        |             Yes |                           Yes |
| Suspend/ban user           |             Yes |                           Yes |

### 15.2. Audit Payload

Audit log should include:

```txt
actor_id
action
target_type
target_id
previous_state
new_state
reason
ip_address if needed
user_agent if needed
created_at
```

---

## 16. Notification Requirements

| Trigger       | Receiver | Notification Type | Priority |
| ------------- | -------- | ----------------- | -------- |
| Event happens | User     | type              | P0/P1    |

### Notification Privacy Rule

```txt
Notification must not leak sensitive data.
```

Bad:

```txt
Nguyễn Văn A đã báo cáo bạn vì: [full sensitive report detail]
```

Good:

```txt
Một nội dung của bạn đang được xem xét theo quy định cộng đồng.
```

---

## 17. Analytics / Event Tracking

### 17.1. Events

| Event Name            | Trigger              | Payload         | Sensitive? |
| --------------------- | -------------------- | --------------- | ---------- |
| `feature_action_done` | When action succeeds | IDs/status only | No         |

### 17.2. Payload Rules

```txt
- Use IDs and safe metadata.
- Do not store message body.
- Do not store evidence URL.
- Do not store full report detail.
- Do not store full MSSV unless explicitly justified and protected.
```

---

## 18. Edge Cases

| Edge Case                          | Expected Handling                       |
| ---------------------------------- | --------------------------------------- |
| User double submits                | Prevent duplicate / idempotent handling |
| Resource deleted while viewing     | Show unavailable state                  |
| Permission revoked while page open | Reject on submit                        |
| Account suspended while page open  | Block next action                       |
| Network fails                      | Show retry                              |
| File upload interrupted            | Allow retry                             |
| Duplicate record conflict          | Show clear error                        |
| Sensitive data access denied       | Safe 403                                |

---

## 19. Accessibility Requirements

```txt
- Keyboard accessible controls.
- Proper label for form fields.
- Visible focus states.
- Color is not the only indicator.
- Error messages are linked to fields.
- Touch targets are usable on mobile.
- Icons with meaning need accessible labels.
```

Minimum:

```txt
WCAG-aware, mobile-friendly, readable, keyboard-usable.
```

Không cần làm màu accessibility trong docs rồi quên trên UI. Đó là đạo đức giả có aria-label.

---

## 20. Responsive / PWA Requirements

### 20.1. Breakpoints

| Viewport | Behavior                                  |
| -------- | ----------------------------------------- |
| Mobile   | Bottom nav, single-column, thumb-friendly |
| Tablet   | Wider card/list layout                    |
| Desktop  | Sidebar/topbar, multi-column if useful    |

### 20.2. PWA Notes

```txt
- Page should work inside app shell.
- Avoid layout shift.
- Support offline/error state when needed.
- Use safe area spacing on mobile.
- Do not design desktop-first and shrink it.
```

---

## 21. Performance Requirements

```txt
- Use pagination for lists.
- Avoid loading all records.
- Use lazy loading where useful.
- Avoid unnecessary heavy JS.
- Keep Blade-first pages simple.
- Use indexes for frequently filtered data.
```

Performance target examples:

| Area                | Target                   |
| ------------------- | ------------------------ |
| Initial page render | Fast enough for PWA feel |
| List loading        | Paginated                |
| Search/filter       | Indexed                  |
| Upload              | Clear progress/error     |
| Admin queue         | Filterable and paginated |

---

## 22. Content / Copy Rules

### 22.1. Tone

UEConnect copy should be:

```txt
clear
friendly
student-first
trusted
not too corporate
not childish
not dating-like
```

### 22.2. Forbidden Copy

```txt
match
swipe
crush
hot
dating
hẹn hò
ghép đôi
tán tỉnh
người yêu
```

### 22.3. Preferred Copy

```txt
Gửi lời chào
Kết nối
UEer
Bạn cùng khoa
Cùng học
Cùng ngành
Cùng khóa
Mentor
Khám phá
Hỗ trợ
Cộng đồng
```

---

## 23. QA / Test Plan

### 23.1. Test Types

| Test Type        | Required? | Notes                 |
| ---------------- | --------: | --------------------- |
| Unit             |    Yes/No | Business logic        |
| Feature          |       Yes | Laravel HTTP tests    |
| Integration      |    Yes/No | Multi-module actions  |
| E2E              |    Yes/No | Critical user journey |
| Security         |       Yes | Permission/privacy    |
| Manual UX QA     |       Yes | States and responsive |
| Accessibility QA |       Yes | Basic accessibility   |

### 23.2. Test Cases

| Test Case ID | Scenario            | Expected Result |
| ------------ | ------------------- | --------------- |
| TC-XXX-001   | Happy path          | Success         |
| TC-XXX-002   | Unauthorized access | 403 / redirect  |
| TC-XXX-003   | Validation error    | Field error     |
| TC-XXX-004   | Edge case           | Safe handling   |

### 23.3. Regression Checklist

```txt
- Does route guard still work?
- Does permission check still work?
- Does account status override action?
- Does mobile layout still work?
- Does sensitive data remain hidden?
- Does audit still record critical actions?
- Does notification avoid sensitive data?
```

---

## 24. Rollout Checklist

Before marking this feature ready:

```txt
[ ] Requirements mapped.
[ ] Feature priority clear.
[ ] User flow defined.
[ ] Page/routes defined.
[ ] Permissions defined.
[ ] UI states defined.
[ ] Data model impact defined.
[ ] Validation rules defined.
[ ] Security/privacy rules defined.
[ ] Audit rules defined if needed.
[ ] Notification rules defined if needed.
[ ] Analytics events defined if needed.
[ ] Edge cases listed.
[ ] QA test cases listed.
[ ] Mobile behavior considered.
[ ] Accessibility considered.
[ ] Out-of-scope items locked.
```

---

## 25. Implementation Task Breakdown

Use this section to break the feature into dev tasks.

### 25.1. Backend Tasks

```txt
- Migration
- Model
- Relationship
- Form Request
- Policy/Gate
- Controller
- Service/Action
- Audit event
- Notification
- Tests
```

### 25.2. Frontend Tasks

```txt
- Blade page
- Blade components
- Tailwind layout
- Form UI
- Empty/loading/error states
- Modal/dropdown interactions
- Mobile layout
- Accessibility polish
```

### 25.3. QA Tasks

```txt
- Happy path test
- Negative test
- Permission test
- Mobile test
- Sensitive data test
- Regression test
```

---

# 26. Example Mini Spec Structure

Khi viết file thật, có thể dùng cấu trúc rút gọn như sau nếu feature nhỏ:

```md
# Feature Name

## 1. Summary
## 2. Scope
## 3. Actors & Permissions
## 4. Related Requirements
## 5. User Flow
## 6. Routes & Pages
## 7. UI States
## 8. Data Model
## 9. Backend Direction
## 10. Security & Privacy
## 11. Audit / Notification / Analytics
## 12. Edge Cases
## 13. QA Checklist
## 14. Rollout Checklist
```

---

# 27. Feature Spec Quality Checklist

Một feature spec tốt phải trả lời được:

```txt
Feature này tồn tại vì sao?
Ai dùng nó?
Ai không được dùng nó?
Route/page nào liên quan?
Flow chính là gì?
Flow lỗi là gì?
Data nào được tạo/sửa/xóa?
Permission nào cần check?
Sensitive data nào cần bảo vệ?
Có audit không?
Có notification không?
Có analytics không?
QA test gì?
Mobile state ra sao?
Out-of-scope là gì?
```

Nếu không trả lời được mấy câu này, feature spec chưa sẵn sàng để dev. Nó chỉ là một ý tưởng đang mặc áo markdown.

---

# 28. Final Template Statement

```txt
Feature spec của UEConnect phải đủ rõ để Product hiểu value, UX hiểu flow, Frontend hiểu page/component/state, Backend hiểu route/service/permission, Database hiểu entity/constraint, Security hiểu sensitive data, QA hiểu test case. Nếu một feature spec không giúp các vai trò đó build và kiểm thử cùng một thứ, nó chưa đạt chuẩn.
```
