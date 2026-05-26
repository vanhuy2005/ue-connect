---
title: "Color System"
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
next:
  - "04-gradient-policy.md"
  - "08-shadow-elevation-system.md"
  - "09-border-system.md"
  - "14-interaction-states.md"
  - "19-design-token-documentation.md"
related:
  - "examples/preview.html"
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
---

# 03. Color System

## 1. Purpose

File này định nghĩa hệ màu chính thức cho UEConnect.

Mục tiêu:

- Chốt màu brand chính dựa trên HCMUE.
- Xây dựng neutral color system đủ dùng cho social product.
- Định nghĩa semantic colors cho success, warning, danger, info, mentor.
- Quy định cách dùng màu trong UI để tránh chói, lạm dụng brand color hoặc nhìn AI-generated.
- Hỗ trợ implement bằng TailwindCSS, CSS variables và Laravel Blade.
- Chuẩn bị nền tảng để sau này có thể mở rộng dark mode mà không phải phá lại toàn bộ UI.

File này không mô tả chi tiết gradient. Gradient được tách riêng trong:

```txt
04-gradient-policy.md
````

File này cũng không mô tả shadow, border, component state chi tiết. Các phần đó nằm ở:

```txt
08-shadow-elevation-system.md
09-border-system.md
14-interaction-states.md
```

---

## 2. Core Color Decision

UEConnect dùng chiến lược màu:

```txt
Neutral-first
+ restrained HCMUE brand blue
+ calm semantic colors
+ rare gradient moments
```

Tỉ lệ màu khuyến nghị trên product screen thông thường:

```txt
Neutral surfaces: 80–90%
Text colors: 8–12%
Brand blue: 5–10%
Semantic colors: <3%
Gradient: 0–5%, chỉ dùng khi có lý do rõ
```

Nói ngắn gọn:

```txt
UI mặc định phải sạch, sáng, trung tính.
Brand blue chỉ dùng để định hướng, xác thực hoặc nhấn hành động chính.
```

Không được dùng màu theo kiểu:

```txt
Thấy trống thì thêm xanh.
Thấy nhạt thì thêm gradient.
Thấy social thì thêm đỏ/hồng.
Thấy cần premium thì thêm shadow + màu đậm.
```

Đó là công thức nấu món “AI-generated interface”, nhìn thì có vẻ nhiều công sức, dùng thật thì mệt mắt.

---

## 3. Color Philosophy

## 3.1. Neutral-first

UEConnect là social platform. User sẽ đọc feed, comment, profile, message trong thời gian dài.

Vì vậy:

* Nền phải nhẹ.
* Text phải rõ.
* Border phải tinh tế.
* Màu brand không được tranh spotlight với content.
* Post, comment, message phải dễ đọc hơn là “đẹp nổi bật”.

Neutral-first giúp UI:

* Ít mỏi mắt.
* Dễ mở rộng.
* Dễ responsive.
* Dễ làm dark mode sau này.
* Ít bị lỗi thẩm mỹ khi data thật nhiều.

## 3.2. Brand restraint

Màu HCMUE `#124874` là màu nhận diện chính, nhưng không phải mọi thứ đều cần màu đó.

Brand blue dùng cho:

```txt
Logo / brand mark
Active navigation
Primary CTA
Verified UEer badge
Focus state
Important link
Official verification area
```

Không dùng brand blue cho:

```txt
Toàn bộ background feed
Mọi icon
Mọi heading
Mọi card border
Mọi button
Mọi badge
Mọi section title
Mọi hover state
```

Nếu màn hình nhìn như bị nhuộm xanh, đó không phải brand consistency. Đó là giao diện bị bắt mặc đồng phục 24/7.

---

## 4. Core Brand Colors

## 4.1. HCMUE Cerulean

Màu chính thức:

```css
--ue-brand: #124874;
```

Thông số:

```txt
HEX:  #124874
RGB:  18, 72, 116
CMYK: C84 M38 Y0 K55
```

Tên dùng trong docs:

```txt
HCMUE Cerulean
UE Brand Blue
Verified Blue
```

Vai trò:

* Primary brand anchor.
* Trust signal.
* Verified identity.
* Active navigation.
* Primary CTA.
* Focus state.

## 4.2. Brand Hover / Active

