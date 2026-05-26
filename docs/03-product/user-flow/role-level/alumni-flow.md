---

title: "Alumni Flow"
module: "03-product/user-flow/role-level"

## 3. Alumni Journey Overview

```txt
Alumni registers or updates role
→ Submits alumni verification
→ Admin reviews
→ Approved / rejected / need more info
→ Creates alumni profile
→ Reads/posts in feed
→ Optionally enables mentor role
→ Receives mentor requests if approved
→ Joins alumni/faculty/community spaces
→ Shares insights/opportunities with moderation
→ Manages privacy/safety
```

## 4. Entry Points

Alumni có thể vào flow từ:

- signup with alumni role
- student account transitions to alumni after graduation
- admin invitation
- mentor program invitation
- HCMUE alumni community link
- event/AMA invitation
- direct login after approval

## 5. Alumni Verification Flow

Flow

```txt
Alumni starts registration
→ Selects Alumni role
→ Provides HCMUE background
→ Provides graduation/faculty/cohort information
→ Uploads evidence if required
→ Submits verification
→ Admin reviews
→ Approved / rejected / need more info
```

Required Information

| Field                   | Priority |
| ----------------------- | -------- |
| Name                    | P1       |
| Email/phone             | P1       |
| Faculty/major           | P1       |
| Graduation year/cohort  | P1       |
| Student ID if available | P1       |
| Evidence                | P1       |
| Current field/industry  | P2       |

States

| State                  | Meaning                        |
| ---------------------- | ------------------------------ |
| Draft                  | Chưa gửi                       |
| Pending                | Chờ duyệt                      |
| Approved               | Alumni verified                |
| Rejected               | Không được duyệt               |
| Need more info         | Cần bổ sung                    |
| Converted from student | Student role updated to alumni |

Rules

- Alumni verification must be admin-reviewed unless trusted institutional method exists.
- Alumni role should not be self-assigned without verification.
- Evidence privacy applies.
- Role transition from Student to Alumni must be auditable.

## 6. Alumni Profile Setup Flow

Flow

```txt
Approved alumni
→ Add avatar
→ Add HCMUE background
→ Add current field
→ Add short bio
→ Add support topics
→ Choose mentor availability if wanted
→ Save profile
```

Profile Direction

Alumni profile cần:

- trusted
- HCMUE-connected
- short and human
- career-aware but not CV-heavy
- support-focused if mentor enabled

Required Profile Sections

| Section                 | Priority             |
| ----------------------- | -------------------- |
| HCMUE background        | P1                   |
| Current field           | P1                   |
| Short bio               | P1                   |
| Can help with           | P1                   |
| Mentor availability     | P1 if mentor enabled |
| Posts/insights          | P2                   |
| Community participation | P2                   |

UX Rules

- Không làm alumni profile giống resume dài 8 trang.
- Badge Alumni rõ ràng.
- Nếu có mentor access, show support topics.
- Cho phép alumni ẩn một số career info nếu muốn.

## 7. Alumni Feed Flow

Flow

```txt
Alumni opens Home Feed
→ Reads student/community posts
→ Creates insight post
→ Comments on relevant posts
→ Students interact
→ Alumni can save/report/block as needed
```

Content Types

| Type                | Priority |
| ------------------- | -------- |
| Experience sharing  | P1       |
| Learning advice     | P1       |
| Career insight      | P1       |
| Mentor answer       | P1       |
| Opportunity sharing | P2       |
| Alumni event        | P2       |

UX Rules

- Alumni content có badge/context rõ.
- Không để career posts biến thành spam tuyển dụng.
- Opportunity sharing cần moderation nếu public.
- Tone supportive, không dạy đời kiểu motivational poster có Wi-Fi.

## 8. Alumni as Mentor Flow

Flow

```txt
Alumni enables Mentor capability
→ Completes mentor profile
→ Admin approves mentor_access if required
→ Student sends mentor request
→ Alumni reviews request
→ Accepts / declines / asks for more info
→ Conversation starts if accepted
```

Rules

- Alumni không bắt buộc làm mentor.
- Mentor access có thể pause/revoke.
- Có thể chọn lĩnh vực hỗ trợ.
- Có giới hạn request nếu cần.
- Accepted mentor request unlocks messaging.
- Safety/report always available.

## 9. Alumni Messaging Flow

Flow

```txt
Alumni receives allowed interaction
→ Opens conversation
→ Replies
→ Shares advice/resource if appropriate
→ Can mute/report/block
```

Permission Rules

| Case                                    | Messaging Allowed |
| --------------------------------------- | ----------------- |
| Accepted mentor request                 | Yes               |
| Existing connection                     | Yes               |
| Approved community context if supported | Conditional       |
| Random student direct message           | No by default     |
| Blocked relationship                    | No                |
| Suspended/banned account                | No                |

UX Rules

- Không mở inbox alumni cho mọi student spam.
- Alumni có quyền pause/hide contact options.
- Message privacy applies.
- Message body không đưa vào analytics.

## 10. Alumni Community Flow

Flow

```txt
Alumni opens Communities
→ Joins alumni/faculty/community spaces
→ Reads posts
→ Participates in discussion
→ Joins event/AMA if supported
```

Community Types

- Alumni community
- Faculty alumni community
- Mentor community
- Career direction community
- Event-specific community

Priority: P2/P3 depending roadmap.

## 11. Opportunity Sharing Flow

Flow

```txt
Alumni creates opportunity post
→ Adds title/details/source/deadline
→ Selects category
→ Submits
→ Admin/moderation review if required
→ Published or rejected
```

