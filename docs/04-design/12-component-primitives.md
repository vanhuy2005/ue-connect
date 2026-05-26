---
title: "Component Primitives"
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
  - "13-component-variants.md"
  - "14-interaction-states.md"
  - "15-motion-system.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
---

# Component Primitives

## 1. Purpose

Component Primitives là tập hợp các component nền tảng nhất của UEConnect.

Chúng được dùng để xây:

- Authentication UI.
- Verification flow.
- Onboarding.
- Home feed.
- Discovery profile.
- Greeting connection.
- Messaging.
- Mentor matching.
- Community / Club.
- Career Pathway.
- Notification center.
- Search / Filter.
- Admin dashboard.

Primitive không phải là page component. Primitive là lớp thấp nhất của UI system, giúp toàn bộ app nhất quán về spacing, color, typography, radius, shadow, icon, interaction và accessibility.

Nếu một màn cần button, input, card, modal, badge, avatar, tabs, dropdown, form field, toast, table, empty state, loading state, thì phải ưu tiên dùng primitives trong file này trước khi tự chế component mới.

---

## 2. Design Philosophy

### 2.1. UEConnect UI Personality

UEConnect UI phải có cảm giác:

```txt
trusted
academic
friendly
student-first
modern
clean
calm
youthful
HCMUE-rooted
````

Không được có cảm giác:

```txt
dating app
Tinder clone
corporate HR system
government portal cũ kỹ
crypto community
generic SaaS dashboard
visual chaos
```

### 2.2. Component Principles

| Principle        | Meaning                                               |
| ---------------- | ----------------------------------------------------- |
| Consistent       | Một component dùng cùng logic trên toàn app           |
| Accessible       | Keyboard, focus, screen reader, contrast đều phải ổn  |
| Composable       | Primitive có thể ghép thành module lớn                |
| Token-based      | Không hardcode màu, radius, spacing tùy hứng          |
| Mobile-first     | Component phải dùng tốt trên PWA/mobile               |
| State-aware      | Hover, focus, disabled, loading, error phải rõ        |
| Privacy-safe     | Component không vô tình hiển thị dữ liệu nhạy cảm     |
| Enterprise-ready | Có thể scale lên admin, moderation, permission, audit |

### 2.3. Primitive Rule

Không tạo primitive mới nếu component hiện tại có thể mở rộng bằng variant hợp lý.

Không thêm variant mới nếu chỉ dùng đúng một lần.

Một variant sinh ra chỉ để chiều một màn hình là khởi đầu của sự suy đồi giao diện. Vâng, UI cũng có đạo đức nghề nghiệp.

---

# 3. Primitive Inventory

## 3.1. Core Primitives

| Primitive      | Purpose                       | Priority |
| -------------- | ----------------------------- | -------- |
| Button         | Main actions                  | P0       |
| IconButton     | Icon-only actions             | P0       |
| Link           | Text navigation               | P0       |
| Input          | Text input                    | P0       |
| Textarea       | Multi-line input              | P0       |
| Select         | Single option selection       | P0       |
| Checkbox       | Boolean / multi-select        | P0       |
| Radio          | Single choice                 | P0       |
| Switch         | On/off setting                | P0       |
| FormField      | Label, helper, error wrapper  | P0       |
| Card           | Content container             | P0       |
| Badge          | Status/metadata label         | P0       |
| Avatar         | User identity visual          | P0       |
| Tabs           | Section switching             | P0       |
| Modal          | Blocking dialog               | P0       |
| Drawer / Sheet | Mobile panel / side panel     | P0       |
| Dropdown       | Action menu                   | P0       |
| Tooltip        | Short helper                  | P1       |
| Toast          | Temporary feedback            | P0       |
| Alert          | Persistent feedback           | P0       |
| Skeleton       | Loading placeholder           | P0       |
| Spinner        | Inline loading                | P0       |
| Divider        | Visual separation             | P0       |
| Table          | Dense admin data              | P1       |
| Pagination     | List navigation               | P1       |
| EmptyState     | No content UI                 | P0       |
| ErrorState     | Recoverable error UI          | P0       |
| Breadcrumb     | Admin/detail navigation       | P1       |
| Stepper        | Multi-step flow               | P0       |
| Chip           | Filter/tag/action token       | P0       |
| Progress       | Completion/progress indicator | P1       |

---

# 4. Button

## 4.1. Purpose

Button dùng cho hành động chính trong UI.

Examples:

```txt
Đăng nhập
Gửi xác thực
Hoàn tất hồ sơ
Gửi lời chào
Chấp nhận
Từ chối
Gửi yêu cầu mentor
Tham gia cộng đồng
Đánh dấu đã đọc
```

## 4.2. Variants

| Variant     | Usage                       |
| ----------- | --------------------------- |
| `primary`   | Main CTA                    |
| `secondary` | Secondary action            |
| `outline`   | Low emphasis action         |
| `ghost`     | Minimal action              |
| `danger`    | Destructive action          |
| `success`   | Positive confirmation, rare |
| `link`      | Button styled as link       |

## 4.3. Sizes

| Size | Height | Usage                    |
| ---- | -----: | ------------------------ |
| `sm` |   32px | Dense/admin/table action |
| `md` |   40px | Default                  |
| `lg` |   48px | Primary mobile/form CTA  |
| `xl` |   56px | Hero/auth main action    |

## 4.4. Primary Button

Use for the most important action on a surface.

Token direction:

```txt
background: color.brand.primary
text: color.text.inverse
hover: color.brand.primary-hover
focus-ring: color.focus.ring
radius: radius.md or radius.lg
```

Examples:

```txt
Gửi lời chào
Hoàn tất hồ sơ
Gửi yêu cầu mentor
Tham gia cộng đồng
```

Rules:

```txt
- One primary button per section when possible.
- Do not use primary for every action.
- Do not use primary for destructive actions.
```

## 4.5. Secondary Button

Use for supportive actions.

Examples:

```txt
Để sau
Xem thêm
Quay lại
Lưu nháp
```

## 4.6. Danger Button

Use for destructive or restrictive actions.

Examples:

```txt
Xóa bài viết
Chặn người dùng
Tạm khóa cộng đồng
Gỡ tài nguyên
```

Rules:

```txt
- Must require confirmation if destructive.
- Must use clear copy.
- Must not be used for normal cancel action.
```

## 4.7. Button States

| State    | Requirement                        |
| -------- | ---------------------------------- |
| Default  | Clear visual weight                |
| Hover    | Slight color/elevation change      |
| Active   | Pressed feedback                   |
| Focus    | Visible focus ring                 |
| Disabled | Reduced opacity, not clickable     |
| Loading  | Spinner + disabled                 |
| Success  | Optional short feedback            |
| Error    | Inline/toast/alert, not just color |

## 4.8. Button Content

Recommended:

```txt
Icon + Label
Label only
Spinner + Label
```

Avoid:

```txt
Icon only without accessible label
Long sentence inside button
Emoji as action icon
```

## 4.9. Accessibility

```txt
- Minimum touch target: 44px x 44px for mobile/clickable.
- Icon-only button must have aria-label.
- Loading button must communicate busy state.
- Disabled state must not be the only way to explain why action is unavailable.
```

---

# 5. IconButton

## 5.1. Purpose

IconButton dùng cho hành động nhỏ, thường trong header/card/message row.

Examples:

```txt
more
close
back
search
filter
settings
report
delete
edit
```

## 5.2. Sizes

| Size | Visual Icon | Target |
| ---- | ----------: | -----: |
| `sm` |        16px |   32px |
| `md` |        20px |   40px |
| `lg` |        24px |   44px |

## 5.3. Rules

```txt
- Must have aria-label.
- Do not use ambiguous icon without tooltip/label where action is risky.
- Danger actions require confirmation.
- More menu uses IconButton with Dropdown.
```

## 5.4. Example Labels

```txt
Mở bộ lọc
Đóng
Quay lại
Báo cáo nội dung
Xóa tin nhắn
Mở menu
```

---

# 6. Link

## 6.1. Purpose

Link dùng để điều hướng hoặc mở tài nguyên.

Examples:

```txt
Xem hồ sơ
Xem cộng đồng
Quên mật khẩu?
Xem chi tiết
Mở tài nguyên
```

## 6.2. Variants

| Variant    | Usage                 |
| ---------- | --------------------- |
| `default`  | Inline text link      |
| `subtle`   | Low emphasis          |
| `nav`      | Navigation item       |
| `external` | External URL          |
| `danger`   | Rare destructive link |

## 6.3. Rules

```txt
- External links should show external indicator if leaving app.
- Link text must be descriptive.
- Do not use "click here".
- Do not use link for action that mutates data; use button.
```

## 6.4. Accessibility

```txt
- Link must be keyboard focusable.
- Focus ring required.
- Color alone is not enough; underline on hover/focus recommended.
```

---

# 7. FormField

## 7.1. Purpose

FormField là wrapper chuẩn cho input/select/textarea/checkbox/radio.

Structure:

```txt
Label
Control
Helper text
Error text
Character count
```

## 7.2. Required Elements

| Element         |    Required | Notes                                    |
| --------------- | ----------: | ---------------------------------------- |
| Label           |         Yes | Except rare search field with aria-label |
| Control         |         Yes | Input/select/etc                         |
| Helper          |    Optional | Guidance                                 |
| Error           | Conditional | Validation                               |
| Required marker | Conditional | If required                              |
| Character count | Conditional | Long text                                |

## 7.3. FormField States

| State    | UI                       |
| -------- | ------------------------ |
| Default  | Normal                   |
| Focus    | Control ring             |
| Error    | Error text + border      |
| Success  | Optional                 |
| Disabled | Disabled styling         |
| Readonly | Clear non-editable style |
| Loading  | Skeleton/disabled        |

## 7.4. Error Copy

Error copy must be specific.

Good:

```txt
Mật khẩu phải có ít nhất 8 ký tự.
```

Bad:

```txt
Invalid input.
```

Good:

```txt
Vui lòng nhập câu hỏi mentor của bạn.
```

Bad:

```txt
Required.
```

Máy móc nói "Required", người dùng thì cần biết required cái gì. Công nghệ kỳ diệu quá.

---

# 8. Input

## 8.1. Purpose

Input dùng cho text một dòng.

Examples:

```txt
Email HCMUE
Mật khẩu
Tên hiển thị
Tìm kiếm
Ngành học
```

## 8.2. Types

| Type       | Usage    |
| ---------- | -------- |
| `text`     | General  |
| `email`    | Email    |
| `password` | Password |
| `search`   | Search   |
| `url`      | Link     |
| `number`   | Numeric  |
| `date`     | Date     |

## 8.3. Sizes

| Size | Height |
| ---- | -----: |
| `sm` |   36px |
| `md` |   44px |
| `lg` |   52px |

Default:

```txt
md
```

## 8.4. Input Rules

```txt
- Use label for every input.
- Placeholder is not label.
- Use helper text for format requirements.
- Show validation after submit or blur, not while user is still breathing into the keyboard.
```

## 8.5. Auth Input Example

Email:

```txt
Label: Email HCMUE
Placeholder: ten.ban@hcmue.edu.vn
Helper: Chỉ sử dụng email thuộc miền hcmue.edu.vn.
```

Password:

```txt
Label: Mật khẩu
Helper: Tối thiểu 8 ký tự.
```

---

# 9. Textarea

## 9.1. Purpose

Textarea dùng cho nội dung nhiều dòng.

Examples:

```txt
Giới thiệu bản thân
Lời chào
Câu hỏi mentor
Mục tiêu của bạn
Lý do tham gia cộng đồng
Mô tả báo cáo
```

## 9.2. Sizes

| Size | Min Height |
| ---- | ---------: |
| `sm` |       80px |
| `md` |      120px |
| `lg` |      180px |

## 9.3. Character Count

Use character count for:

```txt
greeting message
mentor question
report description
profile bio
community purpose
```

Format:

```txt
120 / 500
```

## 9.4. Rules

```txt
- Long text fields must have max length.
- Helper text should explain what to write.
- Error must say what is missing or too long.
```

---

# 10. Select

## 10.1. Purpose

Select dùng cho chọn một giá trị từ danh sách.

Examples:

```txt
Vai trò
Khoa
Ngành học
Khóa
Chủ đề mentor
Mức độ cần hỗ trợ
Loại cộng đồng
```

## 10.2. Variants

| Variant    | Usage              |
| ---------- | ------------------ |
| `native`   | Mobile/simple      |
| `custom`   | Searchable/complex |
| `combobox` | Large option set   |

## 10.3. Rules

```txt
- Use native select for simple mobile-friendly lists.
- Use combobox if list is long: khoa, ngành, pathway.
- Must support keyboard navigation.
- Must show selected value clearly.
```

## 10.4. Empty Option

Use:

```txt
Chọn khoa
Chọn ngành học
Chọn chủ đề
```

Not:

```txt
---
Select
Option 1
```

Một dropdown có “Option 1” trong production là dấu hiệu ai đó đã hết nhân tính.

---

# 11. Checkbox

## 11.1. Purpose

Checkbox dùng cho boolean hoặc multi-select.

Examples:

```txt
Ghi nhớ đăng nhập
Tôi xác nhận tài nguyên này hợp lệ
Chọn nhiều chủ đề quan tâm
Bật/tắt loại thông báo
```

## 11.2. Rules

```txt
- Label must be clickable.
- Use checkbox for independent choices.
- Do not use checkbox for mutually exclusive options; use radio.
```

## 11.3. Required Agreement

For legal/attestation:

```txt
Tôi xác nhận tài nguyên này không vi phạm bản quyền.
```

Must be explicit and not pre-checked.

---

# 12. Radio

## 12.1. Purpose

Radio dùng cho một lựa chọn trong nhiều lựa chọn.

Examples:

```txt
Vai trò xác thực
Mức độ cần hỗ trợ
Lý do báo cáo chính
Community visibility
```

## 12.2. Rules

```txt
- Use when all options should be visible.
- Good for 2-5 options.
- For long list, use select/combobox.
```

## 12.3. Example

Urgency:

```txt
Không gấp
Bình thường
Cần sớm
Có deadline cụ thể
```

---

# 13. Switch

## 13.1. Purpose

Switch dùng cho bật/tắt setting tức thời.

Examples:

```txt
Hiển thị hồ sơ trong Discovery
Bật thông báo trình duyệt
Tạm dừng nhận yêu cầu mentor
Cho phép nhận lời chào
```

## 13.2. Rules

```txt
- Use switch only for immediate on/off state.
- Do not use switch for dangerous actions.
- If change has serious effect, confirm or explain clearly.
```

## 13.3. Accessibility

```txt
- Must expose checked state.
- Must have label.
- Must support keyboard.
```

---

# 14. Card

## 14.1. Purpose

Card là container chính cho nội dung.

Examples:

```txt
Profile card
Post card
Mentor card
Community card
Career pathway card
Notification item
Admin metric card
Verification evidence card
```

## 14.2. Variants

| Variant       | Usage                |
| ------------- | -------------------- |
| `default`     | Standard content     |
| `interactive` | Clickable card       |
| `elevated`    | Important card       |
| `outlined`    | Low elevation        |
| `soft`        | Light tinted surface |
| `danger`      | Warning/restriction  |
| `success`     | Completed/approved   |
| `admin`       | Dense admin card     |

## 14.3. Base Style

Recommended:

```txt
background: surface.default
border: border.default
radius: radius.xl or radius.2xl
shadow: shadow.sm
padding: spacing.4 - spacing.6
```

## 14.4. Interactive Card

Rules:

```txt
- Entire card clickable only if there is one clear destination.
- Must have hover/focus state.
- Must not contain many conflicting clickable zones unless carefully handled.
```

## 14.5. Card Anatomy

```txt
Header
Body
Metadata
Actions
```

Not all cards need all parts.

---

# 15. Badge

## 15.1. Purpose

Badge hiển thị trạng thái, vai trò, loại nội dung hoặc metadata ngắn.

Examples:

```txt
Đã xác thực
Sinh viên
Cựu sinh viên
Mentor
Đang chờ duyệt
Đã duyệt
Tạm khóa
Cần bổ sung
P0
```

## 15.2. Variants

| Variant   | Usage                     |
| --------- | ------------------------- |
| `neutral` | Default metadata          |
| `brand`   | UEConnect/HCMUE highlight |
| `success` | Approved/active           |
| `warning` | Pending/needs attention   |
| `danger`  | Rejected/suspended        |
| `info`    | Informational             |
| `outline` | Low emphasis              |

## 15.3. Rules

```txt
- Badge text must be short.
- Do not use badge for long messages.
- Status badge must not rely only on color.
```

## 15.4. Standard Status Badge Mapping

| Status                   | Badge         |
| ------------------------ | ------------- |
| `active`                 | success       |
| `pending_review`         | warning       |
| `needs_more_information` | warning       |
| `approved`               | success       |
| `rejected`               | danger        |
| `suspended`              | danger        |
| `archived`               | neutral       |
| `verified`               | brand/success |
| `mentor`                 | info/brand    |

---

# 16. Avatar

## 16.1. Purpose

Avatar hiển thị danh tính user/community.

Usage:

```txt
profile
feed author
comment author
message sender
mentor card
community member
admin user row
```

## 16.2. Sizes

| Size  | Pixel |
| ----- | ----: |
| `xs`  |  24px |
| `sm`  |  32px |
| `md`  |  40px |
| `lg`  |  56px |
| `xl`  |  80px |
| `2xl` | 112px |

## 16.3. Avatar States

| State          | UI                    |
| -------------- | --------------------- |
| Image          | User uploaded         |
| Initial        | Fallback initials     |
| Placeholder    | No avatar             |
| Verified       | Badge overlay         |
| Offline/online | P2, optional          |
| Restricted     | Muted/disabled visual |

## 16.4. Rules

```txt
- Avatar is required after verification/profile setup.
- Do not show private profile data through avatar tooltip.
- Do not use random gendered placeholder.
- Community avatar is separate from user avatar.
```

## 16.5. Fallback

Fallback should use:

```txt
initials
brand soft background
neutral icon
```

Avoid:

```txt
random stock person
gendered silhouette
emoji face
```

---

# 17. Tabs

## 17.1. Purpose

Tabs chuyển đổi section trong cùng một context.

Examples:

```txt
Community: Bài viết / Tài nguyên / Trò chuyện / Thành viên / Giới thiệu
Profile: Tổng quan / Bài viết / Mentor / Cộng đồng
Admin: Tổng quan / Người dùng / Báo cáo / Audit
Notification: Tất cả / Chưa đọc / Quan trọng
```

## 17.2. Variants

| Variant     | Usage                 |
| ----------- | --------------------- |
| `line`      | Standard page tabs    |
| `pill`      | Mobile/compact        |
| `segmented` | Small switch          |
| `vertical`  | Admin/sidebar context |

## 17.3. Rules

```txt
- Use tabs for peer sections.
- Do not use tabs for sequential steps; use Stepper.
- Active tab must be visually clear.
- Tabs must be keyboard accessible.
```

---

# 18. Modal

## 18.1. Purpose

Modal dùng cho task cần tập trung hoặc xác nhận.

Examples:

```txt
Confirm delete
Report content
Block user
Ask more information
Reject verification
Suspend community
```

## 18.2. Modal Types

| Type           | Usage                     |
| -------------- | ------------------------- |
| `standard`     | Normal dialog             |
| `confirmation` | Confirm action            |
| `danger`       | Destructive confirmation  |
| `form`         | Short form                |
| `preview`      | Evidence/resource preview |
| `policy`       | Rules/explanation         |

## 18.3. Modal Rules

```txt
- Use modal sparingly.
- Must have title.
- Must have close mechanism unless forced critical.
- Must trap focus.
- Escape closes unless destructive form state requires confirmation.
- Destructive modal must require explicit action label.
```

## 18.4. Danger Modal Copy

Good:

```txt
Tạm khóa cộng đồng
Cộng đồng sẽ bị khóa bài viết, trò chuyện và yêu cầu tham gia mới.
```

Bad:

```txt
Are you sure?
```

“Are you sure?” là câu hỏi lười biếng nhất trong lịch sử UX. Hãy nói người dùng đang làm gì.

---

# 19. Drawer / Sheet

## 19.1. Purpose

Drawer/Sheet dùng cho panel phụ, đặc biệt trên mobile.

Examples:

```txt
Filter sheet
Notification panel mobile
Community member actions
Profile quick view
Message attachments
Admin side detail
```

## 19.2. Variants

| Variant        | Usage               |
| -------------- | ------------------- |
| `bottom-sheet` | Mobile              |
| `side-drawer`  | Desktop/tablet      |
| `full-screen`  | Complex mobile flow |

## 19.3. Rules

```txt
- Mobile filters should use bottom sheet.
- Side drawer can show detail while keeping list context.
- Must support close, focus trap, keyboard.
```

---

# 20. Dropdown

## 20.1. Purpose

Dropdown dùng cho action menu hoặc small selection.

Examples:

```txt
Post more menu
Message more menu
Community member action
Admin row action
Profile action
```

## 20.2. Rules

```txt
- Use for secondary actions.
- Dangerous actions must be visually separated.
- Must close on outside click/Escape.
- Must be keyboard navigable.
```

## 20.3. Standard Order

Recommended:

```txt
View
Edit
Share / Copy link
Report
Delete / Remove / Block
```

Danger action at bottom.

---

# 21. Tooltip

## 21.1. Purpose

Tooltip giải thích ngắn cho icon/action.

Examples:

```txt
Đã xác thực
Báo cáo
Lưu
Tạm dừng nhận yêu cầu
```

## 21.2. Rules

```txt
- Tooltip must be short.
- Do not put critical info only in tooltip.
- Mobile cannot rely on hover tooltip.
- Tooltip should not contain interactive elements.
```

---

# 22. Toast

## 22.1. Purpose

Toast hiển thị feedback tạm thời sau action.

Examples:

```txt
Đã gửi lời chào.
Đã lưu thay đổi.
Yêu cầu mentor đã được gửi.
Không thể gửi tin nhắn. Vui lòng thử lại.
```

## 22.2. Variants

| Variant   | Usage            |
| --------- | ---------------- |
| `success` | Action succeeded |
| `error`   | Action failed    |
| `warning` | Needs attention  |
| `info`    | Neutral update   |

## 22.3. Rules

```txt
- Toast should be short.
- Do not use toast for critical long messages.
- Critical errors should use inline error/alert.
- Toast should auto-dismiss but remain accessible.
```

---

# 23. Alert

## 23.1. Purpose

Alert dùng cho thông báo cần nằm lại trên màn hình.

Examples:

```txt
Tài khoản cần xác thực.
Cộng đồng này đang bị tạm khóa.
Bạn cần hoàn tất hồ sơ để dùng Discovery.
Tài nguyên này đang chờ duyệt.
```

## 23.2. Variants

| Variant   | Usage             |
| --------- | ----------------- |
| `info`    | General info      |
| `success` | Completed         |
| `warning` | Needs action      |
| `danger`  | Error/restriction |
| `brand`   | Product guidance  |

## 23.3. Alert Anatomy

```txt
Icon
Title
Description
Action optional
Dismiss optional
```

## 23.4. Rules

```txt
- Use clear title.
- Include action if recoverable.
- Do not stack too many alerts.
```

---

# 24. Skeleton

## 24.1. Purpose

Skeleton dùng cho loading layout preserving.

Examples:

```txt
Feed post loading
Profile card loading
Community card loading
Mentor card loading
Message list loading
Admin table loading
```

## 24.2. Rules

```txt
- Skeleton should match final layout shape.
- Avoid layout shift.
- Do not use spinner for full feed/card list if skeleton is better.
```

## 24.3. Skeleton Types

```txt
text-line
avatar
card
image
list-row
table-row
chat-bubble
```

---

# 25. Spinner

## 25.1. Purpose

Spinner dùng cho inline or short action loading.

Examples:

```txt
Button submitting
Small menu loading
Inline refresh
```

## 25.2. Rules

```txt
- Use spinner for small/short operations.
- Use skeleton for page/list loading.
- Loading button should disable repeated submit.
```

---

# 26. Divider

## 26.1. Purpose

Divider tách nhóm nội dung.

Variants:

```txt
solid
subtle
vertical
labelled
```

Rules:

```txt
- Use sparingly.
- Prefer spacing before adding lines everywhere.
- Divider color should be subtle.
```

---

# 27. Chip

## 27.1. Purpose

Chip dùng cho tags, filters, topics, selected values.

Examples:

```txt
Frontend
Giáo dục học
Mentor
Đã xác thực
Đang nhận yêu cầu
Khoa CNTT
```

## 27.2. Variants

| Variant     | Usage          |
| ----------- | -------------- |
| `tag`       | Metadata       |
| `filter`    | Filter value   |
| `selected`  | Selected state |
| `removable` | Active filter  |
| `status`    | Small status   |

## 27.3. Rules

```txt
- Chip text must be short.
- Removable chip must have accessible remove label.
- Do not use chip for paragraphs.
```

---

# 28. Table

## 28.1. Purpose

Table dùng cho dữ liệu dày, chủ yếu admin/manager.

Examples:

```txt
Verification queue
User list
Report queue
Community members
Audit log
Mentor access requests
```

## 28.2. Table Anatomy

```txt
Header
Rows
Cells
Row actions
Sort
Filter
Pagination
Empty state
Loading state
```

## 28.3. Rules

```txt
- Use table for admin dense data.
- On mobile, table should transform to cards or horizontal scroll only if acceptable.
- Row actions use dropdown or icon buttons.
- Dangerous row actions require confirmation.
```

## 28.4. Column Rule

Each table should define:

```txt
column key
label
type
sortable
filterable
responsive priority
```

---

# 29. Pagination

## 29.1. Purpose

Pagination dùng cho list nhiều dữ liệu.

Types:

```txt
page-based
cursor-based
load-more
infinite scroll
```

## 29.2. Recommended Usage

| Context             | Pagination         |
| ------------------- | ------------------ |
| Feed                | cursor / infinite  |
| Messages            | cursor             |
| Notifications       | cursor / load more |
| Admin tables        | page-based         |
| Search results      | page or cursor     |
| Community resources | page/load more     |

## 29.3. Rules

```txt
- Feed should not use numbered pagination.
- Admin table should use page-based pagination.
- Cursor pagination preferred for realtime lists.
```

---

# 30. EmptyState

## 30.1. Purpose

EmptyState hiển thị khi không có dữ liệu.

Examples:

```txt
Chưa có thông báo nào.
Chưa có mentor phù hợp.
Cộng đồng này chưa có tài nguyên.
Bạn chưa có tin nhắn.
Không tìm thấy kết quả.
```

## 30.2. Anatomy

```txt
Icon / Illustration
Title
Description
Primary action optional
Secondary action optional
```

## 30.3. Rules

```txt
- Empty state should explain what happened.
- Include next action where useful.
- Do not blame user.
- Avoid cute copy in serious contexts.
```

## 30.4. Example

```txt
Title: Chưa có tài nguyên nào
Description: Khi tài nguyên được duyệt, chúng sẽ xuất hiện tại đây.
Action: Gửi tài nguyên
```

---

# 31. ErrorState

## 31.1. Purpose

ErrorState hiển thị lỗi recoverable.

Examples:

```txt
Không tải được bài viết.
Không gửi được tin nhắn.
Không thể mở cộng đồng.
Không thể tải danh sách mentor.
```

## 31.2. Anatomy

```txt
Icon
Title
Description
Retry action
Secondary action optional
```

## 31.3. Rules

```txt
- Explain in human language.
- Offer retry if possible.
- Do not expose raw stack trace.
- Do not say "Something went wrong" alone.
```

## 31.4. Example

```txt
Title: Không tải được cộng đồng
Description: Kết nối có thể đang không ổn định. Vui lòng thử lại.
Action: Thử lại
```

---

# 32. Breadcrumb

## 32.1. Purpose

Breadcrumb dùng trong admin/detail pages.

Examples:

```txt
Admin / Người dùng / Nguyễn Văn A
Cộng đồng / CLB Tin học / Tài nguyên
Mentor / Yêu cầu / Chi tiết
```

## 32.2. Rules

```txt
- Use on deep navigation.
- Do not use on simple mobile screens unless needed.
- Last item is current page and not clickable.
```

---

# 33. Stepper

## 33.1. Purpose

Stepper dùng cho flow nhiều bước.

Examples:

```txt
Verification
Onboarding
Profile setup
Mentor access request
Community suggestion
```

## 33.2. Stepper States

```txt
not_started
current
completed
error
disabled
skipped
```

## 33.3. Rules

```txt
- Use stepper for sequence.
- Do not use tabs for required sequence.
- Show progress clearly.
- Allow back if safe.
```

---

# 34. Progress

## 34.1. Purpose

Progress hiển thị mức hoàn thành.

Examples:

```txt
Profile completion
Onboarding progress
Upload progress
Mentor profile completion
```

## 34.2. Variants

```txt
bar
circle
step-progress
```

## 34.3. Rules

```txt
- Must have accessible value.
- Do not fake progress.
- Upload progress should reflect actual upload where possible.
```

---

# 35. Primitive Composition Patterns

## 35.1. Profile Card

Uses:

```txt
Card
Avatar
Badge
Button
Chip
IconButton
```

## 35.2. Post Card

Uses:

```txt
Card
Avatar
Badge
Button
IconButton
Dropdown
Divider
Textarea
```

## 35.3. Mentor Card

Uses:

```txt
Card
Avatar
Badge
Chip
Button
Tooltip
```

## 35.4. Community Card

Uses:

```txt
Card
Avatar/Icon
Badge
Chip
Button
```

## 35.5. Admin Table Row

Uses:

```txt
Table
Badge
Avatar
Dropdown
IconButton
Tooltip
```

## 35.6. Modal Form

Uses:

```txt
Modal
FormField
Input/Textarea/Select
Button
Alert
```

---

# 36. Component State Requirements

Every interactive primitive must support:

```txt
default
hover
active
focus-visible
disabled
loading where applicable
error where applicable
success where applicable
readonly where applicable
```

## 36.1. Focus Rule

All interactive components must have visible focus ring.

Recommended:

```txt
outline: 2px solid color.focus.ring
outline-offset: 2px
```

## 36.2. Disabled Rule

Disabled component must:

```txt
- look disabled
- not be clickable
- not be focusable unless there is strong reason
- have explanation nearby if user may wonder why
```

## 36.3. Loading Rule

Loading action must:

```txt
- prevent duplicate submit
- show clear loading indicator
- preserve layout width if possible
```

---

# 37. Responsive Rules

## 37.1. Mobile-first

All primitives must work at:

```txt
320px width minimum
```

## 37.2. Touch Target

Minimum:

```txt
44px x 44px
```

Applies to:

```txt
buttons
icon buttons
checkbox/radio label target
tabs
dropdown triggers
links in mobile nav
```

## 37.3. Mobile Patterns

| Component | Mobile Behavior                   |
| --------- | --------------------------------- |
| Modal     | Often full-screen or bottom sheet |
| Table     | Card list or horizontal scroll    |
| Filter    | Bottom sheet                      |
| Tabs      | Scrollable or segmented           |
| Dropdown  | Sheet if many actions             |
| Card      | Single column                     |

---

# 38. Accessibility Rules

## 38.1. Required

```txt
- Semantic HTML first.
- Keyboard navigation.
- Visible focus.
- Correct aria-label for icon-only controls.
- Error text linked to form control.
- Contrast compliant.
- Touch targets large enough.
- Do not rely only on color.
```

## 38.2. Screen Reader Labels

Icon-only controls must define:

```txt
aria-label
```

Examples:

```txt
aria-label="Mở bộ lọc"
aria-label="Báo cáo bài viết"
aria-label="Đóng hộp thoại"
aria-label="Xóa tin nhắn"
```

## 38.3. Error Handling

Form errors must be accessible:

```txt
aria-invalid="true"
aria-describedby="field-error-id"
```

---

# 39. Content Rules

## 39.1. Button Copy

Use verbs:

```txt
Gửi
Lưu
Xác nhận
Tham gia
Báo cáo
Chặn
Tải lại
```

Avoid vague:

```txt
OK
Submit
Click
Done
```

## 39.2. Status Copy

Use clear Vietnamese:

```txt
Đang chờ duyệt
Đã xác thực
Cần bổ sung
Tạm khóa
Không khả dụng
```

## 39.3. Serious Contexts

For safety, verification, moderation:

```txt
- clear
- calm
- specific
- no jokes
- no playful copy
```

Người dùng bị reject xác thực không cần app tấu hài. Đến đây thì ta cư xử như người trưởng thành, dù rất khó.

---

# 40. Implementation Direction

## 40.1. Stack

Recommended:

```txt
Blade Components
TailwindCSS
Alpine.js
Livewire where useful
Lucide Icons
Vite
```

## 40.2. Component Folder Structure

Recommended:

```txt
resources/views/components/
  primitives/
    button.blade.php
    icon-button.blade.php
    input.blade.php
    textarea.blade.php
    select.blade.php
    checkbox.blade.php
    radio.blade.php
    switch.blade.php
    form-field.blade.php
    card.blade.php
    badge.blade.php
    avatar.blade.php
    tabs.blade.php
    modal.blade.php
    sheet.blade.php
    dropdown.blade.php
    toast.blade.php
    alert.blade.php
    skeleton.blade.php
    empty-state.blade.php
    error-state.blade.php
