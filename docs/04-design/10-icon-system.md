---
title: "Icon System"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Frontend Team"
depends_on:
  - "00-design-foundation-roadmap.md"
  - "01-brand-attributes.md"
  - "02-brand-identity-hcmue.md"
  - "03-color-system.md"
  - "05-typography-system.md"
  - "06-spacing-system.md"
  - "07-radius-system.md"
next:
  - "11-logo-usage-system.md"
  - "12-component-primitives.md"
  - "13-component-variants.md"
  - "14-interaction-states.md"
  - "17-accessibility-rules.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "page-specs/mentor.md"
  - "page-specs/safety-reporting.md"
  - "21-social-interaction-patterns.md"
---

# 10. Icon System

## 1. Purpose

File này định nghĩa hệ thống icon chính thức cho UEConnect.

Mục tiêu:

- Chốt icon style dùng xuyên suốt product.
- Giữ UI sạch, social, hiện đại và enterprise-ready.
- Tránh icon nhiều màu, nhiều style, nhiều stroke làm UI rối.
- Đảm bảo icon có ý nghĩa rõ, dễ hiểu, dễ dùng trên desktop và mobile.
- Chuẩn hóa icon size, stroke, color, state, accessibility.
- Hỗ trợ implement bằng Laravel Blade, TailwindCSS, Vite và SVG component.

Icon trong UEConnect không phải đồ trang trí. Icon là ngôn ngữ điều hướng và tương tác. Nếu icon sai, user sẽ không biết bấm vào đâu. Và rồi ai đó sẽ viết tooltip dài như tiểu thuyết để cứu một cái icon tệ, một bi kịch frontend rất phổ biến.

---

## 2. Core Decision

UEConnect dùng icon strategy:

```txt
Line icon
+ neutral by default
+ brand only for active/verified/primary states
+ semantic color only when meaningful
+ consistent stroke width
````

Quyết định chính:

```txt
Primary icon style: outline / line icon
Recommended library: Lucide Icons
Alternative: Phosphor Icons, Heroicons Outline, Tabler Icons
Default stroke: 2px
Default size: 20px hoặc 24px tùy context
Default color: neutral text-muted / text-secondary
```

Không dùng:

```txt
Multi-color icons
Random filled icons
3D icons
Emoji làm icon chính
Gradient icons
Icons từ nhiều library trộn lẫn
Icon quá cute
Icon quá corporate
```

---

## 3. Recommended Icon Library

## 3.1. Primary Recommendation: Lucide Icons

Khuyến nghị chính:

```txt
Lucide Icons
```

Lý do:

* Line icon sạch, hiện đại.
* Stroke nhất quán.
* Phù hợp social platform.
* Dễ dùng với Blade thông qua SVG component.
* Dễ chỉnh size/color bằng Tailwind.
* Có đủ icon cho feed, profile, chat, notification, admin, safety.
* Ít bị “template SaaS quá đậm mùi”.

## 3.2. Alternatives

Có thể dùng:

```txt
Phosphor Icons
Heroicons Outline
Tabler Icons
```

Điều kiện:

* Chỉ chọn một library chính.
* Không trộn icon style nếu không bắt buộc.
* Nếu thiếu icon, custom icon phải match stroke/style của library chính.

## 3.3. Do Not Use as Main System

Không dùng làm icon system chính:

```txt
Font Awesome mixed styles
Bootstrap Icons nếu đang dùng không đồng bộ
Material filled icons
Emoji icon
Fluent colored icons
Random SVG từ internet
```

Một app mà mỗi icon đến từ một thư viện khác nhau sẽ nhìn như một nhóm đồ án mà mỗi thành viên tự làm một màn hình và không ai mở Figma chung. Tránh.

---

## 4. Icon Style Principles

## 4.1. Outline-first

Icon mặc định dùng outline/line style.

```txt
Stroke: 2px
Fill: none
Linecap: round
Linejoin: round
```

Ví dụ SVG style:

```html
<svg
  width="20"
  height="20"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
>
  ...
