# UEConnect State Machine Source of Truth

## 1. Purpose

File này là nguồn chuẩn cho toàn bộ trạng thái chính trong UEConnect.

Mọi module phải tuân theo state machine này khi:
- thiết kế database enum
- viết business logic
- viết service/action
- viết policy/gate
- viết test case
- viết UI empty/error/status state
- viết admin/moderation action
- viết analytics event

Nếu state trong code khác file này, code sai.

---

# 2. Global Rules

## 2.1. State Naming

Tất cả state dùng `snake_case`.

Ví dụ đúng:

```txt
pending_review
needs_more_information
hidden_by_moderation
removed_by_moderation
````

Ví dụ sai:

```txt
PendingReview
needMoreInfo
hidden-by-mod
```

## 2.2. Status Field Type

Trong database, status nên dùng string enum.

Ví dụ:

```txt
users.account_status
verification_requests.status
greetings.status
mentor_requests.status
messages.status
reports.status
moderation_cases.status
communities.status
```

## 2.3. Soft Delete Rule

Soft delete không thay thế status.

Ví dụ:

```txt
status = removed_by_moderation
deleted_at = null
```

hoặc:

```txt
status = deleted
deleted_at = 2026-05-26 10:00:00
```

Status mô tả nghiệp vụ. `deleted_at` mô tả lifecycle database.

## 2.4. Terminal State

Terminal state là state không được chuyển tiếp bình thường nữa, trừ admin/system restore đặc biệt.

Ví dụ terminal:

```txt
banned
deleted
removed_by_moderation
expired
```

## 2.5. Transition Rule

Mọi transition phải đi qua service/action.

Không update status trực tiếp trong controller.

Đúng:

```txt
ApproveVerificationAction
AcceptGreetingAction
SuspendCommunityAction
ResolveReportAction
```

Sai:

```php
$model->status = 'approved';
$model->save();
```

Con người đã phát minh service layer để tránh controller thành bãi rác. Ta nên dùng nó.

---

# 3. Account Status Machine

## 3.1. Entity

```txt
users.account_status
```

## 3.2. States

| State                  | Meaning                               |        Can Login | Can Use App | Notes                             |
| ---------------------- | ------------------------------------- | ---------------: | ----------: | --------------------------------- |
| `registered`           | Account created but not verified      |              Yes |          No | Redirect verification             |
| `pending_verification` | Verification submitted                |              Yes |          No | Waiting admin                     |
| `active`               | Verified and usable                   |              Yes |         Yes | Normal state                      |
| `profile_incomplete`   | Verified but required profile missing |              Yes |     Limited | Redirect onboarding/profile setup |
| `restricted`           | Limited by system/moderation          |              Yes |     Limited | Cannot post/message/etc           |
| `suspended`            | Temporarily disabled                  | Yes/No by policy |          No | Show account status               |
| `banned`               | Permanently banned                    |               No |          No | Terminal                          |
| `deleted`              | Soft deleted/deactivated              |               No |          No | Terminal                          |

## 3.3. Events

| Event                  | From                   | To                              | Actor             |
| ---------------------- | ---------------------- | ------------------------------- | ----------------- |
| `register`             | none                   | `registered`                    | user              |
| `submit_verification`  | `registered`           | `pending_verification`          | user              |
| `approve_verification` | `pending_verification` | `profile_incomplete`            | admin             |
| `complete_profile`     | `profile_incomplete`   | `active`                        | user              |
| `restrict_account`     | `active`               | `restricted`                    | admin/system      |
| `lift_restriction`     | `restricted`           | `active`                        | admin/system      |
| `suspend_account`      | any non-terminal       | `suspended`                     | admin/moderator   |
| `reactivate_account`   | `suspended`            | `active` / `profile_incomplete` | admin             |
| `ban_account`          | any non-terminal       | `banned`                        | admin             |
| `delete_account`       | any non-terminal       | `deleted`                       | user/admin/system |

## 3.4. Guards

```txt
approve_verification requires verification.status = approved
complete_profile requires required fields complete
reactivate_account requires ban not active
banned cannot transition except exceptional admin restore policy
deleted cannot transition except system restore policy
```

## 3.5. Side Effects

| Transition             | Side Effects                                         |
| ---------------------- | ---------------------------------------------------- |
| `approve_verification` | send notification, create audit, analytics           |
| `complete_profile`     | unlock `/app/home`                                   |
| `restrict_account`     | block post/message/greeting/mentor/community actions |
| `suspend_account`      | invalidate active sessions optionally                |
| `ban_account`          | invalidate sessions, hide from discovery/search      |
| `delete_account`       | anonymize or soft-delete related data by policy      |

---

# 4. Verification Status Machine

## 4.1. Entity

```txt
verification_requests.status
```

## 4.2. States

| State                    | Meaning                                 |
| ------------------------ | --------------------------------------- |
| `not_submitted`          | User has not submitted verification     |
| `draft`                  | User started but not submitted          |
| `pending_review`         | Submitted and waiting                   |
| `under_review`           | Admin is reviewing                      |
| `needs_more_information` | Admin requests more info                |
| `resubmitted`            | User resubmitted after more info/reject |
| `approved`               | Verification approved                   |
| `rejected`               | Verification rejected                   |
| `conflict`               | MSSV/email/identity conflict            |
| `suspicious`             | Suspicious evidence/account             |
| `expired`                | Verification request expired            |
| `cancelled`              | User/admin cancelled                    |

## 4.3. Events

| Event                      | From                                                              | To                       | Actor      |
| -------------------------- | ----------------------------------------------------------------- | ------------------------ | ---------- |
| `start_verification`       | `not_submitted`                                                   | `draft`                  | user       |
| `submit_verification`      | `draft` / `not_submitted` / `rejected` / `needs_more_information` | `pending_review`         | user       |
| `start_review`             | `pending_review` / `resubmitted`                                  | `under_review`           | admin      |
| `request_more_information` | `under_review`                                                    | `needs_more_information` | admin      |
| `resubmit`                 | `needs_more_information` / `rejected`                             | `resubmitted`            | user       |
| `approve`                  | `under_review` / `resubmitted`                                    | `approved`               | admin      |
| `reject`                   | `under_review` / `resubmitted`                                    | `rejected`               | admin      |
| `mark_conflict`            | `under_review`                                                    | `conflict`               | admin      |
| `suspend_suspicious`       | `under_review` / `conflict`                                       | `suspicious`             | admin      |
| `expire`                   | `pending_review` / `needs_more_information` / `rejected`          | `expired`                | system     |
| `cancel`                   | non-terminal                                                      | `cancelled`              | user/admin |

## 4.4. Guards

```txt
submit_verification requires evidence_count <= 3
submit_verification requires each file <= 5MB
submit_verification allows jpg/jpeg/png/pdf/webp/link
approve requires unique MSSV
approve requires verified HCMUE email or valid evidence
approve requires admin reason
reject requires admin reason
needs_more_information requires admin instruction
mark_conflict requires conflict reason
```

## 4.5. Side Effects

| Transition               | Side Effects                              |
| ------------------------ | ----------------------------------------- |
| `submit_verification`    | notify admin, analytics                   |
| `approve`                | update account status, notify user, audit |
| `reject`                 | notify user, keep evidence, audit         |
| `needs_more_information` | notify user with instruction              |
| `mark_conflict`          | lock duplicate MSSV resolution            |
| `suspicious`             | restrict/suspend account if needed        |

---

# 5. Greeting Status Machine

## 5.1. Entity

```txt
greetings.status
```

## 5.2. States

| State                   | Meaning                |
| ----------------------- | ---------------------- |
| `not_sent`              | No greeting exists     |
| `pending`               | Greeting sent, waiting |
| `accepted`              | Receiver accepted      |
| `declined`              | Receiver declined      |
| `cancelled`             | Sender cancelled       |
| `expired`               | Pending too long       |
| `reported`              | Greeting reported      |
| `blocked`               | Invalidated by block   |
| `removed_by_moderation` | Removed by moderation  |

## 5.3. Events

| Event                  | From                                | To                      | Actor           |
| ---------------------- | ----------------------------------- | ----------------------- | --------------- |
| `send_greeting`        | `not_sent`                          | `pending`               | sender          |
| `accept_greeting`      | `pending`                           | `accepted`              | receiver        |
| `decline_greeting`     | `pending`                           | `declined`              | receiver        |
| `cancel_greeting`      | `pending`                           | `cancelled`             | sender          |
| `expire_greeting`      | `pending`                           | `expired`               | system          |
| `report_greeting`      | `pending` / `accepted` / `declined` | `reported`              | sender/receiver |
| `block_user`           | any non-terminal                    | `blocked`               | sender/receiver |
| `remove_by_moderation` | any non-terminal                    | `removed_by_moderation` | moderator/admin |

## 5.4. Guards

```txt
sender must be verified active
receiver must be verified active
sender != receiver
no block either direction
no active connection already exists
no duplicate pending greeting
declined locks resend by default
accepted transition must be idempotent
```

## 5.5. Side Effects

| Transition         | Side Effects                                          |
| ------------------ | ----------------------------------------------------- |
| `send_greeting`    | notify receiver, analytics                            |
| `accept_greeting`  | create connection, create conversation, notify sender |
| `decline_greeting` | update status, optional silent notification           |
| `report_greeting`  | create report, auto-block if policy                   |
| `block_user`       | remove connection, hide discovery, restrict messaging |

---

# 6. Connection Status Machine

## 6.1. Entity

```txt
connections.status
```

## 6.2. States

| State              | Meaning                      |
| ------------------ | ---------------------------- |
| `none`             | No relation                  |
| `pending_greeting` | Greeting pending             |
| `active`           | Connected                    |
| `removed`          | Connection removed           |
| `blocked`          | Block relation exists        |
| `restricted`       | One/both accounts restricted |
| `archived`         | Historical relation          |

## 6.3. Events

| Event                | From                        | To                 | Actor           |
| -------------------- | --------------------------- | ------------------ | --------------- |
| `greeting_sent`      | `none`                      | `pending_greeting` | sender          |
| `greeting_accepted`  | `pending_greeting` / `none` | `active`           | receiver/system |
| `remove_connection`  | `active`                    | `removed`          | either user     |
| `block_user`         | any                         | `blocked`          | either user     |
| `restrict_account`   | `active`                    | `restricted`       | system/admin    |
| `restore_connection` | `removed` / `restricted`    | `active`           | system/admin    |
| `archive_connection` | `removed`                   | `archived`         | system          |

## 6.4. Guards

```txt
only one active connection per normalized pair
blocked overrides all connection states
restricted account cannot create new active connection
restore requires no block
```

## 6.5. Side Effects

| Transition           | Side Effects                            |
| -------------------- | --------------------------------------- |
| `greeting_accepted`  | create direct conversation              |
| `remove_connection`  | restrict direct messaging               |
| `block_user`         | restrict messaging, discovery, greeting |
| `restore_connection` | reactivate conversation if allowed      |

---

# 7. Message Status Machine

## 7.1. Entity

```txt
messages.status
```

## 7.2. States

| State                   | Meaning                                   |
| ----------------------- | ----------------------------------------- |
| `draft`                 | Client/local only, not persisted          |
| `sending`               | Client pending                            |
| `sent`                  | Stored in database                        |
| `delivered`             | Delivered to recipient/session if tracked |
| `read`                  | Read by recipient                         |
| `edited`                | Edited by sender                          |
| `deleted_by_sender`     | Hidden by sender action                   |
| `hidden_by_moderation`  | Hidden by moderator/system                |
| `removed_by_moderation` | Removed by moderator                      |
| `failed`                | Client/server send failed                 |
| `blocked`               | Prevented by block/restriction            |

## 7.3. Events

| Event                  | From                          | To                      | Actor            |
| ---------------------- | ----------------------------- | ----------------------- | ---------------- |
| `send_message`         | `draft` / `sending`           | `sent`                  | sender           |
| `deliver_message`      | `sent`                        | `delivered`             | system           |
| `mark_read`            | `sent` / `delivered`          | `read`                  | receiver/system  |
| `edit_message`         | `sent` / `delivered` / `read` | `edited`                | sender           |
| `delete_message`       | any visible state             | `deleted_by_sender`     | sender           |
| `hide_by_moderation`   | any visible state             | `hidden_by_moderation`  | moderator/system |
| `remove_by_moderation` | any visible state             | `removed_by_moderation` | moderator/admin  |
| `fail_message`         | `sending`                     | `failed`                | system           |
| `block_message`        | `sending` / `sent`            | `blocked`               | system           |

## 7.4. Guards

```txt
sender must be active participant
conversation must be active
no block either direction
edit only by sender
delete only by sender or moderator
edit window default = 24h
message body max = 2000
attachments must pass media policy
```

## 7.5. Side Effects

| Transition           | Side Effects                       |
| -------------------- | ---------------------------------- |
| `send_message`       | broadcast realtime after DB commit |
| `mark_read`          | update participant read state      |
| `edit_message`       | broadcast message edited           |
| `delete_message`     | show placeholder                   |
| `hide_by_moderation` | create moderation record           |
| `blocked`            | restrict conversation              |

---

# 8. Mentor Request Status Machine

## 8.1. Entity

```txt
mentor_requests.status
```

## 8.2. States

| State                    | Meaning                              |
| ------------------------ | ------------------------------------ |
| `draft`                  | Student started request              |
| `pending`                | Waiting mentor response              |
| `needs_more_information` | Mentor asks for details              |
| `updated_by_student`     | Student updated after request        |
| `accepted`               | Mentor accepted                      |
| `declined`               | Mentor declined                      |
| `cancelled_by_student`   | Student cancelled                    |
| `expired`                | Request expired                      |
| `completed`              | Mentor interaction completed         |
| `reported`               | Request reported                     |
| `restricted`             | Block/account/moderation restriction |
| `removed_by_moderation`  | Removed                              |

## 8.3. Events

| Event                  | From                                                        | To                       | Actor                 |
| ---------------------- | ----------------------------------------------------------- | ------------------------ | --------------------- |
| `start_request`        | none                                                        | `draft`                  | student               |
| `submit_request`       | `draft`                                                     | `pending`                | student               |
| `ask_more_information` | `pending` / `updated_by_student`                            | `needs_more_information` | mentor                |
| `update_request`       | `needs_more_information`                                    | `updated_by_student`     | student               |
| `accept_request`       | `pending` / `updated_by_student`                            | `accepted`               | mentor                |
| `decline_request`      | `pending` / `updated_by_student`                            | `declined`               | mentor                |
| `cancel_request`       | `pending` / `needs_more_information` / `updated_by_student` | `cancelled_by_student`   | student               |
| `expire_request`       | `pending` / `needs_more_information`                        | `expired`                | system                |
| `complete_session`     | `accepted`                                                  | `completed`              | mentor/student/system |
| `report_request`       | any non-terminal                                            | `reported`               | participant           |
| `restrict_request`     | any non-terminal                                            | `restricted`             | system/admin          |
| `remove_by_moderation` | any non-terminal                                            | `removed_by_moderation`  | moderator/admin       |

## 8.4. Guards

```txt
student must be verified active
mentor must be approved active mentor
mentor availability must allow request
no duplicate pending request
student pending limit not exceeded
mentor pending limit not exceeded
topic/question/goal/urgency required
accept must create conversation idempotently
```

## 8.5. Side Effects

| Transition             | Side Effects                               |
| ---------------------- | ------------------------------------------ |
| `submit_request`       | notify mentor                              |
| `ask_more_information` | notify student                             |
| `accept_request`       | create mentor conversation, notify student |
| `decline_request`      | notify student                             |
| `complete_session`     | allow feedback                             |
| `report_request`       | create report, possible block              |

---

# 9. Community Status Machine

## 9.1. Entity

```txt
communities.status
```

## 9.2. States

| State                  | Meaning                           |
| ---------------------- | --------------------------------- |
| `draft`                | Created but not public            |
| `pending_review`       | Suggested or waiting admin review |
| `active`               | Normal operation                  |
| `inactive`             | Visible but low/no activity       |
| `suspended`            | Locked by admin/moderation        |
| `archived`             | Historical/read-only              |
| `hidden_by_moderation` | Hidden due to safety              |
| `deleted`              | Soft deleted                      |

## 9.3. Events

| Event                  | From                                  | To                     | Actor           |
| ---------------------- | ------------------------------------- | ---------------------- | --------------- |
| `create_community`     | none                                  | `draft`                | admin           |
| `submit_for_review`    | `draft`                               | `pending_review`       | user/admin      |
| `approve_community`    | `pending_review`                      | `active`               | admin           |
| `publish_community`    | `draft`                               | `active`               | admin           |
| `mark_inactive`        | `active`                              | `inactive`             | admin/system    |
| `reactivate_community` | `inactive` / `suspended` / `archived` | `active`               | admin           |
| `suspend_community`    | any non-terminal                      | `suspended`            | admin/moderator |
| `archive_community`    | `active` / `inactive`                 | `archived`             | admin           |
| `hide_by_moderation`   | any non-terminal                      | `hidden_by_moderation` | moderator/admin |
| `delete_community`     | any non-terminal                      | `deleted`              | admin           |

## 9.4. Guards

```txt
publish requires name, type, description, visibility, join_policy
suspend requires reason
archive requires reason
reactivate requires no unresolved critical moderation case
deleted is terminal unless admin restore policy exists
```

## 9.5. Side Effects

| Transition             | Side Effects                    |
| ---------------------- | ------------------------------- |
| `active`               | allow join/post/resource/chat   |
| `suspended`            | disable join/post/resource/chat |
| `archived`             | read-only or hidden             |
| `hidden_by_moderation` | hide from search/discovery      |
| `delete_community`     | soft delete dependent surfaces  |

---

# 10. Community Membership Status Machine

## 10.1. Entity

```txt
community_members.status
```

## 10.2. States

| State                   | Meaning                       |
| ----------------------- | ----------------------------- |
| `none`                  | No membership                 |
| `invited`               | Invited by manager/admin      |
| `pending`               | Join request waiting          |
| `active`                | Member                        |
| `rejected`              | Join rejected                 |
| `left`                  | User left                     |
| `removed`               | Removed by manager/admin      |
| `muted`                 | Can view but cannot post/chat |
| `restricted`            | Limited by moderation         |
| `banned_from_community` | Cannot rejoin                 |

## 10.3. Events

| Event                 | From                               | To                      | Actor           |
| --------------------- | ---------------------------------- | ----------------------- | --------------- |
| `invite_member`       | `none`                             | `invited`               | manager/admin   |
| `request_join`        | `none` / `left` / `rejected`       | `pending`               | user            |
| `join_open_community` | `none` / `left`                    | `active`                | user            |
| `approve_join`        | `pending`                          | `active`                | manager/admin   |
| `reject_join`         | `pending`                          | `rejected`              | manager/admin   |
| `accept_invite`       | `invited`                          | `active`                | user            |
| `leave_community`     | `active` / `muted`                 | `left`                  | user            |
| `remove_member`       | `active` / `muted` / `restricted`  | `removed`               | manager/admin   |
| `mute_member`         | `active`                           | `muted`                 | manager/admin   |
| `restrict_member`     | `active` / `muted`                 | `restricted`            | moderator/admin |
| `ban_from_community`  | any non-terminal                   | `banned_from_community` | admin/moderator |
| `restore_member`      | `muted` / `restricted` / `removed` | `active`                | admin/manager   |

## 10.4. Guards

```txt
community must be active
user must be verified active
banned_from_community cannot request join
manager action requires scoped permission
owner removal requires admin permission
```

---

# 11. Report Status Machine

## 11.1. Entity

```txt
reports.status
```

## 11.2. States

| State                    | Meaning                        |
| ------------------------ | ------------------------------ |
| `submitted`              | User submitted report          |
| `triaged`                | System/admin categorized       |
| `in_review`              | Moderator reviewing            |
| `needs_more_information` | More info needed               |
| `actioned`               | Action taken                   |
| `dismissed`              | No violation                   |
| `duplicated`             | Duplicate report               |
| `escalated`              | Escalated to higher admin      |
| `closed`                 | Final closed                   |
| `reopened`               | Reopened after appeal/new info |

## 11.3. Events

| Event                      | From                                    | To                       | Actor            |
| -------------------------- | --------------------------------------- | ------------------------ | ---------------- |
| `submit_report`            | none                                    | `submitted`              | user             |
| `triage_report`            | `submitted`                             | `triaged`                | system/moderator |
| `start_review`             | `submitted` / `triaged` / `reopened`    | `in_review`              | moderator        |
| `request_more_information` | `in_review`                             | `needs_more_information` | moderator        |
| `provide_more_information` | `needs_more_information`                | `in_review`              | reporter         |
| `take_action`              | `in_review`                             | `actioned`               | moderator        |
| `dismiss_report`           | `in_review`                             | `dismissed`              | moderator        |
| `mark_duplicate`           | `submitted` / `triaged`                 | `duplicated`             | moderator/system |
| `escalate_report`          | any non-terminal                        | `escalated`              | moderator        |
| `close_report`             | `actioned` / `dismissed` / `duplicated` | `closed`                 | system/moderator |
| `reopen_report`            | `closed` / `dismissed`                  | `reopened`               | moderator/admin  |

## 11.4. Guards

```txt
one active report per reporter per target
reason required
description optional
report target must exist
reported target cannot be reporter if target type user/profile
```

## 11.5. Side Effects

| Transition        | Side Effects                                  |
| ----------------- | --------------------------------------------- |
| `submit_report`   | auto-block if policy, notify moderation queue |
| `take_action`     | create moderation action                      |
| `dismiss_report`  | notify reporter optionally                    |
| `escalate_report` | notify admin/moderator                        |
| `closed`          | prevent duplicate active reports              |

---

# 12. Moderation Case Status Machine

## 12.1. Entity

```txt
moderation_cases.status
```

## 12.2. States

| State                 | Meaning                             |
| --------------------- | ----------------------------------- |
| `queued`              | Waiting in queue                    |
| `in_review`           | Moderator reviewing                 |
| `waiting_for_context` | Needs more evidence/context         |
| `action_required`     | Violation confirmed, action pending |
| `actioned`            | Action applied                      |
| `dismissed`           | No action needed                    |
| `escalated`           | Higher review needed                |
| `appealed`            | User appealed                       |
| `appeal_in_review`    | Appeal being reviewed               |
| `resolved`            | Final resolved                      |
| `reopened`            | Reopened                            |

## 12.3. Events

| Event                 | From                                   | To                    | Actor            |
| --------------------- | -------------------------------------- | --------------------- | ---------------- |
| `enqueue_case`        | none                                   | `queued`              | system           |
| `start_review`        | `queued` / `reopened`                  | `in_review`           | moderator        |
| `request_context`     | `in_review`                            | `waiting_for_context` | moderator        |
| `context_received`    | `waiting_for_context`                  | `in_review`           | system/moderator |
| `confirm_violation`   | `in_review`                            | `action_required`     | moderator        |
| `apply_action`        | `action_required`                      | `actioned`            | moderator        |
| `dismiss_case`        | `in_review`                            | `dismissed`           | moderator        |
| `escalate_case`       | any non-terminal                       | `escalated`           | moderator        |
| `submit_appeal`       | `actioned` / `resolved`                | `appealed`            | target user      |
| `start_appeal_review` | `appealed`                             | `appeal_in_review`    | admin/moderator  |
| `resolve_appeal`      | `appeal_in_review`                     | `resolved`            | admin/moderator  |
| `resolve_case`        | `actioned` / `dismissed` / `escalated` | `resolved`            | system/moderator |
| `reopen_case`         | `resolved`                             | `reopened`            | admin/moderator  |

## 12.4. Moderation Actions

```txt
dismiss
hide
delete
restore
warn
suspend
ban
```

All action types require reason.

Không có “tôi thấy vậy” làm reason. Máy chủ cũng có tiêu chuẩn sống tối thiểu.

---

# 13. Content Moderation Status Machine

## 13.1. Entity

Applies to:

```txt
posts.moderation_status
comments.moderation_status
messages.moderation_status
profiles.moderation_status
community_resources.status
community_posts.status
```

## 13.2. States

| State          | Meaning                       |
| -------------- | ----------------------------- |
| `clean`        | Normal                        |
| `flagged`      | Keyword/report/system flagged |
| `auto_hidden`  | Auto-hidden by threshold      |
| `under_review` | Moderator reviewing           |
| `hidden`       | Hidden but restorable         |
| `removed`      | Removed by moderation         |
| `restored`     | Restored after review         |
| `locked`       | No further interaction        |
| `appealed`     | Appeal submitted              |

## 13.3. Events

| Event             | From                                 | To             | Actor           |
| ----------------- | ------------------------------------ | -------------- | --------------- |
| `flag_content`    | `clean`                              | `flagged`      | system/user     |
| `auto_hide`       | `flagged` / `clean`                  | `auto_hidden`  | system          |
| `start_review`    | `flagged` / `auto_hidden`            | `under_review` | moderator       |
| `hide_content`    | `under_review` / `flagged`           | `hidden`       | moderator       |
| `remove_content`  | `under_review` / `hidden`            | `removed`      | moderator       |
| `restore_content` | `hidden` / `auto_hidden` / `removed` | `restored`     | moderator/admin |
| `lock_content`    | any visible state                    | `locked`       | moderator/admin |
| `appeal_content`  | `hidden` / `removed` / `locked`      | `appealed`     | owner           |

---

# 14. Notification Status Machine

## 14.1. Entity

```txt
notifications.status
notifications.delivery_status
```

## 14.2. Notification Read States

| State      | Meaning                 |
| ---------- | ----------------------- |
| `unread`   | Created and unread      |
| `read`     | User read/opened        |
| `archived` | Hidden from active list |
| `expired`  | Older than retention    |
| `deleted`  | Soft deleted            |

## 14.3. Delivery States

| State                | Meaning             |
| -------------------- | ------------------- |
| `pending`            | Waiting delivery    |
| `broadcasted`        | Realtime event sent |
| `push_sent`          | Browser push sent   |
| `push_failed`        | Browser push failed |
| `push_denied`        | User/browser denied |
| `push_not_supported` | Browser unsupported |

## 14.4. Events

| Event                  | From              | To         | Actor       |
| ---------------------- | ----------------- | ---------- | ----------- |
| `create_notification`  | none              | `unread`   | system      |
| `mark_read`            | `unread`          | `read`     | user        |
| `mark_all_read`        | `unread`          | `read`     | user        |
| `archive_notification` | `read` / `unread` | `archived` | user/system |
| `expire_notification`  | `read` / `unread` | `expired`  | system      |
| `delete_notification`  | any non-terminal  | `deleted`  | user/system |

Retention:

```txt
expires_at = created_at + 7 days
```

---

# 15. Community Resource Status Machine

## 15.1. Entity

```txt
community_resources.status
```

## 15.2. States

| State                   | Meaning              |
| ----------------------- | -------------------- |
| `draft`                 | User draft           |
| `pending_review`        | Submitted, waiting   |
| `published`             | Visible              |
| `rejected`              | Rejected by reviewer |
| `hidden_by_moderation`  | Hidden               |
| `removed_by_moderation` | Removed              |
| `archived`              | Historical           |
| `deleted`               | Soft deleted         |

## 15.3. Events

| Event              | From             | To                      | Actor           |
| ------------------ | ---------------- | ----------------------- | --------------- |
| `submit_resource`  | `draft` / none   | `pending_review`        | member          |
| `approve_resource` | `pending_review` | `published`             | manager/admin   |
| `reject_resource`  | `pending_review` | `rejected`              | manager/admin   |
| `hide_resource`    | `published`      | `hidden_by_moderation`  | moderator/admin |
| `remove_resource`  | any non-terminal | `removed_by_moderation` | moderator/admin |
| `archive_resource` | `published`      | `archived`              | manager/admin   |
| `delete_resource`  | any non-terminal | `deleted`               | author/admin    |

Guards:

```txt
copyright_attestation required
file/link must pass media/resource validation
reviewer must have scoped permission
```

---

# 16. Career Pathway Status Machine

## 16.1. Entity

```txt
career_pathways.status
```

## 16.2. States

| State                  | Meaning                 |
| ---------------------- | ----------------------- |
| `draft`                | Admin editing           |
| `reviewing`            | Internal review         |
| `source_verified`      | Source checked          |
| `published`            | Visible                 |
| `needs_review`         | Source/content outdated |
| `archived`             | Hidden historical       |
| `hidden_by_moderation` | Hidden due to issue     |
| `deleted`              | Soft deleted            |

## 16.3. Events

| Event                | From                            | To                     | Actor           |
| -------------------- | ------------------------------- | ---------------------- | --------------- |
| `create_pathway`     | none                            | `draft`                | admin           |
| `submit_review`      | `draft`                         | `reviewing`            | admin           |
| `verify_source`      | `reviewing`                     | `source_verified`      | admin           |
| `publish_pathway`    | `source_verified` / `reviewing` | `published`            | admin           |
| `mark_needs_review`  | `published`                     | `needs_review`         | admin/system    |
| `archive_pathway`    | `published` / `needs_review`    | `archived`             | admin           |
| `hide_by_moderation` | any non-terminal                | `hidden_by_moderation` | moderator/admin |
| `restore_pathway`    | `archived` / `needs_review`     | `published`            | admin           |
| `delete_pathway`     | any non-terminal                | `deleted`              | admin           |

---

# 17. Search Result Visibility State

Search result itself không cần lưu DB status riêng, nhưng phải có computed visibility.

## 17.1. Computed States

| State         | Meaning                       |
| ------------- | ----------------------------- |
| `visible`     | Can show                      |
| `redacted`    | Show limited safe preview     |
| `locked`      | Exists but user cannot access |
| `hidden`      | Do not show                   |
| `unavailable` | Deleted/removed/stale         |

## 17.2. Rules

```txt
blocked target -> hidden
private community non-member -> locked or hidden by policy
hidden_by_moderation -> hidden
deleted -> unavailable if clicked from stale result
suspended user -> hidden
```

---

# 18. Cross-machine Rules

## 18.1. Account Overrides Everything

If:

```txt
users.account_status in suspended, banned, deleted
```

Then user cannot:

```txt
send greeting
send message
create post
comment
join community
send mentor request
submit resource
```

## 18.2. Block Overrides Relationship

If block exists:

```txt
greeting -> blocked
connection -> blocked
conversation -> restricted
discovery -> hidden
search result -> hidden
```

## 18.3. Moderation Overrides Visibility

If content is:

```txt
hidden_by_moderation
removed_by_moderation
auto_hidden
```

Then:

```txt
normal users see placeholder or nothing
moderators see review context
analytics must not store raw content
```

## 18.4. Verification Unlocks Product

```txt
registered
→ pending_verification
→ profile_incomplete
→ active
```

Only `active` users get full app access.

## 18.5. Accepted Greeting Creates Connection and Conversation

```txt
greeting.pending
→ greeting.accepted
→ connection.active
→ conversation.active
```

This must be transactional.

## 18.6. Accepted Mentor Request Creates Conversation

```txt
mentor_request.pending
→ mentor_request.accepted
→ conversation.active
```

This must be transactional.

## 18.7. Suspended Community Locks Children

If:

```txt
communities.status = suspended
```

Then:

```txt
community feed posting disabled
community chat sending disabled
community join disabled
community resource submission disabled
```

Existing content may remain read-only or hidden by policy.

---

# 19. Required Test Pattern

Every state machine must have tests for:

```txt
valid transitions
invalid transitions
permission guards
side effects
idempotency
terminal state protection
concurrency
audit creation when required
notification when required
analytics when required
```

Example:

```txt
TC-GREETING-ACCEPT-001:
Given greeting.status = pending
When receiver accepts
Then greeting.status = accepted
And connection.status = active
And conversation.status = active
And sender receives notification
And no duplicate connection is created
```

---

# 20. Implementation Rule

Every module should define:

```txt
Status enum
Transition service/action
Policy guard
Validation guard
Side effect dispatcher
Test suite
```

Suggested structure:

```txt
app/Enums
app/Actions
app/Policies
app/Events
app/Listeners
tests/Feature/StateMachines
```

Example:

```txt
app/Enums/GreetingStatus.php
app/Actions/Greetings/AcceptGreetingAction.php
app/Policies/GreetingPolicy.php
tests/Feature/StateMachines/GreetingStatusMachineTest.php
```

---

# 21. Final Lock

This document is the single source of truth for UEConnect state machines.

Do not create new states casually.

Before adding a new state:

```txt
1. Define business meaning.
2. Define allowed transitions.
3. Define guards.
4. Define side effects.
5. Define UI state.
6. Define test cases.
7. Update this document first.
```