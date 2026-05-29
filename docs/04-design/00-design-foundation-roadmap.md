---
title: "Design Foundation Roadmap"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Frontend Team"
related:
  - "01-brand-attributes.md"
  - "02-brand-identity-hcmue.md"
  - "03-color-system.md"
  - "04-gradient-policy.md"
  - "12-component-primitives.md"
  - "page-specs/home-feed.md"
  - "examples/preview.html"
---

# 00. Design Foundation Roadmap

## 1. Purpose

File này là bản đồ tổng quan cho toàn bộ hệ thống tài liệu thiết kế của UEConnect.

Mục tiêu của file này là giúp team hiểu:

- Nên đọc tài liệu design theo thứ tự nào.
- Mỗi file `.md` trong `docs/04-design` giải quyết vấn đề gì.
- Khi thiết kế một screen mới thì cần dựa vào những foundation nào.
- Khi implement bằng Laravel Blade, TailwindCSS, Vite thì cần mapping design token ra sao.
- Cách tránh các lỗi UI/UX nghiêm trọng khiến sản phẩm nhìn thiếu chuyên nghiệp hoặc có cảm giác AI-generated.

File này không phải nơi mô tả chi tiết từng màu, từng button, từng screen. Nó là roadmap để dẫn sang các file chuyên sâu khác.

---

## 2. Product Design Direction

UEConnect được thiết kế như một nền tảng social dành cho sinh viên HCMUE.

Product không chỉ là một website giới thiệu. UEConnect là một social platform có nhiều module:

```txt
01. Social Feed
02. Discovery Profile
03. Messaging / Community Chat
04. Mentor Matching
05. Career Exploring
06. Identity / Verified UEer
07. Clubs / Classes / Communities
08. Notification
09. Safety / Reporting
10. Settings / Privacy
````

Hướng thiết kế chính thức:

```txt
Neutral-first social UI
+ restrained HCMUE brand identity
+ mobile-first adaptive layout
+ content-first UX
+ enterprise-ready component system
```

Diễn giải đơn giản:

* UI mặc định phải sạch, nhẹ, dễ đọc.
* Màu thương hiệu HCMUE `#124874` chỉ dùng đúng chỗ.
* Không dùng gradient tràn lan.
* Không thiết kế giống landing page AI.
* Không copy Tinder, Threads, Instagram một cách máy móc.
* Chỉ học cách các app lớn tổ chức layout, spacing, icon, hierarchy, interaction state.
* Product phải hoạt động tốt trên cả desktop web app và mobile viewport.

---

## 3. Product Scope

### 3.1. Main Product Pillars

UEConnect có 4 trụ cột chính.

| Pillar                     | Mô tả                                                                                    | Priority |
| -------------------------- | ---------------------------------------------------------------------------------------- | -------: |
| Social Feed                | Feed dạng Threads/Facebook blogging, nơi sinh viên đăng bài, bình luận, chia sẻ          |       P0 |
| Discovery Profile          | Khám phá profile UEers để làm quen, học tập, kết nối trong trường, không phải dating     |       P0 |
| Messaging / Community      | Chat cá nhân, nhóm lớp, câu lạc bộ, cộng đồng thay thế Zalo/Messenger ở ngữ cảnh học tập |       P0 |
| Mentor / Career / Identity | Mentor matching, alumni, career exploring, verified identity                             |       P1 |

### 3.2. MVP Priority

Trong giai đoạn MVP, ưu tiên thiết kế theo thứ tự:

```txt
01. Authentication / Onboarding
02. Home Feed
03. Post Composer
04. Post Detail / Comment
05. Discovery Profile
06. User Profile
07. Messaging
08. Community / Club Chat
09. Notification
10. Safety / Report
11. Mentor Matching
12. Settings / Privacy
```

Lý do:

* Nếu feed và profile không tốt, product sẽ không có hành vi social cốt lõi.
* Nếu messaging không tốt, người dùng vẫn quay lại Zalo/Messenger.
* Nếu identity không rõ, UEConnect mất trust.
* Nếu safety/reporting yếu, social product sẽ tự đốt nhà mình, một truyền thống đáng buồn của internet.

---

## 4. Platform Strategy

UEConnect được thiết kế song song cho:

```txt
Desktop Web App
Tablet Viewport
Mobile Web App
Future Mobile App
```

