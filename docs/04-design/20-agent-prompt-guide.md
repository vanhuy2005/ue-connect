---
title: "Agent Prompt Guide"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / Design System / Frontend / AI Workflow"
depends_on:
  - "01-brand-attributes.md"
  - "02-brand-identity-hcmue.md"
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
  - "19-design-token-documentation.md"
related:
  - "../03-product/product-overview.md"
  - "../03-product/sitemap.md"
  - "../03-product/feature-list.md"
  - "../03-product/feature-priority.md"
  - "../03-product/feature-specs/template.md"
---

# Agent Prompt Guide

## 1. Purpose

File này định nghĩa cách prompt AI agent để tạo UI, component, screen, wireframe, HTML, Blade, TailwindCSS hoặc design review cho UEConnect mà vẫn bám đúng design system.

Mục tiêu chính:

- Giữ UI nhất quán với brand UEConnect.
- Giảm prompt dài nhưng vẫn đủ ràng buộc.
- Tránh AI tự bịa màu, gradient, component, tone hoặc layout.
- Giữ sản phẩm không bị nhầm thành dating app, admin portal cũ kỹ hoặc SaaS dashboard generic.
- Tối ưu token khi làm việc nhiều lần với AI.
- Tạo prompt có thể tái sử dụng cho từng loại task: screen design, component, page spec, code UI, review UI, logo, responsive, accessibility.

Prompt guide này là một phần của design system, không phải ghi chú cho vui. Nếu prompt mơ hồ, AI sẽ sinh ra giao diện mơ hồ. Rồi con người lại ngồi sửa. Một vòng luân hồi rất frontend.

---

## 2. Design Decision

UEConnect sử dụng **Agent Prompt Pack** theo 3 tầng:

```txt
Level 1: Core Identity Prompt
Level 2: Task-specific Prompt
Level 3: QA / Review Prompt
````

Trong đó:

| Level                | Purpose                               |   Token Cost | Usage                   |
| -------------------- | ------------------------------------- | -----------: | ----------------------- |
| Core Identity Prompt | Khóa brand, tone, token, anti-pattern | Low / Medium | Gắn vào mọi prompt      |
| Task-specific Prompt | Mô tả màn/component cần tạo           |       Medium | Tùy tác vụ              |
| QA / Review Prompt   | Kiểm tra output có đúng không         | Low / Medium | Sau khi AI sinh kết quả |

Nguyên tắc:

```txt
Không nhồi toàn bộ design system vào mỗi prompt.
Chỉ nhồi core constraints + file reference + task constraints.
```

AI agent phải được hướng bằng:

```txt
brand anchors
design tokens
allowed patterns
forbidden patterns
expected output format
QA checklist
```

Không prompt kiểu:

```txt
Design modern UI for student social app.
```

Câu này tương đương ném AI vào rừng rồi hy vọng nó tự tìm được HCMUE Blue. Lạc là chắc.

---

## 3. Rationale

UEConnect có nhiều feature:

* Auth.
* Verification.
* Profile.
* Home Feed.
* Discovery.
* Greeting Connection.
* Messaging.
* Notification.
* Mentor Matching.
* Career Pathway.
* Community / Club.
* Search / Filter.
* Safety Reporting.
* Moderation.
* Admin Operations.
* Settings / Privacy.

Nếu prompt không có chuẩn, mỗi feature sẽ sinh ra một kiểu UI riêng:

```txt
Feed giống Threads.
Discovery giống Tinder.
Admin giống Bootstrap 3.
Mentor giống landing page khóa học.
Community giống Facebook group clone.
Verification giống cổng hành chính.
```

Prompt guide giúp AI agent hiểu:

```txt
UEConnect = HCMUE verified student social platform
not dating
not generic SaaS
not admin portal first
not gradient-heavy AI mockup
```

Prompt tốt làm giảm:

* Sai màu.
* Sai tone.
* Sai component.
* Sai responsive.
* Sai accessibility.
* Sai content language.
* Sai state UI.
* Sai product positioning.
* Sai route/page hierarchy.

Nói cách khác, prompt guide là hàng rào. Không có hàng rào, AI sẽ chạy tung tăng qua mọi aesthetic từng thấy trên internet, một cảnh tượng đáng báo động.

---

# 4. Source of Truth Rules

## 4.1. Canonical Design Files

AI agent phải ưu tiên các file canonical:

```txt
01-brand-attributes.md
02-brand-identity-hcmue.md
03-color-system.md
04-gradient-policy.md
05-typography-system.md
06-spacing-system.md
07-radius-system.md
08-shadow-elevation-system.md
09-border-system.md
10-icon-system.md
11-logo-usage-system.md
12-component-primitives.md
13-component-variants.md
14-interaction-states.md
15-motion-system.md
16-content-tone.md
17-accessibility-rules.md
18-responsive-rules.md
19-design-token-documentation.md
20-agent-prompt-guide.md
```

## 4.2. Deprecated Files

Không dùng các file sau làm source of truth chính:

```txt
brand-guideline.md
design-system.md
ux-principles.md
wireframe-notes.md
```

Các file này chỉ là pointer hoặc legacy note.

Nếu AI cần quyết định design:

```txt
Use canonical split files.
Do not expand deprecated placeholder files.
```

## 4.3. Live Preview Rule

```txt
preview.html is a visual reference, not the source of truth.
```

Có thể dùng để tham khảo mood/layout, nhưng quyết định cuối cùng phải lấy từ markdown canonical.

## 4.4. Product Logic Rule

Design prompt phải bám:

```txt
product-overview.md
sitemap.md
feature-list.md
feature-priority.md
feature specs
state machine source of truth
```

Không để AI tự phát minh feature mới nếu chưa có trong scope.

---

# 5. Core Identity Prompt

## 5.1. Full Core Prompt

Dùng khi bắt đầu một session mới với AI agent hoặc khi output cần độ chính xác cao.

```txt
You are designing UI for UEConnect, a verified HCMUE student social platform.