</svg>
```

## 4.2. Filled Icon Usage

Filled icon chỉ dùng khi:

* Cần thể hiện active state rất rõ.
* Icon đó có filled version đồng bộ.
* State thay đổi quan trọng như `liked`, `saved`, `selected`.

Ví dụ:

| Action       | Default          | Active                                         |
| ------------ | ---------------- | ---------------------------------------------- |
| Like         | Heart outline    | Heart filled hoặc brand stroke                 |
| Save         | Bookmark outline | Bookmark filled                                |
| Notification | Bell outline     | Bell with dot                                  |
| Nav item     | Outline          | Brand color + soft bg, không nhất thiết filled |

Không dùng filled icon cho tất cả nav item nếu style tổng thể là outline.

## 4.3. No Multi-color Icon

Icon mặc định chỉ có một màu.

Được dùng:

```txt
Neutral
Brand blue
Danger red
Success green
Warning amber
Mentor purple
```

Không dùng icon nhiều màu trong product UI hằng ngày.

---

## 5. Icon Size System

```css
:root {
  --icon-2xs: 12px;
  --icon-xs: 14px;
  --icon-sm: 16px;
  --icon-md: 20px;
  --icon-lg: 24px;
  --icon-xl: 28px;
  --icon-2xl: 32px;
}
```

## 5.1. Size Usage Table

| Token      | Size | Usage                                |
| ---------- | ---: | ------------------------------------ |
| `icon-2xs` | 12px | Inline tiny status, very small badge |
| `icon-xs`  | 14px | Badge icon, helper icon              |
| `icon-sm`  | 16px | Metadata, small action, form helper  |
| `icon-md`  | 20px | Default UI icon, nav icon desktop    |
| `icon-lg`  | 24px | Bottom nav, primary action, mobile   |
| `icon-xl`  | 28px | Empty state, large CTA visual        |
| `icon-2xl` | 32px | Rare illustration-like icon          |

## 5.2. Default Sizes by Context

| Context                 |    Size |
| ----------------------- | ------: |
| Desktop left nav icon   |    20px |
| Mobile bottom nav icon  |    24px |
| Post action icon        |    20px |
| Icon button default     |    20px |
| Header action icon      | 20–24px |
| Verified badge icon     | 12–14px |
| Empty state icon        | 28–32px |
| Admin table action icon | 16–20px |
| Form helper icon        |    16px |
| Toast icon              |    20px |
| Modal icon              |    24px |

## 5.3. Touch Target Rule

Icon visual size không bằng touch target.

```txt
Icon visual: 20–24px
Touch target: 40–44px minimum
```

Mobile:

```txt
Minimum touch target: 44px
Recommended icon button: 44px × 44px
```

Không làm icon 20px rồi bắt user chạm chính xác vào 20px đó. Ngón tay người không phải con trỏ laser, thật đáng tiếc.

---

## 6. Stroke Width

Default:

```txt
stroke-width: 2px
```

Rules:

* Icon nhỏ 12–14px có thể dùng stroke `2px`, nhưng kiểm tra visual.
* Không dùng stroke quá mảnh dưới `1.5px`.
* Không dùng stroke quá dày trên `2.5px`.
* Không trộn stroke width trong cùng screen.
* Custom icon phải match stroke của Lucide.

## 6.1. Stroke Usage

| Size    |                           Stroke |
| ------- | -------------------------------: |
| 12–14px |                              2px |
| 16–24px |                              2px |
| 28–32px | 2px hoặc 1.75px nếu nhìn quá dày |

---

## 7. Icon Color System

Icon phải dùng color token từ `03-color-system.md`.

## 7.1. Default Colors

```css
:root {
  --icon-default: var(--ue-text-secondary);
  --icon-muted: var(--ue-text-muted);
  --icon-disabled: var(--ue-text-disabled);
  --icon-brand: var(--ue-brand);
  --icon-danger: var(--danger);
  --icon-success: var(--success-text);
  --icon-warning: var(--warning-text);
  --icon-mentor: var(--mentor-text);
}
```

## 7.2. Color Usage Table

| Color               | Usage                                |
| ------------------- | ------------------------------------ |
| `ue-text-secondary` | Default nav/action icon              |
| `ue-text-muted`     | Metadata/supporting icon             |
| `ue-text-disabled`  | Disabled icon                        |
| `ue-brand`          | Active nav, primary action, verified |
| `danger`            | Delete, report, block                |
| `success-text`      | Approved, success                    |
| `warning-text`      | Pending, warning                     |
| `mentor-text`       | Mentor badge/action                  |

## 7.3. Rules

* Icon mặc định không dùng brand blue.
* Brand blue chỉ dùng cho active/selected/primary/verified.
* Danger icon chỉ dùng cho destructive/safety.
* Không dùng nhiều icon color trong cùng một row.
* Không dùng color để thay text label ở action quan trọng.

---

## 8. Navigation Icons

## 8.1. Primary Navigation Set

Recommended nav icons:

| Navigation | Icon Concept               | Lucide Suggestion                     |
| ---------- | -------------------------- | ------------------------------------- |
| Trang chủ  | Home                       | `home`                                |
| Khám phá   | Compass / Search           | `compass`, `search`                   |
| Tin nhắn   | Message                    | `message-circle`                      |
| Thông báo  | Bell                       | `bell`                                |
| Hồ sơ      | User                       | `user`                                |
| Mentor     | Graduation / Spark / Users | `graduation-cap`, `users`, `sparkles` |
| Cộng đồng  | Users / Landmark           | `users`, `circle-users`               |
| Cài đặt    | Settings                   | `settings`                            |

## 8.2. Desktop Nav Rules

* Icon size: 20px.
* Label visible.
* Inactive: neutral.
* Active: brand blue + soft blue background.
* Hover: neutral surface hover.

Example:

```html
<a class="nav-item" aria-current="page">
  <x-icon.home class="h-5 w-5" />
  <span>Trang chủ</span>