```css
--ue-brand-hover: #0E3A60;
--ue-brand-active: #0A2B49;
```

Dùng cho:

* Hover primary button.
* Active primary button.
* Pressed states.
* Deep brand text nếu cần.

Không tự tạo màu hover bằng cách giảm opacity trên mọi nền. Với button chính, nên dùng màu hover cụ thể để contrast ổn định.

## 4.3. Brand Soft

```css
--ue-brand-soft: #EEF7FF;
--ue-brand-soft-hover: #D9ECFF;
--ue-brand-border: rgba(18, 72, 116, 0.16);
--ue-brand-tint: rgba(18, 72, 116, 0.08);
```

Dùng cho:

* Active nav background.
* Verified badge background.
* Info panel nhẹ.
* Account verification card.
* Selected chips.
* Empty state illustration area.

Không dùng làm background toàn page.

## 4.4. HCMUE Heritage Red

```css
--ue-heritage-red: #CF373D;
```

Vai trò:

* Heritage color từ HCMUE identity.
* Có thể dùng trong logo chính thức nếu asset yêu cầu.
* Có thể dùng cho danger/error nếu phù hợp.

Không dùng cho:

* Primary CTA.
* Primary gradient.
* Discovery action.
* Feed interaction.
* Button chính.
* Active nav.
* Social energy.

UEConnect không dùng đỏ/hồng/cam để tạo cảm giác social. Ta đã thử, nó nhìn như Tinder bị bắt nhập học. Không lặp lại.

---

## 5. Neutral Color Scale

Neutral scale là nền sống còn của UEConnect.

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

## 5.1. Neutral Usage Table

| Token         | HEX       | Usage                                      |
| ------------- | --------- | ------------------------------------------ |
| `neutral-0`   | `#FFFFFF` | Card, modal, post surface, message surface |
| `neutral-25`  | `#FCFCFD` | Subtle hover over white                    |
| `neutral-50`  | `#FAFAFA` | Main app background                        |
| `neutral-100` | `#F8FAFC` | Sidebar, subtle panel, input disabled bg   |
| `neutral-150` | `#F1F5F9` | Hover background, selected neutral tab     |
| `neutral-200` | `#E4E6EB` | Default border, divider                    |
| `neutral-300` | `#CBD5E1` | Strong border, input hover border          |
| `neutral-400` | `#94A3B8` | Disabled text/icon                         |
| `neutral-500` | `#64748B` | Metadata, muted text                       |
| `neutral-600` | `#475569` | Secondary text                             |
| `neutral-700` | `#334155` | Strong secondary text                      |
| `neutral-800` | `#1E293B` | Heading alternative                        |
| `neutral-900` | `#0F172A` | Primary text                               |
| `neutral-950` | `#020617` | High contrast text, rarely used            |

## 5.2. Neutral Rules

Nên dùng:

```txt
neutral-50 cho app background
neutral-0 cho post/card/modal
neutral-200 cho border/divider
neutral-500 cho metadata
neutral-900 cho primary text
```

Không nên:

```txt
Dùng pure black #000 cho body text
Dùng quá nhiều gray gần nhau không có lý do
Dùng neutral-300 làm border mặc định quá nặng
Dùng neutral-100 cho mọi card khiến UI bị đục
```

---

## 6. Brand Blue Scale

Brand blue scale mở rộng từ `#124874`.

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

## 6.1. Blue Usage Table

| Token      | HEX       | Usage                                     |
| ---------- | --------- | ----------------------------------------- |
| `blue-50`  | `#EEF7FF` | Active nav bg, verified bg, selected chip |
| `blue-100` | `#D9ECFF` | Soft hover tint                           |
| `blue-200` | `#B7DDFF` | Illustration tint, rarely                 |
| `blue-300` | `#83C5FF` | Decorative accent, rare                   |
| `blue-400` | `#4DA3FF` | Highlight accent, rare                    |
| `blue-500` | `#2178D4` | Optional link/accent                      |
| `blue-600` | `#124874` | Primary brand                             |
| `blue-700` | `#0E3A60` | Primary hover                             |
| `blue-800` | `#0A2B49` | Primary active                            |
| `blue-900` | `#071F35` | Deep brand background, rare               |

## 6.2. Blue Rules

