---
title: "Gradient Policy"
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
  - "03-color-system.md"
next:
  - "05-typography-system.md"
  - "08-shadow-elevation-system.md"
  - "12-component-primitives.md"
  - "14-interaction-states.md"
related:
  - "examples/preview.html"
  - "page-specs/onboarding.md"
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
---

# 04. Gradient Policy

## 1. Purpose

File này định nghĩa chính sách sử dụng gradient trong UEConnect.

Mục tiêu:

- Ngăn việc lạm dụng gradient khiến UI nhìn AI-generated.
- Chốt rõ gradient được dùng ở đâu, không được dùng ở đâu.
- Giữ UEConnect theo hướng neutral-first social UI.
- Đảm bảo HCMUE brand blue `#124874` được dùng có kiểm soát.
- Tránh làm product giống Tinder, landing page AI hoặc app marketing hơn là social platform thật.
- Hỗ trợ frontend implement nhất quán bằng TailwindCSS, CSS variables và Laravel Blade.

Gradient không bị cấm hoàn toàn. Nhưng gradient trong UEConnect là **rare brand moment**, không phải default style.

```txt
Gradient is a special brand moment.
Gradient is not the default UI language.
````

Nói bằng tiếng người: gradient chỉ nên xuất hiện khi nó có nhiệm vụ rõ. Không phải cứ thấy trống là đổ màu chuyển sắc vào, dù lịch sử internet chứng minh con người rất thích làm vậy.

---

## 2. Final Decision

UEConnect dùng gradient theo nguyên tắc:

```txt
Default product UI: no gradient.
Brand moment: gradient allowed.
Marketing/onboarding: gradient allowed with restraint.
Feed/profile/message/admin: gradient avoided.
```

Quyết định chính thức:

| Area               | Gradient Usage                                                                                |
| ------------------ | --------------------------------------------------------------------------------------------- |
| Home Feed          | Không dùng gradient background                                                                |
| Post Card          | Không dùng gradient                                                                           |
| Navigation         | Không dùng gradient                                                                           |
| Profile            | Không dùng gradient làm layout chính                                                          |
| Discovery          | Có thể dùng gradient rất nhẹ trong illustration/empty state, không dùng dating-style gradient |
| Messaging          | Không dùng gradient bubble                                                                    |
| Mentor             | Không dùng gradient card mặc định                                                             |
| Admin              | Không dùng gradient                                                                           |
| Onboarding         | Có thể dùng gradient có kiểm soát                                                             |
| Splash / Marketing | Có thể dùng gradient như brand moment                                                         |
| Empty State        | Có thể dùng subtle tint gradient nhỏ                                                          |

---

## 3. Why Gradient Must Be Restricted

## 3.1. Gradient làm UI dễ nhìn AI-generated

Các lỗi thường gặp:

```txt
Full-page gradient background.
CTA gradient quá rực.
Card nào cũng gradient.
Hero nào cũng glowing.
Text đặt trên nền gradient thiếu contrast.
Màu chuyển không có logic brand.
```

Kết quả:

* UI nhìn như template AI.
* Content bị chìm.
* User mỏi mắt.
* Product mất cảm giác enterprise.
* Brand HCMUE bị loãng.
* Feed social mất tính đọc được.

## 3.2. UEConnect là social product, không phải landing page

UEConnect là app dùng hằng ngày:

* Đọc post.
* Viết comment.
* Nhắn tin.
* Xem profile.
* Tìm mentor.
* Tham gia cộng đồng.

Các tác vụ này cần:

```txt
Readable surfaces
Clear hierarchy
Neutral background
Subtle borders
Stable interaction states
```

Không cần:

```txt
Constant visual spectacle
```

Social app tốt thường để content thắng. Gradient chỉ nên đóng vai phụ. Một vai phụ rất hay nếu biết im đúng lúc, điều mà nhiều gradient đáng tiếc không làm được.

---

## 4. Gradient Usage Levels

UEConnect chia gradient thành 4 cấp độ.

## 4.1. Level 0 — No Gradient

Đây là default cho product UI.

Dùng cho:

```txt
Home Feed
Post Card
Comment
Messaging
Navigation
Settings
Admin Dashboard
Form
Input
Table
Modal
Dropdown
Toast
```

Rule:

```txt
Nếu không có lý do rõ, không dùng gradient.
```

## 4.2. Level 1 — Subtle Tint Gradient

Gradient rất nhẹ, gần như chỉ tạo chiều sâu thị giác.

Dùng cho:

```txt
Empty state illustration area
Small info panel
Profile cover fallback
Onboarding card background
Verified explanation panel
```

Không dùng cho:

```txt
Toàn page
Feed background
Button mặc định
Post card mặc định
Message bubble
```

## 4.3. Level 2 — Brand Moment Gradient

Gradient có nhận diện rõ hơn, nhưng chỉ dùng ở các khoảnh khắc thương hiệu.

Dùng cho:

```txt
Splash screen
First onboarding screen
Marketing hero
App store/social preview
Launch campaign
Official product teaser
```

Không dùng cho:

```txt
Daily product screen
Feed
Messaging
Admin
Settings
Post detail
```

## 4.4. Level 3 — Campaign Gradient

Dành cho chiến dịch đặc biệt.

Dùng cho:

```txt
Event campaign
Official HCMUE campaign
Club festival banner
Mentor launch event
```

Điều kiện:

* Phải có campaign cụ thể.
* Có thời hạn.
* Không thay đổi design system chính.
* Không trở thành default UI.

---

## 5. Approved Gradients

## 5.1. Brand Moment Gradient

Gradient chính thức cho brand moment:

```css
--ue-brand-moment-gradient: linear-gradient(
  135deg,
  #0B3558 0%,
  #124874 38%,
  #2178D4 72%,
  #38BDF8 100%
);
```

Ý nghĩa:

| Stop | Color     | Role                  |
| ---- | --------- | --------------------- |
| 0%   | `#0B3558` | Deep trust, depth     |
| 38%  | `#124874` | HCMUE Cerulean anchor |
| 72%  | `#2178D4` | Digital/social energy |
| 100% | `#38BDF8` | Youthful highlight    |

