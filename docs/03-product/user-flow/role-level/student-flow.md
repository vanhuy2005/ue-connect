---
title: "Student Flow"
module: "03-product/user-flow/role-level"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0"
actor: "Student"
last_updated: "2026-05-26"
owner: "Product / UX / Frontend / Backend / QA"
depends_on:
  - "../../use-cases/student-use-cases.md"
  - "../../../02-requirements/functional-requirements.md"
  - "../../../02-requirements/role-permission-matrix.md"
  - "../../../02-requirements/acceptance-criteria.md"
  - "../../../02-requirements/edge-cases.md"
related_module_flows:
  - "../module-level/onboarding-flow.md"
  - "../module-level/home-feed-flow.md"
  - "../module-level/discovery-flow.md"
  - "../module-level/messaging-flow.md"
  - "../module-level/mentor-flow.md"
  - "../module-level/community-flow.md"
  - "../module-level/safety-reporting-flow.md"
related_feature_specs:
  - "../../feature-specs/authentication.md"
  - "../../feature-specs/verification-identity.md"
  - "../../feature-specs/onboarding.md"
  - "../../feature-specs/profile-management.md"
  - "../../feature-specs/home-feed.md"
  - "../../feature-specs/discovery-profile.md"
  - "../../feature-specs/greeting-connection.md"
  - "../../feature-specs/messaging.md"
  - "../../feature-specs/mentor-matching.md"
  - "../../feature-specs/community-club.md"
  - "../../feature-specs/settings-privacy.md"
---

# Student Flow

## 1. Purpose

File này mô tả hành trình end-to-end của `Student`, actor trung tâm của UEConnect.

Student Flow không chỉ là danh sách tính năng. File này mô tả cách một sinh viên HCMUE đi từ lúc chưa có tài khoản đến khi trở thành `Verified UEer`, dùng feed, discovery, greeting, messaging, mentor, community và safety.

Mục tiêu:

- Kết nối các module flow thành một hành trình liền mạch.
- Làm nền cho page specs, design system, database và test cases.
- Đảm bảo mọi flow của Student giữ đúng định hướng: verified-first, student-first, social nhưng không dating, mobile-first, neutral UI và safety-ready.

---

## 2. Actor Definition

| Field           | Description                                                               |
| --------------- | ------------------------------------------------------------------------- |
| Actor           | Student                                                                   |
| Scope           | Sinh viên HCMUE năm 1 đến năm 4                                           |
| Primary role    | `student`                                                                 |
| Identity source | Mã sinh viên, minh chứng sinh viên, email trường nếu có                   |
| Verification    | Bắt buộc                                                                  |
| Access rule     | Chỉ dùng full product sau khi account `active` và verification `approved` |
| Main value      | Kết nối UEers, học cùng, chia sẻ, tìm mentor, tham gia cộng đồng          |
| UX direction    | Mobile-first, social-first, trusted, minimal, human                       |

---

## 3. Student Journey Overview

```txt
Guest
→ Register
→ Student verification
→ Pending account status
→ Admin approval
→ Verified UEer
→ Profile setup
→ Home feed
→ Discovery
→ Greeting
→ Connection
→ Messaging
→ Mentor request
→ Community participation
→ Privacy / safety / long-term identity growth
```

## 4. Access Rules

| Account State         | Student Access                                 |
| --------------------- | ---------------------------------------------- |
| Guest                 | Chỉ xem landing, login, register               |
| Registered            | Chỉ submit verification                        |
| Pending verification  | Chỉ xem account status                         |
| Need more information | Có thể bổ sung minh chứng                      |
| Rejected              | Xem lý do, có thể resubmit nếu policy cho phép |
| Active / verified     | Được dùng app chính                            |
| Suspended             | Bị chặn social actions                         |
| Banned                | Không được truy cập product                    |

Rule quan trọng:

Account status luôn override permission và UI state.

Nói cách khác, nếu user bị suspended mà vẫn gửi được message chỉ vì còn tab cũ đang mở, đó không phải “edge case thú vị”, đó là bug đang mặc áo hoodie.

## 5. Lifecycle Stages

