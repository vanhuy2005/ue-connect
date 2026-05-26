---
product: UEConnect
brand_context: HCMUE verified student social platform
version: 3.0
status: enterprise-ready replacement
last_updated: 2026-05-25
primary_brand_color: "#124874"
design_strategy: "Neutral-first social UI with restrained HCMUE brand accents"
replaces:
  - UE-connect-DESIGN(3).md
  - UEConnect-DESIGN-HCMUE-blue-gradient.md
  - UEConnect-DESIGN-HCMUE-colors.md
  - design.md
  - preview legacy gradient direction
---

# UEConnect-DESIGN.md

## 0. Mục đích tài liệu

Tài liệu này là bản **Design System chuẩn enterprise** cho UEConnect, thay thế toàn bộ các bản thiết kế cũ. Phiên bản này sửa lỗi quan trọng nhất của các bản trước: **lạm dụng gradient và primary color khiến UI nhìn “AI generated”, chói, thiếu trưởng thành và không giống sản phẩm social thật**.

UEConnect không còn đi theo hướng “gradient-first”. Hướng thiết kế chính thức là:

```txt
Neutral-first social platform
+ restrained HCMUE identity
+ content-first layout
+ icon-driven navigation
+ enterprise-grade accessibility
+ minimal, calm, scalable component system
```

Tài liệu dùng cho:

- Thiết kế Figma
- Thiết kế UI bằng AI prompt
- Implement React / Next.js / mobile app
- Xây dựng design tokens
- Review UI/UX
- Làm chuẩn bàn giao cho nhóm frontend
- Kiểm tra consistency khi mở rộng sản phẩm

---

# 1. Final Design Direction

## 1.1. Product positioning

UEConnect là một nền tảng social dành cho sinh viên HCMUE, tập trung vào:

- Kết nối bạn bè cùng trường
- Khám phá UEers
- Giao tiếp qua post, thread, comment, message
- Tìm mentor / alumni / cộng đồng học tập
- Xác thực danh tính trong môi trường HCMUE

UEConnect **không phải**:

- App hẹn hò clone Tinder
- Cổng thông tin hành chính của trường
- Career portal khô khan
- Landing page AI tràn gradient
- Dashboard enterprise lạnh lẽo

## 1.2. Visual thesis

```txt
UEConnect should feel like Threads / Instagram-level social UI,
but with subtle HCMUE identity and verified student trust.
```

Nói ngắn gọn:

```txt
Default UI phải yên, sạch, trung tính.
Brand color chỉ xuất hiện khi cần định hướng, xác thực hoặc nhấn hành động chính.
```

Thiết kế tốt không phải là chỗ nào cũng có màu thương hiệu. Đó là cách biến UI thành cái áo đồng phục khổng lồ, và dĩ nhiên chẳng ai muốn mặc nó 24/7.

---

# 2. Enterprise UX Principles

## 2.1. Content-first

Nội dung người dùng là trung tâm:

- Post
- Avatar
- Tên người dùng
- Faculty / cohort / verified state
- Caption
- Comment
- Media
- Message
- Notification

Màu thương hiệu chỉ hỗ trợ nhận diện, không tranh spotlight với content.

## 2.2. Neutral-first

80–90% UI nên dùng neutral:

```txt
White
Near-white
Black
Gray
Light border
Subtle surface
```

Brand blue `#124874` chỉ nên chiếm khoảng **5–10% visual weight** trên mỗi screen thông thường.

## 2.3. Strategic brand moments

Chỉ dùng brand color mạnh ở:

- Logo
- Active navigation
- Primary CTA
- Verified UEer badge
- Focus ring
- Link quan trọng
- Onboarding brand moment
- Empty state illustration nhỏ
- Official HCMUE identity area

Không dùng brand blue mạnh cho:

- Toàn bộ background feed
- Mọi button
- Mọi icon
- Mọi badge
- Mọi heading
- Mọi card header
- Mọi border

## 2.4. Enterprise consistency

Tất cả component phải có:

- Default state
- Hover state
- Focus state
- Active state
- Disabled state
- Loading state nếu có async
- Error state nếu có validation
- Responsive behavior
- Accessibility note
- Token mapping

## 2.5. Avoid AI-generated UI smell

Các dấu hiệu phải tránh:

```txt
Gradient phủ toàn màn hình.
Primary color xuất hiện ở mọi card.
Shadow quá mạnh.
Icon nhiều màu lung tung.
Border radius quá lớn cho mọi thứ.
Text quá to, quá đậm.
Component nào cũng "premium".
Không có hierarchy rõ.
Nền quá rực khiến content chìm.
CTA nào cũng giống CTA chính.
```

Quy tắc sửa:

```txt
More neutral.
Less decoration.
More hierarchy.
Less spectacle.
More content.
Less "dribbble cosplay".
```

---

# 3. Brand DNA

## 3.1. Core feeling

```txt
Friendly
Trusted
HCMUE-rooted
Minimal
Human
Content-first
Calm
Student-first
Verified
```

## 3.2. Not this

```txt
Flashy
Dating clone
Gradient-heavy
Administrative
Corporate
Childish
Over-polished AI mockup
```

## 3.3. Product personality

| Attribute | UI Translation |
|---|---|
| Friendly | Rounded avatars, simple copy, generous spacing |
| Trusted | Verified badges, HCMUE blue in exact places |
| Minimal | Neutral surfaces, restrained accents |
| Human | Real profile layout, posts, comments, messages |
| Student-first | Faculty/cohort context, clubs, mentors |
| Enterprise-ready | Clear states, tokens, accessibility, scalable layout |

---