Product identity:
- UEConnect is only for the HCMUE community.
- It connects students, alumni, academic advisors, mentors, clubs, communities and learning/career pathways.
- It is a trusted verified campus social platform, not a dating app, not a generic SaaS dashboard, and not an old administrative portal.

Design direction:
- Neutral-first social UI.
- Content-first layout.
- Restrained HCMUE identity.
- Mobile-first PWA.
- Enterprise-ready accessibility and state handling.
- Friendly, trusted, youthful, academic, calm.

Brand constraints:
- Primary brand color: HCMUE Blue #124874.
- Use blue-only gradients only.
- Do not use red/pink/orange dating-app gradients.
- Do not make the product look like Tinder.
- Use Be Vietnam Pro or a clean system sans fallback.
- UI language is Vietnamese.
- Code, component names and technical identifiers are English.

Visual rules:
- Use mostly white/near-white/neutral surfaces.
- Use HCMUE Blue only for logo, primary CTA, active nav, verified badge, focus ring and important links.
- Do not overuse gradient, shadow, big radius, or colorful icons.
- Use rounded but mature components.
- Prefer borders and subtle elevation over heavy shadows.
- Use Lucide-style rounded line icons.
- Keep typography clear and readable.

Interaction rules:
- Every interactive component needs default, hover, active, focus-visible, disabled, loading and error states where applicable.
- Use visible focus rings.
- Touch target minimum is 44px.
- Respect reduced motion.
- Do not rely only on color for status.
- Gesture interactions must have button fallback.

Responsive rules:
- Mobile-first.
- Works at 320px width.
- Installed PWA safe-area aware.
- Mobile uses bottom nav, sheets and single-column layouts.
- Desktop can use sidebar, right rail, table and split layouts.
- Business logic must remain consistent across devices.

Content tone:
- Vietnamese UI copy.
- Clear, calm, respectful, non-romantic.
- Use "Gửi lời chào", not "match" or "crush".
- Use "Khám phá", "Kết nối", "Hồ sơ", "Cộng đồng", "Mentor", "Lộ trình định hướng".
- Avoid playful copy in verification, safety, moderation and admin flows.