</a>
```

## 8.3. Mobile Bottom Nav Rules

* Icon size: 24px.
* Label size: 11px.
* Active: brand blue.
* Inactive: muted.
* Touch target: 44–64px height.
* Không dùng quá 5 item chính.

Recommended mobile bottom nav:

```txt
Trang chủ
Khám phá
Tạo bài / Composer
Tin nhắn
Hồ sơ
```

Nếu thêm quá nhiều tab, bottom nav sẽ thành bãi đỗ icon. User không đến đây để giải câu đố biểu tượng.

---

## 9. Social Action Icons

## 9.1. Post Actions

| Action     | Icon                  | Default             | Active                   |
| ---------- | --------------------- | ------------------- | ------------------------ |
| Like       | Heart                 | neutral             | brand hoặc filled        |
| Comment    | Message circle        | neutral             | neutral/brand if focused |
| Save       | Bookmark              | neutral             | brand hoặc filled        |
| Share/Send | Send                  | neutral             | neutral                  |
| More       | More horizontal       | neutral             | neutral                  |
| Report     | Flag / Triangle alert | danger only in menu |                          |

## 9.2. Rules

* Post action icon size: 20px.
* Action touch target: 40–44px.
* Không dùng màu riêng cho từng action.
* Like không bắt buộc đỏ.
* Save active có thể dùng brand blue.
* Report nằm trong menu, không show danger icon quá nổi trong feed nếu không cần.

## 9.3. Comment Icons

| Action       | Icon                  |
| ------------ | --------------------- |
| Reply        | Corner down / Message |
| Like comment | Heart                 |
| More         | More horizontal       |
| Report       | Flag                  |

Comment actions nên nhỏ hơn post actions một chút, nhưng vẫn bấm được.

---

## 10. Discovery Icons

Discovery phải tránh dating vibe.

## 10.1. Approved Discovery Actions

| Action       | Icon Concept          | Label        |
| ------------ | --------------------- | ------------ |
| Gửi lời chào | Send / Hand / Message | Gửi lời chào |
| Bỏ qua       | X                     | Bỏ qua       |
| Lưu hồ sơ    | Bookmark              | Lưu          |
| Xem hồ sơ    | User / External       | Xem hồ sơ    |
| Bộ lọc       | Sliders               | Bộ lọc       |
| Báo cáo      | Flag                  | Báo cáo      |

## 10.2. Forbidden Discovery Icons

Không dùng:

```txt
Heart as primary discovery action
Flame
Kiss/love symbols
Hot badge
Tinder-like lightning
Swipe gesture icon as main language
```

Có thể dùng heart cho like trong post, nhưng không dùng heart làm hành động chính trong discovery. Nếu không, sản phẩm lại trượt về dating app nhanh hơn deadline đồ án.

---

## 11. Messaging Icons

| Action              | Icon              |
| ------------------- | ----------------- |
| Send message        | Send              |
| Attach file/image   | Paperclip / Image |
| Emoji               | Smile, optional   |
| More conversation   | More horizontal   |
| Search conversation | Search            |
| Mute                | Bell off          |
| Block               | Ban               |
| Report              | Flag              |
| Back                | Chevron left      |
| New message         | Message plus      |

Rules:

* Send button có thể brand blue.
* Attach/emoji neutral.
* Report/block danger trong menu.
* Message bubble không cần icon nếu không cần.
* Failed message có icon alert nhỏ + retry.

---

## 12. Profile Icons

| Field / Action   | Icon                      |
| ---------------- | ------------------------- |
| Faculty / major  | Graduation cap / BookOpen |
| Cohort/class     | Users / Calendar          |
| Location/context | MapPin, nếu cần           |
| Interest         | Sparkles / Tag            |
| Club             | Users                     |
| Mentor interest  | Compass / GraduationCap   |
| Edit profile     | Pencil                    |
| Share profile    | Share                     |
| Settings         | Settings                  |
| Verified UEer    | BadgeCheck / CheckCircle  |

Rules:

* Profile metadata icon nên nhỏ 16px.
* Không dùng quá nhiều icon trong profile card.
* Nếu text đã rõ, icon có thể bỏ.
* Verified icon phải nhất quán toàn app.

---

## 13. Mentor Icons

| Use              | Icon                        |
| ---------------- | --------------------------- |
| Mentor module    | GraduationCap / Compass     |
| Expertise        | Badge / Sparkles / BookOpen |
| Availability     | Clock                       |
| Request          | Send                        |
| Accepted         | CheckCircle                 |
| Pending          | Clock                       |
| Declined         | XCircle                     |
| Resource         | FileText / Link             |
| Career direction | Briefcase, dùng hạn chế     |

Rules:

* Mentor không nên quá career/corporate.
* Không dùng briefcase quá nhiều nếu muốn tránh LinkedIn vibe.
* Mentor icon có thể dùng mentor purple trong badge nhỏ, nhưng default vẫn neutral.

---

## 14. Community / Club Icons

| Use          | Icon                   |
| ------------ | ---------------------- |
| Community    | Users                  |
| Club         | CircleUsers / Landmark |
| Channel      | Hash                   |
| Event        | Calendar               |
| Announcement | Megaphone              |
| Pinned post  | Pin                    |
| Members      | Users                  |
| Role         | Shield                 |
| Rules        | ClipboardList          |
| Resource     | Folder / FileText      |

Rules:

* Community icon có thể dùng neutral hoặc success green nhẹ trong badge.
* Không biến mỗi community thành một icon màu khác nhau nếu không có identity riêng.
* Official club có thể có avatar/logo riêng, nhưng UI icon vẫn theo system.

---

## 15. Admin & Moderation Icons

| Use              | Icon                 |
| ---------------- | -------------------- |
| Dashboard        | LayoutDashboard      |
| User management  | Users                |
| Verification     | BadgeCheck           |
| Moderation queue | ShieldAlert          |
| Report detail    | Flag                 |
| Suspend          | Ban                  |
| Delete           | Trash2               |
| Approve          | CheckCircle          |
| Reject           | XCircle              |
| Audit log        | ScrollText / History |
| Role management  | Shield               |
| Policy           | FileText             |
| Announcement     | Megaphone            |

Rules:

* Admin icons nên nghiêm túc, không playful.
* Danger action phải rõ nhưng không quá màu mè.
* Table action icon size 16–20px.
* Destructive icon luôn có label hoặc tooltip.

---

## 16. Safety Icons

| Action / State | Icon                 |
| -------------- | -------------------- |
| Report         | Flag                 |
| Block          | Ban                  |
| Warning        | TriangleAlert        |
| Privacy        | Lock                 |
| Public         | Globe                |
| Hidden         | EyeOff               |
| Visible        | Eye                  |
| Suspended      | CircleSlash          |
| Appeal         | MessageSquareWarning |
| Guideline      | ClipboardList        |

Rules:

* Safety icon phải rõ nghĩa.
* Không dùng icon quá cute cho safety.
* Danger color chỉ khi action thực sự nguy hiểm.
* Report/block cần text label, không chỉ icon.

---

## 17. Status Icons

| State          | Icon          | Color          |
| -------------- | ------------- | -------------- |
| Approved       | CheckCircle   | success        |
| Pending        | Clock         | warning        |
| Rejected       | XCircle       | danger         |
| Need more info | CircleHelp    | warning/info   |
| Verified       | BadgeCheck    | brand          |
| Locked         | Lock          | neutral/danger |
| Offline        | WifiOff       | muted          |
| Error          | TriangleAlert | danger         |
| Saved          | BookmarkCheck | brand/success  |
| Sent           | Check         | muted/success  |
| Failed         | CircleAlert   | danger         |

Rules:

* Status icon phải đi kèm text.
* Không dùng chỉ màu để truyền state.
* Pending không dùng danger.
* Rejected phải có reason text nếu liên quan user action.

---

## 18. Icon Button System

## 18.1. Sizes

| Size | Button |    Icon |
| ---- | -----: | ------: |
| SM   |   32px |    16px |
| MD   |   40px |    20px |
| LG   |   44px | 20–24px |
| XL   |   48px |    24px |

## 18.2. Default Style

```css
.icon-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-full);
  color: var(--ue-text-secondary);
}