Không được hiểu responsive là “màn hình nhỏ thì thu nhỏ layout desktop”.

Responsive strategy chính thức:

```txt
Adaptive responsive design
```

Nghĩa là:

* Desktop có thể dùng left navigation + center feed + right panel.
* Tablet có thể collapse left nav và giảm right panel.
* Mobile phải chuyển sang top bar + single feed + bottom navigation.
* Component phải thay đổi layout theo viewport, không chỉ scale kích thước.
* Navigation pattern trên mobile phải giống mobile app thật, không phải desktop bị ép vào màn nhỏ.

---

## 5. Technical Stack Alignment

Design system phải phù hợp với stack hiện tại:

```txt
Backend / Rendering:
Laravel Blade

Frontend Build:
Vite

Styling:
TailwindCSS
CSS Modules khi cần isolate component phức tạp

JavaScript:
Vanilla JS / Alpine.js / Vue hoặc React nếu project mở rộng sau này

Icon:
Lucide Icons hoặc Phosphor Icons

UI Utility:
Headless UI / Radix-inspired pattern / Flowbite optional
```

### 5.1. Recommended Libraries

#### Icon Library

Khuyến nghị chính:

```txt
Lucide Icons
```

Lý do:

* Icon line sạch, hiện đại.
* Stroke 2px phù hợp social UI.
* Dễ dùng với Blade thông qua SVG inline hoặc package icon.
* Dễ đồng bộ với Tailwind.
* Không bị quá “corporate”.

Alternative:

```txt
Phosphor Icons
Heroicons Outline
Tabler Icons
```

Không khuyến nghị dùng icon nhiều màu hoặc icon filled mặc định, vì social UI cần neutral-first và content-first.

#### UI Component Support

Với Laravel Blade + TailwindCSS:

```txt
Headless UI
Flowbite
Preline UI
Blade UI Kit
WireUI nếu dùng Livewire
```

Ưu tiên:

```txt
Headless UI + custom Tailwind components
```

Lý do:

* Ít bị khóa style.
* Dễ giữ identity riêng.
* Phù hợp enterprise design system.
* Không bị “template smell”.

Flowbite / Preline có thể dùng để tham khảo pattern, nhưng không nên bê nguyên style vì dễ làm UEConnect nhìn generic.

---

## 6. Design Documentation Structure

Thư mục `docs/04-design` được chia thành 5 nhóm chính.

```txt
04-design/
├── README.md
├── 00-design-foundation-roadmap.md
├── 01-brand-attributes.md
├── 02-brand-identity-hcmue.md
├── 03-color-system.md
├── 04-gradient-policy.md
├── 05-typography-system.md
├── 06-spacing-system.md
├── 07-radius-system.md
├── 08-shadow-elevation-system.md
├── 09-border-system.md
├── 10-icon-system.md
├── 11-logo-usage-system.md
├── 12-component-primitives.md
├── 13-component-variants.md
├── 14-interaction-states.md
├── 15-motion-system.md
├── 16-content-tone.md
├── 17-accessibility-rules.md
├── 18-responsive-rules.md
├── 19-design-token-documentation.md
├── 20-agent-prompt-guide.md
├── 21-social-interaction-patterns.md
│
├── page-specs/
│   ├── onboarding.md
│   ├── home-feed.md
│   ├── discovery.md
│   ├── profile.md
│   ├── messaging.md
│   ├── notifications.md
│   ├── mentor.md
│   ├── clubs.md
│   ├── settings.md
│   └── safety-reporting.md
│
├── ui-states/
│   ├── empty-states.md
│   ├── loading-states.md
│   ├── error-states.md
│   ├── offline-states.md
│   ├── permission-states.md
│   └── moderation-states.md
│
└── examples/
    ├── preview.html
    ├── component-showcase.md
    ├── desktop-feed-wireframe.md
    └── mobile-feed-wireframe.md
```

---

## 7. Reading Order

Không đọc lung tung. Design system có thứ tự, không phải hộp bánh thập cẩm.

### Phase 1: Foundation

Đọc trước các file sau:

```txt
00-design-foundation-roadmap.md
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
```

Mục tiêu:

* Hiểu brand.
* Hiểu màu.
* Hiểu spacing, radius, shadow.
* Hiểu icon và logo.
* Biết UI nên nhìn như thế nào trước khi thiết kế component.

