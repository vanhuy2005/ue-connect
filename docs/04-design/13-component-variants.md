---
title: "Component Variants"
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
  - "14-interaction-states.md"
  - "15-motion-system.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
  - "21-social-interaction-patterns.md"
---

# Component Variants

## 1. Purpose

File này định nghĩa toàn bộ variant hợp lệ của các component primitive trong UEConnect.

Nếu `component-primitives.md` trả lời câu hỏi:

```txt
Component nào tồn tại?
````

Thì file này trả lời:

```txt
Component đó có những kiểu nào?
Dùng kiểu nào trong ngữ cảnh nào?
Không được lạm dụng kiểu nào?
```

Mục tiêu:

* Giữ UI nhất quán.
* Tránh sinh variant tùy hứng.
* Giúp dev map variant thành Blade/Tailwind classes rõ ràng.
* Giúp QA biết màn nào dùng sai variant.
* Giúp AI/design agent không bịa thêm style như một nghệ sĩ mất kiểm soát.
* Đảm bảo UEConnect giữ được cảm giác trusted, academic, youthful, HCMUE-rooted.

---

## 2. Variant Principles

## 2.1. Variant Must Have Purpose

Mỗi variant phải có lý do rõ:

```txt
primary = hành động chính
danger = hành động nguy hiểm
warning = trạng thái cần chú ý
success = trạng thái hoàn tất/tích cực
brand = nhấn mạnh UEConnect/HCMUE
```

Không tạo variant vì:

```txt
màn này hơi trống
màu này nhìn vui
cho nó khác khác
em thấy trên Dribbble
```

Dribbble không chịu trách nhiệm production bug. Đáng tiếc là bạn thì có.

## 2.2. Variant Must Be Reusable

Một variant chỉ nên tồn tại nếu dùng được ở nhiều nơi.

Good:

```txt
Button variant="primary"
Badge variant="warning"
Card variant="interactive"
Alert variant="danger"
```

Bad:

```txt
Button variant="mentorBlueSpecial"
Card variant="homeFeedButSofter"
Badge variant="almostGreenButNotReally"
```

## 2.3. Variant Must Respect Tokens

Variant phải dùng token:

```txt
color
spacing
radius
shadow
border
typography
motion
```

Không hardcode class/màu tùy tiện trong từng page.

## 2.4. Variant Must Be State-complete

Mỗi variant interactive phải có:

```txt
default
hover
active
focus-visible
disabled
loading if applicable
```

Một variant chỉ đẹp ở trạng thái default là đồ trang trí, không phải component.

---

# 3. Global Variant Language

## 3.1. Semantic Variant Names

Các variant semantic dùng chung:

| Variant     | Meaning                         |
| ----------- | ------------------------------- |
| `primary`   | Hành động/chủ thể chính         |
| `secondary` | Hành động phụ                   |
| `tertiary`  | Hành động rất nhẹ               |
| `ghost`     | Nền trong suốt, ít nhấn mạnh    |
| `outline`   | Viền rõ, nền nhẹ/trắng          |
| `brand`     | Gắn với UEConnect/HCMUE         |
| `neutral`   | Trạng thái/thông tin trung tính |
| `info`      | Thông tin                       |
| `success`   | Thành công/đã hoàn tất          |
| `warning`   | Cần chú ý/chờ xử lý             |
| `danger`    | Lỗi/nguy hiểm/hành động phá hủy |
| `muted`     | Giảm nhấn mạnh                  |
| `inverse`   | Dùng trên nền tối/brand         |
| `admin`     | Dành cho admin/dense operation  |

## 3.2. Variant Tone Mapping

| Tone      | Color Direction                       |
| --------- | ------------------------------------- |
| `primary` | HCMUE Blue                            |
| `brand`   | HCMUE Blue / Blue gradient controlled |
| `neutral` | Slate / Gray                          |
| `info`    | Blue / Sky                            |
| `success` | Green                                 |
| `warning` | Amber / Yellow                        |
| `danger`  | Red                                   |
| `muted`   | Gray muted                            |
| `inverse` | White on dark/blue                    |
| `admin`   | Neutral dense with blue focus         |

## 3.3. Strict Rule

Không dùng màu đỏ cho anything positive.

Không dùng màu xanh lá cho action chính nếu không phải success.

Không dùng warning cho trạng thái bình thường chỉ vì “nó nổi”.

Màu sắc có nghĩa. Nếu dùng bừa, UI bắt đầu nói dối bằng màu, quá nhân loại.

---

# 4. Button Variants

## 4.1. Variant Table

| Variant          | Usage                         | Visual Weight | Main Context                       |
| ---------------- | ----------------------------- | ------------: | ---------------------------------- |
| `primary`        | Main CTA                      |          High | Auth, onboarding, submit, greeting |
| `secondary`      | Secondary CTA                 |        Medium | Back, save draft, view more        |
| `outline`        | Alternative action            |        Medium | Cancel, secondary nav              |
| `ghost`          | Low emphasis                  |           Low | Card actions, toolbar              |
| `danger`         | Destructive action            |          High | Delete, block, suspend             |
| `danger-outline` | Destructive but less dominant |        Medium | Remove, reject                     |
| `success`        | Rare positive confirmation    |        Medium | Approved status action             |
| `link`           | Text-like action              |           Low | Inline action                      |
| `inverse`        | CTA on dark/blue background   |          High | Hero, splash, blue surface         |

## 4.2. Primary Button

Use for:

```txt
Đăng nhập
Tiếp tục
Gửi xác thực
Hoàn tất hồ sơ
Gửi lời chào
Gửi yêu cầu mentor
Tham gia cộng đồng
Lưu thay đổi
```

Do not use for:

```txt
Xóa
Chặn
Tạm khóa
Từ chối
Báo cáo
```

Visual:

```txt
background: color.brand.primary
text: color.text.inverse
border: transparent
hover: darker brand blue
focus: brand focus ring
```

## 4.3. Secondary Button

Use for:

```txt
Quay lại
Để sau
Xem thêm
Lưu nháp
Chọn file khác
```

Visual:

```txt
background: color.surface.soft
text: color.text.primary
border: subtle
```

## 4.4. Outline Button

Use for:

```txt
Hủy
Xem chi tiết
Mở bộ lọc
Sửa thông tin
```

Visual:

```txt
background: transparent / white
border: color.border.default
text: color.text.primary
hover: color.surface.soft
```

## 4.5. Ghost Button

Use for:

```txt
More actions
Toolbar action
Inline card action
Close secondary action
```

Visual:

```txt
background: transparent
text: color.text.secondary
hover: color.surface.soft
```

## 4.6. Danger Button

Use for:

```txt
Xóa bài viết
Xóa tin nhắn
Chặn người dùng
Tạm khóa tài khoản
Tạm khóa cộng đồng
Gỡ nội dung
```

Rules:

```txt
- Must use confirmation for destructive irreversible actions.
- Copy must describe exact action.
- Do not label only "OK".
```

## 4.7. Danger-outline Button

Use when destructive action is not the main page CTA.

Examples:

```txt
Từ chối xác thực
Từ chối yêu cầu mentor
Gỡ thành viên
```

## 4.8. Inverse Button

Use on:

```txt
blue hero
dark splash
gradient background
```

Visual:

```txt
background: white
text: HCMUE Blue
```

## 4.9. Button Size Variants

| Size | Height | Padding     | Usage                    |
| ---- | -----: | ----------- | ------------------------ |
| `xs` |   28px | compact     | Admin small actions only |
| `sm` |   32px | compact     | Table/card dense actions |
| `md` |   40px | default     | Most desktop UI          |
| `lg` |   48px | comfortable | Forms/mobile CTA         |
| `xl` |   56px | large       | Hero/auth primary CTA    |

Default:

```txt
md desktop
lg mobile form submit
```

## 4.10. Button Width Variants

| Width   | Usage                 |
| ------- | --------------------- |
| `auto`  | Default content width |
| `full`  | Mobile form CTA       |
| `fit`   | Icon/text compact     |
| `equal` | Dialog action pair    |

Dialog:

```txt
Cancel = outline/secondary
Confirm = primary/danger
```

---

# 5. IconButton Variants

## 5.1. Variants

| Variant   | Usage                   |
| --------- | ----------------------- |
| `ghost`   | Default icon action     |
| `soft`    | Slight background       |
| `outline` | Bordered icon button    |
| `brand`   | Important brand action  |
| `danger`  | Delete/block/report     |
| `inverse` | On dark/blue background |

## 5.2. Usage Examples

| Action              | Variant                    |
| ------------------- | -------------------------- |
| Open menu           | `ghost`                    |
| Open filter         | `soft`                     |
| Close modal         | `ghost`                    |
| Report content      | `danger` or `ghost-danger` |
| Delete message      | `danger`                   |
| Back on blue header | `inverse`                  |

## 5.3. Size Variants

| Size | Button | Icon |
| ---- | -----: | ---: |
| `sm` |   32px | 16px |
| `md` |   40px | 20px |
| `lg` |   44px | 24px |

---

# 6. Link Variants

## 6.1. Variants

| Variant    | Usage                 |
| ---------- | --------------------- |
| `default`  | Normal inline link    |
| `brand`    | Important app link    |
| `muted`    | Low emphasis          |
| `nav`      | Navigation            |
| `external` | External URL          |
| `danger`   | Rare destructive link |

## 6.2. Rules

```txt
- External link should show external icon if useful.
- Do not use danger link for major destructive actions; use danger button.
- Link text must describe destination.
```

Example:

```txt
Xem chương trình đào tạo
Mở tài nguyên
Xem hồ sơ mentor
```

Bad:

```txt
Bấm vào đây
Tại đây
More
```

---

# 7. Input Variants

## 7.1. Text Input Variants

| Variant    | Usage                    |
| ---------- | ------------------------ |
| `default`  | Standard form input      |
| `filled`   | Soft background form     |
| `search`   | Search input             |
| `inline`   | Compact edit field       |
| `error`    | Validation error         |
| `success`  | Validated field, rare    |
| `readonly` | Non-editable value       |
| `admin`    | Dense admin filter/input |

## 7.2. Default Input

Use for:

```txt
auth
profile setup
verification
settings
mentor request
community suggestion
```

## 7.3. Filled Input

Use for:

```txt
soft onboarding forms
comment composer
message composer
search-like secondary fields
```

Do not use filled input in dense admin forms unless defined.

## 7.4. Search Input

Anatomy:

```txt
leading search icon
placeholder
clear button if has value
optional filter trigger
```

Examples:

```txt
Tìm UEers, cộng đồng, mentor...
Tìm trong cộng đồng này...
Tìm tài nguyên...
```

## 7.5. Error Input

Rules:

```txt
- Error border.
- Error message under field.
- aria-invalid true.
- Do not rely only on red border.
```

## 7.6. Input Size Variants

| Size | Height | Usage                 |
| ---- | -----: | --------------------- |
| `sm` |   36px | Admin filters         |
| `md` |   44px | Default               |
| `lg` |   52px | Auth/mobile prominent |

---

# 8. Textarea Variants

## 8.1. Variants

| Variant    | Usage                       |
| ---------- | --------------------------- |
| `default`  | Standard long text          |
| `filled`   | Soft composer               |
| `composer` | Post/comment/message style  |
| `admin`    | Dense admin note            |
| `error`    | Validation                  |
| `readonly` | Read-only submitted content |

## 8.2. Composer Variant

Use for:

```txt
post composer
comment composer
greeting message
message input
community post
```

Composer should feel lighter than formal forms.

Visual:

```txt
soft background
rounded-xl or rounded-2xl
comfortable padding
```

## 8.3. Formal Textarea

Use for:

```txt
mentor request question
verification note
community suggestion purpose
report description
```

Formal textarea must show helper text and character count where needed.

---

# 9. Select / Combobox Variants

## 9.1. Variants

| Variant      | Usage                |
| ------------ | -------------------- |
| `default`    | Simple select        |
| `searchable` | Long option list     |
| `filter`     | Search/filter bars   |
| `inline`     | Compact table/filter |
| `admin`      | Dense admin forms    |
| `error`      | Validation           |

## 9.2. Use Searchable Combobox For

```txt
khoa
ngành học
career pathway
mentor topic
community
academic program
```

## 9.3. Use Native Select For

```txt
role
urgency
visibility
join policy
status simple filter
```

---

# 10. Checkbox Variants

## 10.1. Variants

| Variant     | Usage                  |
| ----------- | ---------------------- |
| `default`   | Normal checkbox        |
| `card`      | Selectable card option |
| `list-item` | Multi-select list      |
| `agreement` | Required attestation   |
| `filter`    | Filter option          |

## 10.2. Agreement Variant

Use for:

```txt
Tôi xác nhận tài nguyên này không vi phạm bản quyền.
Tôi đồng ý với quy định cộng đồng.
Tôi xác nhận thông tin xác thực là đúng.
```

Rules:

```txt
- Must not be pre-checked.
- Copy must be explicit.
- Required agreement must show error if missing.
```

## 10.3. Card Checkbox

Use for onboarding interest selection.

Example:

```txt
[ ] Công nghệ giáo dục
[ ] Dạy học Tin học
[ ] Tâm lý học đường
```

---

# 11. Radio Variants

## 11.1. Variants

| Variant         | Usage                                    |
| --------------- | ---------------------------------------- |
| `default`       | Standard radio group                     |
| `card`          | Large visible choice                     |
| `segmented`     | Compact option switch                    |
| `admin`         | Dense admin                              |
| `danger-choice` | Moderation/admin reason choices, careful |

## 11.2. Card Radio

Use for important user choices:

```txt
Tôi là sinh viên
Tôi là cựu sinh viên
Tôi là cố vấn học tập
```

## 11.3. Segmented Radio

Use for compact filters:

```txt
Tất cả / Chưa đọc
Tất cả / Đã tham gia / Đang chờ
```

---

# 12. Switch Variants

## 12.1. Variants

| Variant           | Usage                    |
| ----------------- | ------------------------ |
| `default`         | Normal setting           |
| `brand`           | Main enabled state       |
| `danger`          | Safety restriction, rare |
| `admin`           | Admin setting            |
| `disabled-locked` | Setting not editable     |

## 12.2. Use Switch For

```txt
Hiển thị trong Discovery
Nhận lời chào
Tạm dừng nhận mentor request
Bật thông báo trình duyệt
```

## 12.3. Do Not Use Switch For

```txt
Xóa tài khoản
Chặn người dùng
Tạm khóa cộng đồng
Reject verification
```

Những thứ có hậu quả nghiêm trọng không nên là cái gạt nhỏ nhìn vô hại như bật đèn bàn.

---

# 13. Card Variants

## 13.1. Variant Table

| Variant       | Usage                    |  Elevation |
| ------------- | ------------------------ | ---------: |
| `default`     | Basic content            |        Low |
| `interactive` | Clickable card           | Low/Medium |
| `elevated`    | Important container      |     Medium |
| `outlined`    | Formal/dense surface     |   None/Low |
| `soft`        | Friendly tinted surface  |       None |
| `brand`       | Brand highlight          |     Medium |
| `success`     | Approved/completed state |        Low |
| `warning`     | Pending/needs action     |        Low |
| `danger`      | Restricted/error state   |        Low |
| `admin`       | Dense operational card   |        Low |
| `glass`       | Marketing/hero only      |     Medium |

## 13.2. Default Card

Use for:

```txt
settings section
profile section
basic content block
```

## 13.3. Interactive Card

Use for:

```txt
mentor card
community card
career pathway card
search result card
notification item
```

Rules:

```txt
- Must show hover/focus.
- Cursor pointer only if whole card is clickable.
- Do not nest too many actions.
```

## 13.4. Elevated Card

Use for:

```txt
auth form
verification panel
main dashboard widget
important onboarding panel
```

## 13.5. Soft Card

Use for:

```txt
empty state hint
onboarding interest card
profile completion suggestion
career pathway guidance
```

## 13.6. Brand Card

Use sparingly for:

```txt
welcome screen
PWA install prompt
primary success guidance
```

Do not use brand card for every module banner. Branding is seasoning, not soup.

## 13.7. Warning Card

Use for:

```txt
verification needs more info
pending review
community inactive
mentor unavailable
```

## 13.8. Danger Card

Use for:

```txt
account restricted
community suspended
content removed
danger zone settings
```

## 13.9. Admin Card

Use for:

```txt
metric widgets
admin operation panels
audit summary
moderation queue item
```

---

# 14. Badge Variants

## 14.1. Variant Table

| Variant    | Usage                        |
| ---------- | ---------------------------- |
| `neutral`  | Generic metadata             |
| `brand`    | UEConnect/HCMUE identity     |
| `verified` | Verified status              |
| `role`     | Student/alumni/advisor/admin |
| `mentor`   | Mentor badge                 |
| `success`  | Approved/active              |
| `warning`  | Pending/needs more info      |
| `danger`   | Rejected/suspended/banned    |
| `info`     | Informational                |
| `muted`    | Low emphasis                 |
| `outline`  | Lightweight metadata         |

## 14.2. Status Badge Mapping

| State                    | Badge Variant | Copy           |
| ------------------------ | ------------- | -------------- |
| `active`                 | success       | Đang hoạt động |
| `verified`               | verified      | Đã xác thực    |
| `pending_review`         | warning       | Đang chờ duyệt |
| `under_review`           | warning       | Đang xem xét   |
| `needs_more_information` | warning       | Cần bổ sung    |
| `approved`               | success       | Đã duyệt       |
| `rejected`               | danger        | Bị từ chối     |
| `suspended`              | danger        | Tạm khóa       |
| `banned`                 | danger        | Bị cấm         |
| `archived`               | muted         | Đã lưu trữ     |
| `hidden_by_moderation`   | danger        | Đã ẩn          |
| `draft`                  | neutral       | Bản nháp       |

## 14.3. Role Badge Mapping

| Role           | Badge Variant | Copy          |
| -------------- | ------------- | ------------- |
| `student`      | role/brand    | Sinh viên     |
| `alumni`       | role/info     | Cựu sinh viên |
| `advisor`      | role/info     | Cố vấn        |
| `mentor`       | mentor        | Mentor        |
| `admin`        | danger/admin  | Admin         |
| `club_manager` | info          | Quản lý CLB   |

## 14.4. Badge Size

| Size | Usage                     |
| ---- | ------------------------- |
| `xs` | Dense table               |
| `sm` | Card metadata             |
| `md` | Profile/status            |
| `lg` | Hero/profile header, rare |

---

# 15. Avatar Variants

## 15.1. Variants

| Variant                 | Usage                      |
| ----------------------- | -------------------------- |
| `user`                  | User avatar                |
| `community`             | Community/club avatar      |
| `system`                | System/admin announcement  |
| `mentor`                | Mentor emphasis            |
| `anonymous-placeholder` | Avoid in MVP unless needed |
| `initials`              | Fallback                   |
| `image`                 | Uploaded avatar            |

## 15.2. Shape Variants

| Shape            | Usage           |
| ---------------- | --------------- |
| `circle`         | User            |
| `rounded-square` | Community/logo  |
| `squircle`       | App/system icon |
| `soft-square`    | Admin/resource  |

## 15.3. User Avatar

Use circle.

Required for:

```txt
verified active users after onboarding
```

## 15.4. Community Avatar

Use rounded-square.

Reason:

```txt
Differentiate community identity from person identity.
```

## 15.5. Avatar Badge Overlay

Allowed overlays:

```txt
verified
mentor
online future
restricted admin only
```

Do not overload avatar with 3 badges. It is a face/avatar, not a Christmas tree.

---

# 16. Tabs Variants

## 16.1. Variants

| Variant     | Usage                   |
| ----------- | ----------------------- |
| `line`      | Default page tabs       |
| `pill`      | Mobile/soft tabs        |
| `segmented` | Compact switch          |
| `vertical`  | Admin/settings          |
| `underline` | Minimal content section |

## 16.2. Line Tabs

Use for:

```txt
Community detail
Profile detail
Admin sections
```

## 16.3. Pill Tabs

Use for:

```txt
mobile filter categories
notification filter
search category
```

## 16.4. Segmented Tabs

Use for:

```txt
Tất cả / Chưa đọc
Tất cả / Đang chờ / Đã xử lý
```

## 16.5. Vertical Tabs

Use for:

```txt
settings
admin configuration
profile edit sections
```

---

# 17. Modal Variants

## 17.1. Variants

| Variant             | Usage                          |
| ------------------- | ------------------------------ |
| `standard`          | General dialog                 |
| `form`              | Short form                     |
| `confirmation`      | Confirm action                 |
| `danger`            | Destructive confirmation       |
| `preview`           | File/evidence/resource preview |
| `report`            | Report flow                    |
| `moderation`        | Admin/moderator action         |
| `fullscreen-mobile` | Complex mobile modal           |

## 17.2. Standard Modal

Use for simple interaction.

## 17.3. Form Modal

Use for:

```txt
Reject reason
Ask more information
Edit small setting
```

Not for:

```txt
full profile setup
long verification form
```

## 17.4. Danger Modal

Use for:

```txt
delete
block
suspend
ban
remove by moderation
```

Must include:

```txt
title
specific consequence
reason field if admin/moderation
cancel action
danger confirm action
```

## 17.5. Preview Modal

Use for:

```txt
verification evidence preview
resource preview
image preview
```

Rules:

```txt
- Must respect permission.
- Must not expose raw storage path.
```

## 17.6. Report Modal

Use for:

```txt
profile report
post report
comment report
message report
community report
mentor request report
```

Must include:

```txt
reason radio/list
optional description
submit button
privacy/safety note
```

---

# 18. Sheet / Drawer Variants

## 18.1. Variants

| Variant      | Usage                 |
| ------------ | --------------------- |
| `bottom`     | Mobile sheet          |
| `side-right` | Desktop detail/action |
| `side-left`  | Navigation/filter     |
| `fullscreen` | Complex mobile flow   |
| `command`    | Search/action future  |

## 18.2. Bottom Sheet

Use for:

```txt
filters
mobile action menu
share/report menu
notification quick view
```

## 18.3. Side-right Drawer

Use for:

```txt
admin detail preview
moderation case detail
profile quick view
community member detail
```

## 18.4. Fullscreen Sheet

Use for:

```txt
mobile complex form
multi-filter search
profile editing section
```

---

# 19. Dropdown Variants

## 19.1. Variants

| Variant          | Usage                             |
| ---------------- | --------------------------------- |
| `action-menu`    | More actions                      |
| `select-menu`    | Custom select                     |
| `profile-menu`   | User account menu                 |
| `admin-row-menu` | Admin table row                   |
| `danger-menu`    | Destructive grouped actions, rare |

## 19.2. Action Menu Standard Items

Order:

```txt
Xem chi tiết
Chỉnh sửa
Sao chép liên kết
Báo cáo
Ẩn
Xóa / Chặn
```

Danger actions at bottom.

## 19.3. Admin Row Menu

Examples:

```txt
Xem hồ sơ
Xem lịch sử
Cấp quyền
Tạm khóa
Gỡ quyền
```

---

# 20. Toast Variants

## 20.1. Variants

| Variant   | Usage                |
| --------- | -------------------- |
| `success` | Successful action    |
| `error`   | Failed action        |
| `warning` | Needs attention      |
| `info`    | Neutral info         |
| `loading` | Temporary processing |

## 20.2. Toast Copy Examples

Success:

```txt
Đã gửi lời chào.
Đã lưu thay đổi.
Yêu cầu mentor đã được gửi.
```

Error:

```txt
Không thể gửi tin nhắn. Vui lòng thử lại.
Không tải được cộng đồng.
```

Warning:

```txt
Bạn cần hoàn tất hồ sơ trước khi dùng Discovery.
```

Info:

```txt
Tài nguyên đang chờ duyệt.
```

## 20.3. Toast Rules

```txt
- Short and clear.
- No raw error stack.
- No sensitive data.
- Important blocking problems should use Alert instead.
```

---

# 21. Alert Variants

## 21.1. Variants

| Variant      | Usage                    |
| ------------ | ------------------------ |
| `info`       | Neutral information      |
| `brand`      | UEConnect guidance       |
| `success`    | Completed state          |
| `warning`    | Needs action             |
| `danger`     | Error/restriction        |
| `system`     | System announcement      |
| `moderation` | Safety/moderation notice |

## 21.2. Brand Alert

Use for:

```txt
welcome guidance
profile completion suggestion
mentor introduction
```

## 21.3. Warning Alert

Use for:

```txt
verification needs more information
mentor availability paused
community pending review
```

## 21.4. Danger Alert

Use for:

```txt
account restricted
community suspended
content removed
permission denied
```

## 21.5. System Alert

Use for:

```txt
maintenance notice
admin/system announcement
```

## 21.6. Alert Anatomy Variants

| Layout       | Usage                  |
| ------------ | ---------------------- |
| `compact`    | Short inline warning   |
| `standard`   | Most alerts            |
| `actionable` | Has CTA                |
| `banner`     | Page-wide announcement |

---

# 22. Skeleton Variants

## 22.1. Variants

| Variant             | Usage             |
| ------------------- | ----------------- |
| `text`              | Text lines        |
| `avatar`            | Avatar            |
| `card`              | Generic card      |
| `profile-card`      | Profile/discovery |
| `post-card`         | Feed              |
| `mentor-card`       | Mentor            |
| `community-card`    | Community         |
| `message-list`      | Messaging         |
| `table-row`         | Admin table       |
| `notification-item` | Notifications     |

## 22.2. Rules

```txt
- Skeleton must match final layout.
- Use consistent radius.
- Do not animate too aggressively.
- Respect reduced motion settings.
```

---

# 23. EmptyState Variants

## 23.1. Variants

| Variant          | Usage                          |
| ---------------- | ------------------------------ |
| `default`        | Generic no content             |
| `search`         | No search results              |
| `feed`           | No posts                       |
| `notification`   | No notifications               |
| `message`        | No messages                    |
| `community`      | No communities/resources       |
| `mentor`         | No mentors/requests            |
| `permission`     | Locked/no access               |
| `admin`          | Empty admin table              |
| `error-recovery` | Empty due to recoverable issue |

## 23.2. Empty Search

Copy:

```txt
Không tìm thấy kết quả phù hợp.
Hãy thử từ khóa khác hoặc bỏ bớt bộ lọc.
```

## 23.3. Empty Feed

Copy:

```txt
Chưa có bài viết nào.
Khi UEers bắt đầu chia sẻ, bài viết sẽ xuất hiện tại đây.
```

## 23.4. Empty Mentor

Copy:

```txt
Chưa có mentor phù hợp.
Bạn có thể thử chủ đề khác hoặc quay lại sau.
```

## 23.5. Empty Community Resource

Copy:

```txt
Chưa có tài nguyên nào.
Khi tài nguyên được duyệt, chúng sẽ xuất hiện tại đây.
```

---

# 24. ErrorState Variants

## 24.1. Variants

| Variant      | Usage                      |
| ------------ | -------------------------- |
| `network`    | Network issue              |
| `server`     | Server error               |
| `permission` | No permission              |
| `not-found`  | Missing/deleted            |
| `restricted` | Account/content restricted |
| `offline`    | PWA offline                |
| `validation` | Form-level validation      |
| `admin`      | Admin operation error      |

## 24.2. Network Error

Copy:

```txt
Không tải được dữ liệu.
Kết nối có thể đang không ổn định. Vui lòng thử lại.
```

Action:

```txt
Thử lại
```

## 24.3. Permission Error

Copy:

```txt
Bạn không có quyền xem nội dung này.
```

Action optional:

```txt
Quay lại
```

## 24.4. Not Found

Copy:

```txt
Nội dung này không còn khả dụng.
```

## 24.5. Restricted

Copy:

```txt
Tài khoản hoặc nội dung này đang bị hạn chế.
```

---

# 25. Table Variants

## 25.1. Variants

| Variant           | Usage            |
| ----------------- | ---------------- |
| `default`         | General data     |
| `admin`           | Admin operation  |
| `compact`         | Dense data       |
| `moderation`      | Moderation queue |
| `audit`           | Audit log        |
| `selectable`      | Bulk action      |
| `responsive-card` | Mobile fallback  |

## 25.2. Admin Table

Use for:

```txt
users
verification requests
reports
communities
mentor access
audit logs
```

Features:

```txt
sort
filter
pagination
row action
status badge
```

## 25.3. Moderation Table

Must show:

```txt
priority
target type
reason category
status
created time
assigned moderator
action
```

## 25.4. Audit Table

Must show:

```txt
actor
action
target
before/after summary
time
ip/device if allowed
```

---

# 26. Pagination Variants

## 26.1. Variants

| Variant       | Usage                       |
| ------------- | --------------------------- |
| `page-number` | Admin tables                |
| `cursor`      | Feed/messages/notifications |
| `load-more`   | Search/community resources  |
| `infinite`    | Feed/discovery, careful     |
| `compact`     | Mobile                      |

## 26.2. Rules

```txt
- Admin uses page-number.
- Feed/messages use cursor.
- Infinite scroll must not hide important footer actions.
- Load more is safer for resources/search.
```

---

# 27. Chip Variants

## 27.1. Variants

| Variant         | Usage                    |
| --------------- | ------------------------ |
| `tag`           | Topic/category           |
| `filter`        | Filter option            |
| `filter-active` | Active filter            |
| `removable`     | Active removable filter  |
| `selected`      | Selected item            |
| `role`          | Role chip                |
| `status`        | Mini status              |
| `brand`         | HCMUE/UEConnect emphasis |
| `neutral`       | Normal metadata          |

## 27.2. Examples

```txt
Frontend
Giáo dục học
Khoa CNTT
Mentor
Đang nhận yêu cầu
Đã tham gia
```

## 27.3. Removable Chip

Must include accessible label:

```txt
Xóa bộ lọc Khoa CNTT
```

---

# 28. Stepper Variants

## 28.1. Variants

| Variant         | Usage              |
| --------------- | ------------------ |
| `horizontal`    | Desktop short flow |
| `vertical`      | Longer forms       |
| `compact`       | Mobile             |
| `progress-only` | Minimal progress   |
| `admin-review`  | Review workflow    |

## 28.2. Use Cases

```txt
verification
onboarding
profile setup
mentor access request
community suggestion
admin review
```

## 28.3. Step States

```txt
not_started
current
completed
error
disabled
skipped
```

---

# 29. Progress Variants

## 29.1. Variants

| Variant         | Usage              |
| --------------- | ------------------ |
| `bar`           | Profile completion |
| `circle`        | Small dashboard    |
| `steps`         | Onboarding         |
| `upload`        | File upload        |
| `indeterminate` | Unknown processing |

## 29.2. Rules

```txt
- Do not fake numeric progress.
- Upload progress should reflect actual upload if possible.
- Must expose accessible value.
```

---

# 30. Navigation Variants

## 30.1. Nav Item Variants

| Variant    | Usage                            |
| ---------- | -------------------------------- |
| `default`  | Normal nav                       |
| `active`   | Current route                    |
| `disabled` | Not available                    |
| `locked`   | Requires verification/permission |
| `danger`   | Admin danger zone                |
| `badge`    | Has count                        |

## 30.2. App Navigation

Use:

```txt
Home
Discovery
Messages
Communities
Mentor
Notifications
Profile
```

## 30.3. Admin Navigation

Use dense/admin variant:

```txt
Dashboard
Users
Verification
Reports
Moderation
Communities
Mentors
Audit
Settings
```

---

# 31. Domain-specific Component Variants

## 31.1. Verification Components

### VerificationStatusBadge

| Status                   | Variant |
| ------------------------ | ------- |
| `not_submitted`          | neutral |
| `pending_review`         | warning |
| `under_review`           | warning |
| `needs_more_information` | warning |
| `approved`               | success |
| `rejected`               | danger  |
| `conflict`               | danger  |
| `suspicious`             | danger  |
| `expired`                | muted   |

### EvidenceCard

Variants:

```txt
pending
approved
rejected
preview-only
admin-review
```

Rules:

```txt
- User sees own evidence status.
- Admin preview uses protected route.
- Do not expose raw storage path.
```

## 31.2. Profile / Discovery Components

### ProfileCard

Variants:

```txt
default
discovery
public
compact
blocked
restricted
skeleton
```

### DiscoveryCard

Variants:

```txt
standard
high-relevance
limited-info
unavailable
```

Do not use dating-like copy or gestures visually.

“Swipe” được phép là interaction, không được biến UI thành hẹn hò trá hình.

## 31.3. Greeting Components

### GreetingCard

Variants:

```txt
pending
accepted
declined
cancelled
expired
reported
blocked
```

### GreetingButton

Variants:

```txt
send
pending-disabled
accepted-disabled
declined-disabled
blocked-disabled
```

## 31.4. Messaging Components

### MessageBubble

Variants:

```txt
sent
received
sending
failed
edited
deleted
hidden_by_moderation
system
```

### ConversationItem

Variants:

```txt
default
unread
active
restricted
blocked
archived
```

### MessageComposer

Variants:

```txt
default
disabled
blocked
restricted
offline
uploading
```

## 31.5. Mentor Components

### MentorCard

Variants:

```txt
available
limited
full
paused
unavailable
suspended
```

### MentorRequestCard

Variants:

```txt
pending
needs_more_information
updated_by_student
accepted
declined
cancelled
expired
completed
reported
restricted
```

### MentorAvailabilityBadge

| Status        | Variant |
| ------------- | ------- |
| `available`   | success |
| `limited`     | warning |
| `full`        | warning |
| `paused`      | muted   |
| `suspended`   | danger  |
| `unavailable` | muted   |

## 31.6. Community Components

### CommunityCard

Variants:

```txt
public
restricted
private
joined
pending
inactive
suspended
archived
hidden
```

### CommunityStatusBadge

| Status                 | Variant |
| ---------------------- | ------- |
| `active`               | success |
| `inactive`             | warning |
| `pending_review`       | warning |
| `suspended`            | danger  |
| `archived`             | muted   |
| `hidden_by_moderation` | danger  |

### CommunityJoinButton

Variants:

```txt
join
request_join
pending
joined
leave
closed
locked
```

### CommunityResourceCard

Variants:

```txt
published
pending_review
rejected
hidden_by_moderation
archived
unavailable
```

## 31.7. Career Pathway Components

### PathwayCard

Variants:

```txt
default
recommended
saved
official_source
admin_curated
mentor_insight
needs_review
archived
```

### SourceBadge

| Source                 | Variant       |
| ---------------------- | ------------- |
| `official_hcmue`       | brand/success |
| `admin_curated`        | info          |
| `mentor_insight`       | neutral       |
| `community_suggestion` | warning       |
| `needs_review`         | warning       |

## 31.8. Notification Components

### NotificationItem

Variants:

```txt
unread
read
important
system
moderation
expired
```

### NotificationBadge

Variants:

```txt
count
dot
urgent
muted
```

## 31.9. Search Components

### SearchResultCard

Variants:

```txt
person
post
community
mentor
pathway
resource
locked
unavailable
```

### FilterChip

Variants:

```txt
inactive
active
removable
disabled
```

## 31.10. Admin Components

### AdminMetricCard

Variants:

```txt
default
success
warning
danger
info
trend-up
trend-down
```

### ModerationCaseCard

Variants:

```txt
queued
in_review
action_required
actioned
dismissed
escalated
appealed
resolved
```

### AuditLogItem

Variants:

```txt
neutral
create
update
delete
permission
security
moderation
danger
```

---

# 32. Variant-to-State Mapping Rules

## 32.1. State Drives Variant

Business state must drive UI variant.

Example:

```txt
verification.status = approved
→ Badge variant = success
→ Copy = Đã duyệt
```

Not:

```txt
if designer likes green then success
```

## 32.2. Disabled Is Not a Status

Disabled is interaction state, not business status.

Example:

```txt
Mentor is paused = business status
Button disabled = interaction consequence
```

## 32.3. Hidden vs Locked vs Disabled

| UI State   | Meaning                                    |
| ---------- | ------------------------------------------ |
| `hidden`   | User should not see it                     |
| `locked`   | User can see existence but cannot access   |
| `disabled` | User can see action but cannot perform now |

Example:

```txt
private community non-member = locked
blocked profile = hidden
incomplete profile CTA = disabled with explanation
```

---

# 33. Variant Decision Matrix

## 33.1. When To Use Primary

Use primary if:

```txt
- It is the main next step.
- User expects to continue.
- Action is safe/positive.
- Only one main CTA exists in section.
```

Examples:

```txt
Tiếp tục
Gửi xác thực
Hoàn tất hồ sơ
Gửi lời chào
```

## 33.2. When To Use Warning

Use warning if:

```txt
- State needs attention.
- Process is pending.
- User needs to complete something.
```

Examples:

```txt
Đang chờ duyệt
Cần bổ sung
Mentor gần đầy yêu cầu
```

## 33.3. When To Use Danger

Use danger if:

```txt
- Something is blocked/rejected/suspended.
- Action is destructive.
- Safety/moderation problem exists.
```

Examples:

```txt
Tạm khóa
Bị từ chối
Xóa
Chặn
Gỡ nội dung
```

## 33.4. When To Use Muted

Use muted if:

```txt
- State is inactive.
- Content is archived.
- Info is secondary.
```

Examples:

```txt
Đã lưu trữ
Đã hết hạn
Không còn khả dụng
```

---

# 34. Implementation Direction

## 34.1. Variant Prop Naming

Use:

```txt
variant
size
tone
state
```

Recommended:

```php
<x-ui.button variant="primary" size="lg">
    Gửi lời chào