Dùng cho:

* Splash screen.
* First onboarding screen.
* Marketing hero.
* App launch graphic.
* Official product preview.

Không dùng cho:

* Feed.
* Post.
* Comment.
* Message bubble.
* Admin panel.
* Button mặc định.

## 5.2. Subtle Tint Gradient

Gradient nhẹ cho các vùng nhỏ:

```css
--ue-subtle-tint-gradient: linear-gradient(
  135deg,
  rgba(18, 72, 116, 0.06) 0%,
  rgba(56, 189, 248, 0.08) 100%
);
```

Dùng cho:

* Empty state illustration background.
* Verified info panel.
* Profile cover fallback.
* Onboarding feature card.
* Mentor suggestion highlight rất nhẹ.

Rule:

```txt
Nếu người dùng nhận ra gradient quá rõ trong product UI, có thể nó đã quá tay.
```

## 5.3. Verification Gradient, Optional

Dùng rất hạn chế cho verification-related brand moment:

```css
--ue-verification-gradient: linear-gradient(
  135deg,
  rgba(18, 72, 116, 0.10) 0%,
  rgba(32, 201, 151, 0.10) 100%
);
```

Dùng cho:

* Account approved illustration background.
* Verified UEer explainer.
* Trust onboarding card.

Không dùng cho:

* Verification form background toàn màn hình.
* Admin review table.
* Every verified badge.

## 5.4. Mentor Gradient, Optional

Dùng rất nhẹ cho mentor launch/empty state:

```css
--ue-mentor-tint-gradient: linear-gradient(
  135deg,
  rgba(18, 72, 116, 0.06) 0%,
  rgba(124, 92, 255, 0.08) 100%
);
```

Dùng cho:

* Mentor empty state.
* Mentor onboarding card.
* Mentor campaign banner.

Không dùng cho:

* Mentor cards mặc định.
* Mentor profile toàn trang.
* Career dashboard.

Mentor là một tính năng chính, nhưng không phải lý do để biến UI thành hồ bơi tím.

---

## 6. Forbidden Gradients

## 6.1. Tinder-style Pink/Orange Gradient

Không dùng:

```css
background: linear-gradient(135deg, #FD267A, #FF6036);
```

Lý do:

* Gợi dating app.
* Conflict với HCMUE identity.
* Không phù hợp với verified student social platform.
* Làm discovery bị hiểu sai.

UEConnect có thể học interaction từ Tinder cho Discovery, nhưng không copy màu, ngôn ngữ hoặc vibe dating.

## 6.2. Blue + Red Primary Gradient

Không dùng:

```css
background: linear-gradient(135deg, #124874, #CF373D);
```

Lý do:

* Chuyển màu gắt.
* Không hài hòa với UI social neutral-first.
* Dễ tạo cảm giác chói.
* Kéo UI về hướng campaign/poster thay vì product.
* Đã được loại bỏ khỏi primary design direction.

## 6.3. Full-page Product Gradient

Không dùng:

```css
.app-shell {
  background: linear-gradient(...);
}
```

cho các màn hình:

```txt
Home Feed
Messaging
Profile
Discovery daily use
Settings
Admin
Post Detail
Notifications
```

## 6.4. Spotlight Gradient

Không dùng kiểu:

```css
background: radial-gradient(circle at center, #E0F2FE, #0B3558);
```

trong product screen.

Lý do:

* Tạo vùng giữa quá sáng hoặc quá nhạt.
* Làm content hierarchy khó kiểm soát.
* Nhìn giống AI splash screen.
* Không giống social platform thật.

## 6.5. Gradient Text

Không dùng gradient text mặc định:

```css
.heading {
  background: linear-gradient(...);
  -webkit-background-clip: text;
  color: transparent;
}
```

Chỉ cân nhắc cho marketing campaign đặc biệt, không dùng trong product UI.

## 6.6. Gradient Border Everywhere

Không dùng:

```css
.card {
  border-image: linear-gradient(...) 1;
}
```

cho card/post/profile/message.

Gradient border rất dễ nhìn “premium giả”, một thể loại premium mà ai cũng thấy nhưng không ai tin.

---

## 7. Page-level Gradient Rules

## 7.1. Onboarding

Gradient allowed: **Yes, controlled**

Dùng được:

* First screen brand moment.
* Small visual area.
* Background illustration.
* Verification explainer.

Không dùng:

* Mọi step onboarding đều gradient.
* Text dài trên gradient.
* Gradient quá rực.
* Gradient red/pink/orange.

Recommended:

```css
.onboarding-hero {
  background: var(--ue-brand-moment-gradient);
}
```

hoặc nhẹ hơn:

```css
.onboarding-card {
  background: var(--ue-subtle-tint-gradient);
}
```

## 7.2. Auth

Gradient allowed: **Rare**

Signup/login nên ưu tiên trust và clarity.

Dùng được:

* Small brand panel bên trái trên desktop.
* Subtle tint ở illustration.
* Không dùng gradient sau form.

Không dùng:

* Full-screen gradient sau input.
* Button gradient mặc định.
* Text trắng trên gradient nếu contrast không chắc.

## 7.3. Account Verification

Gradient allowed: **Subtle only**

Dùng được:

* Verification info panel.
* Approved state illustration.
* Small trust card.

Không dùng:

* Admin verification queue.
* Form area chính.
* Rejection/error state.

## 7.4. Home Feed

Gradient allowed: **No**

Không dùng gradient trong:

* Feed background.
* Post card.
* Composer.
* Comment.
* Feed tabs.
* Right panel card.

Dùng neutral:

```css
.feed {
  background: var(--ue-surface);
}
```

## 7.5. Discovery

Gradient allowed: **Very limited**

Dùng được:

* Empty state.
* Small background decoration.
* Profile cover fallback rất nhẹ.