### Phase 2: Component System

Đọc tiếp:

```txt
12-component-primitives.md
13-component-variants.md
14-interaction-states.md
15-motion-system.md
16-content-tone.md
17-accessibility-rules.md
18-responsive-rules.md
19-design-token-documentation.md
21-social-interaction-patterns.md
```

Mục tiêu:

* Biết component nào cần có.
* Biết mỗi component có state gì.
* Biết responsive behavior.
* Biết accessibility rule.
* Biết cách map sang TailwindCSS.

### Phase 3: Page Specs

Đọc khi thiết kế từng màn hình:

```txt
page-specs/onboarding.md
page-specs/home-feed.md
page-specs/discovery.md
page-specs/profile.md
page-specs/messaging.md
page-specs/notifications.md
page-specs/mentor.md
page-specs/clubs.md
page-specs/settings.md
page-specs/safety-reporting.md
```

Mục tiêu:

* Chốt UX cho từng page.
* Chốt layout desktop/tablet/mobile.
* Chốt component dùng trong page đó.
* Chốt empty/loading/error state.

### Phase 4: UI States

Đọc khi cần xử lý trạng thái thực tế:

```txt
ui-states/empty-states.md
ui-states/loading-states.md
ui-states/error-states.md
ui-states/offline-states.md
ui-states/permission-states.md
ui-states/moderation-states.md
```

Mục tiêu:

* Không để UI vỡ khi không có data.
* Không để loading nhấp nháy xấu.
* Không dùng alert/error tùy tiện.
* Không bỏ quên offline/permission/moderation state.

### Phase 5: Examples

Dùng để kiểm tra cảm giác UI:

```txt
examples/preview.html
examples/component-showcase.md
examples/desktop-feed-wireframe.md
examples/mobile-feed-wireframe.md
```

Mục tiêu:

* Xem live preview.
* Kiểm tra layout thật.
* So sánh design docs với UI thực tế.
* Phát hiện chỗ docs nói một đằng, UI làm một nẻo.

---

## 8. File Responsibility Matrix

| File                               | Responsibility                                                |
| ---------------------------------- | ------------------------------------------------------------- |
| `01-brand-attributes.md`           | Chốt cảm giác thương hiệu, personality, product identity      |
| `02-brand-identity-hcmue.md`       | Chốt ràng buộc từ HCMUE, màu chính, logo, typography heritage |
| `03-color-system.md`               | Chốt color tokens, semantic colors, usage ratio               |
| `04-gradient-policy.md`            | Quy định gradient dùng ở đâu và không dùng ở đâu              |
| `05-typography-system.md`          | Font stack, type scale, hierarchy                             |
| `06-spacing-system.md`             | Spacing scale, layout rhythm                                  |
| `07-radius-system.md`              | Border radius scale và rule dùng                              |
| `08-shadow-elevation-system.md`    | Shadow, elevation, depth                                      |
| `09-border-system.md`              | Border color, border width, divider usage                     |
| `10-icon-system.md`                | Icon library, icon sizes, icon behavior                       |
| `11-logo-usage-system.md`          | Logo variants, clear space, min size                          |
| `12-component-primitives.md`       | Component base: button, input, avatar, badge, card            |
| `13-component-variants.md`         | Variants theo use case và module                              |
| `14-interaction-states.md`         | Hover, focus, active, disabled, loading, error                |
| `15-motion-system.md`              | Duration, easing, transitions                                 |
| `16-content-tone.md`               | Copywriting, microcopy, naming                                |
| `17-accessibility-rules.md`        | WCAG, keyboard, aria, contrast, touch target                  |
| `18-responsive-rules.md`           | Desktop/tablet/mobile adaptive behavior                       |
| `19-design-token-documentation.md` | Token naming, Tailwind mapping, CSS variables                 |
| `20-agent-prompt-guide.md`         | Prompt rules cho AI design/coding agent                       |
| `21-social-interaction-patterns.md` | Threads-inspired social UX, micro-interactions, optimistic UI |

---

## 9. Core Design Principles

## 9.1. Neutral-first

Default UI phải dùng neutral:

```txt
White
Near-white
Black
Gray
Light border
Subtle surface
```

Brand blue `#124874` chỉ dùng cho:

```txt
Logo
Active navigation
Primary CTA
Verified UEer badge
Focus ring
Important link
Official HCMUE identity area
Rare onboarding brand moment
```

