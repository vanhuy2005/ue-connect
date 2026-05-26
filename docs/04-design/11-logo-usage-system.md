---
title: "Logo Usage System"
module: "04-design"
product: "UEConnect"
version: "1.1"
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
  - "10-icon-system.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
  - "20-agent-prompt-guide.md"
related_assets:
  - "logo-primary.svg"
  - "logo-horizontal.svg"
  - "logo-mark.svg"
  - "logo-mark-inverse.svg"
  - "logo-horizontal-inverse.svg"
  - "favicon.svg"
  - "favicon.ico"
  - "pwa-icon-72.png"
  - "pwa-icon-96.png"
  - "pwa-icon-144.png"
  - "pwa-icon-192.png"
  - "pwa-icon-512.png"
---

# Logo Usage System

## 1. Purpose

File này định nghĩa cách sử dụng logo UEConnect trên toàn bộ sản phẩm, bao gồm:

- Public landing page.
- PWA app shell.
- Desktop navbar.
- Mobile navbar.
- Auth page.
- Onboarding.
- Verification flow.
- Admin dashboard.
- Community / Club pages.
- Mentor / Career Pathway pages.
- Splash screen light/dark.
- Browser favicon.
- PWA app icon.
- Notification icon.
- Portfolio / case study presentation.

Logo là một phần của hệ thống nhận diện, không phải hình trang trí muốn kéo đâu thì kéo. Nếu logo bị dùng sai, toàn bộ sản phẩm sẽ trông như đồ án bị ghép tài nguyên vào phút cuối, và tiếc thay, người dùng có mắt.

---

## 2. Final Logo Direction

### 2.1. Approved Concept

Logo UEConnect đã chốt theo hướng:

```txt
Two connected people + open book / knowledge shape + subtle educational connection symbol
````

Ý tưởng chính:

| Element                       | Meaning                                              |
| ----------------------------- | ---------------------------------------------------- |
| Two circular heads            | Hai con người / hai UEers đang kết nối               |
| Two abstract body/book panels | Vừa là người, vừa gợi mở thành quyển sách / tri thức |
| Open base shape               | Nền tảng học tập, cộng đồng, mở rộng kết nối         |
| Negative space center         | Kết nối, đối thoại, cân bằng                         |
| Blue academic tone            | HCMUE, giáo dục, tin cậy, hiện đại                   |

### 2.2. Design Intention

Logo cần tạo cảm giác:

```txt
trusted
academic
youthful
clean
human
connected
digital-first
HCMUE-rooted
```

Logo không được tạo cảm giác:

```txt
dating app
Tinder clone
corporate HR tool
government portal cũ kỹ
crypto community
generic startup logo
social drama app
```

### 2.3. Important Visual Rule

Icon mark **không được viết rõ chữ UE**.

Icon chỉ nên gợi nhẹ bằng hình khối, negative space và cấu trúc thị giác.

Lý do:

```txt
- Tránh nhìn như duplicate "UE UE" cạnh wordmark.
- Giữ icon đơn giản hơn.
- Tăng khả năng dùng ở favicon/app icon.
- Giúp logo trông mature hơn, bớt literal.
```

Biểu tượng mà phải hét thẳng chữ cái vào mặt người xem thì hơi giống một bài thuyết trình thiếu tự tin. Ta không làm vậy.

---

## 3. Logo Variants

UEConnect có 3 biến thể logo chính:

| Variant         | Description                    | Primary Usage                      |
| --------------- | ------------------------------ | ---------------------------------- |
| Primary Logo    | Icon Mark + Wordmark + Tagline | Brand cover, auth, landing, splash |
| Horizontal Logo | Icon Mark + Wordmark           | Navbar, app shell, admin header    |
| Icon Mark       | Symbol only                    | Favicon, app icon, compact UI      |

---

# 4. Primary Logo

## 4.1. Definition

Primary Logo là phiên bản đầy đủ nhất:

```txt
Icon Mark + UEConnect wordmark + tagline
```

Official tagline:

```txt
Kết nối cộng đồng HCMUE
```

Recommended visual structure:

```txt
[Icon Mark]  UEConnect
             KẾT NỐI CỘNG ĐỒNG HCMUE
