---
title: "Brand Identity from HCMUE"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Product / Frontend Team"
depends_on:
  - "00-design-foundation-roadmap.md"
  - "01-brand-attributes.md"
next:
  - "03-color-system.md"
  - "04-gradient-policy.md"
  - "10-icon-system.md"
  - "11-logo-usage-system.md"
related:
  - "brand-guideline.md"
  - "design-system.md"
  - "examples/preview.html"
---

# 02. Brand Identity from HCMUE

## 1. Purpose

File này định nghĩa cách UEConnect kế thừa và chuyển hóa nhận diện thương hiệu HCMUE vào product UI.

Mục tiêu của file này:

- Xác định yếu tố nào từ HCMUE được dùng làm nền tảng nhận diện.
- Chốt cách dùng màu HCMUE trong UEConnect.
- Chốt cách dùng logo, wordmark, icon, typography và tone nhận diện.
- Ngăn UI biến thành cổng thông tin hành chính của trường.
- Ngăn UI lạm dụng brand color đến mức nhìn chói, AI-generated hoặc thiếu tính social product.
- Đảm bảo UEConnect nhìn vào là biết thuộc hệ sinh thái HCMUE, nhưng vẫn là một social app hiện đại.

File này không định nghĩa toàn bộ color token chi tiết. Phần đó nằm ở:

```txt
03-color-system.md
````

File này cũng không định nghĩa chi tiết component. Phần đó nằm ở:

```txt
12-component-primitives.md
13-component-variants.md
14-interaction-states.md
```

---

## 2. Core Decision

UEConnect phải kế thừa HCMUE identity theo hướng:

```txt
HCMUE-rooted
+ student-social
+ verified
+ neutral-first
+ restrained brand usage
```

Nói đơn giản:

```txt
Nhìn vào phải biết đây là app thuộc ngữ cảnh HCMUE.
Nhưng khi dùng hằng ngày, UI phải giống một social platform sạch, nhẹ, hiện đại.
```

Không được đi theo hướng:

```txt
Logo trường phóng to
Màu xanh phủ toàn màn hình
Card nào cũng brand color
Button nào cũng xanh
Heading nào cũng xanh
Nền nào cũng gradient
```

Đó không phải brand identity. Đó là hành vi lấy thùng sơn đổ lên giao diện rồi gọi là branding, một nghi thức cổ xưa của thiết kế vội.

---

## 3. HCMUE Identity Role in UEConnect

HCMUE identity trong UEConnect có 4 vai trò chính:

| Role                | Ý nghĩa                                                                |
| ------------------- | ---------------------------------------------------------------------- |
| Trust Anchor        | Xác nhận đây là môi trường social dành cho cộng đồng HCMUE             |
| Verification Signal | Hỗ trợ niềm tin qua Verified UEer, mã sinh viên, role                  |
| Cultural Context    | Tạo cảm giác cùng trường, cùng khoa, cùng cộng đồng                    |
| Brand Recognition   | Giúp user nhận ra sản phẩm thuộc HCMUE mà không cần đọc giải thích dài |

HCMUE identity không có nhiệm vụ:

* Trang trí mọi màn hình.
* Thay thế UX hierarchy.
* Làm background chính của mọi page.
* Biến social app thành portal trường.
* Ép mọi component phải dùng màu xanh.

---

## 4. Official Brand Anchor

## 4.1. Primary HCMUE Color

Màu nhận diện chính dùng cho UEConnect:

```css
--hcmue-cerulean: #124874;
```

Thông số:

```txt
HEX:  #124874
RGB:  18, 72, 116
CMYK: C84 M38 Y0 K55
```

Tên nội bộ trong UEConnect:

```txt
HCMUE Cerulean
UE Brand Blue
Verified Blue
```

Trong code, dùng token:

```css
--ue-brand: #124874;
```

Không dùng trực tiếp `#124874` rải rác trong component nếu có thể tránh. Nên map qua token để sau này dễ maintain.

