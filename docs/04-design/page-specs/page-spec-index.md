---
title: "Page Spec Index"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UX / Frontend"
depends_on:
	- "../information-architecture.md"
	- "../12-component-primitives.md"
	- "../13-component-variants.md"
	- "../14-interaction-states.md"
	- "../16-content-tone.md"
	- "../17-accessibility-rules.md"
	- "../18-responsive-rules.md"
	- "../../03-product/sitemap.md"
	- "../../03-product/feature-list.md"
	- "../../03-product/feature-priority.md"
---

<!-- markdownlint-disable MD004 MD024 MD025 -->

# Page Spec Index

## 1. Purpose

File này mô tả ngắn gọn nhưng đầy đủ toàn bộ page specs của UEConnect.

Mỗi page spec trong folder này phải giúp designer/dev hiểu:

- Trang này dùng để làm gì.
- Ai được truy cập.
- Trang nằm ở route nào.
- Layout chính trên mobile và desktop.
- Component chính cần dùng.
- State cần thiết kế.
- Hành động chính/phụ.
- Dữ liệu cần hiển thị.
- Rule privacy/permission.
- Accessibility/responsive bắt buộc.

File này không thay thế từng page spec chi tiết.

Nó là bản đồ tổng quan để không ai mở folder `page-specs/` rồi nhìn 30 file Markdown như đang nhìn danh sách bài tập chưa làm.

---

## 2. Page Spec Writing Contract

Mỗi page spec nên dùng cấu trúc chuẩn:

```txt
1. Purpose
2. Route
3. Access Rules
4. User Context
5. Layout
6. Components
7. Data Displayed
8. Primary Actions
9. Secondary Actions
10. States
11. Responsive Behavior
12. Accessibility
13. Analytics Events
14. QA Checklist
```

Bắt buộc mọi page spec phải có:

```txt
default state
loading state
empty state nếu có list/data
error state
permission/locked state nếu có access rule
mobile layout
desktop layout
accessibility notes
Vietnamese UI copy
```

---

# 3. Page Specs Overview

## 3.1. `account-status.md`

### Purpose

Mô tả trang hoặc state gate dùng để điều hướng người dùng dựa trên trạng thái tài khoản.

Trang này xử lý các trạng thái:

```txt
banned / suspended
not verified
verified but profile incomplete
verified and ready
admin accessing admin route
```

### Main Routes

```txt
/account/status
/account/restricted
/account/pending-verification
```

### Main Users

```txt
guest
registered user
verified user
restricted user
admin
```

### Key UI

- Status card.
- Reason message.
- Next action CTA.
- Support link.
- Verification/profile setup redirect.

### Required States

| State                 | UI Direction                          |
| --------------------- | ------------------------------------- |
| `not_verified`        | CTA đi đến xác thực                   |
| `profile_incomplete`  | CTA hoàn tất hồ sơ                    |
| `restricted`          | Hiển thị lý do bị hạn chế             |
| `suspended`           | Hiển thị thời hạn/lý do nếu được phép |
| `ready`               | Redirect `/app/home`                  |
| `admin_route_allowed` | Cho vào `/admin` nếu có quyền         |

### Design Notes

Tone phải nghiêm túc, rõ lý do, không đổ lỗi user.

---

## 3.2. `account.md`

### Purpose

Trang quản lý tài khoản cá nhân.

Bao gồm:

- Email đăng nhập.
- Password.
- Remembered sessions nếu có.
- Security basics.
- Account status.
- Logout.
- Danger zone nếu cần.

### Main Route

```txt
/app/account
```

### Key UI

- Account summary card.
- Email field readonly.
- Password update form.
- Session/security section.
- Logout button.
- Restricted/account status banner.

### Required States

```txt
default
saving
saved
validation_error
permission_locked
restricted
```

### Design Notes

Không biến account page thành settings tổng hợp. Privacy nằm ở `privacy.md`, app preferences nằm ở `settings.md`.

---

## 3.3. `alumni-profile.md`

### Purpose