Không dùng brand blue cho:

```txt
Mọi heading
Mọi icon
Mọi card
Mọi background
Mọi border
Mọi badge
Mọi button
```

Nếu mọi thứ đều là primary, thì không còn gì là primary. Một phát hiện gây sốc với nhân loại, nhưng rất cần ghi lại.

---

## 9.2. Content-first

UEConnect là social platform, nên content là nhân vật chính:

```txt
Post content
Avatar
Name
Faculty / cohort
Verified state
Media
Comment
Message
Community activity
Mentor information
```

UI không được tranh spotlight với content.

Điều này nghĩa là:

* Feed phải đọc dễ.
* Post action phải nhẹ.
* Icon không được nhiều màu.
* Card không được shadow quá mạnh.
* Background không được chói.
* Typography phải rõ hierarchy.

---

## 9.3. Mobile-first adaptive

UEConnect phải thiết kế mobile nghiêm túc, không chỉ thu nhỏ desktop.

Desktop:

```txt
Left navigation
Center feed
Right panel
```

Tablet:

```txt
Collapsed left nav
Main feed
Optional right panel
```

Mobile:

```txt
Top bar
Single feed
Bottom navigation
Contextual action sheet
```

Mobile không phải là desktop bị bóp nghẹt. Nó là một layout riêng.

---

## 9.4. Enterprise-ready states

Mỗi component quan trọng phải có đầy đủ state:

```txt
Default
Hover
Focus
Active
Disabled
Loading
Error
Success
Empty
Skeleton
Permission denied
Offline
Moderated / hidden
```

Không được thiết kế mỗi happy path. Happy path là nơi designer nghiệp dư trú ngụ trước khi production xuất hiện và phá tan giấc mơ.

---

## 9.5. Brand restraint

Brand identity không nằm ở việc dùng màu thương hiệu khắp nơi.

Brand identity của UEConnect nằm ở:

* Trust của verified UEer.
* Cách dùng `#124874` đúng lúc.
* Layout social sạch.
* Nội dung có ngữ cảnh HCMUE.
* Tone thân thiện.
* Safety tốt.
* Community structure rõ.

---

## 10. Anti-patterns

Các lỗi cần tránh trong toàn bộ UI:

### 10.1. Visual Anti-patterns

```txt
Gradient phủ toàn màn hình trong product UI.
Blue-red gradient làm primary style.
Brand color xuất hiện ở mọi icon.
Post card có shadow nặng.
Border radius quá lớn cho mọi component.
Text quá to hoặc quá đậm.
Màu semantic quá rực.
Background chói hơn content.
Mọi CTA đều giống primary CTA.
Hero style landing page áp vào app screen.
```

### 10.2. UX Anti-patterns

```txt
Mobile chỉ là desktop thu nhỏ.
Navigation quá nhiều mục.
Không có empty state.
Không có loading state.
Không có error recovery.
Không có permission state.
Không có report/block rõ ràng.
Không có focus state.
Placeholder thay thế label.
Icon-only button không có aria-label.
Touch target nhỏ hơn 44px.
```

### 10.3. Product Anti-patterns

```txt
Discovery profile giống dating app quá mức.
Mentor feature bị biến thành job board khô khan.
Community chat quá giống clone Messenger/Zalo nhưng thiếu ngữ cảnh HCMUE.
Feed quá giống Facebook nhưng không có điểm riêng.
UI nói "connect" nhưng không tạo được trust.
```

---

## 11. Design Review Checklist

Trước khi duyệt một screen, kiểm tra:

### 11.1. Layout

* [ ] Screen có layout riêng cho desktop/tablet/mobile.
* [ ] Mobile không chỉ là desktop co lại.
* [ ] Feed width trên desktop nằm trong khoảng dễ đọc.
* [ ] Navigation không quá 7 mục chính.
* [ ] CTA chính dễ thấy nhưng không lấn át content.

### 11.2. Visual

* [ ] UI dùng neutral làm nền chính.
* [ ] Brand blue `#124874` chỉ dùng đúng chỗ.
* [ ] Không dùng gradient tràn lan.
* [ ] Không có icon nhiều màu lung tung.
* [ ] Shadow nhẹ, border rõ.
* [ ] Typography không quá to, không quá nặng.

### 11.3. Interaction

