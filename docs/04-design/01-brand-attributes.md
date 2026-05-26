---
title: "Brand Attributes"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Design / Product / Frontend Team"
depends_on:
  - "00-design-foundation-roadmap.md"
next:
  - "02-brand-identity-hcmue.md"
  - "03-color-system.md"
  - "16-content-tone.md"
related_pages:
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "page-specs/mentor.md"
---

# 01. Brand Attributes

## 1. Purpose

File này định nghĩa **brand attributes** cho UEConnect.

Mục tiêu là giúp toàn bộ team thống nhất:

- UEConnect muốn người dùng cảm thấy gì.
- UEConnect là loại social platform như thế nào.
- UEConnect khác gì app dating, portal trường, Facebook clone, Zalo clone, LinkedIn clone.
- Brand personality ảnh hưởng thế nào đến UI, UX, content, component và product flow.
- Khi thiết kế screen mới, team phải giữ cảm giác sản phẩm nhất quán ra sao.

File này không đi sâu vào màu, typography, component hay layout token. Các phần đó được xử lý ở những file riêng.

```txt
Brand attributes = tính cách và định hướng cảm xúc của sản phẩm.
Design tokens = cách chuyển tính cách đó thành UI cụ thể.
````

Nếu brand attributes sai, các file sau có viết kỹ đến đâu cũng chỉ là trang trí cho một hướng đi lệch. Và vâng, đó là cách nhiều design system trở thành bộ sưu tập button đẹp nhưng product thì vô hồn.

---

## 2. Product Context

UEConnect là nền tảng social dành cho sinh viên HCMUE.

UEConnect giúp sinh viên:

* Kết nối với UEers trong trường.
* Làm quen bạn cùng khoa, cùng lớp, cùng môn học.
* Đăng bài, chia sẻ, thảo luận như một social feed.
* Khám phá profile của các UEers khác theo hướng tin cậy, sáng tạo, không dating.
* Nhắn tin cá nhân và tham gia các cộng đồng học tập.
* Tìm mentor, alumni, định hướng học tập và career.
* Xây dựng identity cá nhân trong môi trường HCMUE.

Brand promise:

```txt
UEConnect giúp sinh viên HCMUE kết nối và làm quen chuẩn HCMUEr.
```

Phiên bản mở rộng:

```txt
UEConnect giúp sinh viên HCMUE kết nối đúng người, đúng cộng đồng, đúng mentor và đúng cơ hội trong hành trình đại học.
```

---

## 3. First 5 Seconds Impression

Trong 5 giây đầu tiên, người dùng nên cảm thấy:

```txt
Social
Creative
Youthful
```

Diễn giải:

| Impression | Ý nghĩa                                                                     |
| ---------- | --------------------------------------------------------------------------- |
| Social     | Đây là nơi để gặp gỡ, đăng bài, trò chuyện, khám phá UEers                  |
| Creative   | Profile và nội dung có chỗ cho cá tính, không khô cứng như hồ sơ hành chính |
| Youthful   | Trẻ trung, năng động, hợp sinh viên, nhưng không trẻ con quá mức            |

Tuy nhiên, 3 cảm giác này phải nằm trên nền:

```txt
Verified
Trusted
HCMUE-rooted
```

Nếu chỉ có Social + Creative + Youthful mà thiếu trust, UEConnect sẽ dễ trượt thành một app làm quen mơ hồ, thiếu an toàn. Internet đã có đủ chỗ như vậy rồi, cảm ơn.

---

## 4. Brand Positioning

## 4.1. Positioning Statement

```txt
UEConnect is a verified HCMUE student social platform that helps students connect, express themselves, discover peers, join communities, and grow through mentorship without becoming a dating app, a school portal, or a generic social network.
```

Bản tiếng Việt:

```txt
UEConnect là nền tảng social xác thực dành cho sinh viên HCMUE, giúp sinh viên kết nối, thể hiện bản thân, khám phá UEers, tham gia cộng đồng và phát triển qua mentor mà không biến thành app hẹn hò, cổng thông tin trường hay mạng xã hội đại trà.
```

## 4.2. Product Category

UEConnect thuộc nhóm:

```txt
Verified Student Social Platform
```

Không định vị là:

```txt
Dating App
Student Portal
Career Portal
Chat App Clone
Generic Social Network
```

## 4.3. Product Mix Reference

UEConnect có thể học từ nhiều product pattern khác nhau, nhưng không copy nguyên bản.

| Area             | Reference Inspiration | UEConnect Adaptation                                        |
| ---------------- | --------------------- | ----------------------------------------------------------- |
| Feed / Blogging  | Threads               | Feed tối giản, text-first, dễ đọc, tập trung vào nội dung   |
| Discovery        | Tinder                | Interaction khám phá profile nhanh, nhưng bỏ dating vibe    |
| Comment          | Facebook              | Comment rõ tầng, dễ thảo luận, quen với user Việt Nam       |
| Profile Setup    | LinkedIn + Tinder     | Đủ thông tin, chuyên nghiệp, nhưng vẫn hấp dẫn và độc bản   |
| Community / Club | Discord               | Có community/chat/channel mindset, nhưng đơn giản hơn ở MVP |
| Mentor           | LinkedIn nhẹ          | Có mentor/career, nhưng không LinkedIn hóa toàn bộ product  |

---

## 5. What UEConnect Is

UEConnect là:

```txt
A trusted social layer for HCMUE student life.
```

Cụ thể hơn:

* Một social feed cho sinh viên HCMUE.
* Một nơi khám phá UEers theo hướng học tập, cộng đồng và identity.
* Một hệ thống nhắn tin và cộng đồng nội bộ.
* Một nơi kết nối mentor, alumni, định hướng học tập và career.
* Một sản phẩm có verified identity dựa trên mã sinh viên.
* Một không gian social đủ trẻ, đủ vui, nhưng vẫn có kiểm duyệt và an toàn.

---

## 6. What UEConnect Is Not

UEConnect không phải:

| Not                       | Vì sao cần tránh                                                                  |
| ------------------------- | --------------------------------------------------------------------------------- |
| Dating app                | Sản phẩm phục vụ kết nối học tập, cộng đồng, mentor, không phải ghép đôi tình cảm |
| Portal trường             | Không phải nơi chỉ đọc thông báo, biểu mẫu, lịch học                              |
| Job board                 | Career là một phần growth, không phải toàn bộ product                             |
| Zalo/Messenger clone      | Messaging cần có ngữ cảnh HCMUE, không chỉ chat rỗng                              |
| Facebook clone            | Có feed, nhưng phải có verified student identity và community scope rõ            |
| LinkedIn clone            | Mentor/career quan trọng, nhưng không biến profile thành CV khô khan              |
| AI-generated landing page | Không gradient tràn lan, không màu quá nhiều, không layout khoe hiệu ứng          |
| SaaS dashboard lạnh lẽo   | Đây là social product, cần human và student-first                                 |

---

## 7. Core Brand Attributes

UEConnect dùng 6 brand attribute chính:

```txt
Trusted
Social
Student-first
Minimal
Human
Growth-oriented
```

Đây là 6 trụ cột cho mọi quyết định UI/UX.

---

# 8. Attribute 1: Trusted

## 8.1. Meaning

UEConnect phải tạo cảm giác tin cậy vì đây là social platform trong môi trường trường đại học.

Trust đến từ:

* Verified identity.
* Mỗi user chỉ có một account.
* Mã sinh viên là nguồn xác thực chính.
* Tạo tài khoản cần được kiểm duyệt kỹ.
* Nội dung có moderation.
* Profile có thông tin học tập rõ.
* Report/block dễ tìm.
* UI rõ ràng, không mập mờ.

## 8.2. UX Translation

| Area      | Rule                                               |
| --------- | -------------------------------------------------- |
| Signup    | Phải làm rõ xác thực bằng mã sinh viên             |
| Profile   | Hiển thị verified UEer rõ nhưng không quá chói     |
| Feed      | Author identity phải rõ: tên, khoa, khóa, verified |
| Discovery | Không chỉ ảnh, phải có context học tập/cộng đồng   |
| Messaging | User biết mình đang nói chuyện với ai              |
| Community | Club/class/community cần có admin/moderator        |
| Safety    | Report/block phải có ở post, profile, message      |
| Mentor    | Mentor/alumni phải có trust signal riêng           |

## 8.3. Visual Translation

Trusted không có nghĩa là dùng màu xanh khắp nơi.

Nên dùng:

* `#124874` cho verified badge, active state, primary CTA.
* Neutral surface cho content.
* Border rõ.
* Typography dễ đọc.
* Icon line rõ nghĩa.
* Không quá nhiều shadow, gradient, decoration.