Mô tả public/private profile riêng cho cựu sinh viên.

Profile alumni cần nhấn mạnh:

- Ngành/khoa đã học.
- Khóa tốt nghiệp.
- Công việc hiện tại nếu user cho phép.
- Chuyên môn.
- Mentor availability nếu có.
- Insight/career sharing.

### Main Routes

```txt
/app/alumni/{id}
@app/profile/alumni
```

### Key UI

- Alumni profile header.
- Verification badge.
- Education history.
- Career/current role.
- Mentor status.
- Shared posts/resources.
- Privacy-sensitive fields.

### Required States

```txt
public_view
private_limited_view
blocked_hidden
mentor_available
mentor_paused
not_found
```

### Design Notes

Không được làm profile giống CV tuyển dụng quá khô, cũng không được giống dating profile. Đây là alumni identity trong campus social platform.

---

## 3.4. `auth.md`

### Purpose

Mô tả toàn bộ màn auth:

- Login.
- Register nếu cho phép.
- Forgot password.
- Reset password.
- Microsoft Azure / Outlook HCMUE login direction.
- Email `hcmue.edu.vn` only.

### Main Routes

```txt
/login
/register
/forgot-password
/reset-password
/auth/microsoft
```

### Key UI

- Primary logo.
- Email/password form.
- Microsoft/HCMUE edu login CTA.
- Remember me.
- Password policy helper.
- Error summary.

### Required States

```txt
default
submitting
invalid_email_domain
invalid_credentials
account_restricted
not_verified_redirect
profile_incomplete_redirect
```

### Design Notes

UI tiếng Việt, code English. Không dùng Google OAuth wording nếu project quyết định dùng Microsoft/Azure cho Outlook edu. Mật khẩu tối thiểu 8 ký tự.

---

## 3.5. `blocked-users.md`

### Purpose

Trang quản lý danh sách người dùng đã chặn.

Block trong UEConnect có tác động:

- Ẩn nhau khỏi Discovery.
- Chặn message.
- Xóa/hủy connection nếu business rule yêu cầu.
- Ngăn greeting.

### Main Route

```txt
/app/settings/blocked-users
```

### Key UI

- List blocked users.
- Reason/context optional.
- Unblock action.
- Empty state.
- Confirmation modal.

### Required States

```txt
empty
loading
error
blocked_list
unblock_confirming
unblock_success
```

### Design Notes

Không hiển thị quá nhiều thông tin người bị chặn nếu privacy yêu cầu. Copy phải trung tính.

---

## 3.6. `club-detail.md`

### Purpose

Trang chi tiết một CLB/cộng đồng dạng club.

Tập trung vào:

- Giới thiệu CLB.
- Chủ sở hữu/operator.
- Thành viên.
- Bài viết.
- Chat.
- Tài nguyên.
- Quy trình tham gia.

### Main Route

```txt
/app/clubs/{club_id}
```

### Key UI

- Club header.
- Status badge.
- Join/request button.
- Tabs: Bài viết / Tài nguyên / Trò chuyện / Thành viên / Giới thiệu.
- Owner/manager actions.
- Suspended state.

### Required States

```txt
public_preview
member_view
pending_join
private_locked
suspended
archived
not_found
```

### Design Notes

Club manager chỉ là scoped operator. Owner có quyền đầy đủ trong community scope. Admin vẫn có quyền moderation cao hơn.

---

## 3.7. `clubs.md`

### Purpose

Trang danh sách CLB/cộng đồng.

Cho user khám phá:

- CLB chính thức.
- Cộng đồng học tập.
- Cộng đồng theo khoa/ngành.
- Nhóm mentor/career.
- Cộng đồng đã tham gia.

### Main Route

```txt
/app/clubs
```

### Key UI

- Search.
- Filter.
- Category tabs.
- Club/community cards.
- Joined/pending status.
- Suggest new community CTA.

### Required States

```txt
loading
empty
search_empty
filter_empty
error
joined
pending
locked
```

### Responsive

