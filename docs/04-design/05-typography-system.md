---
title: "Typography System"
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
  - "04-gradient-policy.md"
next:
  - "06-spacing-system.md"
  - "12-component-primitives.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "19-design-token-documentation.md"
related:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "examples/preview.html"
---

# 05. Typography System

## 1. Purpose

File này định nghĩa hệ thống typography chính thức cho UEConnect.

Mục tiêu:

- Chốt font stack dùng trong product UI.
- Xây dựng type scale phù hợp social platform.
- Định nghĩa text styles cho feed, post, comment, profile, messaging, mentor, admin.
- Đảm bảo UI đọc tốt trên desktop, tablet và mobile.
- Giữ typography nhất quán khi implement bằng Laravel Blade + TailwindCSS + Vite.
- Tránh lỗi UI nhìn AI-generated do heading quá lớn, font weight quá nặng, line-height sai hoặc text hierarchy rối.

Typography của UEConnect phải phục vụ nội dung trước.

```txt
Typography is not decoration.
Typography is product usability.
````

Nói đơn giản: chữ phải giúp user đọc, hiểu, tương tác. Không phải đứng đó gồng mình làm “premium”.

---

## 2. Core Typography Decision

UEConnect dùng chiến lược typography:

```txt
System-first
+ Vietnamese-friendly
+ social readability
+ restrained hierarchy
+ mobile-first scaling
```

Quyết định chính:

```txt
Product UI chính dùng system font stack.
Be Vietnam Pro có thể dùng để tăng cảm giác tiếng Việt.
Inter dùng tốt cho data/admin/table.
Serif chỉ dùng trong official document hoặc rare brand moment, không dùng trong feed.
```

Typography không được làm UEConnect giống:

```txt
Landing page startup
School portal
Dashboard SaaS lạnh lẽo
Dating profile app
CV/LinkedIn clone
```

---

## 3. Font Stack

## 3.1. Primary Product Font

Font chính cho toàn bộ product UI:

```css
--font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Be Vietnam Pro", sans-serif;
```

Dùng cho:

* Feed.
* Post.
* Comment.
* Profile.
* Discovery.
* Messaging.
* Notification.
* Settings.
* Component UI.
* Navigation.
* Forms.

Lý do:

* Render nhanh.
* Quen thuộc với user.
* Đọc tốt trên nhiều hệ điều hành.
* Ít tạo cảm giác template.
* Phù hợp social product thật.

## 3.2. Vietnamese-friendly Font

Font hỗ trợ tiếng Việt nếu cần cảm giác local rõ hơn:

```css
--font-vietnamese: "Be Vietnam Pro", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Dùng cho:

* Marketing/onboarding text.
* Vietnamese-heavy content.
* Section title trong docs/preview.
* Empty state có nhiều tiếng Việt.
* Campaign hoặc official HCMUE moment.

Không nên ép `Be Vietnam Pro` cho mọi text nếu performance hoặc rendering không ổn.

## 3.3. Data / Admin Font

Font cho bảng dữ liệu, admin, dashboard:

```css
--font-data: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Dùng cho:

* Admin table.
* Audit log.
* Moderation queue.
* User management.
* Status table.
* Numeric data.
* Filter/search interface.

## 3.4. Brand / Official Serif

Serif chỉ dùng rất hạn chế:

```css
--font-brand-serif: "Source Serif Variable", Georgia, "Times New Roman", serif;
```

Dùng cho:

* Official HCMUE document context.
* Brand guideline export.
* Một số official announcement rất đặc biệt.
* Không dùng trong product UI hằng ngày.

Không dùng serif cho:

* Feed.
* Post body.
* Comment.
* Messaging.
* Button.
* Input.
* Navigation.
* Discovery card.

Serif trong feed social thường khiến app trông như đang nộp báo cáo môn học thay vì giúp sinh viên kết nối. Một khung cảnh đau lòng nhưng tránh được.

---

## 4. Font Loading Policy

## 4.1. Recommended Default

Với Laravel Blade + TailwindCSS, nên ưu tiên system font để giảm dependency.

```css
body {
  font-family: var(--font-sans);
}
```

## 4.2. If Loading Web Font

Nếu dùng `Be Vietnam Pro` hoặc `Inter`, cần:

* Dùng `font-display: swap`.
* Preconnect nếu load từ Google Fonts.
* Không load quá nhiều weight.
* Chỉ load weight thật sự dùng.
* Tránh load italic nếu không dùng.

Recommended weights:

```txt
400 Regular
500 Medium
600 Semibold
700 Bold
```

Không load:

```txt
100
200
300
800
900
Multiple italic weights
```

trừ khi có lý do rất rõ. Font weight không phải Pokémon, không cần bắt hết.

---

## 5. Typography Principles

## 5.1. Content-first

Feed, post, comment, message là nội dung chính.

Typography phải ưu tiên:

* Readability.
* Scanability.
* Clear hierarchy.
* Comfortable line-height.
* Good mobile reading.
* Không tranh spotlight với content.

## 5.2. Restrained hierarchy

Không dùng heading quá lớn trong product UI.

Product screen không cần:

```txt
H1 64px
Hero title trong mọi page
Text gradient
Uppercase section title khắp nơi
Font weight 800 cho mọi heading
```

Dùng hierarchy rõ nhưng tiết chế:

```txt
Page title: 20–24px
Section title: 16–20px
Post body: 15px
Metadata: 13px
Caption/helper: 12px
```

## 5.3. Vietnamese readability

Tiếng Việt có dấu, nên cần line-height thoải mái.

Rules:

* Body text không dưới `14px`.
* Post body nên `15px`.
* Line-height body nên khoảng `1.45–1.6`.
* Không dùng letter-spacing âm quá mạnh cho tiếng Việt dài.
* Không uppercase câu dài tiếng Việt.

---

## 6. Type Scale

UEConnect dùng type scale vừa phải, phù hợp app social.

```css
--text-2xs: 11px;
--text-xs:  12px;
--text-sm:  13px;
--text-md:  14px;
--text-base: 15px;
--text-lg:  16px;
--text-xl:  18px;
--text-2xl: 20px;
--text-3xl: 24px;
--text-4xl: 32px;
--text-5xl: 40px;
```

## 6.1. Usage Table

| Token       | Size | Usage                                           |
| ----------- | ---: | ----------------------------------------------- |
| `text-2xs`  | 11px | Micro label rất hiếm, counter, compact metadata |
| `text-xs`   | 12px | Caption, helper text, badge                     |
| `text-sm`   | 13px | Metadata, timestamp, secondary labels           |
| `text-md`   | 14px | Form label, small body, sidebar metadata        |
| `text-base` | 15px | Default product body, post content              |
| `text-lg`   | 16px | Emphasized body, mobile input, important copy   |
| `text-xl`   | 18px | Card title, profile name small                  |
| `text-2xl`  | 20px | Section title, feed page title                  |
| `text-3xl`  | 24px | Page title, profile name large                  |
| `text-4xl`  | 32px | Onboarding heading, marketing section           |
| `text-5xl`  | 40px | Rare hero heading only                          |

## 6.2. Product Rule

Trong product UI hằng ngày, thường chỉ dùng:

```txt
12px
13px
14px
15px
16px
18px
20px
24px
```

`32px+` chỉ dùng cho onboarding, landing, marketing hoặc rare brand moment.

---

## 7. Line Height System

```css
--leading-none: 1;
--leading-tight: 1.2;
--leading-snug: 1.35;
--leading-normal: 1.45;
--leading-relaxed: 1.6;
--leading-loose: 1.75;
```

## 7.1. Usage

| Context        |    Line height |
| -------------- | -------------: |
| Badge / pill   | `1` hoặc `1.2` |
| Button         |     `1.2–1.35` |
| Heading        |    `1.15–1.25` |
| Post body      |      `1.5–1.6` |
| Comment        |    `1.45–1.55` |
| Message bubble |    `1.45–1.55` |
| Long paragraph |     `1.6–1.75` |
| Metadata       |    `1.35–1.45` |

## 7.2. Rules

* Text tiếng Việt dài nên có line-height tối thiểu `1.45`.
* Không dùng `line-height: 1` cho body.
* Button cần line-height ổn để chữ không lệch.
* Heading lớn có thể tight hơn nhưng không được đè dấu tiếng Việt.

---

## 8. Font Weight System

```css
--font-regular: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

Không dùng mặc định:

```txt
300
800
900
```