Không nên:

* Dùng màu brand cho mọi icon.
* Dùng verified badge quá to.
* Dùng background xanh toàn màn hình trong feed.
* Dùng gradient để “tạo trust”. Gradient không xác thực được ai cả, thật bất ngờ.

## 8.4. Copy Translation

Nên dùng:

```txt
Đã xác thực UEer
Xác thực bằng mã sinh viên
Thông tin này giúp UEers tin tưởng bạn hơn
Báo cáo nội dung
Chặn người dùng
```

Không dùng:

```txt
Trust us
An toàn tuyệt đối
Verified 100%
Không có rủi ro
```

Không hứa quá mức. Social product nào hứa “an toàn tuyệt đối” thường là đang chuẩn bị xin lỗi trên truyền thông.

## 8.5. QA Checklist

* [ ] User identity có rõ không?
* [ ] Verified state có dễ nhận ra không?
* [ ] Có tránh làm verified badge quá lố không?
* [ ] Có report/block không?
* [ ] Có giải thích tại sao cần mã sinh viên không?
* [ ] Profile có đủ context để user tin tưởng không?
* [ ] Admin/moderation có chỗ trong flow không?

---

# 9. Attribute 2: Social

## 9.1. Meaning

UEConnect là social platform, không phải static website.

Social nghĩa là người dùng có thể:

* Đăng bài.
* Bình luận.
* Tương tác.
* Gửi lời chào.
* Nhắn tin.
* Khám phá UEers.
* Tham gia cộng đồng.
* Theo dõi hoạt động học tập/câu lạc bộ.

## 9.2. UX Translation

| Feature      | Social Behavior                                         |
| ------------ | ------------------------------------------------------- |
| Home Feed    | Blogging/feed giống Threads, nội dung là trung tâm      |
| Comment      | Có thể học Facebook về sự rõ ràng và quen thuộc         |
| Discovery    | Interaction nhanh, vui, gần Tinder nhưng không dating   |
| Messaging    | Ưu tiên real-time 1:1 trước                             |
| Community    | Có thể phát triển sau theo hướng Discord nhẹ            |
| Profile      | Không chỉ hồ sơ, mà là social identity                  |
| Notification | Thông báo phải hỗ trợ conversation, connection, mention |

## 9.3. Visual Translation

Social UI nên:

* Có avatar rõ.
* Feed dễ scan.
* Post action nhẹ.
* Comment dễ đọc.
* Profile có cá tính.
* Discovery có nhịp nhanh.
* Bottom navigation mobile rõ.

Không nên:

* Biến mọi thứ thành form.
* Dùng card quá nặng như dashboard.
* Dùng layout quá hành chính.
* Nhồi quá nhiều metadata làm post mất cảm xúc.

## 9.4. Copy Translation

Nên dùng:

```txt
Gửi lời chào
Kết nối
Khám phá UEers
Bạn cùng khoa
Cùng học
Cộng đồng
Mentor
```

Không dùng:

```txt
Match
Swipe
Crush
Hot profile
Dating
```

Các từ dating phải bị chặn từ cấp content system. Không phải cứ bỏ màu hồng là hết vibe dating, loài người đã từng sai ở chỗ này nhiều lần.

## 9.5. QA Checklist

* [ ] Feed có cảm giác là nơi để tương tác không?
* [ ] Post action có nhẹ và rõ không?
* [ ] Discovery có vui nhưng không dating không?
* [ ] Comment có dễ theo dõi không?
* [ ] Messaging có cảm giác real-time không?
* [ ] Profile có đủ cá tính không?
* [ ] Có tránh ngôn ngữ dating không?

---

# 10. Attribute 3: Student-first

## 10.1. Meaning

UEConnect phục vụ sinh viên HCMUE trước tiên.

Người dùng ưu tiên:

```txt
01. Sinh viên HCMUE năm 1–4
02. Cựu sinh viên / alumni
03. Mentor / cố vấn học tập
04. CLB / cộng đồng
05. Admin / moderator
```

Admin quan trọng cho vận hành, nhưng không phải persona chính trong brand cảm xúc.

## 10.2. UX Translation

Student-first nghĩa là product phải hỗ trợ:

* Làm quen bạn mới trong trường.
* Tìm bạn cùng khoa, cùng môn, cùng lớp.
* Đăng bài học tập và đời sống sinh viên.
* Tham gia CLB.
* Tìm mentor.
* Xây dựng profile sinh viên.
* Có identity riêng của UEer.

## 10.3. Information Context

Profile nên ưu tiên các thông tin:

```txt
Tên
Avatar
Verified UEer
Mã sinh viên đã xác thực, không nhất thiết public toàn bộ
Khoa
Khóa
Ngành
Lớp
Sở thích học tập
CLB / cộng đồng
Môn quan tâm
Mentor/career interest
Bio ngắn
```

Không biến profile thành CV LinkedIn đầy đủ ngay từ đầu.

## 10.4. Visual Translation

Student-first nên có:

* Avatar và profile friendly.
* Badge khoa/khóa rõ.
* Discovery card có personality.
* Feed có content học tập và cộng đồng.
* Community/club có visual riêng nhưng không quá màu mè.

Không nên:

* Quá corporate.
* Quá cute.
* Quá giống school portal.
* Quá giống dating profile.

## 10.5. QA Checklist

* [ ] Sinh viên mới vào có hiểu app dùng để làm gì không?
* [ ] Có hỗ trợ tìm bạn cùng khoa/lớp/môn không?
* [ ] Profile có đủ ngữ cảnh sinh viên không?
* [ ] Có tránh cảm giác CV/LinkedIn quá mức không?
* [ ] Có tránh cảm giác app hẹn hò không?
* [ ] Có thể mở rộng cho alumni/mentor không?

---

# 11. Attribute 4: Minimal

## 11.1. Meaning

Minimal ở đây không có nghĩa là trống rỗng hay nhàm chán.

Minimal nghĩa là:

* Ít màu.
* Ít decoration.
* Ít hiệu ứng.
* Hierarchy rõ.
* Content dễ đọc.
* Component có chủ đích.
* Brand color dùng tiết chế.

## 11.2. UX Translation

Minimal giúp social product:

* Feed dễ đọc hơn.
* Comment không rối.
* Profile không bị quá tải.
* Messaging tập trung vào conversation.
* Discovery vui nhưng không lòe loẹt.
* Enterprise scale dễ hơn.

## 11.3. Visual Rules

Nên dùng:

```txt
White
Near-white
Light border
Neutral text
Subtle hover
Solid primary button
Line icon
```

Hạn chế:

```txt
Gradient
Shadow mạnh
Icon nhiều màu
Card nhiều border lồng nhau
Heading quá to
Background quá chói
```

## 11.4. Brand Color Ratio

Trên một product screen thông thường:

```txt
Neutral: 80–90%
Text: 8–12%
Brand blue: 5–10%
Semantic colors: <3%
Gradient: 0–5%
```

Nếu một screen có quá nhiều `#124874`, hãy giảm. Màu thương hiệu không phải nước mắm, không phải món nào cũng đổ vào cho đậm đà.

## 11.5. QA Checklist

* [ ] Screen có dùng quá nhiều màu không?
* [ ] Có hơn 1 primary CTA không?
* [ ] Icon có quá nhiều màu không?
* [ ] Shadow có quá mạnh không?
* [ ] Border radius có quá cute không?
* [ ] Nội dung có bị decoration lấn át không?

---

# 12. Attribute 5: Human

## 12.1. Meaning

UEConnect phải tạo cảm giác con người thật, mối quan hệ thật, cộng đồng thật.

Human đến từ:

* Avatar.
* Bio.
* Sở thích.
* Bài đăng.
* Comment.
* Lời chào.
* Mentor story.
* Community activity.
* Safety và respect.

## 12.2. UX Translation

Human UI nên hỗ trợ:

* Profile thể hiện cá tính.
* Discovery khiến user thấy mỗi người là độc bản.
* Messaging thân thiện, rõ.
* Feed có voice cá nhân.
* Mentor không chỉ là CV, mà là người có kinh nghiệm thật.
* Community không chỉ là group chat, mà là nơi thuộc về.

## 12.3. Discovery Profile Direction

Discovery profile nên có cảm giác:

```txt
Tin cậy
Vui vẻ
Sáng tạo
Có giá trị cá nhân
Không gò bó
Không dating
```

Có thể học interaction từ Tinder:

* Dễ xem từng profile.
* CTA nhanh.
* Motion nhẹ.
* Profile có tính khám phá.

Nhưng phải bỏ dating vibe:

* Không dùng match.
* Không dùng swipe language.
* Không xếp người dùng như đối tượng hẹn hò.
* Không quá tập trung vào ảnh.
* Không dùng ranking ngoại hình.
* Không dùng từ “hot”.

## 12.4. Copy Translation

Nên dùng:

```txt
Gửi lời chào
Bạn có điểm chung với UEer này
Cùng học môn này
Cùng quan tâm UI/UX
Kết nối để học cùng nhau
```

Không dùng:

```txt
Bạn đã match
Swipe để thích
Hồ sơ nổi bật nóng nhất
Crush quanh bạn
```

## 12.5. QA Checklist

* [ ] Profile có thể hiện cá tính không?
* [ ] Discovery có tránh dating vibe không?
* [ ] Có thông tin học tập/cộng đồng để tạo connection không?
* [ ] Messaging có thân thiện không?
* [ ] Safety có tôn trọng người dùng không?
* [ ] Mentor có cảm giác người thật không?

---

# 13. Attribute 6: Growth-oriented

## 13.1. Meaning

UEConnect không chỉ để lướt feed. Product còn giúp sinh viên phát triển:

* Học tập.
* Quan hệ bạn bè.
* Cộng đồng.
* Mentor.
* Career direction.
* Identity cá nhân.

Mentor là một tính năng chính, ngang hàng với các feed khác, nhưng không được biến toàn bộ product thành LinkedIn.

## 13.2. UX Translation

Growth-oriented thể hiện qua:

| Feature          | Growth Value                      |
| ---------------- | --------------------------------- |
| Mentor Matching  | Tìm người hướng dẫn               |
| Career Exploring | Khám phá hướng đi                 |
| Profile          | Xây dựng identity sinh viên       |
| Feed             | Chia sẻ học tập, kinh nghiệm      |
| Community        | Học cùng, hỏi đáp, CLB            |
| Discovery        | Tìm đúng người để cùng phát triển |

## 13.3. Mentor Direction

Mentor nên có cảm giác:

```txt
Accessible
Trusted
Helpful
Human
Student-friendly
```

Không nên:

```txt
Corporate
LinkedIn-heavy
Recruitment-first
CV-first
Pressure-heavy
```

## 13.4. Career Direction

Career exploring nên xuất hiện như một phần của hành trình sinh viên.

Không nên làm user cảm thấy:

* Phải chuyên nghiệp ngay.
* Phải có CV hoàn hảo.
* Phải chạy đua career.
* Phải “networking” như người đi làm.

UEConnect nên nói:

```txt
Khám phá hướng đi.
Hỏi mentor.
Học từ alumni.
Xây dựng profile từng bước.
```

Không nói:

```txt
Tối ưu thương hiệu cá nhân ngay hôm nay.
Tăng tốc sự nghiệp vượt trội.
Trở thành ứng viên số 1.
```

Nghe như quảng cáo khóa học hơn là social product cho sinh viên.

## 13.5. QA Checklist

* [ ] Mentor có là feature chính không?
* [ ] Mentor UI có tránh LinkedIn hóa không?
* [ ] Career có hỗ trợ thay vì gây áp lực không?
* [ ] Profile có giúp user phát triển identity không?
* [ ] Community có khuyến khích học cùng không?
* [ ] Feed có chỗ cho học tập/kinh nghiệm không?

---

# 14. Brand Attribute Matrix

