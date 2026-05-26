---
title: "Accessibility Rules"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UX / Frontend / QA / Accessibility"
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
  - "14-interaction-states.md"
  - "15-motion-system.md"
  - "16-content-tone.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
related:
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
  - "../03-product/feature-specs/profile-management.md"
  - "../03-product/feature-specs/onboarding.md"
  - "../03-product/feature-specs/settings-privacy.md"
  - "../03-product/feature-specs/home-feed.md"
  - "../03-product/feature-specs/post-comment.md"
  - "../03-product/feature-specs/discovery-profile.md"
  - "../03-product/feature-specs/greeting-connection.md"
  - "../03-product/feature-specs/messaging.md"
  - "../03-product/feature-specs/notification.md"
  - "../03-product/feature-specs/mentor-matching.md"
  - "../03-product/feature-specs/community-club.md"
  - "../03-product/feature-specs/career-pathway.md"
  - "../03-product/feature-specs/search-filter.md"
  - "../03-product/feature-specs/safety-reporting.md"
  - "../03-product/feature-specs/moderation.md"
  - "../03-product/feature-specs/admin-operations.md"
---

# Accessibility Rules

## 1. Purpose

Accessibility Rules định nghĩa các quy tắc bắt buộc để UEConnect có thể sử dụng được bởi nhiều nhóm người dùng hơn, bao gồm:

- Người dùng chỉ dùng bàn phím.
- Người dùng dùng screen reader.
- Người dùng có thị lực yếu.
- Người dùng nhạy cảm với chuyển động.
- Người dùng dùng điện thoại màn hình nhỏ.
- Người dùng có kết nối không ổn định.
- Người dùng cần giao diện rõ ràng trong các tình huống căng thẳng như xác thực, báo cáo, kiểm duyệt, tài khoản bị hạn chế.

Accessibility không phải phần “nice to have”. Nó là điều kiện để UEConnect trở thành một sản phẩm tử tế, đặc biệt khi sản phẩm hướng tới môi trường giáo dục.

Nếu một tính năng không dùng được bằng keyboard, không đọc được bằng screen reader, không đủ contrast, hoặc chỉ hiểu được nhờ màu sắc, thì tính năng đó chưa xong. Đừng gọi là “edge case”, người dùng thật không phải ngoại lệ để ta tiện bỏ qua.

---

## 2. Accessibility Principles

## 2.1. Perceivable

User phải có thể nhận biết nội dung.

UEConnect phải đảm bảo:

```txt
- text đủ contrast
- icon có nhãn nếu cần
- trạng thái không chỉ truyền bằng màu
- ảnh quan trọng có alt text
- video/audio nếu có phải có mô tả hoặc transcript future
````

## 2.2. Operable

User phải có thể thao tác.

UEConnect phải đảm bảo:

```txt
- dùng được bằng keyboard
- focus rõ ràng
- touch target đủ lớn
- modal/sheet/dropdown không khóa người dùng sai cách
- gesture có button fallback
```

## 2.3. Understandable

User phải hiểu chuyện gì đang xảy ra.

UEConnect phải đảm bảo:

```txt
- copy rõ ràng
- lỗi cụ thể
- form label đầy đủ
- trạng thái được giải thích
- flow không đánh đố
```

## 2.4. Robust

UI phải hoạt động tốt với công nghệ hỗ trợ và nhiều môi trường.

UEConnect phải đảm bảo:

```txt
- semantic HTML
- ARIA đúng chỗ
- không abuse div/span làm button
- state được expose cho screen reader
- responsive và PWA behavior ổn
```

## 2.5. Accessibility Is Product Quality

Accessibility không chỉ là checklist.

Nó ảnh hưởng trực tiếp tới:

```txt
auth
verification
profile setup
discovery
greeting
messaging
mentor request
community join
reporting
moderation
admin operations
```

Một app social platform cho trường đại học mà không accessible thì giống thư viện xây cầu thang nhưng quên cửa. Rất hoành tráng, rất vô dụng.

---

# 3. Target Standard

## 3.1. Compliance Target

UEConnect nên hướng tới:

```txt
WCAG 2.2 AA
```

MVP target tối thiểu:

```txt
WCAG 2.1 AA practical baseline
```

## 3.2. Priority

| Level | Meaning            |
| ----- | ------------------ |
| P0    | Bắt buộc cho MVP   |
| P1    | Cần có sớm         |
| P2    | Cải thiện sau      |
| P3    | Future enhancement |

Accessibility P0 bao gồm:

```txt
keyboard navigation
visible focus
text contrast
form labels/errors
touch target
modal focus trap
semantic buttons/links
screen reader labels for icon-only controls
reduced motion support
error/empty/permission states readable
```

---

# 4. Semantic HTML Rules

## 4.1. Use Native Elements First

Ưu tiên native HTML:

| Purpose          | Use                                    |
| ---------------- | -------------------------------------- |
| Action           | `<button>`                             |
| Navigation       | `<a>`                                  |
| Text input       | `<input>`                              |
| Multi-line input | `<textarea>`                           |
| Select           | `<select>` / combobox pattern          |
| List             | `<ul>` / `<ol>`                        |
| Table data       | `<table>`                              |
| Dialog           | `<dialog>` or accessible modal pattern |
| Heading          | `<h1>` to `<h6>`                       |
| Main content     | `<main>`                               |
| Navigation       | `<nav>`                                |
| Header           | `<header>`                             |
| Footer           | `<footer>`                             |

## 4.2. Button vs Link

Use button when action changes state:

```txt
Gửi lời chào
Lưu thay đổi
Chặn người dùng
Gửi báo cáo
Duyệt xác thực
```

Use link when navigating:

```txt
Xem hồ sơ
Mở cộng đồng
Xem lộ trình
Quay về trang chủ
```

Do not use:

```html
<div onclick="...">Gửi</div>
```

Đó không phải button. Đó là một cái div đang cosplay button, và screen reader không thích cosplay.

## 4.3. Heading Structure

Every page must have one clear main heading:

```html
<h1>Bảng tin</h1>
```

Section headings should follow order:

```txt
h1 → h2 → h3
```

Avoid skipping:

```txt
h1 → h4
```

## 4.4. Landmarks

Main app layout should include:

```html
<header>
<nav aria-label="Điều hướng chính">
<main>
<footer optional>
```

Admin layout should include:

```html
<nav aria-label="Điều hướng Admin">
<main>
```

---

# 5. Keyboard Accessibility

## 5.1. Keyboard Requirement

All interactive elements must be reachable and usable with keyboard.

Required keys:

| Key               | Behavior                                            |
| ----------------- | --------------------------------------------------- |
| `Tab`             | Move to next focusable element                      |
| `Shift + Tab`     | Move to previous                                    |
| `Enter`           | Activate link/button                                |
| `Space`           | Activate button/checkbox                            |
| `Escape`          | Close modal/dropdown/sheet                          |
| `Arrow keys`      | Navigate tabs, menus, radio groups where applicable |
| `Home/End`        | Optional for menu/tab lists                         |
| `PageUp/PageDown` | Optional for long lists                             |

## 5.2. Focus Order

Focus order must follow visual order.

Bad:

```txt
Focus jumps from navbar to footer then back to form.
```

Good:

```txt
Navbar → page heading → form fields → actions → secondary links.
```

## 5.3. Skip Link

App should provide skip link:

```html
<a href="#main-content" class="skip-link">Bỏ qua đến nội dung chính</a>
```

Skip link appears on focus.

## 5.4. Keyboard Traps

No keyboard trap except proper modal focus trap.

User must always be able to:

```txt
close modal with Escape
move within modal
return focus to triggering element
```

## 5.5. Gesture Fallback

Any gesture must have button fallback.

Discovery can support swipe, but must also show:

```txt
Bỏ qua
Gửi lời chào
Xem hồ sơ
```

Do not require swipe only. Không phải ai cũng muốn vuốt như đang chơi mini game xã hội.

---

# 6. Focus Rules

## 6.1. Visible Focus

Every focusable element must have visible focus state.

Recommended:

```css
outline: 2px solid var(--color-focus-ring);
outline-offset: 2px;
```

Focus color should be:

```txt
HCMUE blue / accessible blue
```

## 6.2. Never Remove Focus

Forbidden:

```css
outline: none;
```

Unless replaced with equally visible custom focus.

## 6.3. Focus Visible

Use `:focus-visible` where possible.

```css
.button:focus-visible {
  outline: 2px solid var(--color-focus-ring);
  outline-offset: 2px;
}
```

## 6.4. Focus in Modals

When modal opens:

```txt
- focus moves into modal
- usually to title or first meaningful field/action
- background content is not focusable
```

When modal closes:

```txt
- focus returns to trigger
```

## 6.5. Focus in Route Changes

On page navigation:

```txt
- focus should move to main heading or main content
- avoid leaving focus on old removed element
```

## 6.6. Focus in Livewire Updates

If Livewire updates a section:

```txt
- do not unexpectedly steal focus
- after form error, focus first invalid field or error summary
- after modal action, return focus predictably
```

---

# 7. Color Contrast Rules

## 7.1. Text Contrast

Minimum target:

| Text Type           |                                            Minimum Contrast |
| ------------------- | ----------------------------------------------------------: |
| Normal text         |                                                       4.5:1 |
| Large text          |                                                         3:1 |
| UI icons/components |                                                         3:1 |
| Disabled text       | Lower allowed but should remain understandable if important |

## 7.2. Brand Blue Usage

HCMUE Blue:

```txt
#124874
```

Good on:

```txt
white
light blue
light neutral
```

White text on HCMUE Blue is allowed if contrast is sufficient.

## 7.3. Do Not Use Low Contrast Text

Avoid:

```txt
light gray on white
blue text on bright cyan
white text on light gradient
muted text for important warning
```

## 7.4. Status Colors

Status must not rely on color alone.

Bad:

```txt
red dot only
green background only
yellow border only
```

Good:

```txt
Badge text: Đang chờ duyệt
Badge color: warning
Icon: clock
```

## 7.5. Gradient Contrast

On gradient background:

```txt
- use white text/logo only if contrast is stable across area
- add overlay if needed
- avoid putting small text on busy gradient
```

UEConnect gradient đẹp để làm nền, không phải để tra tấn chữ 12px.

---

# 8. Typography Accessibility

## 8.1. Minimum Font Size

Recommended:

| Context           |        Minimum |
| ----------------- | -------------: |
| Body text         |           16px |
| Helper text       |    13px - 14px |
| Badge text        | 12px but short |
| Admin dense table |   13px minimum |
| Legal/helper copy |    13px - 14px |

Avoid body text below 16px on mobile.

## 8.2. Line Height

Recommended:

| Text Type  | Line Height |
| ---------- | ----------: |
| Body       |   1.5 - 1.7 |
| Small text |   1.4 - 1.5 |
| Heading    |  1.15 - 1.3 |
| Button     |         1.2 |

## 8.3. Paragraph Width

Long text should not be too wide.

Recommended:

```txt
max-width: 65-75 characters
```

## 8.4. All Caps

Use all caps rarely.

Allowed:

```txt
small tagline
short badge
label group
```

Avoid:

```txt
long paragraph
error message
button text
```

All caps tiếng Việt nhìn như app đang quát người dùng. Cư xử đàng hoàng chút.

## 8.5. Text Scaling

UI must handle browser zoom up to:

```txt
200%
```

Without losing core functionality.

---

# 9. Touch Target Rules

## 9.1. Minimum Target

Minimum touch target:

```txt
44px x 44px
```

Applies to:

```txt
button
icon button
link in nav
checkbox/radio label
switch
tab
dropdown trigger
message action
notification item action
community join button
```

## 9.2. Spacing Between Targets

Adjacent touch targets should have enough spacing.

Recommended:

```txt
8px gap minimum
```

For dangerous actions:

```txt
larger spacing or confirmation required
```

## 9.3. Icon-only Buttons

Even if icon is 20px:

```txt
clickable area must be at least 40-44px
```

## 9.4. Mobile Bottom Navigation

Bottom nav items must be:

```txt
easy to tap
labeled
not icon-only unless labels are shown or accessible
```

Recommended:

```txt
icon + short label
```

---

# 10. Forms Accessibility

## 10.1. Label Required

Every input must have visible label or accessible label.

Good:

```html
<label for="email">Email HCMUE</label>
<input id="email" type="email" />
```

Bad:

```html
<input placeholder="Email HCMUE" />
```

Placeholder is not label. Placeholder biến mất khi người dùng nhập, đúng là một phát minh khá thiếu kiên nhẫn.

## 10.2. Required Fields

Required fields must be clearly indicated.

Use:

```txt
Tên hiển thị *
```

And/or helper:

```txt
Trường này là bắt buộc.
```

## 10.3. Error Association

Input with error must use:

```html
<input aria-invalid="true" aria-describedby="email-error" />
<p id="email-error">Email phải thuộc miền hcmue.edu.vn.</p>
```

## 10.4. Error Summary

For long forms like verification/profile/admin:

Show error summary at top:

```txt
Vui lòng kiểm tra lại 3 trường cần bổ sung.
```

Each item should link/focus to field if feasible.

## 10.5. Validation Timing

Recommended:

```txt
- validate required fields on blur and submit
- validate file immediately
- validate email format on blur
- show password policy before error
- do not yell red errors while user is typing first character
```

## 10.6. Helper Text

Helper text should be connected:

```html
<input aria-describedby="password-helper" />
<p id="password-helper">Mật khẩu cần có ít nhất 8 ký tự.</p>
```

## 10.7. Fieldsets

Use fieldset/legend for grouped radio/checkbox.

Example:

```html
<fieldset>
  <legend>Vai trò của bạn</legend>
  ...
