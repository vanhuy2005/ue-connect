---
title: "Advisor / Mentor Flow"
module: "03-product/user-flow/role-level"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P1"
actor: "Advisor / Mentor"
last_updated: "2026-05-26"
owner: "Product / UX / Frontend / Backend / QA"
depends_on:
  - "../../use-cases/advisor-use-cases.md"
  - "../../../02-requirements/role-permission-matrix.md"
  - "../../../02-requirements/acceptance-criteria.md"
  - "../../../02-requirements/edge-cases.md"
related_module_flows:
  - "../module-level/mentor-flow.md"
  - "../module-level/messaging-flow.md"
  - "../module-level/safety-reporting-flow.md"
related_feature_specs:
  - "../../feature-specs/mentor-matching.md"
  - "../../feature-specs/profile-management.md"
  - "../../feature-specs/messaging.md"
  - "../../feature-specs/safety-reporting.md"
---

# Advisor / Mentor Flow

## 1. Purpose

File này mô tả hành trình của `Advisor` hoặc user có `mentor_access` trong UEConnect.

Advisor/Mentor có thể là:

- Cố vấn học tập.
- Giảng viên.
- Alumni mentor.
- Senior student mentor.
- Người hỗ trợ học tập, kỹ năng, career hoặc cộng đồng.

Mentor là một tính năng chính của UEConnect, nhưng flow phải giữ cảm giác hỗ trợ, không biến sản phẩm thành LinkedIn mini, nơi ai cũng trông như đang đi phỏng vấn kể cả khi chỉ muốn hỏi môn nào nên học trước.

---

## 2. Actor Definition

| Field               | Description                                                                |
| ------------------- | -------------------------------------------------------------------------- |
| Actor               | Advisor / Mentor                                                           |
| Primary role        | `advisor`, `alumni`, hoặc `student` tùy verification                       |
| Required capability | `mentor_access`                                                            |
| Main goal           | Nhận request phù hợp, hỗ trợ sinh viên, chia sẻ insight/tài nguyên         |
| Access rule         | Chỉ nhận mentor request khi account active và mentor availability cho phép |
| UX direction        | Supportive, calm, focused, not CV-heavy                                    |

Important:

```txt
Mentor is a permission/capability, not a primary system role.
```

## 3. Advisor Journey Overview

```txt
Advisor receives invitation or registers
→ Creates mentor profile
→ Submits for review if required
→ Admin approves mentor access
→ Sets availability
→ Receives mentor requests
→ Reviews student context
→ Accepts / declines / asks for more info
→ Starts mentoring conversation
→ Shares guidance/resources
→ Completes or closes request
→ Can pause availability anytime
```

## 4. Entry Points

Advisor/Mentor có thể vào flow từ:

- admin invitation
- mentor program invitation
- alumni enables mentor mode
- advisor account creation
- student sends mentor request
- notification
- mentor dashboard
- direct email/system link

## 5. Mentor Setup Flow

Flow

```txt
Open mentor setup
→ Add basic profile info
→ Add role/background
→ Add support topics
→ Add short bio
→ Set availability
→ Preview mentor profile
→ Submit for review
→ Pending / approved / rejected / need more info
```

Required Fields

| Field                      | Priority |
| -------------------------- | -------- |
| Display name               | P1       |
| Avatar                     | P1       |
| Role type                  | P1       |
| Expertise / support topics | P1       |
| Short bio                  | P1       |
| Availability               | P1       |
| Communication expectation  | P1       |

Optional Fields

- education/work background
- useful links
- FAQ
- resource list
- response time expectation

UX Rules

- Không bắt mentor tạo CV dài.
- Tập trung vào “có thể hỗ trợ gì”.
- Có preview profile.
- Có trạng thái pending review.
- Có thể pause/hidden sau khi approved.
- Mentor profile không được khoe thành tích quá lố như đang chào bán khóa học.

## 6. Mentor Verification / Approval Flow

Flow

```txt
Mentor submits profile
→ Admin reviews
→ Approved / rejected / need more information
→ Mentor receives status
→ If approved: profile visible according to settings
```

States

| State                 | Meaning                          |
| --------------------- | -------------------------------- |
| Draft                 | Chưa gửi duyệt                   |
| Pending               | Chờ admin duyệt                  |
| Approved              | Có thể nhận request              |
| Rejected              | Không được duyệt                 |
| Need more information | Cần bổ sung                      |
| Hidden                | Tạm ẩn khỏi mentor discovery     |
| Paused                | Mentor tự tạm ngưng nhận request |
| Revoked               | Admin thu hồi mentor access      |

Rules

- mentor_access grant/revoke must be audited.
- Revoked mentor cannot receive new requests.
- Suspended/banned mentor cannot use mentor capability.
- Pending mentor profile is not public.

## 7. Availability Flow

Flow

```txt
Open mentor settings
→ Set active / paused
→ Set support topics
→ Set max active requests if supported
→ Save
→ Student-facing availability updates
```

Availability States

| State       | Meaning                                |
| ----------- | -------------------------------------- |
| Available   | Có thể nhận request                    |
| Limited     | Nhận request giới hạn                  |
| Paused      | Tạm ngưng                              |
| Hidden      | Không xuất hiện trong mentor discovery |
| Unavailable | Không nhận request                     |

UX Rules

- Mentor phải dễ pause.
- Nếu quá tải, student không thấy CTA request hoặc CTA bị disabled.
- Student cần thấy availability rõ.
- Không ép mentor nhận request vô hạn. Bóc lột bằng UI vẫn là bóc lột, chỉ là có border-radius.

## 8. Request Management Flow