.icon-button:hover {
  background: var(--ue-surface-hover);
  color: var(--ue-text);
}

.icon-button:focus-visible {
  outline: none;
  box-shadow: var(--shadow-focus);
}
```

## 18.3. Variants

| Variant | Usage                               |
| ------- | ----------------------------------- |
| Neutral | Default icon action                 |
| Brand   | Primary icon action or active state |
| Danger  | Report, delete, block               |
| Ghost   | Toolbar action                      |
| Soft    | Active nav/action background        |

---

## 19. Accessibility Rules

## 19.1. Icon-only Buttons

Icon-only button bắt buộc có accessible label.

Đúng:

```blade
<button type="button" aria-label="Mở thông báo">
  <x-icon.bell class="h-5 w-5" />
</button>
```

Sai:

```blade
<button>
  <x-icon.bell class="h-5 w-5" />
</button>
```

Không ai muốn screen reader đọc “button” rồi im lặng như đang giữ bí mật quốc gia.

## 19.2. Decorative Icons

Icon chỉ để trang trí cần ẩn với screen reader.

```blade
<x-icon.sparkles class="h-4 w-4" aria-hidden="true" />
```

hoặc:

```html
<svg aria-hidden="true" focusable="false">...</svg>
```

## 19.3. Icon with Text

Nếu icon đi cùng text và không thêm ý nghĩa riêng, đặt `aria-hidden`.

```blade
<a href="/feed">
  <x-icon.home class="h-5 w-5" aria-hidden="true" />
  <span>Trang chủ</span>
