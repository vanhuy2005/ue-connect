---
title: "Spacing System"
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
  - "05-typography-system.md"
next:
  - "07-radius-system.md"
  - "08-shadow-elevation-system.md"
  - "12-component-primitives.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "examples/preview.html"
---

# 06. Spacing System

## 1. Purpose

File này định nghĩa spacing system chính thức cho UEConnect.

Mục tiêu:

- Chốt scale khoảng cách dùng cho toàn bộ UI.
- Giữ layout sạch, dễ đọc, có nhịp thở.
- Đảm bảo feed, profile, discovery, messaging, mentor, admin có spacing nhất quán.
- Hỗ trợ responsive thật sự, không chỉ co layout desktop xuống mobile.
- Giúp frontend implement nhất quán bằng TailwindCSS, Blade component và CSS variables.
- Tránh UI nhìn AI-generated do padding quá lớn, khoảng cách random, card phồng như landing page.

Spacing là thứ âm thầm quyết định UI có chuyên nghiệp hay không. Người dùng không nói “spacing đẹp”, họ chỉ thấy app dễ dùng. Còn khi spacing sai, họ thấy “có gì đó kỳ kỳ”, rồi designer bắt đầu đổ lỗi cho màu, font, hoặc định mệnh.

---

## 2. Core Decision

UEConnect dùng spacing strategy:

```txt
4px base grid
+ social feed density
+ mobile-first comfort
+ enterprise consistency

Base unit:

1 unit = 4px

Spacing phải:

Đều.
Có hệ thống.
Không quá rộng như landing page.
Không quá chật như admin panel cũ từ năm 2012.
Ưu tiên readability và interaction.
3. Spacing Principles
3.1. Content-first spacing

UEConnect là social product, nên spacing phải phục vụ:

Post dễ đọc.
Comment dễ theo dõi.
Message dễ scan.
Profile rõ hierarchy.
Discovery card có cá tính nhưng không rối.
Admin table đủ thông tin nhưng không ngộp.

Không dùng spacing để làm UI “trông premium” bằng cách nhồi padding 40px vào mọi card. Đó không phải premium, đó là thuê mặt bằng quá rộng cho một dòng chữ.

3.2. Density by context

Không phải mọi màn hình đều dùng cùng density.

Context	Density
Home Feed	Medium, đọc lâu
Post Detail	Medium/comfortable
Messaging	Compact but readable
Discovery	Comfortable, visual hơn
Profile	Comfortable
Onboarding	Spacious
Admin	Compact/medium
Mobile	Touch-friendly
3.3. Mobile-first touch comfort

Mobile spacing cần đảm bảo:

Touch target tối thiểu 44px.
Button/input đủ cao.
Icon action không quá sát.
Bottom nav dễ bấm.
Composer/chat không bị chật.
4. Spacing Scale

UEConnect dùng scale sau:

:root {
  --space-0: 0;
  --space-0-5: 2px;
  --space-1: 4px;
  --space-1-5: 6px;
  --space-2: 8px;
  --space-2-5: 10px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 20px;
  --space-6: 24px;
  --space-7: 28px;
  --space-8: 32px;
  --space-10: 40px;
  --space-12: 48px;
  --space-14: 56px;
  --space-16: 64px;
  --space-20: 80px;
  --space-24: 96px;
}
4.1. Token Usage Table
Token	Value	Usage
space-0	0	Reset
space-0-5	2px	Hairline optical adjustment
space-1	4px	Tiny gap, icon detail
space-1-5	6px	Small icon/text gap
space-2	8px	Default icon-label gap, compact gap
space-2-5	10px	Small vertical rhythm
space-3	12px	Avatar-text gap, compact padding
space-4	16px	Default card/post padding
space-5	20px	Comfortable card padding
space-6	24px	Column gap, section padding
space-7	28px	Rare in-between layout
space-8	32px	Large section gap
space-10	40px	Onboarding/hero block spacing
space-12	48px	Large page section
space-14	56px	Header/nav height
space-16	64px	Large vertical section
space-20	80px	Marketing/onboarding only
space-24	96px	Rare hero spacing
5. Layout Spacing Tokens
:root {
  --layout-gutter-mobile: 16px;
  --layout-gutter-tablet: 20px;
  --layout-gutter-desktop: 24px;

  --layout-left-nav: 240px;
  --layout-feed: 600px;
  --layout-feed-min: 400px;
  --layout-feed-max: 640px;
  --layout-right-panel: 300px;
  --layout-shell-max: 1200px;

  --layout-mobile-bottom-nav: 64px;
  --layout-topbar: 56px;
}
5.1. Desktop Shell
Left navigation: 240px
Main feed: 560–640px
Right panel: 280–320px
Column gap: 24px
Shell max: 1200px

CSS example:

.desktop-shell {
  max-width: var(--layout-shell-max);
  margin: 0 auto;
  display: grid;
  grid-template-columns:
    var(--layout-left-nav)
    minmax(var(--layout-feed-min), var(--layout-feed))
    var(--layout-right-panel);
  gap: var(--layout-gutter-desktop);
}
5.2. Tablet Shell
Collapsed nav: 72px
Main feed: flexible
Right panel: hidden or narrow
Gap: 20px
5.3. Mobile Shell
Top bar: 56px
Content gutter: 16px
Bottom nav: 64px
Single column
No right panel

Mobile không phải desktop ép xuống còn một cột như ép vali quá tải. Nó cần layout riêng.

6. Component Spacing Rules
6.1. Button
Button Size	Height	X Padding	Gap
XS	28px	10px	6px
SM	32px	12px	6px
MD	40px	16px	8px
LG	48px	20px	8px
XL	52px	24px	10px

Rules:

Button mặc định dùng 40px height.
Mobile primary CTA nên 44–48px.
Icon-only button tối thiểu 40px, mobile nên 44px.
Không dùng padding quá lớn cho button trong feed.
6.2. Input
Input Size	Height	X Padding
SM	36px	12px
MD	44px	12–14px
LG	48px	16px

Rules:

Auth/verification input nên 44–48px.
Mobile input nên ít nhất 44px.
Label cách input 4–6px.
Helper/error text cách input 4px.
6.3. Card
Card Type	Padding
Compact card	12px
Default card	16px
Comfortable card	20px
Feature/onboarding card	24px
Marketing card	32px

Rules:

Post card/feed item dùng 16px.
Right panel card dùng 14–16px.
Discovery card có thể dùng 20–24px.
Admin card dùng 16px.
Không dùng 32px cho mọi card.
6.4. Avatar + Text
Context	Gap
Avatar + author text	12px
Avatar + compact list item	10px
Small avatar + text	8px
Profile header avatar + info	16–20px
6.5. Icon + Label
Context	Gap
Nav item	12px
Button	8px
Badge	4–6px
Post action	6px
Form helper icon	6px
7. Page-level Spacing
7.1. Home Feed
Feed item padding: 16px
Avatar/content gap: 12px
Post body top gap: 6–8px
Media top gap: 12px
Action row top gap: 10–12px
Comment preview top gap: 10px
Divider: 1px

Rules:

Feed không được quá loãng.
Divider quan trọng hơn shadow.
Action row không được sát post body.
Composer nên có cùng padding với post.
7.2. Post Detail
Post detail padding: 16–20px
Comment list gap: 12px
Comment input padding: 12–16px
Reply indentation: 36–44px

Rules:

Reply không nested vô hạn.
Indentation không làm mobile bị hẹp.
Comment input sticky cần đủ height.
7.3. Discovery
Card padding: 20–24px
Avatar/photo to info gap: 16px
Profile sections gap: 16px
Action row gap: 12px
Filter chips gap: 8px

Rules:

Discovery có thể thoáng hơn feed.
Nhưng không thành dating card quá dramatic.
Actions phải đủ touch target.
7.4. Profile
Header padding: 20–24px
Avatar/info gap: 16–20px
Bio top gap: 8px
Stats gap: 16–24px
Tabs height: 44–48px
Section gap: 20–24px

Rules:

Profile cần hierarchy rõ.
Metadata không chen quá sát name.
Tabs phải đủ dễ bấm trên mobile.
7.5. Messaging
Inbox item padding: 12–16px
Conversation row height: 64–72px
Message list padding: 16px
Message bubble padding: 8px 12px
Message group gap: 4px
Between speakers gap: 12px
Composer padding: 12px

Rules:

Message bubble không quá phồng.
Tin nhắn liên tiếp của cùng người gần nhau hơn.
Đổi speaker cần gap lớn hơn.
Composer không che nội dung cuối.
7.6. Mentor
Mentor card padding: 16–20px
Expertise chip gap: 8px
Request form section gap: 20px
Mentor profile section gap: 24px

Rules:

Mentor cần thoáng hơn admin, nhưng không như landing page.
Form request cần chia section rõ.
7.7. Admin
Dashboard card padding: 16px
Table cell padding: 12px 16px
Filter bar gap: 12px
Toolbar height: 44–48px
Queue item padding: 12–16px

Rules:

Admin cần density tốt.
Không dùng spacing social quá thoáng cho table.
Action destructive cần đủ khoảng cách để tránh click nhầm.
8. Responsive Spacing Rules
8.1. Desktop
Outer page padding: 24px
Column gap: 24px
Section gap: 32px
8.2. Tablet
Outer page padding: 20px
Column gap: 20px
Section gap: 28px
8.3. Mobile
Outer page padding: 16px
Component gap: 12–16px
Section gap: 24px
Bottom safe padding: bottom nav height + 16px

Mobile content cần tránh sát mép màn hình. Một app social mà chữ dính mép như hóa đơn in lỗi thì không ai muốn đọc.

9. Density Modes

UEConnect có thể dùng 3 density mode.

9.1. Comfortable

Dùng cho:

Onboarding.
Discovery.
Profile setup.
Mentor setup.
Padding: 20–24px
Gap: 16–24px
9.2. Default

Dùng cho:

Home feed.
Profile.
Notifications.
Settings.
Padding: 16px
Gap: 12–16px
9.3. Compact

Dùng cho:

Admin table.
Inbox list.
Search result.
Dense lists.
Padding: 8–12px
Gap: 8–12px

Compact không có nghĩa là bóp nghẹt text. Nó chỉ nghĩa là giảm khoảng trống thừa.

10. TailwindCSS Mapping

Tailwind đã có spacing scale tương thích với 4px grid. Có thể dùng mặc định, nhưng nên document các token quan trọng:

// tailwind.config.js
export default {
  theme: {
    extend: {
      spacing: {
        "0.5": "2px",
        "1.5": "6px",
        "2.5": "10px",
        "7": "28px",
        "14": "56px",
        "18": "72px",
      },
      maxWidth: {
        "ue-feed": "600px",
        "ue-feed-max": "640px",
        "ue-shell": "1200px",
      },
    },
  },
}
10.1. Common Tailwind Patterns
Post padding: p-4
Card padding: p-4 or p-5
Right panel card: p-3.5 or p-4
Button MD: h-10 px-4 gap-2
Button LG: h-12 px-5 gap-2
Input MD: h-11 px-3
Avatar/text: gap-3
Icon/label: gap-2
Desktop shell: gap-6
Mobile page padding: px-4
11. Blade Examples
11.1. Feed Post
<article class="grid grid-cols-[44px_minmax(0,1fr)] gap-3 border-b border-ue-border bg-white p-4">
  <x-ui.avatar :user="$post->user" class="h-10 w-10" />

  <div class="min-w-0">
    <x-social.post-author :user="$post->user" />

    <p class="mt-2 text-base leading-[23px] text-ue-text">
      {{ $post->body }}
    </p>

    <div class="mt-3 flex items-center gap-1">
      <x-ui.icon-button icon="heart" label="Thích" />
      <x-ui.icon-button icon="message-circle" label="Bình luận" />
      <x-ui.icon-button icon="bookmark" label="Lưu" />
    </div>
  </div>
</article>
11.2. Right Panel Card
<section class="rounded-xl border border-ue-border bg-white p-4">
  <h2 class="text-base font-semibold text-ue-text">
    Gợi ý kết nối
  </h2>

  <div class="mt-4 grid gap-3">
    {{-- suggestions --}}
  </div>
</section>
11.3. Mobile Bottom Nav
<nav class="fixed inset-x-0 bottom-0 z-40 grid h-16 grid-cols-5 border-t border-ue-border bg-white px-2">
  {{-- nav items --}}
</nav>
12. Spacing Anti-patterns
12.1. Random Values

Không dùng:

padding: 17px;
gap: 23px;
margin-top: 19px;

trừ khi có lý do optical alignment rõ.

12.2. Landing Page Padding in Product UI

Sai:

.post-card {
  padding: 40px;
}

Đúng:

.post-card {
  padding: 16px;
}
12.3. Too Tight Mobile UI

Sai:

.mobile-page {
  padding-left: 8px;
  padding-right: 8px;
}

Đúng:

.mobile-page {
  padding-left: 16px;
  padding-right: 16px;
}
12.4. Same Spacing Everywhere

Không dùng một padding duy nhất cho mọi thứ.

Feed, discovery, admin, messaging có density khác nhau. Nếu mọi page dùng cùng spacing, UI sẽ hoặc quá chật, hoặc quá loãng. Thiết kế không phải nồi cơm điện một nút, dù đôi khi team dev ước vậy.

13. Spacing QA Checklist
13.1. Layout
 Desktop shell có column gap nhất quán không?
 Feed width nằm trong 560–640px không?
 Mobile có gutter ít nhất 16px không?
 Bottom nav có chừa safe padding không?
 Right panel không quá sát feed không?
13.2. Component
 Button height đủ touch target không?
 Input có height tối thiểu 44px ở mobile không?
 Card padding có hợp context không?
 Avatar/text gap có đều không?
 Icon/label gap có nhất quán không?
13.3. Content
 Post body không quá sát author row không?
 Action row không quá sát content không?
 Comment reply indentation không phá mobile không?
 Message bubble gap theo speaker có rõ không?
 Admin table không quá chật không?
13.4. Product Fit
 UI không quá landing page không?
 UI không quá dashboard cũ không?
 Social feed có đọc thoải mái không?
 Discovery có đủ thoáng nhưng không dating không?
 Spacing có tránh cảm giác AI mockup không?
14. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

Follow UEConnect Spacing System.
Use a 4px base grid.
Use p-4 for feed posts, gap-3 for avatar/content, gap-2 for icon/label, and 24px desktop column gaps.
Keep product UI density similar to professional social platforms, not landing-page spacing.
Do not use oversized padding like 40px inside daily product cards.
Mobile layout must have at least 16px horizontal gutter and 44px touch targets.
Adjust layout adaptively for mobile instead of shrinking desktop.
15. Final Decision

Spacing system chính thức của UEConnect:

Base grid: 4px
Default product padding: 16px
Desktop column gap: 24px
Mobile gutter: 16px
Feed width: 560–640px
Default button height: 40px
Mobile touch target: 44px minimum

Câu chốt:

Spacing của UEConnect phải giúp social content dễ đọc, interaction dễ bấm, layout có nhịp thở, và không nhìn như một landing page