# 4. HCMUE Brand Constraints

## 4.1. Official anchor color

```css
--hcmue-cerulean: #124874;
```

Thông số:

```txt
HEX:  #124874
RGB:  18, 72, 116
CMYK: C84 M38 Y0 K55
```

Rules:

- Không thay `#124874` bằng xanh generic.
- Không dùng blue quá sáng làm màu nhận diện chính.
- Không dùng red trong primary brand gradient.
- Không dùng gradient làm default UI background.
- Logo/official brand area có thể dùng màu HCMUE rõ hơn.

## 4.2. HCMUE heritage red

```css
--hcmue-red: #CF373D;
```

Usage:

- Logo gốc
- Heritage detail
- Alumni accent nhỏ
- Error/danger nếu phù hợp
- Tuyệt đối không dùng làm primary CTA gradient

## 4.3. Typography heritage

Brand HCMUE có thể dùng serif trong một số context chính thức, nhưng UEConnect product UI không dùng serif làm font chính.

```txt
Product UI: system-ui / Be Vietnam Pro / Inter
Brand moment: Source Serif Variable
Official document: Times New Roman
```

---

# 5. Color System

## 5.1. Color strategy

Phiên bản cũ dùng quá nhiều gradient. Phiên bản enterprise chuyển sang:

```txt
Neutral-first + restrained brand accent
```

Tỉ lệ màu khuyến nghị trên một screen social thông thường:

```txt
Neutral surfaces: 80–90%
Text: 8–12%
Brand blue: 5–10%
Semantic colors: <3%
Gradient: 0–5%, only for onboarding or rare brand moments
```

## 5.2. Core colors

```css
:root {
  --ue-brand: #124874;
  --ue-brand-hover: #0E3A60;
  --ue-brand-active: #0A2B49;
  --ue-brand-soft: #EEF7FF;

  --ue-heritage-red: #CF373D;

  --ue-bg: #FAFAFA;
  --ue-surface: #FFFFFF;
  --ue-surface-subtle: #F8FAFC;
  --ue-surface-hover: #F1F5F9;

  --ue-text: #0F172A;
  --ue-text-secondary: #475569;
  --ue-text-muted: #64748B;
  --ue-text-disabled: #94A3B8;

  --ue-border: #E4E6EB;
  --ue-border-strong: #CBD5E1;
}
```

## 5.3. Neutral scale

```css
--neutral-0:   #FFFFFF;
--neutral-25:  #FCFCFD;
--neutral-50:  #FAFAFA;
--neutral-100: #F8FAFC;
--neutral-150: #F1F5F9;
--neutral-200: #E4E6EB;
--neutral-300: #CBD5E1;
--neutral-400: #94A3B8;
--neutral-500: #64748B;
--neutral-600: #475569;
--neutral-700: #334155;
--neutral-800: #1E293B;
--neutral-900: #0F172A;
--neutral-950: #020617;
```

Usage:

| Token | Use |
|---|---|
| `neutral-0` | Cards, modals, feed post surface |
| `neutral-50` | App background |
| `neutral-100` | Sidebar / subtle panels |
| `neutral-150` | Hover background |
| `neutral-200` | Border / dividers |
| `neutral-500` | Metadata |
| `neutral-600` | Secondary text |
| `neutral-900` | Primary text |

## 5.4. Brand blue scale

```css
--blue-50:  #EEF7FF;
--blue-100: #D9ECFF;
--blue-200: #B7DDFF;
--blue-300: #83C5FF;
--blue-400: #4DA3FF;
--blue-500: #2178D4;
--blue-600: #124874;
--blue-700: #0E3A60;
--blue-800: #0A2B49;
--blue-900: #071F35;
```

Usage:

| Token | Use |
|---|---|
| `blue-50` | Soft badge background, selected nav bg |
| `blue-100` | Hover tint |
| `blue-500` | Optional link in light UI |
| `blue-600` | Primary brand, active nav, CTA |
| `blue-700` | Hover CTA |
| `blue-800` | Active CTA |
| `blue-900` | Deep brand text if needed |

## 5.5. Semantic colors

```css
--success: #20C997;
--success-text: #0F7B5C;
--success-bg: rgba(32, 201, 151, 0.12);

--warning: #F59E0B;
--warning-text: #92400E;
--warning-bg: rgba(245, 158, 11, 0.12);

--danger: #C62828;
--danger-text: #991B1B;
--danger-bg: rgba(198, 40, 40, 0.10);

--info: #2178D4;
--info-text: #124874;
--info-bg: rgba(18, 72, 116, 0.10);

--mentor: #7C5CFF;
--mentor-text: #5B3FD6;
--mentor-bg: rgba(124, 92, 255, 0.10);
```

## 5.6. Color usage rules

### Do

- Dùng white/near-white cho feed.
- Dùng `#124874` cho active nav và primary CTA.
- Dùng light blue background cho selected state.
- Dùng red chỉ cho danger/error hoặc heritage mark nhỏ.
- Dùng semantic color có background nhạt + text đậm.

### Don't

- Không dùng gradient cho feed background.
- Không dùng brand blue cho mọi icon.
- Không dùng cyan làm text trên nền trắng.
- Không dùng red để tạo “năng lượng social” trong primary UI.
- Không dùng nhiều accent trong cùng một post/card.
- Không dùng màu để thay thế hierarchy.

---

# 6. Gradient Policy

## 6.1. Final decision

Gradient **không bị cấm**, nhưng phải trở thành **rare brand moment**.

```txt
Default UI: no gradient.
Onboarding / splash / marketing hero: optional gradient.
Feed / navigation / post / profile: no full gradient background.
Buttons: solid primary by default, gradient only for rare high-emphasis CTA.
```

