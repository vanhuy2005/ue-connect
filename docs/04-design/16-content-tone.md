---
title: "Content Tone"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UX Writing / Product / Frontend"
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
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
related:
  - "../03-product/feature-specs/authentication.md"
  - "../03-product/feature-specs/verification-identity.md"
  - "../03-product/feature-specs/profile-management.md"
  - "../03-product/feature-specs/onboarding.md"
  - "../03-product/feature-specs/settings-privacy.md"
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

# Content Tone

## 1. Purpose

Content Tone định nghĩa giọng văn, cách viết microcopy, nhãn UI, thông báo, lỗi, empty state, warning, success, admin copy, moderation copy và safety copy trong UEConnect.

Mục tiêu:

- Giúp toàn bộ app nói cùng một giọng.
- Làm UI tiếng Việt rõ ràng, dễ hiểu, thân thiện.
- Tránh văn phong quá hành chính, quá teen, quá bán hàng, hoặc quá “AI dịch máy”.
- Đảm bảo copy phù hợp với HCMUE, môi trường giáo dục, sinh viên, cựu sinh viên, cố vấn học tập và admin.
- Giữ UEConnect khác với dating app dù có Discovery, Greeting và Swipe-like interaction.
- Đảm bảo các flow nhạy cảm như xác thực, report, moderation, block, suspend có tone nghiêm túc, rõ ràng, không đùa cợt.
- Chuẩn bị sẵn nền tảng để localization Việt / Anh sau này.

Content không chỉ là chữ lấp chỗ trống. Nó là phần UI giải thích sản phẩm đang làm gì. Nếu viết tệ, app sẽ giống một cái form biết thở nhưng không biết giao tiếp.

---

## 2. Brand Voice

## 2.1. UEConnect Voice

UEConnect phải nói với user bằng giọng:

```txt
rõ ràng
ấm áp
đáng tin
trẻ trung vừa đủ
tôn trọng người dùng
có tinh thần giáo dục
không phán xét
không làm quá
````

UEConnect không nói bằng giọng:

```txt id="lijz6k"
quá hành chính
quá teen
quá bán hàng
quá máy móc
quá cute
giống dating app
giống mạng xã hội drama
giống chatbot vô hồn
```

## 2.2. Voice Attributes

| Attribute  | Meaning                                | Example                                         |
| ---------- | -------------------------------------- | ----------------------------------------------- |
| Trusted    | Có cảm giác an toàn, nghiêm túc vừa đủ | “Thông tin của bạn đang được xem xét.”          |
| Friendly   | Gần gũi, không lạnh                    | “Bạn có thể hoàn tất hồ sơ để bắt đầu kết nối.” |
| Academic   | Phù hợp môi trường trường học          | “Cộng đồng học tập”, “Mentor”, “Lộ trình”       |
| Clear      | Nói thẳng việc cần làm                 | “Vui lòng tải lên tối đa 3 tệp.”                |
| Calm       | Không gây hoảng                        | “Nội dung này hiện không khả dụng.”             |
| Respectful | Không đổ lỗi user                      | “Hãy kiểm tra lại thông tin.”                   |

## 2.3. Voice Balance

UEConnect nằm giữa:

```txt id="1oo0e0"
formal enough for trust
friendly enough for students
simple enough for mobile
serious enough for safety
```

Không được nghiêng quá mức về:

```txt id="wwtr41"
Cổng thông tin hành chính lạnh tanh
hoặc
mạng xã hội hẹn hò phấn khích quá đà
```

Một bên làm user buồn ngủ. Bên kia làm nhà trường nhíu mày. Cả hai đều không đáng tự hào.

---

# 3. Language Direction

## 3.1. Primary UI Language

Primary UI language:

```txt id="2804f0"
Tiếng Việt
```

Codebase naming:

```txt id="mqbqh3"
English only
```

Localization plan:

```txt id="7fzy2o"
Vietnamese first
English-ready
```

## 3.2. Vietnamese Style

UI tiếng Việt nên:

```txt id="ypy41l"
- ngắn
- rõ
- tự nhiên
- tránh dịch word-by-word từ tiếng Anh
- dùng từ quen thuộc với sinh viên
- dùng thuật ngữ nhất quán
```

## 3.3. English Borrowed Terms

Có thể dùng một số thuật ngữ tiếng Anh nếu đã quen trong sản phẩm:

```txt id="w4ij8q"
Mentor
Admin
PWA
Profile
Feed
```

Nhưng trong UI chính, ưu tiên tiếng Việt nếu dễ hiểu:

| English        | Preferred Vietnamese |
| -------------- | -------------------- |
| Home Feed      | Bảng tin             |
| Profile        | Hồ sơ                |
| Discovery      | Khám phá             |
| Message        | Tin nhắn             |
| Notification   | Thông báo            |
| Community      | Cộng đồng            |
| Report         | Báo cáo              |
| Settings       | Cài đặt              |
| Privacy        | Quyền riêng tư       |
| Verification   | Xác thực             |
| Onboarding     | Thiết lập ban đầu    |
| Career Pathway | Lộ trình định hướng  |
| Resource       | Tài nguyên           |

## 3.4. Do Not Mix Languages Randomly

Bad:

```txt id="7mtypz"
Bạn đã submit request thành công.
Click để view profile.
Message đã sent.
```

Good:

```txt id="i6ej1h"
Bạn đã gửi yêu cầu thành công.
Nhấn để xem hồ sơ.
Tin nhắn đã được gửi.
```

Trừ khi là tên feature chính hoặc thuật ngữ kỹ thuật cần giữ, UI phải nhất quán tiếng Việt. App không cần nói chuyện như commit message.

---

# 4. Tone by Context

## 4.1. General Product Tone

Use:

```txt id="ctv81w"
friendly
clear
student-first
calm
```

Example:

```txt id="3ocppz"
Kết nối với sinh viên, cựu sinh viên và cố vấn trong cộng đồng HCMUE.
```

Avoid:

```txt id="k7j5m6"
Khám phá những kết nối định mệnh đang chờ bạn!
```

Lý do:

```txt id="i0i3ps"
Sounds dating-like.
```

## 4.2. Auth Tone

Auth tone must be:

```txt id="vmg8fc"
trusted
simple
secure
welcoming
```

Example:

```txt id="upmyou"
Đăng nhập bằng email HCMUE để tiếp tục.
```

Avoid:

```txt id="42x82t"
Vào ngay thế giới kết nối siêu đỉnh của bạn!
```

## 4.3. Verification Tone

Verification tone must be:

```txt id="a60h7q"
serious
supportive
specific
non-judgmental
```

Example:

```txt id="q5bz8i"
Tải lên minh chứng để UEConnect xác nhận bạn thuộc cộng đồng HCMUE.
```

Avoid:

```txt id="j0uv2d"
Chứng minh bạn là người thật đi nào!
```

Người dùng đang xác thực danh tính, không phải chơi CAPTCHA có cảm xúc.

## 4.4. Onboarding Tone

Onboarding tone can be:

```txt id="1fhg0r"
warm
encouraging
clear
lightly energetic
```

Example:

```txt id="tf5wqs"
Hoàn tất vài thông tin cơ bản để mọi người hiểu bạn hơn.
```

Avoid:

```txt id="4dnv99"
Hãy trở thành phiên bản tuyệt vời nhất của chính bạn!
```

Câu đó đã bị ngành self-help dùng đến mòn cả vũ trụ.

## 4.5. Discovery Tone

Discovery tone must be:

```txt id="bk8ith"
friendly
connection-focused
non-romantic
respectful
```

Use:

```txt id="61jzvo"
Khám phá UEers phù hợp
Gửi lời chào
Bỏ qua
Xem hồ sơ
```

Avoid:

```txt id="dnqw9b"
Match
Crush
Quẹt phải
Người ấy
Tìm một nửa
```

UEConnect có kết nối, không có “định mệnh”. Nhà trường cảm ơn.

## 4.6. Messaging Tone

Messaging tone must be:

```txt id="g0s7fl"
natural
direct
safe
low-friction
```

Example:

```txt id="w06up6"
Bạn chỉ có thể nhắn tin sau khi hai bên đã kết nối.
```

Avoid:

```txt id="7jfbjx"
Người này chưa mở lòng với bạn.
```

Không ai cần thơ tình trong permission state.

## 4.7. Mentor Tone

Mentor tone must be:

```txt id="b853og"
respectful
goal-oriented
supportive
professional
```

Example:

```txt id="kib99e"
Gửi câu hỏi rõ ràng để mentor hiểu bạn cần hỗ trợ điều gì.
```

Avoid:

```txt id="ztdvgz"
Hỏi gì cũng được, mentor lo hết!
```

Mentor là người hỗ trợ, không phải tổng đài vũ trụ.

## 4.8. Community Tone

Community tone must be:

```txt id="0or6f9"
inclusive
campus-oriented
clear
moderated
```

Example:

```txt id="db5zsd"
Tham gia cộng đồng để theo dõi bài viết, tài nguyên và trao đổi với thành viên.
```

Avoid:

```txt id="4gewp5"
Vào group hóng chuyện ngay!
```

Chúng ta đang xây campus platform, không phải hạ tầng drama.

## 4.9. Career Pathway Tone

Career Pathway tone must be:

```txt id="km49wb"
guiding
academic
practical
not overpromising
```

Example:

```txt id="e9af70"
Lộ trình này giúp bạn hiểu các hướng phát triển phù hợp với ngành học và mục tiêu của mình.
```

Avoid:

```txt id="5umkmh"
Theo lộ trình này là có việc ngay.
```

Không hứa kết quả nghề nghiệp tuyệt đối. Đó là cách sản phẩm tử tế tránh trở thành quảng cáo rẻ tiền.

## 4.10. Safety / Report Tone

Safety tone must be:

```txt id="3x3r6e"
calm
serious
clear
non-blaming
protective
```

Example:

```txt id="eedxqk"
Báo cáo của bạn đã được gửi. UEConnect sẽ xem xét nội dung này.
```

Avoid:

```txt id="hf4voy"
Đã tố cáo thành công!
```

Báo cáo không phải thành tích săn boss.

## 4.11. Moderation Tone

Moderation tone must be:

```txt id="sk3f3a"
neutral
specific
policy-based
non-hostile
```

Example:

```txt id="pgfph6"
Nội dung này đã bị ẩn vì có thể vi phạm quy định cộng đồng.
```

Avoid:

```txt id="w3sqrf"
Bạn đã đăng nội dung xấu.
```

Không phán xét con người. Chỉ mô tả hành động và chính sách.

## 4.12. Admin Tone

Admin tone must be:

```txt id="d12f5m"
precise
operational
dense but readable
audit-friendly
```

Example:

```txt id="ydr7ro"
Tạm khóa tài khoản này sẽ ngăn người dùng đăng bài, gửi tin nhắn và tham gia cộng đồng.
```

Avoid:

```txt id="kr33sq"
Bạn chắc chưa?
```

Admin cần hậu quả rõ, không cần câu hỏi triết học.

---

# 5. Writing Principles

## 5.1. Be Clear Before Being Clever

Good:

```txt id="zvti2i"
Không thể gửi tin nhắn. Vui lòng thử lại.
```

Bad:

```txt id="l45xfe"
Tin nhắn đi lạc đâu đó rồi.
```

Nói vui trong lỗi hệ thống là kiểu hài người dùng không thuê bạn diễn.

## 5.2. Be Specific

Good:

```txt id="vq0qrv"
Mật khẩu phải có ít nhất 8 ký tự.
```

Bad:

```txt id="g5osfg"
Thông tin không hợp lệ.
```

## 5.3. Use Active Voice

Good:

```txt id="xfgogc"
Bạn đã gửi yêu cầu mentor.
```

Bad:

```txt id="xdja8p"
Yêu cầu mentor đã được gửi bởi bạn.
```

## 5.4. Start With What Happened

Good:

```txt id="zifp9i"
Không tải được cộng đồng. Vui lòng thử lại.
```

Bad:

```txt id="nas2yv"
Vui lòng thử lại vì cộng đồng không tải được.
```

## 5.5. Tell The Next Step

Good:

```txt id="5j6az3"
Bạn cần hoàn tất hồ sơ để sử dụng Discovery.
```

Bad:

```txt id="u84f72"
Hồ sơ chưa hoàn tất.
```

## 5.6. Do Not Blame The User

Good:

```txt id="spj0a3"
Hãy kiểm tra lại email HCMUE của bạn.
```

Bad:

```txt id="54q3vb"
Bạn nhập sai email.
```

## 5.7. Keep Copy Short On Mobile

Recommended:

```txt id="1qq00b"
Title: 1 line if possible.
Description: 1-2 short sentences.
Button: 1-4 words.
```

## 5.8. No Fake Urgency

Avoid:

```txt id="ztp274"
Nhanh tay!
Đừng bỏ lỡ!
Cơ hội cuối cùng!
```

UEConnect không bán flash sale nồi chiên không dầu.

---

# 6. Terminology System

## 6.1. Product Terms

| Concept        | Vietnamese UI Term  | Notes                         |
| -------------- | ------------------- | ----------------------------- |
| UEConnect      | UEConnect           | Brand name                    |
| User           | Người dùng / UEer   | Use UEer in friendly contexts |
| Student        | Sinh viên           | Role                          |
| Alumni         | Cựu sinh viên       | Role                          |
| Advisor        | Cố vấn học tập      | Role                          |
| Admin          | Admin               | System role                   |
| Mentor         | Mentor              | Keep English                  |
| Profile        | Hồ sơ               | UI                            |
| Public Profile | Hồ sơ công khai     | Privacy                       |
| Discovery      | Khám phá            | Feature                       |
| Greeting       | Lời chào            | Connection request            |
| Connection     | Kết nối             | Relationship                  |
| Message        | Tin nhắn            | Messaging                     |
| Notification   | Thông báo           | In-app/push                   |
| Community      | Cộng đồng           | Community                     |
| Club           | CLB                 | Club                          |
| Resource       | Tài nguyên          | Files/links                   |
| Feed           | Bảng tin            | Home feed                     |
| Post           | Bài viết            | Feed content                  |
| Comment        | Bình luận           | Comment                       |
| Report         | Báo cáo             | Safety                        |
| Moderation     | Kiểm duyệt          | Safety/admin                  |
| Verification   | Xác thực            | Identity                      |
| Evidence       | Minh chứng          | Verification                  |
| Career Pathway | Lộ trình định hướng | Career guidance               |
| Settings       | Cài đặt             | Settings                      |
| Privacy        | Quyền riêng tư      | Settings                      |

## 6.2. Role Terms

Use consistent role labels:

```txt id="v1huen"
Sinh viên
Cựu sinh viên
Cố vấn học tập
Mentor
Quản lý CLB
Admin
Moderator
```

Do not use inconsistent:

```txt id="e7bd5v"
Alumina
Alumni user
Cựu học sinh
Người hướng dẫn
Người quản trị viên hệ thống cấp cao tối thượng
```

Lưu ý chính tả:

```txt id="xtcvks"
alumni = cựu sinh viên
không phải alumina
```

“Alumina” là nhôm oxit. Không ai muốn mentor là vật liệu gốm.

## 6.3. Status Terms

| Status                   | Vietnamese Copy      |
| ------------------------ | -------------------- |
| `active`                 | Đang hoạt động       |
| `pending`                | Đang chờ             |
| `pending_review`         | Đang chờ duyệt       |
| `under_review`           | Đang xem xét         |
| `needs_more_information` | Cần bổ sung          |
| `approved`               | Đã duyệt             |
| `rejected`               | Bị từ chối           |
| `verified`               | Đã xác thực          |
| `not_verified`           | Chưa xác thực        |
| `profile_incomplete`     | Hồ sơ chưa hoàn tất  |
| `restricted`             | Bị hạn chế           |
| `suspended`              | Tạm khóa             |
| `banned`                 | Bị cấm               |
| `archived`               | Đã lưu trữ           |
| `deleted`                | Đã xóa               |
| `hidden_by_moderation`   | Đã ẩn bởi kiểm duyệt |
| `removed_by_moderation`  | Đã gỡ bởi kiểm duyệt |
| `expired`                | Đã hết hạn           |
| `draft`                  | Bản nháp             |

## 6.4. Action Terms

| Action            | Vietnamese Copy |
| ----------------- | --------------- |
| Submit            | Gửi             |
| Save              | Lưu             |
| Continue          | Tiếp tục        |
| Cancel            | Hủy             |
| Back              | Quay lại        |
| Retry             | Thử lại         |
| Delete            | Xóa             |
| Remove            | Gỡ              |
| Hide              | Ẩn              |
| Restore           | Khôi phục       |
| Report            | Báo cáo         |
| Block             | Chặn            |
| Unblock           | Bỏ chặn         |
| Suspend           | Tạm khóa        |
| Ban               | Cấm             |
| Approve           | Duyệt           |
| Reject            | Từ chối         |
| Request more info | Yêu cầu bổ sung |
| Join              | Tham gia        |
| Leave             | Rời khỏi        |
| Accept            | Chấp nhận       |
| Decline           | Từ chối         |
| Mark as read      | Đánh dấu đã đọc |
| Clear             | Xóa             |
| Search            | Tìm kiếm        |
| Filter            | Lọc             |

---

# 7. Microcopy Patterns

## 7.1. Button Copy

Button copy should be:

```txt id="3qezjy"
verb-first
short
specific
```

Good:

```txt id="q7k9wz"
Gửi lời chào
Lưu thay đổi
Gửi yêu cầu
Tham gia cộng đồng
Mở cuộc trò chuyện
```

Bad:

```txt id="y8e0nl"
OK
Submit
Click here
Done
Go
```

## 7.2. Primary CTA Copy

Use strong action:

```txt id="v0oi3j"
Tiếp tục
Hoàn tất hồ sơ
Gửi xác thực
Bắt đầu khám phá
```

Avoid vague:

```txt id="u8fl5o"
Xong
Được
Ok nha
```

## 7.3. Secondary CTA Copy

Use:

```txt id="4gayud"
Để sau
Quay lại
Hủy
Xem thêm
Tìm hiểu thêm
```

## 7.4. Danger CTA Copy

Must state consequence:

Good:

```txt id="mxyqe8"
Xóa bài viết
Chặn người dùng
Tạm khóa tài khoản
Gỡ nội dung
```

Bad:

```txt id="8wmu2z"
Xác nhận
Có
OK
```

Danger confirmation must never hide the actual action behind generic labels. Vì đó là cách người ta xóa nhầm và trách cả vũ trụ.

---

# 8. Titles & Headings

## 8.1. Page Titles

Page titles should be short and clear.

Examples:

```txt id="fg6dpk"
Bảng tin
Khám phá
Tin nhắn
Cộng đồng
Mentor
Lộ trình định hướng
Thông báo
Cài đặt
```

## 8.2. Section Titles

Examples:

```txt id="1b5xrg"
Thông tin cơ bản
Quyền riêng tư
Yêu cầu đang chờ
Cộng đồng của bạn
Tài nguyên mới
Mentor phù hợp
```

## 8.3. Modal Titles

Modal title must describe action.

Good:

```txt id="sro9tr"
Báo cáo bài viết
Chặn người dùng
Tạm khóa cộng đồng
Từ chối xác thực
```

Bad:

```txt id="37s1mh"
Bạn chắc chưa?
Thông báo
Lưu ý
```

## 8.4. Empty State Titles

Examples:

```txt id="5x33tz"
Chưa có thông báo nào
Không tìm thấy kết quả phù hợp
Bạn chưa có tin nhắn
Cộng đồng này chưa có tài nguyên
```

---

# 9. Helper Text

## 9.1. Purpose

Helper text giúp user hiểu cách nhập đúng thông tin.

Use for:

```txt id="av2q4w"
email format
password policy
file upload
mentor question
profile visibility
community resource copyright
report description
```

## 9.2. Good Helper Text

Email:

```txt id="qnx55w"
Chỉ sử dụng email thuộc miền hcmue.edu.vn.
```

Password:

```txt id="q5m4bg"
Mật khẩu cần có ít nhất 8 ký tự.
```

Evidence upload:

```txt id="ukvd0r"
Bạn có thể tải tối đa 3 tệp, mỗi tệp không quá 5MB.
```

Mentor question:

```txt id="9qcrdk"
Mô tả ngắn gọn điều bạn cần mentor hỗ trợ.
```

Report description:

```txt id="n24nlt"
Bạn có thể mô tả thêm để admin hiểu rõ vấn đề. Phần này không bắt buộc.
```

## 9.3. Helper Text Rule

Helper text should prevent errors before they happen.

Nếu helper text chỉ lặp lại label, bỏ. UI không cần nói hai lần cùng một điều, trừ khi nó là người trong cuộc họp.

---

# 10. Validation & Error Copy

## 10.1. Field Error Pattern

Pattern:

```txt id="3wsfkd"
[Field] + [problem] + [how to fix]
```

Examples:

```txt id="3pzfwt"
Email phải thuộc miền hcmue.edu.vn.
Mật khẩu phải có ít nhất 8 ký tự.
Vui lòng nhập tên hiển thị.
Bạn chỉ có thể tải tối đa 3 tệp.
Tệp này vượt quá giới hạn 5MB.
Định dạng tệp không được hỗ trợ.
```

## 10.2. Form Error Pattern

Pattern:

```txt id="m2krmg"
Không thể [action]. [Reason/next step].
```

Examples:

```txt id="o00rph"
Không thể gửi xác thực. Vui lòng kiểm tra lại các trường bắt buộc.
Không thể lưu hồ sơ. Vui lòng thử lại.
Không thể gửi lời chào. Người dùng này hiện không khả dụng.
```

## 10.3. Network Error

Use:

```txt id="91ib1e"
Không tải được dữ liệu. Kết nối có thể đang không ổn định. Vui lòng thử lại.
```

## 10.4. Server Error

Use:

```txt id="3cmcql"
Đã có lỗi xảy ra. Vui lòng thử lại sau.
```

But prefer more specific if possible.

## 10.5. Permission Error

Use:

```txt id="xp2c23"
Bạn không có quyền xem nội dung này.
```

or:

```txt id="d265rw"
Bạn cần xác thực tài khoản để sử dụng tính năng này.
```

## 10.6. Conflict Error

Use:

```txt id="3t9ewe"
Trạng thái đã thay đổi. Vui lòng tải lại để tiếp tục.
```

## 10.7. Do Not Use

```txt id="9t578q"
Invalid.
Error.
Failed.
Something went wrong.
Oops.
Lỗi hệ thống 500.
SQLSTATE...
```

“Oops” là tiếng của app không chịu trách nhiệm. Đừng dùng.

---

# 11. Success Copy

## 11.1. Pattern

Pattern:

```txt id="m55lcn"
Đã [action].
```

or:

```txt id="ibvayq"
[Object] đã được [action].
```

Examples:

```txt id="7legtn"
Đã lưu thay đổi.
Đã gửi lời chào.
Đã gửi yêu cầu mentor.
Đã tham gia cộng đồng.
Bài viết đã được đăng.
Tài nguyên đã được gửi và đang chờ duyệt.
```

## 11.2. Major Success

For major flows, include next step.

Verification submitted:

```txt id="f1sc6c"
Yêu cầu xác thực đã được gửi.
Admin sẽ xem xét thông tin của bạn. Bạn sẽ nhận thông báo khi có kết quả.
```

Profile completed:

```txt id="we5ndr"
Hồ sơ đã hoàn tất.
Bây giờ bạn có thể bắt đầu khám phá và kết nối với cộng đồng HCMUE.
```

Mentor accepted:

```txt id="7g2vha"
Yêu cầu mentor đã được chấp nhận.
Bạn có thể bắt đầu trao đổi trong cuộc trò chuyện.
```

## 11.3. Do Not Over-celebrate

Avoid:

```txt id="k652vz"
Tuyệt vời ông mặt trời!
Bạn quá đỉnh!
Bùng nổ kết nối!
```

Một app trường đại học không cần hành xử như MC team building.

---

# 12. Empty State Copy

## 12.1. Pattern

Pattern:

```txt id="gog6e1"
Title: [Không có gì?]
Description: [Vì sao / khi nào sẽ có?]
Action: [User có thể làm gì?]
```

## 12.2. Examples

Notifications:

```txt id="wiic56"
Title: Chưa có thông báo nào
Description: Khi có cập nhật mới, thông báo sẽ xuất hiện tại đây.
```

Messages:

```txt id="9j2tpa"
Title: Bạn chưa có tin nhắn
Description: Khi bạn kết nối với người khác, cuộc trò chuyện sẽ xuất hiện tại đây.
```

Community resources:

```txt id="6m5ydr"
Title: Chưa có tài nguyên nào
Description: Khi tài nguyên được duyệt, chúng sẽ xuất hiện tại đây.
Action: Gửi tài nguyên
```

Search:

```txt id="0mj7qn"
Title: Không tìm thấy kết quả phù hợp
Description: Hãy thử từ khóa khác hoặc bỏ bớt bộ lọc.
```

Mentor:

```txt id="00yfx9"
Title: Chưa có mentor phù hợp
Description: Hãy thử chủ đề khác hoặc quay lại sau khi có thêm mentor.
```

Feed:

```txt id="078pm7"
Title: Chưa có bài viết nào
Description: Khi UEers bắt đầu chia sẻ, bài viết sẽ xuất hiện tại đây.
```

## 12.3. Empty State Rules

```txt id="t6swvw"
- Do not make user feel at fault.
- Do not use jokes in serious empty states.
- Include CTA if useful.
- Keep it short.
```

---

# 13. Loading Copy

## 13.1. Common Loading Text

Use:

```txt id="y99cev"
Đang tải...
Đang gửi...
Đang lưu...
Đang xử lý...
Đang kết nối lại...
Đang upload...
```

More specific:

```txt id="vkc12k"
Đang tải bài viết...
Đang tải cộng đồng...
Đang tải tin nhắn...
Đang gửi lời chào...
Đang gửi yêu cầu mentor...
Đang lưu cài đặt...
```

## 13.2. Rules

```txt id="jyh5id"
- Use specific loading copy for longer tasks.
- Button loading can use short text.
- Do not show fake progress.
- If loading takes too long, show retry error.
```

---

# 14. Permission & Locked Copy

## 14.1. Unverified User

```txt id="ohd3sq"
Title: Bạn cần xác thực tài khoản
Description: Hoàn tất xác thực HCMUE để sử dụng tính năng này.
Action: Đi đến xác thực
```

## 14.2. Profile Incomplete

```txt id="3qcx5i"
Title: Hồ sơ chưa hoàn tất
Description: Hoàn tất hồ sơ để mọi người hiểu bạn hơn và sử dụng đầy đủ tính năng.
Action: Hoàn tất hồ sơ
```

## 14.3. Not Connected

```txt id="09qfqu"
Title: Chưa thể nhắn tin
Description: Bạn chỉ có thể nhắn tin sau khi hai bên đã kết nối.
```

## 14.4. Private Community

```txt id="87s0cr"
Title: Cộng đồng riêng tư
Description: Bạn cần được duyệt làm thành viên để xem nội dung này.
Action: Gửi yêu cầu tham gia
```

## 14.5. Admin Permission

```txt id="2tt4hu"
Title: Bạn không có quyền truy cập
Description: Tài khoản của bạn không có quyền thực hiện thao tác này.
```

## 14.6. Restricted Account

```txt id="yu40bh"
Title: Tài khoản đang bị hạn chế
Description: Một số tính năng hiện không khả dụng do trạng thái tài khoản của bạn.
```

## 14.7. Rules

```txt id="xrpsq6"
- Explain why locked.
- Give next action if available.
- Do not reveal sensitive hidden content.
- Do not show locked state for blocked users if target should be hidden.
```

---

# 15. Notification Copy

## 15.1. Notification Principles

Notification copy must be:

```txt id="8exohy"
short
specific
safe
non-sensitive
action-oriented
```

Do not include:

```txt id="h31bm5"
raw report details
full message body if sensitive
verification evidence info
private notes
admin internal reason
```

## 15.2. Verification Notifications

Approved:

```txt id="3g56vj"
Tài khoản của bạn đã được xác thực.
```

Rejected:

```txt id="zmk55d"
Yêu cầu xác thực của bạn chưa được duyệt.
```

Need more info:

```txt id="aluvt8"
Bạn cần bổ sung thông tin xác thực.
```

## 15.3. Greeting Notifications

Received:

```txt id="08d2ya"
Bạn nhận được một lời chào mới.
```

Accepted:

```txt id="4kgakp"
Lời chào của bạn đã được chấp nhận.
```

Declined:

```txt id="rsdxo8"
Lời chào của bạn đã được phản hồi.
```

Avoid:

```txt id="x7hiwy"
Người ấy đã chấp nhận bạn!
```

Rất tiếc, không phải phim thanh xuân.

## 15.4. Message Notifications

In-app:

```txt id="suw0ih"
Bạn có tin nhắn mới.
```

With safe preview:

```txt id="f1u28d"
[Name] đã gửi cho bạn một tin nhắn.
```

Avoid raw sensitive preview if context is unsafe.

## 15.5. Mentor Notifications

Request update:

```txt id="knvomd"
Yêu cầu mentor của bạn đã được cập nhật.
```

Accepted:

```txt id="rj2l4q"
Mentor đã chấp nhận yêu cầu của bạn.
```

Need more info:

```txt id="yn2kje"
Mentor cần bạn bổ sung thêm thông tin.
```

## 15.6. Community Notifications

Join approved:

```txt id="7qs541"
Yêu cầu tham gia cộng đồng của bạn đã được chấp nhận.
```

Join rejected:

```txt id="m8ptx2"
Yêu cầu tham gia cộng đồng của bạn chưa được chấp nhận.
```

Community suspended:

```txt id="cswfj0"
Một cộng đồng bạn tham gia hiện đang bị tạm khóa.
```

## 15.7. Moderation Notifications

Content hidden:

```txt id="sj1h2p"
Một nội dung của bạn đã bị ẩn để xem xét.
```

Content removed:

```txt id="crfkum"
Một nội dung của bạn đã bị gỡ do vi phạm quy định cộng đồng.
```

Warned:

```txt id="lr4w06"
Tài khoản của bạn đã nhận một cảnh báo.
```

Suspended:

```txt id="r4w92l"
Tài khoản của bạn hiện đang bị tạm khóa.
```

---

# 16. Browser Push Copy

## 16.1. Push Principles

Browser push must be:

```txt id="a8wzjw"
short
privacy-safe
useful
not noisy
```

Do not include sensitive detail.

## 16.2. Push Examples

Message:

```txt id="5lo3s1"
Bạn có tin nhắn mới trên UEConnect.
```

Greeting:

```txt id="db4uxf"
Bạn nhận được một lời chào mới.
```

Mentor:

```txt id="6zr0j7"
Yêu cầu mentor của bạn có cập nhật mới.
```

Verification:

```txt id="k1mdr7"
Yêu cầu xác thực của bạn đã được cập nhật.
```

Community:

```txt id="aq5e2a"
Có cập nhật mới trong cộng đồng của bạn.
```

## 16.3. Push Permission Prompt

Soft prompt:

```txt id="xxdrnb"
Bật thông báo trình duyệt để không bỏ lỡ lời chào, tin nhắn và cập nhật quan trọng.
```

CTA:

```txt id="mr3dv1"
Bật thông báo
Để sau
```

Denied:

```txt id="knb6nh"
Trình duyệt đang chặn thông báo. Bạn có thể bật lại trong cài đặt trình duyệt.
```

---

# 17. Safety / Report Copy

## 17.1. Report Modal

Title:

```txt id="byki6i"
Báo cáo nội dung
```

Description:

```txt id="jvne4q"
Chọn lý do phù hợp nhất. Báo cáo của bạn sẽ được gửi đến admin để xem xét.
```

Reason label:

```txt id="k7fmtn"
Lý do báo cáo
```

Optional description:

```txt id="m7dj0r"
Mô tả thêm
```

Helper:

```txt id="mfgdvc"
Bạn có thể mô tả thêm để admin hiểu rõ vấn đề. Phần này không bắt buộc.
```

Submit:

```txt id="b7ced3"
Gửi báo cáo
```

## 17.2. Report Reasons

Use:

```txt id="wj8t3n"
Spam
Quấy rối
Giả mạo danh tính
Nội dung hẹn hò / tình dục
Vi phạm bản quyền
Lộ thông tin cá nhân
Lừa đảo
Ngôn từ công kích
Nội dung chính trị nhạy cảm
Khác
```

## 17.3. Report Success

```txt id="edjb75"
Báo cáo của bạn đã được gửi. UEConnect sẽ xem xét nội dung này.
```

If auto-block:

```txt id="vvsijv"
Bạn sẽ không còn nhìn thấy nội dung hoặc tương tác từ người này.
```

## 17.4. Duplicate Report

```txt id="2uec8y"
Bạn đã báo cáo nội dung này. Vui lòng chờ phản hồi xử lý.
```

## 17.5. Block Copy

Block modal title:

```txt id="qbx25q"
Chặn người dùng này?
```

Description:

```txt id="so1ggd"
Sau khi chặn, hai bên sẽ không thể nhắn tin, gửi lời chào hoặc nhìn thấy nhau trong Khám phá.
```

CTA:

```txt id="6vm38w"
Chặn người dùng
```

Cancel:

```txt id="23d8jl"
Hủy
```

---

# 18. Moderation Copy

## 18.1. Hidden Content Placeholder

```txt id="5wqseu"
Nội dung này đang bị ẩn để xem xét.
```

## 18.2. Removed Content Placeholder

```txt id="vsx0pq"
Nội dung này đã bị gỡ do vi phạm quy định cộng đồng.
```

## 18.3. Community Suspended

```txt id="wd2r87"
Cộng đồng này hiện đang bị tạm khóa.
Một số hoạt động như đăng bài, trò chuyện và tham gia mới hiện không khả dụng.
```

## 18.4. Account Suspended

```txt id="nbjcp3"
Tài khoản của bạn hiện đang bị tạm khóa.
Một số tính năng sẽ không khả dụng trong thời gian này.
```

## 18.5. Appeal

Title:

```txt id="67gspl"
Gửi kháng nghị
```

Description:

```txt id="2rufli"
Nếu bạn cho rằng quyết định này chưa chính xác, bạn có thể gửi kháng nghị để admin xem xét lại.
```

CTA:

```txt id="w2wkg5"
Gửi kháng nghị
```

## 18.6. Admin Moderation Action Copy

Dismiss:

```txt id="9o1pse"
Bỏ qua báo cáo
```

Hide:

```txt id="u7id3t"
Ẩn nội dung
```

Delete:

```txt id="k0m9al"
Gỡ nội dung
```

Restore:

```txt id="pmjbac"
Khôi phục nội dung
```

Warn:

```txt id="qot7wf"
Gửi cảnh báo
```

Suspend:

```txt id="5sv22z"
Tạm khóa tài khoản
```

Ban:

```txt id="njtynl"
Cấm tài khoản
```

Reason label:

```txt id="d03mzu"
Lý do xử lý
```

Helper:

```txt id="mxnc13"
Lý do này sẽ được lưu vào lịch sử xử lý và audit log.
```

---

# 19. Verification Copy

## 19.1. Verification Entry

Title:

```txt id="2futmx"
Xác thực tài khoản HCMUE
```

Description:

```txt id="p3typs"
UEConnect cần xác nhận bạn thuộc cộng đồng HCMUE trước khi mở đầy đủ tính năng.
```

CTA:

```txt id="w9ln65"
Bắt đầu xác thực
```

## 19.2. Email Verification

```txt id="bzbtfl"
Sử dụng email thuộc miền hcmue.edu.vn để xác thực nhanh hơn.
```

## 19.3. Evidence Upload

Title:

```txt id="q3wb05"
Tải lên minh chứng
```

Description:

```txt id="zwq3jz"
Bạn có thể sử dụng thẻ sinh viên, giấy nhập học, bảng điểm, bằng tốt nghiệp hoặc minh chứng pháp lý phù hợp.
```

Helper:

```txt id="pqty4y"
Tối đa 3 tệp, mỗi tệp không quá 5MB. Hỗ trợ JPG, PNG, WEBP và PDF.
```

File note label:

```txt id="hcnhk6"
Ghi chú cho minh chứng
```

File note helper:

```txt id="ro3xyr"
Mô tả ngắn gọn loại minh chứng bạn đã tải lên.
```

## 19.4. Pending Review

```txt id="ucfp40"
Yêu cầu xác thực của bạn đang chờ duyệt.
Bạn sẽ nhận thông báo khi có kết quả.
```

## 19.5. Need More Information

```txt id="e2pbv1"
Bạn cần bổ sung thông tin xác thực.
Vui lòng xem hướng dẫn từ admin và gửi lại minh chứng phù hợp.
```

## 19.6. Rejected

```txt id="5d9d6h"
Yêu cầu xác thực của bạn chưa được duyệt.
Vui lòng xem lý do và gửi lại thông tin nếu cần.
```

## 19.7. Approved

```txt id="w7uruz"
Tài khoản của bạn đã được xác thực.
Hãy hoàn tất hồ sơ để bắt đầu sử dụng UEConnect.
```

---

# 20. Profile Copy

## 20.1. Profile Setup

Title:

```txt id="pfuy1f"
Hoàn tất hồ sơ của bạn
```

Description:

```txt id="18kw8y"
Hồ sơ giúp UEers hiểu bạn là ai, đang học gì và muốn kết nối vì điều gì.
```

## 20.2. Required Avatar

```txt id="4jcy47"
Ảnh đại diện là bắt buộc sau khi tài khoản được xác thực.
```

## 20.3. Bio Helper

```txt id="hxie6d"
Viết ngắn gọn về bạn, ngành học, sở thích hoặc điều bạn muốn chia sẻ với cộng đồng.
```

## 20.4. Privacy Helper

```txt id="82v85u"
Bạn có thể kiểm soát thông tin nào được hiển thị trên hồ sơ công khai.
```

## 20.5. Discovery Visibility

```txt id="fgu0k9"
Cho phép người khác nhìn thấy hồ sơ của bạn trong Khám phá.
```

---

# 21. Discovery / Greeting Copy

## 21.1. Discovery Page

Title:

```txt id="lr8vn0"
Khám phá UEers
```

Description:

```txt id="gz6fv6"
Tìm sinh viên, cựu sinh viên và cố vấn phù hợp để kết nối trong cộng đồng HCMUE.
```

## 21.2. Greeting CTA

```txt id="kgk4yj"
Gửi lời chào
```

## 21.3. Greeting Composer

Title:

```txt id="i2s56i"
Gửi lời chào
```

Description:

```txt id="4aqqy8"
Viết một lời chào ngắn để bắt đầu kết nối.
```

Placeholder:

```txt id="rt7b90"
Chào bạn, mình muốn kết nối để trao đổi thêm về...
```

Helper:

```txt id="l002e3"
Lời chào nên ngắn gọn, lịch sự và rõ lý do kết nối.
```

Submit:

```txt id="gzu44f"
Gửi lời chào
```

## 21.4. Greeting Pending

```txt id="4zdcaw"
Bạn đã gửi lời chào. Vui lòng chờ phản hồi.
```

## 21.5. Greeting Accepted

```txt id="6wygqy"
Hai bạn đã kết nối. Bạn có thể bắt đầu trò chuyện.
```

## 21.6. Greeting Declined

```txt id="ln7qvu"
Lời chào này đã được phản hồi.
```

Avoid saying:

```txt id="oho52w"
Người này đã từ chối bạn.
```

Từ chối request không phải đánh giá nhân phẩm. UI đừng làm màu thêm.

---

# 22. Messaging Copy

## 22.1. Inbox Empty

```txt id="4gal3i"
Title: Bạn chưa có tin nhắn
Description: Khi bạn kết nối với người khác, cuộc trò chuyện sẽ xuất hiện tại đây.
```

## 22.2. Message Composer

Placeholder:

```txt id="lsc9b2"
Nhập tin nhắn...
```

Send:

```txt id="jsg8gz"
Gửi
```

Attachment:

```txt id="24sdkq"
Đính kèm
```

## 22.3. Message Request

```txt id="vwg85k"
Tin nhắn này đang chờ xác nhận.
```

## 22.4. Blocked Conversation

```txt id="gzsb8g"
Bạn không thể gửi tin nhắn trong cuộc trò chuyện này.
```

## 22.5. Deleted Message

```txt id="ih7flh"
Tin nhắn đã được xóa.
```

## 22.6. Edited Message

```txt id="6jbp5l"
Đã chỉnh sửa
```

## 22.7. Read Receipt

```txt id="dr1h2c"
Đã xem
```

Use carefully and only if read receipts are enabled.

---

# 23. Mentor Copy

## 23.1. Mentor Discovery

Title:

```txt id="u1i0ar"
Tìm mentor phù hợp
```

Description:

```txt id="1p8b4w"
Kết nối với cựu sinh viên hoặc cố vấn học tập để nhận định hướng phù hợp.
```

## 23.2. Mentor Request Form

Title:

```txt id="r8qkcb"
Gửi yêu cầu mentor
```

Topic label:

```txt id="jbowea"
Chủ đề cần hỗ trợ
```

Question label:

```txt id="fujz2o"
Câu hỏi của bạn
```

Goal label:

```txt id="hh9llj"
Mục tiêu
```

Urgency label:

```txt id="pqzhus"
Mức độ cần hỗ trợ
```

Question helper:

```txt id="ni4vuu"
Mô tả điều bạn đang thắc mắc hoặc cần mentor góp ý.
```

Goal helper:

```txt id="33r82l"
Cho mentor biết bạn muốn đạt được điều gì sau buổi trao đổi.
```

Submit:

```txt id="1jup73"
Gửi yêu cầu
```

## 23.3. Mentor Availability

Available:

```txt id="sjecll"
Đang nhận yêu cầu
```

Limited:

```txt id="id9qks"
Số lượng yêu cầu còn hạn chế
```

Paused:

```txt id="f1qlbm"
Mentor đang tạm dừng nhận yêu cầu
```

Full:

```txt id="h0wwx7"
Mentor hiện đã đầy yêu cầu chờ xử lý
```

## 23.4. Mentor Request States

Pending:

```txt id="5pb5ze"
Yêu cầu của bạn đang chờ mentor phản hồi.
```

Need more info:

```txt id="b4n7t1"
Mentor cần bạn bổ sung thêm thông tin.
```

Accepted:

```txt id="c2970z"
Mentor đã chấp nhận yêu cầu của bạn.
```

Declined:

```txt id="z0e5ud"
Mentor chưa thể hỗ trợ yêu cầu này.
```

Avoid:

```txt id="r73qx2"
Mentor đã từ chối bạn.
```

---

# 24. Community Copy

## 24.1. Community Page

Title:

```txt id="n6n1hv"
Cộng đồng & CLB
```

Description:

```txt id="wq9kp3"
Khám phá các cộng đồng học tập, CLB và nhóm hoạt động trong HCMUE.
```

## 24.2. Join CTA

Open community:

```txt id="zsvjy2"
Tham gia
```

Approval required:

```txt id="m5bbgy"
Gửi yêu cầu tham gia
```

Pending:

```txt id="qpyiuz"
Đang chờ duyệt
```

Joined:

```txt id="dosn5d"
Đã tham gia
```

Leave:

```txt id="ictsfk"
Rời cộng đồng
```

## 24.3. Community Locked

Private:

```txt id="a9bs7g"
Cộng đồng này chỉ hiển thị với thành viên được duyệt.
```

Suspended:

```txt id="h6kg4w"
Cộng đồng này hiện đang bị tạm khóa.
```

Archived:

```txt id="8mp8tv"
Cộng đồng này đã được lưu trữ.
```

## 24.4. Community Resource

Submit title:

```txt id="a3olhs"
Gửi tài nguyên
```

Copyright attestation:

```txt id="wppzfr"
Tôi xác nhận tài nguyên này không vi phạm bản quyền và có thể chia sẻ trong cộng đồng.
```

Pending:

```txt id="c21jry"
Tài nguyên đang chờ duyệt.
```

Approved:

```txt id="f4a29i"
Tài nguyên đã được duyệt.
```

Rejected:

```txt id="kpfkrs"
Tài nguyên chưa được duyệt.
```

## 24.5. Community Suggestion

Title:

```txt id="r0t95t"
Đề xuất cộng đồng mới
```

Description:

```txt id="u68w7n"
Nếu bạn chưa thấy cộng đồng phù hợp, hãy đề xuất để admin xem xét.
```

Purpose label:

```txt id="eev7ql"
Mục đích cộng đồng
```

Target members label:

```txt id="uyb864"
Đối tượng thành viên
```

Submit:

```txt id="v5pldu"
Gửi đề xuất
```

---

# 25. Career Pathway Copy

## 25.1. Main Page

Title:

```txt id="fjpeo7"
Lộ trình định hướng
```

Description:

```txt id="4to3l2"
Khám phá các hướng phát triển phù hợp với ngành học, mục tiêu và cộng đồng HCMUE.
```

## 25.2. Pathway Card

CTA:

```txt id="bmmhpo"
Xem lộ trình
```

Save:

```txt id="x2fx42"
Lưu lộ trình
```

Saved:

```txt id="jvz840"
Đã lưu
```

## 25.3. Source Labels

```txt id="8d5rsl"
Đối chiếu từ CTĐT HCMUE
Admin biên tập
Mentor chia sẻ
Cựu sinh viên chia sẻ
Cần kiểm tra lại nguồn
```

## 25.4. Year Guidance

Year 1:

```txt id="m2dwc7"
Hãy dùng lộ trình này để hiểu ngành, học nền tảng và tìm cộng đồng phù hợp.
```

Year 2:

```txt id="5cvyd1"
Đây là thời điểm tốt để thử project nhỏ, tham gia cộng đồng và bắt đầu hỏi mentor.
```

Year 3:

```txt id="36jo31"
Bạn nên kết nối mentor, làm project có minh chứng và chuẩn bị thực tập hoặc nghiên cứu.
```

Year 4:

```txt id="mnfg54"
Tập trung hoàn thiện hồ sơ năng lực, khóa luận, thực tập và kế hoạch sau tốt nghiệp.
```

## 25.5. No Overpromise

Avoid:

```txt id="267r93"
Lộ trình đảm bảo có việc làm.
Theo hướng này chắc chắn thành công.
```

Use:

```txt id="h2pc2t"
Lộ trình này giúp bạn tham khảo các hướng phát triển phổ biến.
```

---

# 26. Search & Filter Copy

## 26.1. Search Placeholder

Global:

```txt id="dmutis"
Tìm UEers, cộng đồng, mentor, bài viết...
```

Community:

```txt id="vnn7ww"
Tìm cộng đồng...
```

Mentor:

```txt id="quymvk"
Tìm mentor theo chủ đề...
```

Career:

```txt id="r4xc1z"
Tìm lộ trình, kỹ năng, ngành học...
```

## 26.2. Search Empty

```txt id="g9u039"
Không tìm thấy kết quả phù hợp.
Hãy thử từ khóa khác hoặc bỏ bớt bộ lọc.
```

## 26.3. Filter

```txt id="6udb8s"
Bộ lọc
Áp dụng bộ lọc
Xóa bộ lọc
Xóa tất cả
```

## 26.4. Recent Search

```txt id="cezhyv"
Tìm kiếm gần đây
```

## 26.5. Result Categories

```txt id="n5b52u"
Tất cả
UEers
Bài viết
Cộng đồng
Mentor
Lộ trình
Tài nguyên
```

---

# 27. Settings & Privacy Copy

## 27.1. Settings Page

Title:

```txt id="jbeo1r"
Cài đặt
```

Privacy:

```txt id="84uac4"
Quyền riêng tư
```

Notification:

```txt id="5sd2as"
Thông báo
```

Security:

```txt id="oyh4i7"
Bảo mật tài khoản
```

## 27.2. Privacy Controls

Discovery visibility:

```txt id="1e2x4a"
Hiển thị hồ sơ trong Khám phá
```

Helper:

```txt id="0hmdnd"
Khi bật, người dùng phù hợp có thể nhìn thấy hồ sơ của bạn trong Khám phá.
```

Profile public fields:

```txt id="slo1kt"
Thông tin hiển thị trên hồ sơ công khai
```

## 27.3. Notification Settings

In-app:

```txt id="wl3dwl"
Thông báo trong ứng dụng
```

Browser push:

```txt id="614zc4"
Thông báo trình duyệt
```

Helper:

```txt id="8hdjaf"
Bạn có thể nhận thông báo về lời chào, tin nhắn, mentor request và cập nhật quan trọng.
```

---

# 28. Admin Copy

## 28.1. Admin Dashboard

Title:

```txt id="sys6v3"
Bảng điều khiển Admin
```

Widget labels:

```txt id="hxwleq"
Tài khoản đã xác thực
Bài viết hôm nay
Lời chào đã gửi
Yêu cầu mentor
Báo cáo đang chờ
Cộng đồng đang hoạt động
```

## 28.2. Verification Queue

Title:

```txt id="c8uywm"
Yêu cầu xác thực
```

Actions:

```txt id="3rxw2a"
Duyệt
Từ chối
Yêu cầu bổ sung
Đánh dấu xung đột
Tạm khóa nghi vấn
```

Reason label:

```txt id="3k639p"
Lý do xử lý
```

Instruction label:

```txt id="tn6j9o"
Hướng dẫn bổ sung
```

## 28.3. Permission Grant

Title:

```txt id="e6c50m"
Cấp quyền
```

Description:

```txt id="zkljae"
Quyền này chỉ áp dụng trong phạm vi được chọn.
```

Scoped permission helper:

```txt id="4ncrmm"
Ví dụ: quyền quản lý CLB chỉ áp dụng cho cộng đồng được gán.
```

## 28.4. Audit Copy

```txt id="7bddnx"
Hành động này sẽ được ghi vào audit log.
```

## 28.5. Admin Danger Confirmation

Suspend user:

```txt id="5i7j83"
Tạm khóa tài khoản này?
Người dùng sẽ không thể sử dụng các tính năng chính trong thời gian bị tạm khóa.
```

Ban user:

```txt id="fdqbk2"
Cấm tài khoản này?
Hành động này sẽ ngăn người dùng đăng nhập và sử dụng UEConnect.
```

Suspend community:

```txt id="1e51vi"
Tạm khóa cộng đồng này?
Thành viên sẽ không thể đăng bài, trò chuyện hoặc gửi yêu cầu tham gia mới.
```

---

# 29. Data Privacy Copy

## 29.1. Privacy Notice

Short:

```txt id="gf28xw"
UEConnect chỉ hiển thị thông tin theo cài đặt quyền riêng tư của bạn.
```

Verification evidence:

```txt id="mr3h5n"
Minh chứng xác thực chỉ được dùng để xét duyệt tài khoản và không hiển thị công khai.
```

Search privacy:

```txt id="29d2ni"
Một số thông tin có thể không xuất hiện trong tìm kiếm do cài đặt quyền riêng tư.
```

## 29.2. Report Privacy

```txt id="fyzcy1"
Người bị báo cáo sẽ không nhìn thấy danh tính của bạn trong báo cáo.
```

## 29.3. Analytics Privacy

```txt id="4iio7k"
UEConnect chỉ sử dụng dữ liệu tổng hợp để cải thiện sản phẩm.
```

Do not overpromise:

```txt id="iaxfq5"
Chúng tôi không thu thập bất kỳ dữ liệu nào.
```

Vì nếu có analytics event thì câu đó sai. Và UI nói dối là cách nhanh nhất để mất trust.

---

# 30. Sensitive Content Rules

## 30.1. Do Not Display Raw Sensitive Content In Copy

Never include in notification/toast/analytics copy:

```txt id="wdiulf"
report description
private message full body
verification evidence note
admin internal note
full MSSV
email
phone number
private profile fields
```

## 30.2. Use Safe Preview

Instead of:

```txt id="jkg4ld"
Nguyễn Văn A báo cáo bạn vì: [raw description]
```

Use:

```txt id="v6cru2"
Một nội dung của bạn đã được báo cáo và đang chờ xem xét.
```

Instead of:

```txt id="rk0lhp"
Tin nhắn: [full sensitive message]
```

Use:

```txt id="ivz5z8"
Bạn có tin nhắn mới.
```

## 30.3. Safety Copy Must Not Escalate Conflict

Avoid:

```txt id="xl5w9t"
Người dùng này đã tố cáo bạn.
Bạn bị người khác chặn.
```

Use:

```txt id="9q2myc"
Nội dung này đang được xem xét.
Cuộc trò chuyện này hiện không khả dụng.
```

---

# 31. Dating-like Language Ban

UEConnect must avoid dating-app language.

## 31.1. Forbidden Terms

Do not use:

```txt id="l6i1ay"
match
crush
người ấy
hẹn hò
quẹt phải
quẹt trái
tìm một nửa
ghép đôi
thả tim để kết nối
đối tượng phù hợp
bạn có tương hợp
```

## 31.2. Preferred Terms

Use:

```txt id="3lgb8l"
kết nối
lời chào
khám phá
phù hợp
cộng đồng
trao đổi
hồ sơ
mentor
cùng ngành
cùng mối quan tâm
```

## 31.3. Visual Copy Rule

Avoid:

```txt id="1gs2si"
heart-based CTA
romantic metaphor
flirt copy
```

Use:

```txt id="7fsek3"
connection
learning
community
academic support
```

---

# 32. Localization Guidelines

## 32.1. Key Naming

Code keys in English:

```txt id="lzxq1g"
auth.login.title
verification.submit.cta
greeting.send.cta
community.join.pending
mentor.request.accepted
```

UI value in Vietnamese:

```txt id="az5uwx"
Đăng nhập
Gửi xác thực
Gửi lời chào
Đang chờ duyệt
Yêu cầu mentor đã được chấp nhận
```

## 32.2. Avoid Hardcoded Copy

All reusable UI copy should support localization.

Examples:

```txt id="9u1ww0"
buttons
labels
helper text
errors
empty states
status badges
notifications
admin actions
```

## 32.3. English-ready Notes

When translating to English later:

```txt id="kgph87"
- keep tone clear and calm
- avoid dating language
- preserve education/community meaning
- do not overformalize
```

## 32.4. Variable Interpolation

Use safe variables:

Good:

```txt id="n3ljdy"
{count} thông báo chưa đọc
```

Bad if sensitive:

```txt id="ixma03"
{raw_report_description}
{private_message_body}
```

---

# 33. Content QA Checklist

Before shipping any copy:

```txt id="f3et6x"
[ ] Copy is in Vietnamese unless intentionally branded/technical.
[ ] Copy is short enough for mobile.
[ ] Copy is clear without extra context.
[ ] Button copy starts with action.
[ ] Error copy explains what happened and what to do.
[ ] Success copy confirms actual completed action.
[ ] Empty state explains why it is empty or what happens next.
[ ] Permission state explains condition if safe.
[ ] Safety/moderation copy is calm and non-accusatory.
[ ] No dating-app wording.
[ ] No raw sensitive data.
[ ] No overpromise.
[ ] No machine-like "Invalid/Submit/Error".
[ ] Terminology matches glossary.
[ ] Tone matches context seriousness.
[ ] Copy is localization-ready.
```

---

# 34. Anti-patterns

Do not write:

```txt id="tb6zoa"
Oops, something went wrong.
Submit
Invalid
Click here
Are you sure?
User not found
You are rejected
Match found
Crush mới
Báo cáo thành công, xử đẹp rồi!
Tài khoản của bạn vi phạm nghiêm trọng vì bạn...
Cơ hội cuối cùng để kết nối!
Lộ trình này đảm bảo có việc làm.
```

Use instead:

```txt id="9qx56e"
Đã có lỗi xảy ra. Vui lòng thử lại sau.
Gửi
Thông tin chưa hợp lệ.
Xem chi tiết
Tạm khóa tài khoản này?
Nội dung này không còn khả dụng.
Bạn nhận được một lời chào mới.
Báo cáo của bạn đã được gửi.
Tài khoản của bạn hiện đang bị tạm khóa.
Khám phá các hướng phát triển phù hợp.
Lộ trình này giúp bạn tham khảo các hướng phát triển phổ biến.
```

---

# 35. Final Rule

Content tone là một phần của UX system.

Trước khi thêm copy mới:

```txt id="s3q9us"
1. Xác định context.
2. Xác định user đang cần biết gì.
3. Viết ngắn nhất có thể nhưng vẫn đủ rõ.
4. Chọn tone phù hợp với mức độ nghiêm túc.
5. Tránh dating/social drama language.
6. Tránh sensitive data.
7. Kiểm tra terminology.
8. Kiểm tra mobile length.
9. Kiểm tra localization key.
```

Nếu copy không giúp user hiểu trạng thái hoặc hành động tiếp theo, nó chỉ là chữ trang trí. Và chữ trang trí trong UI thì cũng giống comment code sai sự thật: nhìn có vẻ hữu ích, thật ra đang âm thầm phá hoại.

```
```
