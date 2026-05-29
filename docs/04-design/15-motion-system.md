---
title: "Motion System"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UI Design / Frontend"
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
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
  - "21-social-interaction-patterns.md"
related:
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
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

# Motion System

## 1. Purpose

Motion System định nghĩa cách UEConnect sử dụng animation, transition và micro-interaction trên toàn bộ PWA.

Motion trong UEConnect phải giúp:

- Giao diện phản hồi rõ hơn.
- Chuyển cảnh mềm hơn.
- Giảm cảm giác “đơ” khi loading.
- Giúp user hiểu quan hệ giữa các trạng thái.
- Tăng cảm giác hiện đại, thân thiện, đáng tin cậy.
- Hỗ trợ mobile/PWA interaction tốt hơn.
- Không gây chóng mặt, rối mắt, hoặc làm app giống dating game.

Motion không phải để khoe kỹ thuật. Motion là feedback có chủ đích.

Nếu animation không giải thích được trạng thái hoặc không cải thiện cảm giác dùng, bỏ. Không phải mọi thứ biết chuyển động đều nên chuyển động. Quạt trần cũng chuyển động, đâu có nghĩa là nó nên xuất hiện trong UI.

---

## 2. Motion Principles

## 2.1. Calm and Useful

Motion của UEConnect phải:

```txt
calm
subtle
purposeful
fast
accessible
predictable
student-friendly
````

Không được:

```txt
flashy
bouncy quá mức
game-like
dating-like
slow
distracting
dramatic
```

## 2.2. Motion Explains State

Motion nên trả lời:

```txt
Cái gì vừa xuất hiện?
Cái gì vừa biến mất?
Cái gì đang được chọn?
Cái gì đang xử lý?
Cái gì chuyển từ trạng thái này sang trạng thái khác?
```

Ví dụ tốt:

```txt
User gửi lời chào
→ button loading
→ toast xuất hiện
→ card chuyển sang pending state
```

Ví dụ tệ:

```txt
User gửi lời chào
→ card xoay 3D
→ icon bay lên
→ sparkle
→ người dùng mất niềm tin
```

## 2.3. Fast by Default

UEConnect là PWA, nhiều interaction nằm trên mobile. Motion phải nhanh.

Rule:

```txt
Most UI transitions should finish under 220ms.
```

Các animation dài chỉ dùng cho:

```txt
splash
empty state illustration
onboarding hero
major success state
```

Và cũng phải tiết chế. Đây là app trường học, không phải intro phim siêu anh hùng.

## 2.4. Motion Must Respect Accessibility

Phải hỗ trợ:

```txt
prefers-reduced-motion
```

Nếu user bật reduced motion:

```txt
- tắt animation lớn
- tắt parallax
- tắt shimmer mạnh
- giảm slide/scale
- dùng fade nhẹ hoặc instant state change
```

---

# 3. Motion Token System

## 3.1. Duration Tokens

| Token                     |   Value | Usage                            |
| ------------------------- | ------: | -------------------------------- |
| `motion.duration.instant` |   `0ms` | Reduced motion / immediate state |
| `motion.duration.xs`      |  `75ms` | Very small feedback              |
| `motion.duration.sm`      | `120ms` | Button hover/press               |
| `motion.duration.md`      | `180ms` | Default UI transition            |
| `motion.duration.lg`      | `240ms` | Modal/sheet/dropdown             |
| `motion.duration.xl`      | `320ms` | Page section transition          |
| `motion.duration.2xl`     | `480ms` | Splash/onboarding only           |

Recommended default:

```txt
180ms
```

## 3.2. Easing Tokens

| Token                    | CSS                             | Usage                 |
| ------------------------ | ------------------------------- | --------------------- |
| `motion.ease.standard`   | `cubic-bezier(0.2, 0, 0, 1)`    | Default               |
| `motion.ease.out`        | `cubic-bezier(0, 0, 0.2, 1)`    | Enter animation       |
| `motion.ease.in`         | `cubic-bezier(0.4, 0, 1, 1)`    | Exit animation        |
| `motion.ease.inOut`      | `cubic-bezier(0.4, 0, 0.2, 1)`  | State change          |
| `motion.ease.emphasized` | `cubic-bezier(0.16, 1, 0.3, 1)` | Modal/sheet           |
| `motion.ease.linear`     | `linear`                        | Progress/spinner only |

## 3.3. Distance Tokens

| Token                 |  Value | Usage                |
| --------------------- | -----: | -------------------- |
| `motion.distance.xs`  |  `2px` | Button press         |
| `motion.distance.sm`  |  `4px` | Hover lift           |
| `motion.distance.md`  |  `8px` | Dropdown/small enter |
| `motion.distance.lg`  | `16px` | Modal/sheet content  |
| `motion.distance.xl`  | `24px` | Page section         |
| `motion.distance.2xl` | `40px` | Large mobile sheet   |

## 3.4. Scale Tokens

| Token                 |      Value | Usage                         |
| --------------------- | ---------: | ----------------------------- |
| `motion.scale.press`  |     `0.98` | Button/card press             |
| `motion.scale.enter`  | `0.98 → 1` | Modal/card enter              |
| `motion.scale.exit`   | `1 → 0.98` | Modal/card exit               |
| `motion.scale.avatar` | `0.96 → 1` | Avatar/profile small entrance |

## 3.5. Opacity Tokens

| Token                    | Value |
| ------------------------ | ----: |
| `motion.opacity.hidden`  |   `0` |
| `motion.opacity.muted`   | `0.6` |
| `motion.opacity.visible` |   `1` |

## 3.6. CSS Variables & Tailwind Configuration

Để hỗ trợ đắc lực cho việc tương tác mạng xã hội kiểu Threads, UEConnect định nghĩa các CSS Variables và Tailwind aliases sau:

### CSS Variables (`:root`)
```css
:root {
  --motion-duration-instant: 75ms;
  --motion-duration-fast: 120ms;
  --motion-duration-base: 180ms;
  --motion-duration-slow: 240ms;
  --motion-duration-sheet: 280ms;

  --motion-ease-standard: cubic-bezier(0.2, 0, 0, 1);
  --motion-ease-out: cubic-bezier(0, 0, 0.2, 1);
  --motion-ease-in: cubic-bezier(0.4, 0, 1, 1);
  --motion-ease-emphasized: cubic-bezier(0.2, 0, 0, 1.2);

  --motion-press-scale: 0.98;
  --motion-popover-scale-from: 0.96;
  --motion-like-scale: 1.16;
}
```

### Tailwind Aliases
```js
transitionDuration: {
  instant: '75ms',
  fast: '120ms',
  base: '180ms',
  slow: '240ms',
  sheet: '280ms',
},
transitionTimingFunction: {
  standard: 'cubic-bezier(0.2, 0, 0, 1)',
  out: 'cubic-bezier(0, 0, 0.2, 1)',
  in: 'cubic-bezier(0.4, 0, 1, 1)',
  emphasized: 'cubic-bezier(0.2, 0, 0, 1.2)',
}
```

---

# 4. Motion Categories

## 4.1. Micro-interactions

Small feedback on controls:

```txt
button hover
button press
icon button hover
card hover
chip selected
switch toggle
checkbox checked
tab change
```

Duration:

```txt
75ms - 180ms
```

## 4.2. Component Transitions

Component-level appear/disappear:

```txt
dropdown
tooltip
toast
modal
drawer
bottom sheet
accordion
filter panel
```

Duration:

```txt
120ms - 240ms
```

## 4.3. Page / Section Transitions

Large UI changes:

```txt
onboarding step
verification step
settings section
admin detail drawer
community tab
profile tab
```

Duration:

```txt
180ms - 320ms
```

## 4.4. Loading Motion

Used to show waiting state:

```txt
spinner
skeleton pulse
skeleton shimmer
upload progress
message sending
realtime reconnect
```

Must be subtle.

## 4.5. Feedback Motion

Used after action:

```txt
toast enter
success check
error shake, rare
notification unread highlight
message failed marker
```

No excessive celebration.

---

# 5. Global Motion Rules

## 5.1. What Can Animate

Allowed properties:

```txt
opacity
transform
box-shadow
background-color
border-color
color
filter, carefully
height, only if necessary and controlled
```

Preferred:

```txt
opacity + transform
```

Avoid animating:

```txt
width
left
top
margin
padding
font-size
layout-heavy properties
```

Cảm ơn CSS layout engine vì đã chịu đủ khổ. Đừng bắt nó animate margin trên feed dài 200 bài.

## 5.2. No Layout Shift

Motion không được làm layout nhảy lung tung.

Rules:

```txt
- reserve space for loading state
- skeleton should match final layout
- button loading should preserve width
- toast should not push main layout
- modal should overlay, not reflow page
```

## 5.3. Motion Should Be Interruptible

User có thể:

```txt
close modal
cancel sheet
navigate away
retry action
```

Animation không được khóa UI quá lâu.

## 5.4. Respect Product Seriousness

Serious contexts cần motion tối giản:

```txt
verification rejection
account suspended
report submitted
moderation action
admin permission change
identity conflict
safety warning
```

Không dùng:

```txt
confetti
bounce
cute illustration animation
sparkle
playful sound, nếu sau này có sound thì cũng không
```

---

# 6. Reduced Motion

## 6.1. CSS Requirement

Must support:

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.001ms !important;
    scroll-behavior: auto !important;
  }
}
```