```

## 4.2. Usage

Dùng Primary Logo ở:

```txt
- Public landing hero
- Auth page
- Verification welcome page
- Onboarding welcome screen
- Splash screen if space allows
- Brand guideline cover
- Portfolio case study cover
- Presentation title slide
- App install prompt
```

## 4.3. Do Not Use Primary Logo In

```txt
- small mobile navbar
- bottom navigation
- favicon
- tiny icon button
- notification icon
- dense sidebar item
- avatar placeholder
```

## 4.4. Minimum Size

Digital minimum:

```txt
height: 40px
```

Recommended:

```txt
height: 48px - 96px
```

Minimum width depends on aspect ratio, but do not compress manually.

---

# 5. Horizontal Logo

## 5.1. Definition

Horizontal Logo là phiên bản tối ưu cho UI navigation:

```txt
Icon Mark + UEConnect wordmark
```

Không có tagline trong variant này.

## 5.2. Usage

Dùng Horizontal Logo ở:

```txt
- Desktop navbar
- Desktop sidebar header
- Admin dashboard header
- Public landing navbar
- Email header future
- Documentation header
```

## 5.3. Recommended Sizes

Desktop navbar:

```txt
height: 32px - 40px
```

Tablet navbar:

```txt
height: 28px - 32px
```

Compact desktop/sidebar:

```txt
height: 28px - 32px
```

## 5.4. Minimum Size

Digital minimum:

```txt
height: 28px
```

Không dùng nhỏ hơn vì wordmark sẽ bắt đầu thành một vệt xanh tự tin vô nghĩa.

---

# 6. Icon Mark

## 6.1. Definition

Icon Mark là biểu tượng rút gọn của UEConnect.

Nó gồm:

```txt
- two human dots
- two abstract connected panels
- open book / connection base
```

## 6.2. Usage

Dùng Icon Mark ở:

```txt
- favicon
- PWA app icon
- mobile navbar
- compact sidebar
- splash screen
- loading screen
- notification icon
- empty state subtle mark
- brand watermark
```

## 6.3. Visual Requirements

Icon Mark phải:

```txt
- readable at 24px
- recognizable at 32px
- clean at 48px
- scalable to 512px
- work in blue, white, and neutral ink
- preserve rounded, friendly geometry
- avoid tiny inner details
```

## 6.4. Minimum Size

Digital minimum:

```txt
24px
```

Favicon exception:

```txt
16px allowed only for favicon export
```

Recommended UI size:

```txt
32px - 48px
```

---

# 7. Wordmark

## 7.1. Product Name

Official name:

```txt
UEConnect
```

Allowed:

```txt
UEConnect
```

Avoid:

```txt
UE Connect
UE connector
UEConnector
UE-connect
ueconnect
UE CONNECT
UEConnect App
```

The product is **UEConnect**. “UE connector” nghe như tên package npm chưa ai maintain, nên thôi.

## 7.2. Wordmark Style

Recommended typography direction:

```txt
Font family: Be Vietnam Pro or closest geometric sans
Weight: 700 / 800
Letter spacing: -0.02em to 0
Case: Brand case
```

## 7.3. Tagline Style

Tagline:

```txt
KẾT NỐI CỘNG ĐỒNG HCMUE
```

Recommended:

```txt
Font family: Be Vietnam Pro
Weight: 600 / 700
Letter spacing: 0.14em - 0.22em
Case: uppercase
```

## 7.4. Tagline Rule

Tagline chỉ dùng với Primary Logo hoặc brand presentation.

Không dùng tagline trong:

```txt
navbar
favicon
app icon
mobile header
small components
```

---

# 8. Color System

## 8.1. Brand Anchor

Primary brand color:

```txt
HCMUE Blue: #124874
```

Recommended supporting blues:

```txt
Deep Blue: #0A3761
Academic Blue: #005BAC
Bright Blue: #0B7FEA
Soft Cyan Blue: #4BB7E8
```

## 8.2. Logo Color Variants

UEConnect có 4 color variants chính:

| Variant              | Description                                 | Usage                      |
| -------------------- | ------------------------------------------- | -------------------------- |
| Full Color / Default | Primary blue logo on white/light background | Default usage              |
| Blue Monochrome      | One-color blue logo                         | Formal UI/docs             |
| Inverse / White      | White logo on blue/dark/gradient background | Dark surfaces              |
| Neutral / Ink        | Black/near-black logo                       | Rare print/formal fallback |

## 8.3. Full Color / Default

Usage:

```txt
white background
light neutral background
light blue background if contrast is enough
```

Color:

```txt
logo: #124874 or controlled blue gradient
wordmark: #124874
tagline: #124874
```

## 8.4. Blue Monochrome

Usage:

```txt
formal documents
simple UI surfaces
low-complexity brand placement
```

Color:

```txt
#124874
```

## 8.5. Inverse / White

Usage:

```txt
HCMUE blue background
blue-only gradient background
dark navy background
splash screen dark
```

Color:

```txt
#FFFFFF
```

## 8.6. Neutral / Ink

Usage:

```txt
black-and-white print
internal wireframe
rare legal/formal context
```

Color:

```txt
#111827
```

## 8.7. Forbidden Colors

Không dùng logo với:

```txt
red
pink
orange
purple dating gradient
rainbow gradient
neon green
low contrast gray
random yellow
random brand colors from another project
```

UEConnect được phép học cách Tinder làm gradient mượt, nhưng không được mượn luôn linh hồn hẹn hò của nó. Một trường đại học còn cần chút phẩm giá.

---

# 9. Gradient Usage

## 9.1. Allowed Gradient Direction

Logo/app icon có thể dùng blue-only gradient:

```txt
#124874 → #005BAC → #0B7FEA
```

Hoặc:

```txt
#0A3761 → #124874 → #4BB7E8
```

## 9.2. Gradient Rules

```txt
- Only blue family.
- Smooth transition.
- No red/pink/orange.
- No high-saturation neon.
- No chaotic multi-stop rainbow.
- Keep logo readable at small size.
```

## 9.3. Where Gradient Is Allowed

```txt
- app icon background
- splash screen background
- hero section background
- logo presentation board
- soft decorative brand background
```

## 9.4. Where Gradient Should Be Avoided

```txt
- small wordmark
- favicon 16px
- dense navbar
- admin dashboard header
- formal verification page
```

---

# 10. Background Usage

## 10.1. On White

Preferred:

```txt
Full Color / Default
Blue Monochrome
```

Usage:

```txt
main navbar
auth form
documentation
dashboard
```

## 10.2. On Light Blue

Preferred:

```txt
Blue Monochrome
Full Color if contrast remains strong
```

Use light blue backgrounds like:

```txt
#EFF6FF
#EAF4FF
#F4FAFF
```

## 10.3. On HCMUE Blue

Preferred:

```txt
Inverse / White
```

Background:

```txt
#124874
```

## 10.4. On Blue-only Gradient

Preferred:

```txt
Inverse / White
```

Gradient:

```txt
#0A3761 → #005BAC → #0B7FEA
```

## 10.5. On Image Background

Allowed only when:

```txt
- logo is placed on solid/scrim container
- contrast is clear
- background is not visually noisy
```

Recommended scrim:

```txt
rgba(18, 72, 116, 0.72)
```

## 10.6. Background Misuse

Do not place logo on:

```txt
busy photos
low contrast pattern
red/orange gradient
random illustration
dark background without inverse logo
```

---

# 11. Clear Space

## 11.1. Definition

Clear space là khoảng trống tối thiểu quanh logo.

Use `x`:

```txt
x = height of one circular head in the Icon Mark
```

Minimum clear space:

```txt
0.5x on all sides
```

Preferred clear space:

```txt
1x on all sides
```

## 11.2. Clear Space Rules

Không đặt logo quá gần:

```txt
buttons
search box
avatar
notification badge
divider
screen edge
other logos
dense text
community logo
```

## 11.3. Navbar Spacing

Desktop navbar:

```txt
left padding: 24px
gap between logo and nav/search: 24px minimum
```

Mobile navbar:

```txt
left/right padding: 16px
icon mark touch target: 44px
```

---

# 12. Minimum Size

## 12.1. Primary Logo

Minimum digital:

```txt
height: 40px
```

Recommended:

```txt
48px - 96px
```

## 12.2. Horizontal Logo

Minimum digital:

```txt
height: 28px
```

Recommended:

```txt
32px - 40px
```

## 12.3. Icon Mark

Minimum UI:

```txt
24px
```

Recommended:

```txt
32px - 48px
```

Favicon:

```txt
16px, 32px, 48px
```

## 12.4. App Icon Export Sizes

Required:

```txt
72x72
96x96
128x128
144x144
152x152
192x192
384x384
512x512
```

## 12.5. Favicon Export Sizes

Required:

```txt
16x16
32x32
48x48
```

Recommended files:

```txt
favicon.svg
favicon.ico
```

---

# 13. PWA App Icon

## 13.1. App Icon Direction

PWA app icon uses:

```txt
Icon Mark in white
Blue-only gradient background
Rounded square container
```

Recommended:

```txt
background: #0A3761 → #005BAC → #0B7FEA
icon: #FFFFFF
corner radius: platform-safe
```

## 13.2. Safe Area

App icon must have safe padding:

```txt
12% - 18%
```

Reason:

```txt
Different OS masks icons differently.
```

## 13.3. Maskable Icon

Provide maskable versions:

```txt
pwa-icon-maskable-192.png
pwa-icon-maskable-512.png
```

Manifest purpose:

```json
{
  "purpose": "maskable any"
}
```

## 13.4. App Icon Must Not

```txt
- include wordmark
- include tagline
- use tiny text
- use white background only without shape
- use dating gradient
- crop icon mark
```

---

# 14. Favicon

## 14.1. Favicon Direction

Favicon uses:

```txt
Icon Mark only
Blue monochrome or white-on-blue depending file
```

## 14.2. Favicon Sizes

Use:

```txt
16x16
32x32
48x48
```

Reference/export source:

```txt
128x128
```

## 14.3. Favicon Rules

```txt
- Must remain recognizable at 16px.
- No wordmark.
- No tagline.
- No complex gradient at 16px.
- Avoid thin strokes.
- Preserve icon mark center alignment.
```

---

# 15. Splash Screen

## 15.1. Light Splash

Use:

```txt
background: #FFFFFF or #F4FAFF
logo: Full Color / Blue Monochrome
```

Recommended layout:

```txt
centered Icon Mark
UEConnect wordmark below
tagline optional
```

## 15.2. Dark Splash

Use:

```txt
background: #124874 or blue-only gradient
logo: Inverse / White
```

Recommended:

```txt
centered icon mark
wordmark below
```

## 15.3. Splash Rules

```txt
- Keep centered.
- Avoid too much text.
- Do not use loading spinner inside logo.
- Do not animate logo aggressively.
- Use subtle fade/scale only if motion system allows.
```

---

# 16. Product Surface Usage

## 16.1. Desktop Navbar

Use:

```txt
Horizontal Logo
```

Recommended:

```txt
height: 32px
position: top-left
```

Rules:

```txt
- On white nav, use blue logo.
- Do not include tagline.
- Do not place notification badge on logo.
- Logo click navigates to /app/home.
```

## 16.2. Mobile Navbar

Use:

```txt
Icon Mark or compact Horizontal Logo
```

Recommended:

```txt
Icon Mark for very small headers
Horizontal Logo if header has enough width
```

Rules:

```txt
- Touch target minimum 44x44.
- Do not include tagline.
- Avoid cramming logo beside too many actions.
```

## 16.3. Auth Page

Use:

```txt
Primary Logo
```

Recommended:

```txt
centered at top
logo height: 48px - 64px
```

Auth page should feel:

```txt
trusted
official enough
friendly
not corporate cold
```

## 16.4. Verification Page

Use:

```txt
Horizontal Logo or Icon Mark
```

Recommended:

```txt
Logo top-left or centered.
Keep tone serious and clear.
```

Avoid:

```txt
playful oversized branding
animated logo
confetti
```

Người dùng đang xác thực danh tính, không cần màn xiếc thị giác.

## 16.5. Onboarding

Use:

```txt
Icon Mark
Primary Logo on first welcome screen only
```

Allowed:

```txt
soft blue background
gentle brand illustration
```

## 16.6. App Shell

Desktop:

```txt
Horizontal Logo in sidebar/header
```

Mobile:

```txt
Icon Mark in header if needed
```

Bottom navigation:

```txt
Do not use logo as a nav item.
```

## 16.7. Admin Dashboard

Use:

```txt
Horizontal Logo + Admin label
```

Example:

```txt
UEConnect Admin
```

Rules:

```txt
- Admin label must be separate from logo.
- Do not create a new admin logo.
- Do not recolor logo red/danger.
```

## 16.8. Community / Club Pages

UEConnect logo remains app-level.

Community logo appears only inside community header.

Hierarchy:

```txt
App identity: UEConnect
Scoped identity: Community / Club
```

Do not replace UEConnect logo with community logo in global navigation.

## 16.9. Mentor / Career Pathway Pages

Use UEConnect logo only through global shell.

Do not create:

```txt
MentorConnect
CareerConnect
ClubConnect
```

Một sản phẩm, một hệ logo. Không mở đa vũ trụ logo chỉ vì có nhiều module.

---

# 17. Color Variant Usage Matrix

| Background    | Recommended Logo Variant               | Notes                   |
| ------------- | -------------------------------------- | ----------------------- |
| White         | Full Color / Blue Monochrome           | Default                 |
| Light neutral | Full Color / Blue Monochrome           | Ensure contrast         |
| Light blue    | Blue Monochrome                        | Safe and clean          |
| HCMUE Blue    | Inverse / White                        | Required                |
| Blue gradient | Inverse / White                        | Required                |
| Dark navy     | Inverse / White                        | Required                |
| Image         | Inverse or Blue inside solid container | Avoid noisy backgrounds |
| Print B/W     | Neutral / Ink                          | Rare fallback           |

---

# 18. Logo Usage Examples

The logo system must include separate exported guideline images for:

```txt
01. Primary Logo
02. Horizontal Logo
03. Icon Mark
04. 4 Color Variants
05. App Icon
06. Favicon in different sizes
07. Logo on 4 backgrounds
08. Usage Examples
09. Minimum Size
```

## 18.1. Primary Logo Example

Should show:

```txt
Icon Mark + UEConnect + KẾT NỐI CỘNG ĐỒNG HCMUE
```

## 18.2. Horizontal Logo Example

Should show:

```txt
Icon Mark + UEConnect
```

## 18.3. Icon Mark Example

Should show:

```txt
Large centered icon mark
No text
```

## 18.4. 4 Color Variants Example

Should show:

```txt
1. Full Color / Default
2. Blue Monochrome
3. Inverse / White
4. Neutral / Ink
```

## 18.5. App Icon Example

Should show:

```txt
512x512
192x192
144x144
96x96
72x72
```

## 18.6. Favicon Example

Should show:

```txt
128x128 reference
32x32
16x16
```

## 18.7. Logo on Backgrounds Example

Should show:

```txt
1. On White
2. On Light Blue
3. On HCMUE Blue
4. On Blue-only Gradient
```

## 18.8. Usage Examples

Should show:

```txt
Desktop navbar
Mobile navbar
Auth page
Splash screen light
Splash screen dark
```

## 18.9. Minimum Size

Should show:

```txt
Primary Logo minimum: 40px height
Horizontal Logo minimum: 28px height
Icon Mark minimum: 24px height
```

---

# 19. Misuse Rules

## 19.1. Never Do

```txt
- Stretch logo horizontally.
- Stretch logo vertically.
- Rotate logo.
- Skew logo.
- Crop logo.
- Add random shadow.
- Add glow.
- Add outline stroke.
- Put logo on low-contrast background.
- Use red/pink/orange gradients.
- Change wordmark font randomly.
- Change product name spelling.
- Add emoji to logo.
- Add notification badge directly on logo.
- Put tagline inside app icon.
- Put wordmark inside favicon.
- Use old literal UE icon if it looks duplicated with wordmark.
- Combine with HCMUE official logo without permission.
```

## 19.2. Specific UEConnect Misuse

Wrong:

```txt
Icon mark clearly writes UE while wordmark also says UEConnect.
```

Why wrong:

```txt
Looks duplicated and too literal.
```

Wrong:

```txt
Logo uses Tinder-like warm gradient.
```

Why wrong:

```txt
Breaks HCMUE academic identity and creates dating-app association.
```

Wrong:

```txt
Logo on community page replaced by club logo.
```

Why wrong:

```txt
Breaks global app identity.
```

Wrong:

```txt
Favicon uses full wordmark.
```

Why wrong:

```txt
Unreadable at small sizes.
```

---

# 20. Co-branding with HCMUE

## 20.1. Important Rule

UEConnect is designed for HCMUE community, but it must not misuse official HCMUE identity.

Unless there is official approval, avoid claiming:

```txt
Ứng dụng chính thức của HCMUE
Cổng chính thức của trường
Được phát hành bởi HCMUE
```

## 20.2. Safe Wording

Allowed:

```txt
Dành cho cộng đồng HCMUE
Kết nối sinh viên HCMUE
Một sản phẩm hướng tới cộng đồng HCMUE
Case study cho cộng đồng HCMUE
```

## 20.3. HCMUE Logo Relationship

If official HCMUE logo is used with permission:

```txt
HCMUE logo and UEConnect logo must stay separate.
Do not merge marks.
Do not recolor HCMUE logo.
Respect HCMUE official brand rules.
```

Recommended co-brand layout:

```txt
[HCMUE logo] | [UEConnect logo]
```

Use divider and clear spacing.

---

# 21. Accessibility

## 21.1. Alt Text

Logo alt text:

```txt
UEConnect
```

If clickable home link:

```txt
UEConnect - về trang chủ
```

## 21.2. Icon-only Logo

Use:

```html
aria-label="UEConnect"
```

## 21.3. Decorative Logo

If repeated and decorative:

```html
aria-hidden="true"
```

But the main logo in navbar/auth should not be hidden.

## 21.4. Contrast

Logo must have enough contrast against background.

Rules:

```txt
- Blue logo on white/light background.
- White logo on dark/blue background.
- Do not use low-opacity logo as primary navigation.
```

## 21.5. Touch Target

Clickable logo must have:

```txt
minimum target: 44px x 44px
```

Visual mark can be smaller, but clickable area must remain large enough.

---

# 22. Implementation Specs

## 22.1. Asset Names

Recommended:

```txt
logo-primary.svg
logo-horizontal.svg
logo-mark.svg
logo-mark-blue.svg
logo-mark-inverse.svg
logo-primary-inverse.svg
logo-horizontal-inverse.svg
logo-neutral.svg
favicon.svg
favicon.ico
pwa-icon-72.png
pwa-icon-96.png
pwa-icon-128.png
pwa-icon-144.png
pwa-icon-152.png
pwa-icon-192.png
pwa-icon-384.png
pwa-icon-512.png
pwa-icon-maskable-192.png
pwa-icon-maskable-512.png
```

## 22.2. Folder Structure

Recommended:

```txt
public/
  brand/
    logo-primary.svg
    logo-horizontal.svg
    logo-mark.svg
    logo-mark-blue.svg
    logo-mark-inverse.svg
    logo-primary-inverse.svg
    logo-horizontal-inverse.svg
    logo-neutral.svg

  icons/
    favicon.svg
    favicon.ico
    pwa-icon-72.png
    pwa-icon-96.png
    pwa-icon-128.png
    pwa-icon-144.png
    pwa-icon-152.png
    pwa-icon-192.png
    pwa-icon-384.png
    pwa-icon-512.png
    pwa-icon-maskable-192.png
    pwa-icon-maskable-512.png