Output must:
- Follow UEConnect design tokens.
- Use documented component primitives and variants.
- Include empty/loading/error/permission states if relevant.
- Include accessibility notes if producing specs.
- Avoid inventing new top-level navigation or features unless requested.
```

## 5.2. Compact Core Prompt

Dùng khi cần tiết kiệm token.

```txt
Design for UEConnect: a verified HCMUE-only student social PWA. Use neutral-first, content-first social UI with restrained HCMUE Blue #124874. Blue-only gradients only. Vietnamese UI, English code names. Friendly, trusted, youthful, academic, calm. Not Tinder, not dating, not generic SaaS, not old admin portal. Use Be Vietnam Pro/system sans, Lucide-style icons, subtle borders/shadows, mobile-first responsive, 44px touch targets, visible focus, accessible states, no color-only status. Follow existing design tokens, component primitives, variants, interaction states, responsive rules and content tone.
```

## 5.3. Ultra-short Anchor

Dùng trong prompt lặp lại nhiều lần trong cùng context.

```txt
Apply UEConnect design system: HCMUE Blue #124874, neutral-first social UI, blue-only gradients, Vietnamese UI, mobile-first PWA, accessible states, no dating/Tinder styling, use documented tokens/components.
```

---

# 6. Negative Prompt

## 6.1. Universal Negative Prompt

Gắn vào hầu hết prompt tạo UI.

```txt
Avoid:
- Tinder/dating app styling.
- Red/pink/orange romantic gradients.
- Heart/match/crush language.
- Generic SaaS dashboard look.
- Old government/admin portal look.
- Heavy full-screen gradients.
- Overly colorful icons.
- Excessive shadows.
- Over-rounded cute components.
- Random hex colors.
- Arbitrary Tailwind values.
- Tiny text.
- Hover-only actions.
- Color-only status.
- English UI copy unless explicitly requested.
- Invented top-level features or routes.
```

## 6.2. Visual Negative Prompt

```txt
Do not create:
- gradient-heavy AI mockup
- glassmorphism everywhere
- neon colors
- 3D illustrations as core UI
- dating decision cards with hearts
- floating cards with huge shadows
- admin tables with tiny unreadable text
- feed cards that look like landing page sections
- inconsistent badge colors
```

## 6.3. Copy Negative Prompt

```txt
Do not use:
- match
- crush
- swipe right
- người ấy
- tìm một nửa
- submit
- invalid
- oops
- click here
- are you sure
- verified student dating
```

Preferred language:

```txt
Gửi lời chào
Kết nối
Khám phá
Xem hồ sơ
Báo cáo
Đã xác thực
Đang chờ duyệt
Cần bổ sung
Không thể gửi
Vui lòng thử lại
```

---

# 7. Prompt Variables

Dùng biến để prompt ngắn và tái sử dụng.

## 7.1. Common Variables

```txt
{{TASK}} = what the agent must do
{{FEATURE}} = target feature
{{SCREEN}} = target page/screen
{{USER_ROLE}} = student/alumni/advisor/mentor/admin/club_manager
{{DEVICE}} = mobile/desktop/tablet/PWA
{{OUTPUT_FORMAT}} = markdown/html/blade/tailwind/spec/checklist
{{STATE}} = default/loading/empty/error/locked/restricted/success
{{SOURCE_DOCS}} = relevant files to follow
{{CONSTRAINTS}} = extra constraints
```

## 7.2. Example Variable Use

```txt
{{TASK}}: Create a detailed page spec.
{{FEATURE}}: Mentor Matching.
{{SCREEN}}: Mentor Request Form.
{{USER_ROLE}}: verified student.
{{DEVICE}}: mobile-first PWA and desktop.
{{OUTPUT_FORMAT}}: markdown spec.
```

## 7.3. Token Optimization Rule

Không viết lại tất cả context nếu đã có session memory.

Dùng:

```txt
Use the UEConnect Core Identity Prompt from 20-agent-prompt-guide.md.
```

Hoặc:

```txt
Apply UEConnect design system and generate only the requested section.
```

---

# 8. Output Format Rules

## 8.1. For Markdown Specs

Output structure:

```txt
# [Feature / Screen Name]

## 1. Purpose
## 2. User Context
## 3. Layout
## 4. Components
## 5. States
## 6. Interactions
## 7. Responsive Behavior
## 8. Accessibility
## 9. Content / Microcopy
## 10. QA Checklist
```

## 8.2. For UI Code

When generating Blade/Tailwind UI:

```txt
- Use English component names.
- UI text must be Vietnamese.
- Prefer Blade components over raw repeated classes.
- Use documented tokens.
- Avoid arbitrary Tailwind values.
- Include responsive classes.
- Include focus-visible states.
- Include aria-label for icon-only buttons.
- Include loading/empty/error states where relevant.
```

## 8.3. For HTML Preview

When generating HTML preview:

```txt
- Single HTML file allowed.
- Use Be Vietnam Pro.
- Use CSS variables for tokens.
- Use HCMUE Blue #124874.
- Use blue-only gradient only for rare brand moments.
- Include mobile responsive behavior.
- Include accessible labels.
- Do not import random UI library unless requested.
```

## 8.4. For Design Review

Review format:

```txt
## Summary
## What Matches UEConnect
## Issues
## Missing States
## Accessibility Problems
## Responsive Problems
## Token Violations
## Priority Fixes
## Final Recommendation
```

---

# 9. Screen Design Prompt Template

## 9.1. Full Screen Prompt

```txt
Use the UEConnect Core Identity Prompt.

Task:
Design the {{SCREEN}} screen for {{FEATURE}}.

Context:
- Product: UEConnect, verified HCMUE-only student social PWA.
- User role: {{USER_ROLE}}.
- Device target: {{DEVICE}}.
- UI language: Vietnamese.
- Code/component naming: English.
- Source docs: {{SOURCE_DOCS}}.

Requirements:
- Follow HCMUE Blue #124874 and neutral-first social UI.
- Use blue-only gradients only if this is a rare brand moment.
- Use documented component primitives and variants.
- Include primary layout, secondary actions and navigation placement.
- Include default, loading, empty, error, locked/permission states if relevant.
- Include mobile-first and desktop responsive behavior.
- Include accessibility requirements.
- Include microcopy in Vietnamese.
- Do not invent new product scope unless explicitly requested.

Output:
{{OUTPUT_FORMAT}}