## 6.2. Approved gradients

### Brand moment gradient

```css
--ue-brand-moment-gradient: linear-gradient(
  135deg,
  #0B3558 0%,
  #124874 38%,
  #2178D4 72%,
  #38BDF8 100%
);
```

Use only for:

- Splash screen
- Onboarding first screen
- Marketing hero
- App store preview
- Special launch campaign

### Subtle tint gradient

```css
--ue-subtle-tint-gradient: linear-gradient(
  135deg,
  rgba(18, 72, 116, 0.06) 0%,
  rgba(56, 189, 248, 0.08) 100%
);
```

Use for:

- Empty state illustration area
- Small onboarding card
- Verified info panel
- Never as a default card style

## 6.3. Anti-patterns

Không dùng:

```css
/* AI-generated overkill */
background: linear-gradient(...) on every section;

/* Tinder clone smell */
linear-gradient(135deg, #FD267A, #FF6036);

/* Bad UE mix */
linear-gradient(135deg, #124874, #CF373D);

/* Spotlight problem */
radial-gradient(circle at center, light-blue, dark-blue);

/* Chói trên content */
cyan gradient behind white text without contrast check;
```

---

# 7. Typography System

## 7.1. Enterprise font decision

Để UI bớt “AI mockup” và giống social product hơn, ưu tiên system font. `Be Vietnam Pro` vẫn được giữ nếu cần chất Việt Nam rõ hơn.

```css
--font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Be Vietnam Pro", sans-serif;
--font-vietnamese: "Be Vietnam Pro", system-ui, sans-serif;
--font-data: "Inter", system-ui, sans-serif;
--font-brand-serif: "Source Serif Variable", Georgia, serif;
```

Rules:

- Product UI chính: `system-ui` first.
- Vietnamese-heavy marketing: có thể dùng `Be Vietnam Pro`.
- Admin/data table: `Inter`.
- Brand heritage heading: `Source Serif Variable` rất hạn chế.
- Không dùng Times New Roman trong app UI.

## 7.2. Typography scale

Enterprise social UI không cần H1 88px trừ marketing. Product screen nên tiết chế.

```css
--text-2xs: 11px;
--text-xs:  12px;
--text-sm:  13px;
--text-md:  14px;
--text-base:15px;
--text-lg:  16px;
--text-xl:  18px;
--text-2xl: 20px;
--text-3xl: 24px;
--text-4xl: 32px;
```

## 7.3. Text styles

| Style | Size | Weight | Line height | Use |
|---|---:|---:|---:|---|
| App Title | 24px | 700 | 32px | Page header |
| Section Title | 20px | 700 | 28px | Sidebar/card group |
| Post Author | 15px | 600 | 21px | User name |
| Body | 15px | 400 | 21px | Post content |
| Body Small | 14px | 400 | 20px | Secondary copy |
| Metadata | 13px | 400 | 18px | Time, cohort, faculty |
| Caption | 12px | 400 | 16px | Helper text |
| Button | 15px | 600 | 21px | Button label |
| Tab/Nav | 15px | 600 | 21px | Navigation |

## 7.4. Typography UX rules

- Body post content nên `15px/21px`.
- Không dùng quá nhiều font weight.
- Heading không nên quá lớn trong product UI.
- Metadata phải dễ đọc, không dưới 12px.
- Dùng `font-weight: 600` thay vì 700 cho nhiều label nhỏ.
- Không dùng uppercase quá nhiều trong social UI.
- Không center-align body text.

---

# 8. Layout Architecture

## 8.1. Social platform layout

Desktop layout chuẩn:

```txt
Left navigation: 240px
Main feed: 560–640px
Right panel: 280–320px
Gutter: 24px
```

Tablet:

```txt
Collapsed left nav
Main feed + optional right panel
```

Mobile:

```txt
Top bar
Single feed
Bottom navigation
```

## 8.2. Container tokens

```css
--layout-left-nav: 240px;
--layout-feed-min: 400px;
--layout-feed: 600px;
--layout-right-panel: 300px;
--layout-gutter: 24px;
--layout-shell-max: 1200px;
```

## 8.3. Page shell

```css
.app-shell {
  min-height: 100dvh;
  background: var(--neutral-50);
}

.desktop-shell {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 240px minmax(0, 600px) 300px;
  gap: 24px;
}
```

## 8.4. Feed rules

- Feed column không nên quá rộng. Tối ưu đọc: `560–640px`.
- Post card có border/divider nhẹ, không cần shadow mạnh.
- Mỗi post nên có author row, content, media, actions, comment preview.
- Actions dùng icon line, không dùng button màu lớn.
- Nên dùng divider thay vì card shadow nặng.

---

# 9. Spacing System

## 9.1. Scale

```css
--space-0: 0;
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-5: 20px;
--space-6: 24px;
--space-8: 32px;
--space-10: 40px;
--space-12: 48px;
--space-16: 64px;
```

## 9.2. Practical usage

| Context | Spacing |
|---|---:|
| Icon + label gap | 8px |
| Avatar + text gap | 12px |
| Post inner padding | 16px |
| Feed item vertical padding | 16px |
| Sidebar item padding | 12px 16px |
| Card gap | 12–16px |
| Column gap desktop | 24px |
| Section gap | 24–32px |

## 9.3. Spacing anti-patterns

- Không dùng 40px padding trong mọi card.
- Không để post quá “loãng” như landing page.
- Không để sidebar item cao quá 56px nếu không cần.
- Không nhồi icon sát chữ dưới 6px.
- Không dùng spacing lẻ kiểu 17px, 23px nếu không có lý do.