trừ khi có lý do.

## 8.1. Usage

| Weight | Usage                                           |
| ------ | ----------------------------------------------- |
| 400    | Body, post, comment, message                    |
| 500    | Secondary emphasis, nav item                    |
| 600    | Button, author name, section label, active item |
| 700    | Page title, important heading, brand name       |

## 8.2. Rules

* Post body dùng `400`.
* Author name dùng `600`.
* Button dùng `600`.
* Page title dùng `700`.
* Không dùng `700` cho mọi label.
* Không dùng `800/900` cho social UI mặc định.

Quá nhiều bold làm UI la hét. Một vài chữ đậm là hierarchy. Tất cả đều đậm là hội trường đang tranh micro.

---

## 9. Letter Spacing

```css
--tracking-tight: -0.03em;
--tracking-snug: -0.015em;
--tracking-normal: 0;
--tracking-wide: 0.04em;
--tracking-caps: 0.08em;
```

## 9.1. Usage

| Token             | Usage                             |
| ----------------- | --------------------------------- |
| `tracking-tight`  | Hero/large heading rất hạn chế    |
| `tracking-snug`   | Page title, brand name            |
| `tracking-normal` | Body, post, comment, message      |
| `tracking-wide`   | Small label nếu cần               |
| `tracking-caps`   | Kicker/eyebrow uppercase rất ngắn |

## 9.2. Vietnamese Rules

* Không dùng tracking âm mạnh cho câu tiếng Việt dài.
* Không uppercase đoạn tiếng Việt dài.
* Kicker uppercase chỉ dùng 1–3 từ.
* Body text luôn `tracking-normal`.

---

## 10. Text Style Tokens

## 10.1. App / Page Styles

| Style           | Size | Weight | Line height | Tracking | Usage                     |
| --------------- | ---: | -----: | ----------: | -------: | ------------------------- |
| `display-hero`  | 40px |    700 |        1.05 |  -0.03em | Rare onboarding/marketing |
| `page-title`    | 24px |    700 |        1.25 |  -0.02em | Page title                |
| `page-subtitle` | 15px |    400 |         1.6 |        0 | Page description          |
| `section-title` | 20px |    700 |        1.35 | -0.015em | Section header            |
| `section-label` | 12px |    700 |         1.2 |   0.08em | Kicker label              |

## 10.2. Social Content Styles

| Style            | Size | Weight | Line height | Usage                     |
| ---------------- | ---: | -----: | ----------: | ------------------------- |
| `post-author`    | 15px |    600 |        21px | Tên người đăng            |
| `post-meta`      | 13px |    400 |        18px | Khoa, khóa, thời gian     |
| `post-body`      | 15px |    400 |        23px | Nội dung bài viết         |
| `comment-author` | 14px |    600 |        20px | Tên người comment         |
| `comment-body`   | 14px |    400 |        21px | Nội dung comment          |
| `reply-preview`  | 13px |    400 |        19px | Preview reply/comment     |
| `action-label`   | 13px |    600 |        18px | Like, comment, save count |

## 10.3. Messaging Styles

| Style                  | Size | Weight | Line height | Usage              |
| ---------------------- | ---: | -----: | ----------: | ------------------ |
| `conversation-name`    | 15px |    600 |        21px | Tên conversation   |
| `conversation-preview` | 13px |    400 |        18px | Tin nhắn cuối      |
| `message-body`         | 15px |    400 |        22px | Nội dung message   |
| `message-meta`         | 12px |    400 |        16px | Time, seen, failed |
| `typing-indicator`     | 13px |    400 |        18px | Đang nhập          |

## 10.4. Form Styles

| Style               | Size | Weight | Line height | Usage         |
| ------------------- | ---: | -----: | ----------: | ------------- |
| `label`             | 14px |    600 |        20px | Input label   |
| `input-text`        | 15px |    400 |        21px | Input value   |
| `placeholder`       | 15px |    400 |        21px | Placeholder   |
| `helper-text`       | 12px |    400 |        16px | Help text     |
| `error-text`        | 12px |    500 |        16px | Error message |
| `field-group-title` | 15px |    600 |        21px | Group label   |

## 10.5. Component Styles