</x-ui.button>
```

Badge:

```php
<x-ui.badge variant="warning">
    Đang chờ duyệt
</x-ui.badge>
```

Card:

```php
<x-ui.card variant="interactive">
    ...
</x-ui.card>
```

## 34.2. Avoid Boolean Explosion

Bad:

```php
<x-button primary danger big rounded soft blue />
```

Good:

```php
<x-ui.button variant="danger" size="lg" />
```

Boolean props multiplying like rabbits is how components become cursed objects.

## 34.3. Class Mapping

Variant classes should be centralized.

Recommended:

```txt
resources/views/components/ui/button.blade.php
app/View/Components/Ui/Button.php
config/ui-variants.php
```

Or simple Blade mapping if project scope is smaller.

## 34.4. Token Use

All variants must map to:

```txt
color tokens
spacing tokens
radius tokens
shadow tokens
border tokens
motion tokens
```

No raw random class unless token-approved.

---

# 35. QA Checklist

Before approving a variant:

```txt
[ ] Variant has clear purpose.
[ ] Variant is used in more than one reasonable context or is business-critical.
[ ] Variant name is semantic, not page-specific.
[ ] Variant uses design tokens.
[ ] Variant has hover/focus/active/disabled states if interactive.
[ ] Variant works on mobile.
[ ] Variant passes contrast.
[ ] Variant has Vietnamese text examples.
[ ] Variant does not conflict with business state mapping.
[ ] Variant does not duplicate another existing variant.
[ ] Variant is documented in this file.
```

---

# 36. Anti-patterns

Do not create:

```txt
button-home-special
card-profile-v2
mentor-card-blue-but-lighter
badge-pending-yellowish
modal-danger-but-friendly
input-rounded-extra-because-nice
community-card-owner-view-final
```

Do not:

```txt
- Use primary for every CTA.
- Use danger for normal cancel.
- Use warning just to attract attention.
- Use brand gradient inside dense admin UI.
- Use glass variant in app core surfaces.
- Use disabled style instead of explaining permission.
- Use one-off variants directly in page.
- Make every card elevated.
- Make every badge colorful.
```

Một design system tốt thường hơi nhàm chán ở tầng primitive. Nhàm chán ở đây là tính năng, không phải lỗi.

---

# 37. Final Rule

Variant là hợp đồng giữa design và code.

Trước khi thêm variant mới:

```txt
1. Check if existing variant covers the need.
2. Define semantic purpose.
3. Define allowed contexts.
4. Define forbidden contexts.
5. Define token mapping.
6. Define states.
7. Add usage examples.
8. Add QA checks.
```

Nếu không làm đủ, variant đó chưa được phép vào design system.

Không phải mọi cảm hứng thị giác đều xứng đáng thành API component. Frontend đã đủ đau rồi.

```
```
