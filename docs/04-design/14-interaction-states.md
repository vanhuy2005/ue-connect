---
title: "Interaction States"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UI Design / Frontend"
depends_on:
  - "01-design-overview.md"
  - "02-brand-foundation.md"
  - "03-color-system.md"
  - "04-gradient-policy.md"
  - "05-typography-system.md"
  - "06-spacing-system.md"
  - "07-radius-system.md"
  - "08-shadow-elevation-system.md"
  - "09-border-system.md"
  - "10-icon-system.md"
  - "11-logo-usage-system.md"
  - "12-component-primitives.md"
  - "13-component-variants.md"
  - "15-motion-system.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
related:
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
  - "../03-product/feature-specs/profile-management.md"
  - "../03-product/feature-specs/home-feed.md"
  - "../03-product/feature-specs/post-comment.md"
  - "../03-product/feature-specs/discovery-profile.md"
  - "../03-product/feature-specs/greeting-connection.md"
  - "../03-product/feature-specs/messaging.md"
  - "../03-product/feature-specs/notification.md"
  - "../03-product/feature-specs/mentor-matching.md"
  - "../03-product/feature-specs/community-club.md"
  - "../03-product/feature-specs/search-filter.md"
  - "../03-product/feature-specs/safety-reporting.md"
  - "../03-product/feature-specs/moderation.md"
  - "../03-product/feature-specs/admin-operations.md"
---

# Interaction States

## 1. Purpose

File này định nghĩa toàn bộ trạng thái tương tác của UI trong UEConnect.

Interaction state trả lời các câu hỏi:

- Component đang bình thường hay đang hover?
- User đã focus bằng keyboard chưa?
- Button đang loading hay đã disabled?
- Form field đang lỗi gì?
- Card có thể click được không?
- Action đã thành công hay thất bại?
- Nội dung đang loading, empty, offline, locked hay restricted?
- Realtime đang connected, reconnecting hay failed?
- Optimistic UI có rollback không?
- State UI có khớp state machine nghiệp vụ không?

Nếu không có interaction states rõ ràng, người dùng sẽ bấm nút rồi nhìn màn hình như đang chờ lời tiên tri từ server. Không nên.

---

## 2. Core Principle

### 2.1. Every Interaction Must Give Feedback

Mỗi hành động của user phải có phản hồi rõ:

```txt
tap
click
hover
focus
submit
save
send
delete
upload
join
accept
decline
report
block
````

UI phải trả lời:

```txt
Tôi đã nhận thao tác.
Tôi đang xử lý.
Tôi đã xử lý xong.
Tôi không thể xử lý vì lý do cụ thể.
```

### 2.2. Interaction State Must Match Business State

Interaction state không được tự bịa.

Ví dụ:

```txt
mentor_request.status = accepted
→ button "Gửi yêu cầu mentor" disabled
→ badge "Đã chấp nhận"
→ CTA "Mở cuộc trò chuyện"
```

Không được:

```txt
mentor_request.status = accepted
→ vẫn hiện button "Gửi yêu cầu mentor"
```

Đó không phải UX, đó là bẫy bug mặc áo xanh.

### 2.3. UI State Is Not Authorization

Ẩn hoặc disable button không thay thế backend permission.

Frontend:

```txt
hide / disable / explain
```

Backend:

```txt
authorize / validate / reject
```

Nếu chỉ disable ở UI mà backend cho qua, chúc mừng, bạn vừa tạo “security bằng niềm tin”.

---

# 3. Universal Interaction States

## 3.1. State List

Mọi interactive component cần hỗ trợ các state sau nếu phù hợp:

| State           | Meaning                                      |
| --------------- | -------------------------------------------- |
| `default`       | Bình thường                                  |
| `hover`         | Pointer đang hover                           |
| `active`        | Đang nhấn/click                              |
| `focus`         | Đang focus                                   |
| `focus-visible` | Focus bằng keyboard                          |
| `disabled`      | Không thể tương tác                          |
| `readonly`      | Xem được nhưng không sửa                     |
| `loading`       | Đang xử lý                                   |
| `success`       | Hành động thành công                         |
| `error`         | Có lỗi                                       |
| `warning`       | Cần chú ý                                    |
| `selected`      | Đang được chọn                               |
| `checked`       | Checkbox/radio/switch được bật               |
| `expanded`      | Section/dropdown mở                          |
| `collapsed`     | Section/dropdown đóng                        |
| `pressed`       | Toggle button đang bật                       |
| `dragging`      | Đang kéo/thả                                 |
| `uploading`     | Đang upload                                  |
| `offline`       | Không có kết nối                             |
| `reconnecting`  | Realtime đang nối lại                        |
| `restricted`    | Bị hạn chế bởi account/permission/moderation |
| `locked`        | Có tồn tại nhưng user không thể truy cập     |
| `hidden`        | Không hiển thị cho user                      |
| `empty`         | Không có dữ liệu                             |
| `skeleton`      | Đang tải layout                              |
| `stale`         | Dữ liệu cũ cần refresh                       |

---

# 4. Pointer States

## 4.1. Default

Default là trạng thái component chưa được tương tác.

Rules:

```txt
- Visual must be clear enough without hover.
- Important actions must be discoverable.
- Do not rely on hover-only UI for mobile.
```

## 4.2. Hover

Hover dùng cho desktop/pointer devices.

Allowed changes:

```txt
background tint
border color
text color
shadow slight increase
icon color
subtle transform if approved by motion system
```

Avoid:

```txt
large movement
layout shift
sudden color jump
hidden critical controls only on hover
```

Hover should be subtle.

Nếu hover làm card nhảy lên như bị điện giật, không phải “premium”, chỉ là mệt mắt.

## 4.3. Active / Pressed

Active xảy ra khi user đang nhấn.

Recommended:

```txt
slightly darker background
slightly reduced elevation
subtle scale 0.98 only if motion allows
```

Rules:

```txt
- Active state must feel immediate.
- Do not delay press feedback.
- Do not use active state to trigger destructive action without confirmation.
```

## 4.4. Touch State

Mobile không có hover.

Therefore:

```txt
- Main actions must be visible without hover.
- Secondary actions should be accessible by visible menu button.
- Press feedback should be immediate.
- Touch target minimum 44x44.
```

---

# 5. Focus States

## 5.1. Focus

Focus xảy ra khi element nhận input focus.

Must apply to:

```txt
button
link
input
textarea
select
checkbox
radio
switch
tab
dropdown trigger
modal close
card if clickable
```

## 5.2. Focus-visible

Focus-visible ưu tiên cho keyboard navigation.

Recommended token:

```txt
outline: 2px solid color.focus.ring
outline-offset: 2px
```

Color direction:

```txt
focus ring: HCMUE Blue / accessible blue
```

## 5.3. Focus Rules

```txt
- Never remove focus outline without replacement.
- Focus must be visible on light and dark background.
- Focus must not be hidden behind overflow.
- Focus order must follow visual order.
- Modal and sheet must trap focus.
```

## 5.4. Focus for Cards

Clickable card must either:

```txt
- use real anchor/button wrapper
- or have keyboard handler + role + tabindex
```

Preferred:

```txt
Use anchor if card navigates.
Use button if card performs action.
```

## 5.5. Focus in Complex Cards

If card contains multiple actions:

```txt
- Do not make entire card clickable.
- Make primary area/link explicit.
- Keep separate buttons keyboard reachable.
```

---

# 6. Disabled States

## 6.1. Meaning

Disabled nghĩa là user hiện tại không thể tương tác với control.

Examples:

```txt
Gửi yêu cầu mentor disabled vì mentor đang tạm dừng.
Gửi tin nhắn disabled vì chưa kết nối.
Tham gia cộng đồng disabled vì cộng đồng bị tạm khóa.
Lưu disabled vì form chưa thay đổi.
```

## 6.2. Disabled Visual

Recommended:

```txt
opacity reduced
muted text
muted background
cursor not-allowed only on desktop
no hover effect
```

## 6.3. Disabled Rules

```txt
- Disabled control must not submit action.
- Disabled reason should be visible or discoverable.
- Do not disable without explanation in important flows.
- Disabled is not enough for permission; backend must enforce.
```

## 6.4. Disabled With Explanation

Good:

```txt
Button: Gửi yêu cầu mentor
Disabled helper: Mentor này hiện đang tạm dừng nhận yêu cầu.
```

Bad:

```txt
Button disabled silently.
```

Im lặng không phải là UX. Im lặng là server đang phán xét người dùng mà không nói lý do.

## 6.5. Disabled vs Hidden

| Use          | When                                                |
| ------------ | --------------------------------------------------- |
| `disabled`   | User should know action exists but unavailable      |
| `hidden`     | User should not know or does not need action        |
| `locked`     | User can see feature but needs condition/permission |
| `restricted` | User/account/content is limited                     |

Example:

```txt
Unverified user sees locked app feature.
Blocked user target is hidden from discovery.
Mentor paused makes request button disabled with explanation.
```

---

# 7. Readonly States

## 7.1. Meaning

Readonly nghĩa là user xem được giá trị nhưng không sửa được.

Examples:

```txt
Verified email
Approved MSSV
Admin-only field
Submitted evidence preview
Audit log value
```

## 7.2. Visual

Recommended:

```txt
background: muted surface
text: primary or secondary
border: subtle
cursor: default
```

## 7.3. Rules

```txt
- Readonly field can still be focusable if user may copy text.
- Do not make readonly look like disabled if value is important.
- Explain why field cannot be edited when needed.
```

---

# 8. Loading States

## 8.1. Loading Types

| Loading Type   | Usage                                        |
| -------------- | -------------------------------------------- |
| `skeleton`     | Page/card/list loading                       |
| `spinner`      | Short inline/button action                   |
| `progress`     | File upload/long task                        |
| `optimistic`   | Immediate UI update before server confirms   |
| `blocking`     | User cannot continue until done              |
| `non_blocking` | User can continue while background task runs |

## 8.2. Skeleton Loading

Use for:

```txt
feed posts
profile cards
mentor cards
community cards
career pathway cards
notification list
admin tables
message list initial load
```

Rules:

```txt
- Skeleton should match final layout.
- Avoid layout shift.
- Do not use generic spinner for full feed/list.
```

## 8.3. Spinner Loading

Use for:

```txt
button submit
small inline refresh
dropdown loading
short menu action
```

Rules:

```txt
- Button loading disables duplicate submit.
- Spinner should not replace all text unless space requires.
- Loading state should preserve button width if possible.
```

## 8.4. Upload Progress

Use for:

```txt
verification evidence
avatar upload
post image upload
message attachment
community resource
```

Display:

```txt
file name if safe
file type
file size
progress bar
cancel/remove action if allowed
error state per file
```

Do not expose raw storage path. Dĩ nhiên rồi, nhưng phần mềm thích lộ thứ không nên lộ.

## 8.5. Loading Copy

Good:

```txt
Đang gửi...
Đang tải bài viết...
Đang tải cộng đồng...
Đang tải tin nhắn...
Đang upload tệp...
```

Avoid:

```txt
Loading...
Processing...
Wait...
```

UI tiếng Việt thì loading cũng nên tiếng Việt. Đừng nửa Việt nửa Anh như commit message lúc 3 giờ sáng.

---

# 9. Success States

## 9.1. Meaning

Success state xác nhận action đã hoàn tất.

Examples:

```txt
Đã gửi lời chào.
Đã lưu thay đổi.
Tài khoản đã được xác thực.
Yêu cầu mentor đã được gửi.
Đã tham gia cộng đồng.
```

## 9.2. Success Feedback Types

| Type             | Usage               |
| ---------------- | ------------------- |
| Toast            | Short feedback      |
| Inline success   | Form field/section  |
| Success page     | Major flow complete |
| Badge            | Persistent status   |
| Redirect + toast | Common app action   |
| Notification     | Async/remote event  |

## 9.3. Success Rules

```txt
- Show success only after server confirms unless optimistic UI is safe.
- For major flows, show next action.
- Do not overuse green celebration.
- Do not use confetti except rare onboarding milestone, if approved.
```

## 9.4. Examples

Verification submitted:

```txt
Title: Đã gửi yêu cầu xác thực
Description: Admin sẽ xem xét thông tin của bạn. Bạn sẽ nhận thông báo khi có kết quả.
```

Greeting sent:

```txt
Toast: Đã gửi lời chào.
```

Mentor request accepted:

```txt
Title: Yêu cầu mentor đã được chấp nhận
Action: Mở cuộc trò chuyện
```

---

# 10. Error States

## 10.1. Meaning

Error state nói rõ điều gì không thành công và user có thể làm gì tiếp theo.

## 10.2. Error Types

| Error Type        | Example                    |
| ----------------- | -------------------------- |
| `validation`      | Field missing/invalid      |
| `permission`      | No access                  |
| `network`         | Connection issue           |
| `server`          | Unexpected server issue    |
| `conflict`        | State changed/stale        |
| `not_found`       | Content unavailable        |
| `restricted`      | Account/content restricted |
| `rate_limited`    | Too many attempts          |
| `upload_failed`   | File upload failed         |
| `realtime_failed` | WebSocket/realtime failed  |

## 10.3. Error Display

| Context            | Display                  |
| ------------------ | ------------------------ |
| Field validation   | Inline below field       |
| Form-level error   | Alert above form         |
| Button action fail | Toast + inline if needed |
| Page load fail     | ErrorState               |
| Permission error   | Locked/PermissionState   |
| Upload fail        | Per-file error           |
| Realtime fail      | Connection banner        |

## 10.4. Error Copy Rules

Good error copy:

```txt
- specific
- short
- human
- action-oriented
```

Bad error copy:

```txt
Error 500
Something went wrong
Invalid
Failed
```

## 10.5. Examples

Network:

```txt
Không tải được dữ liệu.
Kết nối có thể đang không ổn định. Vui lòng thử lại.
```

Permission:

```txt
Bạn không có quyền xem nội dung này.
```

Conflict:

```txt
Nội dung này đã thay đổi. Vui lòng tải lại để tiếp tục.
```

Upload:

```txt
Tệp này vượt quá giới hạn 5MB.
```

Message send fail:

```txt
Không thể gửi tin nhắn. Vui lòng thử lại.
```

## 10.6. Raw Error Rule

Do not show:

```txt
stack trace
SQL error
exception class
server file path
debug message
token
secret
```

Không ai cần thấy `SQLSTATE[23000]` ngoại trừ dev đang chịu quả báo.

---

# 11. Warning States

## 11.1. Meaning

Warning là trạng thái cần chú ý nhưng chưa phải lỗi nghiêm trọng.

Examples:

```txt
Verification needs more information.
Mentor has limited availability.
Community inactive.
Profile incomplete.
Content pending review.
```

## 11.2. Warning Display

Use:

```txt
warning badge
warning alert
helper text
soft yellow/amber surface
```

## 11.3. Warning Rules

```txt
- Warning should explain consequence.
- Warning should include action if user can fix it.
- Do not use warning for normal info.
```

Example:

```txt
Hồ sơ của bạn chưa hoàn tất.
Hoàn tất hồ sơ để sử dụng Discovery và Mentor Matching.
```

---

# 12. Selected / Checked States

## 12.1. Selected

Used for:

```txt
tabs
chips
cards
filters
menu items
navigation
```

Visual:

```txt
brand color
stronger border/background
check icon if needed
```

## 12.2. Checked

Used for:

```txt
checkbox
radio
switch
```

Rules:

```txt
- State must be clear without relying only on color.
- Checkbox/radio label must be clickable.
- Switch must expose checked state to screen reader.
```

## 12.3. Selected Card

Use for onboarding interests, role choice, pathway choice.

Visual:

```txt
border: brand
background: brand soft
check indicator
```

---

# 13. Expanded / Collapsed States

## 13.1. Used For

```txt
accordion
dropdown
filter panel
comment thread
resource detail
admin row detail
mobile nav
```

## 13.2. Rules

```txt
- Use chevron direction consistently.
- Expanded content must be keyboard reachable.
- State should persist where useful.
- Animate height carefully, respect reduced motion.
```

## 13.3. Copy

Collapsed:

```txt
Xem thêm
```

Expanded:

```txt
Thu gọn
```

---

# 14. Empty States

## 14.1. Meaning

Empty state xuất hiện khi không có dữ liệu.

Examples:

```txt
No posts
No messages
No mentors
No communities
No resources
No notifications
No search results
```

## 14.2. Empty State Anatomy

```txt
Icon / illustration
Title
Description
Primary action optional
Secondary action optional
```

## 14.3. Empty State Rules

```txt
- Explain what is empty.
- Suggest next action when possible.
- Do not blame user.
- Use serious tone in safety/admin contexts.
```

## 14.4. Examples

No notifications:

```txt
Title: Chưa có thông báo nào
Description: Khi có cập nhật mới, thông báo sẽ xuất hiện tại đây.
```

No mentor:

```txt
Title: Chưa có mentor phù hợp
Description: Hãy thử chủ đề khác hoặc quay lại sau khi có thêm mentor.
```

No community resources:

```txt
Title: Chưa có tài nguyên nào
Description: Khi tài nguyên được duyệt, chúng sẽ xuất hiện tại đây.
Action: Gửi tài nguyên
```

Search empty:

```txt
Title: Không tìm thấy kết quả phù hợp
Description: Hãy thử từ khóa khác hoặc bỏ bớt bộ lọc.
```

---

# 15. Permission / Locked States

## 15.1. Meaning

Permission state cho biết user không thể truy cập do chưa đủ điều kiện/quyền.

Examples:

```txt
not verified
profile incomplete
not community member
not connected
not mentor
not admin
no scoped permission
account restricted
```

## 15.2. Locked vs Restricted

| State               | Meaning                                                |
| ------------------- | ------------------------------------------------------ |
| `locked`            | Feature/content tồn tại nhưng user cần điều kiện để mở |
| `restricted`        | User/account/content bị hạn chế do policy/moderation   |
| `permission_denied` | User không có quyền                                    |
| `hidden`            | Không nên hiển thị sự tồn tại                          |

## 15.3. Locked State Anatomy

```txt
Icon
Title
Description
Required condition
Action if available
```

## 15.4. Examples

Unverified:

```txt
Title: Bạn cần xác thực tài khoản
Description: Hoàn tất xác thực HCMUE để sử dụng tính năng này.
Action: Đi đến xác thực
```

Community private:

```txt
Title: Cộng đồng riêng tư
Description: Bạn cần được duyệt làm thành viên để xem nội dung này.
Action: Gửi yêu cầu tham gia
```

Messaging not connected:

```txt
Title: Chưa thể nhắn tin
Description: Bạn chỉ có thể nhắn tin sau khi hai bên đã kết nối.
```

Admin no permission:

```txt
Title: Bạn không có quyền truy cập
Description: Tài khoản của bạn không có quyền thực hiện thao tác này.
```

## 15.5. Rule

Do not show locked state if revealing existence is sensitive.

Example:

```txt
Blocked profile should be hidden, not locked.
```

---

# 16. Offline States

## 16.1. Meaning

Offline state xuất hiện khi PWA không có network hoặc request thất bại do mất kết nối.

## 16.2. Offline Surfaces

```txt
global offline banner
page offline state
message composer offline state
upload disabled state
cached content notice
```

## 16.3. Offline Rules

```txt
- Do not fake successful server actions offline.
- Read-only cached content may be shown if clearly labeled.
- Sending message/post/request requires network.
- Upload requires network.
- Offline banner should be dismissible only if non-critical.
```

## 16.4. Offline Copy

Global banner:

```txt
Bạn đang ngoại tuyến. Một số tính năng cần kết nối mạng để hoạt động.
```

Message composer:

```txt
Bạn đang ngoại tuyến. Tin nhắn sẽ không được gửi lúc này.
```

Cached content:

```txt
Đây có thể là dữ liệu đã lưu trước đó.
```

## 16.5. Offline Actions

Allowed:

```txt
view cached pages
read cached pathway/resource if available
draft local text if supported
```

Not allowed in MVP:

```txt
submit verification
send greeting
send mentor request
send message
upload file
join community
report content
```

---

# 17. Realtime States

## 17.1. Used For

```txt
messaging
community chat
notifications
typing indicator
read receipts
browser push
```

## 17.2. Realtime Connection States

| State              | Meaning                 |
| ------------------ | ----------------------- |
| `connecting`       | Connecting to websocket |
| `connected`        | Realtime active         |
| `reconnecting`     | Lost, trying again      |
| `disconnected`     | Not connected           |
| `failed`           | Cannot connect          |
| `fallback_polling` | Optional fallback       |

## 17.3. UI Display

Messaging page:

```txt
Connected: no banner
Reconnecting: small subtle banner
Disconnected: warning banner
Failed: error banner + retry
```

## 17.4. Copy

Reconnecting:

```txt
Đang kết nối lại...
```

Disconnected:

```txt
Kết nối realtime bị gián đoạn. Tin nhắn mới có thể đến chậm.
```

Failed:

```txt
Không thể kết nối realtime. Vui lòng tải lại hoặc thử lại sau.
```

## 17.5. Rules

```txt
- Message send still requires API confirmation.
- WebSocket broadcast is not source of truth.
- DB state is source of truth.
- Realtime events update UI after permission check.
```

WebSocket không phải thần linh. Nó chỉ là đường ống, và đường ống thì hay rò.

---

# 18. Optimistic UI States

## 18.1. Meaning

Optimistic UI là cập nhật UI trước khi server xác nhận.

Allowed only when rollback is safe.

## 18.2. Safe Optimistic Actions

Allowed with care:

```txt
mark notification read
like/reaction if implemented
save/unsave pathway
typing indicator
message local sending bubble
filter UI change
```

## 18.3. Avoid Optimistic For

Do not use optimistic final success for:

```txt
verification submit
verification approve
mentor request accept
greeting accept
block user
report content
delete content
moderation action
community join approval
permission grant
payment if future
```

## 18.4. Optimistic Message

Message sending flow:

```txt
draft
→ sending bubble
→ sent after server confirms
→ failed with retry if server rejects
```

## 18.5. Rollback Rules

If optimistic action fails:

```txt
- revert UI state
- show error toast/inline
- preserve user input if applicable
- do not lose draft
```

Example:

```txt
User sends message
→ bubble appears as sending
→ server fails
→ bubble becomes failed
→ user can retry/delete
```

---

# 19. Conflict / Stale States

## 19.1. Meaning

Conflict xảy ra khi user thao tác trên dữ liệu đã thay đổi.

Examples:

```txt
mentor request already accepted
community already suspended
post already deleted
join request already reviewed
verification already approved by another admin
```

## 19.2. UI Response

Show:

```txt
Nội dung này đã thay đổi. Vui lòng tải lại để tiếp tục.
```

Or specific:

```txt
Yêu cầu này đã được xử lý trước đó.
```

## 19.3. Rules

```txt
- Backend must return conflict status.
- UI must refresh affected entity.
- Do not silently overwrite newer state.
```

## 19.4. Admin Conflict

For admin/moderation:

```txt
Show stale state warning.
Disable old action.
Provide refresh action.
```

Copy:

```txt
Trạng thái đã thay đổi bởi người khác. Hãy tải lại trước khi tiếp tục.
```

---

# 20. Form Interaction States

## 20.1. Form States

| State             | Meaning                |
| ----------------- | ---------------------- |
| `pristine`        | User has not changed   |
| `dirty`           | User changed           |
| `validating`      | Validation running     |
| `valid`           | Valid                  |
| `invalid`         | Has errors             |
| `submitting`      | Submit in progress     |
| `submitted`       | Submit success         |
| `failed`          | Submit failed          |
| `saved_draft`     | Draft saved            |
| `unsaved_changes` | Dirty and leaving page |

## 20.2. Validation Timing

Recommended:

```txt
required fields: validate on submit and blur
format fields: validate on blur
password: helper visible before error
long text: character count live
file upload: validate immediately
```

Avoid:

```txt
showing red error while user is still typing first character
```

Máy móc la mắng người dùng từng chữ là biểu hiện của UI thiếu giáo dục.

## 20.3. Unsaved Changes

Use for:

```txt
profile edit
mentor profile edit
community settings
admin settings
long forms
```

Copy:

```txt
Bạn có thay đổi chưa lưu. Rời khỏi trang này sẽ mất các thay đổi đó.
```

## 20.4. Submit Button States

| Form State                  | Button                                                         |
| --------------------------- | -------------------------------------------------------------- |
| pristine but required empty | enabled or disabled by pattern                                 |
| dirty valid                 | enabled                                                        |
| invalid                     | enabled with validation on submit or disabled with explanation |
| submitting                  | loading disabled                                               |
| submitted                   | success feedback                                               |
| failed                      | enabled retry                                                  |

## 20.5. Required Field

Display:

```txt
Label *
```

or:

```txt
Label
Required text in helper
```

Do not rely only on red asterisk without explanation.

---

# 21. Upload Interaction States

## 21.1. Upload States

| State           | Meaning            |
| --------------- | ------------------ |
| `idle`          | No file            |
| `selected`      | File selected      |
| `validating`    | Checking type/size |
| `invalid`       | File rejected      |
| `uploading`     | Uploading          |
| `uploaded`      | Upload complete    |
| `preview_ready` | Preview available  |
| `failed`        | Upload failed      |
| `removed`       | User removed file  |

## 21.2. Evidence Upload

Rules:

```txt
max files: 3
max size: 5MB per file
allowed: jpg/jpeg/png/pdf/webp/link
note required per file if product requires
```

States must show per file:

```txt
file type
size
status
note field
remove action
preview if allowed
```

## 21.3. Upload Error Examples

```txt
Tệp này vượt quá giới hạn 5MB.
Định dạng tệp không được hỗ trợ.
Bạn chỉ có thể tải tối đa 3 tệp.
Không thể upload tệp. Vui lòng thử lại.
```

## 21.4. Upload Security

```txt
- Do not expose raw file path.
- Use protected preview route.
- Admin preview only where permission allows.
```

---

# 22. Messaging Interaction States

## 22.1. Message Bubble States

| State                  | UI                        |
| ---------------------- | ------------------------- |
| `sending`              | faded bubble + spinner    |
| `sent`                 | normal bubble             |
| `delivered`            | subtle check if supported |
| `read`                 | read indicator if enabled |
| `edited`               | show "Đã chỉnh sửa"       |
| `failed`               | error state + retry       |
| `deleted`              | placeholder               |
| `hidden_by_moderation` | moderation placeholder    |
| `blocked`              | composer disabled         |

## 22.2. Message Composer States

| State           | UI                                |
| --------------- | --------------------------------- |
| `default`       | input active                      |
| `empty`         | send disabled or submit validates |
| `typing`        | normal                            |
| `uploading`     | attachment progress               |
| `offline`       | disabled with explanation         |
| `blocked`       | disabled with explanation         |
| `restricted`    | disabled                          |
| `not_connected` | disabled / message request flow   |
| `sending`       | send button loading               |

## 22.3. Typing Indicator

Rules:

```txt
- Show only for active conversation.
- Hide after timeout.
- Do not store as persistent event.
- Do not over-animate.
```

Copy:

```txt
Đang nhập...
```

## 22.4. Read Receipts

Rules:

```txt
- Must respect privacy if read receipts setting exists.
- Do not show overly precise tracking if not needed.
```

---

# 23. Notification Interaction States

## 23.1. Notification Item States

| State        | UI                              |
| ------------ | ------------------------------- |
| `unread`     | stronger background/dot         |
| `read`       | normal/muted                    |
| `important`  | subtle priority marker          |
| `expired`    | hidden/archived after retention |
| `actionable` | has CTA                         |
| `opened`     | marked read                     |

## 23.2. Mark As Read

Interaction:

```txt
click notification
→ open target if available
→ mark as read
```

If target unavailable:

```txt
show unavailable state
mark read if notification was opened
```

## 23.3. Browser Push Permission States

| State                 | UI                             |
| --------------------- | ------------------------------ |
| `not_supported`       | Hide or explain                |
| `default`             | Soft prompt allowed            |
| `prompting`           | Browser prompt                 |
| `granted`             | Enabled                        |
| `denied`              | Show browser settings guidance |
| `subscribed`          | Active                         |
| `subscription_failed` | Error/retry                    |

Soft prompt copy:

```txt
Bật thông báo trình duyệt để không bỏ lỡ lời chào, tin nhắn và cập nhật quan trọng.
```

Denied copy:

```txt
Trình duyệt đang chặn thông báo. Bạn có thể bật lại trong cài đặt trình duyệt.
```

---

# 24. Discovery / Greeting Interaction States

## 24.1. Discovery Card States

| State              | UI                   |
| ------------------ | -------------------- |
| `loading`          | profile skeleton     |
| `available`        | card interactive     |
| `passed`           | removed/next         |
| `greeting_pending` | CTA disabled/pending |
| `connected`        | show connected state |
| `blocked`          | hidden               |
| `unavailable`      | removed from stack   |
| `empty`            | empty state          |

## 24.2. Greeting Button States

| State        | Button                    |
| ------------ | ------------------------- |
| `can_send`   | Gửi lời chào              |
| `sending`    | Đang gửi...               |
| `pending`    | Đã gửi lời chào           |
| `accepted`   | Đã kết nối                |
| `declined`   | Không thể gửi lại         |
| `blocked`    | hidden/disabled           |
| `restricted` | disabled with explanation |

## 24.3. Swipe / Button Interaction

UEConnect có thể dùng swipe gesture nhưng phải có button fallback.

Rules:

```txt
- Swipe is optional enhancement.
- Buttons must exist for desktop/accessibility.
- Do not use dating language.
- No heart/crush/match visual language.
```

Button copy:

```txt
Bỏ qua
Gửi lời chào
Xem hồ sơ
```

Not:

```txt
Match
Crush
Swipe right
```

---

# 25. Mentor Interaction States

## 25.1. Mentor Availability States

| State         | UI              |
| ------------- | --------------- |
| `available`   | CTA active      |
| `limited`     | warning helper  |
| `full`        | CTA disabled    |
| `paused`      | CTA disabled    |
| `suspended`   | hidden/disabled |
| `unavailable` | disabled        |

## 25.2. Mentor Request Button

| State                    | Button                       |
| ------------------------ | ---------------------------- |
| `can_request`            | Gửi yêu cầu mentor           |
| `submitting`             | Đang gửi...                  |
| `pending`                | Đang chờ phản hồi            |
| `needs_more_information` | Bổ sung thông tin            |
| `accepted`               | Mở cuộc trò chuyện           |
| `declined`               | Đã từ chối                   |
| `full`                   | Mentor đang đầy yêu cầu      |
| `paused`                 | Mentor tạm dừng nhận yêu cầu |

## 25.3. Mentor Request Detail

States:

```txt
pending
needs_more_information
updated_by_student
accepted
declined
cancelled_by_student
expired
completed
reported
restricted
```

UI must show:

```txt
status badge
next action
timeline/history if available
safe explanation
```

---

# 26. Community Interaction States

## 26.1. Community Join Button

| State                          | Button                    |
| ------------------------------ | ------------------------- |
| `not_member_open`              | Tham gia                  |
| `not_member_approval_required` | Gửi yêu cầu tham gia      |
| `pending`                      | Đang chờ duyệt            |
| `active_member`                | Đã tham gia               |
| `can_leave`                    | Rời cộng đồng             |
| `closed`                       | Không nhận thành viên mới |
| `private_locked`               | Cộng đồng riêng tư        |
| `suspended`                    | Cộng đồng tạm khóa        |
| `banned_from_community`        | Không thể tham gia        |

## 26.2. Community Page States

| State                  | UI              |
| ---------------------- | --------------- |
| `public_preview`       | limited preview |
| `member_view`          | full tabs       |
| `pending_join`         | pending message |
| `private_locked`       | locked state    |
| `suspended`            | locked page     |
| `archived`             | read-only       |
| `hidden_by_moderation` | unavailable     |
| `not_found`            | unavailable     |

## 26.3. Community Chat States

```txt
active
readonly
restricted
muted
offline
reconnecting
suspended
```

Composer must reflect state.

---

# 27. Safety / Report Interaction States

## 27.1. Report Flow States

| State                 | UI                      |
| --------------------- | ----------------------- |
| `idle`                | report button available |
| `opening`             | modal opening           |
| `selecting_reason`    | reason required         |
| `describing`          | optional description    |
| `submitting`          | loading                 |
| `submitted`           | success                 |
| `duplicate_prevented` | already reported        |
| `failed`              | error                   |

## 27.2. Report Button

Rules:

```txt
- Should be available from profile, post, comment, message, community, mentor request.
- Must not be visually loud unless in menu.
- Report modal must be calm and clear.
```

## 27.3. After Report

Default behavior:

```txt
show success confirmation
apply auto block if policy says
hide target if block created
```

Copy:

```txt
Báo cáo của bạn đã được gửi. UEConnect sẽ xem xét nội dung này.
```

If auto block:

```txt
Bạn sẽ không còn nhìn thấy nội dung hoặc tương tác từ người này.
```

## 27.4. Duplicate Report

Copy:

```txt
Bạn đã báo cáo nội dung này. Vui lòng chờ phản hồi xử lý.
```

---

# 28. Moderation Interaction States

## 28.1. Moderation Queue States

| State             | UI                  |
| ----------------- | ------------------- |
| `queued`          | normal queue item   |
| `priority`        | priority marker     |
| `in_review`       | assigned/in review  |
| `action_required` | needs action        |
| `actioned`        | completed           |
| `dismissed`       | closed/no violation |
| `escalated`       | elevated            |
| `appealed`        | appeal marker       |
| `resolved`        | final               |

## 28.2. Moderation Action Button States

Actions:

```txt
dismiss
hide
delete
restore
warn
suspend
ban
```

Rules:

```txt
- Every action requires reason.
- Dangerous actions require confirmation.
- UI must show target type and consequence.
- Do not allow action if case stale.
```

## 28.3. Moderation Placeholder

Hidden content should show placeholder:

```txt
Nội dung này đang bị ẩn để xem xét.
```

Removed content:

```txt
Nội dung này đã bị gỡ do vi phạm quy định.
```

Do not show report description publicly.

---

# 29. Admin Interaction States

## 29.1. Admin Action States

| State               | Meaning                              |
| ------------------- | ------------------------------------ |
| `idle`              | Ready                                |
| `confirming`        | Confirmation modal                   |
| `processing`        | Action running                       |
| `success`           | Action done                          |
| `failed`            | Action failed                        |
| `stale`             | Data changed                         |
| `permission_denied` | Missing permission                   |
| `audit_required`    | Reason/audit required                |
| `audit_failed`      | Action should rollback or show error |

## 29.2. Admin Tables

Row states:

```txt
default
selected
hover
expanded
processing
stale
restricted
danger
```

## 29.3. Audit-required Actions

Must show reason field for:

```txt
approve/reject verification
suspend user
ban user
grant/revoke permission
suspend community
moderation action
mentor access approve/reject/revoke
```

## 29.4. Admin Success Feedback

Use:

```txt
toast
row status update
audit timeline update
```

Do not redirect randomly after admin action unless workflow requires it. Admins already suffer enough.

---

# 30. Search / Filter Interaction States

## 30.1. Search Input States

| State               | UI               |
| ------------------- | ---------------- |
| `idle`              | placeholder      |
| `typing`            | clear button     |
| `loading`           | spinner/skeleton |
| `loaded`            | result list      |
| `empty`             | empty state      |
| `error`             | error state      |
| `invalid_query`     | validation       |
| `offline`           | disabled/error   |
| `permission_denied` | locked           |

## 30.2. Filter States

| State       | UI             |
| ----------- | -------------- |
| `inactive`  | default chip   |
| `active`    | selected chip  |
| `removable` | active with x  |
| `disabled`  | unavailable    |
| `applying`  | loading        |
| `cleared`   | default result |

## 30.3. Result Card States

```txt
visible
locked
redacted
unavailable
hidden
```

Search must respect privacy and block rules.

---

# 31. Visual Token Mapping

## 31.1. Recommended Color Mapping

| Interaction State | Token Direction             |
| ----------------- | --------------------------- |
| default           | surface/text normal         |
| hover             | surface hover / brand hover |
| active            | deeper surface / pressed    |
| focus             | focus ring                  |
| disabled          | muted surface/text          |
| loading           | muted + spinner/skeleton    |
| success           | success token               |
| warning           | warning token               |
| error/danger      | danger token                |
| selected          | brand soft + brand border   |
| locked            | muted/warning mix           |
| restricted        | danger/warning              |
| offline           | warning/neutral             |
| skeleton          | surface skeleton            |
| unread            | brand soft                  |

## 31.2. Shadow Mapping

| State                  | Shadow                |
| ---------------------- | --------------------- |
| default card           | shadow.sm             |
| hover interactive card | shadow.md             |
| active                 | shadow.xs / lowered   |
| modal/sheet            | shadow.xl             |
| toast                  | shadow.lg             |
| disabled               | no elevation increase |

## 31.3. Border Mapping

| State    | Border         |
| -------- | -------------- |
| default  | border.default |
| hover    | border.hover   |
| focus    | border.focus   |
| error    | border.danger  |
| success  | border.success |
| warning  | border.warning |
| selected | border.brand   |
| disabled | border.muted   |

---

# 32. Motion Rules for Interaction States

## 32.1. Allowed Motion

```txt
hover transition
focus ring transition
modal fade/scale
sheet slide
toast slide/fade
skeleton shimmer subtle
button press subtle
dropdown fade/slide
```

## 32.2. Duration

Recommended:

```txt
fast: 100ms - 150ms
default: 150ms - 220ms
slow: 250ms - 320ms
```

## 32.3. Reduced Motion

If user prefers reduced motion:

```txt
disable shimmer
disable large movement
use fade or instant state change
```

## 32.4. Forbidden Motion

```txt
bouncy destructive confirmation
excessive card jumping
aggressive loading animation
continuous attention animation
dating-like swipe celebration
```

Không phải cứ chuyển động là “xịn”. Đôi khi nó chỉ là UI đang nhảy múa trong khi người dùng muốn gửi form.

---

# 33. Accessibility Requirements

## 33.1. Required

```txt
visible focus
keyboard navigation
screen reader state
aria-expanded
aria-pressed
aria-selected
aria-checked
aria-disabled when applicable
aria-invalid for errors
aria-busy for loading regions
live region for important async feedback
```

## 33.2. Live Regions

Use for:

```txt
toast
form submit result
message send failed
upload completed
notification count updated
```

Example:

```html
<div role="status" aria-live="polite">
  Đã lưu thay đổi.
