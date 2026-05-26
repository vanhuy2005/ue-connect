---
title: "Responsive Rules"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UI Design / Frontend / QA"
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
  - "19-design-token-documentation.md"
related:
  - "../03-product/sitemap.md"
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
  - "../03-product/feature-specs/profile-management.md"
  - "../03-product/feature-specs/onboarding.md"
  - "../03-product/feature-specs/home-feed.md"
  - "../03-product/feature-specs/post-comment.md"
  - "../03-product/feature-specs/discovery-profile.md"
  - "../03-product/feature-specs/greeting-connection.md"
  - "../03-product/feature-specs/messaging.md"
  - "../03-product/feature-specs/notification.md"
  - "../03-product/feature-specs/mentor-matching.md"
  - "../03-product/feature-specs/community-club.md"
  - "../03-product/feature-specs/career-pathway.md"
  - "../03-product/feature-specs/search-filter.md"
  - "../03-product/feature-specs/safety-reporting.md"
  - "../03-product/feature-specs/moderation.md"
  - "../03-product/feature-specs/admin-operations.md"
---

# Responsive Rules

## 1. Purpose

Responsive Rules định nghĩa cách UEConnect hiển thị và hoạt động trên nhiều kích thước màn hình:

- Mobile browser.
- Installed PWA.
- Tablet.
- Laptop.
- Desktop.
- Large desktop.
- Admin dashboard dense view.
- Mobile-first student experience.
- Desktop-friendly admin and moderation workflows.

UEConnect định hướng là **PWA**, nên responsive không chỉ là “co màn hình lại cho vừa”. Responsive ở đây là cách sản phẩm thích nghi với thiết bị, input method, safe-area, navigation, layout density và hành vi sử dụng thực tế.

Nếu desktop là bản chính rồi mobile chỉ là bản bị ép nhỏ lại, thì đó không phải responsive. Đó là tra tấn giao diện bằng CSS media query.

---

## 2. Responsive Principles

### 2.1. Mobile-first

UEConnect phải thiết kế và code theo hướng mobile-first.

Lý do:

```txt
- Sinh viên dùng điện thoại rất nhiều.
- PWA cần cảm giác gần giống app.
- Discovery, messaging, notification, community đều là mobile-heavy.
- Auth, verification, onboarding cần làm tốt trên mobile.
````

Mobile-first không có nghĩa là desktop bị bỏ rơi.

Nó nghĩa là:

```txt
- core flow phải hoạt động tốt ở màn hình nhỏ trước
- sau đó mở rộng layout cho tablet/desktop
- không ép mobile gánh layout desktop thu nhỏ
```

### 2.2. Content First, Decoration Later

Khi màn hình nhỏ:

```txt
ưu tiên nội dung chính
ẩn bớt decoration
giảm layout phức tạp
chuyển sidebar thành sheet/drawer
chuyển table thành card/list
giảm số cột
```

Không được hy sinh:

```txt
CTA chính
trạng thái quan trọng
lỗi form
permission reason
safety warning
```

### 2.3. Same Product, Different Layout

Mobile và desktop có thể khác layout, nhưng không được khác business logic.

Ví dụ:

```txt
Mobile community filter = bottom sheet
Desktop community filter = sidebar/topbar
```

Nhưng filter logic giống nhau.

Không được:

```txt
Mobile thiếu moderation reason
Desktop có reason
Mobile cho gửi thiếu field
Desktop bắt buộc field
```

Responsive không phải cái cớ để feature mất não theo chiều rộng màn hình.

### 2.4. Touch and Pointer Aware

UEConnect phải hỗ trợ:

```txt
touch input
mouse input
keyboard input
screen reader
```

Không được phụ thuộc hoàn toàn vào:

```txt
hover
swipe
right click
drag only
```

### 2.5. Density Changes by Context

Student-facing UI:

```txt
comfortable
card-based
touch-friendly
```

Admin-facing UI:

```txt
denser
table-friendly
still readable
still accessible
```

Admin dense không có nghĩa là chữ bé như hợp đồng bảo hiểm.

---

# 3. Breakpoint System

## 3.1. Breakpoint Tokens

Recommended Tailwind-compatible breakpoints:

| Token |  Width | Usage                       |
| ----- | -----: | --------------------------- |
| `xs`  |  360px | Small mobile support        |
| `sm`  |  640px | Large mobile / small tablet |
| `md`  |  768px | Tablet                      |
| `lg`  | 1024px | Laptop / desktop start      |
| `xl`  | 1280px | Desktop                     |
| `2xl` | 1536px | Large desktop               |

Minimum supported width:

```txt
320px
```

Primary design references:

```txt
mobile: 390px
tablet: 768px
desktop: 1440px
```

## 3.2. Breakpoint Philosophy

Use breakpoints for meaningful layout changes, not random pixel panic.

Good:

```txt
mobile: 1 column
md: 2 columns
lg: sidebar + content
xl: content + right rail
```

Bad:

```txt
@media 917px because one button looked weird on your laptop
```

Một breakpoint sinh ra từ cảm xúc nhất thời sẽ sống mãi trong codebase như lời nguyền.

## 3.3. Tailwind Direction

Use mobile-first classes:

```txt
default = mobile
sm:
md:
lg:
xl:
2xl:
```

Example:

```html
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
  ...