Also include:
- Component list.
- State list.
- QA checklist.
- Any assumptions.
```

## 9.2. Compact Screen Prompt

```txt
Apply UEConnect design system. Create {{SCREEN}} for {{FEATURE}} as {{OUTPUT_FORMAT}}. Vietnamese UI, English code names. Mobile-first PWA + desktop behavior. Use #124874, neutral-first, blue-only gradients, documented components/states. Include layout, components, states, interactions, accessibility, responsive notes, microcopy and QA checklist. Avoid dating/Tinder styling and invented scope.
```

---

# 10. Component Prompt Template

## 10.1. Component Spec Prompt

```txt
Use the UEConnect design system.

Task:
Define the {{COMPONENT_NAME}} component.

Context:
- Product: UEConnect.
- UI language: Vietnamese.
- Code/component naming: English.
- Must follow component-primitives.md, component-variants.md, interaction-states.md and design-token-documentation.md.

Output:
Markdown component spec with:
1. Purpose
2. Anatomy
3. Props
4. Variants
5. Sizes
6. States
7. Responsive behavior
8. Accessibility
9. Token mapping
10. Usage examples
11. Anti-patterns
12. QA checklist

Constraints:
- Use HCMUE Blue #124874 only through semantic tokens.
- No arbitrary colors/radius/shadows.
- Touch target minimum 44px where interactive.
- Icon-only controls must have aria-label.
```

## 10.2. Component Code Prompt

```txt
Apply UEConnect design system.

Generate a Laravel Blade + TailwindCSS component for {{COMPONENT_NAME}}.

Requirements:
- English component/prop names.
- Vietnamese example UI copy.
- Token-based Tailwind classes.
- Variants: {{VARIANTS}}.
- Sizes: {{SIZES}}.
- States: default, hover, active, focus-visible, disabled, loading if applicable.
- Accessibility included.
- No arbitrary Tailwind values unless justified.
- No dating-app styling.
```

---

# 11. Feature Spec Prompt Template

```txt
Use the UEConnect Core Identity Prompt and feature-spec template.

Task:
Write the feature spec for {{FEATURE}}.

Product context:
UEConnect is a verified HCMUE-only student social PWA with trust, identity verification, feed, discovery, greeting, messaging, mentor, community, safety, moderation and admin operations.

Feature constraints:
{{CONSTRAINTS}}

Output in Markdown:
1. Overview
2. Goals
3. Non-goals
4. Actors / Roles
5. Entry Points
6. User Stories
7. Functional Requirements
8. State Machine
9. Permissions
10. UX / UI Requirements
11. Components
12. Data Requirements
13. Notifications
14. Edge Cases
15. Accessibility
16. Responsive Behavior
17. Analytics Events
18. Acceptance Criteria
19. QA Checklist
20. Open Questions

Rules:
- Vietnamese documentation.
- English technical identifiers.
- UI copy in Vietnamese.
- Do not invent unsupported business scope.
- Align with UEConnect state machine and permission model.
```

---

# 12. Page Spec Prompt Template

```txt
Use UEConnect design system and sitemap.

Task:
Write a page spec for {{PAGE_NAME}}.

Include:
- Route
- Auth/verification requirement
- User role access
- Layout per device
- Page sections
- Components
- Empty/loading/error/permission/offline states
- Primary actions
- Secondary actions
- Navigation behavior
- Data displayed
- Privacy rules
- Accessibility
- Responsive behavior
- Analytics events
- QA checklist

Constraints:
- Mobile-first PWA.
- Vietnamese UI copy.
- English route/component names.
- Do not add new top-level nav unless product docs require it.
```

---

# 13. UI Code Prompt Template

## 13.1. Blade + Tailwind Prompt

```txt
Use UEConnect design system.

Task:
Generate Laravel Blade + TailwindCSS UI for {{SCREEN_OR_COMPONENT}}.

Stack:
- Laravel
- Blade
- TailwindCSS
- Alpine.js if needed
- Livewire if needed
- Lucide-style icons

Rules:
- UI copy in Vietnamese.
- Code identifiers in English.
- Use HCMUE Blue #124874 via semantic classes/tokens.
- Neutral-first UI.
- Blue-only gradients only for rare brand moments.
- Mobile-first responsive.
- Accessible focus-visible state.
- 44px touch target.
- aria-label for icon-only controls.
- Include loading/empty/error states if relevant.
- Avoid arbitrary Tailwind values.
- Avoid dating/Tinder styling.

Output:
- Blade markup.
- Minimal CSS/Tailwind assumptions if needed.
- Notes on states and accessibility.
```

## 13.2. HTML Preview Prompt

```txt
Use UEConnect design system.

Create a single-file HTML preview for {{SCREEN_OR_FLOW}}.

Requirements:
- Vietnamese UI.
- Be Vietnam Pro font.
- CSS variables for design tokens.
- HCMUE Blue #124874.
- Neutral-first social UI.
- Blue-only gradients only for rare brand moments.
- Responsive mobile/desktop layout.
- Accessible semantic HTML.
- Include realistic states and sample data.
- No external JS framework unless requested.
- No dating-app language or colors.
```

---

# 14. Review Prompt Template

## 14.1. Design Review Prompt

```txt
Review this UI against the UEConnect design system.

