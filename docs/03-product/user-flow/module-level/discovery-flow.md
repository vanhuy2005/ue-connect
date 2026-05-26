---
title: "Discovery Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0"
actors:
  - Student
related_use_cases:
  - STU-DIS-001
  - STU-DIS-004
  - STU-SOC-004
---

# Discovery Flow

## 1. Purpose

Discovery giúp sinh viên khám phá UEers khác trong trường để làm quen, cùng học, tìm bạn cùng khoa, cùng môn, cùng mục tiêu.

Discovery có thể học từ Tinder về tốc độ và sự hấp dẫn của interaction, nhưng tuyệt đối không dùng dating language hoặc dating framing.

Nói ngắn gọn: học cách chuyển profile nhanh, không học cách biến con người thành thẻ hẹn hò. Nhân loại đã đủ rối rồi.

---

## 2. Actors

| Actor           | Role                        |
| --------------- | --------------------------- |
| Student         | Khám phá và kết nối UEers   |
| Admin/Moderator | Xử lý report profile nếu có |

---

## 3. Entry Points

- Bottom nav `Khám phá`.
- Desktop left nav `Khám phá`.
- Empty feed suggestion.
- Profile completion suggestion.
- Search result.
- Mentor/community recommendation.

---

## 4. High-level Flow

```txt
User opens Discovery
→ System loads recommended UEer profiles
→ User views profile card
→ User can send greeting / skip / save / open profile
→ System loads next profile
→ If mutual or accepted connection, enable messaging
```

## 5. Recommendation Inputs

Discovery có thể dùng các tín hiệu:

Signal Priority
Same faculty High
Same cohort/class High
Shared courses/interests High
Mutual connections Medium
Same clubs/community Medium
Mentor/career interest Medium
Activity level Low
Profile completeness Medium

Không dùng ranking theo ngoại hình. Ghi rõ để sau này không ai “vô tình” biến product thành chợ đánh giá người khác.

## 6. Main Flow: Browse Profiles

Open discovery
→ Load profile suggestion
→ Show profile card
→ User reads context
→ User chooses action
→ Load next profile
Profile Card Must Show
Avatar/photo.
Name.
Verified UEer.
Faculty.
Cohort/class.
Short bio.
Shared interests.
Mutual context.
CTA: Gửi lời chào.
Secondary actions: Bỏ qua, Lưu, Xem hồ sơ.

## 7. Main Flow: Send Greeting

User clicks Gửi lời chào
→ Optional short message
→ Submit
→ Show sent state
→ Load next profile or stay
Greeting Rules
Không gọi là match.
Không gọi là swipe.
Không tạo cảm giác tán tỉnh.
Cho message ngắn optional.
Có rate limit để chống spam.
Good Copy
Gửi lời chào
Bạn có thể bắt đầu bằng một lời chào ngắn.
Kết nối này giúp hai bạn dễ nhắn tin và học cùng nhau hơn.

## 8. Main Flow: Skip Profile

User clicks Bỏ qua
→ Profile is hidden from current queue
→ Load next profile
UX Rules
Skip không phải dislike.
Có thể undo trong vài giây nếu cần.
Không làm user kia biết.

## 9. Main Flow: Open Full Profile

User clicks Xem hồ sơ
→ Open profile page/modal
→ User can send greeting/message/report

Full profile cần nhiều thông tin hơn discovery card:

Bio.
Posts public.
Interests.
Clubs.
Mentor/career interests.
Mutual connections.
Privacy-respecting academic info.

## 10. Alternative Flows

### 10.1. No More Profiles

No recommendation available
→ Show empty state
→ Suggest adjusting filters or completing profile
Empty Copy
Chưa có UEer phù hợp để hiển thị.
Thử mở rộng bộ lọc hoặc hoàn thiện hồ sơ để nhận gợi ý tốt hơn.

### 10.2. Profile Reported

User reports profile
→ Open report flow
→ Submit
→ Hide profile from queue

### 10.3. User Has Incomplete Profile

Open discovery
→ System detects low profile completion
→ Show suggestion to complete profile
→ User can continue with limited discovery

## 11. Filters

MVP filters:

Khoa.
Khóa.
Ngành.
Cùng môn học.
Cùng sở thích học tập.

Later filters:

CLB.
Mentor/career interest.
Online/active recently.
Mutual connections.

## 12. Required Pages

Page Purpose
discovery.md Browse UEers
profile.md Full profile
profile-setup.md Improve profile
safety-reporting.md Report user/profile
connection-management.md Manage greetings/connections
messaging.md Continue after accepted connection

## 13. Required Components

Discovery card.
Profile summary.
Verified badge.
Interest chips.
Filter drawer.
Greeting modal.
Skip/Save/Connect actions.
Empty state.
Report action.
Loading skeleton.

## 14. Required States

State Description
Loading Fetching profiles
Empty No suggestions
Sent Greeting sent
Saved Profile saved
Skipped Profile skipped
Reported Profile reported
Incomplete profile User needs profile completion
Permission denied User not verified
Rate limited Too many greetings

## 15. Desktop Layout

Left Nav
Center Discovery Card
Right Panel: Filters, profile tips, suggested communities

## 16. Mobile Layout

Top Bar
Full-screen discovery card
Bottom action row
Filter action sheet

Mobile discovery cần cảm giác nhanh, nhưng không được ép user vào gesture dating.

## 17. UX Risks

Risk Prevention
Giống dating app Cấm match/swipe/crush/hot
Quá tập trung ảnh Thêm academic/community context
Spam greeting Rate limit + message rules
Thiếu trust Verified badge + student context
Nhàm chán Profile card có personality
Quá màu mè Neutral-first, brand blue đúng chỗ

## 18. UX Checklist

- Discovery không dùng dating language.
- Profile có thông tin học tập/cộng đồng.
- CTA chính là Gửi lời chào.
- Có report/block rõ.
- Có empty state khi hết gợi ý.
- Có filter nhưng không quá phức tạp.
- Mobile action đủ 44px.
- Không dùng gradient tràn nền.