</div>
```

Error:

```html
<div role="alert">
  Không thể gửi tin nhắn. Vui lòng thử lại.
</div>
```

## 33.3. Do Not Rely Only On

```txt
color
hover
animation
icon alone
position alone
```

Status must include text.

---

# 34. Implementation Direction

## 34.1. CSS State Pattern

Recommended Tailwind pattern:

```txt
default classes
hover:
active:
focus-visible:
disabled:
aria-expanded:
aria-selected:
data-state:
data-status:
```

Examples:

```txt
data-state="open"
data-state="closed"
data-status="pending"
data-status="accepted"
data-loading="true"
```

## 34.2. Data Attribute Convention

Use:

```txt
data-state
data-status
data-variant
data-size
data-disabled
data-loading
```

Examples:

```html
<button data-variant="primary" data-loading="true">
  Đang gửi...
</button>
```

```html
<div data-status="pending_review">
  Đang chờ duyệt
</div>
```

## 34.3. Backend-to-UI Mapping

Create mapping helpers for business state:

```txt
VerificationStatus → Badge variant/copy
AccountStatus → Permission state/copy
GreetingStatus → Button state/copy
MentorRequestStatus → CTA state/copy
CommunityStatus → Page state/copy
ReportStatus → Queue state/copy
```

Do not duplicate mapping in every Blade file.

## 34.4. Suggested Structure

```txt
resources/views/components/ui/
  button.blade.php
  badge.blade.php
  alert.blade.php
  empty-state.blade.php
  error-state.blade.php