Check:
1. Brand alignment
2. Color/token usage
3. Gradient policy
4. Typography
5. Spacing/radius/shadow
6. Component primitive/variant correctness
7. Interaction states
8. Motion appropriateness
9. Content tone
10. Accessibility
11. Responsive behavior
12. Product scope alignment
13. Dating-app risk
14. Admin/generic SaaS risk
15. Missing states

Output:
- Summary verdict
- Critical issues
- High-priority fixes
- Medium-priority improvements
- Token violations
- Suggested corrected copy
- Final checklist
```

## 14.2. Token Audit Prompt

```txt
Audit this UI for UEConnect token compliance.

Find:
- random hex colors
- arbitrary spacing
- arbitrary radius
- arbitrary shadow
- inconsistent status colors
- wrong gradient
- wrong font
- inaccessible contrast
- inconsistent component variants

Output:
A table with Issue / Current / Required Token / Severity / Fix.
```

## 14.3. Accessibility Review Prompt

```txt
Review this UEConnect screen for accessibility.

Check:
- semantic HTML
- keyboard navigation
- focus-visible
- form labels
- aria-labels
- modal/sheet focus trap
- color contrast
- touch targets
- reduced motion
- status not color-only
- screen reader copy
- mobile 320px support

Output:
- Pass/fail summary
- P0 issues
- P1 improvements
- corrected markup/copy where useful
```

---

# 15. Feature-specific Prompt Anchors

## 15.1. Authentication

```txt
Feature: Authentication.
Tone: trusted, simple, secure.
Use email hcmue.edu.vn only.
Support Microsoft Azure / Outlook edu login direction if applicable.
No generic Google-only edu assumption.
UI copy Vietnamese.
States: banned/suspended, not verified, profile incomplete, verified ready, admin route.
```

## 15.2. Verification Identity

```txt
Feature: Verification Identity.
Tone: serious, supportive, trust-heavy.
Evidence upload max 3 files, 5MB each, jpg/jpeg/png/pdf/webp/link.
Admin actions: approve, reject, need_more_information, mark_conflict, suspend_suspicious.
MSSV unique.
Evidence preview protected.
Keep old evidence after reject/approve.
Audit required.
```

## 15.3. Profile Management

```txt
Feature: Profile Management.
Role-aware profile for student, alumni, advisor, mentor.
Avatar required after verification.
Public profile supported.
Privacy controls required.
LinkedIn-like structured profile but student-friendly.
No dating-profile tone.
```

## 15.4. Home Feed / Post / Comment

```txt
Feature: Home Feed.
Threads/Instagram-like content-first feed.
Everyone verified can post.
Post image supported in MVP.
Admin/system announcement supported.
Vietnamese copy.
Comment design clean and accessible.
No anonymous post.
```

## 15.5. Discovery / Greeting

```txt
Feature: Discovery and Greeting.
Connection-focused, not dating.
Swipe gesture optional but buttons required.
Use "Gửi lời chào", "Bỏ qua", "Xem hồ sơ".
Greeting can include a short message.
If declined, cannot send again.
Accepted creates connection, conversation and notification.
No heart/match/crush language.
```

## 15.6. Messaging

```txt
Feature: Messaging.
Realtime required.
Only connected users can message directly.
Non-connected messages go to message request flow if allowed.
Support attachments/images, edit, delete, read receipts, typing indicator, block, report per message.
DB is source of truth; WebSocket is transport, not truth.
```

## 15.7. Notification

```txt
Feature: Notifications.
In-app + browser push only.
PWA installed widget/mobile behavior must be considered.
Notification retention 7 days.
Types: verification, greeting, message, mentor request, moderation.
Mark as read required.
Preview must be privacy-safe.
```

## 15.8. Mentor Matching

```txt
Feature: Mentor Matching.
Mentors can be alumni or academic advisors.
Mentor access can be requested and admin-approved or admin-granted.
Student request includes topic, question, goal, urgency.
Mentor actions: accept, decline, ask more info, pause availability.
Accepted request creates conversation.
Scheduling supported in architecture.
Do not make rating feel like marketplace/shopping app.
```

## 15.9. Career Pathway

```txt
Feature: Career Pathway.
Must cover many HCMUE faculties/programs, not only IT.
Use official/curated/mentor insight source labels.
No overpromise job outcomes.
Mobile-first pathway cards and detail pages.
```

## 15.10. Community / Club

```txt
Feature: Community / Club.
Admin creates communities.
User can suggest community.
Club manager is scoped operator.
Owner has full community control.
Community has posts, chat, resources, members.
Join process requires clear approval rules.
Suspended community shows locked state and reason.
Scoped permissions required.
```

## 15.11. Search / Filter

```txt
Feature: Search / Filter.
Search across UEers, posts, communities, mentors, pathways, resources.
Respect privacy, block, moderation and permission rules.
Mobile uses full-screen search and filter sheet.
Desktop can use top search, category tabs, filter sidebar.
```

## 15.12. Safety Reporting

```txt
Feature: Safety Reporting.
Report targets: profile, post, comment, message, community, mentor request, evidence abuse.
Reasons: spam, harassment, impersonation, sexual/dating content, copyright, personal info leak, scam, hate/offensive language, politically sensitive content, other.
Report description optional.
No duplicate report for same target while pending.
Report can auto-block.
```

## 15.13. Moderation

```txt
Feature: Moderation.
Report queue with priority.
Auto-hide applies to post/comment/profile/message/community post.
Actions: dismiss, hide, delete, restore, warn, suspend, ban.
Every action requires reason.
Hidden content shows placeholder.
Appeal supported.
Admin/moderator UI must be dense but accessible.
```

## 15.14. Admin Operations

```txt
Feature: Admin Operations.
Dashboard widgets for verified accounts, daily posts, mentor requests, greetings, reports, communities.
Permission-based access.
Admin can grant scoped permissions if allowed.
Audit log required for important actions.
SQL Server audit direction for DB-level changes.
UI must show consequences before destructive actions.
```

---

# 16. Prompt Recipes

## 16.1. Generate a New Screen Spec

```txt
Apply UEConnect design system.