</fieldset>
```

## 10.8. File Upload

File upload must:

```txt
- be keyboard accessible
- show accepted formats
- show max size
- show upload progress
- show per-file error
- allow remove/replace
```

Evidence upload helper:

```txt
Tối đa 3 tệp, mỗi tệp không quá 5MB. Hỗ trợ JPG, PNG, WEBP và PDF.
```

---

# 11. Button & Link Accessibility

## 11.1. Button Labels

Buttons must describe action.

Good:

```txt
Gửi lời chào
Lưu thay đổi
Tạm khóa cộng đồng
```

Bad:

```txt
OK
Submit
Yes
Click
```

## 11.2. Icon-only Button

Must include accessible label:

```html
<button aria-label="Mở bộ lọc">
  <IconFilter />
</button>
```

## 11.3. Loading Button

When loading:

```html
<button aria-busy="true" disabled>
  Đang gửi...
</button>
```

Rules:

```txt
- prevent duplicate submit
- keep label understandable
- do not only show spinner without text for major actions
```

## 11.4. Disabled Button

Disabled important actions need explanation.

Example:

```txt
Mentor này hiện đang tạm dừng nhận yêu cầu.
```

## 11.5. Link Text

Good:

```txt
Xem hồ sơ mentor
Mở cộng đồng
Xem lộ trình
```

Bad:

```txt
Tại đây
Click here
Xem thêm
```

“Xem thêm” có thể dùng trong context rõ, nhưng không nên là link trơ trọi trong screen reader.

---

# 12. Modal, Dialog & Sheet Accessibility

## 12.1. Modal Requirements

Modal must:

```txt
- have role dialog or native dialog
- have accessible name via title
- trap focus
- close with Escape unless critical forced flow
- restore focus to trigger
- prevent background interaction
- have clear close/cancel action
```

Example:

```html
<div role="dialog" aria-modal="true" aria-labelledby="report-title">
  <h2 id="report-title">Báo cáo nội dung</h2>