---

# 10. Radius System

Phiên bản cũ bo góc hơi “cute app”. Enterprise social cần tiết chế.

```css
--radius-xs: 4px;
--radius-sm: 6px;
--radius-md: 8px;
--radius-lg: 12px;
--radius-xl: 16px;
--radius-2xl: 20px;
--radius-full: 999px;
```

Usage:

| Component | Radius |
|---|---:|
| Avatar | 999px |
| Badge | 999px |
| Icon button hover bg | 999px or 8px |
| Input | 8px |
| Button | 8px |
| Post card | 12px |
| Modal | 16px |
| Large feature card | 20px |
| Image media | 12px |

Rules:

- Không dùng 24px cho mọi card.
- Product UI chính dùng 8–12px nhiều hơn.
- 20px chỉ cho large cards / onboarding.
- Pill chỉ cho badge/avatar/very specific CTA.

---

# 11. Elevation & Border

## 11.1. Shadow tokens

```css
--shadow-none: none;
--shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
--shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08);
--shadow-md: 0 4px 12px rgba(15, 23, 42, 0.10);
--shadow-lg: 0 12px 32px rgba(15, 23, 42, 0.14);
```

## 11.2. Elevation rules

| Level | Use |
|---|---|
| None | Feed rows, nav, sidebar |
| XS | Subtle card if separated from background |
| SM | Dropdown, small floating panel |
| MD | Popover, profile preview |
| LG | Modal only |

## 11.3. Border tokens

```css
--border-subtle: #F1F5F9;
--border-default: #E4E6EB;
--border-strong: #CBD5E1;
```

## 11.4. Enterprise rule

```txt
Use borders before shadows.
Use shadows only when something floats.
```

Social feed nên giống product thật, không phải từng post là một cái thẻ bay trong không gian.

---

# 12. Icon System

## 12.1. Icon style

```txt
Rounded line icons
2px stroke
No filled colorful icons by default
24px nav icon
20px action icon
```

Recommended:

```txt
Lucide Icons
Phosphor Icons
Heroicons outline
```

## 12.2. Main navigation icons

```txt
Home
Search
Compass / Discover
PlusSquare
MessageCircle
Bell
UserCircle
Users
GraduationCap
```

## 12.3. Post action icons

```txt
Heart
MessageCircle
Repeat2 / Share2
Send
Bookmark
MoreHorizontal
Flag
```

## 12.4. Icon color rules

Default:

```css
color: #0F172A;
```

Muted:

```css
color: #64748B;
```

Active:

```css
color: #124874;
```

Danger:

```css
color: #C62828;
```

Rules:

- Không tô icon mỗi cái một màu.
- Không dùng icon filled trừ active state đặc biệt.
- Icon-only button phải có `aria-label`.
- Nav active dùng blue, không dùng gradient.
- Post action mặc định neutral, hover mới tint nhẹ.

---

# 13. Navigation System

## 13.1. Desktop left nav

```css
.left-nav {
  position: sticky;
  top: 0;
  height: 100dvh;
  padding: 20px 12px;
  background: #FFFFFF;
  border-right: 1px solid #E4E6EB;
}

.nav-item {
  height: 48px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 12px;
  border-radius: 999px;
  color: #0F172A;
  font-size: 15px;
  font-weight: 500;
}

.nav-item:hover {
  background: #F1F5F9;
}

.nav-item[aria-current="page"] {
  color: #124874;
  font-weight: 700;
  background: #EEF7FF;
}
```

## 13.2. Mobile bottom nav

```css
.bottom-nav {
  height: 64px;
  background: #FFFFFF;
  border-top: 1px solid #E4E6EB;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
}

.bottom-nav-item {
  min-width: 44px;
  min-height: 44px;
  display: grid;
  place-items: center;
  color: #64748B;
}

.bottom-nav-item[aria-current="page"] {
  color: #124874;
}
```

## 13.3. Navigation content

Primary nav:

```txt
Trang chủ
Khám phá
Tạo
Tin nhắn
Thông báo
Hồ sơ
```

Optional:

```txt
Mentor
Câu lạc bộ
Sự kiện
Cài đặt
```

## 13.4. Navigation UX mistakes to avoid

- Không dùng quá 7 mục chính.
- Không dùng text quá dài trong nav.
- Không dùng gradient trong active nav.
- Không dùng red làm active nav.
- Không ẩn label desktop nếu chưa cần.
- Không để nav item dưới 44px height.

---

# 14. Button System

## 14.1. Button philosophy

Trong social app enterprise, primary button thường **solid**, không gradient. Gradient chỉ dùng cho onboarding brand moment.

## 14.2. Button sizes

| Size | Height | Padding | Font |
|---|---:|---:|---:|
| XS | 32px | 8px 12px | 13px |
| SM | 36px | 8px 14px | 14px |
| MD | 40px | 10px 16px | 15px |
| LG | 44px | 12px 20px | 15px |
| XL | 48px | 12px 24px | 16px |

## 14.3. Primary button

```css
.button-primary {
  height: 40px;
  padding: 0 16px;
  border-radius: 8px;
  border: 1px solid #124874;
  background: #124874;
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 600;
  line-height: 21px;
}

.button-primary:hover {
  background: #0E3A60;
  border-color: #0E3A60;
}

.button-primary:active {
  background: #0A2B49;
  border-color: #0A2B49;
}

.button-primary:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(18, 72, 116, 0.18);
}
```

## 14.4. Secondary button