Write a complete page spec for the {{SCREEN}} screen in {{FEATURE}}.

Use Vietnamese documentation. UI copy Vietnamese. Technical names English.

Include:
- route
- access rules
- layout mobile/desktop
- components
- states
- data displayed
- interactions
- permissions
- privacy
- accessibility
- responsive behavior
- analytics events
- QA checklist

Use neutral-first HCMUE social UI, #124874, blue-only gradients, no dating styling, no invented scope.
```

## 16.2. Generate a Blade Component

```txt
Apply UEConnect design system.

Generate a Laravel Blade component named {{COMPONENT_NAME}}.

Include:
- props
- variants
- sizes
- states
- Tailwind classes
- accessibility attributes
- Vietnamese usage examples

Use token-based classes, HCMUE Blue #124874, neutral-first UI, focus-visible, 44px touch targets, no arbitrary values.
```

## 16.3. Generate a Mobile Screen

```txt
Apply UEConnect design system.

Design the mobile PWA version of {{SCREEN}}.

Requirements:
- 320px minimum width
- bottom nav or top header behavior
- safe-area aware
- single-column layout
- bottom sheet for filters/actions where appropriate
- Vietnamese UI
- accessible touch targets
- include loading/empty/error/locked states
- no hover-only actions
```

## 16.4. Generate Desktop/Admin Screen

```txt
Apply UEConnect design system.

Design the desktop/admin version of {{SCREEN}}.

Requirements:
- sidebar/topbar layout
- dense but readable tables/cards
- filters/search/pagination
- row actions with clear labels
- audit/action reason where required
- accessible keyboard/focus behavior
- no tiny text
- no generic SaaS styling
```

## 16.5. Review AI-generated UI

```txt
Review this output against UEConnect design system.

Check for:
- HCMUE Blue #124874 usage
- neutral-first layout
- blue-only gradient rule
- typography consistency
- component primitive/variant match
- interaction states
- accessibility
- responsive behavior
- Vietnamese content tone
- dating-app risk
- invented product scope

Return:
- verdict
- P0 fixes
- P1 fixes
- corrected copy
- token corrections
- final checklist
```

---

# 17. Token-efficient Prompt Packs

## 17.1. Pack A: General UI

```txt
UEConnect UI Pack:
Verified HCMUE-only student social PWA. Neutral-first, content-first, #124874, blue-only gradients, Vietnamese UI, English code names, Be Vietnam Pro/system sans, Lucide icons, mobile-first, accessible states, no dating/Tinder, no generic SaaS, follow tokens/components.
```

## 17.2. Pack B: Feature Spec

```txt
UEConnect Feature Spec Pack:
Write Vietnamese enterprise product docs. Use English technical identifiers. Include goals, actors, requirements, states, permissions, UI/UX, data, notifications, edge cases, accessibility, responsive, analytics, acceptance criteria. Do not invent scope. Align with UEConnect verified HCMUE social platform.
```

## 17.3. Pack C: Blade UI

```txt
UEConnect Blade Pack:
Laravel Blade + TailwindCSS + Alpine/Livewire if useful. UI Vietnamese, code English. Use semantic tokens, #124874, neutral surfaces, documented variants, responsive classes, focus-visible, aria labels, loading/empty/error states. Avoid arbitrary values and dating styling.
```

## 17.4. Pack D: Design Review

```txt
UEConnect Review Pack:
Audit brand, tokens, gradient policy, typography, spacing, radius, shadow, components, states, accessibility, responsive, content tone, scope and dating-risk. Return P0/P1 issues and exact fixes.
```

---

# 18. AI Output Quality Rules

## 18.1. Required In Good Output

Good AI output should include:

```txt
- correct product positioning
- correct Vietnamese UI language
- correct HCMUE Blue usage
- neutral-first visual hierarchy
- documented components
- state coverage
- responsive behavior
- accessibility notes
- privacy/safety awareness
- no invented unrelated scope
```

## 18.2. Red Flags

Reject or revise output if it has:

```txt
- red/pink/orange gradient
- Tinder-like copy
- heart/match/crush visuals
- generic admin dashboard look for student pages
- heavy gradient backgrounds everywhere
- random colors outside tokens
- tiny inaccessible text
- no empty/error/loading states
- no mobile behavior
- no focus/accessibility notes
- English UI copy mixed randomly
- invented recruiter portal or dating feature
```

## 18.3. Acceptable Assumptions

AI may assume:

```txt
- user is verified if screen is inside /app
- UI is Vietnamese
- code identifiers are English
- PWA mobile-first behavior is required
- component system exists
```

AI must not assume:

```txt
- public registration is open to non-HCMUE users
- Google OAuth is the only edu login method
- UEConnect is dating-oriented
- admin can bypass audit without reason
- private data can appear in notifications
- mobile can omit important business logic
```

---

# 19. Common Task Prompts

## 19.1. Prompt: Design Home Feed

```txt
Use UEConnect UI Pack.