But preferably implement more gracefully through tokens.

## 6.2. Reduced Motion Behavior

| Normal Motion      | Reduced Motion           |
| ------------------ | ------------------------ |
| Slide + fade       | Fade only or instant     |
| Scale enter        | Fade only                |
| Skeleton shimmer   | Static skeleton          |
| Card lift          | Border/background change |
| Toast slide        | Fade only                |
| Bottom sheet slide | Instant/fade             |
| Page transition    | Instant                  |
| Swipe animation    | Direct state change      |

## 6.3. Never Force Motion

Do not force:

```txt
parallax
continuous loop
large zoom
background particle animation
auto-moving carousel
```

UEConnect không cần nền có bong bóng bay để chứng minh mình hiện đại. Người dùng cần app chạy được.

---

# 7. Button Motion

## 7.1. Hover

Desktop:

```txt
duration: motion.duration.sm
easing: motion.ease.standard
```

Allowed:

```txt
background-color change
border-color change
shadow slight increase
translateY(-1px), optional
```

Do not:

```txt
scale up too much
bounce
rotate icon
```

## 7.2. Press / Active

Recommended:

```txt
transform: scale(0.98)
duration: 75ms
```

Or:

```txt
translateY(1px)
shadow decrease
```

## 7.3. Loading

Button loading state:

```txt
show spinner
disable action
preserve width
text can become "Đang gửi..."
```

Examples:

```txt
Gửi lời chào → Đang gửi...
Lưu thay đổi → Đang lưu...
Tham gia → Đang xử lý...
```

## 7.4. Success

For small action:

```txt
toast only
```

For inline:

```txt
button may briefly show success icon for 800ms - 1200ms
```

Use sparingly.

## 7.5. Danger Button

Danger button hover should be clear but not theatrical.

Do not animate danger action with playful bounce. Đang xóa dữ liệu, không phải bóp bong bóng.

---

# 8. Card Motion

## 8.1. Interactive Card Hover

Use for:

```txt
mentor card
community card
career pathway card
search result card
profile card
```

Recommended:

```txt
shadow.sm → shadow.md
border.default → border.brand-subtle
translateY(-2px)
duration: 180ms
```

## 8.2. Card Press

Recommended:

```txt
translateY(0)
scale(0.995)
duration: 75ms
```

## 8.3. Card Enter

For lists:

```txt
fade in
translateY(4px → 0)
duration: 180ms
stagger: optional, max 30ms between items
```

Do not over-stagger long lists.

## 8.4. Feed Cards

Feed should avoid dramatic enter animations.

Recommended:

```txt
first load: skeleton → content fade
new post inserted: soft highlight for 1200ms
```

## 8.5. Discovery Card Motion

Discovery may use swipe motion, but not dating-like.

Allowed:

```txt
card slide left/right on pass/greeting
subtle fade/translate
button fallback
```

Forbidden:

```txt
heart burst
match celebration
flame icons
romantic motion language
```

---

# 9. Modal Motion

## 9.1. Modal Enter

Recommended:

```txt
overlay fade in
modal opacity 0 → 1
modal scale 0.98 → 1
modal translateY(8px → 0)
duration: 180ms - 240ms
easing: motion.ease.emphasized
```

## 9.2. Modal Exit

Recommended:

```txt
overlay fade out
modal opacity 1 → 0
modal scale 1 → 0.98
duration: 120ms - 180ms
easing: motion.ease.in
```

## 9.3. Danger Modal

Use same motion as normal modal.

Do not add shake by default.

Shake may be used only for validation error, not for “dramatic danger”.

## 9.4. Modal Focus

After modal opens:

```txt
focus first meaningful interactive element
or modal title if no immediate input
```

Motion must not delay focus.

## 9.5. Mobile Modal

For complex mobile modal:

```txt
prefer full-screen sheet or bottom sheet
```

---

# 10. Sheet / Drawer Motion

## 10.1. Bottom Sheet Enter

Recommended:

```txt
overlay fade in
sheet translateY(100% → 0)
duration: 240ms
easing: motion.ease.emphasized
```

## 10.2. Bottom Sheet Exit

```txt
sheet translateY(0 → 100%)
overlay fade out
duration: 180ms
```

## 10.3. Side Drawer Enter

```txt
drawer translateX(100% → 0)
overlay fade in
duration: 240ms
```

For left drawer:

```txt
translateX(-100% → 0)
```

## 10.4. Use Cases

```txt
filter sheet
profile quick view
admin detail panel
community member actions
notification panel
```

## 10.5. Rules

```txt
- Sheet must not feel slow.
- Sheet must trap focus.
- Drag-to-close future optional.
- Reduced motion uses fade/instant.
```

---

# 11. Dropdown / Tooltip Motion

## 11.1. Dropdown Enter

Recommended:

```txt
opacity 0 → 1
translateY(-4px → 0)
scale 0.98 → 1
duration: 120ms
```

## 11.2. Dropdown Exit

```txt
opacity 1 → 0
translateY(0 → -4px)
duration: 90ms
```

## 11.3. Tooltip Enter

Recommended:

```txt
delay: 300ms - 500ms
fade in
translateY(2px → 0)
duration: 120ms
```

## 11.4. Tooltip Rules

```txt
- Tooltip should not appear instantly on accidental hover.
- Tooltip should not contain critical-only information.
- Mobile cannot rely on tooltip.
```

---

# 12. Toast Motion

## 12.1. Toast Enter

Recommended:

Desktop:

```txt
opacity 0 → 1
translateY(-8px → 0)
duration: 180ms
```

Mobile:

```txt
opacity 0 → 1
translateY(12px → 0)
duration: 180ms
```

## 12.2. Toast Exit

```txt
opacity 1 → 0
translateY(0 → -8px or 8px)
duration: 120ms
```

## 12.3. Toast Duration

| Toast Type |         Display Duration |
| ---------- | -----------------------: |
| success    |                   3000ms |
| info       |                   3500ms |
| warning    |                   4500ms |
| error      | 5000ms or manual dismiss |
| loading    |           until resolved |

## 12.4. Rules

```txt
- Do not stack too many toasts.
- Max visible toasts: 3.
- Important errors should not only be toast.
- Toast should use aria-live.
```

---

# 13. Alert Motion

## 13.1. Alert Enter

Recommended:

```txt
fade in
translateY(-4px → 0)
duration: 180ms
```

## 13.2. Dismissible Alert Exit

```txt
fade out
height collapse only if controlled
duration: 180ms
```

## 13.3. Persistent Alerts

For critical states:

```txt
no repeating animation
no pulsing
no flashing
```

Examples:

```txt
account restricted
community suspended
verification rejected
```

Nếu cần người dùng chú ý, dùng copy rõ ràng. Đừng làm banner nhấp nháy như biển hiệu sửa xe.

---

# 14. Tabs / Segmented Control Motion

## 14.1. Active Indicator

Recommended:

```txt
indicator translate/resize
duration: 180ms
easing: motion.ease.standard
```

## 14.2. Tab Content

Default:

```txt
fade content 0 → 1
duration: 120ms - 180ms
```

Avoid:

```txt
large slide for every tab
```

## 14.3. Community Tabs

For:

```txt
Bài viết
Tài nguyên
Trò chuyện
Thành viên
Giới thiệu
```

Use subtle fade only.

## 14.4. Admin Tabs

Admin tabs should be fast and minimal.

```txt
duration: 120ms
no decorative animation
```

Admin không cần UI biểu diễn múa lụa giữa lúc xử lý report.

---

# 15. Accordion Motion

## 15.1. Expand

Recommended:

```txt
height 0 → content height
opacity 0 → 1
duration: 180ms - 240ms
```

## 15.2. Collapse

```txt
height content → 0
opacity 1 → 0
duration: 150ms - 200ms
```

## 15.3. Rules

```txt
- Avoid jumpy height.
- Respect reduced motion.
- Chevron rotates 0deg ↔ 180deg.
- Content must remain accessible.
```

Use for:

```txt
FAQ
settings sections
admin filters
career pathway sections
community rules
```

---

# 16. Skeleton / Loading Motion

## 16.1. Skeleton Shimmer

Default:

```txt
subtle shimmer
duration: 1200ms - 1600ms
```

Reduced motion:

```txt
static skeleton
```

## 16.2. Skeleton Pulse

Allowed alternative:

```txt
opacity 0.6 ↔ 1
duration: 1200ms
```