---

## 4.2. HCMUE Heritage Red

Màu đỏ thuộc nhận diện HCMUE:

```css
--hcmue-heritage-red: #CF373D;
```

Trong UEConnect, màu đỏ **không phải primary color**.

Được dùng cho:

* Chi tiết heritage nhỏ nếu liên quan logo chính thức.
* Error/danger state nếu phù hợp.
* Một số official identity asset nếu bắt buộc.
* Nội dung cảnh báo hoặc moderation.

Không dùng cho:

* Primary CTA.
* Primary gradient.
* Navigation active.
* Discovery action.
* Background feed.
* Button social chính.
* Badge thông thường.

Lý do: UEConnect đã từng thử mix xanh `#124874` với đỏ, và kết quả nhìn không hài hòa. Màu đỏ nếu dùng sai sẽ kéo UI về hướng chói, emotional quá mức hoặc dating-app vibe. Ta đã thoát khỏi cái hố đó, không cần leo xuống lần nữa để kiểm tra đáy.

---

## 5. Brand Color Usage Levels

HCMUE brand color phải được dùng theo cấp độ.

## 5.1. Level 1 — Strong Brand Usage

Chỉ dùng `#124874` mạnh ở các vị trí quan trọng:

```txt
Logo / brand mark
Active navigation
Primary CTA
Verified UEer badge
Focus ring
Important link
Official HCMUE identity area
Account verification state
```

Ví dụ:

```css
.brand-mark {
  background: var(--ue-brand);
  color: #ffffff;
}

.nav-item[aria-current="page"] {
  color: var(--ue-brand);
  background: var(--ue-brand-soft);
}

.button-primary {
  background: var(--ue-brand);
  color: #ffffff;
}
```

## 5.2. Level 2 — Soft Brand Usage

Dùng blue tint nhẹ cho selected state, verified area, info panel.

```css
--ue-brand-soft: #EEF7FF;
--ue-brand-border: rgba(18, 72, 116, 0.16);
--ue-brand-tint: rgba(18, 72, 116, 0.08);
```

Dùng cho:

```txt
Selected nav background
Verified badge background
Onboarding info box
Account verification card
Empty state illustration background
Mentor info panel
```

## 5.3. Level 3 — Rare Brand Moment

Dùng brand gradient hoặc brand visual mạnh cho:

```txt
Splash screen
First onboarding screen
Official announcement
Marketing preview
App launch campaign
```

Không dùng trong product screen hằng ngày.

---

## 6. Brand Usage Ratio

Trên một màn hình product thông thường:

```txt
Neutral surface: 80–90%
Text: 8–12%
HCMUE brand blue: 5–10%
Semantic colors: <3%
Gradient: 0–5%, chỉ khi có lý do rõ
```

Nếu một screen có cảm giác “xanh quá”, khả năng cao là sai.

Checklist nhanh:

* Logo xanh: được.
* Active nav xanh: được.
* Primary CTA xanh: được.
* Verified badge xanh nhẹ: được.
* Heading xanh: thường không cần.
* Icon nào cũng xanh: không.
* Card nào cũng viền xanh: không.
* Background xanh toàn feed: không.
* Gradient xanh toàn page: không.

---

## 7. Logo Strategy

## 7.1. HCMUE Official Logo

Logo HCMUE là tài sản nhận diện chính thức. Khi dùng trong UEConnect, phải đảm bảo:

* Không bóp méo.
* Không đổi tỉ lệ.
* Không đổi màu tùy tiện.
* Không đặt trên nền thiếu tương phản.
* Không thêm effect như glow, shadow mạnh, gradient, bevel.
* Không dùng logo chính thức như pattern trang trí lặp lại.

Nếu chưa có file logo vector chính thức, phải dùng bản chất lượng cao nhất có thể và cần thay bằng SVG/official asset khi triển khai thật.

## 7.2. UEConnect Product Mark