Design Home Feed for verified HCMUE users.

Include:
- mobile PWA layout
- desktop layout with sidebar and optional right rail
- post composer
- post card
- image post
- comment preview
- admin/system announcement
- loading skeleton
- empty feed
- error state
- moderation placeholder
- Vietnamese microcopy
- accessibility notes
- QA checklist

Avoid dating/social drama tone.
```

## 19.2. Prompt: Design Messaging

```txt
Use UEConnect UI Pack.

Design Messaging UI.

Include:
- mobile conversation list and detail
- desktop two-pane layout
- realtime connection states
- typing indicator
- read receipts
- attachment upload
- message edit/delete
- failed message retry
- blocked/restricted conversation state
- report message action
- Vietnamese copy
- accessibility and responsive behavior

DB is source of truth. WebSocket is transport only.
```

## 19.3. Prompt: Design Admin Moderation Queue

```txt
Use UEConnect UI Pack.

Design Admin Moderation Queue.

Tone: precise, operational, serious.

Include:
- dashboard/list view
- priority queue
- target type
- report reason
- status
- assigned moderator
- action menu
- detail drawer
- action reason required
- audit log note
- stale/conflict state
- accessible table
- mobile fallback as cards

Actions: dismiss, hide, delete, restore, warn, suspend, ban.
```

## 19.4. Prompt: Design Community Club

```txt
Use UEConnect UI Pack.

Design Community / Club feature.

Include:
- community list
- community detail
- posts tab
- resources tab
- chat tab
- members tab
- join/request flow
- club manager scoped permissions
- owner/admin controls
- suspended community state
- resource moderation state
- mobile and desktop layouts
- Vietnamese copy
```

---

# 20. Prompt Compression Strategy

## 20.1. When Token Budget Is Large

Use:

```txt
Full Core Prompt + Task Prompt + Negative Prompt + Output Format.
```

Best for:

```txt
new feature
complex admin screen
safety/moderation
identity verification
messaging
```

## 20.2. When Token Budget Is Medium

Use:

```txt
Compact Core Prompt + Task Prompt + Key Constraints.
```

Best for:

```txt
page spec
component spec
layout variant
responsive version
```

## 20.3. When Token Budget Is Small

Use:

```txt
Ultra-short Anchor + one clear task + output format.
```

Example:

```txt
Apply UEConnect design system. Write the empty/error/loading states for Mentor Matching in Vietnamese, with accessibility and responsive notes.
```

## 20.4. Do Not Waste Tokens On

Avoid repeating:

```txt
full project history
every feature list
every token value
all previous decisions
entire markdown files
```

Instead say:

```txt
Follow canonical UEConnect design docs.
Use the Core Identity Prompt.
Use the feature-specific anchor for Messaging.
```

Đừng đem cả thư viện đổ vào prompt chỉ để nhờ AI vẽ một cái button. Máy cũng có giới hạn chịu đựng, dù ít than hơn người.

---

# 21. Agent Workflow

## 21.1. Recommended Workflow

```txt
1. Select task type.
2. Pick Core Prompt level.
3. Add feature-specific anchor.
4. Add task requirements.
5. Specify output format.
6. Add negative prompt.
7. Generate output.
8. Run review prompt.
9. Fix P0/P1 issues.
10. Save into correct docs folder.
```

## 21.2. Multi-agent Workflow

If using multiple AI agents:

| Agent               | Responsibility                       |
| ------------------- | ------------------------------------ |
| Product Agent       | Scope, requirements, state machine   |
| UX Agent            | Flow, page structure, microcopy      |
| UI Agent            | Layout, components, tokens           |
| Accessibility Agent | WCAG, keyboard, focus, screen reader |
| Frontend Agent      | Blade/Tailwind implementation        |
| QA Agent            | Acceptance criteria and test cases   |

Rule:

```txt
All agents must use the same Core Identity Prompt.
```

Không để mỗi agent tự “cảm nhận thương hiệu”. Một team người đã đủ loạn rồi, đừng nhân bản sự loạn bằng AI.

## 21.3. Review Loop

After AI output:

```txt
Run UEConnect Review Pack.
Fix P0.
Fix P1 if time allows.
Update docs.
Do not ship raw AI output without review.
```

---

# 22. Prompt Examples

## 22.1. Good Prompt

```txt
Apply UEConnect design system.