| Style              | Size | Weight | Line height | Usage             |
| ------------------ | ---: | -----: | ----------: | ----------------- |
| `button-md`        | 15px |    600 |        20px | Default button    |
| `button-sm`        | 14px |    600 |        18px | Small button      |
| `nav-label`        | 15px |    600 |        21px | Desktop nav       |
| `bottom-nav-label` | 11px |    600 |        14px | Mobile bottom nav |
| `badge-label`      | 12px |    600 |        16px | Badge             |
| `chip-label`       | 13px |    600 |        18px | Chip/tag          |
| `toast-title`      | 14px |    600 |        20px | Toast title       |
| `toast-body`       | 13px |    400 |        18px | Toast body        |

---

## 11. Page-level Typography

## 11.1. Home Feed

Home Feed là nơi typography quan trọng nhất.

Recommended:

```css
.post-author {
  font-size: 15px;
  font-weight: 600;
  line-height: 21px;
}

.post-meta {
  font-size: 13px;
  font-weight: 400;
  line-height: 18px;
}

.post-body {
  font-size: 15px;
  font-weight: 400;
  line-height: 23px;
}
```

Rules:

* Post body không dưới `15px`.
* Metadata không làm quá nhạt.
* Action label nhỏ nhưng vẫn đọc được.
* Không dùng heading lớn trong mỗi post.
* Không center-align post content.

## 11.2. Discovery

Discovery cần hấp dẫn nhưng không dating vibe.

Recommended:

| Element        | Style             |
| -------------- | ----------------- |
| Name           | 24px / 700        |
| Faculty/cohort | 14px / 500        |
| Bio            | 15px / 400 / 1.55 |
| Interest chip  | 13px / 600        |
| CTA            | 15px / 600        |

Rules:

* Name có thể lớn hơn feed.
* Bio không nên quá dài.
* Không dùng typography quá sexy/dramatic.
* Không dùng text như `Hot`, `Match`, `Swipe`.

## 11.3. Profile

Profile cần vừa cá tính vừa rõ thông tin.

| Element           | Style             |
| ----------------- | ----------------- |
| Profile name      | 24px / 700        |
| Username/metadata | 14px / 400        |
| Bio               | 15px / 400 / 1.55 |
| Stats number      | 16px / 700        |
| Stats label       | 13px / 400        |
| Tab label         | 15px / 600        |

Rules:

* Profile name rõ nhưng không quá khổng lồ.
* Bio dễ đọc.
* Metadata không biến thành hồ sơ hành chính.
* Tabs phải rõ active state.

## 11.4. Messaging

Messaging cần đọc nhanh và thoải mái.

| Element           | Style             |
| ----------------- | ----------------- |
| Message body      | 15px / 400 / 22px |
| Conversation name | 15px / 600        |
| Preview           | 13px / 400        |
| Timestamp         | 12px / 400        |
| Composer input    | 15px / 400        |

Rules:

* Không dùng font nhỏ hơn 14px cho message body.
* Bubble không nhồi quá nhiều text style.
* Failed state cần text nhỏ rõ.
* Placeholder phải đủ contrast.

## 11.5. Mentor

Mentor cần chuyên nghiệp vừa đủ, không LinkedIn hóa.

| Element        | Style            |
| -------------- | ---------------- |
| Mentor name    | 20–24px / 700    |
| Role/expertise | 14px / 500       |
| Bio            | 15px / 400 / 1.6 |
| Section title  | 16px / 700       |
| Request status | 13px / 600       |

Rules:

* Tránh CV typography dày đặc.
* Chia thông tin thành section nhỏ.
* Dùng chip cho expertise thay vì paragraph dài.
* Tone chữ hỗ trợ, không áp lực career.

## 11.6. Admin

Admin cần data readability.

| Element          | Style                           |
| ---------------- | ------------------------------- |
| Admin page title | 24px / 700                      |
| Table header     | 12px / 700 / uppercase optional |
| Table cell       | 14px / 400                      |
| Status badge     | 12px / 600                      |
| Filter label     | 13px / 600                      |
| Audit log        | 13–14px / 400                   |

Rules:

* Không dùng text quá nhỏ trong table.
* Table header có thể uppercase nhưng phải ngắn.
* Numeric data nên dùng `font-data`.
* Admin không dùng playful typography.

---

## 12. Responsive Typography

## 12.1. Desktop