UEConnect nên có product mark riêng, lấy cảm hứng từ HCMUE nhưng không thay thế logo HCMUE.

Product mark nên:

* Đơn giản.
* Dùng được ở 24px.
* Nhận ra được chữ `UE` hoặc tinh thần kết nối.
* Không quá giống logo Tinder, Threads, Instagram hoặc Discord.
* Không dùng flame icon.
* Không dùng hình trái tim.
* Không tạo dating vibe.

Gợi ý product mark:

```txt
UE monogram
UE + connection node
UE + chat/link shape
UEConnect wordmark + small verified mark
```

## 7.3. Brand Lockup

UEConnect có thể có các lockup sau:

```txt
1. Full lockup:
   [UE mark] UEConnect

2. HCMUE context lockup:
   [UE mark] UEConnect
   HCMUE Verified Student Social Platform

3. Compact app mark:
   UE

4. Official context:
   UEConnect · HCMUE
```

Không nên dùng:

```txt
UEConnect by HCMUE
```

trừ khi team có quyết định pháp lý/branding rõ ràng. Chữ “by” nghe như sản phẩm official cấp tổ chức, cần cẩn thận. Đừng tự phong vương cho app, chuyện đó thường kết thúc bằng email chỉnh sửa từ ai đó có quyền.

---

## 8. Logo Placement Rules

## 8.1. Desktop

Logo/brand mark nên xuất hiện ở:

```txt
Left sidebar top
Topbar nếu layout không có sidebar
Auth/onboarding header
Admin dashboard header
```

Không nên lặp logo trong:

```txt
Mỗi card
Mỗi post
Mỗi modal
Mỗi empty state
Mỗi footer nhỏ
```

## 8.2. Mobile

Mobile nên dùng:

```txt
Top bar compact mark
Bottom nav không cần logo
Splash/onboarding có thể dùng full lockup
```

Mobile feed không nên để logo chiếm quá nhiều chiều cao. Người dùng vào app để đọc nội dung, không phải chiêm ngưỡng huy hiệu như đang vào hội nghị.

---

## 9. Clear Space & Minimum Size

## 9.1. Clear Space

Với UEConnect product mark:

```txt
Minimum clear space = 0.5x logo height
Recommended clear space = 1x logo height
```

Không đặt logo sát:

* Edge màn hình.
* Button.
* Avatar.
* Text heading.
* Image busy background.

## 9.2. Minimum Size

| Asset               |                      Minimum Size |
| ------------------- | --------------------------------: |
| Product mark `UE`   |                              24px |
| Brand mark in nav   |                              32px |
| Full wordmark       |                       120px width |
| HCMUE official logo | Theo guideline chính thức, nếu có |
| Favicon/app icon    |             32px hoặc 48px source |

Nếu logo không đọc được ở kích thước nhỏ, dùng product mark compact thay vì ép full logo vào chỗ hẹp. Một dòng chữ bị ép nhỏ không phải logo, nó là bài kiểm tra thị lực trá hình.

---

## 10. Typography Relationship with HCMUE

## 10.1. Product UI Typography

UEConnect product UI ưu tiên:

```css
--font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Be Vietnam Pro", sans-serif;
--font-vietnamese: "Be Vietnam Pro", system-ui, sans-serif;
--font-data: "Inter", system-ui, sans-serif;
```

Lý do:

* Dễ đọc trên web app.
* Hỗ trợ tiếng Việt tốt.
* Giống social product thật.
* Không quá formal.
* Dễ implement với TailwindCSS/Vite.

## 10.2. Official / Document Typography

Trong official document hoặc context học thuật, có thể dùng:

```txt
Times New Roman
Source Serif
Serif official font nếu có trong brand guideline
```

Nhưng không dùng serif làm font chính trong product UI.

## 10.3. Brand Moment Typography

Một số màn hình như onboarding hero hoặc official campaign có thể dùng type treatment đặc biệt, nhưng phải tiết chế.

