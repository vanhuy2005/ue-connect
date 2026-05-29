---
title: "Shadow & Elevation System"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Frontend Team"
depends_on:
  - "00-design-foundation-roadmap.md"
  - "03-color-system.md"
  - "06-spacing-system.md"
  - "07-radius-system.md"
next:
  - "09-border-system.md"
  - "12-component-primitives.md"
  - "14-interaction-states.md"
  - "19-design-token-documentation.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/messaging.md"
  - "page-specs/admin-dashboard.md"
  - "21-social-interaction-patterns.md"
---

# 08. Shadow & Elevation System

## 1. Purpose

File này định nghĩa shadow và elevation system chính thức cho UEConnect.

Mục tiêu:

- Chốt cách tạo depth trong UI.
- Tránh lạm dụng shadow khiến UI nhìn như template AI hoặc Dribbble mockup.
- Ưu tiên border/divider cho social feed.
- Dùng elevation có chủ đích cho modal, dropdown, floating element.
- Đảm bảo product UI nhẹ, sạch, content-first.
- Hỗ trợ implement bằng TailwindCSS và CSS variables.

Shadow không phải mỹ phẩm. Shadow là tín hiệu phân lớp. Dùng sai, UI sẽ nhìn như mọi card đang cố bay khỏi màn hình, một hành vi khá thiếu trách nhiệm với người dùng.

---

## 2. Core Decision

UEConnect dùng elevation strategy:

```txt
Border-first
+ subtle shadow
+ elevation only when layering matters

Nghĩa là:

Feed post dùng divider/border nhiều hơn shadow.
Card thường dùng border nhẹ.
Shadow dùng cho dropdown, modal, floating panel.
Không dùng shadow nặng cho mọi card.
Admin UI ưu tiên border và table structure.
3. Elevation Principles
3.1. Border before shadow

Trong product UI hằng ngày:

Default separation = border/divider
Special layering = shadow

Dùng border cho:

Feed item.
Card.
Input.
Sidebar.
Right panel.
Table.
List item.

Dùng shadow cho:

Modal.
Popover.
Dropdown.
Floating composer.
Toast.
Sticky header nhẹ nếu cần.
Discovery card nếu tách khỏi nền.
3.2. Shadow must be subtle

Shadow của UEConnect phải:

Nhẹ.
Mờ.
Không quá đen.
Không tạo cảm giác card nổi quá cao.
Không lấn át content.
3.3. Elevation must communicate hierarchy

Elevation trả lời câu hỏi:

Element này có đang nằm trên layer khác không?

Nếu không, không cần shadow.

4. Shadow Tokens
:root {
  --shadow-none: none;

  --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);

  --shadow-sm:
    0 1px 2px rgba(15, 23, 42, 0.06),
    0 1px 3px rgba(15, 23, 42, 0.08);

  --shadow-md:
    0 4px 12px rgba(15, 23, 42, 0.10);

  --shadow-lg:
    0 12px 32px rgba(15, 23, 42, 0.14);

  --shadow-xl:
    0 20px 48px rgba(15, 23, 42, 0.18);

  --shadow-focus:
    0 0 0 3px rgba(18, 72, 116, 0.16);

  --shadow-danger-focus:
    0 0 0 3px rgba(198, 40, 40, 0.12);
}
4.1. Token Usage Table
Token	Usage
shadow-none	Feed item, flat card, table row
shadow-xs	Subtle card if no border enough
shadow-sm	Right panel card, sticky surface, subtle elevation
shadow-md	Dropdown, popover, floating composer
shadow-lg	Modal, large overlay panel
shadow-xl	Rare hero/onboarding preview
shadow-focus	Focus ring, not physical elevation
shadow-danger-focus	Error focus ring
5. Elevation Levels
5.1. Level 0 — Flat
No shadow
Border/divider only

Dùng cho:

Feed post.
Comment.
Message bubble.
Sidebar item.
Table row.
Settings list.
5.2. Level 1 — Subtle Surface
Border + shadow-xs optional

Dùng cho:

Right panel card.
Profile info card.
Suggestion card.
Empty state card.
Admin dashboard small card.
5.3. Level 2 — Floating Control
shadow-sm or shadow-md

Dùng cho:

Dropdown menu.
Popover.
Floating action panel.
Tooltip large.
Date picker.
5.4. Level 3 — Overlay
shadow-lg

Dùng cho:

Modal.
Dialog.
Bottom sheet.
Report form overlay.
Image viewer.
5.5. Level 4 — Rare Showcase
shadow-xl

Dùng cho:

Marketing preview.
Onboarding device mockup.
App showcase card.

Không dùng cho product card hằng ngày.

6. Component Elevation Rules
6.1. Feed Post

Default:

.post {
  box-shadow: none;
  border-bottom: 1px solid var(--ue-border);
}

Rules:

Feed post không dùng shadow mặc định.
Divider giúp feed giống social platform thật hơn.
Hover có thể đổi background rất nhẹ, không cần shadow.
6.2. Card

Default card:

.card {
  border: 1px solid var(--ue-border);
  box-shadow: var(--shadow-none);
}

If isolated:

.card-elevated {
  border: 1px solid var(--ue-border);
  box-shadow: var(--shadow-xs);
}

Rules:

Border là chính.
Shadow chỉ bổ sung.
Không dùng shadow-lg cho default card.
6.3. Modal
.modal {
  box-shadow: var(--shadow-lg);
}

Rules:

Modal cần elevation rõ.
Overlay backdrop hỗ trợ separation.
Không cần shadow quá đen.
6.4. Dropdown / Popover
.dropdown {
  box-shadow: var(--shadow-md);
}

Rules:

Dropdown phải nổi trên content.
Có border nhẹ.
Không dùng shadow quá rộng.
6.5. Sticky Header
.sticky-header {
  border-bottom: 1px solid var(--ue-border);
  box-shadow: none;
}

Nếu cần:

.sticky-header.is-scrolled {
  box-shadow: var(--shadow-xs);
}

Rules:

Sticky header ưu tiên blur/border nhẹ.
Không dùng shadow nặng khi scroll.
6.6. Message Bubble

Message bubble không dùng shadow.

.message-bubble {
  box-shadow: none;
}

Tin nhắn không cần bay. Nó chỉ cần đọc được.

6.7. Admin Table

Admin table dùng border.

.admin-table {
  border: 1px solid var(--ue-border);
  box-shadow: none;
}

Dashboard cards có thể shadow-xs, nhưng table/queue không nên shadow từng row.

7. Page-level Elevation
7.1. Home Feed
Feed item: no shadow.
Composer: no shadow or shadow-xs if floating.
Right panel cards: border + optional shadow-xs.
Sticky topbar: border-bottom.
7.2. Discovery
Discovery card có thể dùng shadow-sm nếu nằm trên background neutral.
Action buttons không cần shadow.
Filter drawer/bottom sheet dùng shadow-lg.
7.3. Profile
Profile header không cần shadow nặng.
Profile cards dùng border + optional shadow-xs.
Dropdown menu dùng shadow-md.
7.4. Messaging
Conversation list: flat.
Message bubble: flat.
Composer sticky: border-top, optional shadow-xs.
Attachment popover: shadow-md.
7.5. Mentor
Mentor cards: border + optional shadow-xs.
Mentor request modal: shadow-lg.
Mentor filters: dropdown shadow-md.
7.6. Admin
Dashboard cards: border + optional shadow-xs.
Tables: border only.
Modals: shadow-lg.
Critical alert: border + semantic bg, no heavy shadow.
8. Focus Ring vs Shadow

Focus ring không phải elevation.

Focus ring chính thức:

:focus-visible {
  outline: none;
  box-shadow: var(--shadow-focus);
}

Error focus:

.input[aria-invalid="true"]:focus {
  box-shadow: var(--shadow-danger-focus);
}

Rules:

Không xóa focus ring.
Không thay focus bằng hover color mơ hồ.
Focus phải rõ cho keyboard users.
Focus ring dùng brand blue tint, không dùng glow neon.
9. TailwindCSS Mapping
// tailwind.config.js
export default {
  theme: {
    extend: {
      boxShadow: {
        none: "none",
        xs: "0 1px 2px rgba(15, 23, 42, 0.04)",
        sm: "0 1px 2px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.08)",
        md: "0 4px 12px rgba(15, 23, 42, 0.10)",
        lg: "0 12px 32px rgba(15, 23, 42, 0.14)",
        xl: "0 20px 48px rgba(15, 23, 42, 0.18)",
        focus: "0 0 0 3px rgba(18, 72, 116, 0.16)",
        "danger-focus": "0 0 0 3px rgba(198, 40, 40, 0.12)",
      },
    },
  },
}
9.1. Common Tailwind Patterns
Feed post: shadow-none border-b
Default card: border shadow-none
Subtle card: border shadow-xs
Dropdown: border shadow-md
Modal: shadow-lg
Marketing preview: shadow-xl
Focus: focus-visible:ring-4 focus-visible:ring-[rgba(18,72,116,0.16)]
10. Blade Examples
10.1. Feed Post
<article class="border-b border-ue-border bg-white p-4 shadow-none">
  {{-- post content --}}
</article>
10.2. Right Panel Card
<section class="rounded-xl border border-ue-border bg-white p-4 shadow-xs">
  {{-- right panel content --}}
</section>
10.3. Dropdown
<div class="rounded-lg border border-ue-border bg-white p-2 shadow-md">
  {{-- menu items --}}
</div>
10.4. Modal
<div class="rounded-xl border border-ue-border bg-white p-5 shadow-lg">
  {{-- modal content --}}
</div>
11. Shadow Anti-patterns
11.1. Heavy Shadow on Every Card

Sai:

.card {
  box-shadow: 0 20px 50px rgba(0,0,0,.25);
}

Đúng:

.card {
  border: 1px solid var(--ue-border);
  box-shadow: var(--shadow-xs);
}
11.2. Feed Cards Floating Everywhere

Sai:

.post {
  margin: 16px;
  box-shadow: var(--shadow-lg);
}

Đúng:

.post {
  border-bottom: 1px solid var(--ue-border);
  box-shadow: none;
}
11.3. Neon Focus Glow

Sai:

:focus {
  box-shadow: 0 0 20px #38BDF8;
}

Đúng:

:focus-visible {
  box-shadow: 0 0 0 3px rgba(18,72,116,.16);
}
11.4. Shadow Replacing Layout

Không dùng shadow để sửa hierarchy yếu. Nếu hierarchy mơ hồ, xem lại spacing, border, typography trước. Shadow không phải băng keo cứu mọi UI bị vỡ, dù nhiều template rất tin vào điều đó.

12. Elevation QA Checklist
12.1. Usage
 Feed post có dùng divider thay vì shadow không?
 Card mặc định có border nhẹ không?
 Shadow chỉ dùng khi có layer không?
 Modal/dropdown có elevation rõ không?
 Admin table không shadow từng row không?
12.2. Visual Quality
 Shadow có quá đen không?
 Shadow có quá rộng không?
 UI có nhìn như template AI không?
 Shadow có làm content mất tập trung không?
 Border và shadow có phối hợp tốt không?
12.3. Accessibility
 Focus ring rõ không?
 Focus ring không bị shadow khác che không?
 Overlay/modal có separation rõ không?
 Shadow không phải tín hiệu duy nhất cho state không?
12.4. Product Fit
 Social feed có nhẹ không?
 Discovery có nổi vừa đủ không?
 Messaging có flat và dễ đọc không?
 Admin có nghiêm túc không?
 UI có tránh over-polished AI look không?
13. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

Follow UEConnect Shadow & Elevation System.
Use border-first separation and subtle shadows.
Do not add heavy shadows to feed posts, message bubbles, or admin table rows.
Use shadow-xs for subtle cards, shadow-md for dropdowns/popovers, shadow-lg for modals.
Keep social feed flat and content-first.
Avoid over-polished AI mockup shadows.
Focus rings must be visible and use a subtle HCMUE blue ring.
14. Final Decision

Shadow/elevation system chính thức của UEConnect:

Default separation: border/divider
Default card: border + no shadow or shadow-xs
Feed post: no shadow
Dropdown/popover: shadow-md
Modal/bottom sheet: shadow-lg
Marketing preview: shadow-xl only if needed
Focus ring: brand blue 3px ring

Câu chốt:

Shadow của UEConnect phải giúp user hiểu layer, không biến mỗi card thành một vật thể đang cố thoát khỏi lực hấp dẫn.