* `blue-600` là màu nhận diện chính.
* `blue-50` là màu nền soft được dùng thường xuyên hơn `blue-600`.
* `blue-500` có thể dùng cho link nếu `blue-600` quá đậm trong context text.
* `blue-300/400` chỉ dùng decorative rất hạn chế.
* Không dùng blue scale để tạo cầu vồng xanh trên mọi component.

---

## 7. Semantic Colors

Semantic colors dùng để truyền trạng thái, không phải để trang trí.

## 7.1. Success

```css
--success: #20C997;
--success-text: #0F7B5C;
--success-bg: rgba(32, 201, 151, 0.12);
--success-border: rgba(32, 201, 151, 0.24);
```

Dùng cho:

* Account approved.
* Save success.
* Message sent.
* Verification accepted.
* Form success.

Không dùng cho decoration.

## 7.2. Warning

```css
--warning: #F59E0B;
--warning-text: #92400E;
--warning-bg: rgba(245, 158, 11, 0.12);
--warning-border: rgba(245, 158, 11, 0.24);
```

Dùng cho:

* Account pending.
* Need more information.
* Unsaved changes.
* Content warning.
* Copyright warning.

## 7.3. Danger

```css
--danger: #C62828;
--danger-text: #991B1B;
--danger-bg: rgba(198, 40, 40, 0.10);
--danger-border: rgba(198, 40, 40, 0.22);
```

Dùng cho:

* Error.
* Delete.
* Report.
* Block.
* Suspended account.
* Rejected verification.
* Destructive confirmation.

## 7.4. Info

```css
--info: #2178D4;
--info-text: #124874;
--info-bg: rgba(18, 72, 116, 0.10);
--info-border: rgba(18, 72, 116, 0.18);
```

Dùng cho:

* Verification explanation.
* Helpful hints.
* System info.
* Account status explanation.

## 7.5. Mentor

```css
--mentor: #7C5CFF;
--mentor-text: #5B3FD6;
--mentor-bg: rgba(124, 92, 255, 0.10);
--mentor-border: rgba(124, 92, 255, 0.20);
```

Dùng cho:

* Mentor badge.
* Mentor module highlight.
* Mentor request status.
* Career/growth hint.

Không dùng quá nhiều purple trong app. Mentor là feature chính, nhưng không phải lý do để biến UI thành festival màu tím.

---

## 8. Role Colors

Role colors giúp phân biệt user type nhưng phải cực kỳ tiết chế.

| Role             | Text      | Background              | Usage                 |
| ---------------- | --------- | ----------------------- | --------------------- |
| Verified UEer    | `#124874` | `rgba(18,72,116,0.08)`  | Verified badge        |
| Student          | `#124874` | `#EEF7FF`               | Student context, rare |
| Alumni           | `#7C5CFF` | `rgba(124,92,255,0.10)` | Alumni badge          |
| Mentor           | `#5B3FD6` | `rgba(124,92,255,0.10)` | Mentor badge          |
| Club / Community | `#0F7B5C` | `rgba(32,201,151,0.12)` | Community badge       |
| Admin / Official | `#334155` | `#F1F5F9`               | Admin/system badge    |

Rules:

* Role colors dùng cho badge nhỏ, không dùng cho page background.
* Không dùng quá 2 role color nổi bật trong cùng một card.
* Role badge phải có text, không chỉ màu.
* Nếu màu làm UI rối, ưu tiên neutral badge + icon/text.

---

## 9. Feature Accent Colors

Feature accent chỉ dùng để hỗ trợ nhận diện module, không phải theme riêng toàn trang.

| Feature          | Accent                    | Usage                                  |
| ---------------- | ------------------------- | -------------------------------------- |
| Home Feed        | Neutral + brand blue      | Active state, composer CTA             |
| Discovery        | Brand blue + soft blue    | Greeting CTA, verified context         |
| Messaging        | Brand blue for own bubble | Own message, active conversation       |
| Mentor           | Mentor purple             | Badge, request status, small highlight |
| Community / Club | Success green             | Badge, joined state                    |
| Safety           | Danger red                | Report, block, warning                 |
| Admin            | Neutral + brand blue      | Dashboard state, verification          |
| Notification     | Brand/info                | Unread indicator                       |