| Stage         | User Goal                                 | Related Flow                | Priority |
| ------------- | ----------------------------------------- | --------------------------- | -------- |
| Entry         | Hiểu UEConnect là gì                      | onboarding-flow.md          | P0       |
| Registration  | Tạo tài khoản                             | authentication / onboarding | P0       |
| Verification  | Trở thành Verified UEer                   | verification-identity       | P0       |
| Profile Setup | Xây dựng identity đáng tin                | profile-management          | P0       |
| Social Feed   | Đọc, đăng, comment                        | home-feed-flow.md           | P0       |
| Discovery     | Khám phá UEers phù hợp                    | discovery-flow.md           | P0       |
| Greeting      | Bắt đầu kết nối                           | greeting-connection         | P0       |
| Messaging     | Giao tiếp sau kết nối                     | messaging-flow.md           | P0       |
| Mentor        | Tìm hỗ trợ học tập/career                 | mentor-flow.md              | P1       |
| Community     | Tham gia CLB/lớp/nhóm                     | community-flow.md           | P1/P2    |
| Safety        | Report, block, privacy                    | safety-reporting-flow.md    | P0       |
| Growth        | Duy trì identity, network, mentor history | profile/settings            | P2       |

## 6. Main Student Flow

```txt
Student opens UEConnect
→ System checks authentication
→ If guest: show landing/login/register
→ If pending: show account status
→ If rejected/need more info: show verification correction flow
→ If verified but profile incomplete: suggest profile setup
→ If active and ready: open Home Feed
→ Student reads or creates post
→ Student explores Discovery
→ Student sends Greeting
→ Receiver accepts
→ Connection created
→ Messaging enabled
→ Student sends Mentor Request if needed
→ Student joins Community
→ Student manages privacy/safety over time
```

## 7. Authentication & Verification Flow

Flow

```txt
Open app
→ Register
→ Enter basic information
→ Enter student identity information
→ Upload evidence if required
→ Submit verification
→ Account pending
→ Admin review
→ Approved / rejected / need more information
→ Continue profile setup
```

Required Pages

| Page              | Purpose                          |
| ----------------- | -------------------------------- |
| auth.md           | Login/register                   |
| verification.md   | Student verification             |
| account-status.md | Pending/rejected/approved states |
| profile-setup.md  | Initial profile setup            |

Required States

- default
- loading
- validation_error
- duplicate_student_id
- uploading
- pending_approval
- need_more_information
- rejected
- approved
- suspended
- banned

UX Rules

- Không cho user vào full app nếu chưa verified.
- Giải thích rõ vì sao cần MSSV/minh chứng.
- Không public MSSV trong product.
- Pending state phải rõ ràng, không được để user tưởng hệ thống chết lâm sàng.
- Rejected state phải có reason và hướng xử lý.

## 8. Profile Setup Flow

Flow

```txt
Account approved
→ Show welcome verified state
→ Upload avatar
→ Add faculty / major / cohort / class
→ Add short bio
→ Select learning interests
→ Select connection goals
→ Set discovery/privacy visibility
→ Enter Home Feed
```

Required Fields

| Field              | Priority |
| ------------------ | -------- |
| Avatar             | P0       |
| Display name       | P0       |
| Faculty            | P0       |
| Major              | P0       |
| Cohort             | P0       |
| Class              | P1       |
| Short bio          | P0       |
| Learning interests | P1       |
| Career interests   | P1       |
| Clubs              | P2       |
| Mentor goals       | P1       |

UX Rules

- Profile không được giống CV quá mức.
- Profile không được giống dating profile.
- Có progress indicator.
- Có thể skip optional fields.
- Có privacy explanation rõ ràng.
- Full MSSV không xuất hiện ở public profile.

## 9. Home Feed Flow

Flow

```txt
Open Home Feed
→ Load skeleton
→ Render feed
→ User reads post
→ User likes/comments/saves/reports
→ User opens post detail
→ User creates post
→ Feed updates
```

Main Actions

| Action      | Priority |
| ----------- | -------- |
| Read posts  | P0       |
| Create post | P0       |
| Comment     | P0       |
| Like        | P0       |
| Save        | P1       |
| Share/send  | P1       |
| Report      | P0       |
| Hide        | P1       |

UX Rules

