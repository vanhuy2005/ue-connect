---
title: "Student Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
actor: "Student"
priority: "P0"
---

# Student Use Cases

## 1. Purpose

File này mô tả toàn bộ use case dành cho **Student**, actor chính của UEConnect.

Student là người dùng trung tâm của product. Mọi tính năng lớn như feed, discovery, messaging, mentor, profile, community đều phải phục vụ hành trình của sinh viên HCMUE trước tiên.

UEConnect không chỉ giúp sinh viên “đăng bài”. Product phải giúp sinh viên:

- Xác thực danh tính HCMUE.
- Xây dựng profile cá nhân.
- Tìm bạn cùng khoa, cùng lớp, cùng môn.
- Chia sẻ bài viết và thảo luận.
- Khám phá UEers khác.
- Kết nối, gửi lời chào, nhắn tin.
- Tìm mentor / alumni.
- Tham gia cộng đồng học tập, CLB.
- Kiểm soát quyền riêng tư và an toàn.

---

## 2. Actor Definition

| Field        | Description                                          |
| ------------ | ---------------------------------------------------- |
| Actor        | Student                                              |
| Main users   | Sinh viên HCMUE năm 1, 2, 3, 4                       |
| Trust source | Mã sinh viên                                         |
| Account rule | Mỗi mã sinh viên chỉ có một tài khoản                |
| Verification | Bắt buộc                                             |
| Primary goal | Kết nối, học tập, khám phá UEers, tham gia cộng đồng |
| UX priority  | Mobile-first, social-first, verified-first           |

---

## 3. Priority Groups

| Priority | Meaning                           |
| -------- | --------------------------------- |
| P0       | Bắt buộc cho MVP                  |
| P1       | Quan trọng cho product hoàn chỉnh |
| P2       | Có giá trị, phát triển sau        |
| P3       | Nice-to-have                      |

---

# 4. P0 — Core Student Use Cases

## STU-001 — Đăng ký tài khoản bằng mã sinh viên

### Priority

P0

### Goal

Student tạo tài khoản UEConnect với danh tính HCMUE đã xác thực.

### Trigger

Student mở app lần đầu và chọn “Tạo tài khoản”.

### Preconditions

- Student có mã sinh viên hợp lệ.
- Student có email hoặc số điện thoại để nhận xác minh.
- Hệ thống có rule kiểm tra mã sinh viên.

### Main Flow

1. Student chọn “Tạo tài khoản”.
2. System hiển thị form đăng ký.
3. Student nhập mã sinh viên, họ tên, email/số điện thoại, mật khẩu.
4. System kiểm tra định dạng mã sinh viên.
5. System kiểm tra mã sinh viên đã tồn tại chưa.
6. Student xác nhận thông tin.
7. System tạo account ở trạng thái `pending_verification` hoặc `verified` tùy cơ chế.
8. System chuyển sang onboarding/profile setup.

### Alternative Flows

- Mã sinh viên đã tồn tại.
- Mã sinh viên không hợp lệ.
- Email/số điện thoại đã được dùng.
- Cần admin duyệt thủ công.
- User thoát giữa chừng.

### Required UI

- Register page
- Student ID input
- Verification explanation panel
- Error message
- Pending verification state
- Privacy note

### Acceptance Criteria

- [ ] Mã sinh viên là required field.
- [ ] Một mã sinh viên không thể tạo nhiều tài khoản.
- [ ] User hiểu vì sao cần xác thực.
- [ ] Error message rõ ràng, không chung chung.
- [ ] Có trạng thái pending nếu cần admin duyệt.

---

## STU-002 — Đăng nhập

### Priority

P0

### Goal

Student đăng nhập vào UEConnect.

### Main Flow

1. Student mở login page.
2. Student nhập email/mã sinh viên và mật khẩu.
3. System xác thực credentials.
4. System kiểm tra account status.
5. Nếu hợp lệ, student vào home feed.

### Alternative Flows

- Sai mật khẩu.
- Account chưa verified.
- Account bị khóa.
- Account bị pending review.
- Quên mật khẩu.

### Required UI

- Login page
- Error state
- Forgot password link
- Account status message

### Acceptance Criteria