```css
.button-secondary {
  height: 40px;
  padding: 0 16px;
  border-radius: 8px;
  border: 1px solid #E4E6EB;
  background: #FFFFFF;
  color: #0F172A;
  font-size: 15px;
  font-weight: 600;
}

.button-secondary:hover {
  background: #F8FAFC;
  border-color: #CBD5E1;
}
```

## 14.5. Ghost button

```css
.button-ghost {
  height: 36px;
  padding: 0 12px;
  border-radius: 8px;
  border: 1px solid transparent;
  background: transparent;
  color: #0F172A;
  font-size: 15px;
  font-weight: 600;
}

.button-ghost:hover {
  background: #F1F5F9;
}
```

## 14.6. Icon button

```css
.icon-button {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  border: none;
  background: transparent;
  color: #0F172A;
  display: grid;
  place-items: center;
}

.icon-button:hover {
  background: #F1F5F9;
}
```

## 14.7. Button rules

- Một view chỉ có một primary action chính.
- Primary button không dùng gradient trong feed.
- Post action không dùng button filled.
- Danger button chỉ dùng khi destructive.
- Loading button phải giữ width để không layout shift.
- Button text phải rõ hành động.

---

# 15. Input & Form System

## 15.1. Text input

```css
.input {
  height: 44px;
  padding: 0 14px;
  border-radius: 8px;
  border: 1px solid #E4E6EB;
  background: #FFFFFF;
  color: #0F172A;
  font-size: 15px;
  line-height: 21px;
}

.input:hover {
  border-color: #CBD5E1;
}

.input:focus {
  outline: none;
  border-color: #124874;
  box-shadow: 0 0 0 3px rgba(18, 72, 116, 0.12);
}

.input::placeholder {
  color: #94A3B8;
}
```

## 15.2. Textarea

```css
.textarea {
  min-height: 96px;
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #E4E6EB;
  resize: vertical;
}
```

## 15.3. Form UX rules

- Label không được biến mất hoàn toàn.
- Placeholder không thay thế label.
- Error phải có text, không chỉ đổi màu border.
- Form dài phải chia step.
- Signup phải giảm friction, nhưng vẫn rõ trust/safety.
- Password input phải có show/hide.
- Search input nên có icon search + clear button.

---

# 16. Avatar & Identity

## 16.1. Avatar sizes

```css
--avatar-xs: 24px;
--avatar-sm: 32px;
--avatar-md: 40px;
--avatar-lg: 48px;
--avatar-xl: 64px;
--avatar-2xl: 96px;
```

## 16.2. Avatar rules

- Avatar luôn tròn.
- Có fallback initials.
- Có alt text hoặc aria-hidden tùy context.
- Online dot dùng mint, size nhỏ.
- Verified mark không che mặt.
- Không dùng ring màu quá dày.

## 16.3. Verified identity

Verified UEer badge nên là trust signal nhỏ:

```css
.verified-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #124874;
  background: rgba(18, 72, 116, 0.08);
  border: 1px solid rgba(18, 72, 116, 0.14);
  border-radius: 999px;
  padding: 2px 8px;
  font-size: 12px;
  font-weight: 600;
}
```

---

# 17. Feed & Post System

## 17.1. Post card / feed item

```css
.post {
  background: #FFFFFF;
  border-bottom: 1px solid #E4E6EB;
  padding: 16px;
}

.post:hover {
  background: #FCFCFD;
}
```

Alternative card layout:

```css
.post-card {
  background: #FFFFFF;
  border: 1px solid #E4E6EB;
  border-radius: 12px;
  padding: 16px;
  box-shadow: none;
}
```

## 17.2. Post anatomy

```txt
Author row
  Avatar
  Name + verified
  Faculty / cohort / time
  More menu

Content
  Text
  Media
  Links

Actions
  Like
  Comment
  Repost/share
  Send
  Save

Replies preview
```

## 17.3. Post typography

```css
.post-author {
  font-size: 15px;
  font-weight: 600;
  line-height: 21px;
  color: #0F172A;
}

.post-meta {
  font-size: 13px;
  font-weight: 400;
  line-height: 18px;
  color: #64748B;
}

.post-body {
  font-size: 15px;
  font-weight: 400;
  line-height: 21px;
  color: #0F172A;
}
```

## 17.4. Post media

```css
.post-media {
  margin-top: 12px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #E4E6EB;
  background: #F1F5F9;
}
```

Rules:

- Không crop media tùy tiện nếu user post ảnh.
- Có skeleton loading.
- Có alt text support nếu upload.
- Video/audio cần control rõ.
- Sensitive content cần blur + warning.

---

# 18. Profile System

## 18.1. Profile header

Profile không nên giống card hẹn hò. Cần giống social identity page:

```txt
Cover area
Avatar
Name + verified
Faculty / cohort
Bio
Stats
Primary action
Tabs
```

## 18.2. Profile actions

Visitor:

```txt
Gửi lời chào
Nhắn tin
Theo dõi
Báo cáo
```

Owner:

```txt
Chỉnh sửa hồ sơ
Xem như người khác
Cài đặt quyền riêng tư
```

## 18.3. Discovery profile card

Discovery card có thể cảm xúc hơn, nhưng vẫn không dating clone:

```css
.discovery-card {
  background: #FFFFFF;
  border: 1px solid #E4E6EB;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
```

Rules:

- CTA: “Gửi lời chào”, “Kết nối”, “Lưu hồ sơ”.
- Tránh “Swipe”, “Match”, “Crush”.
- Ảnh lớn nhưng info phải rõ và tôn trọng người dùng.
- Có report/block dễ tìm.

---

# 19. Messaging System

## 19.1. Inbox layout

Desktop:

```txt
Conversation list 320px
Message panel flexible
Profile/context panel optional
```