## 16.3. Rules

```txt
- Skeleton must match final layout.
- Do not use excessive high-contrast shimmer.
- Do not use skeleton for tiny inline actions.
- Do not keep skeleton forever without timeout/error.
```

## 16.4. Loading Timeout

If loading takes too long:

```txt
after ~8-10s show error/retry
```

Copy:

```txt
Không tải được dữ liệu. Vui lòng thử lại.
```

---

# 17. Progress Motion

## 17.1. Upload Progress

Use for:

```txt
evidence upload
avatar upload
post image upload
message attachment
community resource
```

Behavior:

```txt
bar fills linearly with actual upload progress
show percent optional
show file status
```

## 17.2. Indeterminate Progress

Use when exact progress unknown:

```txt
verification processing
admin action processing
search loading
```

Use subtle animated bar, not giant spinner.

## 17.3. Rules

```txt
- Do not fake progress reaching 100% before server confirms.
- If upload fails, bar changes to error state.
- If success, show completed state before replacing/removing item.
```

---

# 18. Page / Route Transition

## 18.1. App Route Transition

PWA route transitions should be minimal.

Recommended:

```txt
content fade in
duration: 120ms - 180ms
```

Avoid:

```txt
large page slide
3D transition
full-screen wipe
```

## 18.2. Auth / Onboarding

Can use slightly more motion:

```txt
step fade + translateY(8px)
duration: 240ms
```

## 18.3. Admin Pages

Minimal:

```txt
instant or fade 100ms
```

## 18.4. Mobile Navigation

For mobile:

```txt
bottom nav route changes should feel instant
```

No heavy transitions between primary app tabs.

---

# 19. Onboarding Motion

## 19.1. Goal

Onboarding motion should make first use feel friendly and guided.

Allowed:

```txt
soft fade
small upward entrance
progress step transition
gentle icon movement
```

Avoid:

```txt
confetti everywhere
cartoon bounce
slow hero animation
```

## 19.2. Step Transition

Recommended:

```txt
old step fade out 120ms
new step fade in + translateY(8px → 0) 180ms
```

## 19.3. Welcome Screen

Logo/icon can fade in:

```txt
opacity 0 → 1
scale 0.98 → 1
duration: 320ms
```

Only for welcome/splash.

## 19.4. Activation Check-in

If onboarding uses Tinder-like activation check-in, motion must avoid dating tone.

Allowed:

```txt
card selection
gentle selected border animation
progress movement
```

Forbidden:

```txt
heart burst
match animation
romantic swipe celebration
```

---

# 20. Verification Motion

## 20.1. Tone

Verification is trust-heavy. Motion must be calm and professional.

Allowed:

```txt
step transition
upload progress
status badge change
success confirmation fade
```

Avoid:

```txt
playful bounce
confetti
dramatic warning animations
```

## 20.2. Evidence Upload

Motion:

```txt
file card appears with fade/translateY
progress bar fills
success check appears
error state fades in
```

## 20.3. Admin Review Status

Status changes should be simple:

```txt
badge color transition
toast/alert
no animated drama
```

Reject/need more info should not animate like failure in a game. Người dùng không thua màn chơi, họ cần bổ sung hồ sơ.

---

# 21. Feed / Post Motion

## 21.1. Feed Loading

Use:

```txt
post-card skeleton
avatar skeleton
text line skeleton
image skeleton if needed
```

## 21.2. New Post Created

After successful post:

```txt
new post appears at top
soft highlight background for 1000ms - 1500ms
fade to normal
```

## 21.3. Comment Add

After successful comment:

```txt
comment appears
fade in + translateY(4px)
duration: 180ms
```

## 21.4. Delete / Hide Post

Use:

```txt
fade out
height collapse optional
show placeholder if moderation
```

Do not instantly remove if user needs undo.

## 21.5. Reported / Hidden Content

Motion must be serious:

```txt
content fades to placeholder
no playful animation
```

Placeholder:

```txt
Nội dung này đang bị ẩn để xem xét.
```

---

# 22. Discovery / Greeting Motion

## 22.1. Discovery Card

Allowed:

```txt
card enter fade + translateY
pass card slide left
greeting card slide right or soft state change
```

Duration:

```txt
180ms - 240ms
```

## 22.2. Swipe Gesture

If implemented:

```txt
drag follows finger/pointer
release animates to decision if threshold passed
returns to center if threshold not passed
```

Threshold:

```txt
distance + velocity based
```

Must have button fallback:

```txt
Bỏ qua
Gửi lời chào
```

## 22.3. Avoid Dating Feel

Do not use:

```txt
heart icons
match animations
romantic sparks
red/pink decision color
Tinder-like card stamp
```

Use:

```txt
blue connection indicator
friendly line/icon
subtle check
```

## 22.4. Greeting Sent

After send:

```txt
button loading
card state changes to pending
toast: Đã gửi lời chào.
```

No giant celebration. Một lời chào không cần pháo hoa.

---

# 23. Messaging Motion

## 23.1. Message Send

Flow:

```txt
user sends
→ local bubble appears as sending
→ server confirms
→ bubble becomes sent
→ realtime confirms delivery/read if supported
```

Motion:

```txt
bubble fade in + translateY(4px)
duration: 120ms
```

## 23.2. Failed Message

Motion:

```txt
failed icon fades in
bubble remains
retry action appears
```

Do not remove typed message.

## 23.3. Typing Indicator

Use:

```txt
three-dot subtle animation
```

Duration:

```txt
800ms - 1200ms loop
```

Reduced motion:

```txt
static "Đang nhập..."
```

## 23.4. Read Receipt

Subtle:

```txt
fade in
no animation loop
```

## 23.5. Conversation Open

Messages should load:

```txt
skeleton or existing cached content
then fade in
```

Do not animate every old message one by one. Người dùng muốn đọc chat, không xem đoàn diễu hành bong bóng.

## 23.6. Realtime Reconnect

Use small banner:

```txt
Đang kết nối lại...
```

Motion:

```txt
banner slide/fade in
duration: 180ms
```

---

# 24. Notification Motion

## 24.1. Notification List

Unread notification:

```txt
brand soft background
small dot
optional soft highlight on new item
```

New notification:

```txt
fade in + translateY(-4px)
duration: 180ms
```

## 24.2. Mark Read

Motion:

```txt
unread dot fades out
background transitions to normal
duration: 180ms
```

## 24.3. Notification Panel

Desktop dropdown:

```txt
fade + translateY(-4px)
```

Mobile sheet:

```txt
bottom sheet slide up
```

## 24.4. Push Prompt

Use calm motion:

```txt
soft card enter
no browser-permission pressure animation
```

Do not nag with bouncing bell icons. Chuông lắc liên tục chỉ chứng minh app không có tự trọng.

---

# 25. Mentor Motion

## 25.1. Mentor Card

Hover:

```txt
subtle lift
border brand-subtle
```

## 25.2. Mentor Request Submit

Flow:

```txt
submit button loading
form disabled
success state
redirect or show request detail
```

Motion:

```txt
success panel fade in
CTA "Xem yêu cầu" / "Mở cuộc trò chuyện" if accepted later
```

## 25.3. Mentor Accepted

When mentor accepts:

```txt
status badge transition
conversation CTA appears
notification sent
```

Motion should be subtle. Không cần “mentor unlocked” như game mobile.

## 25.4. Availability Toggle

Switch motion:

```txt
thumb slides
track color changes
duration: 180ms
```

If pausing availability has consequence, show confirmation/alert, not just cute switch.

---

# 26. Community Motion

## 26.1. Community Card

Hover:

```txt
shadow slight increase
translateY(-2px)
```

## 26.2. Join Community

Flow:

```txt
button loading
state becomes joined or pending
toast appears
```

Motion:

```txt
button label transition
badge appears with fade
```

## 26.3. Community Tabs

Use simple fade.

Tabs:

```txt
Bài viết
Tài nguyên
Trò chuyện
Thành viên
Giới thiệu
```

No large slide between tabs.

## 26.4. Community Suspended

When state is suspended:

```txt
page shows locked state
actions disabled
chat composer disabled
```

Motion:

```txt
no dramatic animation
status alert fades in
```

## 26.5. Community Resource

Resource submit:

```txt
file card appears
progress bar
pending review badge fades in
```

Resource approved:

```txt
badge changes pending → published
```

---

# 27. Career Pathway Motion

## 27.1. Pathway List

Cards can fade in:

```txt
opacity 0 → 1
translateY(4px → 0)
duration: 180ms
```

## 27.2. Save Pathway

Motion:

```txt
save icon fills
button/chip changes state
toast appears
```

Use blue brand, not heart-like dating save.

## 27.3. Skill Roadmap

For expandable sections:

```txt
accordion expand/collapse
duration: 180ms - 240ms
```

## 27.4. Year-based Tabs

Use:

```txt
indicator transition
content fade
```