</a>
```

## 19.4. Status Icons

Status icon phải có text.

Đúng:

```blade
<span class="inline-flex items-center gap-1">
  <x-icon.clock class="h-4 w-4 text-warning-text" aria-hidden="true" />
  <span>Đang chờ duyệt</span>
</span>
```

Sai:

```blade
<x-icon.clock class="text-warning" />
```

---

## 20. Icon Naming System

Icon component naming nên nhất quán.

## 20.1. Blade Component Convention

```txt
<x-icon.home />
<x-icon.search />
<x-icon.message-circle />
<x-icon.bell />
<x-icon.user />
<x-icon.badge-check />
<x-icon.flag />
```

Hoặc nếu dùng namespace riêng:

```txt
<x-ui.icon name="home" />
<x-ui.icon name="message-circle" />
```

## 20.2. Recommended Structure

```txt
resources/views/components/icon/
├── home.blade.php
├── compass.blade.php
├── message-circle.blade.php
├── bell.blade.php
├── user.blade.php
├── badge-check.blade.php
├── heart.blade.php
├── bookmark.blade.php
├── send.blade.php
├── flag.blade.php
├── settings.blade.php
└── ...
```

Nếu dùng package như Blade Icons:

```txt
blade-ui-kit/blade-icons
mallardduck/blade-lucide-icons
```

hoặc tự tạo SVG component. Đừng copy SVG lung tung vào từng view như rải vụn bánh mì cho bug đi theo.

---

## 21. Implementation Rules

## 21.1. SVG Must Use currentColor

Icon SVG nên dùng:

```html
stroke="currentColor"
```

Không hard-code:

```html
stroke="#124874"
```

trừ khi icon là official logo asset.

Lý do:

* Dễ đổi màu bằng class.
* Dễ active/hover/focus.
* Dễ dark mode sau này.
* Dễ tái sử dụng.

## 21.2. Icon Should Not Define Random Size

Không hard-code size trong SVG nếu component có class.

Nên:

```blade
<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} ...>
```

Không nên:

```html
<svg width="37" height="37">
```

Con số 37 ở UI thường là dấu vết của một buổi kéo chuột thiếu niềm tin.

## 21.3. Filled Active Icons

Nếu cần filled icon, đặt tên rõ:

```txt
heart.blade.php
heart-filled.blade.php
bookmark.blade.php
bookmark-filled.blade.php
```

Không sửa icon outline thành filled bằng CSS hack nếu SVG không hỗ trợ.

---

## 22. TailwindCSS Mapping

## 22.1. Icon Size Utilities

Tailwind có sẵn:

```txt
h-3 w-3    = 12px
h-3.5 w-3.5 = 14px
h-4 w-4    = 16px
h-5 w-5    = 20px
h-6 w-6    = 24px
h-7 w-7    = 28px
h-8 w-8    = 32px
```

## 22.2. Common Patterns

```txt
Badge icon: h-3.5 w-3.5
Metadata icon: h-4 w-4
Post action icon: h-5 w-5
Desktop nav icon: h-5 w-5
Mobile nav icon: h-6 w-6
Header action icon: h-5 w-5 or h-6 w-6
Empty state icon: h-8 w-8
```

## 22.3. Icon Button Classes

```blade
<button
  type="button"
  aria-label="Mở thông báo"
  class="inline-flex h-10 w-10 items-center justify-center rounded-full text-ue-text-secondary transition hover:bg-ue-surface-hover hover:text-ue-text focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[rgba(18,72,116,0.16)]"