Desktop có thể dùng:

```txt
Page title: 24px
Section title: 20px
Post body: 15px
Right panel text: 13–14px
```

## 12.2. Tablet

Tablet giữ gần desktop nhưng giảm spacing, không nhất thiết giảm font.

```txt
Page title: 22–24px
Post body: 15px
Nav label có thể ẩn nếu collapsed
```

## 12.3. Mobile

Mobile không chỉ thu nhỏ chữ.

```txt
Top bar title: 18–20px
Post body: 15px
Comment: 14px
Message: 15px
Bottom nav label: 11px
Button: 15px
Input: 16px recommended to avoid iOS zoom
```

## 12.4. Mobile Rules

* Input trên mobile nên `16px` nếu muốn tránh iOS auto zoom.
* Body không dưới `14px`.
* Post vẫn nên `15px`.
* Bottom nav label có thể `11px`, nhưng icon phải rõ.
* Page title không cần quá lớn.

---

## 13. Reading Width

Typography không chỉ là font size. Width cũng quyết định đọc có đau khổ hay không.

## 13.1. Feed Width

```txt
Recommended feed width: 560–640px
Default target: 600px
```

## 13.2. Paragraph Width

Long paragraph nên giới hạn:

```css
max-width: 680px;
```

## 13.3. Messaging Width

Message bubble max width:

```css
.message-bubble {
  max-width: min(72%, 520px);
}
```

Mobile:

```css
.message-bubble {
  max-width: 82%;
}
```

## 13.4. Admin Table

Admin table không ép text wrap quá sớm. Dùng:

* Truncate cho email/ID dài.
* Tooltip/detail view cho nội dung dài.
* Row height đủ thoáng.

---

## 14. Truncation Rules

## 14.1. Where Truncation Is Allowed

Được truncate:

* Conversation preview.
* Notification preview.
* Long username in sidebar.
* Community description in card.
* Mentor expertise summary.
* Admin table cell with detail view.

## 14.2. Where Truncation Is Not Allowed

Không truncate:

* Error message quan trọng.
* Verification status reason.
* Safety warning.
* Form label.
* Primary CTA.
* Report reason.
* Terms/privacy confirmation.

## 14.3. CSS Example