| Attribute       | UI Meaning                                            | Content Meaning                     | Product Meaning                        |
| --------------- | ----------------------------------------------------- | ----------------------------------- | -------------------------------------- |
| Trusted         | Verified badge, clear identity, restrained brand blue | Rõ ràng, an toàn, không hứa quá mức | Mã sinh viên, kiểm duyệt, report/block |
| Social          | Feed, comment, message, discovery                     | Gần gũi, dễ bắt chuyện              | Kết nối và cộng đồng                   |
| Student-first   | Faculty/cohort/class context                          | Ngôn ngữ sinh viên HCMUE            | Sinh viên là persona chính             |
| Minimal         | Neutral-first, ít màu, ít gradient                    | Copy ngắn, dễ hiểu                  | Không nhồi feature vào UI              |
| Human           | Avatar, bio, profile độc bản                          | Tôn trọng, thân thiện               | Mối quan hệ thật, community thật       |
| Growth-oriented | Mentor, learning, career module                       | Hỗ trợ, định hướng                  | Phát triển trong hành trình đại học    |

---

# 15. Feature Personality Mapping

## 15.1. Home Feed

Primary attributes:

```txt
Social
Minimal
Student-first
```

Feed nên giống:

```txt
Threads-style blogging + Facebook-style comment clarity
```

Feed không nên giống:

```txt
Facebook clone đầy noise
Portal thông báo trường
Dashboard tin tức
```

Rules:

* Text và author identity là trung tâm.
* Action icon nhẹ.
* Brand color chỉ dùng cho active/focus/verified.
* Comment dễ đọc, không rối.

---

## 15.2. Discovery Profile

Primary attributes:

```txt
Human
Social
Trusted
```

Discovery nên giống:

```txt
Tinder-like interaction speed
+ verified student identity
+ creative profile expression
```

Discovery không nên giống:

```txt
Dating app
Student directory khô khan
Ranking ngoại hình
```

Rules:

* Profile phải có cá tính.
* Ảnh quan trọng nhưng không được là tất cả.
* Thông tin học tập/cộng đồng phải rõ.
* CTA dùng “Gửi lời chào”, “Kết nối”, “Lưu hồ sơ”.
* Không dùng “match”, “swipe”, “crush”.

---

## 15.3. Messaging

Primary attributes:

```txt
Human
Social
Trusted
```

Messaging nên giống:

```txt
Clean 1:1 real-time chat
+ community discussion support later
```

Priority hiện tại:

```txt
A. Chat cá nhân
D. Threaded discussion / post cộng đồng
```

Group chat, club chat, channel model có thể phát triển sau.

Rules:

* Own bubble có thể dùng brand blue.
* Other bubble dùng neutral.
* Không dùng gradient cho message bubble.
* Có report/block.
* Có typing, seen, failed state về sau.

---

## 15.4. Community / Club

Primary attributes:

```txt
Social
Student-first
Growth-oriented
```

Community có thể học từ Discord ở cách tổ chức:

```txt
Server / community
Channel / topic
Member role
Moderator
Pinned content
```

Nhưng MVP không cần phức tạp như Discord.

Rules:

* Community/CLB là phụ ở giai đoạn đầu.
* Không để community làm rối core feed.
* Có thể bắt đầu bằng post cộng đồng và group chat đơn giản.
* Club identity nên rõ nhưng không quá màu mè.

---

## 15.5. Mentor

Primary attributes:

```txt
Growth-oriented
Trusted
Human
```

Mentor là tính năng chính, nhưng không LinkedIn hóa.

Rules:

* Mentor profile cần chuyên nghiệp vừa đủ.
* Có expertise, ngành, kinh nghiệm, availability.
* Có “hỏi mentor”, “đặt lịch”, “gửi câu hỏi”.
* Không biến mọi người thành CV.
* Không dùng ngôn ngữ tuyển dụng quá sớm.

---

## 15.6. Profile Setup

Primary attributes:

```txt
Human
Trusted
Growth-oriented
```

Profile setup nên học:

```txt
LinkedIn: completeness, credibility
Tinder: personality, attractiveness, uniqueness
```

Nhưng phải là:

```txt
Student identity profile
```

Không phải:

```txt
Dating profile
CV profile
School form
```

Rules:

* Setup đủ thông tin nhưng không dài quá.
* Chia step.
* Có progress.
* Giải thích vì sao cần thông tin.
* Có privacy note.
* Dùng mã sinh viên để verified, nhưng không public toàn bộ nếu không cần.