Mobile dùng list/card một cột, filter mở bottom sheet. Desktop dùng grid + filter sidebar/topbar.

---

## 3.8. `community-channel.md`

### Purpose

Mô tả channel trong community/club.

Channel có thể là:

- Discussion channel.
- Announcement channel.
- Resource channel.
- Chat-like channel.

### Main Route

```txt
/app/communities/{community_id}/channels/{channel_id}
```

### Key UI

- Channel title.
- Channel description/rules.
- Post/message list.
- Composer nếu có quyền.
- Member/channel actions.
- Moderation placeholder.

### Required States

```txt
active
readonly
member_only
moderator_only
suspended
hidden_by_moderation
empty
```

### Design Notes

Không trộn channel chat và feed nếu behavior khác nhau. UI phải thể hiện rõ đây là channel dạng nào.

---

## 3.9. `community-chat.md`

### Purpose

Trang chat trong community.

Khác với direct messaging vì đây là không gian nhóm.

### Main Route

```txt
/app/communities/{community_id}/chat
```

### Key UI

- Message list.
- Composer.
- Attachment support.
- Member identity.
- Moderation/report actions.
- Realtime status.
- Suspended/readonly banner.

### Required States

```txt
connected
reconnecting
offline
readonly
not_member_locked
community_suspended
message_failed
```

### Design Notes

Realtime cần rõ trạng thái. DB là source of truth, WebSocket chỉ là transport. Vâng, đường ống không phải thánh.

---

## 3.10. `composer.md`

### Purpose

Mô tả composer dùng chung cho:

- Post.
- Comment.
- Message.
- Community post.
- Resource note.
- Greeting message.

### Main Usage

```txt
feed composer
comment composer
message composer
community composer
```

### Key UI

- Textarea.
- Character count nếu cần.
- Attachment/image action.
- Submit button.
- Draft/validation state.
- Privacy/visibility selector nếu áp dụng.

### Required States

```txt
empty
typing
valid
invalid
submitting
uploading
failed
disabled
restricted
```

### Design Notes

Composer là primitive quan trọng. Không tự chế mỗi module một kiểu, không là app sẽ có 7 textarea cùng tồn tại như các nhánh tiến hóa lỗi.

---

## 3.11. `connection-management.md`

### Purpose

Trang quản lý connection/lời chào/kết nối.

Bao gồm:

- Connections hiện tại.
- Greeting sent.
- Greeting received.
- Pending/accepted/declined status.
- Block/report actions.

### Main Route

```txt
/app/connections
```

### Key UI

- Connection list.
- Pending greetings.
- Received greetings.
- Status badges.
- CTA mở chat.
- Decline/accept actions.

### Required States

```txt
empty
pending
accepted
declined
expired
blocked
reported
```

### Design Notes

Không dùng ngôn ngữ “match”. Dùng “kết nối”, “lời chào”, “phản hồi”.

---

## 3.12. `conversation.md`

### Purpose

Trang chi tiết một cuộc trò chuyện.

### Main Route

```txt
/app/messages/{conversation_id}
```

### Key UI

- Conversation header.
- Message list.
- Message bubbles.
- Attachment preview.
- Typing indicator.
- Read receipts.
- Composer.
- More actions: block/report/delete.

### Required States

```txt
loading
empty
active
reconnecting
offline
blocked
not_connected
message_request_pending
restricted
message_failed
```

### Responsive

Mobile: conversation detail page riêng.
Desktop: nằm trong two-pane messaging layout.

---

## 3.13. `discovery.md`

### Purpose

Trang khám phá UEers phù hợp để kết nối.

### Main Route

```txt
/app/discovery
```

### Key UI

- Discovery card.
- Profile summary.
- Shared context: khoa/ngành/chủ đề/cộng đồng chung.
- Actions: Bỏ qua / Gửi lời chào / Xem hồ sơ.
- Filter sheet/sidebar.
- Empty recommendation state.

### Required States

```txt
loading
available
greeting_pending
connected
empty
blocked_hidden
profile_incomplete_locked
```