- [ ] Có thông báo rõ khi account pending.
- [ ] Có route quên mật khẩu.
- [ ] Không tiết lộ quá nhiều thông tin bảo mật.
- [ ] Login form dùng keyboard tốt trên mobile.

---

## STU-003 — Hoàn thiện profile cá nhân

### Priority

P0

### Goal

Student tạo profile đủ tin cậy và có cá tính để người khác hiểu mình.

### Main Flow

1. Student vào profile setup.
2. System hiển thị các step cần hoàn thành.
3. Student thêm avatar.
4. Student nhập bio ngắn.
5. Student chọn khoa/ngành/lớp/khóa.
6. Student thêm sở thích học tập, môn quan tâm, CLB nếu có.
7. Student chọn mục tiêu: kết bạn, học cùng, mentor, career.
8. System lưu profile.

### Required UI

- Profile setup wizard
- Progress indicator
- Avatar uploader
- Bio field
- Faculty/class/cohort selector
- Interest chips
- Privacy note

### Acceptance Criteria

- [ ] Không bắt user nhập quá nhiều ngay lần đầu.
- [ ] Profile có thể lưu draft.
- [ ] Có progress rõ.
- [ ] Có thông tin nào public/private rõ ràng.
- [ ] Không tạo cảm giác CV hay dating profile.

---

## STU-004 — Xem home feed

### Priority

P0

### Goal

Student xem bài viết mới từ UEers, cộng đồng, mentor, CLB.

### Main Flow

1. Student vào home.
2. System load feed.
3. Student scroll feed.
4. Student đọc post.
5. Student tương tác hoặc mở post detail.

### Feed Sources

- Bài viết từ UEers đã kết nối.
- Bài viết public trong HCMUE.
- Bài viết từ cộng đồng/CLB đã tham gia.
- Bài mentor/alumni được đề xuất.
- Bài được ghim hoặc thông báo chính thức nếu có.

### Required UI

- Home feed page
- Feed tabs
- Post card/feed item
- Skeleton loading
- Empty feed state
- Error reload state

### Acceptance Criteria

- [ ] Feed đọc tốt trên mobile.
- [ ] Post author identity rõ.
- [ ] Có verified badge nếu user verified.
- [ ] Có loading state.
- [ ] Có empty state nếu chưa có nội dung.

---

## STU-005 — Tạo bài viết

### Priority

P0

### Goal

Student đăng bài để chia sẻ suy nghĩ, học tập, câu hỏi, kinh nghiệm hoặc hoạt động.

### Main Flow

1. Student chọn “Tạo bài”.
2. System mở composer.
3. Student nhập nội dung.
4. Student có thể thêm ảnh/link/tag.
5. Student chọn visibility.
6. Student bấm đăng.
7. System validate nội dung.
8. System tạo post và đưa vào feed.

### Post Types

- Text post
- Image post
- Question post
- Experience sharing
- Club/community post
- Mentor/career question

### Required UI

- Composer
- Textarea
- Media upload
- Visibility selector
- Character counter nếu cần
- Submit loading
- Success toast
- Error state

### Acceptance Criteria

- [ ] Không cho đăng post rỗng.
- [ ] Có loading khi submit.
- [ ] Có error nếu upload lỗi.
- [ ] Có visibility rõ.
- [ ] Có moderation hook nếu nội dung vi phạm.

---

## STU-006 — Bình luận bài viết

### Priority

P0

### Goal

Student tham gia thảo luận trong post.

### Main Flow

1. Student mở post hoặc comment area.
2. Student nhập comment.
3. Student gửi comment.
4. System hiển thị comment mới.
5. Người khác có thể reply hoặc react.

### Required UI

- Comment input
- Comment list
- Reply thread
- Loading state
- Deleted/moderated comment state

### Acceptance Criteria

- [ ] Comment dễ đọc.
- [ ] Reply hierarchy không rối.
- [ ] Có report comment.
- [ ] Có state comment bị ẩn/xóa.
- [ ] Mobile input không bị keyboard che.

---

## STU-007 — Tương tác bài viết

### Priority

P0

### Actions

- Like
- Comment
- Save
- Share/send
- Report
- Hide post

### Required UI