```css
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

For multiline:

```css
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
```

---

## 15. Typography Color Pairing

Typography phải dùng color token từ `03-color-system.md`.

```css
--ue-text: #0F172A;
--ue-text-secondary: #475569;
--ue-text-muted: #64748B;
--ue-text-disabled: #94A3B8;
--ue-text-inverse: #FFFFFF;
```

## 15.1. Usage

| Text Role      | Color                 |
| -------------- | --------------------- |
| Heading        | `--ue-text`           |
| Body           | `--ue-text`           |
| Secondary copy | `--ue-text-secondary` |
| Metadata       | `--ue-text-muted`     |
| Placeholder    | `--ue-text-disabled`  |
| Text on brand  | `--ue-text-inverse`   |
| Error          | `--danger-text`       |
| Success        | `--success-text`      |
| Warning        | `--warning-text`      |

## 15.2. Rules

* Không dùng brand blue cho paragraph dài.
* Không dùng muted text cho nội dung quan trọng.
* Không dùng text-disabled cho helper text bình thường.
* Error text phải có màu danger + nội dung rõ.
* Link có thể dùng brand blue nhưng phải nhận ra là link.

---

## 16. Link Typography

Links trong UEConnect nên rõ nhưng không quá gây nhiễu.

```css
.link {
  color: var(--ue-brand);
  font-weight: 500;
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}
```

Rules:

* Link trong paragraph nên underline on hover.
* Important link có thể `font-weight: 600`.
* Không dùng cyan link quá sáng.
* Không dùng gradient text link.
* Link phải có focus state.

---

## 17. Button Typography

```css
.button {
  font-size: 15px;
  font-weight: 600;
  line-height: 20px;
}
```

Sizes:

| Button | Font          |
| ------ | ------------- |
| XS     | 13px / 600    |
| SM     | 14px / 600    |
| MD     | 15px / 600    |
| LG     | 15–16px / 600 |
| XL     | 16px / 700    |

Rules:

* Button text nên ngắn.
* Không uppercase toàn bộ button tiếng Việt.
* Không dùng 700 nếu không cần.
* Loading button phải giữ width, tránh layout shift.
* Icon + label gap 8px.

Good:

```txt
Tạo bài viết
Gửi lời chào
Hoàn thiện hồ sơ
Xác thực tài khoản
```

Bad:

```txt
BẤM VÀO ĐÂY NGAY
TẠO KẾT NỐI CỰC HOT
MATCH NGAY
```

Xin đừng bắt typography tham gia tội ác marketing.

---

## 18. Form Typography

## 18.1. Label

```css
.form-label {
  font-size: 14px;
  font-weight: 600;
  line-height: 20px;
  color: var(--ue-text);
}
```

## 18.2. Helper Text

```css
.form-helper {
  font-size: 12px;
  font-weight: 400;
  line-height: 16px;
  color: var(--ue-text-muted);
}
```

## 18.3. Error Text

```css
.form-error {
  font-size: 12px;
  font-weight: 500;
  line-height: 16px;
  color: var(--danger-text);
}
```

Rules:

* Placeholder không thay label.
* Error text phải gần field.
* Label không quá nhỏ.
* Form nhiều step cần title + description rõ.
* Verification form phải dùng official tone, không quá casual.

---

## 19. Badge & Chip Typography

## 19.1. Badge

```css
.badge {
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
}
```

Dùng cho:

* Verified UEer.
* Mentor.
* Alumni.
* Pending.
* Approved.
* Community role.

## 19.2. Chip

```css
.chip {
  font-size: 13px;
  font-weight: 600;
  line-height: 18px;
}
```

Dùng cho:

* Interest.
* Faculty.
* Course.
* Mentor expertise.
* Discovery filters.

Rules:

* Badge/chip không dùng text dài.
* Nếu chip quá dài, truncate hoặc wrap có kiểm soát.
* Không uppercase chip tiếng Việt.

---

## 20. Empty State Typography

Empty state cần rõ, không lạc quan giả tạo.

Recommended:

| Element     | Style                |
| ----------- | -------------------- |
| Empty title | 16–18px / 700        |
| Empty body  | 14–15px / 400 / 1.55 |
| CTA         | Button MD            |

Good:

```txt
Feed của bạn đang hơi yên ắng.
Khám phá UEers cùng khoa hoặc tạo bài viết đầu tiên để bắt đầu.
```

Bad:

```txt
Không có dữ liệu.
```

Câu này đúng nhưng vô dụng, như nhiều thông báo lỗi mà nhân loại vẫn viết với niềm tin mong manh.

---

## 21. Notification Typography

| Element           | Style                         |
| ----------------- | ----------------------------- |
| Actor name        | 14px / 600                    |
| Notification body | 14px / 400                    |
| Timestamp         | 12–13px / 400                 |
| Unread indicator  | Không chỉ dựa vào font weight |

Rules:

* Notification phải scan nhanh.
* Actor/action/object rõ.
* Không viết câu quá dài.
* Unread có thể dùng background nhẹ + dot + font weight.

---

## 22. Accessibility Rules

## 22.1. Minimum Sizes

* Body text: tối thiểu `14px`.
* Post/message: khuyến nghị `15px`.
* Metadata: tối thiểu `12px`.
* Button: tối thiểu `14px`.
* Form input mobile: khuyến nghị `16px`.

## 22.2. Contrast

Typography phải đi cùng color system:

* Body dùng `--ue-text`.
* Secondary dùng `--ue-text-secondary`.
* Metadata dùng `--ue-text-muted`.
* Disabled chỉ dùng cho disabled.
* Không dùng text quá nhạt trên nền trắng.

## 22.3. Zoom Support

UI phải ổn khi browser zoom 125% hoặc 150%.

Rules:

* Không lock height quá cứng với text dài.
* Button cho phép text wrap ở một số context.
* Card không vỡ khi font tăng.
* Form error có chỗ hiển thị.

## 22.4. Screen Reader

Typography visual không thay thế semantic HTML.

Dùng đúng:

```html
<h1>Trang chủ</h1>
<h2>Gợi ý kết nối</h2>
<p>...</p>
```

Không dùng toàn `div` rồi style giống heading. Mắt người có thể bị lừa, screen reader thì không cảm động trước CSS.

---

## 23. TailwindCSS Mapping

## 23.1. Font Family

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      fontFamily: {
        sans: [
          "system-ui",
          "-apple-system",
          "BlinkMacSystemFont",
          "\"Segoe UI\"",
          "Roboto",
          "\"Helvetica Neue\"",
          "Arial",
          "\"Be Vietnam Pro\"",
          "sans-serif",
        ],
        vn: ["\"Be Vietnam Pro\"", "system-ui", "sans-serif"],
        data: ["Inter", "system-ui", "sans-serif"],
        serifBrand: ["\"Source Serif Variable\"", "Georgia", "serif"],
      },
    },
  },
}
```

