---
title: "Design Token Documentation"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / Design System / Frontend"
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
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
related:
  - "../03-product/product-overview.md"
  - "../03-product/sitemap.md"
  - "../03-product/feature-list.md"
  - "../03-product/feature-priority.md"
---

# Design Token Documentation

## 1. Purpose

Design Token Documentation định nghĩa hệ thống token chính thức của UEConnect.

Token là các giá trị thiết kế có tên, được dùng thống nhất trong design và code:

- màu sắc
- typography
- spacing
- radius
- shadow
- border
- layout
- icon
- logo
- motion
- z-index
- breakpoint
- component alias
- semantic state

Mục tiêu của file này là đảm bảo UEConnect có một nguồn chuẩn khi thiết kế UI, viết Blade component, config TailwindCSS, tạo page, tạo admin dashboard hoặc sinh UI bằng AI.

Nếu một màu, khoảng cách, shadow, radius hoặc font-size không nằm trong token, đừng tự tiện dùng. Tự tiện một lần thì vui, tự tiện 200 lần thì thành di sản kỹ thuật số cần khai quật.

---

## 2. Token Principles

### 2.1. Token First

Mọi UI phải ưu tiên token thay vì hardcode giá trị.

Đúng:

```txt
color.brand.primary
spacing.4
radius.xl
shadow.sm
font.size.body
````

Sai:

```txt
#1367aa dùng đại
17px vì nhìn vừa
border-radius: 13px
box-shadow tự chế
```

### 2.2. Semantic Over Raw

Ưu tiên token theo ý nghĩa.

Good:

```txt
color.text.primary
color.surface.default
color.border.default
color.status.warning.bg
```

Bad:

```txt
blue-700
gray-100
yellow-50
```

Raw color có thể tồn tại ở primitive scale, nhưng UI production nên dùng semantic token.

### 2.3. Brand Consistency

UEConnect phải giữ cảm giác:

```txt
trusted
academic
youthful
clean
HCMUE-rooted
```

Token không được làm app trông như:

```txt
dating app
generic SaaS dashboard
old government portal
crypto community
random Bootstrap theme
```

### 2.4. Token Must Map To Code

Token phải có thể map sang:

```txt
TailwindCSS config
CSS variables
Blade components
Alpine/Livewire UI state
design files
AI prompt guide
```

Token không map được sang code thì chỉ là văn thơ kỹ thuật. Đẹp đấy, vô dụng đấy.

### 2.5. Accessibility First

Token phải hỗ trợ:

```txt
WCAG AA contrast
visible focus
reduced motion
touch target
readable typography
responsive spacing
```

---

# 3. Token Naming Convention

## 3.1. Format

Token dùng dot notation:

```txt
category.group.name.state
```

Examples:

```txt
color.brand.primary
color.text.secondary
color.surface.default
spacing.4
radius.xl
shadow.md
motion.duration.md
z.modal
```

## 3.2. Naming Rules

```txt
- dùng lowercase
- dùng kebab-case nếu cần nhiều từ trong cùng cấp
- không dùng tên theo page cụ thể
- không dùng tên theo cảm xúc mơ hồ
- không dùng tên theo màu nếu đó là semantic token
```

Good:

```txt
color.action.primary.bg
color.status.danger.text
color.feed.card.bg
```

Bad:

```txt
color.home-blue
color.nice-gray
color.mentor-card-special
color.blue-but-soft
```

“nice-gray” là cách một design system bắt đầu mất trí nhớ.

---

# 4. Token Architecture

## 4.1. Token Layers

UEConnect token system có 4 lớp:

| Layer            | Purpose            | Example                   |
| ---------------- | ------------------ | ------------------------- |
| Primitive Tokens | Giá trị gốc        | `primitive.blue.700`      |
| Semantic Tokens  | Ý nghĩa UI         | `color.brand.primary`     |
| Component Tokens | Gắn với component  | `button.primary.bg`       |
| State Tokens     | Gắn với trạng thái | `color.status.warning.bg` |

## 4.2. Primitive Token

Primitive token là raw scale.

Example:

```txt
primitive.blue.900 = #0A3761
primitive.blue.800 = #124874
primitive.blue.700 = #005BAC
primitive.blue.500 = #0B7FEA
primitive.blue.300 = #4BB7E8
```

## 4.3. Semantic Token

Semantic token là thứ UI nên dùng.

Example:

```txt
color.brand.primary = primitive.blue.800
color.text.primary = primitive.neutral.900
color.surface.default = primitive.white
```

## 4.4. Component Token

Component token map trực tiếp tới component.

Example:

```txt
button.primary.bg = color.brand.primary
button.primary.text = color.text.inverse
button.primary.hover.bg = color.brand.primary-hover
```

## 4.5. State Token

State token map tới status.

Example:

```txt
badge.warning.bg = color.status.warning.bg
badge.warning.text = color.status.warning.text
```

---

# 5. Color Tokens

## 5.1. Brand Colors

Primary brand anchor:

```txt
color.brand.primary = #124874
```

Supporting brand colors:

```txt
color.brand.deep = #0A3761
color.brand.primary = #124874
color.brand.academic = #005BAC
color.brand.bright = #0B7FEA
color.brand.soft = #4BB7E8
color.brand.pale = #EAF4FF
```

Usage:

| Token                  | Usage                                  |
| ---------------------- | -------------------------------------- |
| `color.brand.primary`  | Main brand color, primary button, logo |
| `color.brand.deep`     | Dark blue background, strong headers   |
| `color.brand.academic` | Link/active states, charts if needed   |
| `color.brand.bright`   | Accent highlight, focus support        |
| `color.brand.soft`     | Soft accent, illustration, gradient    |
| `color.brand.pale`     | Soft background                        |

## 5.2. Neutral Colors

```txt
color.neutral.0 = #FFFFFF
color.neutral.50 = #F8FAFC
color.neutral.100 = #F1F5F9
color.neutral.200 = #E2E8F0
color.neutral.300 = #CBD5E1
color.neutral.400 = #94A3B8
color.neutral.500 = #64748B
color.neutral.600 = #475569
color.neutral.700 = #334155
color.neutral.800 = #1E293B
color.neutral.900 = #0F172A
color.neutral.950 = #020617
```

## 5.3. Text Colors

```txt
color.text.primary = color.neutral.900
color.text.secondary = color.neutral.600
color.text.tertiary = color.neutral.500
color.text.muted = color.neutral.400
color.text.inverse = #FFFFFF
color.text.brand = color.brand.primary
color.text.danger = color.status.danger.text
color.text.warning = color.status.warning.text
color.text.success = color.status.success.text
```

Usage:

```txt
primary: title/body important
secondary: metadata/helper
tertiary: placeholder/subtle metadata
muted: disabled or low emphasis
inverse: text on blue/dark background
```

## 5.4. Surface Colors

```txt
color.surface.default = #FFFFFF
color.surface.subtle = #F8FAFC
color.surface.soft = #F1F5F9
color.surface.brand-subtle = #EAF4FF
color.surface.elevated = #FFFFFF
color.surface.overlay = rgba(15, 23, 42, 0.48)
color.surface.inverse = #0A3761
color.surface.disabled = #F1F5F9
```

## 5.5. Border Colors

```txt
color.border.default = #E2E8F0
color.border.subtle = #F1F5F9
color.border.strong = #CBD5E1
color.border.brand = #124874
color.border.brand-subtle = #B9DDF7
color.border.danger = #DC2626
color.border.warning = #D97706
color.border.success = #16A34A
color.border.focus = #0B7FEA
```

## 5.6. Action Colors

```txt
color.action.primary.bg = #124874
color.action.primary.text = #FFFFFF
color.action.primary.hover = #0A3761
color.action.primary.active = #082F56