- Post action row
- Icon button
- Count label
- Hover/focus/active state
- Mobile touch target

### Acceptance Criteria

- [ ] Action icon tối thiểu 44px touch target.
- [ ] Active state rõ nhưng không quá màu mè.
- [ ] Report không bị giấu quá sâu.
- [ ] Không dùng quá nhiều màu cho từng action.

---

## STU-008 — Khám phá UEers

### Priority

P0

### Goal

Student khám phá những UEers phù hợp để làm quen, học cùng, kết nối.

### Main Flow

1. Student vào Discovery.
2. System hiển thị profile đề xuất.
3. Student xem thông tin cơ bản.
4. Student có thể gửi lời chào, bỏ qua, lưu hồ sơ, xem chi tiết.
5. System cập nhật suggestion.

### Matching Signals

- Cùng khoa.
- Cùng khóa.
- Cùng môn quan tâm.
- Cùng CLB.
- Cùng mục tiêu học tập.
- Mentor/career interest.
- Mutual connections.

### Required UI

- Discovery page
- Discovery profile card
- Profile detail drawer/page
- Filter
- Empty suggestion state

### Acceptance Criteria

- [ ] Không dùng dating language.
- [ ] Không dùng “match”, “swipe”, “crush”.
- [ ] Profile có đủ context học tập.
- [ ] CTA là “Gửi lời chào”, “Kết nối”, “Lưu hồ sơ”.
- [ ] User có thể report/block từ discovery.

---

## STU-009 — Gửi lời chào / yêu cầu kết nối

### Priority

P0

### Goal

Student bắt đầu kết nối với UEer khác.

### Main Flow

1. Student xem profile UEer.
2. Student bấm “Gửi lời chào”.
3. System có thể mở optional message.
4. Student gửi.
5. Receiver nhận notification.
6. Request ở trạng thái pending.

### States

- Not connected
- Request sent
- Request accepted
- Request declined
- Blocked
- Cannot connect

### Required UI

- Connect button
- Optional message modal
- Request status badge
- Notification

### Acceptance Criteria

- [ ] Không dùng từ “match”.
- [ ] Có pending state.
- [ ] Không gửi request spam liên tục.
- [ ] Có block/report.

---

## STU-010 — Nhắn tin cá nhân

### Priority

P0

### Goal

Student nhắn tin với UEer khác.

### Main Flow

1. Student mở Messages.
2. Student chọn conversation.
3. Student nhập tin nhắn.
4. System gửi realtime.
5. Receiver nhận message.

### Required UI

- Inbox list
- Conversation screen
- Message bubble
- Message input
- Sending state
- Failed state
- Seen/read state nếu có

### Acceptance Criteria

- [ ] Chat realtime hoặc gần realtime.
- [ ] Có failed message state.
- [ ] Có block/report.
- [ ] Không dùng gradient cho bubble.
- [ ] Own message dùng brand blue, other message dùng neutral.

---

## STU-011 — Xem profile người khác

### Priority

P0

### Goal

Student xem thông tin một UEer trước khi kết nối.

### Required Sections

- Avatar/cover
- Name + verified
- Faculty / cohort / major
- Bio
- Interests
- Communities
- Mutual context
- Recent posts
- Actions: gửi lời chào, nhắn tin, lưu, report

### Acceptance Criteria

- [ ] Có trust signal.
- [ ] Có đủ thông tin social và học tập.
- [ ] Không giống dating profile.
- [ ] Không giống CV khô khan.
- [ ] Có privacy-aware fields.

---

## STU-012 — Nhận thông báo

### Priority

P0

### Notification Types

- New connect request
- Accepted request
- New message
- Comment/reply
- Mention
- Mentor response
- Community update
- Report/moderation result
- Account verification result

### Acceptance Criteria

- [ ] Notification có grouping nếu nhiều.
- [ ] Có read/unread state.
- [ ] Click notification mở đúng context.
- [ ] Có empty state.
- [ ] Có settings sau này.

---

## STU-013 — Báo cáo nội dung/người dùng

### Priority

P0

### Goal

Student báo cáo nội dung hoặc người dùng vi phạm.

### Report Targets