>
  <x-icon.bell class="h-5 w-5" aria-hidden="true" />
</button>
```

---

## 23. Blade Examples

## 23.1. Active Nav Item

```blade
<a
  href="{{ route('feed') }}"
  aria-current="page"
  class="inline-flex h-12 items-center gap-3 rounded-full bg-ue-brand-soft px-3 text-sm font-semibold text-ue-brand"
>
  <x-icon.home class="h-5 w-5" aria-hidden="true" />
  <span>Trang chủ</span>
</a>
```

## 23.2. Post Action

```blade
<button
  type="button"
  aria-label="Thích bài viết"
  class="inline-flex h-10 min-w-10 items-center justify-center gap-1 rounded-full px-2 text-ue-text-muted hover:bg-ue-surface-hover hover:text-ue-text"
>
  <x-icon.heart class="h-5 w-5" aria-hidden="true" />
  <span class="text-sm">24</span>
</button>
```

## 23.3. Verified Badge

```blade
<span class="inline-flex items-center gap-1 rounded-full border border-[rgba(18,72,116,0.14)] bg-[rgba(18,72,116,0.08)] px-2 py-0.5 text-xs font-semibold text-ue-brand">
  <x-icon.badge-check class="h-3.5 w-3.5" aria-hidden="true" />
  Đã xác thực UEer
</span>
```

## 23.4. Report Menu Item

```blade
<button
  type="button"
  class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-danger-text hover:bg-danger-bg"
>
  <x-icon.flag class="h-4 w-4" aria-hidden="true" />
  <span>Báo cáo</span>
</button>
```

## 23.5. Status Label

```blade
<span class="inline-flex items-center gap-1.5 rounded-full border border-warning-border bg-warning-bg px-2 py-0.5 text-xs font-semibold text-warning-text">
  <x-icon.clock class="h-3.5 w-3.5" aria-hidden="true" />
  Đang chờ duyệt