Mobile:

```txt
Conversation list → chat screen
```

## 19.2. Message bubble

```css
.message-bubble {
  max-width: 70%;
  padding: 8px 12px;
  border-radius: 18px;
  font-size: 15px;
  line-height: 21px;
}

.message-bubble--own {
  background: #124874;
  color: #FFFFFF;
}

.message-bubble--other {
  background: #F1F5F9;
  color: #0F172A;
}
```

Rules:

- Own message có thể dùng brand blue.
- Other message dùng neutral.
- Không dùng gradient cho bubble.
- Timestamp muted.
- Seen/read state nhỏ, không làm phiền.
- Có block/report.

---

# 20. Badge & Status System

## 20.1. Badge base

```css
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  height: 24px;
  padding: 0 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
}
```

## 20.2. Badge mapping

| Badge | Background | Text | Use |
|---|---|---|---|
| Verified UEer | `rgba(18,72,116,.08)` | `#124874` | Xác thực sinh viên |
| Mentor | `rgba(124,92,255,.10)` | `#5B3FD6` | Mentor |
| Alumni | `rgba(207,55,61,.08)` | `#A92B31` | Cựu sinh viên |
| Online | `rgba(32,201,151,.12)` | `#0F7B5C` | Online |
| Pending | `rgba(245,158,11,.12)` | `#92400E` | Chờ |
| Error | `rgba(198,40,40,.10)` | `#991B1B` | Lỗi |

Rules:

- Không dùng badge quá nhiều trong một card.
- Không dùng badge filled mạnh trừ status critical.
- Badge không được cạnh tranh với tên người dùng.
- Verified badge nhỏ, không biến thành huy chương Olympic.

---

# 21. Empty State System

## 21.1. Empty state anatomy

```txt
Small icon / illustration
Title
Description
One primary action
Optional secondary link
```

## 21.2. Empty state example

```txt
Chưa có kết nối nào
Bắt đầu khám phá UEers cùng khoa hoặc cùng sở thích với bạn.
[Khám phá ngay]
```

## 21.3. Empty state style

```css
.empty-state {
  padding: 40px 24px;
  text-align: center;
  color: #475569;
}

.empty-state__icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #124874;
  background: rgba(18,72,116,.08);
  border-radius: 999px;
}
```

Rules:

- Không dùng minh họa quá lớn.
- Không dùng gradient full panel.
- Copy phải giúp người dùng làm bước tiếp theo.
- Không đổ lỗi cho người dùng.

---

# 22. Modal, Drawer, Toast

## 22.1. Modal

```css
.modal {
  width: min(100% - 32px, 560px);
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: 0 12px 32px rgba(15,23,42,.14);
  border: 1px solid #E4E6EB;
}
```

Rules:

- Focus trap.
- Escape close nếu không phải destructive confirmation.
- Close button rõ.
- Title rõ.
- Primary action nằm cuối, bên phải desktop, full-width mobile nếu cần.

## 22.2. Drawer

Use for:

- Mobile menu
- Filter
- Comment detail
- Profile quick view

## 22.3. Toast

```css
.toast {
  background: #0F172A;
  color: #FFFFFF;
  border-radius: 12px;
  padding: 12px 16px;
  box-shadow: 0 8px 24px rgba(15,23,42,.18);
}
```

Rules:

- Auto-dismiss 4–6s.
- Có action nếu cần undo.
- Không spam toast.

---

# 23. Motion System

## 23.1. Tokens

```css
--duration-instant: 80ms;
--duration-fast: 120ms;
--duration-base: 180ms;
--duration-slow: 240ms;

--ease-standard: cubic-bezier(0.2, 0, 0, 1);
--ease-out: cubic-bezier(0, 0, 0.2, 1);
```

## 23.2. Motion rules

- Hover: background tint, no dramatic jump.
- Card hover: optional, max translateY(-1px) in enterprise feed.
- Reaction: small scale 1.05.
- Modal: fade + 4px slide.
- Bottom sheet: slide up.
- No parallax.
- No bouncy animation in core UI.
- Respect reduced motion.

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

---

# 24. Accessibility Requirements

## 24.1. Core

- WCAG AA contrast.
- Text body >= 14px.
- Touch target >= 44px.
- Focus visible.
- Keyboard navigation.
- Icon-only buttons must have `aria-label`.
- Forms need visible labels.
- Error messages must be text + color.
- Do not rely on color only.
- Support reduced motion.
- Dark mode must keep contrast.

## 24.2. Social safety UX

- Report/block visible but not intrusive.
- Sensitive content warning.
- Privacy settings accessible.
- User verification transparent.
- No manipulative dark patterns.
- No dating-style pressure mechanics.

## 24.3. Accessibility anti-patterns

- Removing focus outline.
- Cyan text on white.
- Light gray text below contrast.
- Icon-only actions without label.
- Infinite scroll without landmarks.
- Modal without focus trap.
- Toast used as only error feedback.
- Placeholder-only input labels.

---

# 25. Responsive System

## 25.1. Breakpoints

```css
--bp-mobile: 320px;
--bp-tablet: 640px;
--bp-desktop: 1024px;
--bp-wide: 1200px;
```

| Breakpoint | Layout |
|---|---|
| 320–639 | Mobile single feed + bottom nav |
| 640–1023 | Tablet feed + collapsed nav |
| 1024–1199 | Desktop left nav + feed |
| 1200+ | Desktop left nav + feed + right panel |

## 25.2. Mobile rules

- Bottom nav fixed.
- Main CTA full-width in forms.
- Post actions remain icon row.
- Composer should be sticky or easy to reach.
- Avoid dense side panels.
- Modals become bottom sheets where appropriate.