color.action.secondary.bg = #F1F5F9
color.action.secondary.text = #0F172A
color.action.secondary.hover = #E2E8F0

color.action.danger.bg = #DC2626
color.action.danger.text = #FFFFFF
color.action.danger.hover = #B91C1C
color.action.danger.active = #991B1B
```

## 5.7. Status Colors

### Success

```txt
color.status.success.bg = #DCFCE7
color.status.success.soft = #F0FDF4
color.status.success.border = #86EFAC
color.status.success.text = #166534
color.status.success.icon = #16A34A
```

### Warning

```txt
color.status.warning.bg = #FEF3C7
color.status.warning.soft = #FFFBEB
color.status.warning.border = #FCD34D
color.status.warning.text = #92400E
color.status.warning.icon = #D97706
```

### Danger

```txt
color.status.danger.bg = #FEE2E2
color.status.danger.soft = #FEF2F2
color.status.danger.border = #FCA5A5
color.status.danger.text = #991B1B
color.status.danger.icon = #DC2626
```

### Info

```txt
color.status.info.bg = #DBEAFE
color.status.info.soft = #EFF6FF
color.status.info.border = #93C5FD
color.status.info.text = #1E40AF
color.status.info.icon = #2563EB
```

### Neutral

```txt
color.status.neutral.bg = #F1F5F9
color.status.neutral.border = #CBD5E1
color.status.neutral.text = #334155
color.status.neutral.icon = #64748B
```

## 5.8. Role Colors

Role colors should remain subtle.

```txt
color.role.student.bg = color.brand.pale
color.role.student.text = color.brand.primary

color.role.alumni.bg = #EEF2FF
color.role.alumni.text = #3730A3

color.role.advisor.bg = #ECFEFF
color.role.advisor.text = #155E75

color.role.mentor.bg = #EAF4FF
color.role.mentor.text = #124874

color.role.admin.bg = #FEE2E2
color.role.admin.text = #991B1B