Không dùng:

* Full-screen Tinder-style gradient.
* Pink/orange/red.
* Large swipe card gradient.
* CTA gradient.

Discovery phải hấp dẫn bằng content, profile context và motion, không phải bằng màu rực như app hẹn hò.

## 7.6. Profile

Gradient allowed: **Limited**

Dùng được:

* Profile cover fallback nhẹ.
* Empty profile completion card.
* Verified info block.

Không dùng:

* Toàn bộ profile header gradient nặng.
* Mỗi profile một gradient ngẫu nhiên.
* Gradient avatar ring.

## 7.7. Messaging

Gradient allowed: **No**

Không dùng:

* Gradient message bubble.
* Gradient conversation background.
* Gradient inbox item.
* Gradient send button mặc định.

Messaging cần đọc nhanh. Tin nhắn không cần cosplay poster.

## 7.8. Mentor

Gradient allowed: **Subtle / Campaign only**

Dùng được:

* Mentor empty state.
* Mentor launch banner.
* Mentor onboarding card.

Không dùng:

* Mentor profile card mặc định.
* Mentor list background.
* Career dashboard background.

## 7.9. Community / Club

Gradient allowed: **Campaign only**

Dùng được:

* Event banner.
* Club campaign.
* Festival announcement.

Không dùng:

* Community feed.
* Community chat.
* Member list.
* Club detail default background.

## 7.10. Admin

Gradient allowed: **No**

Admin UI phải:

```txt
Neutral
Task-oriented
Readable
Audit-friendly
```

Không dùng gradient trong admin dashboard, moderation queue, verification review. Admin đã đủ khổ với report queue rồi, đừng bắt họ xử lý thêm màu mè.

---

## 8. Component-level Gradient Rules

## 8.1. Buttons

Default:

```txt
No gradient.
```

Primary button:

```css
.button-primary {
  background: var(--ue-brand);
}
```

Gradient button chỉ được dùng cho:

* Marketing hero CTA.
* App launch campaign.
* Rare onboarding hero CTA.

Không dùng gradient button cho:

* Composer submit.
* Save.
* Follow/connect.
* Send message.
* Admin action.
* Delete/report.

## 8.2. Cards

Default card:

```css
.card {
  background: var(--ue-surface);
  border: 1px solid var(--ue-border);
}
```

Subtle gradient card chỉ được dùng cho:

* Empty state card.
* Onboarding feature card.
* Verification explanation card.

Không dùng gradient cho:

* Post card.
* Comment card.
* Message preview.
* Admin table row.
* Profile info card.

## 8.3. Badges

Default badge không dùng gradient.

Verified badge:

```css
.verified-badge {
  color: var(--ue-brand);
  background: rgba(18, 72, 116, 0.08);
  border: 1px solid rgba(18, 72, 116, 0.14);
}
```

Không dùng gradient badge. Badge nhỏ mà còn gradient thì chỉ khiến UI giống sticker pack.

## 8.4. Icons

Không dùng gradient icon trong product UI.

Dùng:

```txt
Neutral icon
Brand icon for active state
Danger icon for destructive state
```

Không dùng:

```txt
Gradient stroke
Multi-color icon set
Random colorful icons
```

## 8.5. Skeleton / Loading

Không dùng colorful gradient skeleton.

Dùng neutral shimmer:

```css
.skeleton {
  background: linear-gradient(
    90deg,
    #F1F5F9 0%,
    #E4E6EB 50%,
    #F1F5F9 100%
  );
}
```

Skeleton gradient được phép vì đây là loading technique, không phải brand gradient.

---

## 9. Gradient Token System

## 9.1. CSS Variables

```css
:root {
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

  --ue-verification-gradient: linear-gradient(
    135deg,
    rgba(18, 72, 116, 0.10) 0%,
    rgba(32, 201, 151, 0.10) 100%
  );

  --ue-mentor-tint-gradient: linear-gradient(
    135deg,
    rgba(18, 72, 116, 0.06) 0%,
    rgba(124, 92, 255, 0.08) 100%
  );

  --ue-skeleton-gradient: linear-gradient(
    90deg,
    #F1F5F9 0%,
    #E4E6EB 50%,
    #F1F5F9 100%
  );
}
```