- Feed content-first như Threads.
- Comment rõ ràng, dễ đọc hơn kiểu Facebook.
- Không dùng gradient background.
- Brand blue chỉ dùng cho CTA, verified/active states.
- Post action dùng icon line, neutral.
- Report không được giấu quá sâu.
- Loading, empty, error state bắt buộc.

## 10. Discovery Flow

Flow

```txt
Open Discovery
→ Load suggested UEers
→ Show profile card
→ User reads shared context
→ User sends Greeting / skips / saves / opens profile
→ System updates queue
```

Discovery Card Must Show

- avatar
- display_name
- verified_badge
- faculty
- major/cohort
- short_bio
- shared_context
- primary_cta: Gửi lời chào
- secondary_actions: Bỏ qua, Lưu, Xem hồ sơ

UX Rules

- Không dùng match, swipe, crush, hot, dating.
- CTA chính là Gửi lời chào.
- Card phải có context học tập/cộng đồng.
- Không ranking theo ngoại hình.
- Có report/block từ profile card hoặc profile detail.
- Interaction có thể nhanh như Tinder nhưng framing tuyệt đối không dating. Học cái tốt, đừng copy cái làm nhà trường tăng huyết áp.

## 11. Greeting & Connection Flow

Flow

```txt
Student views another UEer
→ Clicks Gửi lời chào
→ Optional short message
→ Submit
→ Greeting status = pending
→ Receiver gets notification
→ Receiver accepts or declines
→ If accepted: connection created
→ Messaging enabled
```

States

| State         | Meaning             |
| ------------- | ------------------- |
| Not connected | Chưa có quan hệ     |
| Greeting sent | Đã gửi lời chào     |
| Pending       | Chờ phản hồi        |
| Accepted      | Đã chấp nhận        |
| Connected     | Có thể nhắn tin     |
| Declined      | Từ chối             |
| Blocked       | Không thể tương tác |
| Cancelled     | Đã hủy nếu hỗ trợ   |

Rules

- Không tạo duplicate pending greeting.
- Không gửi greeting cho chính mình.
- Không gửi greeting cho blocked user.
- Không gửi greeting cho suspended/banned user.
- Greeting accepted phải tạo connection đúng một lần.

## 12. Messaging Flow

Flow

```txt
Open Inbox
→ Load conversation list
→ Open conversation
→ Load messages
→ Send message
→ Show sending/sent/failed state
→ Receive reply
→ Optional report/block/mute
```

Permission Rules

| Case                           | Messaging Allowed |
| ------------------------------ | ----------------- |
| Accepted greeting / connection | Yes               |
| Accepted mentor request        | Yes               |
| No connection                  | No                |
| Blocked relationship           | No                |
| Suspended/banned account       | No                |

UX Rules

- Own bubble dùng brand blue.
- Other bubble dùng neutral.
- Không dùng gradient bubble.
- Failed message phải có retry.
- Private message content không đưa vào analytics.
- Non-participant không được xem conversation.

## 13. Mentor Flow

Flow

```txt
Open Mentor
→ Browse mentors
→ Filter by topic
→ Open mentor profile
→ Send mentor request
→ Wait for response
→ Mentor accepts / declines / asks for more info
→ If accepted: conversation opens
```

Request Must Include

- topic
- question/context
- student_goal
- optional background
- respectful communication consent

UX Rules

- Mentor là feature chính, không phải phụ kiện cho đẹp.
- Không LinkedIn hóa.
- Mentor profile nói rõ “có thể hỗ trợ gì”.
- Có availability rõ.
- Student không bị làm cho cảm giác bị đánh giá khi mentor decline.
- Mentor request accepted unlock messaging.

## 14. Community Flow

Flow

```txt
Open Communities
→ Browse clubs/classes/groups
→ Open community detail
→ Join/request to join
→ View posts
→ Participate in discussion
→ Report unsafe content if needed
```

Priority

| Community Capability              | Priority   |
| --------------------------------- | ---------- |
| Community listing                 | P1         |
| Community detail                  | P1         |
| Join/follow                       | P1         |
| Community posts                   | P2         |
| Community chat                    | P2/P3      |
| Multi-channel Discord-like system | Out of MVP |

UX Rules

- Học Discord về community structure, không copy nguyên con.
- MVP chỉ cần community page + posts + basic governance.
- Community phải có approval nếu official/semi-official.
- Community report và moderation phải có từ đầu nếu cho user đăng nội dung.