</span>
```

---

## 24. Icon Inventory

## 24.1. Core App Icons

```txt
home
compass
search
message-circle
bell
user
settings
plus
x
check
chevron-left
chevron-right
chevron-down
more-horizontal
more-vertical
```

## 24.2. Social Icons

```txt
heart
heart-filled
message-circle
bookmark
bookmark-filled
send
share
repeat
flag
eye
eye-off
```

## 24.3. Identity Icons

```txt
badge-check
check-circle
graduation-cap
book-open
users
calendar
tag
sparkles
shield
lock
globe
```

## 24.4. Messaging Icons

```txt
send
paperclip
image
smile
bell-off
ban
circle-alert
wifi-off
check-check
```

## 24.5. Mentor / Career Icons

```txt
graduation-cap
compass
book-open
briefcase
clock
file-text
link
circle-help
```

## 24.6. Community Icons

```txt
users
circle-users
hash
megaphone
pin
calendar
folder
clipboard-list
shield
```

## 24.7. Admin Icons

```txt
layout-dashboard
user-cog
shield-alert
flag
trash-2
ban
history
scroll-text
file-text
megaphone
sliders-horizontal
```

---

## 25. Icon Anti-patterns

## 25.1. Mixed Libraries

Sai:

```txt
Lucide home
Material chat
FontAwesome user
Emoji bell
Custom random heart
```

Đúng:

```txt
Lucide for all product icons
Custom SVG only if style matches Lucide
```

## 25.2. Brand Blue Everywhere

Sai:

```css
.icon {
  color: #124874;
}
```

Đúng:

```css
.icon {
  color: var(--ue-text-secondary);
}

.nav-item[aria-current="page"] .icon {
  color: var(--ue-brand);
}
```

## 25.3. Icon Without Label Where Needed

Sai:

```blade
<button>
  <x-icon.flag />
</button>
```

Đúng:

```blade
<button aria-label="Báo cáo bài viết">
  <x-icon.flag aria-hidden="true" />
</button>
```

## 25.4. Dating Discovery Icons

Không dùng:

```txt
heart as main discovery CTA
flame
kiss
hot
match
swipe
```

## 25.5. Oversized Icons

Sai:

```txt
48px icon in every empty state
32px nav icon
```

Đúng:

```txt
24px mobile nav
20px desktop nav
28–32px empty state icon
```

---

## 26. Icon QA Checklist

## 26.1. Style

* [ ] Icon có cùng library/style không?
* [ ] Stroke width có nhất quán không?
* [ ] Icon có dùng currentColor không?
* [ ] Có tránh filled icon bừa bãi không?
* [ ] Có tránh multi-color icon không?

## 26.2. Color

* [ ] Icon mặc định dùng neutral không?
* [ ] Brand blue chỉ dùng active/verified/primary không?
* [ ] Danger icon chỉ dùng đúng destructive/safety không?
* [ ] Có quá nhiều icon màu trong một row không?

## 26.3. Size

* [ ] Desktop nav icon khoảng 20px không?
* [ ] Mobile nav icon khoảng 24px không?
* [ ] Post action icon khoảng 20px không?
* [ ] Touch target có đủ 40–44px không?
* [ ] Badge icon có quá lớn không?

## 26.4. Accessibility

* [ ] Icon-only button có aria-label không?
* [ ] Decorative icon có aria-hidden không?
* [ ] Status icon có text đi kèm không?
* [ ] Report/block/delete có label rõ không?
* [ ] Focus state có rõ không?

## 26.5. Product Fit

* [ ] Discovery có tránh dating icon không?
* [ ] Mentor có tránh LinkedIn/corporate quá mức không?
* [ ] Admin icon có nghiêm túc không?
* [ ] Feed icon có nhẹ và content-first không?
* [ ] UI có tránh cảm giác ráp icon từ nhiều template không?

---

## 27. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

```txt
Follow UEConnect Icon System.
Use Lucide-style outline icons with 2px stroke, round caps, and currentColor.
Use neutral icon color by default.
Use HCMUE blue #124874 only for active navigation, primary action, verified badge, and selected states.
Do not use multi-color icons, gradient icons, emoji icons, or mixed icon libraries.
Use 20px icons for desktop actions, 24px for mobile bottom navigation, 12–14px for badges.
Icon-only buttons must have aria-label and 40–44px touch targets.
Avoid dating-style icons in Discovery such as flame, hot, match, swipe, or heart as primary action.
```

---

## 28. Final Decision

Icon system chính thức của UEConnect:

```txt
Primary library: Lucide Icons
Style: outline / line
Stroke: 2px
Default size: 20px desktop, 24px mobile nav
Default color: neutral
Active color: #124874
Touch target: 40–44px minimum
Icon-only: aria-label required
Filled icons: only for clear active states
Gradient/multi-color icons: not allowed
```

Câu chốt:

```txt
Icon của UEConnect phải hướng dẫn user nhẹ nhàng và nhất quán, không biến giao diện thành bảo tàng biểu tượng mượn từ năm thư viện khác nhau.
```
