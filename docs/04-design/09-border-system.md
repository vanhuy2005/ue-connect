---
title: "Border System"
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
  - "08-shadow-elevation-system.md"
next:
  - "10-icon-system.md"
  - "12-component-primitives.md"
  - "14-interaction-states.md"
  - "19-design-token-documentation.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "page-specs/admin-dashboard.md"
---

# 09. Border System

## 1. Purpose

File này định nghĩa border system chính thức cho UEConnect.

Mục tiêu:

- Chốt cách dùng border, divider và outline.
- Hỗ trợ neutral-first social UI.
- Giảm phụ thuộc vào shadow.
- Giữ feed, card, input, table, modal, list rõ ràng và sạch.
- Đảm bảo state như focus, error, selected, disabled nhất quán.
- Hỗ trợ implement bằng TailwindCSS và CSS variables.

Border trong UEConnect là công cụ phân lớp chính. Nếu shadow là đèn sân khấu, border là người làm việc thật ở hậu trường. Ít ai khen, nhưng thiếu nó là mọi thứ rối ngay.

---

## 2. Core Decision

UEConnect dùng border strategy:

```txt
Light border
+ subtle divider
+ semantic border only when needed
+ no decorative brand border

Nghĩa là:

Feed dùng divider.
Card dùng border nhẹ.
Input dùng border rõ hơn khi hover/focus/error.
Admin table dùng border có hệ thống.
Brand border chỉ dùng cho selected/verified/brand info, không dùng khắp nơi.
3. Border Tokens
:root {
  --border-none: transparent;

  --ue-border-subtle: #F1F5F9;
  --ue-border: #E4E6EB;
  --ue-border-strong: #CBD5E1;

  --ue-border-brand: rgba(18, 72, 116, 0.16);
  --ue-border-brand-strong: rgba(18, 72, 116, 0.32);

  --ue-border-success: rgba(32, 201, 151, 0.24);
  --ue-border-warning: rgba(245, 158, 11, 0.24);
  --ue-border-danger: rgba(198, 40, 40, 0.22);
  --ue-border-mentor: rgba(124, 92, 255, 0.20);
}
3.1. Token Usage Table
Token	Usage
ue-border-subtle	Very light divider, nested separation
ue-border	Default card/input/divider
ue-border-strong	Hover input, selected neutral, table emphasis
ue-border-brand	Verified badge, selected brand chip, active info panel
ue-border-brand-strong	Rare focused brand panel
ue-border-success	Success alert/card
ue-border-warning	Pending/warning state
ue-border-danger	Error/destructive state
ue-border-mentor	Mentor badge/card highlight, limited
4. Border Width Tokens
:root {
  --border-width-0: 0;
  --border-width-1: 1px;
  --border-width-2: 2px;
}

Rules:

Default border = 1px.
Focus không dùng border dày, dùng focus ring.
Active tab có thể dùng 2px underline.
Error input dùng 1px border + error focus ring.
Không dùng border 3px+ trong product UI.
5. Divider Rules
5.1. Feed Divider

Feed post nên dùng divider:

.post {
  border-bottom: 1px solid var(--ue-border);
}

Rules:

Divider giúp feed liên tục và nhẹ.
Không cần card shadow cho từng post.
Divider không quá đậm.
5.2. List Divider

Dùng cho:

Notifications.
Inbox.
Settings.
Admin queue.
Comment thread.
.list-item + .list-item {
  border-top: 1px solid var(--ue-border);
}
5.3. Section Divider

Dùng khi cần tách section lớn:

.section-divider {
  border-top: 1px solid var(--ue-border);
}

Không dùng divider quá nhiều đến mức UI giống bảng Excel. Excel đã tồn tại rồi, không cần social app nhập vai.

6. Component Border Rules
6.1. Card

Default:

.card {
  border: 1px solid var(--ue-border);
}

Rules:

Card mặc định có border nhẹ.
Không dùng brand border cho card thường.
Hover card có thể dùng ue-border-strong.
Selected card có thể dùng brand border nếu có ý nghĩa.
6.2. Post
.post {
  border-bottom: 1px solid var(--ue-border);
}

Rules:

Post item trong feed không cần full border box.
Nếu post standalone/detail, có thể dùng border container.
Không dùng blue border cho post.
6.3. Input
.input {
  border: 1px solid var(--ue-border);
}

.input:hover {
  border-color: var(--ue-border-strong);
}

.input:focus {
  border-color: var(--ue-brand);
  box-shadow: 0 0 0 3px rgba(18, 72, 116, 0.16);
}

.input[aria-invalid="true"] {
  border-color: var(--danger);
}

Rules:

Input cần border rõ.
Error không chỉ border đỏ, cần text lỗi.
Disabled input dùng border default + bg disabled.
Placeholder không thay label.
6.4. Button

Primary button:

.button-primary {
  border: 1px solid var(--ue-brand);
}

Secondary button:

.button-secondary {
  border: 1px solid var(--ue-border);
}

Ghost button:

.button-ghost {
  border: 1px solid transparent;
}

Rules:

Primary button border cùng màu nền.
Secondary button dùng border neutral.
Danger button dùng danger border.
Không dùng brand border cho mọi button.
6.5. Badge / Chip

Verified badge:

.verified-badge {
  border: 1px solid rgba(18, 72, 116, 0.14);
}

Status badge:

.badge-warning {
  border: 1px solid var(--ue-border-warning);
}

Rules:

Badge border nhẹ.
Badge không cần shadow.
Role color phải tiết chế.
6.6. Modal
.modal {
  border: 1px solid var(--ue-border);
}

Modal cần border + shadow. Border giúp modal rõ trên nền sáng.

6.7. Dropdown
.dropdown {
  border: 1px solid var(--ue-border);
}

Dropdown dùng border + shadow-md.

6.8. Table
.table-wrapper {
  border: 1px solid var(--ue-border);
}

.table-row {
  border-bottom: 1px solid var(--ue-border);
}

Rules:

Admin table cần border nhất quán.
Không dùng border quá đậm.
Không border từng cell nếu không cần.
Header có thể dùng border-bottom strong.
7. Page-level Border Rules
7.1. Home Feed
Left nav separator: right border.
Feed column separator: right/left border nếu desktop.
Feed post: bottom border.
Composer: bottom border.
Tabs: bottom border or active underline.
Right panel cards: full border.
7.2. Discovery
Discovery card: full border.
Filter chip: border.
Action group: no excessive border.
Empty state card: border.
7.3. Profile
Profile header: bottom border.
Tabs: bottom border + active indicator.
Profile cards: full border.
Edit form fields: input border.
7.4. Messaging
Inbox/conversation split: vertical border.
Conversation item: bottom border or hover bg.
Composer: top border.
Message bubble: no border by default.
Attachment preview: border.
7.5. Mentor
Mentor card: border.
Mentor request status card: semantic border if needed.
Expertise chip: border.
Mentor profile sections: subtle divider.
7.6. Admin
Table wrapper: border.
Table row: border-bottom.
Queue item: border.
Filter controls: input border.
Critical alert: semantic border.
8. Border State Rules
8.1. Default
border-color: var(--ue-border);
8.2. Hover
border-color: var(--ue-border-strong);

Use for:

Input hover.
Secondary button hover.
Clickable card hover if subtle.
8.3. Focus

Focus should combine border + ring:

border-color: var(--ue-brand);
box-shadow: 0 0 0 3px rgba(18, 72, 116, 0.16);
8.4. Error
border-color: var(--danger);
box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.12);
8.5. Selected
border-color: var(--ue-border-brand);
background: var(--ue-brand-soft);
8.6. Disabled
border-color: var(--ue-border);
background: var(--ue-surface-disabled);
color: var(--ue-text-disabled);
9. Border + Radius Pairing
Component	Border	Radius
Button	1px	8px
Input	1px	8px
Card	1px	12–16px
Post media	1px	12px
Modal	1px	16px
Dropdown	1px	12px
Badge	1px	999px
Admin table	1px	8–12px

Rules:

Border follows radius.
Media with border must clip overflow.
Rounded card needs overflow hidden only if media touches edge.
Do not combine thick border with large radius unless intentional.
10. TailwindCSS Mapping
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        ue: {
          border: "#E4E6EB",
          "border-subtle": "#F1F5F9",
          "border-strong": "#CBD5E1",
          "border-brand": "rgba(18, 72, 116, 0.16)",
          "border-brand-strong": "rgba(18, 72, 116, 0.32)",
        },
      },
      borderWidth: {
        1: "1px",
        2: "2px",
      },
    },
  },
}
10.1. Common Tailwind Patterns
Default card: border border-ue-border
Feed item: border-b border-ue-border
Input: border border-ue-border hover:border-ue-border-strong
Focus: focus:border-ue-brand focus:ring-4
Modal: border border-ue-border
Table wrapper: border border-ue-border
Selected chip: border border-ue-border-brand bg-ue-brand-soft
11. Blade Examples
11.1. Card
<section class="rounded-xl border border-ue-border bg-white p-4">
  {{-- card content --}}
</section>
11.2. Feed Item
<article class="border-b border-ue-border bg-white p-4">
  {{-- post content --}}
</article>
11.3. Input
<input
  class="h-11 w-full rounded-md border border-ue-border bg-white px-3 text-base text-ue-text hover:border-ue-border-strong focus:border-ue-brand focus:outline-none focus:ring-4 focus:ring-[rgba(18,72,116,0.16)]"
/>
11.4. Error Input
<input
  aria-invalid="true"
  class="h-11 w-full rounded-md border border-danger bg-white px-3 text-base text-ue-text focus:outline-none focus:ring-4 focus:ring-[rgba(198,40,40,0.12)]"
/>
11.5. Admin Table
<div class="overflow-hidden rounded-lg border border-ue-border bg-white">
  <table class="w-full">
    <thead class="border-b border-ue-border bg-ue-surface-subtle">
      {{-- table head --}}
    </thead>
    <tbody class="divide-y divide-ue-border">
      {{-- rows --}}
    </tbody>
  </table>
</div>
12. Border Anti-patterns
12.1. Brand Border Everywhere

Sai:

.card {
  border: 1px solid #124874;
}

Đúng:

.card {
  border: 1px solid var(--ue-border);
}
12.2. Too Many Nested Borders

Sai:

Card border
→ Inner panel border
→ Inner row border
→ Inner chip border
→ Inner icon border

Nếu mọi thứ đều bị đóng khung, UI trông như một nhà tù component.

12.3. Border Too Dark

Sai:

border-color: #94A3B8;

cho mọi card.

Đúng:

border-color: var(--ue-border);
12.4. Error Only by Border

Sai:

.input-error {
  border-color: red;
}

Đúng:

Red border + error message + aria-invalid + describedby
12.5. Removing Focus Border

Không xóa focus style nếu chưa có thay thế.

Sai:

button:focus {
  outline: none;
  box-shadow: none;
}

Đúng:

button:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(18,72,116,.16);
}
13. Border QA Checklist
13.1. Consistency
 Card dùng border default không?
 Feed dùng divider nhẹ không?
 Input hover/focus/error có rõ không?
 Modal/dropdown có border không?
 Admin table border nhất quán không?
13.2. Product Fit
 UI có ưu tiên border hơn shadow không?
 Brand border có bị lạm dụng không?
 Feed có nhẹ và content-first không?
 Messaging không bị đóng khung quá mức không?
 Admin đủ rõ nhưng không nặng không?
13.3. Accessibility
 Focus border/ring đủ rõ không?
 Error không chỉ dựa vào màu không?
 Selected state có text/icon ngoài màu không?
 Divider có đủ contrast nhưng không quá mạnh không?
13.4. Anti-AI
 Có tránh gradient border không?
 Có tránh border quá đậm cho mọi card không?
 Có tránh nested border quá nhiều không?
 Có tránh blue border khắp nơi không?
14. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

Follow UEConnect Border System.
Use light neutral borders and dividers as the primary separation method.
Use border #E4E6EB for cards, feed items, inputs, tables, and panels.
Do not use HCMUE blue border on every card.
Do not use gradient borders.
Use border + focus ring for input focus.
Use semantic borders only for success, warning, danger, mentor states.
Keep feed divider-based and content-first.
15. Final Decision

Border system chính thức của UEConnect:

Default border: #E4E6EB
Strong border: #CBD5E1
Subtle border: #F1F5F9
Brand border: rgba(18,72,116,0.16), limited use
Default width: 1px
Feed separation: divider
Card separation: border
Focus: border + ring
Error: semantic border + text

Câu chốt:

Border của UEConnect phải tạo cấu trúc rõ ràng mà không làm UI nặng nề, giống một social platform trưởng thành chứ không p