</div>
```

## 12.2. Confirmation Modal

Must clearly state:

```txt
what action
what consequence
how to cancel
how to confirm
```

Danger confirm button must not say only:

```txt
OK
Có
Xác nhận
```

Use:

```txt
Xóa bài viết
Chặn người dùng
Tạm khóa tài khoản
```

## 12.3. Bottom Sheet

Mobile sheet must:

```txt
- trap focus
- support Escape/back if applicable
- have close button
- keep title visible
- not rely only on drag gesture
```

## 12.4. Preview Modal

Evidence/resource preview must:

```txt
- be permission protected
- have title
- have close button
- allow keyboard navigation
- not expose raw storage path
```

---

# 13. Dropdown, Menu & Tooltip Accessibility

## 13.1. Dropdown Trigger

Trigger must be button:

```html
<button aria-haspopup="menu" aria-expanded="false">
  Mở menu
</button>
```

## 13.2. Dropdown Keyboard

Menu should support:

```txt
Escape closes
Arrow keys navigate if menu pattern used
Enter/Space activates
Tab behavior predictable
```

## 13.3. Danger Actions

Danger actions in dropdown:

```txt
- visually separated
- labeled clearly
- confirmation required for destructive actions
```

## 13.4. Tooltip

Tooltip must:

```txt
- be short
- not contain critical-only information
- not be required on mobile
- be connected with aria-describedby if needed
```

Bad:

```txt
Only tooltip explains why button disabled.
```

Good:

```txt
Disabled helper text visible near action.
```

---

# 14. Tabs Accessibility

## 14.1. Tab Structure

Use proper roles or accessible component pattern:

```html
<div role="tablist" aria-label="Cộng đồng">
  <button role="tab" aria-selected="true" aria-controls="community-feed">
    Bài viết
  </button>
</div>

<section role="tabpanel" id="community-feed">
  ...
</section>
```

## 14.2. Keyboard Behavior

Recommended:

```txt
Arrow Left/Right changes tab focus
Enter/Space activates tab
Tab moves into panel
```

## 14.3. Active State

Active tab must be indicated by:

```txt
text
aria-selected
visual style
not color alone
```

## 14.4. Mobile Tabs

Scrollable tabs must:

```txt
- remain keyboard accessible
- show active tab
- not hide important tabs completely
```

---

# 15. Table Accessibility

## 15.1. Use Real Tables

Admin dense data should use real table markup.

Required:

```html
<table>
  <caption>Yêu cầu xác thực</caption>
  <thead>
  <tbody>
</table>
```

## 15.2. Table Headers

Use `<th scope="col">`.

For row headers if needed:

```html
<th scope="row">Nguyễn Văn A</th>
```

## 15.3. Caption

Caption can be visually hidden but accessible.

Example:

```txt
Danh sách yêu cầu xác thực đang chờ xử lý.
```

## 15.4. Row Actions

Row actions must have labels.

Bad:

```txt
three dots with no aria-label
```

Good:

```html
<button aria-label="Mở thao tác cho Nguyễn Văn A">
```

## 15.5. Responsive Tables

On mobile:

```txt
- transform to card layout when possible
- or horizontal scroll with clear label
- do not make content unreadably tiny
```

## 15.6. Sortable Columns

Sortable headers must expose state:

```html
<button aria-sort="ascending">Ngày gửi</button>
```

or proper table pattern.

---

# 16. Navigation Accessibility

## 16.1. Main Navigation

Use:

```html
<nav aria-label="Điều hướng chính">
```

Admin:

```html
<nav aria-label="Điều hướng Admin">
```

## 16.2. Current Page

Use:

```html
aria-current="page"
```

Example:

```html
<a href="/app/messages" aria-current="page">Tin nhắn</a>
```

## 16.3. Mobile Navigation

Bottom nav should include:

```txt
icon
visible label
aria-current for active item
badge label if count exists
```

Notification badge accessible label:

```txt
5 thông báo chưa đọc
```

## 16.4. Breadcrumb

Use nav landmark:

```html
<nav aria-label="Breadcrumb">
```

Last item should indicate current page.

---

# 17. Image, Avatar & Icon Accessibility

## 17.1. Informative Images

If image conveys content:

```html
<img src="..." alt="Ảnh minh chứng thẻ sinh viên" />
```

## 17.2. Decorative Images

If decorative:

```html
<img src="..." alt="" aria-hidden="true" />
```

## 17.3. Avatar

User avatar:

```txt
alt = "Ảnh đại diện của [display_name]"
```

If name is next to avatar, avatar can be decorative:

```html
<img alt="" aria-hidden="true" />
```

## 17.4. Icon-only Meaning

Icon that conveys status needs accessible text.

Example:

```txt
Verified badge must expose "Đã xác thực"
```

## 17.5. Lucide Icons

Lucide icons in buttons should usually be:

```html
aria-hidden="true"
```

Button carries label.

## 17.6. Logo

Logo alt:

```txt
UEConnect
```

Clickable logo:

```txt
UEConnect - về trang chủ
```

---

# 18. Status, Badge & State Accessibility

## 18.1. Status Text Required

Badge must include visible text.

Good:

```txt
Đã xác thực
Đang chờ duyệt
Tạm khóa
```

Bad:

```txt
green dot only
red icon only
```

## 18.2. Live Status

For dynamic status updates:

```html
<div role="status" aria-live="polite">
  Đã lưu thay đổi.