Không dùng:

* Font quá decorative.
* Serif dày trong feed.
* Heading quá lớn trong product screen.
* Uppercase quá nhiều.
* Text gradient mặc định.

---

## 11. HCMUE Identity in Product Areas

## 11.1. Onboarding

Onboarding là nơi HCMUE identity có thể rõ nhất.

Nên thể hiện:

```txt
UEConnect dành cho cộng đồng HCMUE.
Tài khoản cần xác thực bằng mã sinh viên.
Verified UEer giúp cộng đồng an toàn hơn.
```

Có thể dùng:

* Product mark.
* HCMUE blue.
* Light brand panel.
* Một brand moment gradient rất nhẹ nếu cần.

Không nên:

* Dùng logo trường quá to.
* Dùng background xanh toàn màn hình quá lâu.
* Dùng nhiều slogan hành chính.
* Làm onboarding giống brochure trường.

## 11.2. Home Feed

Home Feed phải content-first.

HCMUE identity chỉ nên xuất hiện qua:

```txt
Brand mark trong nav
Verified badge
Faculty/cohort metadata
Official announcement nếu có
```

Không dùng:

```txt
HCMUE logo trong mỗi post
Blue background feed
Blue card headers
Gradient separators
```

## 11.3. Discovery

Discovery cần HCMUE identity để tránh dating vibe.

Nên có:

```txt
Verified UEer
Khoa
Khóa
Ngành
Bạn cùng khoa
Cùng học
Cùng quan tâm
```

Không dùng:

```txt
Match
Swipe
Crush
Hot
Dating
```

HCMUE identity ở đây không chỉ là màu. Nó là context học tập và cộng đồng.

## 11.4. Messaging

Messaging nên nhẹ, trung tính.

HCMUE identity xuất hiện qua:

* Verified badge nhỏ.
* Role/context trong conversation header.
* Safety/trust note nếu cần.

Không dùng:

* Blue bubble cho cả hai bên.
* Gradient bubble.
* Logo trong chat bubble.
* Official tone quá cứng.

## 11.5. Mentor

Mentor cần cảm giác trusted và growth-oriented.

HCMUE identity nên xuất hiện qua:

```txt
Mentor / Alumni / Advisor badge
Verified status
Faculty / field / expertise
HCMUE background
```

Không biến mentor thành LinkedIn clone.

## 11.6. Admin / Verification

Đây là nơi HCMUE identity cần rõ và nghiêm túc hơn.

Nên dùng:

* Brand blue cho trust state.
* Clear status labels.
* Verification badges.
* Official copy rõ ràng.
* Audit/moderation UI nghiêm túc.

Không dùng:

* Social tone quá vui.
* Decoration thừa.
* Gradient.
* Màu mè.

---

## 12. Verified UEer Identity

## 12.1. Role of Verified UEer

`Verified UEer` là trust signal quan trọng nhất của UEConnect.

Nó đại diện cho:

* User đã xác thực bằng mã sinh viên.
* Mỗi user chỉ có một tài khoản.
* User thuộc cộng đồng HCMUE.
* Hệ thống có kiểm duyệt đầu vào.

## 12.2. Verified Badge Design

Badge nên nhỏ, rõ, không quá phô.

```css
.verified-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--ue-brand);
  background: rgba(18, 72, 116, 0.08);
  border: 1px solid rgba(18, 72, 116, 0.14);
  border-radius: 999px;
  padding: 2px 8px;
  font-size: 12px;
  font-weight: 600;
}
```

## 12.3. Verified Badge Usage

Dùng ở:

```txt
Profile header
Post author row
Discovery profile card
Mentor/alumni card
Messaging header
Admin verification view
```

Không dùng ở:

```txt
Mọi comment nếu quá rối
Mọi notification item
Mọi small avatar
Nơi badge làm layout vỡ
```

## 12.4. Verification Copy

Good:

```txt
Đã xác thực UEer
Xác thực bằng mã sinh viên
Tài khoản đang chờ xác thực
Cần bổ sung thông tin xác thực
```

Bad:

```txt
Verified 100%
An toàn tuyệt đối
Tài khoản thật chắc chắn
Không thể giả mạo
```

Không hứa quá mức. Hệ thống kiểm duyệt tốt vẫn cần khiêm tốn, một phẩm chất hiếm hoi nhưng nên thử.

---

## 13. HCMUE Context Metadata

UEConnect nên dùng metadata học tập như một phần identity.

## 13.1. Student Metadata

Có thể hiển thị:

```txt
Khoa
Ngành
Khóa
Lớp
Môn đang học
CLB
Sở thích học tập
Mentor/career interest
```

Không nên public mặc định:

```txt
Mã sinh viên đầy đủ
Số điện thoại
Email cá nhân
Thông tin nhạy cảm
```

## 13.2. Metadata Display Rules

Trong post author row:

```txt
Tên · Khoa CNTT · K49 · 2 giờ trước
```

Trong profile card:

```txt
Khoa CNTT
Khóa K49
Quan tâm: UI/UX, Backend, AI
```

Trong discovery:

```txt
Cùng khoa CNTT
Cùng quan tâm UI/UX
Đã xác thực UEer
```

Metadata phải giúp kết nối, không biến UI thành bảng thông tin sinh viên.

---

## 14. Official vs Product Tone

UEConnect có 2 tone nhận diện:

## 14.1. Official Tone

Dùng cho:

```txt
Verification
Policy
Safety
Admin
Account status
Privacy
System announcement
```

Đặc điểm:

* Rõ ràng.
* Trung tính.
* Chính xác.
* Không đùa quá mức.
* Có trust.

Ví dụ:

```txt
Tài khoản của bạn đang chờ xác thực.
Chúng tôi cần kiểm tra thông tin để giữ cộng đồng UEConnect an toàn.
```

## 14.2. Product Social Tone

Dùng cho:

```txt
Feed
Discovery
Profile
Messaging
Community
Mentor suggestion
Empty state nhẹ
```

Đặc điểm:

* Gần gũi.
* Trẻ trung vừa phải.
* Hỗ trợ.
* Không cringe.
* Không dating.

Ví dụ:

```txt
Khám phá UEers có cùng mối quan tâm với bạn.
Gửi lời chào để bắt đầu kết nối.
```

---

## 15. Brand Voice Constraints

UEConnect nên dùng các từ:

```txt
UEer
Kết nối
Gửi lời chào
Mentor
Bạn cùng khoa
Cùng học
Hỗ trợ
Khám phá
Cộng đồng
CLB
Verified UEer
```

Không dùng:

```txt
Crush
Match
Swipe
Hot
Dating
Ghép đôi
Tán tỉnh
```

Các từ bị cấm không chỉ vì ngữ nghĩa, mà vì chúng kéo toàn bộ brand vào dating territory. Một khi user nghĩ “app này giống dating”, rất khó kéo lại trust.

---

## 16. Brand Identity Decision Matrix

| Situation               | Use HCMUE Identity Strongly? | Rule                                       |
| ----------------------- | ---------------------------: | ------------------------------------------ |
| Onboarding first screen |                  Medium/High | Có thể dùng brand mark, blue, trust copy   |
| Login/signup            |                       Medium | Dùng brand mark và verification message    |
| Account verification    |                         High | Trust-first, official tone                 |
| Home feed               |                          Low | Chỉ nav, verified, metadata                |
| Discovery               |                       Medium | Dùng HCMUE context để chống dating vibe    |
| Profile                 |                       Medium | Verified, faculty/cohort, student identity |
| Messaging               |                          Low | Chỉ context nhẹ                            |
| Mentor                  |                       Medium | Role, verified, HCMUE background           |
| Community/CLB           |                       Medium | Community identity + HCMUE context         |
| Admin dashboard         |                         High | Rõ, nghiêm túc, trust-oriented             |
| Error state             |                   Low/Medium | Semantic color, không brand quá mức        |
| Empty state             |                          Low | Có thể dùng soft brand illustration        |