## 9.2. Tailwind Mapping

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      backgroundImage: {
        "ue-brand-moment":
          "linear-gradient(135deg, #0B3558 0%, #124874 38%, #2178D4 72%, #38BDF8 100%)",
        "ue-subtle-tint":
          "linear-gradient(135deg, rgba(18,72,116,0.06) 0%, rgba(56,189,248,0.08) 100%)",
        "ue-verification":
          "linear-gradient(135deg, rgba(18,72,116,0.10) 0%, rgba(32,201,151,0.10) 100%)",
        "ue-mentor-tint":
          "linear-gradient(135deg, rgba(18,72,116,0.06) 0%, rgba(124,92,255,0.08) 100%)",
        "ue-skeleton":
          "linear-gradient(90deg, #F1F5F9 0%, #E4E6EB 50%, #F1F5F9 100%)",
      },
    },
  },
}
```

## 9.3. Naming Rules

Gradient token naming:

```txt
ue-[purpose]-gradient
```

Allowed names:

```txt
ue-brand-moment-gradient
ue-subtle-tint-gradient
ue-verification-gradient
ue-mentor-tint-gradient
ue-skeleton-gradient
```

Không đặt tên kiểu:

```txt
cool-gradient
main-gradient
blue-gradient
pretty-bg
tinder-like-gradient
```

Tên token phải nói rõ mục đích. Không ai muốn debug `pretty-bg` lúc 1 giờ sáng, dù nhân loại vẫn tự tạo ra những tai họa như vậy.

---

## 10. Implementation Examples

## 10.1. Onboarding Brand Moment

```blade
<section class="relative overflow-hidden rounded-2xl bg-ue-brand-moment p-8 text-white">
  <div class="max-w-xl">
    <p class="text-sm font-semibold opacity-90">
      HCMUE Verified Student Social Platform
    </p>

    <h1 class="mt-3 text-3xl font-bold tracking-tight">
      Kết nối và làm quen chuẩn HCMUEr.
    </h1>

    <p class="mt-4 text-white/85">
      Khám phá UEers, tìm bạn cùng khoa, cùng học và kết nối mentor trong một không gian đã xác thực.
    </p>
  </div>
</section>
```

Rule:

* Chỉ dùng trong onboarding/marketing.
* Text phải đủ contrast.
* Không nhồi quá nhiều nội dung lên gradient.

## 10.2. Subtle Empty State

```blade
<div class="rounded-2xl border border-ue-border bg-ue-subtle-tint p-6 text-center">
  <h3 class="text-base font-semibold text-ue-text">
    Feed của bạn đang hơi yên ắng
  </h3>

  <p class="mt-2 text-sm text-ue-text-secondary">
    Khám phá UEers cùng khoa hoặc tạo bài viết đầu tiên để bắt đầu.
  </p>
</div>
```

Rule:

* Gradient nhẹ.
* Nội dung vẫn dùng text neutral.
* CTA nếu có vẫn dùng solid button.

## 10.3. Verification Approved Panel

```blade
<div class="rounded-xl border border-success-border bg-ue-verification p-4">
  <div class="flex items-start gap-3">
    <x-icon.badge-check class="h-5 w-5 text-success-text" />

    <div>
      <h3 class="text-sm font-semibold text-ue-text">
        Tài khoản đã được xác thực
      </h3>
      <p class="mt-1 text-sm text-ue-text-secondary">
        Bạn đã trở thành Verified UEer và có thể bắt đầu kết nối trong cộng đồng HCMUE.
      </p>
    </div>
  </div>