</div>
```

## 18.3. Error Status

For errors:

```html
<div role="alert">
  Không thể gửi tin nhắn. Vui lòng thử lại.
</div>
```

## 18.4. Loading Status

For loading region:

```html
<section aria-busy="true">
```

If loading text exists:

```txt
Đang tải tin nhắn...
```

## 18.5. Hidden/Removed Content

Placeholder must have text:

```txt
Nội dung này đang bị ẩn để xem xét.
```

Do not show blank space like the UI got haunted.

---

# 19. Live Region Rules

## 19.1. Use Live Regions For

```txt
toast
form submit result
upload complete/fail
message send fail
notification count update
realtime reconnect/fail
moderation action result
```

## 19.2. Polite vs Assertive

Use `aria-live="polite"` for:

```txt
success toast
notification count
message sent
upload completed
```

Use `role="alert"` or assertive for:

```txt
critical error
form submit failed
permission denied
connection lost in active chat
```

## 19.3. Do Not Overuse

Do not announce every tiny feed update.

Screen reader spam is still spam, just more personal.

---

# 20. Motion Accessibility

## 20.1. Reduced Motion

Must support:

```css
@media (prefers-reduced-motion: reduce)
```

When reduced motion:

```txt
- disable large slide/scale
- disable parallax
- disable skeleton shimmer
- reduce continuous animations
- use fade/instant instead
```

## 20.2. Avoid Flashing

No flashing content.

Do not create animation that flashes more than 3 times per second.

## 20.3. Swipe Motion

Discovery swipe must:

```txt
- not be required
- have button fallback
- respect reduced motion
- avoid dating-like celebration
```

## 20.4. Loading Animation

Continuous animations must be subtle and stoppable/reduced.

---

# 21. Realtime & Messaging Accessibility

## 21.1. Message List

Message list should:

```txt
- preserve reading position
- not steal focus when new messages arrive
- announce new message only if conversation is active and useful
- support keyboard navigation
```

## 21.2. Sending Message

States must be readable:

```txt
Đang gửi
Đã gửi
Không gửi được
Đã chỉnh sửa
Tin nhắn đã được xóa
```

## 21.3. Typing Indicator

Typing indicator:

```txt
Đang nhập...
```

Do not rely only on animated dots.

## 21.4. Read Receipts

If enabled:

```txt
Đã xem
```

Do not make read receipts the only way to understand message state.

## 21.5. Realtime Disconnected

Active chat must show:

```txt
Kết nối realtime bị gián đoạn. Tin nhắn mới có thể đến chậm.
```

---

# 22. Notification & Push Accessibility

## 22.1. Notification Center

Notification item should include:

```txt
type
short message
time
read/unread state
target action
```

Unread state must not be only color.

Use:

```txt
dot + stronger background + accessible label
```

## 22.2. Mark As Read

Button label:

```txt
Đánh dấu đã đọc
```

Bulk:

```txt
Đánh dấu tất cả đã đọc
```

## 22.3. Browser Push Permission

Soft prompt must be clear:

```txt
Bật thông báo trình duyệt để không bỏ lỡ lời chào, tin nhắn và cập nhật quan trọng.
```

Must include:

```txt
Bật thông báo
Để sau
```

Do not trap user into browser permission. Người dùng không phải con mồi trong funnel.

---

# 23. Search & Filter Accessibility

## 23.1. Search Input

Search input must have label.

Visible or accessible:

```html
<label for="global-search">Tìm kiếm</label>
<input id="global-search" type="search" />
```

If visual label hidden:

```html
<label class="sr-only" for="global-search">Tìm kiếm</label>
```

## 23.2. Search Results

After search:

```txt
- announce result count if useful
- focus should remain predictable
- category tabs accessible
```

Example:

```txt
Tìm thấy 12 kết quả.
```

## 23.3. Filter Sheet

Mobile filter sheet must:

```txt
- have title
- trap focus
- have Apply/Clear buttons
- support keyboard
```

## 23.4. Active Filter Chips

Removable chip must have accessible label:

```txt
Xóa bộ lọc Khoa CNTT
```

## 23.5. Empty Search

Use clear copy:

```txt
Không tìm thấy kết quả phù hợp. Hãy thử từ khóa khác hoặc bỏ bớt bộ lọc.
```

---

# 24. Verification Accessibility

## 24.1. Multi-step Flow

Verification must:

```txt
- show current step
- allow keyboard navigation
- preserve data when moving back
- show errors clearly
- focus first invalid field after failed submit
```

## 24.2. Evidence Upload

Must support:

```txt
keyboard file selection
drag/drop optional only
clear file requirements
per-file status
per-file note label
preview accessible name
remove file button
```

## 24.3. File Preview

Preview modal must:

```txt
- be keyboard accessible
- have title
- support close
- not expose raw file path
```

## 24.4. Admin Verification Review

Admin action buttons must be specific:

```txt
Duyệt xác thực
Từ chối xác thực
Yêu cầu bổ sung
Đánh dấu xung đột
Tạm khóa nghi vấn
```

Not:

```txt
OK
Reject
Action
```

---

# 25. Discovery & Greeting Accessibility

## 25.1. Discovery Card

Card must expose:

```txt
display name
role
short visible context
available actions
```

## 25.2. Swipe Alternative

Swipe actions require button alternatives:

```txt
Bỏ qua
Gửi lời chào
Xem hồ sơ
```

## 25.3. Gesture Labels

Do not say:

```txt
Quẹt phải để match
```

Use:

```txt
Gửi lời chào để bắt đầu kết nối.
```

## 25.4. Greeting Composer

Greeting textarea must include:

```txt
label
helper
character count
submit button
cancel button
```

Character count should be accessible but not spammy.

---

# 26. Community Accessibility

## 26.1. Community Card

Must include:

```txt
community name
type
member count if shown
join status
CTA
```

## 26.2. Community Tabs

Tabs:

```txt
Bài viết
Tài nguyên
Trò chuyện
Thành viên
Giới thiệu
```

Must be keyboard accessible.

## 26.3. Community Join State

Join button/state must explain:

```txt
Tham gia
Gửi yêu cầu tham gia
Đang chờ duyệt
Đã tham gia
Cộng đồng tạm khóa
```

## 26.4. Suspended Community

Must show accessible locked state:

```txt
Cộng đồng này hiện đang bị tạm khóa.
Một số hoạt động như đăng bài, trò chuyện và tham gia mới hiện không khả dụng.
```

## 26.5. Community Resources

Resource card must include:

```txt
title
type
status
open/download action
review status if applicable
```

File icons alone are not enough.

---

# 27. Mentor Accessibility

## 27.1. Mentor Card

Must include:

```txt
mentor name
role context
expertise topics
availability status
request action
```

Availability must use text:

```txt
Đang nhận yêu cầu
Tạm dừng nhận yêu cầu
Đã đầy yêu cầu chờ xử lý
```

## 27.2. Mentor Request Form

Must include labels:

```txt
Chủ đề cần hỗ trợ
Câu hỏi của bạn
Mục tiêu
Mức độ cần hỗ trợ
```

## 27.3. Mentor Request Status

Status detail must explain next step:

```txt
Yêu cầu của bạn đang chờ mentor phản hồi.
Mentor cần bạn bổ sung thêm thông tin.
Mentor đã chấp nhận yêu cầu của bạn.
```

---

# 28. Safety & Moderation Accessibility

## 28.1. Report Modal

Report modal must:

```txt
- have clear title
- reason choices accessible
- optional description labeled
- submit/cancel buttons
- success confirmation
```

## 28.2. Report Reasons

Reason list should use radio group or accessible list.

Reasons:

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

## 28.3. Block Modal

Must explain consequence:

```txt
Sau khi chặn, hai bên sẽ không thể nhắn tin, gửi lời chào hoặc nhìn thấy nhau trong Khám phá.
```

## 28.4. Moderation Queue

Moderation queue must be usable with keyboard.

Each item must expose:

```txt
priority
target type
reason category
status
created time
assigned moderator if any
actions
```

## 28.5. Moderation Action

Every moderation action requiring reason must:

```txt
focus reason field
announce validation error
show consequence
require confirmation for destructive actions
```

---

# 29. Admin Accessibility

## 29.1. Dense UI Does Not Mean Tiny UI

Admin can be dense, but still needs:

```txt
readable text
keyboard access
focus state
row action labels
table semantics
proper contrast
```

Admin dashboard không phải cái bảng Excel bị ép vào trình duyệt rồi cầu nguyện.

## 29.2. Admin Widgets

Metric cards must include:

```txt
metric name
value
time range
trend if shown
```

Do not rely only on arrow color.

Example:

```txt
Bài viết hôm nay: 128, tăng 12% so với hôm qua
```

## 29.3. Audit Log

Audit entries must be readable:

```txt
actor
action
target
time
status
reason if allowed
```

Do not show raw JSON as primary UI.

## 29.4. Permission Grant

Scoped permission UI must clearly state:

```txt
who receives permission
which permission
which scope
what it allows
```

Example:

```txt
Cấp quyền quản lý CLB cho Nguyễn Văn A trong cộng đồng CLB Tin học.
```

---

# 30. Privacy Accessibility

## 30.1. Privacy Settings

Privacy settings must clearly explain:

```txt
what is shown
who can see it
what changes when toggled
```

Example:

```txt
Khi bật, người dùng phù hợp có thể nhìn thấy hồ sơ của bạn trong Khám phá.
```

## 30.2. Hidden Fields

Do not visually hide privacy-sensitive fields while leaving them in accessible DOM for everyone.

If content should not be accessible:

```txt
do not render it
```

Not:

```css
display visually hidden but screen reader can read it
```

Yes, privacy bugs can happen in accessibility layer too, because misery is full-stack.

## 30.3. Blocked Users

Blocked profiles should be hidden from discovery/search. Do not expose hidden target via accessible labels.

---

# 31. PWA & Mobile Accessibility

## 31.1. Viewport

Must support:

```txt
320px width minimum
portrait mobile
tablet
desktop
browser zoom
```

## 31.2. Safe Area

For installed PWA:

```css
padding-bottom: env(safe-area-inset-bottom);
```

Especially bottom nav and sheets.

## 31.3. Orientation

Core flows should work in portrait.

Do not require landscape.

## 31.4. Offline State

Offline message must be readable:

```txt
Bạn đang ngoại tuyến. Một số tính năng cần kết nối mạng để hoạt động.
```

Do not silently fail.

## 31.5. Browser Push Permission

Do not force browser prompt immediately.

Use soft prompt with clear value and dismiss option.

---

# 32. ARIA Usage Rules

## 32.1. ARIA Is Last Resort

Use semantic HTML first.

ARIA is useful when native HTML cannot express complex component behavior.

Rule:

```txt
No ARIA is better than wrong ARIA.
```

Vì ARIA sai giống biển chỉ đường sai: nhìn có vẻ hỗ trợ, thật ra dẫn người dùng xuống hố.

## 32.2. Common ARIA Attributes

| Attribute          | Usage                         |
| ------------------ | ----------------------------- |
| `aria-label`       | Icon-only controls            |
| `aria-labelledby`  | Dialog/section named by title |
| `aria-describedby` | Helper/error text             |
| `aria-expanded`    | Dropdown/accordion            |
| `aria-controls`    | Tabs/accordion relation       |
| `aria-selected`    | Tabs/options                  |
| `aria-current`     | Current nav item              |
| `aria-invalid`     | Invalid form field            |
| `aria-busy`        | Loading region                |
| `aria-live`        | Dynamic updates               |
| `aria-modal`       | Modal dialog                  |

## 32.3. Hidden Content

Use correctly:

| Technique            | Meaning                             |
| -------------------- | ----------------------------------- |
| `hidden`             | Hidden from all users and AT        |
| `display:none`       | Hidden from all users and AT        |
| `aria-hidden="true"` | Hidden from assistive tech only     |
| `sr-only`            | Visually hidden but available to AT |

Do not put focusable elements inside `aria-hidden="true"`.

---

# 33. Laravel / Blade Implementation Direction

## 33.1. Component Props

Every component should support accessibility props when needed.

Example Button:

```php
<x-ui.button
    variant="primary"
    aria-label="Gửi lời chào"