---

## 17. Logo Misuse

Không được:

```txt
Bóp méo logo.
Đổi màu logo tùy tiện.
Thêm shadow/glow mạnh.
Đặt logo trên nền rối.
Dùng logo làm watermark lớn.
Dùng logo HCMUE thay mọi icon.
Dùng logo trong mỗi post/card.
Dùng logo như decoration pattern.
```

Được:

```txt
Dùng logo/product mark ở nav.
Dùng logo trong onboarding.
Dùng logo trong auth screen.
Dùng logo trong official announcement.
Dùng logo trong admin/verification context nếu cần.
```

---

## 18. Color Misuse

Không được:

```css
/* Sai */
.feed {
  background: #124874;
}

/* Sai */
.post-card {
  border-color: #124874;
}

/* Sai */
.icon {
  color: #124874;
}

/* Sai */
.button-secondary {
  background: #124874;
}

/* Sai */
.page {
  background: linear-gradient(...);
}
```

Được:

```css
/* Đúng */
.button-primary {
  background: var(--ue-brand);
}

/* Đúng */
.nav-item[aria-current="page"] {
  color: var(--ue-brand);
  background: var(--ue-brand-soft);
}

/* Đúng */
.verified-badge {
  color: var(--ue-brand);
  background: rgba(18,72,116,0.08);
}

/* Đúng */
.input:focus {
  border-color: var(--ue-brand);
  box-shadow: 0 0 0 3px rgba(18,72,116,0.12);
}
```

---

## 19. Relationship with Other Design Files

File này liên kết với các file sau:

| File                        | Relationship                                         |
| --------------------------- | ---------------------------------------------------- |
| `01-brand-attributes.md`    | Định nghĩa personality và product positioning        |
| `03-color-system.md`        | Chuyển brand identity thành color tokens             |
| `04-gradient-policy.md`     | Quy định khi nào được dùng brand gradient            |
| `05-typography-system.md`   | Chốt font và type scale                              |
| `10-icon-system.md`         | Chốt icon style tránh lệch brand                     |
| `11-logo-usage-system.md`   | Chi tiết hóa logo usage                              |
| `16-content-tone.md`        | Chuyển brand voice thành microcopy                   |
| `17-accessibility-rules.md` | Đảm bảo brand usage không phá contrast/accessibility |

---

## 20. Implementation Notes

## 20.1. CSS Variables

Nên định nghĩa brand identity ở root:

```css
:root {
  --ue-brand: #124874;
  --ue-brand-hover: #0E3A60;
  --ue-brand-active: #0A2B49;
  --ue-brand-soft: #EEF7FF;
  --ue-brand-border: rgba(18, 72, 116, 0.16);

  --ue-heritage-red: #CF373D;

  --ue-bg: #FAFAFA;
  --ue-surface: #FFFFFF;
  --ue-border: #E4E6EB;
  --ue-text: #0F172A;
  --ue-text-muted: #64748B;
}
```

## 20.2. Tailwind Mapping

```js
// tailwind.config.js
theme: {
  extend: {
    colors: {
      ue: {
        brand: "#124874",
        "brand-hover": "#0E3A60",
        "brand-active": "#0A2B49",
        "brand-soft": "#EEF7FF",
        "heritage-red": "#CF373D",
      }
    }
  }
}
```

## 20.3. Blade Usage Example

```blade
<a
  href="{{ route('feed') }}"
  class="inline-flex items-center gap-3 rounded-full px-3 py-2 text-slate-600 hover:bg-slate-100 aria-[current=page]:bg-[#EEF7FF] aria-[current=page]:text-[#124874]"
  aria-current="page"
>
  <x-icon.home class="h-5 w-5" />
  <span>Trang chủ</span>
</a>
```