## 25.3. Desktop rules

- Left nav sticky.
- Feed centered.
- Right panel optional.
- Do not stretch feed beyond 640px.
- Keep content density controlled.

---

# 26. Dark Mode

## 26.1. Dark mode tokens

```css
[data-theme="dark"] {
  --ue-bg: #000000;
  --ue-surface: #101010;
  --ue-surface-subtle: #181818;
  --ue-surface-hover: #1F1F1F;

  --ue-text: #F8FAFC;
  --ue-text-secondary: #CBD5E1;
  --ue-text-muted: #94A3B8;

  --ue-border: #2A2A2A;
  --ue-border-strong: #3A3A3A;

  --ue-brand-soft: rgba(18,72,116,.22);
}
```

## 26.2. Dark mode rules

- Không đảo màu máy móc.
- Border phải đủ thấy nhưng không sáng quá.
- Brand blue có thể sáng hơn một chút trên dark.
- Post surface dark dùng `#101010`, background dùng black.
- Không dùng gradient full background.
- Images/media không bị overlay quá tối.

---

# 27. Content Tone

## 27.1. Voice

```txt
Clear
Friendly
Student-aware
Supportive
Not cringe
Not bureaucratic
Not dating-app coded
```

## 27.2. Good copy

```txt
Gửi lời chào
Khám phá UEers
Tìm mentor
Hoàn thiện hồ sơ
Bạn có thể chặn hoặc báo cáo bất kỳ lúc nào
```

## 27.3. Avoid copy

```txt
Swipe right
Match ngay
Crush của bạn
Hot UEer
Tán tỉnh
Submit
OK
Click here
```

## 27.4. Error copy

Bad:

```txt
Invalid input.
```

Better:

```txt
Email sinh viên chưa đúng định dạng. Vui lòng kiểm tra lại.
```

---

# 28. Design QA Checklist

Dùng checklist này trước khi duyệt UI.

## 28.1. Visual restraint

- [ ] Screen có dùng quá 10% brand blue không?
- [ ] Có gradient không cần thiết không?
- [ ] CTA chính có nổi bật nhưng không chói không?
- [ ] Neutral có đủ vai trò chính không?
- [ ] Màu semantic có bị dùng như decoration không?

## 28.2. Layout

- [ ] Feed width có nằm khoảng 560–640px không?
- [ ] Spacing có theo token không?
- [ ] Nav có quá nhiều mục không?
- [ ] Card/post có quá nhiều shadow không?
- [ ] Mobile có bottom nav rõ không?

## 28.3. Accessibility

- [ ] Text đủ contrast?
- [ ] Touch target >= 44px?
- [ ] Focus visible?
- [ ] Icon-only có aria-label?
- [ ] Error có text?
- [ ] Motion có reduced motion?

## 28.4. Product fit

- [ ] Có giống social platform thật không?
- [ ] Có tránh dating clone không?
- [ ] Có trust signal HCMUE không?
- [ ] Có dùng brand đúng chỗ không?
- [ ] Có cảm giác “AI-generated” không? Nếu có, giảm màu, giảm shadow, giảm gradient.

---

# 29. Component Priority

Thứ tự implement:

```txt
01. AppShell
02. Navigation
03. Button
04. IconButton
05. Avatar
06. Badge
07. Input
08. PostCard
09. Composer
10. Feed
11. ProfileHeader
12. DiscoveryCard
13. MessageBubble
14. Modal
15. Drawer
16. Toast
17. EmptyState
18. Skeleton
19. RightPanel
20. SafetyMenu
```

---

# 30. Design Token File Structure

```txt
src/
  styles/
    tokens/
      colors.css
      typography.css
      spacing.css
      radius.css
      shadows.css
      motion.css
      layout.css
      z-index.css
      themes.css
    globals.css
  components/
    primitives/
      Button.tsx
      IconButton.tsx
      Input.tsx
      Avatar.tsx
      Badge.tsx
      Card.tsx
      Modal.tsx
      Drawer.tsx
      Toast.tsx
    social/
      AppShell.tsx
      LeftNav.tsx
      BottomNav.tsx
      Feed.tsx
      PostCard.tsx
      Composer.tsx
      ProfileHeader.tsx
      DiscoveryCard.tsx
      MessageBubble.tsx
      EmptyState.tsx
```

---

# 31. Full CSS Token Reference