>
    Gửi lời chào
</x-ui.button>
```

Icon Button:

```php
<x-ui.icon-button
    icon="filter"
    aria-label="Mở bộ lọc"
/>
```

Input:

```php
<x-ui.input
    name="email"
    label="Email HCMUE"
    helper="Chỉ sử dụng email thuộc miền hcmue.edu.vn."
    error="$errors->first('email')"
/>
```

## 33.2. Error Mapping

Blade form components should automatically:

```txt
set aria-invalid
set aria-describedby
render error message
render helper text
```

## 33.3. ID Generation

Components must generate stable IDs for:

```txt
label
helper
error
description
```

Avoid duplicate IDs.

## 33.4. Livewire Loading

Livewire actions should expose:

```txt
loading state
disabled duplicate submit
aria-busy where appropriate
```

Example:

```html
<button wire:loading.attr="disabled" wire:target="submit">
  <span wire:loading.remove>Gửi</span>
  <span wire:loading>Đang gửi...</span>
</button>
```

## 33.5. Alpine Components

Alpine dropdown/modal/sheet must handle:

```txt
Escape
focus trap
click outside
aria-expanded
aria-hidden
return focus
```

---

# 34. Testing Accessibility

## 34.1. Manual Tests

Every critical flow must pass:

```txt
keyboard-only navigation
screen reader smoke test
mobile touch test
zoom 200%
reduced motion
dark/blue background contrast
form error focus
modal focus trap
```

## 34.2. Automated Tools

Use:

```txt
axe DevTools
Lighthouse Accessibility
browser built-in accessibility tree
contrast checker
keyboard testing
```

Automated tools help, but they do not replace human testing. Máy kiểm tra được thiếu label, không kiểm tra được copy nghe như robot mất ngủ.

## 34.3. Screen Reader Smoke Tests

Test at least with:

```txt
NVDA + Chrome/Firefox on Windows
VoiceOver + Safari on iOS/macOS if available
TalkBack + Chrome on Android if available
```

## 34.4. Keyboard Test Checklist

For each page:

```txt
[ ] Can reach all interactive elements with Tab.
[ ] Focus order is logical.
[ ] Focus is visible.
[ ] Enter/Space activates correct controls.
[ ] Escape closes modal/dropdown/sheet.
[ ] No keyboard trap.
[ ] Current nav item is announced.
[ ] Form errors are announced.
[ ] Modal focus is trapped and restored.
```

## 34.5. Contrast Test Checklist

```txt
[ ] Body text passes 4.5:1.
[ ] Large text passes 3:1.
[ ] Buttons pass contrast.
[ ] Focus ring visible.
[ ] Status badges readable.
[ ] Text on gradient readable.
[ ] Error/warning/success states not color-only.
```

---

# 35. Accessibility by Feature

## 35.1. Authentication

Must support:

```txt
email/password labels
password helper
remember me checkbox label
error summary
keyboard submit
focus first invalid field
```

## 35.2. Verification

Must support:

```txt
stepper accessible
file upload keyboard accessible
evidence preview accessible
required fields clear
admin review action reason accessible
```

## 35.3. Profile

Must support:

```txt
avatar upload accessible
privacy controls labeled
public/private fields clear
profile completion progress labeled
```

## 35.4. Feed / Posts

Must support:

```txt
post composer label
post card semantic structure
comment input label
report/delete actions labeled
hidden content placeholder text
```

## 35.5. Discovery / Greeting

Must support:

```txt
button fallback for swipe
profile card readable
greeting composer accessible
no dating language
```

## 35.6. Messaging

Must support:

```txt
message input label
message state text
failed retry button
attachment upload accessible
new messages not stealing focus
realtime status announced if important
```

## 35.7. Notifications

Must support:

```txt
unread state text
mark as read accessible
notification target link clear
browser push prompt dismissible
```

## 35.8. Mentor

Must support:

```txt
mentor availability text
mentor request form labels
status next-step copy
rating/feedback future not color-only
```

## 35.9. Community

Must support:

```txt
community tabs
join status text
community resource status
community chat composer state
suspended community locked state
```

## 35.10. Career Pathway

Must support:

```txt
pathway sections navigable
resources links descriptive
year tabs accessible
saved state not icon-only
```

## 35.11. Search / Filter

Must support:

```txt
search label
filter sheet focus trap
active chip remove labels
result categories tabs
empty result copy
```

## 35.12. Safety / Moderation

Must support:

```txt
report modal radio group
reason labels
block consequence copy
moderation queue keyboard access
action confirmation
appeal form labels
```

## 35.13. Admin Operations

Must support:

```txt
table captions
sortable headers
row action labels
audit log readable
permission grant scope clear
dense but legible UI
```

---

# 36. Accessibility Acceptance Criteria

A feature is not ready unless:

```txt
[ ] Main flow usable with keyboard only.
[ ] Focus is visible and logical.
[ ] Form fields have labels.
[ ] Errors are associated and readable.
[ ] Buttons/links have clear names.
[ ] Icon-only controls have aria-label.
[ ] Modal/sheet/dropdown handles focus and Escape.
[ ] Text contrast passes target.
[ ] Touch targets meet minimum size.
[ ] Status is not color-only.
[ ] Loading/error/empty/permission states are accessible.
[ ] Reduced motion is respected.
[ ] Sensitive hidden content is not exposed to assistive tech.
[ ] Screen reader smoke test passes for critical flow.
```

---

# 37. Common Anti-patterns

Do not:

```txt
- Use placeholder as label.
- Remove focus outline.
- Use div as button.
- Use icon-only button with no aria-label.
- Use color only for status.
- Put critical instructions only in tooltip.
- Show disabled button without reason.
- Trap focus outside modal.
- Let modal close and lose focus randomly.
- Animate large movement without reduced motion support.
- Use tiny 12px text for important info.
- Put private hidden data in sr-only text.
- Make swipe the only interaction.
- Use table-like divs for admin data without semantics.
- Show raw technical errors.
- Create buttons labeled "OK" for destructive actions.
```

---

# 38. QA Checklist

Before final design/dev approval:

```txt
[ ] Page has correct semantic structure.
[ ] Page has one clear h1.
[ ] Main navigation has aria-label.
[ ] Current route uses aria-current.
[ ] All controls are keyboard reachable.
[ ] Focus order follows visual order.
[ ] Focus state is visible.
[ ] All inputs have labels.
[ ] Helper/error text is connected.
[ ] Error summary exists for long forms.
[ ] Buttons have clear action names.
[ ] Icon buttons have aria-label.
[ ] Links describe destination.
[ ] Modal/sheet/dropdown focus works.
[ ] Tabs follow accessible pattern.
[ ] Tables use proper headers/captions.
[ ] Status badges include visible text.
[ ] Live updates use appropriate live region.
[ ] Text contrast passes AA target.
[ ] Touch targets are at least 44px.
[ ] Reduced motion mode is respected.
[ ] Hidden sensitive data is not exposed.
[ ] Mobile and 200% zoom are usable.
```

---

# 39. Final Rule

Accessibility là một phần của định nghĩa “done”.

Trước khi ship một component/page/feature:

```txt
1. Dùng được bằng keyboard chưa?
2. Focus có rõ chưa?
3. Screen reader có hiểu chưa?
4. Người dùng có thấy lỗi và biết sửa chưa?
5. Màu có đủ contrast chưa?
6. Touch target có đủ lớn chưa?
7. Motion có giảm được chưa?
8. Có vô tình lộ dữ liệu nhạy cảm không?
9. Copy có rõ và không phán xét không?
10. State UI có khớp business state không?
```

Nếu câu trả lời là “chắc được”, thì chưa được. Accessibility không sống bằng niềm tin, dù ngành phần mềm hình như rất thích thử.

```
```