- Post
- Comment
- Profile
- Message
- Community
- Club event

### Report Reasons

- Spam
- Harassment
- Fake account
- Inappropriate content
- Copyright issue
- Impersonation
- Safety concern
- Other

### Acceptance Criteria

- [ ] Report accessible từ post/profile/message.
- [ ] Có reason selector.
- [ ] Có optional description.
- [ ] Có confirmation.
- [ ] User biết report đã gửi.
- [ ] Không expose reporter identity.

---

## STU-014 — Quản lý quyền riêng tư cơ bản

### Priority

P0

### Controls

- Ai xem profile.
- Ai gửi lời chào.
- Ai nhắn tin.
- Hiển thị khoa/lớp/khóa.
- Hiển thị online status.
- Blocked users.
- Account visibility.

### Acceptance Criteria

- [ ] Privacy settings dễ hiểu.
- [ ] Default an toàn.
- [ ] Không public mã sinh viên đầy đủ nếu không cần.
- [ ] Có blocked list.

---

# 5. P1 — Growth Student Use Cases

## STU-015 — Tìm mentor

### Goal

Student tìm mentor phù hợp với mục tiêu học tập/career.

### Filters

- Khoa/ngành
- Lĩnh vực chuyên môn
- Alumni/senior/teacher/advisor
- Availability
- Career interest
- Rating/feedback nếu có sau này

### Acceptance Criteria

- [ ] Mentor không bị trình bày như recruiter.
- [ ] Có trust signal.
- [ ] Có CTA “Gửi câu hỏi” hoặc “Yêu cầu mentor”.

---

## STU-016 — Gửi câu hỏi cho mentor

### Goal

Student gửi câu hỏi học tập/career cho mentor.

### Required UI

- Question composer
- Topic selector
- Context note
- Submit state
- Pending response state

### Acceptance Criteria

- [ ] Student biết câu hỏi đang chờ phản hồi.
- [ ] Mentor có quyền accept/reject.
- [ ] Có safety/report nếu cần.

---

## STU-017 — Tham gia cộng đồng/CLB

### Goal

Student tham gia CLB hoặc community.

### Acceptance Criteria

- [ ] Có join/request join.
- [ ] Có community rule.
- [ ] Có member list.
- [ ] Có post/chat tùy phase.

---

## STU-018 — Tìm bạn cùng môn học

### Goal

Student tìm người cùng học môn hoặc cùng mục tiêu.

### Acceptance Criteria

- [ ] Có filter theo môn.
- [ ] Có suggestion.
- [ ] Có CTA gửi lời chào.
- [ ] Không biến thành dating discovery.

---

## STU-019 — Lưu profile UEer

### Goal

Student lưu profile để xem lại.

### Acceptance Criteria

- [ ] Có saved list.
- [ ] Có remove saved.
- [ ] User được thông báo lưu thành công.
- [ ] Không gửi notification cho người bị lưu nếu không cần.

---

## STU-020 — Quản lý danh sách kết nối

### Goal

Student xem và quản lý network cá nhân.

### Actions

- View connections
- Remove connection
- Message
- Block
- Search connection

---

## STU-021 — Cập nhật career interest

### Goal

Student cập nhật lĩnh vực quan tâm để nhận mentor/career suggestion.

### Acceptance Criteria

- [ ] Không làm UI quá LinkedIn.
- [ ] Có thể skip.
- [ ] Dùng chip/tags dễ chọn.

---

# 6. P2 — Extended Student Use Cases

| ID      | Use Case                       | Description                                |
| ------- | ------------------------------ | ------------------------------------------ |
| STU-022 | Tạo nhóm học tập               | Tạo group nhỏ theo môn/lớp                 |
| STU-023 | Đăng ký sự kiện CLB            | Join event                                 |
| STU-024 | Chia sẻ tài liệu có kiểm soát  | Upload/link tài liệu, cần copyright policy |
| STU-025 | Tạo poll                       | Vote trong cộng đồng                       |
| STU-026 | Tùy chỉnh discovery preference | Chọn ưu tiên khám phá                      |
| STU-027 | Tìm sự kiện                    | Browse event                               |
| STU-028 | Theo dõi mentor/alumni         | Follow content                             |
| STU-029 | Đặt lịch mentor                | Scheduling                                 |
| STU-030 | Export/delete account data     | Privacy compliance                         |