Write a detailed page spec for the mobile and desktop Notification Center.

Context:
- UEConnect is a verified HCMUE-only student social PWA.
- Notifications are in-app and browser push only.
- Retention is 7 days.
- Types include verification, greeting, message, mentor request and moderation.
- UI copy must be Vietnamese.
- Code identifiers English.

Include:
- route
- layout
- notification item anatomy
- read/unread states
- mark as read
- browser push permission states
- empty/loading/error/offline states
- accessibility
- responsive behavior
- QA checklist

Avoid sensitive previews and dating-style copy.
```

## 22.2. Bad Prompt

```txt
Make notification page modern, beautiful, with cool gradients.
```

Why bad:

```txt
- no product context
- no HCMUE constraint
- no Vietnamese UI
- no states
- no privacy
- no accessibility
- asks for generic gradient nonsense
```

## 22.3. Corrected Prompt

```txt
Apply UEConnect UI Pack. Design Notification Center for verified HCMUE users. Use neutral-first UI, #124874, Vietnamese copy, privacy-safe previews, mobile-first PWA behavior, unread/read states, mark as read, browser push permission states, loading/empty/error states, accessibility and QA checklist. Avoid non-blue gradients and dating-style language.
```

---

# 23. Prompt Anti-patterns

Do not prompt:

```txt
- Make it look like Tinder but for students.
- Use beautiful red-blue gradient.
- Make it super modern with glassmorphism everywhere.
- Just generate UI, no need states.
- Ignore accessibility for now.
- Use any colors you think fit.
- Make admin dashboard like typical SaaS.
- Add whatever features you think are useful.
- Keep it short but include everything.
```

Use instead:

```txt
- Make it connection-focused, not dating-oriented.
- Use blue-only gradient only for rare brand moments.
- Use neutral-first social UI.
- Include required states.
- Include accessibility baseline.
- Use UEConnect tokens.
- Follow scope and sitemap.
```

---

# 24. QA Checklist For Prompts

Before sending prompt to AI:

```txt
[ ] Prompt names UEConnect.
[ ] Prompt says HCMUE-only / HCMUE community.
[ ] Prompt includes HCMUE Blue #124874.
[ ] Prompt says blue-only gradient.
[ ] Prompt says Vietnamese UI.
[ ] Prompt says English code identifiers if code/spec.
[ ] Prompt says mobile-first PWA if UI.
[ ] Prompt references relevant feature/screen.
[ ] Prompt defines output format.
[ ] Prompt includes required states.
[ ] Prompt includes accessibility expectation.
[ ] Prompt forbids dating/Tinder styling.
[ ] Prompt forbids invented scope.
[ ] Prompt is not longer than necessary.
```

---

# 25. QA Checklist For AI Output

After AI generates output:

```txt
[ ] Output follows UEConnect positioning.
[ ] HCMUE Blue is used correctly.
[ ] Gradient rule is respected.
[ ] UI copy is Vietnamese.
[ ] Technical names are English.
[ ] Layout is mobile-first.
[ ] Desktop behavior is defined if needed.
[ ] Components match primitives/variants.
[ ] Empty/loading/error states exist.
[ ] Permission/locked states exist where needed.
[ ] Accessibility notes exist.
[ ] Touch targets and focus states are mentioned.
[ ] No dating language.
[ ] No generic SaaS/admin smell in student UI.
[ ] No random colors or arbitrary styling.
[ ] No invented major feature.
[ ] Output can be implemented or reviewed directly.
```

---

# 26. Final Rule

Prompting AI for UEConnect is not “ask it to make something pretty”.

Prompting AI for UEConnect means giving it:

```txt
product identity
brand constraints
design tokens
component system
state expectations
accessibility rules
responsive rules
content tone
scope boundaries
output format
review criteria
```

The shortest useful prompt is not the shortest sentence.

The shortest useful prompt is the shortest prompt that prevents the AI from confidently producing the wrong product.

Nếu prompt không nhắc `#124874`, blue-only gradient, mobile-first, Vietnamese UI, accessibility và “not dating”, thì đừng ngạc nhiên khi AI trả về một app nửa Tinder nửa dashboard. Máy móc không đọc được ý định mơ hồ. Con người cũng vậy, chỉ là con người giấu tốt hơn.

```
```