```

## 22.3. SVG Rules

Use SVG for UI logos.

Rules:

```txt
- Preserve viewBox.
- Optimize SVG.
- Remove editor metadata.
- Do not rasterize UI logo unless needed.
- Do not inline huge SVG repeatedly.
- Keep fill colors token-friendly where possible.
```

## 22.4. PNG Rules

Use PNG for:

```txt
- PWA app icons
- maskable icons
- social preview if needed
- legacy favicon support
```

## 22.5. Web App Manifest

Recommended:

```json
{
  "name": "UEConnect",
  "short_name": "UEConnect",
  "start_url": "/app/home",
  "display": "standalone",
  "theme_color": "#124874",
  "background_color": "#FFFFFF",
  "icons": [
    {
      "src": "/icons/pwa-icon-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/pwa-icon-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/pwa-icon-maskable-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable"
    },
    {
      "src": "/icons/pwa-icon-maskable-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
```

---

# 23. Logo Component

## 23.1. Component Name

Recommended component:

```txt
BrandLogo
```

## 23.2. Props

```txt
variant:
- primary
- horizontal
- mark

tone:
- default
- blue
- inverse
- neutral

size:
- xs
- sm
- md
- lg
- xl

asLink:
- true
- false

href:
- /
- /app/home
- /admin
```

## 23.3. Usage Examples

Desktop navbar:

```txt
<BrandLogo variant="horizontal" tone="blue" size="md" asLink href="/app/home" />
```

Mobile navbar:

```txt
<BrandLogo variant="mark" tone="blue" size="md" asLink href="/app/home" />
```

Auth page:

```txt
<BrandLogo variant="primary" tone="blue" size="lg" />
```

Dark splash:

```txt
<BrandLogo variant="primary" tone="inverse" size="xl" />
```

Admin:

```txt
<BrandLogo variant="horizontal" tone="blue" size="md" />
<span>Admin</span>
```

## 23.4. Component Rules

```txt
- Must preserve aspect ratio.
- Must expose accessible label.
- Must not allow arbitrary color outside approved tone.
- Must not allow arbitrary distortion.
- Must use correct asset for variant/tone.
```

---

# 24. Logo Size Tokens

Recommended tokens:

```txt
--logo-mark-xs: 16px;
--logo-mark-sm: 24px;
--logo-mark-md: 32px;
--logo-mark-lg: 48px;
--logo-mark-xl: 64px;

--logo-horizontal-sm-height: 28px;
--logo-horizontal-md-height: 32px;
--logo-horizontal-lg-height: 40px;

--logo-primary-md-height: 40px;
--logo-primary-lg-height: 64px;
--logo-primary-xl-height: 96px;
```

Usage:

| Context                   | Token                         |
| ------------------------- | ----------------------------- |
| Favicon                   | `--logo-mark-xs`              |
| Compact UI                | `--logo-mark-sm`              |
| Mobile header             | `--logo-mark-md`              |
| Desktop navbar            | `--logo-horizontal-md-height` |
| Auth page                 | `--logo-primary-lg-height`    |
| Splash / case study cover | `--logo-primary-xl-height`    |

---

# 25. QA Checklist

Before shipping any screen using the logo:

```txt
[ ] Correct logo variant is used.
[ ] Correct color variant is used.
[ ] Logo has enough contrast.
[ ] Logo is not stretched or distorted.
[ ] Logo has enough clear space.
[ ] Logo is not below minimum size.
[ ] Wordmark spelling is UEConnect.
[ ] Tagline is only used where allowed.
[ ] No forbidden color is used.
[ ] No red/pink/orange dating gradient is used.
[ ] Icon mark does not look like duplicated "UE".
[ ] Clickable logo has accessible label.
[ ] Clickable logo has 44px minimum touch target.
[ ] Favicon is readable at 16px and 32px.
[ ] PWA icon has safe padding.
[ ] Maskable icon does not crop.
[ ] Admin logo does not create a separate brand.
[ ] Co-branding does not misuse HCMUE identity.
```

---

# 26. AI Prompt Guidance

When using AI tools to generate logo-related assets, use:

```txt
Create a clean brand logo system for UEConnect, a trusted HCMUE student social platform. The logo mark should show two abstract connected people and an open book / knowledge shape, with a subtle educational and community feeling. Do not make the icon explicitly spell "UE". Use HCMUE Blue #124874, blue-only gradients, white/light backgrounds, and Be Vietnam Pro-inspired typography. The system must include primary logo, horizontal logo, icon mark, 4 color variants, app icon, favicon, logo on backgrounds, usage examples, and minimum size. Avoid dating-app aesthetics, red/pink/orange gradients, generic startup symbols, and overly literal letterforms.
```

For app icon:

```txt
Create a PWA app icon for UEConnect using the approved icon mark: two connected people and open book shape, white symbol on a blue-only gradient rounded square. Keep it simple, readable at 72x72 and 512x512, with safe padding for maskable app icon usage.
```

For favicon:

```txt
Create a favicon set for UEConnect using only the icon mark, blue monochrome, readable at 16x16, 32x32, and 48x48. No wordmark, no tagline, no tiny text.
```

---

# 27. Final Rule

Logo usage is part of the design system and product trust layer.

Before changing the logo, color, typography, spacing, or icon shape, update this file first and document:

```txt
what changed
why it changed
where it applies
which assets must be regenerated
which components are affected
which QA checklist items changed
```

Nếu không làm vậy, sau ba lần “chỉnh nhẹ”, logo sẽ biến thành một sinh vật lạ màu xanh nằm giữa app. Và rồi tất cả sẽ giả vờ đó là “brand evolution”.

```
```