```css
:root {
  /* Brand */
  --ue-brand: #124874;
  --ue-brand-hover: #0E3A60;
  --ue-brand-active: #0A2B49;
  --ue-brand-soft: #EEF7FF;
  --ue-heritage-red: #CF373D;

  /* Neutral */
  --neutral-0: #FFFFFF;
  --neutral-25: #FCFCFD;
  --neutral-50: #FAFAFA;
  --neutral-100: #F8FAFC;
  --neutral-150: #F1F5F9;
  --neutral-200: #E4E6EB;
  --neutral-300: #CBD5E1;
  --neutral-400: #94A3B8;
  --neutral-500: #64748B;
  --neutral-600: #475569;
  --neutral-700: #334155;
  --neutral-800: #1E293B;
  --neutral-900: #0F172A;
  --neutral-950: #020617;

  /* Semantic */
  --success: #20C997;
  --success-text: #0F7B5C;
  --success-bg: rgba(32, 201, 151, 0.12);

  --warning: #F59E0B;
  --warning-text: #92400E;
  --warning-bg: rgba(245, 158, 11, 0.12);

  --danger: #C62828;
  --danger-text: #991B1B;
  --danger-bg: rgba(198, 40, 40, 0.10);

  --info: #2178D4;
  --info-text: #124874;
  --info-bg: rgba(18, 72, 116, 0.10);

  --mentor: #7C5CFF;
  --mentor-text: #5B3FD6;
  --mentor-bg: rgba(124, 92, 255, 0.10);

  /* Surfaces */
  --color-bg: var(--neutral-50);
  --color-surface: var(--neutral-0);
  --color-surface-subtle: var(--neutral-100);
  --color-surface-hover: var(--neutral-150);

  /* Text */
  --color-text: var(--neutral-900);
  --color-text-secondary: var(--neutral-600);
  --color-text-muted: var(--neutral-500);
  --color-text-disabled: var(--neutral-400);
  --color-text-inverse: var(--neutral-0);

  /* Borders */
  --color-border: var(--neutral-200);
  --color-border-strong: var(--neutral-300);

  /* Rare gradients */
  --ue-brand-moment-gradient: linear-gradient(
    135deg,
    #0B3558 0%,
    #124874 38%,
    #2178D4 72%,
    #38BDF8 100%
  );

  --ue-subtle-tint-gradient: linear-gradient(
    135deg,
    rgba(18, 72, 116, 0.06) 0%,
    rgba(56, 189, 248, 0.08) 100%
  );

  /* Typography */
  --font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Be Vietnam Pro", sans-serif;
  --font-vietnamese: "Be Vietnam Pro", system-ui, sans-serif;
  --font-data: "Inter", system-ui, sans-serif;
  --font-brand-serif: "Source Serif Variable", Georgia, serif;

  --text-2xs: 11px;
  --text-xs: 12px;
  --text-sm: 13px;
  --text-md: 14px;
  --text-base: 15px;
  --text-lg: 16px;
  --text-xl: 18px;
  --text-2xl: 20px;
  --text-3xl: 24px;
  --text-4xl: 32px;

  --leading-xs: 16px;
  --leading-sm: 18px;
  --leading-md: 20px;
  --leading-base: 21px;
  --leading-lg: 24px;
  --leading-xl: 28px;
  --leading-2xl: 32px;
  --leading-3xl: 40px;

  /* Spacing */
  --space-0: 0;
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 20px;
  --space-6: 24px;
  --space-8: 32px;
  --space-10: 40px;
  --space-12: 48px;
  --space-16: 64px;

  /* Radius */
  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-2xl: 20px;
  --radius-full: 999px;

  /* Shadow */
  --shadow-none: none;
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08);
  --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.10);
  --shadow-lg: 0 12px 32px rgba(15, 23, 42, 0.14);

  /* Layout */
  --layout-left-nav: 240px;
  --layout-feed: 600px;
  --layout-right-panel: 300px;
  --layout-gutter: 24px;
  --layout-shell-max: 1200px;

  /* Motion */
  --duration-instant: 80ms;
  --duration-fast: 120ms;
  --duration-base: 180ms;
  --duration-slow: 240ms;

  --ease-standard: cubic-bezier(0.2, 0, 0, 1);
  --ease-out: cubic-bezier(0, 0, 0.2, 1);

  /* Z-index */
  --z-base: 0;
  --z-sticky: 100;
  --z-dropdown: 200;
  --z-overlay: 300;
  --z-modal: 400;
  --z-toast: 500;
}
```

---

# 32. AI Prompt Rules

Khi dùng AI tạo UI cho UEConnect, dùng prompt:

```txt
Design a minimal enterprise-grade social platform UI for UEConnect, a verified HCMUE student community app.
Use a neutral-first interface similar to Threads/Instagram: white and near-white surfaces, subtle borders, content-first feed, icon-driven navigation.
Use the official HCMUE blue #124874 only as a restrained brand accent for logo, active nav, primary CTA, verified badge, links, and focus states.
Do not flood the interface with gradients or primary color.
Do not use red/pink/orange/coral in primary UI.
Use clean system typography, readable 15px body text, subtle 1px borders, restrained shadows, and accessible 44px touch targets.
The UI must feel like a real enterprise social product, not an AI-generated gradient landing page.
```

Không dùng prompt:

```txt
Make it modern with lots of gradients.
Make it look like Tinder.
Use primary color everywhere.
Make it colorful and eye-catching.
Use futuristic glow.
```

Vì AI nghe mấy câu đó là bắt đầu nấu một nồi lẩu gradient mà không ai trong team design muốn ăn.

---

# 33. Final Do / Don't

## Do

- Build neutral-first UI.
- Use brand blue only where useful.
- Design feed like a real social product.
- Use borders before shadows.
- Keep icons line-based and neutral.
- Keep typography modest and readable.
- Make verified trust signals clear but small.
- Support dark mode.
- Prioritize accessibility.
- Review every screen for “AI-generated smell”.

## Don't

- Don't use gradient as default background.
- Don't make every card blue.
- Don't use brand color for every icon.
- Don't copy Tinder’s language.
- Don't use giant pill CTA everywhere.
- Don't make shadow/radius excessive.
- Don't hide important safety actions.
- Don't rely on color only.
- Don't let marketing style leak into product UI.
- Don't over-design simple states.

---

# 34. Final Direction Statement

```txt
UEConnect is a calm, verified HCMUE social platform.
Its UI should feel content-first, neutral, accessible, and trustworthy.
The HCMUE identity appears through precise, restrained use of #124874,
not through flooding the interface with gradients.
```

Một câu chốt để tự kiểm UI:

```txt
Nếu bỏ logo ra mà screen vẫn hét "tôi là màu xanh HCMUE" quá to,
thì screen đó đang dùng brand sai cách.
```