## 15. Notification Flow

Flow

```txt
User receives notification
→ Opens notification center
→ Taps notification
→ Navigates to target
→ Notification marked as read
```

Notification Types

| Type                  | Destination                    |
| --------------------- | ------------------------------ |
| Verification result   | Account status / profile setup |
| Greeting request      | Greeting detail / profile      |
| Greeting accepted     | Conversation                   |
| Message               | Conversation                   |
| Mentor request update | Mentor request detail          |
| Comment/like          | Post detail                    |
| Community update      | Community detail               |
| Safety/moderation     | Safety/account status          |

Rules

- Notification không leak sensitive data.
- Deleted/hidden target phải show unavailable state.
- In-app notification là đủ cho MVP; push notification là future.

## 16. Safety Flow

Flow

```txt
User sees unsafe content/user
→ Opens more menu
→ Chooses Report or Block
→ Selects reason
→ Optional detail
→ Submit
→ Confirmation
→ Admin moderation queue receives report
```

Report Surfaces

- profile
- post
- comment
- message
- discovery card
- mentor request
- community post
- community

UX Rules

- Report/block phải dễ tìm.
- Không bắt user viết mô tả dài.
- Submit fail không mất dữ liệu.
- Không hứa chắc chắn sẽ xóa nội dung.
- Block phải có hiệu lực ngay với direct interaction.

## 17. Student Page Map

| Flow Area  | Required Pages                                  |
| ---------- | ----------------------------------------------- |
| Auth       | auth.md, verification.md, account-status.md     |
| Setup      | profile-setup.md, privacy.md                    |
| Feed       | home-feed.md, post-detail.md, composer.md       |
| Discovery  | discovery.md, profile.md                        |
| Connection | connection-management.md, notifications.md      |
| Messaging  | messaging.md, conversation.md                   |
| Mentor     | mentor.md, mentor-profile.md, mentor-request.md |
| Community  | clubs.md, club-detail.md, community-chat.md     |
| Safety     | safety-reporting.md, blocked-users.md           |
| Settings   | settings.md, privacy.md, account.md             |

## 18. Critical States

| State                  | Where                          |
| ---------------------- | ------------------------------ |
| Account pending        | Auth / onboarding              |
| Account rejected       | Auth / onboarding              |
| Need more information  | Verification                   |
| Profile incomplete     | Profile / feed / discovery     |
| Empty feed             | Home feed                      |
| No discovery profiles  | Discovery                      |
| Greeting pending       | Discovery / profile            |
| No messages            | Messaging                      |
| Message failed         | Conversation                   |
| No mentor found        | Mentor                         |
| Mentor request pending | Mentor                         |
| Blocked user           | Messaging / profile            |
| Content moderated      | Feed / post detail             |
| Offline                | Async actions                  |
| Permission denied      | Messaging / community / mentor |

## 19. UX Risks

| Risk                          | Prevention                                           |
| ----------------------------- | ---------------------------------------------------- |
| Discovery giống dating        | Cấm dating language, thêm academic/community context |
| Feed quá generic              | Verified UEer + HCMUE context                        |
| Profile quá CV                | Bio, interest, goal, human tone                      |
| Profile quá Tinder            | Không appearance-first, không romantic intent        |
| Messaging thiếu trust         | Chỉ mở sau connection/request                        |
| Mentor quá LinkedIn           | Tone supportive, không tuyển dụng hóa                |
| UI quá nhiều màu              | Neutral-first, brand restrained                      |
| User pending bị hoang mang    | Account status rõ                                    |
| Safety yếu                    | Report/block/moderation bắt buộc                     |
| Mobile chỉ là desktop thu nhỏ | Mobile-first layout thật                             |

## 20. QA Checklist

- User có đường rõ từ signup đến verified.
- Pending/rejected/need more info/approved state rõ.
- Profile setup không quá dài.
- Home feed có loading/empty/error.
- Discovery không có dating vibe.
- Greeting có pending/accepted/declined state.
- Messaging có permission model.
- Mentor request có trạng thái rõ.
- Community không làm rối MVP.
- Report/block có ở mọi điểm rủi ro.
- Full MSSV không public.
- Mobile flow không chỉ là desktop thu nhỏ.