---

# 16. Tone of Voice

Tone chính:

```txt
Gần gũi
An toàn
Trẻ trung vừa phải
Học thuật nhẹ
```

## 16.1. Tone Rules

Nên viết:

* Như một người bạn cùng trường đáng tin.
* Rõ ràng, dễ hiểu.
* Có năng lượng trẻ nhưng không cringe.
* Tôn trọng người dùng.
* Có học thuật nhẹ khi nói về mentor, học tập, cộng đồng.

Không nên viết:

* Quá formal như văn bản hành chính.
* Quá Gen Z đến mức khó chịu.
* Quá marketing.
* Quá career-driven.
* Quá dating.

## 16.2. Example Copy

### Good

```txt
Khám phá UEers có cùng mối quan tâm với bạn.
Gửi lời chào để bắt đầu kết nối.
Thêm vài thông tin để UEers hiểu bạn hơn.
Xác thực mã sinh viên giúp cộng đồng an toàn hơn.
Tìm mentor phù hợp với mục tiêu học tập của bạn.
```

### Bad

```txt
Swipe để tìm match mới.
Crush quanh bạn đang chờ.
Tạo profile cực hot ngay hôm nay.
Nâng cấp thương hiệu cá nhân vượt trội.
Chào mừng đến với cổng thông tin kết nối sinh viên.
```

Dòng cuối nghe như có mùi phòng ban và máy in hết mực. Tránh.

---

# 17. Naming System

## 17.1. Approved Terms