</div>
```

---

## 11. Gradient Accessibility Rules

## 11.1. Contrast

Text trên gradient phải đạt contrast.

Rules:

* Dùng white text chỉ khi nền đủ đậm.
* Nếu gradient có vùng sáng, đặt overlay hoặc không đặt text lên đó.
* Không dùng text muted trên gradient.
* Không đặt body paragraph dài trên gradient.
* Không dùng gradient sau form input.

## 11.2. Overlay

Nếu cần text trên brand gradient:

```css
.gradient-text-panel {
  background:
    linear-gradient(rgba(2, 6, 23, 0.28), rgba(2, 6, 23, 0.28)),
    var(--ue-brand-moment-gradient);
}
```

Không dùng overlay quá tối khiến màu brand mất hẳn.

## 11.3. Reduced Motion

Nếu gradient có animation, phải hỗ trợ reduced motion.

Default:

```txt
No animated gradient.
```

Nếu campaign cần animated gradient:

```css
@media (prefers-reduced-motion: reduce) {
  .animated-gradient {
    animation: none;
  }
}
```

Nhưng khuyến nghị: đừng animate gradient trong product UI. Thứ chuyển động liên tục phía sau content là món quà tồi tệ cho khả năng tập trung.

---

## 12. Gradient and Dark Mode Preparation

Dark mode chưa triển khai ngay, nhưng gradient token phải không phá dark mode.

Rules:

* Không hard-code gradient vào component nếu không qua token.
* Không dùng gradient làm nền bắt buộc của component.
* Dark mode có thể dùng cùng gradient brand moment nhưng cần kiểm tra contrast.
* Subtle tint gradient cần phiên bản dark riêng nếu sau này bật dark mode.

Gợi ý dark tokens sau này:

```css
[data-theme="dark"] {
  --ue-subtle-tint-gradient: linear-gradient(
    135deg,
    rgba(18, 72, 116, 0.22) 0%,
    rgba(56, 189, 248, 0.12) 100%
  );

  --ue-verification-gradient: linear-gradient(
    135deg,
    rgba(18, 72, 116, 0.22) 0%,
    rgba(32, 201, 151, 0.12) 100%
  );
}
```

---

## 13. Decision Tree

Dùng decision tree này trước khi thêm gradient.

```txt
Bạn có đang thiết kế product screen dùng hằng ngày không?
├─ Có → Không dùng gradient.
└─ Không
   └─ Đây có phải onboarding/marketing/campaign không?
      ├─ Không → Không dùng gradient.
      └─ Có
         └─ Gradient có giúp tăng brand/trust không?
            ├─ Không → Không dùng gradient.
            └─ Có
               └─ Text contrast có đảm bảo không?
                  ├─ Không → Sửa hoặc bỏ gradient.
                  └─ Có → Dùng approved gradient token.