app/Support/Ui/
  StatusUiMapper.php
  VerificationStatusUi.php
  GreetingStatusUi.php
  CommunityStatusUi.php
```

---

# 35. QA Checklist

Before approving any interactive UI:

```txt
[ ] Default state is clear.
[ ] Hover state works on desktop.
[ ] Touch state works on mobile.
[ ] Focus-visible state is visible.
[ ] Keyboard navigation works.
[ ] Disabled state has reason if important.
[ ] Loading state prevents duplicate action.
[ ] Success feedback appears after server confirmation.
[ ] Error feedback is specific and recoverable.
[ ] Empty state has useful copy/action.
[ ] Permission/locked state explains condition.
[ ] Offline state does not fake success.
[ ] Realtime reconnect state is handled.
[ ] Optimistic update has rollback.
[ ] State matches product state machine.
[ ] State does not expose sensitive data.
[ ] Screen reader state is correct.
[ ] Reduced motion is respected.
```

---

# 36. Anti-patterns

Do not:

```txt
- Hide critical actions only on hover.
- Disable button without explaining why.
- Show success before server confirms for critical actions.
- Use spinner for full page loading when skeleton is better.
- Show raw server errors.
- Use red for normal cancel.
- Use green for primary CTA.
- Use warning to make random things noticeable.
- Let stale admin actions silently overwrite data.
- Treat WebSocket event as source of truth.
- Use hover interaction as the only way on mobile.
- Use color only to communicate status.
- Show locked state for blocked users when target should be hidden.
- Make destructive actions one-click without confirmation.
```

---

# 37. Final Rule

Interaction states are not decoration. They are product behavior expressed visually.

Before adding or changing an interaction state:

```txt
1. Define what user did.
2. Define what system is doing.
3. Define business state.
4. Define UI feedback.
5. Define accessibility state.
6. Define error/recovery behavior.
7. Define backend authorization.
8. Add test case if state affects business logic.
```

Nếu không làm đủ, UI sẽ lại rơi vào trạng thái “bấm được nhưng không biết có gì xảy ra”. Một loại địa ngục nhỏ, có border-radius.

```
```