color.role.club-manager.bg = #F0FDF4
color.role.club-manager.text = #166534
```

## 5.9. Moderation Colors

```txt
color.moderation.flagged.bg = color.status.warning.soft
color.moderation.hidden.bg = color.status.warning.bg
color.moderation.removed.bg = color.status.danger.bg
color.moderation.restored.bg = color.status.success.bg
color.moderation.placeholder.bg = color.neutral.100
```

## 5.10. Gradient Tokens

UEConnect chỉ dùng blue-only gradient.

```txt
gradient.brand.primary = linear-gradient(135deg, #0A3761 0%, #124874 42%, #0B7FEA 100%)
gradient.brand.soft = linear-gradient(135deg, #EAF4FF 0%, #F8FAFC 45%, #DBEAFE 100%)
gradient.brand.app-icon = linear-gradient(135deg, #0A3761 0%, #005BAC 48%, #0B7FEA 100%)
gradient.brand.hero = linear-gradient(135deg, #0A3761 0%, #124874 45%, #4BB7E8 100%)
```

Forbidden gradients:

```txt
red/pink/orange dating gradient
rainbow
neon
purple romance gradient
```

UEConnect có thể mượt như Tinder, nhưng không được thơm mùi Tinder. Cần phân biệt cảm hứng kỹ thuật và bắt chước linh hồn.

---

# 6. Typography Tokens

## 6.1. Font Family

Primary UI font:

```txt
font.family.sans = "Be Vietnam Pro", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif
```

Fallback:

```txt
font.family.system = system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif
```

Monospace:

```txt
font.family.mono = "JetBrains Mono", "SFMono-Regular", Consolas, monospace
```

## 6.2. Font Weight

```txt
font.weight.regular = 400
font.weight.medium = 500
font.weight.semibold = 600
font.weight.bold = 700
font.weight.extrabold = 800
```

## 6.3. Font Size

```txt
font.size.xs = 12px
font.size.sm = 14px
font.size.base = 16px
font.size.lg = 18px
font.size.xl = 20px
font.size.2xl = 24px
font.size.3xl = 30px
font.size.4xl = 36px
font.size.5xl = 48px
font.size.6xl = 56px
```

## 6.4. Line Height

```txt
font.lineHeight.xs = 16px
font.lineHeight.sm = 20px
font.lineHeight.base = 24px
font.lineHeight.lg = 28px
font.lineHeight.xl = 30px
font.lineHeight.2xl = 32px
font.lineHeight.3xl = 38px
font.lineHeight.4xl = 44px
font.lineHeight.5xl = 56px
font.lineHeight.6xl = 64px
```

## 6.5. Letter Spacing

```txt
font.letterSpacing.tight = -0.02em
font.letterSpacing.normal = 0
font.letterSpacing.wide = 0.04em
font.letterSpacing.logo-tagline = 0.16em
```

## 6.6. Typography Semantic Tokens

### Display

```txt
typography.display.fontSize = font.size.5xl
typography.display.lineHeight = font.lineHeight.5xl
typography.display.fontWeight = font.weight.extrabold
typography.display.letterSpacing = font.letterSpacing.tight
```

### Page Title

```txt
typography.page-title.fontSize = font.size.3xl
typography.page-title.lineHeight = font.lineHeight.3xl
typography.page-title.fontWeight = font.weight.bold
typography.page-title.letterSpacing = font.letterSpacing.tight
```

### Section Title

```txt
typography.section-title.fontSize = font.size.2xl
typography.section-title.lineHeight = font.lineHeight.2xl
typography.section-title.fontWeight = font.weight.bold
```

### Card Title

```txt
typography.card-title.fontSize = font.size.lg
typography.card-title.lineHeight = font.lineHeight.lg
typography.card-title.fontWeight = font.weight.semibold
```

### Body

```txt
typography.body.fontSize = font.size.base
typography.body.lineHeight = font.lineHeight.base
typography.body.fontWeight = font.weight.regular
```

### Body Small

```txt
typography.body-sm.fontSize = font.size.sm
typography.body-sm.lineHeight = font.lineHeight.sm
typography.body-sm.fontWeight = font.weight.regular
```

### Caption

```txt
typography.caption.fontSize = font.size.xs
typography.caption.lineHeight = font.lineHeight.xs
typography.caption.fontWeight = font.weight.medium
```

### Button

```txt
typography.button.fontSize = font.size.sm
typography.button.lineHeight = font.lineHeight.sm
typography.button.fontWeight = font.weight.semibold
```

### Badge

```txt
typography.badge.fontSize = font.size.xs
typography.badge.lineHeight = font.lineHeight.xs
typography.badge.fontWeight = font.weight.semibold
```

## 6.7. Responsive Type Tokens

Mobile:

```txt
typography.mobile.page-title = 24px / 32px / 700
typography.mobile.section-title = 20px / 28px / 700
typography.mobile.body = 16px / 24px / 400
```

Desktop:

```txt
typography.desktop.page-title = 32px / 40px / 700
typography.desktop.section-title = 24px / 32px / 700
typography.desktop.body = 16px / 24px / 400
```

---

# 7. Spacing Tokens

## 7.1. Base Spacing Scale

Base unit:

```txt
spacing.base = 4px
```

Scale:

```txt
spacing.0 = 0px
spacing.0_5 = 2px
spacing.1 = 4px
spacing.1_5 = 6px
spacing.2 = 8px
spacing.2_5 = 10px
spacing.3 = 12px
spacing.3_5 = 14px
spacing.4 = 16px
spacing.5 = 20px
spacing.6 = 24px
spacing.7 = 28px
spacing.8 = 32px
spacing.10 = 40px
spacing.12 = 48px
spacing.14 = 56px
spacing.16 = 64px
spacing.20 = 80px
spacing.24 = 96px
spacing.32 = 128px
```

## 7.2. Semantic Spacing

```txt
spacing.page.mobile = spacing.4
spacing.page.tablet = spacing.6
spacing.page.desktop = spacing.8
spacing.page.large = spacing.10

spacing.section.mobile = spacing.6
spacing.section.desktop = spacing.10

spacing.card.mobile = spacing.4
spacing.card.desktop = spacing.6

spacing.form.field-gap = spacing.4
spacing.form.section-gap = spacing.6
spacing.form.action-gap = spacing.3

spacing.nav.item-gap = spacing.2
spacing.nav.section-gap = spacing.4

spacing.list.item-gap = spacing.3
spacing.feed.card-gap = spacing.4
spacing.admin.row-gap = spacing.2
```

## 7.3. Touch Target

```txt
size.touch.min = 44px
```

Applies to:

```txt
button
icon button
bottom nav item
checkbox/radio label
tab
dropdown trigger
message action
```

## 7.4. Container Widths

```txt
container.auth = 480px
container.verification = 720px
container.feed = 720px
container.profile = 1120px
container.community = 1280px
container.admin = 1440px
container.prose = 720px
```

---

# 8. Radius Tokens

## 8.1. Radius Scale

```txt
radius.none = 0px
radius.xs = 4px
radius.sm = 6px
radius.md = 8px
radius.lg = 12px
radius.xl = 16px
radius.2xl = 20px
radius.3xl = 24px
radius.full = 9999px
```

## 8.2. Semantic Radius

```txt
radius.button = radius.lg
radius.button-pill = radius.full
radius.input = radius.lg
radius.card = radius.2xl
radius.modal = radius.2xl
radius.sheet = radius.2xl
radius.badge = radius.full
radius.avatar = radius.full
radius.community-avatar = radius.xl
radius.toast = radius.xl
radius.app-icon = radius.3xl
```

## 8.3. Rules

```txt
- Cards use xl/2xl.
- Buttons use lg/full depending style.
- Inputs use lg.
- Avatar user uses full.
- Community avatar uses rounded-square.
- Admin dense surfaces can use lg/xl.
```

Không tự chế radius 13px vì “nhìn vừa mắt”. 13px không làm bạn khác biệt, nó làm CSS bạn đáng ngờ.

---

# 9. Shadow & Elevation Tokens

## 9.1. Shadow Scale

```txt
shadow.none = none

shadow.xs = 0 1px 2px rgba(15, 23, 42, 0.05)

shadow.sm = 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04)

shadow.md = 0 4px 12px rgba(15, 23, 42, 0.10)

shadow.lg = 0 10px 24px rgba(15, 23, 42, 0.12)

shadow.xl = 0 20px 40px rgba(15, 23, 42, 0.16)

shadow.brand = 0 12px 32px rgba(18, 72, 116, 0.18)

shadow.focus = 0 0 0 3px rgba(11, 127, 234, 0.28)
```

## 9.2. Elevation Levels

| Level | Token          | Usage                 |
| ----- | -------------- | --------------------- |
| 0     | `shadow.none`  | Flat surfaces         |
| 1     | `shadow.xs`    | Subtle cards          |
| 2     | `shadow.sm`    | Default cards         |
| 3     | `shadow.md`    | Hover/elevated cards  |
| 4     | `shadow.lg`    | Dropdown/toast        |
| 5     | `shadow.xl`    | Modal/sheet           |
| Brand | `shadow.brand` | Hero/auth brand panel |

## 9.3. Semantic Shadow

```txt
shadow.card.default = shadow.sm
shadow.card.hover = shadow.md
shadow.dropdown = shadow.lg
shadow.modal = shadow.xl
shadow.sheet = shadow.xl
shadow.toast = shadow.lg
shadow.button.focus = shadow.focus
```

## 9.4. Rules

```txt
- Do not overuse large shadows.
- Admin dense UI should use borders more than heavy shadows.
- Long feed lists should avoid expensive heavy shadows.
- Brand shadow is rare.
```

---

# 10. Border Tokens

## 10.1. Border Width

```txt
border.width.none = 0px
border.width.hairline = 1px
border.width.default = 1px
border.width.strong = 2px
border.width.focus = 2px
```

## 10.2. Border Style

```txt
border.style.solid = solid
border.style.dashed = dashed
```

## 10.3. Semantic Border

```txt
border.default = 1px solid color.border.default
border.subtle = 1px solid color.border.subtle
border.strong = 1px solid color.border.strong
border.focus = 2px solid color.border.focus
border.danger = 1px solid color.border.danger
border.warning = 1px solid color.border.warning
border.success = 1px solid color.border.success
```

## 10.4. Divider

```txt
divider.default = 1px solid color.border.default
divider.subtle = 1px solid color.border.subtle
divider.strong = 1px solid color.border.strong
```

---

# 11. Icon Tokens

## 11.1. Icon Library

Recommended:

```txt
Lucide Icons
```

## 11.2. Icon Size

```txt
icon.size.xs = 12px
icon.size.sm = 16px
icon.size.md = 20px
icon.size.lg = 24px
icon.size.xl = 32px
icon.size.2xl = 40px
```

## 11.3. Icon Stroke

```txt
icon.stroke.default = 2
icon.stroke.soft = 1.75
icon.stroke.strong = 2.25
```

## 11.4. Semantic Icon Colors

```txt
icon.color.default = color.text.secondary
icon.color.primary = color.brand.primary
icon.color.inverse = color.text.inverse
icon.color.success = color.status.success.icon
icon.color.warning = color.status.warning.icon
icon.color.danger = color.status.danger.icon
icon.color.muted = color.text.muted
```

## 11.5. Icon Usage Tokens

```txt
icon.button.size = icon.size.md
icon.nav.size = icon.size.lg
icon.badge.size = icon.size.xs
icon.empty-state.size = icon.size.2xl
icon.toast.size = icon.size.md
icon.table-action.size = icon.size.sm
```

## 11.6. Rules

```txt
- Icon-only button must have aria-label.
- Icons are support, not replacement for status text.
- Do not mix icon styles randomly.
- Do not use heart/dating-like icons for discovery/greeting.
```

---

# 12. Logo Tokens

## 12.1. Logo Size

```txt
logo.mark.xs = 16px
logo.mark.sm = 24px
logo.mark.md = 32px
logo.mark.lg = 48px
logo.mark.xl = 64px

logo.horizontal.sm.height = 28px
logo.horizontal.md.height = 32px
logo.horizontal.lg.height = 40px

logo.primary.md.height = 40px
logo.primary.lg.height = 64px
logo.primary.xl.height = 96px
```

## 12.2. Logo Color

```txt
logo.color.default = color.brand.primary
logo.color.inverse = color.text.inverse
logo.color.neutral = color.text.primary
```

## 12.3. Logo Background

```txt
logo.bg.app-icon = gradient.brand.app-icon
logo.bg.splash-light = color.surface.default
logo.bg.splash-dark = color.brand.primary
```

## 12.4. Logo Rules

```txt
- Icon mark does not explicitly spell UE.
- Primary logo includes wordmark and optional tagline.
- Horizontal logo does not include tagline.
- App icon uses icon mark only.
- Favicon uses icon mark only.
```

---

# 13. Motion Tokens

## 13.1. Duration

```txt
motion.duration.instant = 0ms
motion.duration.xs = 75ms
motion.duration.sm = 120ms
motion.duration.md = 180ms
motion.duration.lg = 240ms
motion.duration.xl = 320ms
motion.duration.2xl = 480ms
```

## 13.2. Easing

```txt
motion.ease.standard = cubic-bezier(0.2, 0, 0, 1)
motion.ease.out = cubic-bezier(0, 0, 0.2, 1)
motion.ease.in = cubic-bezier(0.4, 0, 1, 1)
motion.ease.inOut = cubic-bezier(0.4, 0, 0.2, 1)
motion.ease.emphasized = cubic-bezier(0.16, 1, 0.3, 1)
motion.ease.linear = linear
```

## 13.3. Distance

```txt
motion.distance.xs = 2px
motion.distance.sm = 4px
motion.distance.md = 8px
motion.distance.lg = 16px
motion.distance.xl = 24px
motion.distance.2xl = 40px
```

## 13.4. Scale

```txt
motion.scale.press = 0.98
motion.scale.enter = 0.98 to 1
motion.scale.exit = 1 to 0.98
```

## 13.5. Semantic Motion

```txt
motion.button.hover.duration = motion.duration.sm
motion.button.press.duration = motion.duration.xs

motion.card.hover.duration = motion.duration.md
motion.modal.enter.duration = motion.duration.lg
motion.modal.exit.duration = motion.duration.md
motion.sheet.enter.duration = motion.duration.lg
motion.toast.enter.duration = motion.duration.md
motion.dropdown.enter.duration = motion.duration.sm
```

## 13.6. Reduced Motion

```txt
motion.reduced.duration = motion.duration.instant
motion.reduced.transform = none
motion.reduced.shimmer = none
```

---

# 14. Z-index Tokens

## 14.1. Scale

```txt
z.base = 0
z.raised = 10
z.sticky = 100
z.header = 200
z.bottom-nav = 250
z.dropdown = 400
z.popover = 500
z.tooltip = 600
z.overlay = 700
z.modal = 800
z.sheet = 850
z.toast = 900
z.devtools = 9999
```

## 14.2. Rules

```txt
- Do not use random z-index like 999999.
- Modal/sheet must appear above overlay.
- Toast appears above most UI but should not block critical modal.
- Header/bottom nav must not cover focusable content.
```

Random z-index là cách CSS biến thành chiến trường. Và thường, chiến trường thắng.

---

# 15. Breakpoint Tokens

```txt
breakpoint.xs = 360px
breakpoint.sm = 640px
breakpoint.md = 768px
breakpoint.lg = 1024px
breakpoint.xl = 1280px
breakpoint.2xl = 1536px
```

Minimum supported:

```txt
viewport.min = 320px
```

Primary design widths:

```txt
viewport.mobile = 390px
viewport.tablet = 768px
viewport.desktop = 1440px
```

---

# 16. Layout Tokens

## 16.1. Header

```txt
layout.header.mobile.height = 56px
layout.header.desktop.height = 64px
layout.header.admin.height = 64px
```

## 16.2. Bottom Nav

```txt
layout.bottom-nav.height = 64px
layout.bottom-nav.safe-padding = env(safe-area-inset-bottom)
```

## 16.3. Sidebar

```txt
layout.sidebar.width = 280px
layout.sidebar.collapsed-width = 72px
layout.admin-sidebar.width = 280px
```

## 16.4. Feed

```txt
layout.feed.column.width = 680px
layout.feed.right-rail.width = 320px
```

## 16.5. Messaging

```txt
layout.messages.conversation-list.width = 340px
layout.messages.right-rail.width = 320px
layout.messages.composer.min-height = 56px
```

## 16.6. Admin

```txt
layout.admin.content.max-width = 1440px
layout.admin.table.row-height = 56px
layout.admin.table.compact-row-height = 44px
```

---

# 17. Component Tokens

## 17.1. Button Tokens

```txt
button.height.sm = 32px
button.height.md = 40px
button.height.lg = 48px
button.height.xl = 56px

button.padding.x.sm = spacing.3
button.padding.x.md = spacing.4
button.padding.x.lg = spacing.5
button.radius = radius.lg
button.font = typography.button
button.gap = spacing.2
```

Primary:

```txt
button.primary.bg = color.action.primary.bg
button.primary.text = color.action.primary.text
button.primary.hover.bg = color.action.primary.hover
button.primary.active.bg = color.action.primary.active
```

Secondary:

```txt
button.secondary.bg = color.action.secondary.bg
button.secondary.text = color.action.secondary.text
button.secondary.hover.bg = color.action.secondary.hover
```

Danger:

```txt
button.danger.bg = color.action.danger.bg
button.danger.text = color.action.danger.text
button.danger.hover.bg = color.action.danger.hover
```

## 17.2. Input Tokens

```txt
input.height.sm = 36px
input.height.md = 44px
input.height.lg = 52px

input.radius = radius.lg
input.border = color.border.default
input.border.focus = color.border.focus
input.border.error = color.border.danger
input.bg = color.surface.default
input.bg.disabled = color.surface.disabled
input.text = color.text.primary
input.placeholder = color.text.tertiary
input.padding.x = spacing.3
```

## 17.3. Card Tokens

```txt
card.bg = color.surface.default
card.border = color.border.default
card.radius = radius.2xl
card.shadow = shadow.sm
card.shadow.hover = shadow.md
card.padding.mobile = spacing.4
card.padding.desktop = spacing.6
```

## 17.4. Badge Tokens

```txt
badge.radius = radius.full
badge.padding.x = spacing.2
badge.padding.y = spacing.0_5
badge.font = typography.badge
badge.gap = spacing.1
```

## 17.5. Avatar Tokens

```txt
avatar.size.xs = 24px
avatar.size.sm = 32px
avatar.size.md = 40px
avatar.size.lg = 56px
avatar.size.xl = 80px
avatar.size.2xl = 112px
avatar.radius.user = radius.full
avatar.radius.community = radius.xl
```

## 17.6. Modal Tokens

```txt
modal.width.confirmation = 480px
modal.width.form = 640px
modal.width.preview = 960px
modal.radius = radius.2xl
modal.shadow = shadow.xl
modal.padding = spacing.6
modal.overlay.bg = color.surface.overlay
```

## 17.7. Sheet Tokens

```txt
sheet.radius.top = radius.2xl
sheet.shadow = shadow.xl
sheet.padding = spacing.4
sheet.mobile.max-height = 90vh
```

## 17.8. Toast Tokens

```txt
toast.width = 360px
toast.radius = radius.xl
toast.shadow = shadow.lg
toast.padding = spacing.4
toast.gap = spacing.3
toast.z = z.toast
```

## 17.9. Skeleton Tokens

```txt
skeleton.bg = color.neutral.100
skeleton.highlight = color.neutral.200
skeleton.radius = radius.md
skeleton.animation.duration = 1400ms
```

## 17.10. Table Tokens

```txt
table.row.height = 56px
table.row.compact-height = 44px
table.cell.padding.x = spacing.4
table.cell.padding.y = spacing.3
table.header.bg = color.surface.subtle
table.border = color.border.default
```

---

# 18. Feature-specific Token Aliases

## 18.1. Verification

```txt
verification.status.pending.bg = color.status.warning.bg
verification.status.approved.bg = color.status.success.bg
verification.status.rejected.bg = color.status.danger.bg
verification.evidence.card.radius = radius.xl
verification.evidence.preview.modal.width = modal.width.preview
```

## 18.2. Feed

```txt
feed.card.width = layout.feed.column.width
feed.card.gap = spacing.feed.card-gap
feed.composer.radius = radius.2xl
feed.image.radius = radius.xl
```

## 18.3. Discovery

```txt
discovery.card.radius = radius.3xl
discovery.card.shadow = shadow.md
discovery.action.primary = color.brand.primary
discovery.action.pass = color.neutral.600
```

No red/pink/heart token. Không phải lỗi thiếu token, mà là cố ý.

## 18.4. Messaging

```txt
message.bubble.sent.bg = color.brand.primary
message.bubble.sent.text = color.text.inverse
message.bubble.received.bg = color.surface.soft
message.bubble.received.text = color.text.primary
message.bubble.radius = radius.2xl
message.composer.height.min = layout.messages.composer.min-height
```

## 18.5. Mentor

```txt
mentor.available.bg = color.status.success.bg
mentor.limited.bg = color.status.warning.bg
mentor.paused.bg = color.status.neutral.bg
mentor.card.radius = radius.2xl
```

## 18.6. Community

```txt
community.card.radius = radius.2xl
community.avatar.radius = radius.xl
community.suspended.bg = color.status.danger.soft
community.resource.card.radius = radius.xl
```

## 18.7. Admin

```txt
admin.sidebar.width = layout.admin-sidebar.width
admin.card.radius = radius.xl
admin.table.row-height = layout.admin.table.row-height
admin.table.compact-row-height = layout.admin.table.compact-row-height
admin.danger.bg = color.status.danger.soft
```

---

# 19. CSS Variable Mapping

## 19.1. Recommended CSS Variables

```css
:root {
  --color-brand-primary: #124874;
  --color-brand-deep: #0A3761;
  --color-brand-academic: #005BAC;
  --color-brand-bright: #0B7FEA;
  --color-brand-soft: #4BB7E8;
  --color-brand-pale: #EAF4FF;

  --color-text-primary: #0F172A;
  --color-text-secondary: #475569;
  --color-text-tertiary: #64748B;
  --color-text-muted: #94A3B8;
  --color-text-inverse: #FFFFFF;

  --color-surface-default: #FFFFFF;
  --color-surface-subtle: #F8FAFC;
  --color-surface-soft: #F1F5F9;
  --color-surface-brand-subtle: #EAF4FF;

  --color-border-default: #E2E8F0;
  --color-border-strong: #CBD5E1;
  --color-border-focus: #0B7FEA;

  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-2xl: 20px;

  --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04);
  --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.10);
  --shadow-lg: 0 10px 24px rgba(15, 23, 42, 0.12);

  --motion-duration-sm: 120ms;
  --motion-duration-md: 180ms;
  --motion-duration-lg: 240ms;
  --motion-ease-standard: cubic-bezier(0.2, 0, 0, 1);
}
```

## 19.2. CSS Variable Rule

Use CSS variables for:

```txt
themeable colors
runtime state
component styling
dark mode future
brand consistency
```

Avoid duplicating raw hex in component files.

---

# 20. TailwindCSS Mapping

## 20.1. Tailwind Config Direction

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        brand: {
          deep: "#0A3761",
          primary: "#124874",
          academic: "#005BAC",
          bright: "#0B7FEA",
          soft: "#4BB7E8",
          pale: "#EAF4FF",
        },
        surface: {
          default: "#FFFFFF",
          subtle: "#F8FAFC",
          soft: "#F1F5F9",
          brand: "#EAF4FF",
        },
        ink: {
          primary: "#0F172A",
          secondary: "#475569",
          tertiary: "#64748B",
          muted: "#94A3B8",
        },
      },
      fontFamily: {
        sans: ["Be Vietnam Pro", "system-ui", "sans-serif"],
      },
      borderRadius: {
        xl: "16px",
        "2xl": "20px",
        "3xl": "24px",
      },
      boxShadow: {
        xs: "0 1px 2px rgba(15, 23, 42, 0.05)",
        sm: "0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04)",
        md: "0 4px 12px rgba(15, 23, 42, 0.10)",
        lg: "0 10px 24px rgba(15, 23, 42, 0.12)",
        xl: "0 20px 40px rgba(15, 23, 42, 0.16)",
        brand: "0 12px 32px rgba(18, 72, 116, 0.18)",
      },
      transitionDuration: {
        xs: "75ms",
        sm: "120ms",
        md: "180ms",
        lg: "240ms",
        xl: "320ms",
      },
      transitionTimingFunction: {
        standard: "cubic-bezier(0.2, 0, 0, 1)",
        emphasized: "cubic-bezier(0.16, 1, 0.3, 1)",
      },
      zIndex: {
        header: "200",
        dropdown: "400",
        modal: "800",
        sheet: "850",
        toast: "900",
      },
    },
  },
};
```

## 20.2. Tailwind Usage Rules

Allowed:

```html
<button class="bg-brand-primary text-white rounded-lg px-4 py-2">
```

Better through component:

```blade
<x-ui.button variant="primary">
  Gửi lời chào
</x-ui.button>
```

Avoid:

```html
<button class="bg-[#126392] rounded-[13px] shadow-[0_6px_17px_#123]">
```

Đây là lúc Tailwind trở thành súng bắn vào chân. Dùng cẩn thận.

---

# 21. Blade Component Mapping

## 21.1. Component Should Own Tokens

Page should not repeat low-level styling.

Good:

```blade
<x-ui.card variant="interactive">
  ...
</x-ui.card>
```

Bad:

```blade
<div class="rounded-[17px] shadow-[0_7px_20px_rgba(0,0,0,.12)] border-[#ddd]">
```

## 21.2. Button Example

```blade
<x-ui.button variant="primary" size="lg">
  Gửi xác thực
</x-ui.button>
```

Maps to:

```txt
button.primary.bg
button.primary.text
button.height.lg
button.radius
button.font
motion.button.hover
```

## 21.3. Badge Example

```blade
<x-ui.badge variant="warning">
  Đang chờ duyệt
</x-ui.badge>
```

Maps to:

```txt
color.status.warning.bg
color.status.warning.text
badge.radius
badge.font
```

## 21.4. Status Mapper

Business status should map to UI tokens centrally.

Example:

```txt
verification.status = needs_more_information
→ badge.warning
→ color.status.warning
→ copy: Cần bổ sung
```

Do not map status manually in every Blade page. Đó là cách “Đang chờ duyệt” có 5 màu khác nhau trong cùng app, một thành tựu buồn.

---

# 22. Token Governance

## 22.1. Adding a Token

Before adding a token:

```txt
1. Check if existing token already solves it.
2. Define purpose.
3. Define layer: primitive / semantic / component / state.
4. Define code mapping.
5. Define accessibility impact.
6. Update this file.
7. Update Tailwind/CSS variable if needed.
8. Update component docs if used by component.
```

## 22.2. Removing a Token

Before removing:

```txt
1. Find all usages.
2. Replace with approved token.
3. Test UI.
4. Update docs.
5. Remove from config.
```

## 22.3. Token Review Questions

```txt
- Is this token reusable?
- Is it semantic?
- Does it follow naming convention?
- Does it preserve UEConnect visual identity?
- Does it pass accessibility needs?
- Does it create unnecessary variant?
- Does it map to Tailwind/Blade cleanly?
```

## 22.4. Forbidden Token Types

Do not create:

```txt
color.random-blue
spacing.page-special-huy
radius.almost-rounded
shadow.cool-card
button.home-special
gradient.tinder-ish
```

Rất tiếc, “tinder-ish” không phải token, nó là lời kêu cứu.

---

# 23. Design-to-Code Workflow

## 23.1. Design Phase

Designer should use:

```txt
documented color tokens
documented type scale
documented spacing scale
component variants
state mapping
```

## 23.2. Implementation Phase

Frontend should:

```txt
use Blade components first
use Tailwind tokens
avoid arbitrary values
centralize variant classes
map business status through helper
```

## 23.3. QA Phase

QA should check:

```txt
visual consistency
accessibility
responsive behavior
state correctness
token misuse
hardcoded styling
```

## 23.4. AI-assisted UI Generation

When asking AI to generate UI, include:

```txt
Use UEConnect design tokens:
- HCMUE Blue #124874 as brand primary.
- Blue-only gradients.
- Be Vietnam Pro typography.
- Rounded 2xl cards.
- Soft shadows.
- Mobile-first PWA layout.
- No dating-app red/pink/orange gradient.
- Use semantic status colors.
- Use accessible focus and contrast.
```

---

# 24. Token QA Checklist

Before approving a page/component:

```txt
[ ] Uses approved color tokens.
[ ] Uses approved typography tokens.
[ ] Uses approved spacing scale.
[ ] Uses approved radius.
[ ] Uses approved shadow/elevation.
[ ] Uses approved border tokens.
[ ] Uses approved icon sizes.
[ ] Uses semantic status colors.
[ ] Uses blue-only gradient if gradient exists.
[ ] Does not use arbitrary hex.
[ ] Does not use arbitrary radius.
[ ] Does not use arbitrary shadow.
[ ] Does not use dating-like colors/icons.
[ ] Passes contrast.
[ ] Works responsively.
[ ] Motion uses approved duration/easing.
[ ] Component variants match documentation.
[ ] Business state maps to correct UI token.
```

---

# 25. Common Anti-patterns

Do not:

```txt
- Hardcode random hex in pages.
- Use arbitrary Tailwind values everywhere.
- Create one-off card shadows.
- Use different blue for every screen.
- Use red/pink/orange gradient.
- Use body text smaller than token scale.
- Use inconsistent badge colors.
- Use random z-index.
- Use custom radius for one card.
- Make admin UI tiny to fit more data.
- Use hover-only visual state.
- Use color-only status.
- Duplicate status mapping in many files.
- Add token without documenting.
```

---

# 26. Final Rule

Design tokens are the contract between brand, design and code.

Before any UI ships, it must answer:

```txt
1. Which tokens does it use?
2. Are those tokens documented?
3. Are those tokens semantic?
4. Do they match UEConnect brand?
5. Do they pass accessibility?
6. Do they work in responsive layouts?
7. Are states mapped correctly?
8. Can another developer reuse this without guessing?
```

Nếu không trả lời được, UI đó chưa thuộc design system. Nó chỉ là một màn hình đang mặc tạm vài class Tailwind và hy vọng không ai soi.

```
```
