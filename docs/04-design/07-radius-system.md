---
title: "Radius System"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Frontend Team"
depends_on:
  - "00-design-foundation-roadmap.md"
  - "01-brand-attributes.md"
  - "03-color-system.md"
  - "06-spacing-system.md"
next:
  - "08-shadow-elevation-system.md"
  - "09-border-system.md"
  - "12-component-primitives.md"
  - "19-design-token-documentation.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
---

# 07. Radius System

## 1. Purpose

File này định nghĩa border-radius system chính thức cho UEConnect.

Mục tiêu:

- Chốt mức bo góc dùng cho từng loại component.
- Giữ UI thân thiện nhưng không trẻ con quá mức.
- Tránh card/button bị bo quá lớn khiến app nhìn như template AI.
- Đảm bảo feed, profile, messaging, discovery, admin có hình thái nhất quán.
- Hỗ trợ implement bằng TailwindCSS và Blade components.

Radius là một chi tiết nhỏ nhưng có sức phá hoại lớn. Bo quá ít thì UI lạnh. Bo quá nhiều thì UI giống app đồ chơi. Bo tất cả 24px thì xin chúc mừng, ta vừa phát minh lại “AI SaaS card”.

---

## 2. Core Decision

UEConnect dùng radius strategy:

```txt
Soft but restrained
+ social-friendly
+ enterprise-clean
+ context-based

Nghĩa là:

Avatar và badge có thể pill/circle.
Button/input dùng radius vừa phải.
Card dùng radius nhẹ.
Modal dùng radius lớn hơn một chút.
Không bo quá nhiều cho admin table hoặc dense UI.
3. Radius Scale
:root {
  --radius-none: 0;
  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-2xl: 20px;
  --radius-3xl: 24px;
  --radius-full: 999px;
}
3.1. Token Usage Table
Token	Value	Usage
radius-none	0	Divider, table cell, edge-to-edge layout
radius-xs	4px	Tiny elements, admin badges, small media
radius-sm	6px	Compact button, small input, tooltip
radius-md	8px	Default button, input, small card
radius-lg	12px	Post media, default card, dropdown
radius-xl	16px	Modal, profile card, large panel
radius-2xl	20px	Onboarding card, discovery card
radius-3xl	24px	Rare hero/marketing card
radius-full	999px	Avatar, pill, badge, icon button
4. Radius Principles
4.1. Radius must match density

Compact UI cần radius nhỏ hơn.

Density	Recommended Radius
Compact	4–8px
Default	8–12px
Comfortable	12–16px
Brand/Onboarding	16–24px
4.2. Radius must match component role
Interactive controls cần radius rõ nhưng không quá mềm.
Content containers cần radius nhẹ.
Social identity elements như avatar/badge có thể full.
Admin/data UI cần radius tiết chế.
4.3. Do not over-round everything

Không dùng rounded-2xl cho mọi component. Nếu mọi thứ đều mềm, không còn hierarchy. UI sẽ giống một túi kẹo marshmallow có khả năng đăng bài, nghe không thuyết phục lắm.

5. Component Radius Rules
5.1. Avatar
.avatar {
  border-radius: var(--radius-full);
}

Rules:

Avatar luôn circle.
Không dùng rounded-square avatar trong social feed mặc định.
Club/community avatar có thể dùng radius-lg hoặc circle tùy identity.
5.2. Badge / Chip
.badge,
.chip {
  border-radius: var(--radius-full);
}

Rules:

Verified badge dùng pill.
Interest chip dùng pill.
Status badge có thể pill hoặc radius-sm.
Admin status badge có thể radius-sm để nghiêm túc hơn.
5.3. Button
Button Type	Radius
Primary / Secondary	8px
Icon button	999px or 8px
Pill CTA	999px, rare
Admin action button	6–8px
Mobile bottom action	8–12px

Default:

.button {
  border-radius: var(--radius-md);
}

Rules:

Product button mặc định dùng 8px.
Pill button dùng cho CTA đặc biệt hoặc social action cần mềm hơn.
Không dùng pill cho mọi button.
Delete/report button không cần quá cute.
5.4. Input
.input {
  border-radius: var(--radius-md);
}

Rules:

Input mặc định 8px.
Search input có thể radius-full.
Textarea dùng 8–12px.
Admin filter input dùng 6–8px.
5.5. Card
Card Type	Radius
Feed post item	0–12px tùy layout
Right panel card	12–16px
Profile card	16px
Discovery card	20px
Onboarding card	20–24px
Admin card	8–12px

Rules:

Feed item trong continuous feed có thể dùng divider và không cần radius.
Card độc lập dùng 12–16px.
Discovery card có thể lớn hơn vì visual hơn.
Admin không dùng radius quá lớn.
5.6. Modal / Dialog
.modal {
  border-radius: var(--radius-xl);
}

Rules:

Modal desktop dùng 16px.
Mobile bottom sheet dùng top radius 16–20px.
Alert dialog destructive vẫn dùng radius bình thường, không cần góc nhọn để dọa user.
5.7. Dropdown / Popover
.dropdown {
  border-radius: var(--radius-lg);
}

Rules:

Dropdown dùng 12px.
Tooltip dùng 6px.
Menu item bên trong dùng 6–8px.
5.8. Media
Media Type	Radius
Post image	12px
Profile cover	16px or 0 if full-width
Message attachment	12px
Discovery photo	16–20px
Admin preview thumbnail	6–8px
6. Page-level Radius Rules
6.1. Home Feed
Continuous feed item: border divider, radius optional.
Composer box: 12px if boxed.
Post media: 12px.
Action hover background: full pill or 8px.
6.2. Discovery
Main discovery card: 20px.
Profile photo/media: 16–20px.
Action buttons: full pill or 12px.
Filter drawer: 16px.

Discovery được phép mềm hơn feed, nhưng không được thành dating app candy UI.

6.3. Profile
Profile card/panel: 16px.
Avatar: circle.
Cover image: 16px if contained, 0 if edge-to-edge.
Tabs: 8px indicator or underline.
6.4. Messaging
Message bubble: 16–20px.
Own/other bubble có thể chỉnh corner theo group, nhưng MVP không cần quá phức tạp.
Inbox item hover: 12px.
Composer: 20px or full pill.

Message bubble bo tròn vừa phải. Nếu bubble tròn như viên thuốc khổng lồ, tin nhắn dài sẽ nhìn kỳ. Con chữ cũng cần phẩm giá.

6.5. Admin
Dashboard card: 8–12px.
Table container: 12px.
Table rows: không cần radius từng row.
Filter chips: full pill.
Status badges: 6px or full pill.
7. Mobile Radius Rules

Mobile có thể dùng radius hơi lớn hơn desktop ở:

Bottom sheet.
Action sheet.
Mobile card.
Composer.
Search input.

Recommended:

Mobile card: 12–16px
Bottom sheet: 20px top corners
Search: full pill
Button: 8–12px

Không nên:

Dùng 24px cho tất cả.
Bo quá lớn trong dense list.
Bo từng post trong feed nếu feed đã có divider.
8. TailwindCSS Mapping
// tailwind.config.js
export default {
  theme: {
    extend: {
      borderRadius: {
        none: "0",
        xs: "4px",
        sm: "6px",
        md: "8px",
        lg: "12px",
        xl: "16px",
        "2xl": "20px",
        "3xl": "24px",
        full: "999px",
      },
    },
  },
}
8.1. Common Tailwind Patterns
Button: rounded-md
Input: rounded-md
Search: rounded-full
Badge/chip: rounded-full
Post media: rounded-lg
Right panel card: rounded-xl
Profile card: rounded-xl
Discovery card: rounded-2xl
Modal: rounded-xl
Bottom sheet: rounded-t-2xl
Admin table container: rounded-lg
9. Blade Examples
9.1. Button
<button class="inline-flex h-10 items-center justify-center rounded-md bg-ue-brand px-4 text-sm font-semibold text-white">
  Tạo bài viết
</button>
9.2. Verified Badge
<span class="inline-flex items-center gap-1 rounded-full bg-[rgba(18,72,116,0.08)] px-2 py-0.5 text-xs font-semibold text-ue-brand">
  Đã xác thực UEer
</span>
9.3. Discovery Card
<article class="rounded-2xl border border-ue-border bg-white p-5">
  {{-- profile discovery content --}}
</article>
9.4. Modal
<div class="rounded-xl border border-ue-border bg-white p-5 shadow-md">
  {{-- modal content --}}
</div>
10. Radius Anti-patterns
10.1. Everything Rounded 24px

Sai:

* {
  border-radius: 24px;
}

Không có gì nói “tôi không có design system” rõ hơn việc mọi thứ đều bo y chang nhau.

10.2. Pill Buttons Everywhere

Sai:

.button {
  border-radius: 999px;
}

Đúng:

.button {
  border-radius: 8px;
}

.button-pill {
  border-radius: 999px;
}
10.3. Sharp Social UI

Sai:

.card,
.button,
.input {
  border-radius: 0;
}

UEConnect cần thân thiện. Góc vuông hoàn toàn dễ làm UI lạnh như form nội bộ trường.

10.4. Over-rounded Admin

Sai:

.admin-table {
  border-radius: 24px;
}

Đúng:

.admin-table {
  border-radius: 12px;
}
11. Radius QA Checklist
11.1. Consistency
 Button mặc định dùng radius 8px không?
 Input dùng radius 8px không?
 Badge/chip dùng pill không?
 Card dùng 12–16px theo context không?
 Modal dùng 16px không?
11.2. Product Fit
 UI có thân thiện nhưng không trẻ con không?
 Discovery mềm hơn feed nhưng không dating không?
 Admin đủ nghiêm túc không?
 Messaging bubble không quá tròn không?
 Feed không bị card hóa quá mức không?
11.3. Responsive
 Mobile bottom sheet có top radius hợp lý không?
 Touch target không bị radius làm méo layout không?
 Dense list không quá bo không?
 Media radius không cắt nội dung quan trọng không?
11.4. Anti-AI
 Có tránh rounded-2xl cho mọi thứ không?
 Có tránh pill button tràn lan không?
 Có tránh card quá “cute” không?
 Radius có theo component role không?
12. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

Follow UEConnect Radius System.
Use restrained radius: buttons and inputs use 8px, cards use 12–16px, discovery cards can use 20px, avatars/badges/chips use full pill.
Do not make every component rounded-2xl or pill-shaped.
Keep UI friendly but enterprise-ready, not childish or AI-template-like.
Admin screens should use smaller radius than discovery/onboarding.
13. Final Decision

Radius system chính thức của UEConnect:

Button/Input: 8px
Post media: 12px
Default card: 12–16px
Profile card: 16px
Discovery card: 20px
Modal: 16px
Avatar/Badge/Chip: 999px
Admin dense UI: 6–12px

Câu chốt:

Radius của UEConnect phải làm UI mềm và gần gũi hơn, không biến toàn bộ sản phẩm thành một bộ sưu tập viên thuốc màu trắn