Không tạo mỗi feature một palette riêng quá mạnh. Làm vậy app sẽ giống năm app khác nhau bị nhét chung vào một repo, một cảnh tượng quá quen với đồ án nhóm.

---

## 10. Surface Colors

```css
--ue-bg: #FAFAFA;
--ue-surface: #FFFFFF;
--ue-surface-subtle: #F8FAFC;
--ue-surface-hover: #F1F5F9;
--ue-surface-pressed: #E4E6EB;
--ue-surface-disabled: #F8FAFC;
```

## 10.1. Usage

| Token                 | Usage                                    |
| --------------------- | ---------------------------------------- |
| `ue-bg`               | App background                           |
| `ue-surface`          | Post, card, modal, input                 |
| `ue-surface-subtle`   | Sidebar, right panel, info section       |
| `ue-surface-hover`    | Hover item, nav hover, icon button hover |
| `ue-surface-pressed`  | Pressed neutral action                   |
| `ue-surface-disabled` | Disabled field/card                      |

## 10.2. Rules

* Feed background nên là `ue-bg` hoặc `ue-surface`.
* Post item thường dùng `ue-surface`.
* Sidebar có thể dùng `ue-surface`.
* Right panel có thể dùng `ue-bg` hoặc `ue-surface-subtle`.
* Không dùng tinted blue surface làm default feed.

---

## 11. Text Colors

```css
--ue-text: #0F172A;
--ue-text-secondary: #475569;
--ue-text-muted: #64748B;
--ue-text-disabled: #94A3B8;
--ue-text-inverse: #FFFFFF;
```

## 11.1. Text Usage

| Token               | Usage                             |
| ------------------- | --------------------------------- |
| `ue-text`           | Body chính, heading, post content |
| `ue-text-secondary` | Supporting text, panel copy       |
| `ue-text-muted`     | Metadata, time, helper text       |
| `ue-text-disabled`  | Disabled, placeholder             |
| `ue-text-inverse`   | Text trên nền brand/dark          |

## 11.2. Rules

* Body post dùng `ue-text`.
* Metadata dùng `ue-text-muted`.
* Placeholder dùng `ue-text-disabled`.
* Không dùng brand blue cho body text dài.
* Không dùng gray quá nhạt cho text quan trọng.
* Không dùng text màu cyan trên nền trắng.

---

## 12. Border Colors

```css
--ue-border: #E4E6EB;
--ue-border-strong: #CBD5E1;
--ue-border-subtle: #F1F5F9;
--ue-border-brand: rgba(18, 72, 116, 0.16);
--ue-border-danger: rgba(198, 40, 40, 0.22);
```

## 12.1. Border Usage

| Token              | Usage                                       |
| ------------------ | ------------------------------------------- |
| `ue-border`        | Default card/input/divider                  |
| `ue-border-strong` | Hover input, selected neutral, table border |
| `ue-border-subtle` | Very light divider                          |
| `ue-border-brand`  | Verified badge, selected brand chip         |
| `ue-border-danger` | Error input, danger alert                   |

Rules:

* Dùng border trước shadow.
* Border mặc định không quá đậm.
* Không dùng brand border cho mọi card.
* Không dùng red border nếu không có error/danger.

---

## 13. Interaction Colors

## 13.1. Hover

```css
--hover-neutral: #F1F5F9;
--hover-brand-soft: #D9ECFF;
--hover-danger-soft: rgba(198, 40, 40, 0.08);
```

## 13.2. Focus

```css
--focus-ring-brand: 0 0 0 3px rgba(18, 72, 116, 0.16);
--focus-ring-danger: 0 0 0 3px rgba(198, 40, 40, 0.12);
```

## 13.3. Active

```css
--active-brand: #0A2B49;
--active-neutral: #E4E6EB;
```

## 13.4. Disabled

```css
--disabled-bg: #F8FAFC;
--disabled-text: #94A3B8;
--disabled-border: #E4E6EB;
```

Rules:

* Hover phải tinh tế.
* Focus phải rõ hơn hover.
* Disabled không chỉ giảm opacity nếu contrast bị tệ.
* Active state phải cho cảm giác phản hồi.
* Không dùng màu semantic cho hover nếu không phải semantic action.

---

## 14. Component Color Mapping

## 14.1. Button

### Primary Button