## 23.2. Font Size

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      fontSize: {
        "2xs": ["11px", { lineHeight: "14px" }],
        xs: ["12px", { lineHeight: "16px" }],
        sm: ["13px", { lineHeight: "18px" }],
        md: ["14px", { lineHeight: "20px" }],
        base: ["15px", { lineHeight: "23px" }],
        lg: ["16px", { lineHeight: "24px" }],
        xl: ["18px", { lineHeight: "26px" }],
        "2xl": ["20px", { lineHeight: "28px" }],
        "3xl": ["24px", { lineHeight: "32px" }],
        "4xl": ["32px", { lineHeight: "38px" }],
        "5xl": ["40px", { lineHeight: "46px" }],
      },
    },
  },
}
```

## 23.3. Letter Spacing

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      letterSpacing: {
        snug: "-0.015em",
        tightProduct: "-0.03em",
        caps: "0.08em",
      },
    },
  },
}
```

---

## 24. CSS Variables

```css
:root {
  /* Font families */
  --font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Be Vietnam Pro", sans-serif;
  --font-vietnamese: "Be Vietnam Pro", system-ui, sans-serif;
  --font-data: "Inter", system-ui, sans-serif;
  --font-brand-serif: "Source Serif Variable", Georgia, "Times New Roman", serif;

  /* Font sizes */
  --text-2xs: 11px;
  --text-xs: 12px;
  --text-sm: 13px;
  --text-md: 14px;
  --text-base: 15px;
  --text-lg: 16px;
  --text-xl: 18px;
  --text-2xl: 20px;
  --text-3xl: 24px;
  --text-4xl: 32px;
  --text-5xl: 40px;

  /* Line heights */
  --leading-none: 1;
  --leading-tight: 1.2;
  --leading-snug: 1.35;
  --leading-normal: 1.45;
  --leading-relaxed: 1.6;
  --leading-loose: 1.75;

  /* Font weights */
  --font-regular: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold: 700;

  /* Tracking */
  --tracking-tight: -0.03em;
  --tracking-snug: -0.015em;
  --tracking-normal: 0;
  --tracking-wide: 0.04em;
  --tracking-caps: 0.08em;
}
```

---

## 25. Blade / Tailwind Examples

## 25.1. Post Author Row

```blade
<div class="flex items-center gap-2">
  <a href="{{ route('profile.show', $user) }}" class="text-base font-semibold leading-[21px] text-ue-text hover:underline">
    {{ $user->name }}
  </a>

  <span class="inline-flex items-center rounded-full bg-[rgba(18,72,116,0.08)] px-2 py-0.5 text-xs font-semibold text-ue-brand">
    Đã xác thực UEer
  </span>

  <span class="text-sm font-normal text-ue-text-muted">
    · {{ $post->created_at->diffForHumans() }}
  </span>
</div>
```

## 25.2. Post Body

```blade
<p class="mt-2 text-base font-normal leading-[23px] text-ue-text">
  {{ $post->body }}
</p>
```

## 25.3. Form Field

```blade
<div>
  <label for="student_id" class="mb-1 block text-md font-semibold leading-5 text-ue-text">
    Mã sinh viên
  </label>

  <input
    id="student_id"
    name="student_id"
    class="h-11 w-full rounded-md border border-ue-border bg-white px-3 text-lg leading-6 text-ue-text placeholder:text-ue-text-disabled focus:border-ue-brand focus:outline-none focus:ring-4 focus:ring-[rgba(18,72,116,0.16)] md:text-base"
    placeholder="Nhập mã sinh viên của bạn"
  />

  <p class="mt-1 text-xs leading-4 text-ue-text-muted">
    Mã sinh viên giúp UEConnect xác thực bạn thuộc cộng đồng HCMUE.
  </p>
</div>
```