* [ ] Button có hover/focus/active/disabled.
* [ ] Input có label, focus, error text.
* [ ] Icon-only action có aria-label.
* [ ] Touch target tối thiểu 44px.
* [ ] Loading không gây layout shift.
* [ ] Error có hướng xử lý.

### 11.4. Content

* [ ] Copy rõ ràng, không sáo rỗng.
* [ ] Không dùng dating language như “crush”, “match”, “swipe right”.
* [ ] Dùng từ phù hợp: UEer, kết nối, gửi lời chào, mentor, câu lạc bộ.
* [ ] Empty state có hướng dẫn hành động tiếp theo.

### 11.5. Safety

* [ ] Có report/block ở profile, post, message.
* [ ] Sensitive content có state riêng.
* [ ] Moderated content không biến mất vô lý.
* [ ] Permission denied có giải thích rõ.

---

## 12. Design to Code Strategy

## 12.1. TailwindCSS Mapping

Design token phải map được sang Tailwind.

Ví dụ:

```js
// tailwind.config.js
theme: {
  extend: {
    colors: {
      brand: {
        DEFAULT: "#124874",
        hover: "#0E3A60",
        active: "#0A2B49",
        soft: "#EEF7FF"
      },
      surface: {
        DEFAULT: "#FFFFFF",
        subtle: "#F8FAFC",
        hover: "#F1F5F9"
      },
      border: {
        DEFAULT: "#E4E6EB",
        strong: "#CBD5E1"
      }
    },
    borderRadius: {
      xs: "4px",
      sm: "6px",
      md: "8px",
      lg: "12px",
      xl: "16px",
      "2xl": "20px",
      full: "999px"
    }
  }
}
```

## 12.2. Blade Component Strategy

Nên tạo component Blade cho các UI primitives:

```txt
resources/views/components/ui/button.blade.php
resources/views/components/ui/icon-button.blade.php
resources/views/components/ui/input.blade.php
resources/views/components/ui/avatar.blade.php
resources/views/components/ui/badge.blade.php
resources/views/components/ui/card.blade.php
resources/views/components/ui/modal.blade.php
resources/views/components/ui/dropdown.blade.php
resources/views/components/social/post-card.blade.php
resources/views/components/social/composer.blade.php
resources/views/components/social/profile-card.blade.php
resources/views/components/social/bottom-nav.blade.php
```

## 12.3. CSS Module Usage

Dùng CSS Module hoặc CSS file riêng khi:

```txt
Component có layout phức tạp.
Animation khó viết bằng Tailwind.
Cần isolate style khỏi global.
Cần reuse pattern nhiều lần.
```

Không cần CSS Module cho mọi thứ. Làm vậy chỉ khiến frontend thành mê cung class name, rồi ai đó sẽ khóc trong code review.

---

## 13. Page Design Workflow

Khi thiết kế một page mới, làm theo thứ tự:

```txt
01. Xác định user goal
02. Xác định primary action
03. Xác định content hierarchy
04. Chọn layout desktop/tablet/mobile
05. Chọn component primitives
06. Xác định states
07. Xác định empty/loading/error
08. Kiểm tra accessibility
09. Kiểm tra responsive
10. Viết page-spec markdown
11. Tạo wireframe
12. Tạo UI preview
13. Review bằng checklist
14. Implement Blade/Tailwind
15. QA bằng dữ liệu thật
```

Không nhảy từ ý tưởng sang UI pixel-perfect ngay. Đó là cách sinh ra giao diện đẹp trong ảnh nhưng gãy khi có dữ liệu thật, một thể loại bi kịch frontend rất phổ biến.

---

## 14. Page Spec Template

Mỗi file trong `page-specs/` nên dùng format này:

```md
# Page Name

## 1. Purpose
Page này giúp user làm gì.

## 2. User Goals
User vào page này để đạt mục tiêu gì.

## 3. Primary Actions
Các action chính.

## 4. Secondary Actions
Các action phụ.

## 5. Information Architecture
Nội dung trên page được tổ chức ra sao.

## 6. Desktop Layout
Layout desktop.

## 7. Tablet Layout
Layout tablet.

## 8. Mobile Layout
Layout mobile.

## 9. Components Used
Danh sách component.

## 10. States
Default, loading, empty, error, permission, offline.

## 11. Accessibility
Keyboard, aria, focus, contrast.

## 12. UX Risks
Các lỗi dễ xảy ra.

## 13. QA Checklist
Checklist review.
```