```css
.button-primary {
  background: var(--ue-brand);
  border-color: var(--ue-brand);
  color: #FFFFFF;
}

.button-primary:hover {
  background: var(--ue-brand-hover);
  border-color: var(--ue-brand-hover);
}

.button-primary:active {
  background: var(--ue-brand-active);
  border-color: var(--ue-brand-active);
}
```

### Secondary Button

```css
.button-secondary {
  background: var(--ue-surface);
  border-color: var(--ue-border);
  color: var(--ue-text);
}

.button-secondary:hover {
  background: var(--ue-surface-subtle);
  border-color: var(--ue-border-strong);
}
```

### Ghost Button

```css
.button-ghost {
  background: transparent;
  color: var(--ue-text);
}

.button-ghost:hover {
  background: var(--ue-surface-hover);
}
```

### Danger Button

```css
.button-danger {
  background: var(--danger);
  border-color: var(--danger);
  color: #FFFFFF;
}
```

Rules:

* Một screen chỉ nên có một primary action chính.
* Không dùng gradient button trong feed/product UI.
* Danger chỉ dùng cho destructive action.
* Secondary không được dùng brand background.

---

## 14.2. Navigation

```css
.nav-item {
  color: var(--ue-text-secondary);
}

.nav-item:hover {
  background: var(--ue-surface-hover);
  color: var(--ue-text);
}

.nav-item[aria-current="page"] {
  color: var(--ue-brand);
  background: var(--ue-brand-soft);
}
```

Rules:

* Active nav dùng brand blue + soft blue background.
* Không dùng gradient active nav.
* Không dùng red active nav.
* Icon inactive dùng neutral, không brand.

---

## 14.3. Verified Badge

```css
.verified-badge {
  color: var(--ue-brand);
  background: rgba(18, 72, 116, 0.08);
  border: 1px solid rgba(18, 72, 116, 0.14);
}
```

Rules:

* Badge phải nhỏ.
* Không làm badge chiếm spotlight hơn tên user.
* Badge cần text hoặc tooltip nếu icon-only.

---

## 14.4. Post Card

```css
.post {
  background: var(--ue-surface);
  border-bottom: 1px solid var(--ue-border);
  color: var(--ue-text);
}

.post:hover {
  background: #FCFCFD;
}
```

Rules:

* Post không có brand border mặc định.
* Post không có gradient.
* Post không có shadow nặng.
* Action icon dùng neutral, active có thể brand.

---

## 14.5. Message Bubble

```css
.message-own {
  background: var(--ue-brand);
  color: #FFFFFF;
}

.message-other {
  background: var(--ue-surface-hover);
  color: var(--ue-text);
}
```

Rules:

* Own bubble có thể dùng brand blue.
* Other bubble dùng neutral.
* Không dùng gradient bubble.
* Error message dùng danger text nhỏ, không tô cả bubble đỏ nếu không cần.

---

## 14.6. Input

```css
.input {
  background: var(--ue-surface);
  border: 1px solid var(--ue-border);
  color: var(--ue-text);
}

.input:hover {
  border-color: var(--ue-border-strong);
}

.input:focus {
  border-color: var(--ue-brand);
  box-shadow: var(--focus-ring-brand);
}

.input[aria-invalid="true"] {
  border-color: var(--danger);
  box-shadow: var(--focus-ring-danger);
}
```

Rules:

* Error cần text, không chỉ đổi màu.
* Placeholder không thay thế label.
* Focus brand blue được phép.

---

## 15. Page-level Color Usage

## 15.1. Onboarding

Có thể dùng brand rõ hơn:

* Brand mark.
* Brand blue CTA.
* Brand soft panel.
* Rare brand gradient nếu đúng policy.

Nhưng vẫn không biến toàn bộ onboarding thành poster gradient.

## 15.2. Home Feed

Nên dùng:

* White/near-white.
* Neutral border.
* Brand blue cho active nav, primary composer CTA, verified badge.

Không dùng:

* Full blue background.
* Gradient section.
* Blue card header.

## 15.3. Discovery

Nên dùng:

* Neutral card.
* Brand CTA `Gửi lời chào`.
* Verified badge.
* Interest chips nhẹ.

Không dùng:

* Pink/red/orange dating palette.
* Swipe/match visual language.
* Quá nhiều highlight màu.