```

Câu hỏi nhanh:

```txt
Gradient này có nhiệm vụ cụ thể không?
Nếu bỏ gradient, UI có dễ dùng hơn không?
Gradient có làm content khó đọc không?
Gradient có làm app giống dating/AI landing không?
Gradient có dùng đúng token không?
```

Nếu trả lời lúng túng, bỏ gradient. Một quyết định thiết kế trưởng thành hiếm hoi.

---

## 14. Gradient QA Checklist

## 14.1. Usage

* [ ] Gradient có thuộc approved token không?
* [ ] Gradient có dùng đúng page/context không?
* [ ] Gradient có phải rare brand moment không?
* [ ] Có tránh dùng gradient trong feed/post/message/admin không?
* [ ] Có tránh gradient red/pink/orange không?

## 14.2. Visual Quality

* [ ] Gradient chuyển màu mượt không?
* [ ] Không có spotlight sáng giữa gây cấn mắt không?
* [ ] Không có top/bottom quá đậm và giữa quá nhạt không?
* [ ] Không làm UI nhìn AI-generated không?
* [ ] Không làm content bị chìm không?

## 14.3. Accessibility

* [ ] Text contrast đạt không?
* [ ] Không đặt paragraph dài trên gradient không?
* [ ] Focus state vẫn rõ không?
* [ ] Nếu có animation, có reduced motion không?
* [ ] Không truyền ý nghĩa chỉ bằng gradient không?

## 14.4. Product Fit

* [ ] Có giữ neutral-first direction không?
* [ ] Có giữ HCMUE identity đúng mức không?
* [ ] Có tránh dating vibe không?
* [ ] Có tránh portal trường quá nặng không?
* [ ] Có phù hợp social platform dùng hằng ngày không?

---

## 15. Common Mistakes and Fixes

## 15.1. Mistake: Full Feed Gradient

Sai:

```css
.home-feed {
  background: var(--ue-brand-moment-gradient);
}
```

Sửa:

```css
.home-feed {
  background: var(--ue-bg);
}
```

## 15.2. Mistake: Gradient Primary Button Everywhere

Sai:

```css
.button-primary {
  background: var(--ue-brand-moment-gradient);
}
```

Sửa:

```css
.button-primary {
  background: var(--ue-brand);
}
```

## 15.3. Mistake: Discovery Looks Like Dating App

Sai:

```css
.discovery {
  background: linear-gradient(135deg, #FD267A, #FF6036);
}
```

Sửa:

```css
.discovery {
  background: var(--ue-bg);
}
```

Dùng brand blue cho CTA `Gửi lời chào`, không dùng dating palette.

## 15.4. Mistake: Profile Cover Too Loud

Sai:

```css
.profile-cover {
  background: var(--ue-brand-moment-gradient);
}
```

Sửa:

```css
.profile-cover {
  background: var(--ue-subtle-tint-gradient);
}
```

hoặc:

```css
.profile-cover {
  background: var(--ue-surface-subtle);
}
```

## 15.5. Mistake: Gradient Behind Form

Sai:

```css
.auth-form {
  background: var(--ue-brand-moment-gradient);
}
```

Sửa:

```css
.auth-form {
  background: var(--ue-surface);
}
```

Có thể đặt gradient ở side visual panel, không đặt sau input.

---

## 16. Relationship with Other Files

| File                            | Relationship                                     |
| ------------------------------- | ------------------------------------------------ |
| `02-brand-identity-hcmue.md`    | Chốt brand identity và vai trò HCMUE             |
| `03-color-system.md`            | Cung cấp color tokens nền                        |
| `05-typography-system.md`       | Đảm bảo text trên gradient có hierarchy/contrast |
| `08-shadow-elevation-system.md` | Tránh dùng gradient để thay shadow/elevation     |
| `12-component-primitives.md`    | Component mặc định không dùng gradient           |
| `14-interaction-states.md`      | Hover/focus/active không dùng gradient tùy tiện  |
| `17-accessibility-rules.md`     | Kiểm tra contrast, reduced motion                |
| `20-agent-prompt-guide.md`      | Cấm AI lạm dụng gradient                         |

---

## 17. AI Prompt Notes

Khi yêu cầu AI thiết kế hoặc code UI cho UEConnect, thêm:

```txt
Follow UEConnect Gradient Policy.
Do not use gradients in daily product screens such as feed, post cards, comments, messaging, settings, admin, or profile content areas.
Gradients are allowed only as rare brand moments for onboarding, splash, marketing hero, campaign banners, or very subtle empty-state illustration backgrounds.
Use the approved HCMUE blue-based gradient only:
linear-gradient(135deg, #0B3558 0%, #124874 38%, #2178D4 72%, #38BDF8 100%).
Do not use red, pink, orange, or Tinder-like gradients.
Do not use blue-red gradients.
Do not use gradient buttons by default.
Keep UI neutral-first, content-first, readable, and enterprise-ready.
```

---

## 18. Final Decision

Gradient policy chính thức của UEConnect:

```txt
Gradient is allowed.
Gradient is rare.
Gradient is not the default.
Gradient must never overpower content.
Gradient must never make UEConnect look like a dating app, school portal, or AI-generated landing page.
```

Câu chốt:

```txt
UEConnect dùng gradient như một điểm nhấn thương hiệu hiếm hoi, không dùng gradient như bình chữa cháy cho mọi khoảng trống thị giác.
```