### Design Notes

Có thể hỗ trợ swipe nhưng luôn phải có button fallback. Không dùng heart, match, crush, romantic copy. Không phải Tinder mặc áo HCMUE.

---

## 3.14. `events.md`

### Purpose

Trang sự kiện trong UEConnect.

Có thể bao gồm:

- Sự kiện CLB.
- Workshop.
- Mentor session.
- Career talk.
- Community event.

### Main Route

```txt
/app/events
/app/communities/{id}/events
```

### Key UI

- Event cards.
- Date/time/location.
- Host/community.
- RSVP/join action.
- Calendar/schedule state future.
- Filter by type.

### Required States

```txt
upcoming
ongoing
ended
cancelled
full
joined
empty
```

### Design Notes

MVP có thể để P1/P2 nếu chưa nằm trong scope chính. Nhưng page spec nên sẵn hướng mở rộng.

---

## 3.15. `home-feed.md`

### Purpose

Trang bảng tin chính sau khi user verified và profile ready.

### Main Route

```txt
/app/home
```

### Key UI

- Feed composer.
- Post cards.
- Image posts.
- Comments preview.
- Admin/system announcement.
- Suggested communities/mentors right rail desktop.
- Infinite/cursor loading.

### Required States

```txt
loading_skeleton
empty_feed
network_error
moderation_placeholder
restricted_account
offline_cached
```

### Design Notes

Lấy cảm hứng Threads/Instagram về content-first, nhưng giữ HCMUE trust identity. Không full gradient background. Feed mà gradient phủ hết thì post thành nạn nhân thị giác.

---

## 3.16. `mentor-profile.md`

### Purpose

Trang hồ sơ mentor.

Dành cho alumni/advisor đã được cấp mentor access.

### Main Route

```txt
/app/mentors/{mentor_id}
```

### Key UI

- Mentor header.
- Expertise topics.
- Availability.
- Background.
- Mentoring style.
- Request CTA.
- Rating/feedback nếu có, nhưng không marketplace hóa.
- Scheduling section future.

### Required States

```txt
available
limited
full
paused
unavailable
suspended
request_pending
```

### Design Notes

Không làm mentor như sản phẩm Shopee. Rating phải tinh tế, hỗ trợ trust, không biến người hướng dẫn thành món hàng.

---

## 3.17. `mentor-request.md`

### Purpose

Trang gửi và theo dõi yêu cầu mentor.

### Main Routes

```txt
/app/mentors/{mentor_id}/request
/app/mentor-requests/{request_id}
```

### Key UI

- Topic.
- Question.
- Goal.
- Urgency.
- Mentor response.
- Status timeline.
- CTA mở conversation khi accepted.
- Ask more info flow.

### Required States

```txt
draft
submitting
pending
needs_more_information
updated_by_student
accepted
declined
cancelled
expired
completed
```

### Design Notes

Student request phải đủ rõ để mentor quyết định. Không cho user gửi câu kiểu “giúp em với” rồi bắt mentor đoán như bói bài.

---

## 3.18. `mentor.md`

### Purpose

Trang chính của Mentor Matching.

### Main Route

```txt
/app/mentor
```

### Key UI

- Mentor discovery.
- Topic filters.
- Recommended mentors.
- My mentor requests.
- Become mentor / mentor access request nếu user đủ điều kiện.
- Availability status nếu user là mentor.

### Required States

```txt
no_mentor_available
filter_empty
request_pending
mentor_access_pending
mentor_access_approved
mentor_paused
```

### Responsive

Mobile: list cards + filter sheet.
Desktop: filter sidebar + mentor grid/list.

---

## 3.19. `messaging.md`

### Purpose

Trang tổng quan tin nhắn.

### Main Route

```txt
/app/messages
```

### Key UI

- Conversation list.
- Message request tab/section.
- Conversation detail desktop.
- Search conversation.
- Unread state.
- Realtime connection banner.

### Required States