## 15.4. Messaging

Nên dùng:

* Neutral inbox.
* Own bubble brand blue.
* Other bubble neutral.
* Danger only for report/block.

Không dùng:

* Gradient bubble.
* Mỗi conversation một màu.
* Blue toàn màn hình.

## 15.5. Mentor

Nên dùng:

* Mentor purple cho badge nhỏ.
* Brand blue cho primary CTA.
* Neutral surface.

Không dùng:

* Purple toàn page.
* Career dashboard quá corporate.
* Gradient mentor cards.

## 15.6. Admin

Nên dùng:

* Neutral surfaces.
* Brand blue cho verification/action.
* Semantic colors rõ.
* Danger cho destructive action.

Không dùng:

* Social playful color.
* Decoration thừa.
* Gradient.

---

## 16. Data Visualization Colors

Nếu sau này có admin dashboard/chart:

```css
--chart-blue: #2178D4;
--chart-green: #20C997;
--chart-yellow: #F59E0B;
--chart-red: #C62828;
--chart-purple: #7C5CFF;
--chart-gray: #64748B;
```

Rules:

* Chart không dùng quá 5 màu cùng lúc.
* Luôn có label, không chỉ dựa vào màu.
* Danger chart không dùng red nếu không phải cảnh báo.
* Admin dashboard không biến thành cầu vồng số liệu. Chart nhiều màu không làm dữ liệu thông minh hơn, tiếc là vậy.

---

## 17. Dark Mode Preparation

Dark mode chưa làm ngay, nhưng token phải sẵn sàng.

Không hard-code:

```css
color: #0F172A;
background: #FFFFFF;
```

Ưu tiên:

```css
color: var(--ue-text);
background: var(--ue-surface);
```

Gợi ý dark token sau này:

```css
[data-theme="dark"] {
  --ue-bg: #020617;
  --ue-surface: #0F172A;
  --ue-surface-subtle: #111827;
  --ue-surface-hover: #1E293B;

  --ue-text: #F8FAFC;
  --ue-text-secondary: #CBD5E1;
  --ue-text-muted: #94A3B8;
  --ue-text-disabled: #64748B;

  --ue-border: #1E293B;
  --ue-border-strong: #334155;

  --ue-brand-soft: rgba(18, 72, 116, 0.22);
}
```

Rules:

* Không cần implement dark mode ngay.
* Nhưng class/token phải không chặn dark mode tương lai.
* Tránh hard-code màu trong Blade component.

---

## 18. Accessibility Rules

## 18.1. Contrast

* Text chính phải đủ contrast với background.
* Text trắng trên `#124874` được phép.
* Text brand blue trên white phải kiểm tra theo kích thước.
* Text muted không dùng cho nội dung quan trọng.
* Danger/success/warning phải có text + icon, không chỉ màu.

## 18.2. Color Is Not the Only Signal

Không truyền ý nghĩa chỉ bằng màu.

Sai:

```txt
Ô màu đỏ nghĩa là lỗi.
```

Đúng:

```txt
Ô có border đỏ + text lỗi + icon cảnh báo.
```

## 18.3. Focus Visibility

Focus state phải rõ:

```css
:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(18, 72, 116, 0.16);
}
```

Không xóa outline nếu chưa có thay thế. Việc xóa outline để “đẹp hơn” là cách con người tuyên chiến với keyboard users.

---