Flow

```txt
Student sends mentor request
→ Mentor receives notification
→ Mentor opens request list
→ Opens request detail
→ Reviews student profile and question
→ Accepts / declines / asks for more info / reports
```

Request Detail Must Show

- student_name
- verified_badge
- faculty
- major
- cohort
- topic
- question/context
- student_goal
- profile_summary
- previous interaction if any
- safety/report action

Request States

| State          | Meaning                     |
| -------------- | --------------------------- |
| Pending        | Chờ mentor phản hồi         |
| Accepted       | Mentor đồng ý               |
| Declined       | Mentor từ chối              |
| Need more info | Cần sinh viên bổ sung       |
| Cancelled      | Student hủy hoặc system hủy |
| Completed      | Đã hoàn thành               |
| Reported       | Có vấn đề safety            |
| Closed         | Đã đóng                     |

## 9. Accept Request Flow

Flow

```txt
Mentor opens pending request
→ Reviews context
→ Clicks Accept
→ Optional welcome message
→ System creates/unlocks conversation
→ Student receives notification
→ Request status = accepted
```

Required UI

- confirmation
- optional welcome message
- conversation context header
- request status badge
- student context card

Rules

- Only target mentor can accept.
- Mentor must still have active mentor_access.
- Mentor availability/account status checked at accept time.
- Conversation created once only.

## 10. Decline Request Flow

Flow

```txt
Mentor opens request
→ Clicks Decline
→ Selects optional polite reason/template
→ Submit
→ Student notified
→ Request status = declined
→ Student may see suggested alternatives
```

Decline UX Rules

- Không làm student cảm thấy bị đánh giá cá nhân.
- Reason nên nhẹ, không quá chi tiết nếu không cần.
- Có template lịch sự.

Example copy:

Mentor hiện chưa thể hỗ trợ yêu cầu này.
Bạn có thể thử gửi yêu cầu đến mentor khác phù hợp hơn.

## 11. Ask for More Information Flow

Flow

```txt
Mentor clicks Need more info
→ Writes clarification request
→ Student receives notification
→ Student updates question/context
→ Request returns to pending review
```

Use When

- question too vague
- topic unclear
- student goal missing
- mentor needs context before accepting

## 12. Mentoring Conversation Flow

Flow

```txt
Request accepted
→ Conversation opens
→ Mentor answers question
→ Student replies
→ Mentor shares resource if needed
→ Mentor marks request completed or leaves conversation active
```

Conversation Context Should Show

- topic
- request status
- student goal
- mentor role
- safety/report action

Rules

- Conversation uses Messaging system.
- Non-participants cannot access.
- Message privacy applies.
- Report/block available.
- Accepted mentor request unlocks messaging.

## 13. Content Sharing Flow

Flow

```txt
Mentor opens composer
→ Writes insight/resource post
→ Adds topic tag
→ Publishes
→ Students can read/comment/save
```

Content Types

| Type                       | Priority |
| -------------------------- | -------- |
| Learning advice            | P1       |
| Career direction           | P1       |
| FAQ answer                 | P2       |
| Resource list              | P2       |
| Event/session announcement | P2       |

Rules

- Resource sharing must respect copyright.
- Career content should be supportive, not recruiter spam.
- Mentor content can be highlighted but not dominate student feed.

## 14. Group Mentoring Flow

Priority: P2/P3.

```txt
Mentor creates session
→ Sets topic/time/capacity
→ Admin/community review if needed
→ Students register
→ Session happens
→ Follow-up resource shared
```

Not MVP-critical.

## 15. Safety Flow

Flow

```txt
Mentor sees inappropriate request/message
→ Report
→ Optionally block or close request
→ Admin review
```

Rules

- Mentor can report unsafe requests.
- Mentor can pause receiving requests.
- Mentor can close conversation if policy supports it.
- Mentor/alumni/advisor status does not exempt user from moderation.

## 16. Advisor Page Map

| Flow Area    | Required Pages                              |
| ------------ | ------------------------------------------- |
| Setup        | mentor-profile.md, profile-edit.md          |
| Status       | account-status.md                           |
| Requests     | mentor-request.md, mentor-request-detail.md |
| Conversation | conversation.md                             |
| Content      | composer.md, post-detail.md                 |
| Settings     | mentor-settings.md, settings.md             |
| Safety       | safety-reporting.md                         |

## 17. Critical States

| State                  | Where                  |
| ---------------------- | ---------------------- |
| Draft mentor profile   | Setup                  |
| Pending verification   | Account status         |
| Approved mentor        | Mentor profile         |
| Rejected mentor        | Account status         |
| Need more info         | Account/request status |
| Paused availability    | Mentor settings        |
| Request pending        | Request list           |
| Request accepted       | Request detail         |
| Request declined       | Request detail         |
| Need more info request | Request detail         |
| Conversation active    | Messaging              |
| Request completed      | Request detail         |
| Report submitted       | Safety                 |
| Mentor access revoked  | Mentor settings/status |

## 18. UX Risks

| Risk                            | Prevention                   |
| ------------------------------- | ---------------------------- |
| Mentor flow quá giống LinkedIn  | Focus support topics, not CV |
| Mentor quá tải                  | Availability + request cap   |
| Request quá mơ hồ               | Structured request form      |
| Student bị từ chối khó chịu     | Polite decline template      |
| Mentor không biết student là ai | Student context card         |
| Mentor profile quá khoe         | Short, useful, human copy    |
| Không an toàn                   | Report/block/close request   |
| Admin duyệt thiếu kỹ            | Mentor verification flow     |

## 19. QA Checklist

```

```