```txt
empty_inbox
loading
conversation_selected
no_conversation_selected
message_request_pending
offline
reconnecting
error
```

### Responsive

Mobile dùng 2 page: list và detail. Desktop dùng two-pane layout.

---

## 3.20. `notifications.md`

### Purpose

Trang trung tâm thông báo.

### Main Route

```txt
/app/notifications
```

### Key UI

- Notification list.
- Read/unread state.
- Type icon.
- Privacy-safe preview.
- Mark as read.
- Mark all as read.
- Browser push soft prompt.

### Required Notification Types

```txt
verification approved/rejected/need more info
greeting received/accepted
message received
mentor request update
moderation action
community update
```

### Required States

```txt
empty
loading
error
unread
read
expired
push_permission_default
push_permission_denied
```

### Design Notes

Notification retention 7 ngày. Không nhét private message/evidence/report detail vào preview.

---

## 3.21. `onboarding.md`

### Purpose

Flow thiết lập ban đầu sau khi verified.

### Main Route

```txt
/app/onboarding
```

### Key UI

- Welcome screen.
- Role-based steps.
- Profile setup prompts.
- Interest/community/mentor discovery setup.
- Activation check-in style nhưng không dating.
- Skip rules nếu được phép.

### Required States

```txt
welcome
role_based_steps
profile_required
skippable_step
completed
resume_onboarding
```

### Design Notes

Avatar bắt buộc sau verification. Onboarding khác nhau theo role: student, alumni, advisor, mentor.

---

## 3.22. `post-detail.md`

### Purpose

Trang chi tiết bài viết.

### Main Route

```txt
/app/posts/{post_id}
```

### Key UI

- Full post.
- Media.
- Comments.
- Comment composer.
- Report/delete/edit actions.
- Moderation placeholder.
- Related context/community if any.

### Required States

```txt
loading
not_found
hidden_by_moderation
removed
comments_empty
comment_submitting
comment_failed
restricted
```

### Design Notes

Comment design phải rõ hierarchy, dễ đọc, dễ report. Không giấu action quan trọng chỉ bằng hover vì mobile không có hover, thật kỳ lạ nhưng đúng.

---

## 3.23. `privacy.md`

### Purpose

Trang quyền riêng tư.

### Main Route

```txt
/app/settings/privacy
```

### Key UI

- Profile visibility.
- Discovery visibility.
- Public fields control.
- Message/greeting permissions nếu có.
- Read receipts setting nếu có.
- Blocked users link.

### Required States

```txt
default
saving
saved
error
restricted_locked
```

### Design Notes

Privacy copy phải giải thích ai có thể thấy thông tin gì. Không được chỉ có switch mơ hồ kiểu “Public profile: on”.

---

## 3.24. `profile-edit.md`

### Purpose

Trang chỉnh sửa hồ sơ.

### Main Route

```txt
/app/profile/edit
```

### Key UI

- Avatar upload.
- Basic info.
- Academic info.
- Bio.
- Interests.
- Skills/topics.
- Privacy hints.
- Save/cancel.
- Unsaved changes warning.

### Required States

```txt
loading
dirty
saving
saved
validation_error
uploading_avatar
avatar_required
```

### Design Notes

Một số field có thể bị readonly nếu đã verified như email/MSSV/role tùy policy.

---

## 3.25. `profile-setup.md`

### Purpose

Trang thiết lập hồ sơ bắt buộc sau verification.

Khác với `profile-edit.md` vì đây là flow bắt buộc để mở app đầy đủ.

### Main Route

```txt
/app/profile/setup
```

### Key UI

- Required avatar.
- Display name.
- Role-specific required fields.
- Faculty/major/cohort.
- Bio/interests.
- Completion progress.
- Continue to app CTA.

### Required States

```txt
initial
incomplete
validation_error
saving
completed
```

### Design Notes

Sau khi hoàn tất chuyển `/app/home`.

---

## 3.26. `profile.md`

### Purpose

Trang hồ sơ cá nhân/public profile chính.

### Main Routes