## 19. TailwindCSS Mapping

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        ue: {
          brand: "#124874",
          "brand-hover": "#0E3A60",
          "brand-active": "#0A2B49",
          "brand-soft": "#EEF7FF",
          "heritage-red": "#CF373D",

          bg: "#FAFAFA",
          surface: "#FFFFFF",
          "surface-subtle": "#F8FAFC",
          "surface-hover": "#F1F5F9",

          text: "#0F172A",
          "text-secondary": "#475569",
          "text-muted": "#64748B",
          "text-disabled": "#94A3B8",

          border: "#E4E6EB",
          "border-strong": "#CBD5E1",
        },

        success: {
          DEFAULT: "#20C997",
          text: "#0F7B5C",
          bg: "rgba(32, 201, 151, 0.12)",
        },

        warning: {
          DEFAULT: "#F59E0B",
          text: "#92400E",
          bg: "rgba(245, 158, 11, 0.12)",
        },

        danger: {
          DEFAULT: "#C62828",
          text: "#991B1B",
          bg: "rgba(198, 40, 40, 0.10)",
        },

        mentor: {
          DEFAULT: "#7C5CFF",
          text: "#5B3FD6",
          bg: "rgba(124, 92, 255, 0.10)",
        },
      },
    },
  },
}
```

---

## 20. CSS Variables

```css
:root {
  /* Brand */
  --ue-brand: #124874;
  --ue-brand-hover: #0E3A60;
  --ue-brand-active: #0A2B49;
  --ue-brand-soft: #EEF7FF;
  --ue-brand-soft-hover: #D9ECFF;
  --ue-brand-border: rgba(18, 72, 116, 0.16);
  --ue-brand-tint: rgba(18, 72, 116, 0.08);
  --ue-heritage-red: #CF373D;

  /* Surface */
  --ue-bg: #FAFAFA;
  --ue-surface: #FFFFFF;
  --ue-surface-subtle: #F8FAFC;
  --ue-surface-hover: #F1F5F9;
  --ue-surface-pressed: #E4E6EB;
  --ue-surface-disabled: #F8FAFC;

  /* Text */
  --ue-text: #0F172A;
  --ue-text-secondary: #475569;
  --ue-text-muted: #64748B;
  --ue-text-disabled: #94A3B8;
  --ue-text-inverse: #FFFFFF;

  /* Border */
  --ue-border: #E4E6EB;
  --ue-border-strong: #CBD5E1;
  --ue-border-subtle: #F1F5F9;
  --ue-border-brand: rgba(18, 72, 116, 0.16);

  /* Semantic */
  --success: #20C997;
  --success-text: #0F7B5C;
  --success-bg: rgba(32, 201, 151, 0.12);
  --success-border: rgba(32, 201, 151, 0.24);

  --warning: #F59E0B;
  --warning-text: #92400E;
  --warning-bg: rgba(245, 158, 11, 0.12);
  --warning-border: rgba(245, 158, 11, 0.24);

  --danger: #C62828;
  --danger-text: #991B1B;
  --danger-bg: rgba(198, 40, 40, 0.10);
  --danger-border: rgba(198, 40, 40, 0.22);

  --info: #2178D4;
  --info-text: #124874;
  --info-bg: rgba(18, 72, 116, 0.10);
  --info-border: rgba(18, 72, 116, 0.18);

  --mentor: #7C5CFF;
  --mentor-text: #5B3FD6;
  --mentor-bg: rgba(124, 92, 255, 0.10);
  --mentor-border: rgba(124, 92, 255, 0.20);

  /* Interaction */
  --focus-ring-brand: 0 0 0 3px rgba(18, 72, 116, 0.16);
  --focus-ring-danger: 0 0 0 3px rgba(198, 40, 40, 0.12);
}
```

---

## 21. Blade / Tailwind Examples

## 21.1. Primary Button

```blade
<button
  type="button"
  class="inline-flex h-10 items-center justify-center rounded-md bg-ue-brand px-4 text-sm font-semibold text-white transition hover:bg-ue-brand-hover active:bg-ue-brand-active focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[rgba(18,72,116,0.16)]"
>
  Tạo bài viết
</button>
```

## 21.2. Active Navigation

```blade
<a
  href="{{ route('feed') }}"
  aria-current="page"
  class="inline-flex h-12 items-center gap-3 rounded-full px-3 text-sm font-semibold text-ue-brand bg-ue-brand-soft"
>
  <x-icon.home class="h-5 w-5" />
  <span>Trang chủ</span>
</a>
```

## 21.3. Verified Badge

```blade
<span class="inline-flex items-center gap-1 rounded-full border border-[rgba(18,72,116,0.14)] bg-[rgba(18,72,116,0.08)] px-2 py-0.5 text-xs font-semibold text-ue-brand">
  <x-icon.badge-check class="h-3.5 w-3.5" />
  Đã xác thực UEer