Opportunity Types

- internship
- job
- scholarship
- workshop
- event
- project collaboration

UX Rules

- Có disclaimer.
- Có report.
- Có moderation nếu public.
- Không biến feed thành job board.
- Không có recruiter portal trong MVP.
- Không có paid posting.

## 12. Alumni Event / AMA Flow

Priority: P2/P3.

```txt
Alumni proposes AMA/session
→ Admin/community manager reviews
→ Event published
→ Students register or follow
→ Session happens
→ Follow-up post/resources
```

This is not MVP-critical.

## 13. Safety Flow

Flow

```txt
Alumni reports inappropriate message/post/request
→ Admin review
→ Alumni can block/mute
→ Moderator action if needed
```

Safety Rules

- Alumni phải được bảo vệ khỏi spam.
- Student cũng phải được bảo vệ khỏi alumni misuse.
- Alumni/mentor status không miễn trừ moderation.
- Report/block applies to alumni like every verified user.

## 14. Alumni Page Map

| Flow Area    | Required Pages                                                 |
| ------------ | -------------------------------------------------------------- |
| Verification | verification.md, account-status.md                             |
| Profile      | profile.md, profile-edit.md, alumni-profile.md                 |
| Feed         | home-feed.md, composer.md, post-detail.md                      |
| Mentor       | mentor-profile.md, mentor-request.md, mentor-request-detail.md |
| Messaging    | messaging.md, conversation.md                                  |
| Community    | clubs.md, club-detail.md                                       |
| Opportunity  | composer.md, post-detail.md                                    |
| Safety       | safety-reporting.md, blocked-users.md                          |
| Settings     | settings.md, privacy.md                                        |

## 15. Critical States

| State                        | Where          |
| ---------------------------- | -------------- |
| Alumni verification pending  | Account status |
| Alumni verification rejected | Account status |
| Need more information        | Verification   |
| Alumni profile incomplete    | Profile        |
| Mentor role off              | Mentor profile |
| Mentor role pending          | Mentor status  |
| Mentor access approved       | Mentor profile |
| Mentor access revoked        | Mentor status  |
| Request pending              | Mentor request |
| Opportunity pending review   | Post detail    |
| Message permission denied    | Messaging      |
| Report submitted             | Safety         |
| Account suspended            | Account status |

## 16. UX Risks

| Risk                        | Prevention                         |
| --------------------------- | ---------------------------------- |
| Alumni profile quá LinkedIn | Keep short, human, support-focused |
| Opportunity spam            | Moderation/review                  |
| Alumni bị spam message      | Permission-based messaging         |
| Career pressure quá mạnh    | Tone supportive                    |
| Alumni role không rõ        | Badge and profile context          |
| Mentor quá tải              | Availability and pause             |
| Product mất student-first   | Alumni supports, does not dominate |
| Feed thành job board        | Opportunity controls + moderation  |

## 17. QA Checklist

- Alumni verification rõ ràng.
- Evidence privacy được bảo vệ.
- Alumni profile có HCMUE background.
- Alumni profile không quá CV.
- Mentor role là optional.
- Mentor access cần permission.
- Message permission bảo vệ alumni.
- Opportunity sharing có moderation.
- Alumni content có badge/context.
- Safety/report hoạt động đầy đủ.
- Career tone không gây áp lực.
- Student-first vẫn là trọng tâm sản phẩm
  Alumni reports inappropriate message/post/request
  → Admin review
  → Alumni can block/mute
  → Moderator action if needed
  Safety Rules
  Alumni phải được bảo vệ khỏi spam.
  Student cũng phải được bảo vệ khỏi alumni misuse.
  Alumni/mentor status không miễn trừ moderation.
  Report/block applies to alumni like every verified user.

14. Alumni Page Map
    Flow Area Required Pages
    Verification verification.md, account-status.md
    Profile profile.md, profile-edit.md, alumni-profile.md
    Feed home-feed.md, composer.md, post-detail.md
    Mentor mentor-profile.md, mentor-request.md, mentor-request-detail.md
    Messaging messaging.md, conversation.md
    Community clubs.md, club-detail.md
    Opportunity composer.md, post-detail.md
    Safety safety-reporting.md, blocked-users.md
    Settings settings.md, privacy.md
15. Critical States
    State Where
    Alumni verification pending Account status
    Alumni verification rejected Account status
    Need more information Verification
    Alumni profile incomplete Profile
    Mentor role off Mentor profile
    Mentor role pending Mentor status
    Mentor access approved Mentor profile
    Mentor access revoked Mentor status
    Request pending Mentor request
    Opportunity pending review Post detail
    Message permission denied Messaging
    Report submitted Safety
    Account suspended Account status
16. UX Risks
    Risk Prevention
    Alumni profile quá LinkedIn Keep short, human, support-focused
    Opportunity spam Moderation/review
    Alumni bị spam message Permission-based messaging
    Career pressure quá mạnh Tone supportive
    Alumni role không rõ Badge and profile context
    Mentor quá tải Availability and pause
    Product mất student-first Alumni supports, does not dominate
    Feed thành job board Opportunity controls + moderation
17. QA Checklist
    Alumni verification rõ ràng.
    Evidence privacy được bảo vệ.
    Alumni profile có HCMUE background.
    Alumni profile không quá CV.
    Mentor role là optional.
    Mentor access cần permission.
    Message permission bảo vệ alumni.
    Opportunity sharing có moderation.
    Alumni content có badge/context.
    Safety/report hoạt động đầy đủ.
    Career tone không gây áp lực.
    Student-first vẫn là trọng tâm sản phẩm