</div>
```

---

# 4. Layout Containers

## 4.1. App Container Width

Recommended maximum content widths:

| Surface               |           Max Width |
| --------------------- | ------------------: |
| Auth form             |       420px - 480px |
| Verification form     |               720px |
| Onboarding            |       720px - 960px |
| Feed center column    |       640px - 720px |
| Profile page          |      960px - 1120px |
| Community detail      |     1120px - 1280px |
| Career pathway detail |      960px - 1120px |
| Admin dashboard       |     1280px - 1440px |
| Admin tables          | fluid with safe max |

## 4.2. Page Padding

Recommended page padding:

| Width           | Padding |
| --------------- | ------: |
| 320px - 639px   |    16px |
| 640px - 767px   |    20px |
| 768px - 1023px  |    24px |
| 1024px - 1279px |    32px |
| 1280px+         |    40px |

## 4.3. Content Alignment

Student-facing core surfaces:

```txt
centered content column
comfortable reading width
cards stacked on mobile
```

Admin surfaces:

```txt
full-width layout
dense grid/table
sticky filters where useful
```

## 4.4. Avoid Full-width Text

Long text should not stretch across large screens.

Maximum readable text width:

```txt
65-75 characters
```

Use:

```txt
max-w-prose
```

or equivalent.

---

# 5. Grid System

## 5.1. Core Grid

Use CSS grid/flex with responsive columns.

Common patterns:

```txt
1 column mobile
2 columns tablet
3 columns desktop
4 columns large desktop only when cards are small
```

## 5.2. Card Grid

For community/mentor/pathway cards:

```txt
mobile: 1 column
md: 2 columns
xl: 3 columns
```

Avoid 4 columns unless cards are compact and content stays readable.

## 5.3. Dashboard Grid

Admin dashboard widgets:

```txt
mobile: 1 column
md: 2 columns
lg: 3 columns
xl: 4 columns
```

## 5.4. Detail Layout

For detail pages:

```txt
mobile: stacked
lg: main content + right sidebar
```

Example:

```txt
Community detail:
mobile:
- header
- tabs
- content