```txt
/app/profile
/app/users/{user_id}
```

### Key UI

- Profile header.
- Avatar.
- Verification badge.
- Role badge.
- Academic/career summary.
- Bio.
- Posts.
- Communities.
- Mentor section if applicable.
- Greeting/connect/message/report actions.

### Required States

```txt
own_profile
public_profile
limited_private_profile
blocked_hidden
not_found
restricted
```

### Design Notes

Tách rõ own profile và public profile. Không lộ private fields qua DOM/accessibility layer.

---

## 3.27. `resource-library.md`

### Purpose

Trang thư viện tài nguyên.

Có thể scoped theo community hoặc global curated resources.

### Main Routes

```txt
/app/resources
/app/communities/{id}/resources
```

### Key UI

- Resource list.
- Search/filter.
- File/link cards.
- Upload/submit resource.
- Review status.
- Copyright attestation.
- Preview/download/open action.

### Required States

```txt
empty
loading
filter_empty
pending_review
approved
rejected
hidden_by_moderation
unavailable
```

### Design Notes

Tài nguyên cần kiểm soát bản quyền, permission, moderation. Không expose raw storage path.

---

## 3.28. `safety-reporting.md`

### Purpose

Trang/modal báo cáo an toàn.

Dùng cho nhiều target:

```txt
profile
post
comment
message
community
mentor request
evidence abuse
```

### Main UI

Thường là modal/sheet, có thể có page lịch sử báo cáo nếu cần.

### Key UI

- Target summary.
- Reason list.
- Optional description.
- Submit.
- Auto-block notice nếu áp dụng.
- Duplicate report state.

### Reasons

```txt
Spam
Quấy rối
Giả mạo danh tính
Nội dung hẹn hò / tình dục
Vi phạm bản quyền
Lộ thông tin cá nhân
Lừa đảo
Ngôn từ công kích
Nội dung chính trị nhạy cảm
Khác
```

### Required States

```txt
idle
submitting
submitted
duplicate_pending
failed
auto_blocked
```

### Design Notes

Tone nghiêm túc, không dùng từ “tố cáo”. Report không phải mini game săn phù thủy.

---

## 3.29. `saved-profiles.md`

### Purpose

Trang hồ sơ đã lưu.

Dùng để user quay lại xem những UEers/mentor/profile quan tâm.

### Main Route

```txt
/app/saved-profiles
```

### Key UI

- Saved profile cards.
- Search/filter.
- Remove saved.
- Open profile.
- Empty state.

### Required States

```txt
empty
loading
error
removed
blocked_hidden
profile_unavailable
```

### Design Notes

Save không nên dùng heart icon nếu dễ gây dating feel. Dùng bookmark/save neutral hơn.

---

## 3.30. `search.md`

### Purpose

Trang tìm kiếm toàn cục.

Search across:

```txt
UEers
posts
communities
mentors
career pathways
resources
```

### Main Route

```txt
/app/search
```

### Key UI

- Search input.
- Recent searches.
- Category tabs.
- Filters.
- Result cards.
- Privacy-aware redaction.
- Empty state.

### Required States

```txt
idle
typing
loading
results
empty
filter_empty
error
offline
permission_limited
```

### Responsive

Mobile: full-screen search + filter bottom sheet.
Desktop: search page/topbar + filters sidebar.

---

## 3.31. `settings.md`

### Purpose

Trang cài đặt tổng quan.

### Main Route

```txt
/app/settings
```

### Key UI

- Account.
- Privacy.
- Notifications.
- Appearance future.
- Blocked users.
- Support.
- Logout.

### Required States

```txt
default
saving
saved
error
restricted
```

### Design Notes

Settings là hub, không nhồi toàn bộ form vào một page dài vô tận. Người dùng không đến settings để đọc tiểu thuyết.

---

## 3.32. `support.md`

### Purpose

Trang hỗ trợ người dùng.

Bao gồm:

- Help center basic.
- Contact/support form.
- Verification support.
- Account restriction support.
- Safety appeal/support link.