---

## 15. UI State Template

Mỗi file trong `ui-states/` nên dùng format này:

```md
# State Name

## 1. Purpose
State này xuất hiện khi nào.

## 2. Trigger
Điều kiện kích hoạt.

## 3. Visual Treatment
UI hiển thị thế nào.

## 4. Copywriting
Text nên viết gì.

## 5. Actions
User có thể làm gì tiếp.

## 6. Accessibility
ARIA, focus, screen reader.

## 7. Examples
Ví dụ trong product.

## 8. Anti-patterns
Không nên làm gì.
```

---

## 16. Example Preview Responsibility

`examples/preview.html` dùng để:

* Kiểm tra cảm giác UI tổng thể.
* Demo social layout.
* Demo component states.
* Demo responsive behavior.
* Review neutral-first design.
* Review brand usage.

`preview.html` không phải source of truth duy nhất.

Source of truth là:

```txt
Design docs
Design tokens
Component specs
Page specs
```

Nếu preview khác docs, cần sửa một trong hai. Đừng để preview và docs sống hai cuộc đời riêng như hai nhóm làm đồ án chưa merge git bao giờ.

---

## 17. Agent Prompt Rules

Khi dùng AI để generate UI hoặc code, luôn nêu rõ:

```txt
Design UEConnect as a neutral-first social platform for HCMUE students.
Use #124874 only as restrained brand accent.
Do not use gradient as default UI background.
Use content-first layout similar to professional social platforms.
Design desktop and mobile adaptively, not just responsive shrinking.
Use Laravel Blade + TailwindCSS conventions.
Include hover, focus, active, disabled, loading, empty, error states.
Use line icons such as Lucide Icons.
Avoid AI-generated UI smell: no excessive gradient, no overdecorated cards, no random colorful icons.
```

Không prompt kiểu:

```txt
Make it modern and beautiful.
Make it like Tinder.
Use a cool gradient.
Make it premium.
```

Những câu đó là lời triệu hồi quỷ gradient. Ta đã trả giá đủ rồi.

---

## 18. Definition of Done

Một design document được xem là hoàn thành khi:

* [ ] Có purpose rõ.
* [ ] Có decision cụ thể.
* [ ] Có rationale.
* [ ] Có rules.
* [ ] Có Do / Don't.
* [ ] Có token hoặc specs nếu cần.
* [ ] Có responsive note.
* [ ] Có accessibility note.
* [ ] Có QA checklist.
* [ ] Có liên kết đến component/page/state liên quan.
* [ ] Có thể dùng để implement hoặc review UI thật.

Một screen design được xem là hoàn thành khi:

* [ ] Có desktop layout.
* [ ] Có mobile layout.
* [ ] Có component list.
* [ ] Có all major states.
* [ ] Có empty/loading/error.
* [ ] Có accessibility review.
* [ ] Có responsive behavior.
* [ ] Có copywriting rõ.
* [ ] Không vi phạm color/gradient policy.
* [ ] Có thể implement bằng Blade + Tailwind mà không cần đoán.

---

## 19. Immediate Next Steps

Sau file này, tiếp tục viết theo thứ tự:

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
```

Sau khi foundation ổn, mới viết tiếp:

```txt
12-component-primitives.md
13-component-variants.md
14-interaction-states.md
```

Sau đó mới đi vào:

```txt
page-specs/home-feed.md
page-specs/discovery.md
page-specs/messaging.md
page-specs/profile.md
```

Lý do:

```txt
Foundation → Component → Page → State → Preview → Implementation
```

Không đảo thứ tự. Đảo thứ tự là cách biến design system thành bãi đỗ xe của các quyết định tạm bợ.

---

## 20. Final Principle

UEConnect không cần khoe mình có design system bằng cách tô màu mọi thứ.

UEConnect cần chứng minh mình là một social product đáng tin bằng:

```txt
Clear hierarchy
Readable content
Restrained brand usage
Good mobile behavior
Strong interaction states
Accessible components
Safe community design
Consistent implementation
```

Thiết kế tốt là khi user dùng app mà không phải nghĩ về design system.
Design system chỉ nên âm thầm làm việc, như một người trưởng thành hiếm hoi trong căn phòng toàn người thích gradient.

````