desktop:
- main content
- right rail with community info/actions
```

## 5.5. Right Rail Rule

Right rail is optional enhancement.

Content in right rail must not be critical-only.

If important, it must appear in mobile layout too.

---

# 6. Navigation Responsive Rules

## 6.1. Public Landing Navigation

Mobile:

```txt
top header
icon mark or compact horizontal logo
menu button
drawer menu
primary CTA visible if space allows
```

Desktop:

```txt
horizontal logo
nav links
primary CTA
```

## 6.2. App Navigation

Mobile PWA:

```txt
bottom navigation for primary app sections
top header for title/actions/search
```

Desktop:

```txt
left sidebar or top navigation
optional right utility area
```

## 6.3. Mobile Bottom Navigation

Recommended primary items:

```txt
Bảng tin
Khám phá
Tin nhắn
Cộng đồng
Hồ sơ
```

Alternative if Mentor must be prominent:

```txt
Bảng tin
Khám phá
Tin nhắn
Mentor
Hồ sơ
```

Community and Mentor can also be accessible through Home/More depending final IA, but MVP must not bury P0 features.

## 6.4. Bottom Nav Rules

Bottom nav must:

```txt
- have icon + visible label
- show active state
- support safe-area inset
- not exceed 5 primary items
- have touch target >= 44px
- include unread badge where useful
```

Do not use:

```txt
icon-only mystery meat nav
```

Một cái icon không label là bài kiểm tra đoán hình. Người dùng không đăng ký thi.

## 6.5. Desktop Sidebar

Desktop app sidebar can include:

```txt
UEConnect logo
Bảng tin
Khám phá
Tin nhắn
Cộng đồng
Mentor
Lộ trình
Thông báo
Hồ sơ
Cài đặt
```

Rules:

```txt
- current route uses active state
- sidebar can collapse at lg if needed
- labels remain visible unless collapsed intentionally
- icon-only collapsed state must have tooltips/aria-labels
```

## 6.6. Admin Navigation

Mobile admin is supported but not primary.

Admin desktop:

```txt
left sidebar
topbar search/actions
main content table/dashboard
```

Admin mobile:

```txt
topbar + drawer nav
cards instead of wide tables where possible
```

Admin should be usable on mobile for emergency review, but optimized for desktop. Vì duyệt audit log trên điện thoại là một dạng tự hành hạ, nhưng đời vẫn có lúc cần.

---

# 7. Header Rules

## 7.1. Mobile Header

Mobile header should include:

```txt
page title
primary contextual action
search/filter icon if needed
notification icon if appropriate
```

Height:

```txt
56px - 64px
```

## 7.2. Desktop Header

Desktop header can include:

```txt
page title
global search
notification icon
profile menu
primary CTA
```

Height:

```txt
64px - 72px
```

## 7.3. Sticky Header

Sticky header allowed for:

```txt
mobile app shell
messaging
community detail
search/filter
admin table
```

Rules:

```txt
- avoid covering content
- account for safe area
- shadow/border on scroll
```

## 7.4. Logo in Header

Mobile:

```txt
icon mark or hidden if page title dominates
```

Desktop:

```txt
horizontal logo in sidebar/topbar
```

Do not place full primary logo in dense app header.

---

# 8. Safe Area & PWA Rules

## 8.1. Safe Area Insets

For installed PWA, respect:

```css
env(safe-area-inset-top)
env(safe-area-inset-right)
env(safe-area-inset-bottom)
env(safe-area-inset-left)
```

Apply to:

```txt
top header
bottom nav
bottom sheet
toast
floating action button
message composer
```

## 8.2. Bottom Nav Safe Area

```css
padding-bottom: env(safe-area-inset-bottom);
```

## 8.3. Message Composer Safe Area

Message composer must not be hidden behind mobile browser/PWA controls.

Rules:

```txt
- sticky bottom
- safe-area padding
- keyboard-aware if feasible
- attachment panel not clipped
```

## 8.4. PWA Standalone Mode

When display mode is standalone:

```txt
- no assumption of browser back button
- provide in-app back where needed
- safe-area matters more
- splash/app icon must align brand system
```

## 8.5. Offline State

Offline banner should adapt:

Mobile:

```txt
compact top/bottom banner
```

Desktop:

```txt
top inline banner
```

---

# 9. Responsive Typography

## 9.1. Type Scaling

Recommended type sizes:

| Text          |      Mobile |     Desktop |
| ------------- | ----------: | ----------: |
| Display       |        32px | 48px - 56px |
| Page title    | 24px - 28px | 32px - 40px |
| Section title |        20px | 24px - 28px |
| Card title    | 16px - 18px | 18px - 20px |
| Body          |        16px |        16px |
| Small         | 13px - 14px |        14px |
| Caption       | 12px - 13px | 12px - 13px |

## 9.2. Mobile Text Rules

```txt
- avoid body text below 16px
- keep line length short
- avoid long all-caps
- headings can wrap naturally
- buttons should not have long sentence labels
```

## 9.3. Desktop Text Rules

```txt
- do not over-enlarge body text
- use max width for reading
- increase spacing rather than stretching text
```

## 9.4. Long Vietnamese Text

Vietnamese text can wrap differently.

Components must handle:

```txt
long names
long faculty/major names
long community names
long status copy
long admin action reason
```

Use:

```txt
line-clamp where appropriate
word-break carefully
tooltip/detail for truncated text
```

Do not truncate critical information without way to view full content.

---

# 10. Responsive Spacing

## 10.1. Spacing Scale by Device

Mobile:

```txt
compact but touch-friendly
section gap: 16px - 24px
card padding: 16px
page padding: 16px
```

Tablet:

```txt
section gap: 24px - 32px
card padding: 20px - 24px
```

Desktop:

```txt
section gap: 32px - 48px
card padding: 24px
page padding: 32px - 40px
```

## 10.2. Do Not Over-compress Mobile

Mobile is smaller, but controls still need room.

Do not:

```txt
reduce tap target below 44px
use tiny icons
remove helper/error text
hide important status
```

## 10.3. Dense Admin Spacing

Admin can use:

```txt
smaller row height
compact badges
smaller card padding
```

But must keep:

```txt
readability
focus visibility
touch/click target for row actions
```

---

# 11. Component Responsive Rules

## 11.1. Buttons

Mobile:

```txt
primary form CTA often full-width
height 44px - 48px
```

Desktop:

```txt
button can be auto width
height 40px - 44px
```

Dialog actions:

Mobile:

```txt
stack vertically if text long
danger action clear
```

Desktop:

```txt
align right
horizontal pair
```

## 11.2. Icon Buttons

Mobile:

```txt
touch target >= 44px
```

Desktop:

```txt
40px target acceptable
```

## 11.3. Cards

Mobile:

```txt
full-width stacked
padding 16px
```

Tablet/Desktop:

```txt
grid or multi-column where useful
padding 20px - 24px
```

## 11.4. Modals

Mobile:

```txt
use bottom sheet or full-screen modal
```

Desktop:

```txt
center modal
max width based on content
```

Recommended modal widths:

| Modal Type   |  Desktop Width |
| ------------ | -------------: |
| confirmation |  420px - 520px |
| form         |  520px - 640px |
| preview      |  720px - 960px |
| admin detail | 720px - 1024px |

## 11.5. Bottom Sheets

Use on mobile for:

```txt
filters
action menus
share/report
notification panel
profile quick actions
```

Desktop equivalent:

```txt
dropdown
side drawer
modal
sidebar
```

## 11.6. Dropdowns

Mobile:

```txt
convert long dropdown/action menu to sheet
```

Desktop:

```txt
dropdown menu
```

## 11.7. Tabs

Mobile:

```txt
scrollable horizontal tabs or segmented tabs
```

Desktop:

```txt
line tabs or vertical tabs where useful
```

Rules:

```txt
- active tab visible
- no tab hidden without scroll cue
- do not cram too many tabs
```

## 11.8. Tables

Mobile:

```txt
convert to cards if possible
or horizontal scroll for admin-only dense table
```

Desktop:

```txt
real table
sticky header optional
```

## 11.9. Forms

Mobile:

```txt
single column
full-width inputs
sticky submit optional for long forms
```

Desktop:

```txt
1-2 columns depending content
grouped sections
```

Verification/profile forms should stay readable rather than aggressively two-column.

---

# 12. Auth Responsive Rules

## 12.1. Mobile Auth

Layout:

```txt
logo
title
description
form
secondary links
```

Rules:

```txt
- single column
- no decorative side panel
- full-width CTA
- keyboard-friendly
- avoid form hidden behind mobile keyboard
```

## 12.2. Desktop Auth

Layout options:

```txt
centered card
or split layout with brand panel
```

Recommended:

```txt
left brand/illustration panel
right auth form
```

But form must remain focus.

## 12.3. Auth Width

Form max:

```txt
420px - 480px
```

## 12.4. Auth Background

Mobile:

```txt
simple white/light blue
```

Desktop:

```txt
soft blue gradient/brand panel allowed
```

No heavy decorative background behind form.

---

# 13. Verification Responsive Rules

## 13.1. Mobile Verification

Layout:

```txt
step indicator compact
form sections stacked
evidence upload cards
sticky bottom submit if useful
```

Rules:

```txt
- evidence upload must be easy to tap
- helper text visible
- preview opens full-screen/sheet
- errors appear near fields
```

## 13.2. Desktop Verification

Layout:

```txt
form column + side guidance panel
or centered 720px flow
```

Admin review:

```txt
left user info/evidence
right decision panel
```

## 13.3. Evidence Preview

Mobile:

```txt
full-screen preview modal
```

Desktop:

```txt
large modal or split review panel
```

Must not expose storage path. Vâng, responsive cũng không được làm lộ file riêng tư, thật bất ngờ chưa.

---

# 14. Onboarding Responsive Rules

## 14.1. Mobile Onboarding

Layout:

```txt
single card/step
large touch targets
progress visible
skip optional where allowed
```

## 14.2. Desktop Onboarding

Layout:

```txt
centered panel
optional side illustration
```

## 14.3. Role-based Onboarding

Onboarding steps can differ by role, but layout rules remain consistent.

```txt
student
alumni
advisor
mentor
```

## 14.4. Selection Cards

Mobile:

```txt
1 column
```

Tablet:

```txt
2 columns
```

Desktop:

```txt
2-3 columns
```

---

# 15. Home Feed Responsive Rules

## 15.1. Mobile Feed

Layout:

```txt
top header
composer entry
feed cards stacked
bottom nav
```

Rules:

```txt
- one column
- card width full
- action buttons reachable
- comments can open inline or sheet
```

## 15.2. Desktop Feed

Layout:

```txt
left nav/sidebar
center feed column
right rail optional
```

Recommended:

```txt
center column: 640px - 720px
right rail: profile/community suggestions
```

## 15.3. Feed Composer

Mobile:

```txt
compact composer opens full-screen/sheet
```

Desktop:

```txt
inline composer/card
```

## 15.4. Post Images

Mobile:

```txt
full card width
avoid horizontal overflow
```

Desktop:

```txt
constrained to feed column
```

## 15.5. Comments

Mobile:

```txt
comment composer sticky inside post detail or bottom sheet
```

Desktop:

```txt
inline under post
```

---

# 16. Discovery Responsive Rules

## 16.1. Mobile Discovery

Layout:

```txt
single discovery card
bottom actions
filters as sheet
profile detail as full page/sheet
```

Actions:

```txt
Bỏ qua
Gửi lời chào
Xem hồ sơ
```

## 16.2. Desktop Discovery

Layout:

```txt
card stack/grid
filter sidebar
profile preview panel optional
```

## 16.3. Swipe Rules

Mobile:

```txt
swipe allowed but buttons required
```

Desktop:

```txt
buttons primary
keyboard support
```

## 16.4. Avoid Dating Layout

Do not use:

```txt
huge heart/pass buttons
red/green dating decision language
match celebration
```

Use:

```txt
connection-focused actions
blue academic style
```

---

# 17. Greeting Responsive Rules

## 17.1. Greeting Composer

Mobile:

```txt
bottom sheet or full-screen modal
```

Desktop:

```txt
center modal
```

## 17.2. Greeting List

Mobile:

```txt
stacked cards
```

Desktop:

```txt
list + detail panel optional
```

## 17.3. Greeting Status

Status must remain visible on all devices:

```txt
Đang chờ phản hồi
Đã kết nối
Đã phản hồi
Đã hết hạn
```

---

# 18. Messaging Responsive Rules

## 18.1. Mobile Messaging

Layout:

```txt
conversation list page
conversation detail page
message composer sticky bottom
```

Do not show two-pane layout on small screens.

## 18.2. Desktop Messaging

Layout:

```txt
left conversation list
right conversation detail
optional right profile/context rail
```

Recommended:

```txt
conversation list: 320px - 380px
message panel: flexible
right rail: 280px - 360px optional
```

## 18.3. Tablet Messaging

Options:

```txt
two-pane if width allows
otherwise mobile pattern
```

## 18.4. Message Composer

Mobile:

```txt
sticky bottom
safe-area aware
keyboard aware
attachment button accessible
```

Desktop:

```txt
bottom of conversation panel
```

## 18.5. Attachments

Mobile:

```txt
attachment options in bottom sheet
preview full-screen
```

Desktop:

```txt
popover/modal preview
```

## 18.6. Realtime Banner

Mobile:

```txt
compact banner above message list/composer
```

Desktop:

```txt
inline top of conversation
```

## 18.7. Long Messages

Messages must:

```txt
wrap safely
not overflow horizontally
support long URLs with break-word
```

---

# 19. Notification Responsive Rules

## 19.1. Mobile Notifications

Layout:

```txt
notification center page
or bottom sheet/panel from header
```

Notification item:

```txt
icon/type
title/short copy
time
read state
```

## 19.2. Desktop Notifications

Layout:

```txt
dropdown panel from topbar
full notification page for all
```

Dropdown width:

```txt
360px - 420px
```

## 19.3. Push Prompt

Mobile:

```txt
bottom sheet/card
```

Desktop:

```txt
inline card/banner
```

Must include:

```txt
Bật thông báo
Để sau
```

---

# 20. Mentor Responsive Rules

## 20.1. Mentor Discovery

Mobile:

```txt
single column mentor cards
filters as bottom sheet
```

Desktop:

```txt
filter sidebar + mentor grid/list
```

## 20.2. Mentor Profile

Mobile:

```txt
stacked profile sections
sticky CTA optional
```

Desktop:

```txt
main profile content + right CTA/availability panel
```

## 20.3. Mentor Request Form

Mobile:

```txt
single column
step/section grouping
full-width submit
```

Desktop:

```txt
centered form or two-column with guidance
```

## 20.4. Scheduling Future

Mobile:

```txt
calendar list/day picker
```

Desktop:

```txt
calendar grid
```

---

# 21. Community Responsive Rules

## 21.1. Community List

Mobile:

```txt
single column cards
search top
filters sheet
```

Tablet:

```txt
2-column cards
```

Desktop:

```txt
filter sidebar/topbar + 2-3 column cards
```

## 21.2. Community Detail

Mobile:

```txt
community header
join/status CTA
horizontal tabs
content stacked
```

Desktop:

```txt
header
tabs
main content
right rail with info/actions
```

## 21.3. Community Chat

Mobile:

```txt
similar to messaging detail
composer safe-area aware
```

Desktop:

```txt
chat panel within community tab
```

## 21.4. Community Resources

Mobile:

```txt
resource cards/list
upload via sheet/full-screen form
```

Desktop:

```txt
resource table/list/grid depending type
```

## 21.5. Community Members

Mobile:

```txt
member list cards
actions in sheet/dropdown
```

Desktop:

```txt
table/list with role badges
```

## 21.6. Suspended Community

Locked state must appear clearly on all devices.

Mobile:

```txt
top alert + disabled tabs/actions
```

Desktop:

```txt
page banner + disabled right rail actions
```

---

# 22. Career Pathway Responsive Rules

## 22.1. Pathway List

Mobile:

```txt
single column cards
filter sheet
```

Tablet/Desktop:

```txt
2-3 column cards
filter sidebar/topbar
```

## 22.2. Pathway Detail

Mobile:

```txt
stacked sections
sticky save/request mentor CTA optional
accordion for long sections
```

Desktop:

```txt
main content + table of contents/right rail
```

## 22.3. Program / Faculty Coverage

Because HCMUE has many faculties and academic programs, filters must handle long names.

Mobile:

```txt
searchable select/combobox in sheet
```

Desktop:

```txt
filter sidebar with search
```

## 22.4. Resource Lists

Mobile:

```txt
cards
```

Desktop:

```txt
cards/table depending density
```

---

# 23. Search & Filter Responsive Rules

## 23.1. Global Search

Mobile:

```txt
full-screen search page
search input at top
category tabs scrollable
results stacked
filters in bottom sheet
```

Desktop:

```txt
topbar search dropdown preview
full search page with category tabs and filters
```

## 23.2. Filter UI

Mobile:

```txt
bottom sheet
Apply button
Clear all button
```

Desktop:

```txt
sidebar or top filter bar
instant/debounced apply depending module
```

## 23.3. Search Results

Mobile:

```txt
single column result cards
```

Desktop:

```txt
grouped sections
optional multi-column only if card content short
```

## 23.4. Active Filter Chips

Mobile:

```txt
horizontal scroll chips
```

Desktop:

```txt
wrap chips
```

Removable chip must remain tappable.

---

# 24. Safety / Reporting Responsive Rules

## 24.1. Report Modal

Mobile:

```txt
bottom sheet or full-screen modal
```

Desktop:

```txt
center modal
```

## 24.2. Reason Selection

Mobile:

```txt
radio list with large touch targets
```

Desktop:

```txt
radio list/cards
```

## 24.3. Block Confirmation

Mobile:

```txt
clear title/consequence
stacked actions
danger CTA separated
```

Desktop:

```txt
standard danger modal
```

## 24.4. Appeal Form

Mobile:

```txt
single column
textarea comfortable
```

Desktop:

```txt
modal/page form
```

---

# 25. Admin Responsive Rules

## 25.1. Admin Primary Target

Admin optimized for:

```txt
tablet landscape
laptop
desktop
large desktop
```

Admin must still be usable on mobile for urgent operations, but not every dense table needs perfect tiny-screen elegance.

## 25.2. Admin Dashboard

Mobile:

```txt
metric cards stacked
nav drawer
tables become cards
filters in sheet
```

Desktop:

```txt
sidebar nav
metric grid
tables
right detail drawer
```

## 25.3. Admin Tables

Desktop:

```txt
real table
sticky header
pagination
filters
row actions
```

Mobile:

```txt
card list
key fields only
actions in menu/sheet
```

If horizontal scroll is used:

```txt
show clear scroll affordance
keep first key column visible if possible
```

## 25.4. Verification Review

Desktop:

```txt
split view:
left evidence/profile data
right admin decision panel
```

Mobile:

```txt
stack sections
decision actions sticky bottom or final section
preview full-screen
```

## 25.5. Moderation Queue

Desktop:

```txt
queue list/table + detail drawer
```

Mobile:

```txt
queue cards + detail page/sheet
```

## 25.6. Audit Log

Desktop:

```txt
table
```

Mobile:

```txt
timeline/card list
```

## 25.7. Permission Grant UI

Mobile:

```txt
step-based form
```

Desktop:

```txt
modal or drawer with scoped permission controls
```

---

# 26. Landing Page Responsive Rules

## 26.1. Hero

Mobile:

```txt
headline first
short paragraph
CTA
hero image below or hidden if too heavy
```

Desktop:

```txt
two-column hero
text left
visual right
```

## 26.2. Feature Sections

Mobile:

```txt
stacked cards
```

Desktop:

```txt
2-3 column grid
```

## 26.3. CTA Sections

Mobile:

```txt
single clear CTA
```

Desktop:

```txt
primary + secondary CTA
```

## 26.4. Social Proof / Metrics

Mobile:

```txt
stacked metric cards
```

Desktop:

```txt
horizontal metric row
```

## 26.5. Avoid Heavy Landing on Mobile

Do not overload mobile landing with:

```txt
large decorative illustrations
complex animation
multiple columns
huge gradient blocks behind text
```

---

# 27. Media Responsive Rules

## 27.1. Images

Images must:

```txt
max-width: 100%
height: auto
object-fit controlled
```

## 27.2. Post Images

Mobile:

```txt
fit card width
rounded corners
tap to preview
```

Desktop:

```txt
same feed column width
preview modal
```

## 27.3. Avatar

Avatar sizes:

| Context        | Mobile |     Desktop |
| -------------- | -----: | ----------: |
| Comment        |   32px | 32px - 40px |
| Feed author    |   40px |        40px |
| Profile header |   80px |       112px |
| Mentor card    |   48px |        56px |
| Admin row      |   32px |        32px |

## 27.4. File Preview

Mobile:

```txt
full-screen
```

Desktop:

```txt
modal/drawer
```

## 27.5. Prevent Layout Shift

For images:

```txt
set aspect ratio
reserve space
use skeleton
```

---

# 28. Overflow Rules

## 28.1. Horizontal Overflow

No page should accidentally scroll horizontally at:

```txt
320px
360px
390px
768px
1024px
1440px
```

Allowed horizontal scroll only for:

```txt
tabs
filter chips
admin table if unavoidable
carousel if intentionally designed
```

## 28.2. Long Text

Use:

```txt
break-words
truncate with tooltip/detail when appropriate
line-clamp for cards
```

Do not line-clamp critical admin/safety reasons without access to full text.

## 28.3. Long URLs

Long URLs in messages/posts/resources must wrap.

Use:

```css
overflow-wrap: anywhere;
word-break: break-word;
```

## 28.4. Sticky Elements

Sticky headers/composers must not cover content.

Add bottom padding for sticky composer/nav.

---

# 29. Orientation Rules

## 29.1. Portrait First

Mobile portrait is primary.

All core flows must work in portrait:

```txt
auth
verification
onboarding
feed
discovery
messaging
community
mentor request
report
```

## 29.2. Landscape

Landscape should not break.

For mobile landscape:

```txt
reduce vertical spacing
avoid overly tall modals
allow scrolling
```

## 29.3. No Orientation Lock

Do not require specific orientation.

---

# 30. Browser Zoom & Text Scaling

## 30.1. Zoom Target

UI must remain usable at:

```txt
200% browser zoom
```

## 30.2. Rules

```txt
- avoid fixed heights that clip text
- allow content wrap
- avoid absolute positioning for core content
- forms must remain usable
- modals must scroll internally if needed
```

## 30.3. Admin Dense UI

At zoom 200%, admin may require more scrolling, but must not become unusable.

---

# 31. Device Input Rules

## 31.1. Touch Devices

Use:

```txt
large targets
bottom sheets
visible actions
no hover-only controls
```

## 31.2. Pointer Devices

Can use:

```txt
hover states
tooltips
right rail
dense tables
dropdown menus
```

## 31.3. Keyboard Users

All responsive layouts must maintain:

```txt
logical focus order
visible focus
skip link
modal focus trap
```

## 31.4. Hybrid Devices

Tablet/laptop touch devices may have both pointer and touch.

Do not assume:

```txt
wide screen = mouse only
small screen = touch only
```

---

# 32. Responsive State Rules

## 32.1. Loading

Mobile:

```txt
skeleton cards
```

Desktop:

```txt
skeleton list/table/cards
```

## 32.2. Empty State

Mobile:

```txt
centered block with concise copy
```

Desktop:

```txt
centered or within panel
```

## 32.3. Error State

Mobile:

```txt
full-width error state
retry button full/comfortable
```

Desktop:

```txt
inline panel or page state
```

## 32.4. Permission State

Must show same reason across devices.

Mobile copy can be shorter but cannot remove meaning.

## 32.5. Offline State

Mobile:

```txt
compact banner
```

Desktop:

```txt
top banner/alert
```

---

# 33. Responsive Implementation Direction

## 33.1. Tailwind Pattern

Prefer:

```html
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
```

Avoid:

```css
@media (min-width: 847px) { ... }
```

unless strongly justified.

## 33.2. Component-level Responsiveness

Components should own simple responsive behavior.

Example:

```txt
Card padding changes by breakpoint.
Button can be full-width on mobile.
Modal switches to sheet on mobile.
```

Page should own layout-level behavior.

Example:

```txt
Feed decides one-column vs center-column + right rail.
Admin decides table vs card list.
```

## 33.3. Avoid Duplicate Markup

Do not create separate mobile and desktop markup unless necessary.

Bad:

```txt
two separate navs with duplicated logic and mismatched states
```

Allowed:

```txt
same data rendered in different layout component
```

If duplication is necessary, business state and accessibility labels must remain consistent.

## 33.4. CSS Container Queries

Future/P2:

```txt
Use container queries for components that adapt to parent width.
```

Useful for:

```txt
cards
profile header
admin widgets
resource cards
```

## 33.5. Blade Component Props

Responsive props should be controlled.

Example:

```php
<x-ui.button variant="primary" size="lg" mobile-full>
  Gửi xác thực