```

## 40.3. Naming Convention

Blade component names:

```txt
x-primitives.button
x-primitives.input
x-primitives.card
x-primitives.badge
```

Or simplified:

```txt
x-ui.button
x-ui.input
x-ui.card
```

Pick one convention and never mix both like a confused stylesheet.

## 40.4. Props Pattern

Example Button props:

```txt
variant
size
type
disabled
loading
icon
iconPosition
href optional
```

Example Badge props:

```txt
variant
size
icon
```

Example Card props:

```txt
variant
padding
interactive
href
```

---

# 41. QA Checklist

Before approving a primitive:

```txt
[ ] Uses design tokens.
[ ] Supports required variants.
[ ] Supports required sizes.
[ ] Supports hover/focus/active/disabled states.
[ ] Has accessible focus.
[ ] Works with keyboard.
[ ] Works on mobile.
[ ] Has minimum touch target.
[ ] Handles long Vietnamese text.
[ ] Handles loading/error state if applicable.
[ ] Does not hardcode random colors.
[ ] Does not create one-off styling.
[ ] Does not leak sensitive data.
[ ] Has examples in common UEConnect contexts.
```

---

# 42. Anti-patterns

Do not:

```txt
- Create page-specific button style.
- Use arbitrary Tailwind colors outside token system.
- Use icon-only action without aria-label.
- Put destructive action as primary blue button.
- Use modal for every tiny action.
- Use tooltip as the only place for important info.
- Use table on mobile without responsive plan.
- Use disabled button without explaining why.
- Use placeholder as label.
- Use spinner for entire feed loading instead of skeleton.
- Use random rounded/shadow values.
- Create 7 card styles because one page looked "a bit empty".
```

---

# 43. Final Rule

Component primitives are the foundation of UEConnect UI.

Before adding a new primitive or changing an existing primitive:

```txt
1. Check if existing primitive can support it.
2. Check if variant belongs in component-variants.md.
3. Check accessibility.
4. Check mobile behavior.
5. Check token usage.
6. Add examples.
7. Update QA checklist if needed.
```

Nếu bỏ qua bước này, mỗi màn sẽ sinh ra một component “đặc biệt”. Và rồi một ngày nào đó bạn sẽ có 14 loại button cùng tên `ButtonFinalRealNew`. Đó không phải design system, đó là khảo cổ học frontend.

```
```
