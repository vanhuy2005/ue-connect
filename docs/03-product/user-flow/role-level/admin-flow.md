## 4. Admin Journey Overview

````txt
Admin login
→ Dashboard
→ Review urgent queues
→ Verification queue
→ Approve / reject / need more info
→ Moderation queue
→ Handle reports
→ User management
→ Suspend / ban / reactivate if needed
→ Permission management
→ Grant / revoke scoped permission
→ Community approval
→ Audit log review
→ Operational follow-up
## 15. System Announcement Flow

Flow

```txt
Open announcements
→ Create announcement
→ Select audience
→ Preview
→ Send or schedule
→ Track delivery/status
````

Audience Options

- all users
- students
- alumni
- mentors
- specific faculty
- specific community
- admin-only

Priority

System Announcement is P2 unless required for operation.

## 16. Audit Log Flow

Flow

```txt
Critical admin action occurs
→ System records audit log
→ Authorized audit viewer opens audit page
→ Search/filter by actor/action/target/date
→ Review action detail
```

Audit Must Record

- actor_id
- action
- target_type
- target_id
- timestamp
- reason
- previous_state
- new_state
- ip/device if needed

Rules

- Audit log is not debug log.
- Audit logs cannot be edited from UI.
- view_audit_log required.
- Critical action without audit should fail or raise incident.

## 17. Admin Page Map

| Flow Area     | Required Pages                                                             |
| ------------- | -------------------------------------------------------------------------- |
| Auth          | admin/auth.md                                                              |
| Dashboard     | admin/admin-dashboard.md                                                   |
| Verification  | admin/account-verification-review.md, admin/account-verification-detail.md |
| User          | admin/user-management.md, admin/user-detail.md                             |
| Moderation    | admin/moderation-queue.md, admin/report-detail.md                          |
| Policy        | admin/policy-management.md                                                 |
| Permission    | admin/permission-management.md                                             |
| Mentor/Alumni | admin/mentor-verification.md, admin/mentor-detail.md                       |
| Community     | admin/community-management.md, admin/community-detail.md                   |
| Announcement  | admin/system-announcements.md                                              |
| Audit         | admin/audit-log.md                                                         |

## 18. Critical States

| State                      | Where                      |
| -------------------------- | -------------------------- |
| Empty queue                | Verification / moderation  |
| High-risk report           | Moderation dashboard       |
| Duplicate student ID       | Verification detail        |
| Permission denied          | Admin route/action         |
| Action confirmation        | Destructive actions        |
| Action failed              | Admin actions              |
| Audit recorded             | After critical action      |
| User suspended             | User detail                |
| Content hidden             | Report detail              |
| Auto-hidden pending review | Moderation queue           |
| Stale request              | Verification/report detail |
| Evidence unavailable       | Verification detail        |

## 19. UX Risks

| Risk                         | Prevention                                       |
| ---------------------------- | ------------------------------------------------ |
| Duyệt nhầm tài khoản         | Detail review + duplicate warning + confirmation |
| Xóa nhầm nội dung            | Confirmation + reason + audit                    |
| Moderator abuse              | Permission scope + audit                         |
| Queue quá tải                | Filter, search, priority, risk level             |
| User không biết lý do reject | Required reason                                  |
| Admin panel quá rối          | Task-oriented dashboard                          |
| Thiếu context report         | Show content/user/report history                 |
| Evidence leak                | Private viewer + permission                      |
| Permission quá rộng          | Scoped permission                                |
| Audit thiếu                  | Transactional action + audit                     |

## 20. QA Checklist

- Admin dashboard task-oriented.
- Normal user không vào được admin route.
- Verification queue có filter/search.
- Evidence chỉ admin có quyền mới xem được.
- Approve/reject/need more info đều audit.
- Reject/need more info cần reason.
- Moderation queue có đủ context.
- Hide/delete/dismiss report cần reason.
- Permission grant/revoke cần reason và audit.
- Scoped permission hoạt động đúng.
- Admin không tự grant quyền cho chính mình.
- Audit viewer cần view_audit_log.
- High-risk reports được ưu tiên.
- User nhận notification phù hợp sau admin action.
  | Delete | Xóa theo moderation | Yes | Yes |
  | Restore | Khôi phục nội dung | Yes | Yes |
  | Warn | Cảnh báo user | Yes | Yes |
  | Suspend | Tạm khóa user | Yes | Yes |
  | Ban | Khóa nghiêm trọng | Yes | Yes |
  | Escalate | Chuyển cấp xử lý | Yes | Yes |
  | Request info | Cần thêm dữ liệu | Yes | Yes |

## 11. Content Moderation Flow

Flow

```txt
Open moderation queue
→ Filter by content type, risk level, status
→ Open content/report detail
→ Review content and context
→ Apply moderation action
→ Notify if needed
→ Audit action
```

Required Context

Admin cần thấy:

- reported content
- content author
- report reason
- report detail
- reporter identity if policy allows
- content history
- previous reports
- author account status
- community context
- related guideline
- previous admin actions

Rules

- Report is signal, not final violation.
- Keyword flagging is signal, not judgement.
- Auto-hide is temporary and reviewable.
- Scoped moderator cannot act outside scope.

## 12. Permission Management Flow

Flow

```txt
Open permission management
→ Search user
→ Select permission
→ Select scope if required
→ Enter reason
→ Confirm
→ Grant/revoke permission
→ Audit log created
```

Permission Types

- mentor_access
- manage_club
- moderate_community
- moderate_content
- review_verification
- manage_users
- manage_permissions
- approve_community
- view_audit_log
- manage_system_settings

Scoped Permissions

| Permission          | Scope                        |
| ------------------- | ---------------------------- |
| manage_club         | community_id                 |
| moderate_community  | community_id                 |
| mentor_access       | global or topic-based future |
| moderate_content    | global/module                |
| review_verification | global                       |

Rules

- Không self-grant.
- Permission grant/revoke phải có reason.
- Permission grant/revoke phải audit.
- Scoped permission phải check đúng scope.
- Account status override permission.

## 13. Mentor / Alumni Verification Flow

Flow

```txt
Open mentor/alumni review queue
→ Review profile/evidence/context
→ Approve / reject / need more info
→ Notify user
→ Audit action
```

UX Rules

- Không duyệt mentor mù.
- Cần thấy expertise, background, support topics.
- Mentor access là permission, không phải primary role.
- Alumni là primary role nếu verification xác nhận.
- Reject phải có reason.
- Mentor bị report có thể hidden/paused/suspended tùy policy.

## 14. Community Management Flow

Flow

```txt
Open community management
→ Review community creation request
→ Approve / reject / need more info
→ Assign Club Manager
→ Monitor community reports
→ Suspend/archive community if needed
→ Audit actions
```

Admin Actions

| Action                  | Permission                           |
| ----------------------- | ------------------------------------ |
| Approve community       | approve_community                    |
| Reject community        | approve_community                    |
| Suspend community       | approve_community / moderate_content |
| Assign Club Manager     | manage_permissions                   |
| Revoke Club Manager     | manage_permissions                   |
| Hide community post     | moderate_content or scoped moderator |
| Review copyright report | moderate_content                     |

Rules

- Community must be approved before public.
- Club Manager is scoped permission, not primary role.
- Community chat/resource library are future-ready, not forced into MVP.
  → View pending requests
  → Filter/search by status, name, email, MSSV
  → Open request detail
  → Review submitted info and evidence
  → Check duplicate MSSV
  → Approve / reject / need more information
  → System updates user/account status
  → User notified
  → Audit log created
  Admin Actions
  Action Result Reason Required Audit
  Approve User becomes active verified UEer Optional Yes
  Reject User sees rejection reason Yes Yes
  Need more info User can resubmit Yes Yes
  Flag duplicate Request marked risky Yes Yes
  Suspend suspicious account Account restricted Yes Yes
  Required UI
  queue table/list
  status filter
  search by name/email/MSSV
  request detail panel
  evidence viewer
  duplicate warning
  reason textarea
  confirmation modal
  audit note
  Rules
  Evidence is private.
  Full MSSV visible only in admin context.
  Approval must recheck MSSV uniqueness at submit time.
  Reject/need more info must have reason.
  Action without audit should fail or rollback. 9. User Management Flow
  Flow
  Open user management
  → Search/filter users
  → Open user detail
  → Review profile/account/report/activity summary
  → Take action if needed
  → Confirm reason
  → Apply action
  → Audit log created
  User Actions
  Action Priority Required Permission
  View user detail P0 manage_users
  Search user P0 manage_users
  Suspend account P0 manage_users
  Ban account P0 manage_users
  Reactivate account P0 manage_users
  Warn user P0 moderate_content or manage_users
  View report history P0 moderate_content / manage_users
  Reset verification status P1 review_verification + policy
  Deactivate account by policy P1 manage_users
  UX Rules
  Destructive actions require confirmation.
  Serious actions require reason.
  User status changes must be audited.
  Account status must immediately affect access.
  Admin should see enough context, not a scary wall of raw database values. 10. Report Moderation Flow
  Flow
  User submits report
  → Report enters moderation queue
  → Admin opens report detail
  → Reviews target, reporter, reason, context, history
  → Chooses moderation decision
  → System applies decision
  → Relevant users notified if needed
  → Audit log records action
  Report Types
  Type Target
  Post report Post
  Comment report Comment
  Profile report User profile
  Message report Message / conversation
  Discovery report Profile in Discovery
  Mentor report Mentor request / conversation
  Community report Community / community post
  Copyright report Resource/document future
  Admin Decisions
  Decision Meaning Reason Audit
  Dismiss Không vi phạm Yes Yes
  Hide Ẩn khỏi public Yes Yes
  Delete Xóa theo moderation Yes Yes
  Restore Khôi phục nội dung Yes Yes
  Warn Cảnh báo user Yes Yes
  Suspend Tạm khóa user Yes Yes
  Ban Khóa nghiêm trọng Yes Yes
  Escalate Chuyển cấp xử lý Yes Yes
  Request info Cần thêm dữ liệu Yes Yes 11. Content Moderation Flow
  Flow
  Open moderation queue
  → Filter by content type, risk level, status
  → Open content/report detail
  → Review content and context
  → Apply moderation action
  → Notify if needed
  → Audit action
  Required Context

Admin cần thấy:

reported content
content author
report reason
report detail
reporter identity if policy allows
content history
previous reports
author account status
community context
related guideline
previous admin actions
Rules
Report is signal, not final violation.
Keyword flagging is signal, not judgement.
Auto-hide is temporary and reviewable.
Scoped moderator cannot act outside scope. 12. Permission Management Flow
Flow
Open permission management
→ Search user
→ Select permission
→ Select scope if required
→ Enter reason
→ Confirm
→ Grant/revoke permission
→ Audit log created
Permission Types
mentor_access
manage_club
moderate_community
moderate_content
review_verification
manage_users
manage_permissions
approve_community
view_audit_log
manage_system_settings
Scoped Permissions
Permission Scope
manage_club community_id
moderate_community community_id
mentor_access global or topic-based future
moderate_content global/module
review_verification global
Rules
Không self-grant.
Permission grant/revoke phải có reason.
Permission grant/revoke phải audit.
Scoped permission phải check đúng scope.
Account status override permission. 13. Mentor / Alumni Verification Flow
Flow
Open mentor/alumni review queue
→ Review profile/evidence/context
→ Approve / reject / need more info
→ Notify user
→ Audit action
UX Rules
Không duyệt mentor mù.
Cần thấy expertise, background, support topics.
Mentor access là permission, không phải primary role.
Alumni là primary role nếu verification xác nhận.
Reject phải có reason.
Mentor bị report có thể hidden/paused/suspended tùy policy. 14. Community Management Flow
Flow
Open community management
→ Review community creation request
→ Approve / reject / need more info
→ Assign Club Manager
→ Monitor community reports
→ Suspend/archive community if needed
→ Audit actions
Admin Actions
Action Permission
Approve community approve_community
Reject community approve_community
Suspend community approve_community / moderate_content
Assign Club Manager manage_permissions
Revoke Club Manager manage_permissions
Hide community post moderate_content or scoped moderator
Review copyright report moderate_content
Rules
Community must be approved before public.
Club Manager is scoped permission, not primary role.
Community chat/resource library are future-ready, not forced into MVP. 15. System Announcement Flow
Flow
Open announcements
→ Create announcement
→ Select audience
→ Preview
→ Send or schedule
→ Track delivery/status
Audience Options
all users
students
alumni
mentors
specific faculty
specific community
admin-only
Priority

System Announcement is P2 unless required for operation.

16. Audit Log Flow
    Flow
    Critical admin action occurs
    → System records audit log
    → Authorized audit viewer opens audit page
    → Search/filter by actor/action/target/date
    → Review action detail
    Audit Must Record
    actor_id
    action
    target_type
    target_id
    timestamp
    reason
    previous_state
    new_state
    ip/device if needed
    Rules
    Audit log is not debug log.
    Audit logs cannot be edited from UI.
    view_audit_log required.
    Critical action without audit should fail or raise incident.
17. Admin Page Map
    Flow Area Required Pages
    Auth admin/auth.md
    Dashboard admin/admin-dashboard.md
    Verification admin/account-verification-review.md, admin/account-verification-detail.md
    User admin/user-management.md, admin/user-detail.md
    Moderation admin/moderation-queue.md, admin/report-detail.md
    Policy admin/policy-management.md
    Permission admin/permission-management.md
    Mentor/Alumni admin/mentor-verification.md, admin/mentor-detail.md
    Community admin/community-management.md, admin/community-detail.md
    Announcement admin/system-announcements.md
    Audit admin/audit-log.md
18. Critical States
    State Where
    Empty queue Verification / moderation
    High-risk report Moderation dashboard
    Duplicate student ID Verification detail
    Permission denied Admin route/action
    Action confirmation Destructive actions
    Action failed Admin actions
    Audit recorded After critical action
    User suspended User detail
    Content hidden Report detail
    Auto-hidden pending review Moderation queue
    Stale request Verification/report detail
    Evidence unavailable Verification detail
19. UX Risks
    Risk Prevention
    Duyệt nhầm tài khoản Detail review + duplicate warning + confirmation
    Xóa nhầm nội dung Confirmation + reason + audit
    Moderator abuse Permission scope + audit
    Queue quá tải Filter, search, priority, risk level
    User không biết lý do reject Required reason
    Admin panel quá rối Task-oriented dashboard
    Thiếu context report Show content/user/report history
    Evidence leak Private viewer + permission
    Permission quá rộng Scoped permission
    Audit thiếu Transactional action + audit
20. QA Checklist
    Admin dashboard task-oriented.
    Normal user không vào được admin route.
    Verification queue có filter/search.
    Evidence chỉ admin có quyền mới xem được.
    Approve/reject/need more info đều audit.
    Reject/need more info cần reason.
    Moderation queue có đủ context.
    Hide/delete/dismiss report cần reason.
    Permission grant/revoke cần reason và audit.
    Scoped permission hoạt động đúng.
    Admin không tự grant quyền cho chính mình.
    Audit viewer cần view_audit_log.
    High-risk reports được ưu tiên.
    User nhận notification phù hợp sau admin action.