### Main Route

```txt
/app/support
```

### Key UI

- FAQ sections.
- Contact form.
- Support category.
- User account context.
- Submit ticket/request.

### Required States

```txt
default
submitting
submitted
validation_error
restricted_user_support
```

### Design Notes

Support copy phải rõ ràng, không hứa phản hồi tức thì nếu chưa có SLA.

---

## 3.33. `verification.md`

### Purpose

Trang xác thực danh tính HCMUE.

### Main Route

```txt
/app/verification
```

### Key UI

- Role selection.
- Email/domain verification status.
- MSSV/teacher/advisor info.
- Evidence upload.
- Evidence note per file.
- Submit.
- Review status.
- Admin instruction when need more info.

### Required States

```txt
not_submitted
draft
submitting
pending_review
under_review
needs_more_information
rejected
approved
conflict
suspicious
expired
```

### Design Notes

Evidence tối đa 3 file, mỗi file tối đa 5MB, hỗ trợ JPG/JPEG/PNG/PDF/WEBP/link. Admin preview qua protected route. Sau reject vẫn giữ evidence cũ.

---

# 4. Admin Page Specs

## 4.1. `admin/`

Folder admin chứa page specs cho dashboard và operation tools.

Admin UI khác student UI ở chỗ:

```txt
dense hơn
table-heavy hơn
audit-heavy hơn
permission-aware hơn
ít motion hơn
copy chính xác hơn
```

Nhưng vẫn phải:

```txt
accessible
responsive enough
token-based
Vietnamese UI
clear destructive action consequence
```

## 4.2. Admin Pages Expected

Admin folder nên có các page specs:

```txt
admin/dashboard.md
admin/users.md
admin/verification-queue.md
admin/reports.md
admin/moderation-queue.md
admin/communities.md
admin/mentor-access.md
admin/permissions.md
admin/audit-log.md
admin/settings.md
```

Nếu hiện tại chưa có đủ, nên bổ sung sau.

---

# 5. Page Spec Coverage Matrix

| Area                   | Page Specs                                                                                                         |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Auth / Account         | `auth.md`, `account.md`, `account-status.md`                                                                       |
| Verification           | `verification.md`                                                                                                  |
| Profile                | `profile.md`, `profile-edit.md`, `profile-setup.md`, `alumni-profile.md`, `mentor-profile.md`, `saved-profiles.md` |
| Feed                   | `home-feed.md`, `post-detail.md`, `composer.md`                                                                    |
| Discovery / Connection | `discovery.md`, `connection-management.md`                                                                         |
| Messaging              | `messaging.md`, `conversation.md`                                                                                  |
| Notifications          | `notifications.md`                                                                                                 |
| Mentor                 | `mentor.md`, `mentor-request.md`, `mentor-profile.md`                                                              |
| Community / Club       | `clubs.md`, `club-detail.md`, `community-channel.md`, `community-chat.md`, `resource-library.md`, `events.md`      |
| Search                 | `search.md`                                                                                                        |
| Safety                 | `safety-reporting.md`, `blocked-users.md`                                                                          |
| Settings               | `settings.md`, `privacy.md`, `support.md`                                                                          |
| Admin                  | `admin/*`                                                                                                          |

---

# 6. Route Grouping Recommendation

## 6.1. Public Routes

```txt
/
 /login
 /register
 /forgot-password
 /reset-password
```

## 6.2. Account Gate Routes

```txt
/account/status
/account/restricted
/app/verification
/app/profile/setup
/app/onboarding
```

## 6.3. Main App Routes

```txt
/app/home
/app/discovery
/app/connections
/app/messages
/app/messages/{conversation_id}
/app/notifications
/app/profile
/app/users/{user_id}
/app/mentor
/app/mentors/{mentor_id}
/app/mentor-requests/{request_id}
/app/clubs
/app/clubs/{club_id}
/app/search
/app/settings
```

## 6.4. Community Routes