Nên dùng thường xuyên:

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
Bạn cùng môn
Verified UEer
```

## 17.2. Restricted Terms

Không dùng trong product UI:

```txt
Crush
Match
Swipe
Hot
Dating
Ghép đôi
Người yêu
Tán tỉnh
```

## 17.3. Conditional Terms

Có thể dùng nếu context phù hợp:

| Term    | Rule                                                   |
| ------- | ------------------------------------------------------ |
| Follow  | Dùng nếu product có follow model rõ                    |
| Friend  | Dùng nếu có friend request hai chiều                   |
| Connect | Có thể dùng, nhưng ưu tiên “Kết nối”                   |
| Network | Hạn chế, vì dễ LinkedIn hóa                            |
| Career  | Dùng trong mentor/career module, không làm brand chính |

---

# 18. Brand Risk Register

## 18.1. Top Risks

| Risk                       | Severity | Prevention                                                    |
| -------------------------- | -------: | ------------------------------------------------------------- |
| Nhìn AI-generated          |     High | Neutral-first, ít gradient, ít màu, layout có hierarchy       |
| Nhìn giống portal trường   |     High | Social feed, avatar, post, comment, discovery, messaging rõ   |
| Nhìn quá nhiều màu         |     High | Brand color ratio 5–10%, semantic <3%                         |
| Nhìn nhàm chán             |   Medium | Discovery/profile có cá tính, copy trẻ, micro-interaction nhẹ |
| Khó mở rộng enterprise     |     High | Token system, component states, page specs, accessibility     |
| Nhìn dating quá mức        |     High | Cấm dating language, profile có learning/community context    |
| Thiếu trust                |     High | Verified bằng mã sinh viên, report/block, moderation planning |
| Quá trẻ con                |   Medium | Radius, icon, illustration tiết chế                           |
| Quá LinkedIn               |   Medium | Mentor human-friendly, không CV-first                         |
| Quá giống Facebook/Threads |   Medium | Thêm HCMUE verified identity và UEer terminology              |

---

# 19. Visual Impact Summary

Brand attributes ảnh hưởng trực tiếp đến visual direction:

| Decision   | Direction                        |
| ---------- | -------------------------------- |
| Background | White / near-white               |
| Brand blue | Restrained accent                |
| Gradient   | Rare brand moment only           |
| Red        | Heritage/error only              |
| Feed       | Content-first, divider-based     |
| Card       | Light border, minimal shadow     |
| Icon       | Line icon, neutral by default    |
| Typography | System-first, readable           |
| Profile    | Human, verified, student context |
| Discovery  | Creative but safe                |
| Messaging  | Real-time, clean, trusted        |
| Mentor     | Growth-oriented, not corporate   |

---

# 20. UX Impact Summary

Brand attributes ảnh hưởng đến UX như sau:

| UX Area    | Required Direction                    |
| ---------- | ------------------------------------- |
| Onboarding | Nói rõ verified bằng mã sinh viên     |
| Signup     | Trust-first, giải thích privacy       |
| Feed       | Nội dung dễ đọc, post action nhẹ      |
| Discovery  | Khám phá UEers không dating           |
| Profile    | Độc bản, có giá trị, không chỉ CV     |
| Messaging  | Rõ người gửi, có safety               |
| Mentor     | Dễ tiếp cận, không áp lực             |
| Community  | Học cùng, CLB, nhóm lớp               |
| Safety     | Report/block/moderation rõ            |
| Mobile     | Bottom nav, single feed, action sheet |

---

# 21. Brand Attribute QA Checklist

Dùng checklist này khi review bất kỳ screen nào.

## 21.1. Trusted

* [ ] User identity có rõ không?
* [ ] Verified UEer có xuất hiện đúng chỗ không?
* [ ] Có tránh làm trust signal quá lố không?
* [ ] Có safety/report/block nếu cần không?
* [ ] Có tránh copy mơ hồ như “an toàn tuyệt đối” không?

## 21.2. Social

* [ ] Screen có hỗ trợ tương tác không?
* [ ] Avatar/name/action có rõ không?
* [ ] Feed/comment/message có dễ scan không?
* [ ] CTA có khuyến khích kết nối đúng cách không?

## 21.3. Student-first

* [ ] Có ngữ cảnh HCMUE không?
* [ ] Có khoa/khóa/lớp/môn/CLB nếu phù hợp không?
* [ ] Có tránh cảm giác portal hành chính không?
* [ ] Có tránh career pressure quá sớm không?

## 21.4. Minimal

* [ ] Có quá nhiều màu không?
* [ ] Có quá nhiều gradient không?
* [ ] Shadow có quá mạnh không?
* [ ] Component có quá “cute” không?
* [ ] Content có là trung tâm không?

## 21.5. Human

* [ ] Profile có cá tính không?
* [ ] Copy có thân thiện không?
* [ ] Interaction có tự nhiên không?
* [ ] User có cảm thấy được tôn trọng không?

## 21.6. Growth-oriented

* [ ] Screen có hỗ trợ học tập/kết nối/phát triển không?
* [ ] Mentor/career có human-friendly không?
* [ ] Có tránh LinkedIn hóa không?
* [ ] Có hỗ trợ người dùng từng bước không?

---

# 22. AI Prompt Notes

Khi dùng AI để tạo UI hoặc viết code cho UEConnect, luôn thêm:

```txt
UEConnect is a verified HCMUE student social platform.
The brand attributes are Trusted, Social, Student-first, Minimal, Human, and Growth-oriented.
The product should feel social, creative, and youthful in the first 5 seconds, but still safe and verified.
Use neutral-first UI with restrained HCMUE blue #124874.
Avoid dating app language and visual patterns.
Avoid AI-generated UI smell: excessive gradients, too many colors, oversized text, heavy shadows, and generic startup visuals.
Use UEer, kết nối, gửi lời chào, mentor, bạn cùng khoa, cùng học, hỗ trợ, khám phá.
Do not use crush, match, swipe, hot, or dating.
Design for both desktop web app and mobile app behavior.
```

---

# 23. Final Decision

Brand attributes chính thức của UEConnect:

```txt
Trusted
Social
Student-first
Minimal
Human
Growth-oriented
```

First impression:

```txt
Social
Creative
Youthful
```

Core promise:

```txt
Kết nối và làm quen chuẩn HCMUEr.
```

Design personality:

```txt
Một social platform trẻ, sáng tạo, có xác thực, có cộng đồng, có mentor, và đủ sạch để mở rộng thành enterprise product.
```

Câu chốt:

```txt
UEConnect không cố trở thành Tinder, Threads, Facebook, Discord hay LinkedIn.
UEConnect học đúng pattern từ từng sản phẩm đó, rồi chuyển hóa thành một verified student social platform riêng cho HCMUE.
```

```
```