Nên chuyển hard-code class màu sang token hoặc Tailwind config khi codebase ổn định.

---

## 21. Accessibility Requirements

Brand identity không được phá accessibility.

Rules:

* Text trên `#124874` phải là white hoặc màu đủ contrast.
* Không dùng blue nhạt làm text chính trên nền trắng.
* Verified badge phải đọc được ở 12px.
* Focus ring phải rõ.
* Logo phải có alt text nếu là image.
* Icon-only logo link cần `aria-label`.
* Không truyền ý nghĩa chỉ bằng màu.

Ví dụ:

```html
<a href="/" aria-label="UEConnect home">
  <img src="/logo.svg" alt="UEConnect" />
</a>
```

Nếu logo chỉ là decoration:

```html
<img src="/mark.svg" alt="" aria-hidden="true" />
```

---

## 22. Brand Identity QA Checklist

Dùng checklist này khi review bất kỳ screen nào.

## 22.1. Recognition

* [ ] Nhìn vào có biết đây là app trong ngữ cảnh HCMUE không?
* [ ] Có brand mark hoặc context HCMUE đúng chỗ không?
* [ ] Có Verified UEer hoặc trust signal nếu cần không?

## 22.2. Restraint

* [ ] Brand blue có bị dùng quá nhiều không?
* [ ] Có icon nào bị tô xanh không cần thiết không?
* [ ] Có card/header/background nào dùng brand color quá tay không?
* [ ] Gradient có bị dùng trong product UI hằng ngày không?

## 22.3. Trust

* [ ] Verification state có rõ không?
* [ ] Mã sinh viên có được bảo vệ privacy không?
* [ ] User có hiểu vì sao cần xác thực không?
* [ ] Safety/report có liên kết đúng không?

## 22.4. Social Fit

* [ ] UI có còn giống social app không?
* [ ] Có tránh portal trường không?
* [ ] Có tránh dating vibe không?
* [ ] Metadata học tập có giúp kết nối không?

## 22.5. Implementation

* [ ] Màu dùng qua token chưa?
* [ ] Logo không bị méo/chói chưa?
* [ ] Text contrast đạt chưa?
* [ ] Mobile logo/brand usage có gọn chưa?
* [ ] Tailwind class có nhất quán chưa?

---

## 23. AI Prompt Notes

Khi yêu cầu AI tạo UI cho UEConnect, luôn thêm:

```txt
Use HCMUE identity as a restrained trust layer, not as full-page decoration.
The core brand color is #124874 and should only be used for logo, active navigation, primary CTA, verified badge, focus state, and official verification areas.
Do not use red in the primary UI or gradient.
Do not use full-page gradients in product screens.
Keep the UI neutral-first, content-first, and social-platform-like.
Make it clear this is a verified HCMUE student social platform without making it look like a school portal.
Use terms like UEer, Verified UEer, kết nối, gửi lời chào, bạn cùng khoa, cùng học, mentor.
Avoid dating terms like match, swipe, crush, hot, dating.
```

---

## 24. Final Decision

UEConnect kế thừa HCMUE identity theo hướng:

```txt
HCMUE as trust foundation
UEConnect as social product
Brand blue as restrained accent
Verified UEer as core identity signal
Neutral UI as default
Gradient as rare brand moment
```

Câu chốt:

```txt
UEConnect phải đủ HCMUE để đáng tin, đủ social để hấp dẫn, đủ neutral để dùng hằng ngày, và đủ tiết chế để không nhìn như AI vừa phát hiện ra gradient.
```

```

File này nên là nền cho `03-color-system.md`. Sau file 02, màu không còn là chuyện “thấy xanh đẹp thì dùng”, mà là một hệ nhận diện có kiểm soát. Một bước nhỏ cho repo, một bước khá lớn để UI bớt gây án thị giác.
```