```txt
/app/communities/{community_id}
/app/communities/{community_id}/channels/{channel_id}
/app/communities/{community_id}/chat
/app/communities/{community_id}/resources
/app/communities/{community_id}/events
```

## 6.5. Admin Routes

```txt
/admin
/admin/users
/admin/verification
/admin/reports
/admin/moderation
/admin/communities
/admin/mentors
/admin/permissions
/admin/audit-log
/admin/settings
```

Admin route không auto ép user vào `/admin`; chỉ vào khi user truy cập admin route và có quyền hợp lệ.

---

# 7. Global Page State Requirements

Mọi page spec phải cân nhắc các state sau:

```txt
default
loading
empty
error
offline
permission_denied
locked
restricted
not_found
stale_conflict
success_feedback
validation_error
```

Không phải trang nào cũng cần mọi state, nhưng page spec phải nêu rõ state nào có, state nào không áp dụng.

---

# 8. Responsive Rules Summary

## 8.1. Mobile

```txt
single column
bottom nav for main app
top header for page title/actions
bottom sheet for filter/action menu
full-screen modal for complex form
safe-area aware
44px touch target
no hover-only actions
```

## 8.2. Desktop

```txt
sidebar/topbar navigation
feed center column
right rail where useful
tables for admin
split view for messaging/admin review
dropdown/drawer instead of bottom sheet
```

## 8.3. Admin

```txt
desktop-first but mobile usable
table on desktop
card list on mobile
detail drawer on desktop
detail page/sheet on mobile
```

---

# 9. Accessibility Rules Summary

Mọi page spec phải đảm bảo:

```txt
semantic HTML
one clear h1
keyboard navigation
visible focus
aria-label for icon-only buttons
form labels
error association
modal/sheet focus trap
status not color-only
touch target >= 44px
reduced motion respected
private data not exposed in accessible hidden content
```

---

# 10. Content Tone Summary

UI copy phải:

```txt
Vietnamese
clear
calm
respectful
student-friendly
non-romantic
privacy-safe
```

Không dùng:

```txt
match
crush
người ấy
quẹt phải
oops
submit
invalid
are you sure
click here
```

Dùng:

```txt
Gửi lời chào
Kết nối
Khám phá
Xem hồ sơ
Báo cáo
Đã xác thực
Đang chờ duyệt
Cần bổ sung
Không thể gửi
Vui lòng thử lại
```

---

# 11. QA Checklist For This Folder

Before approving any page spec:

```txt
[ ] Page has clear purpose.
[ ] Route is defined.
[ ] Access rule is defined.
[ ] Main user roles are defined.
[ ] Mobile layout is defined.
[ ] Desktop layout is defined.
[ ] Components are listed.
[ ] Data fields are listed.
[ ] Primary actions are clear.
[ ] Secondary actions are clear.
[ ] Loading state exists if page fetches data.
[ ] Empty state exists if page can have no content.
[ ] Error state exists.
[ ] Permission/locked state exists if access-controlled.
[ ] Privacy concerns are documented.
[ ] Accessibility notes are included.
[ ] Vietnamese microcopy examples are included.
[ ] No dating-app language.
[ ] No invented product scope.
[ ] Page maps back to IA/sitemap.
```

---

# 12. Final Rule

Page specs là lớp nối giữa product requirement và UI implementation.

Một page spec tốt phải giúp designer/dev trả lời ngay:

```txt
Trang này để làm gì?
Ai dùng?
Vào từ đâu?
Thấy gì?
Bấm gì?
Khi lỗi thì sao?
Khi không có dữ liệu thì sao?
Mobile khác desktop thế nào?
Có bị khóa quyền không?
Có lộ dữ liệu riêng tư không?
Có đúng UEConnect design system không?
```

Nếu một page spec chỉ viết “Trang danh sách cộng đồng” rồi hết, thì nó chưa phải spec. Nó là một lời nhắc mơ hồ, và mơ hồ là cách bug đặt lịch hẹn với tương lai.

<!-- markdownlint-enable MD004 MD024 MD025 -->