</span>
```

## 21.4. Error Input

```blade
<div>
  <label class="mb-1 block text-sm font-semibold text-ue-text">
    Mã sinh viên
  </label>

  <input
    aria-invalid="true"
    aria-describedby="student-id-error"
    class="h-11 w-full rounded-md border border-danger bg-white px-3 text-sm text-ue-text focus:outline-none focus:ring-4 focus:ring-[rgba(198,40,40,0.12)]"
  />

  <p id="student-id-error" class="mt-1 text-xs font-medium text-danger-text">
    Mã sinh viên này đã được sử dụng.
  </p>
</div>
```

---

## 22. Color Anti-patterns

## 22.1. Too Much Brand Blue

Sai:

```css
.feed {
  background: #124874;
}

.post-card {
  border: 1px solid #124874;
}

.post-title {
  color: #124874;
}
```

Đúng:

```css
.feed {
  background: var(--ue-bg);
}

.post-card {
  border: 1px solid var(--ue-border);
}

.verified-badge {
  color: var(--ue-brand);
}
```

## 22.2. Dating Palette

Không dùng:

```css
--primary: #FD267A;
--secondary: #FF6036;
```

UEConnect không phải dating app. Không cần giả vờ “vui” bằng màu hồng/cam.

## 22.3. Random Accent Colors

Không dùng mỗi feature một màu quá mạnh:

```txt
Feed xanh
Discovery tím
Messaging cam
Mentor vàng
Community đỏ
Settings lục
```

Đây không phải hệ thống màu. Đây là bảng màu của một app chưa được ai ngăn lại.

## 22.4. Low Contrast Text

Sai:

```css
.meta {
  color: #CBD5E1;
}
```

trên nền trắng.

Đúng:

```css
.meta {
  color: var(--ue-text-muted);
}
```

---

## 23. Color QA Checklist

Dùng checklist này khi review bất kỳ screen nào.

## 23.1. Brand Usage

* [ ] Brand blue `#124874` có được dùng đúng chỗ không?
* [ ] Brand blue có chiếm dưới khoảng 5–10% visual weight không?
* [ ] Có tránh dùng brand blue cho mọi icon/card/heading không?
* [ ] Active nav và primary CTA có nhất quán không?

## 23.2. Neutral System

* [ ] Background có dùng neutral thay vì brand color không?
* [ ] Card/post/modal có surface rõ không?
* [ ] Border có đủ nhẹ không?
* [ ] Metadata có dùng muted text đủ contrast không?

## 23.3. Semantic Colors

* [ ] Success/warning/danger có dùng đúng ngữ nghĩa không?
* [ ] Error có text, không chỉ màu không?
* [ ] Danger có chỉ dùng cho hành động nguy hiểm không?
* [ ] Mentor purple có bị dùng quá tay không?

## 23.4. Accessibility

* [ ] Text contrast đủ không?
* [ ] Focus ring rõ không?
* [ ] Không truyền ý nghĩa chỉ bằng màu không?
* [ ] Disabled state vẫn đọc được không?
* [ ] Mobile touch state rõ không?

## 23.5. Product Fit

* [ ] Feed có sạch và content-first không?
* [ ] Discovery có tránh dating vibe không?
* [ ] Messaging có dễ đọc không?
* [ ] Admin có nghiêm túc, không màu mè không?
* [ ] UI có tránh cảm giác AI-generated không?

---

## 24. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm đoạn này:

```txt
Use UEConnect official color system.
The UI must be neutral-first with white/near-white surfaces and restrained HCMUE blue #124874.
Use #124874 only for logo, active navigation, primary CTA, verified badge, focus ring, and official verification areas.
Do not use full-page gradients in product screens.
Do not use red/pink/orange as primary social colors.
Do not make every icon, card, heading, or border blue.
Use semantic colors only for actual status: success, warning, danger, info, mentor.
Keep feed, profile, messaging, and discovery calm, readable, and content-first.
Avoid AI-generated UI smell: excessive gradients, too many colors, heavy shadows, low contrast text, and random accent colors.
```

---

## 25. Final Decision

Color system chính thức của UEConnect:

```txt
Primary brand: #124874
Default background: #FAFAFA
Default surface: #FFFFFF
Default text: #0F172A
Default border: #E4E6EB
Brand strategy: restrained accent
UI strategy: neutral-first social product
Gradient strategy: rare brand moment only
```

Câu chốt:

```txt
Màu của UEConnect phải làm product đáng tin và dễ dùng hơn, không phải chứng minh rằng chúng ta biết dùng linear-gradient.
```