</x-ui.button>
```

But avoid prop explosion:

```php
mobileBigDesktopSmallBlueButNotTooBlue
```

Frontend cũng có giới hạn chịu đựng.

---

# 34. Responsive Testing Matrix

## 34.1. Required Viewports

Test at:

```txt
320x568
360x640
390x844
414x896
768x1024
1024x768
1280x800
1440x900
1536x864
```

## 34.2. PWA Tests

Test:

```txt
mobile browser
installed PWA standalone
desktop browser
desktop narrow window
mobile keyboard open
offline mode
safe-area device if possible
```

## 34.3. Feature Test Matrix

Must test responsive behavior for:

```txt
auth
verification
onboarding
home feed
post/comment
discovery/greeting
messaging
notifications
mentor matching
community/club
career pathway
search/filter
safety reporting
moderation
admin dashboard
admin tables
settings/privacy
```

## 34.4. Browser Testing

Minimum:

```txt
Chrome
Edge
Safari mobile if available
Firefox if feasible
```

## 34.5. Interaction Testing

For each viewport:

```txt
keyboard
touch/click
focus visible
modal/sheet
form submit
error state
loading state
overflow
```

---

# 35. Responsive Acceptance Criteria

A page is responsive-ready only if:

```txt
[ ] Works at 320px width.
[ ] No accidental horizontal overflow.
[ ] Main CTA remains visible/reachable.
[ ] Navigation works on mobile and desktop.
[ ] Touch targets are at least 44px.
[ ] Text remains readable.
[ ] Forms are usable with mobile keyboard.
[ ] Modals become sheet/full-screen where needed.
[ ] Tables have mobile fallback or intentional scroll.
[ ] Sticky header/nav/composer does not cover content.
[ ] Safe-area is respected in PWA mode.
[ ] Important state/copy is not hidden on mobile.
[ ] Feature has same business rules across devices.
[ ] Keyboard order remains logical.
[ ] 200% zoom remains usable.
```

---

# 36. Common Anti-patterns

Do not:

```txt
- Design desktop first then shrink with panic.
- Hide important CTA on mobile.
- Use hover-only actions.
- Make bottom nav icon-only without labels.
- Create 7 custom breakpoints for one screen.
- Use fixed pixel widths that overflow.
- Put tables on mobile with 9 unreadable columns.
- Make modal taller than screen with no scroll.
- Hide error/helper text on mobile.
- Reduce touch targets below 44px.
- Use tiny text to fit more content.
- Put message composer behind safe-area/browser controls.
- Duplicate mobile/desktop markup with mismatched states.
- Remove admin action consequences on mobile.
- Let search/filter become unusable on mobile.
```

---

# 37. Final Rule

Responsive design trong UEConnect không phải là “vừa màn hình”.

Responsive đúng nghĩa là:

```txt
cùng một sản phẩm
cùng business rules
cùng accessibility
layout phù hợp từng thiết bị
interaction phù hợp từng input
content vẫn rõ
CTA vẫn dùng được
state vẫn chính xác
```

Trước khi ship bất kỳ page nào, kiểm tra:

```txt
1. Mobile 320px có dùng được không?
2. Desktop có tận dụng không gian tốt không?
3. Có horizontal overflow không?
4. CTA chính có dễ tìm không?
5. Form có dùng được khi bàn phím mở không?
6. Modal/sheet có scroll/focus đúng không?
7. Bottom nav/header có đè content không?
8. Safe-area PWA có ổn không?
9. Admin/mobile fallback có đủ dùng không?
10. Business logic có nhất quán giữa mobile và desktop không?
```

Nếu chỉ “nhìn tạm được trên máy tôi”, thì chưa xong. Câu đó là bia mộ của mọi responsive bug.

```
```