---

# 7. Student Page Mapping

| Use Case          | Page Specs                                                |
| ----------------- | --------------------------------------------------------- |
| STU-001 → STU-003 | onboarding.md, auth.md, verification.md, profile-setup.md |
| STU-004 → STU-007 | home-feed.md, composer.md, post-detail.md                 |
| STU-008 → STU-011 | discovery.md, profile.md                                  |
| STU-010           | messaging.md, conversation.md                             |
| STU-012           | notifications.md                                          |
| STU-013           | safety-reporting.md                                       |
| STU-014           | settings.md, privacy.md                                   |
| STU-015 → STU-016 | mentor.md, mentor-profile.md, mentor-request.md           |
| STU-017           | clubs.md, club-detail.md                                  |

# 8. Edge Cases & Error Handling

- Student nhập mã sinh viên không hợp lệ hoặc đã được dùng: hiển thị lỗi rõ ràng và hướng dẫn liên hệ support.
- Sinh viên mất kết nối trong quá trình upload ảnh/profile setup: cho phép resume hoặc lưu draft cục bộ.
- Bị khóa do moderation: hiển thị trạng thái và hướng dẫn kháng nghị.
- Nhiều yêu cầu kết nối/nhắn tin spam: throttle hành vi và yêu cầu friction (captcha/rate limit).
- User cố tình đăng nội dung vi phạm bản quyền: cho moderation workflow và flag automated detection.

# 9. Metrics & Success Criteria

Gợi ý các chỉ số để đo hiệu quả của các use case chính (đội product fill số cụ thể):

- Activation (onboarding completion rate): % người hoàn tất profile trong 7 ngày.
- Signup verification rate: % tài khoản xác thực thành công trên tổng đăng ký.
- DAU/MAU: chỉ số engagement cơ bản.
- Feed engagement: lượt tương tác (like/comment/share) trên mỗi active user / ngày.
- Message delivery SLA: % message delivered < 5s.
- Report handling SLA: thời gian trung bình để xử lý report trong hours.
- Retention W30: % người còn hoạt động sau 30 ngày.

# 10. Accessibility & Localization

- Tất cả UI phải tuân thủ WCAG 2.1 AA ở mức khả năng tiếp cận cơ bản: contrast, keyboard navigation, focus state.
- Forms (signup, verification, composer) cần label rõ ràng, aria hỗ trợ và lỗi có giải pháp rõ ràng.
- Hỗ trợ tiếng Việt chuẩn (locale `vi-VN`) với copy ngắn gọn, không dùng từ chuyên môn khó hiểu.
- Kế hoạch localization cho tương lai: resource file per locale, ngày cập nhật nội dung.

# 11. Data Privacy & Retention

- Không lưu trữ mã sinh viên nguyên vẹn ở public profiles; chỉ hiển thị tín hiệu đã verified.
- Lưu trữ logs và message metadata theo chính sách retention (ví dụ: 1 năm), nội dung message có thể có retention khác (review với legal).
- Cung cấp flow export/delete data theo yêu cầu (STU-XXX), include personal data và activity.
- Mã hóa truyền tải (TLS) và lưu trữ nhạy cảm theo chuẩn.

# 12. Operational Considerations

- Moderation: quy trình báo cáo → triage → action → feedback cho reporter.
- Support & escalation: contact point cho verification failures và appeals.
- Monitoring: alert nếu signup spike, verification failure rate tăng, hoặc message delivery SLA bị tụt.

# 13. Open Questions / Decisions Needed

- Canonical display of verification: hiển thị "Verified" badge hay chi tiết (ví dụ: faculty) — product quyết định.
- Throttling policy cho messaging/connection requests: numeric limits cần định nghĩa.
- Retention periods for messages and reports: cần alignment với legal.

# 14. How to use this document

- Authoritative source for `Student` use cases: product + UX owners maintain and update.
- When creating a page spec or component spec, reference the specific `STU-` IDs in this file to ensure traceability.
- Use these use cases to drive acceptance criteria in tickets and QA test cases.