## 27.5. Resource Click

No special animation needed.

Productivity sometimes means not animating a link like it discovered purpose.

---

# 28. Search / Filter Motion

## 28.1. Search Suggestions

Enter:

```txt
dropdown fade + translateY(-4px)
duration: 120ms
```

## 28.2. Search Results

Use:

```txt
skeleton while loading
content fade in
```

Do not animate each result excessively.

## 28.3. Filter Sheet

Mobile:

```txt
bottom sheet slide up
```

Desktop:

```txt
filter panel expand/fade
```

## 28.4. Filter Chips

Active chip:

```txt
background/border transition
optional check icon fade
```

Remove chip:

```txt
fade out + width collapse only if smooth
```

## 28.5. Empty Search

Empty state can fade in.

No sad mascot required. The app isn’t lonely, it just found nothing.

---

# 29. Admin / Moderation Motion

## 29.1. Admin Philosophy

Admin UI motion must be:

```txt
fast
minimal
clear
non-distracting
```

Admin users are doing operational work. Do not waste their time with “delight”.

## 29.2. Admin Table

Allowed:

```txt
row hover background
expanded row slide/fade
status badge transition
loading skeleton rows
```

Avoid:

```txt
row flying in
large stagger
animated counters every refresh
```

## 29.3. Moderation Action

Flow:

```txt
click action
confirmation modal
reason required
processing state
status update
audit timeline update
toast
```

Motion:

```txt
modal standard
row status soft highlight
```

## 29.4. Dangerous Admin Actions

Use minimal motion:

```txt
fade confirmation
danger button loading
result toast/alert
```

Do not use shake, bounce, or pulse unless validation error needs attention.

---

# 30. Safety / Reporting Motion

## 30.1. Report Modal

Motion:

```txt
modal fade/scale
reason list appears normally
submit loading
success confirmation fade
```

## 30.2. Report Submitted

Success should feel calm:

```txt
Báo cáo của bạn đã được gửi.
```

No celebration.

## 30.3. Auto-block After Report

If report triggers block:

```txt
target content fades out
safe explanation appears
```

Copy:

```txt
Bạn sẽ không còn nhìn thấy nội dung hoặc tương tác từ người này.
```

## 30.4. Moderation Placeholder

Transition:

```txt
content fades into placeholder
duration: 180ms
```

No dramatic removal.

---

# 31. Logo / Brand Motion

## 31.1. Logo Motion Allowed

Logo can animate only in:

```txt
splash screen
loading screen
brand presentation
onboarding welcome
```

Allowed:

```txt
fade in
subtle scale 0.98 → 1
soft gradient background movement, rare
```

## 31.2. Logo Motion Not Allowed

Do not animate logo in:

```txt
navbar
admin dashboard
normal app shell
every page load
```

Do not:

```txt
spin logo
bounce logo
pulse logo continuously
morph logo excessively
```

Logo không phải con thú cưng. Đừng bắt nó nhảy mỗi lần mở trang.

---

# 32. PWA-specific Motion

## 32.1. App Launch

Splash:

```txt
logo fade in
duration: 320ms max
```

## 32.2. Standalone Mode

PWA should feel app-like:

```txt
fast route changes
minimal layout shift
bottom sheet for mobile actions
```

## 32.3. Offline Transition

When offline detected:

```txt
banner slide/fade in
duration: 180ms
```

When online restored:

```txt
banner fades out
optional toast: Đã kết nối lại.
```

## 32.4. Install Prompt

Install card:

```txt
fade in + translateY(8px)
duration: 240ms
```

Do not nag aggressively.

---

# 33. Implementation with TailwindCSS

## 33.1. Tailwind Token Direction

Recommended extension:

```js
// tailwind.config.js
theme: {
  extend: {
    transitionDuration: {
      xs: '75ms',
      sm: '120ms',
      md: '180ms',
      lg: '240ms',
      xl: '320ms',
      '2xl': '480ms',
    },
    transitionTimingFunction: {
      standard: 'cubic-bezier(0.2, 0, 0, 1)',
      enter: 'cubic-bezier(0, 0, 0.2, 1)',
      exit: 'cubic-bezier(0.4, 0, 1, 1)',
      emphasized: 'cubic-bezier(0.16, 1, 0.3, 1)',
    }
  }
}
```

## 33.2. Common Utility Patterns

Button:

```html
class="transition-colors duration-sm ease-standard active:scale-[0.98]"
```

Card:

```html
class="transition-[box-shadow,transform,border-color] duration-md ease-standard hover:-translate-y-0.5 hover:shadow-md"
```

Modal panel:

```html
class="transition duration-lg ease-emphasized data-[state=open]:opacity-100 data-[state=open]:scale-100 data-[state=closed]:opacity-0 data-[state=closed]:scale-[0.98]"
```

Dropdown:

```html
class="transition duration-sm ease-standard data-[state=open]:opacity-100 data-[state=open]:translate-y-0 data-[state=closed]:opacity-0 data-[state=closed]:-translate-y-1"
```

## 33.3. Data Attribute Pattern

Use:

```txt
data-state="open"
data-state="closed"
data-loading="true"
data-status="pending"
data-motion="reduced"
```

Examples:

```html
<div data-state="open">
  ...
</div>
```

```html
<button data-loading="true">
  Đang gửi...
</button>
```

## 33.4. Alpine.js Direction

Use Alpine for:

```txt
dropdown open/close
modal open/close
accordion expand/collapse
filter sheet
toast state
```

Keep animation definitions consistent.

## 33.5. Livewire Direction

When Livewire updates state:

```txt
use loading indicators
preserve button width
show skeleton for list refresh
avoid full page flicker
```

---

# 34. Performance Rules

## 34.1. Prefer Compositor-friendly Properties

Best:

```txt
transform
opacity
```

Careful:

```txt
box-shadow
filter
height
```

Avoid:

```txt
top
left
width
margin
padding
```

## 34.2. Avoid Animating Large Lists

For feed/search/admin table:

```txt
animate container or first few items only
do not animate 100 rows individually
```

## 34.3. Avoid Continuous Animation

Continuous animation allowed only for:

```txt
spinner
typing indicator
progress indeterminate
skeleton shimmer
```

And must respect reduced motion.

## 34.4. Mobile Performance

Mobile PWA rules:

```txt
avoid heavy blur
avoid complex shadows on long lists
avoid large background animation
avoid expensive fixed overlays with blur
```

Không phải máy sinh viên nào cũng là MacBook Pro. Một số đang chạy Chrome với 47 tab và niềm tin mong manh.

---

# 35. Motion QA Checklist

Before approving motion:

```txt
[ ] Motion has a clear purpose.
[ ] Motion explains state or improves feedback.
[ ] Duration is not too slow.
[ ] Motion uses approved duration/easing tokens.
[ ] Motion does not cause layout shift.
[ ] Motion works on mobile.
[ ] Motion respects prefers-reduced-motion.
[ ] Motion does not block user action too long.
[ ] Motion does not animate sensitive/safety states playfully.
[ ] Loading motion has timeout/error fallback.
[ ] Realtime states have reconnect/failure motion.
[ ] Admin/moderation motion is minimal.
[ ] No dating-like animation in Discovery/Greeting.
[ ] Performance is acceptable on low-end devices.
```

---

# 36. Motion Anti-patterns

Do not:

```txt
- Animate everything because it looks modern.
- Use bounce for serious flows.
- Use heart/match animation.
- Use confetti for normal success.
- Use spinner for full page loading.
- Keep shimmer forever.
- Animate layout-heavy properties in long lists.
- Add hover-only motion for mobile-critical actions.
- Use aggressive pulse to demand attention.
- Use slow transitions in admin dashboard.
- Animate logo on every page.
- Use red/pink/orange motion language copied from dating apps.
- Let motion delay form submission feedback.
```

---

# 37. Motion Decision Checklist

Before adding motion, answer:

```txt
1. What state changed?
2. What should user understand?
3. Is motion necessary?
4. Can opacity/transform solve it?
5. How long should it last?
6. What happens with reduced motion?
7. Does it work on mobile?
8. Does it hurt performance?
9. Does it fit UEConnect tone?
10. Is this serious/safety/admin context?
```

If the answer to “is motion necessary?” is “cho vui”, delete it. UI không cần đi bar.

---

# 38. Final Rule

Motion in UEConnect must be useful, calm, fast and accessible.

Motion is allowed when it:

```txt
clarifies feedback
supports navigation
reduces perceived waiting
shows state transition
helps mobile interaction
```

Motion is not allowed when it:

```txt
distracts
slows the user
hides information
feels like dating app
hurts accessibility
hurts performance
makes serious states playful
```

Nếu motion làm người dùng hiểu app tốt hơn, dùng. Nếu motion chỉ để designer cảm thấy mình đã “thêm cảm xúc”, dừng lại trước khi frontend phải debug cảm xúc đó.

```
```
