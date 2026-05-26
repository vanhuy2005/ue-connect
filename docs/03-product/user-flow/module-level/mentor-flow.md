---
title: "Mentor Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P1"
actors:
  - Student
  - Mentor
  - Alumni
  - Advisor
related_use_cases:
  - STU-MEN-001
  - ADV-REQ-001
  - ALU-MEN-001
---

# Mentor Flow

## 1. Purpose

Mentor Flow mô tả cách sinh viên tìm mentor, gửi request, được mentor chấp nhận và bắt đầu tương tác.

Mentor là một tính năng chính của UEConnect, ngang hàng về định hướng sản phẩm với feed, discovery và messaging. Nhưng mentor không được làm UEConnect biến thành LinkedIn clone. Sinh viên cần hỗ trợ, không cần thêm một mạng xã hội để cảm thấy mình chưa đủ thành công.

---

## 2. Actors

| Actor   | Role                                        |
| ------- | ------------------------------------------- |
| Student | Tìm và gửi request mentor                   |
| Mentor  | Nhận, duyệt, hỗ trợ                         |
| Alumni  | Có thể đóng vai trò mentor                  |
| Advisor | Cố vấn học tập hoặc người hỗ trợ chính thức |
| Admin   | Duyệt mentor, xử lý report                  |

---

## 3. Entry Points

- Nav `Mentor`.
- Profile setup chọn career/learning interest.
- Home feed mentor card.
- Discovery suggestion.
- Search.
- Alumni/mentor profile.
- Notification.

---

## 4. High-level Student Flow

```txt
Student opens Mentor
→ Browse suggested mentors
→ Filter by topic
→ Open mentor profile
→ Send mentor request
→ Wait for response
→ Mentor accepts
→ Conversation opens
→ Student receives guidance
```

## 5. High-level Mentor Flow

Mentor opens request list
→ Reviews request
→ Opens student profile
→ Accepts or declines
→ If accepted, conversation starts
→ Mentor supports student
→ Mark request completed

## 6. Mentor Discovery

Mentor List Must Show
Avatar.
Name.
Role badge: Mentor / Alumni / Advisor.
Expertise.
Faculty/industry if relevant.
Availability.
Short intro.
CTA: Gửi yêu cầu.
Filters

P1:

Lĩnh vực hỗ trợ.
Khoa/ngành.
Mentor type.
Availability.

P2:

Career path.
Company/industry.
Alumni year.
Language.
Response time.

## 7. Mentor Profile

Required Information
Name.
Verified badge.
Mentor role.
Expertise.
Short bio.
Can help with.
Availability.
Guidelines / expectation.
Request CTA.
Avoid
CV quá dài.
Danh sách achievement quá khoe.
Giao diện như tuyển dụng.
CTA như Hire me, Connect professionally.

## 8. Send Mentor Request

Student clicks Gửi yêu cầu
→ Request form opens
→ Student chooses topic
→ Student writes question/context
→ Submit
→ Request pending
Required Fields
Topic.
Short question.
Goal.
Optional context.
Consent to respectful communication.
Validation
Question không được trống.
Question không quá ngắn.
Topic bắt buộc.
User phải verified.

## 9. Request States

State Meaning
Draft Student is writing
Pending Waiting mentor response
Accepted Mentor accepted
Declined Mentor declined
Need more info Mentor asks clarification
Closed Mentoring ended
Reported Safety issue

## 10. Mentor Accept Flow

Mentor sees request
→ Opens request detail
→ Opens student profile if needed
→ Clicks Accept
→ System creates conversation
→ Student notified

## 11. Mentor Decline Flow

Mentor sees request
→ Clicks Decline
→ Optional polite reason
→ Student notified
→ Suggest other mentors
Decline Copy
Mentor hiện chưa thể hỗ trợ yêu cầu này.
Bạn có thể thử gửi yêu cầu đến mentor khác phù hợp hơn.

Không làm student cảm thấy bị từ chối cá nhân. UX tối thiểu cũng nên có chút nhân tính, ngạc nhiên chưa.

## 12. Mentoring Conversation

Mentor conversation dùng Messaging system nhưng có context header:

Topic.
Request status.
Mentor role.
Safety note.
Close request action.

## 13. Alternative Flows

### 13.1. No Mentors Available

No mentor matching
→ Show empty state
→ Suggest broadening filter or saving interest

### 13.2. Mentor Is Full

Mentor availability full
→ Disable request
→ Show "Hiện mentor đang tạm ngưng nhận yêu cầu"

### 13.3. Student Request Rejected

Request declined
→ Notify student
→ Suggest other mentors

### 13.4. Inappropriate Request

Mentor reports request
→ Admin moderation
→ Student may receive warning

## 14. Required Pages

Page Purpose
mentor.md Mentor list/discovery
mentor-profile.md Mentor detail
mentor-request.md Request form/status
conversation.md Mentor conversation
profile.md Student context
safety-reporting.md Report issue
admin/mentor-verification.md Admin approval

## 15. Required Components

Mentor card.
Mentor profile header.
Expertise chips.
Availability badge.
Request form.
Request status card.
Mentor request list.
Conversation context header.
Empty state.
Report modal.

## 16. Required States

State Description
Loading mentors Fetching mentor list
Empty mentors No match
Request draft Form incomplete
Request pending Waiting
Accepted Conversation available
Declined Suggest alternatives
Mentor unavailable Cannot request
Permission denied User not verified
Reported Safety submitted

## 17. Success Metrics

Mentor profile views.
Request submission rate.
Request acceptance rate.
Average mentor response time.
Conversation start after acceptance.
Mentor overload rate.
Report rate in mentor interactions.

## 18. UX Checklist

- Mentor không bị LinkedIn hóa.
- Request form có topic/context rõ.
- Student biết request đang ở trạng thái nào.
- Mentor có quyền từ chối lịch sự.
- Mentor availability rõ.
- Accepted request mở conversation.
- Safety/report có trong mentor flow.
- Empty state gợi ý hành động tiếp theo.