## 25.4. Message Bubble

```blade
<div class="max-w-[82%] rounded-2xl bg-ue-brand px-4 py-2 text-base font-normal leading-[22px] text-white md:max-w-[72%]">
  {{ $message->body }}
</div>
```

---

## 26. Typography Anti-patterns

## 26.1. Oversized Product Heading

Sai:

```css
.feed-title {
  font-size: 48px;
  font-weight: 800;
}
```

Đúng:

```css
.feed-title {
  font-size: 20px;
  font-weight: 700;
}
```

## 26.2. Too Many Weights

Sai:

```txt
300, 400, 500, 600, 700, 800, 900 all on one screen
```

Đúng:

```txt
400 body
500 secondary emphasis
600 actions/name
700 title
```

## 26.3. Muted Important Text

Sai:

```css
.error-message {
  color: var(--ue-text-muted);
}
```

Đúng:

```css
.error-message {
  color: var(--danger-text);
}
```

## 26.4. Uppercase Vietnamese

Sai:

```txt
KHÁM PHÁ UEERS CÙNG KHOA VỚI BẠN NGAY HÔM NAY
```

Đúng:

```txt
Khám phá UEers cùng khoa với bạn
```

## 26.5. Center-aligned Post Body

Sai:

```css
.post-body {
  text-align: center;
}
```

Đúng:

```css
.post-body {
  text-align: left;
}
```

---

## 27. Typography QA Checklist

## 27.1. Readability

* [ ] Body text có tối thiểu 14px không?
* [ ] Post/message có khoảng 15px không?
* [ ] Line-height có đủ thoáng cho tiếng Việt không?
* [ ] Metadata có đọc được không?
* [ ] Paragraph dài có width hợp lý không?

## 27.2. Hierarchy

* [ ] Page title có rõ hơn section title không?
* [ ] Author name có nổi hơn metadata không?
* [ ] Button label có rõ không?
* [ ] Error/helper text có phân biệt không?
* [ ] Có quá nhiều bold không?

## 27.3. Product Fit

* [ ] Feed có giống social product thật không?
* [ ] Discovery không quá dating/dramatic không?
* [ ] Mentor không quá LinkedIn/CV không?
* [ ] Admin đủ nghiêm túc không?
* [ ] Typography có tránh cảm giác landing page AI không?

## 27.4. Responsive

* [ ] Mobile input có tránh iOS zoom không?
* [ ] Text không vỡ khi màn nhỏ không?
* [ ] Bottom nav label đọc được không?
* [ ] Message bubble wrap tốt không?
* [ ] Zoom 125–150% vẫn ổn không?

## 27.5. Accessibility

* [ ] Heading dùng semantic HTML không?
* [ ] Text contrast đủ không?
* [ ] Error không chỉ dựa vào màu không?
* [ ] Truncate không làm mất thông tin quan trọng không?
* [ ] Icon-only action có label/aria không?

---

## 28. AI Prompt Notes

Khi yêu cầu AI tạo UI/code cho UEConnect, thêm:

```txt
Follow UEConnect Typography System.
Use system-first typography with Vietnamese-friendly fallback.
Use restrained social product type scale: post body around 15px, metadata around 13px, page title around 20–24px.
Do not use oversized landing-page headings in daily product screens.
Do not use too many font weights.
Do not uppercase long Vietnamese text.
Do not use serif fonts in feed, messaging, discovery, or product UI.
Keep text readable, content-first, and mobile-friendly.
Use clear hierarchy for author, metadata, post body, comment, message, form label, helper text, and error text.
```

---

## 29. Final Decision

Typography system chính thức của UEConnect:

```txt
Primary font: system-ui stack
Vietnamese support: Be Vietnam Pro
Data/admin: Inter
Brand serif: rare official context only

Default product body: 15px / 400 / 23px
Metadata: 13px / 400 / 18px
Button: 15px / 600
Page title: 20–24px / 700
Mobile input: 16px recommended
```

Câu chốt:

```txt
Typography của UEConnect phải làm nội dung dễ đọc, interaction dễ hiểu, và sản phẩm trông như social platform thật, không phải poster AI đang hét bằng font 48px.
```
