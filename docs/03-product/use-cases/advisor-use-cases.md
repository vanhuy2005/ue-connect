---
title: "Advisor / Mentor Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
actor: "Advisor / Mentor"
priority: "P1"
last_updated: "2026-05-25"
---

# Advisor / Mentor Use Cases

## 1. Purpose

File này liệt kê use case dành cho `Advisor` và `Mentor`.

Advisor/Mentor có thể là:

- Cố vấn học tập.
- Giảng viên.
- Alumni mentor.
- Senior student mentor.
- Người hỗ trợ định hướng học tập/career.

Mentor là một trụ cột chính của UEConnect, nhưng không được làm sản phẩm bị LinkedIn hóa. Vì sinh viên vào UEConnect để được hỗ trợ phát triển, không phải để bị bắt tối ưu “personal brand” lúc còn chưa kịp qua môn.

---

## 2. Actor Definition

```txt
Actor: Advisor / Mentor
Mô tả: Người có khả năng hỗ trợ sinh viên về học tập, career, kỹ năng, định hướng hoặc cộng đồng.
Mục tiêu: Nhận request phù hợp, hỗ trợ sinh viên, chia sẻ tài nguyên, xây dựng kết nối có giá trị.
```

## 3. Advisor / Mentor Use Case Catalog

### 3.1. Mentor Verification & Setup

| ID          | Use Case                                       | Priority | Page Mapping      | UI States         |
| ----------- | ---------------------------------------------- | -------- | ----------------- | ----------------- |
| ADV-SET-001 | Đăng ký vai trò mentor/advisor                 | P1       | mentor-profile.md | pending           |
| ADV-SET-002 | Cung cấp thông tin chuyên môn                  | P1       | mentor-profile.md | validation        |
| ADV-SET-003 | Cung cấp role: advisor, alumni, senior student | P1       | mentor-profile.md | selected          |
| ADV-SET-004 | Chờ admin duyệt mentor                         | P1       | account-status.md | pending           |
| ADV-SET-005 | Nhận kết quả duyệt mentor                      | P1       | account-status.md | approved/rejected |
| ADV-SET-006 | Bổ sung thông tin khi admin yêu cầu            | P1       | mentor-profile.md | need more info    |
| ADV-SET-007 | Tắt/bật trạng thái nhận request                | P1       | mentor-profile.md | active/paused     |

### 3.2. Mentor Profile

| ID          | Use Case                              | Priority | Page Mapping      | UI States      |
| ----------- | ------------------------------------- | -------- | ----------------- | -------------- |
| ADV-PRO-001 | Tạo mentor profile                    | P1       | mentor-profile.md | saving         |
| ADV-PRO-002 | Cập nhật lĩnh vực hỗ trợ              | P1       | mentor-profile.md | saved          |
| ADV-PRO-003 | Cập nhật kinh nghiệm                  | P1       | mentor-profile.md | saved          |
| ADV-PRO-004 | Cập nhật availability                 | P1       | mentor-profile.md | saved          |
| ADV-PRO-005 | Cập nhật giới hạn số request          | P2       | mentor-profile.md | saved          |
| ADV-PRO-006 | Cập nhật câu hỏi thường gặp           | P2       | mentor-profile.md | saved          |
| ADV-PRO-007 | Preview mentor profile                | P1       | mentor-profile.md | preview        |
| ADV-PRO-008 | Ẩn/hiện mentor profile khỏi discovery | P1       | mentor-profile.md | hidden/visible |

### 3.3. Mentor Request Management

| ID          | Use Case                           | Priority | Page Mapping        | UI States |
| ----------- | ---------------------------------- | -------- | ------------------- | --------- |
| ADV-REQ-001 | Xem danh sách mentor request       | P1       | mentor-request.md   | empty     |
| ADV-REQ-002 | Xem chi tiết request               | P1       | mentor-request.md   | loading   |
| ADV-REQ-003 | Xem profile sinh viên gửi request  | P1       | profile.md          | loading   |
| ADV-REQ-004 | Chấp nhận request                  | P1       | mentor-request.md   | accepted  |
| ADV-REQ-005 | Từ chối request                    | P1       | mentor-request.md   | declined  |
| ADV-REQ-006 | Gửi lý do từ chối lịch sự          | P2       | mentor-request.md   | sent      |
| ADV-REQ-007 | Đánh dấu request cần xem sau       | P2       | mentor-request.md   | saved     |
| ADV-REQ-008 | Lọc request theo chủ đề            | P2       | mentor-request.md   | filtered  |
| ADV-REQ-009 | Tạm ngưng nhận request khi quá tải | P1       | mentor-profile.md   | paused    |
| ADV-REQ-010 | Report request không phù hợp       | P0       | safety-reporting.md | submitted |

### 3.4. Mentoring Interaction

| ID          | Use Case                                 | Priority | Page Mapping        | UI States    |
| ----------- | ---------------------------------------- | -------- | ------------------- | ------------ |
| ADV-INT-001 | Nhắn tin với sinh viên đã được chấp nhận | P1       | conversation.md     | realtime     |
| ADV-INT-002 | Trả lời câu hỏi của sinh viên            | P1       | conversation.md     | sending      |
| ADV-INT-003 | Gửi tài nguyên học tập/career            | P2       | conversation.md     | attachment   |
| ADV-INT-004 | Gợi ý bước tiếp theo                     | P2       | conversation.md     | message      |
| ADV-INT-005 | Lưu ghi chú cá nhân về request           | P2       | mentor-request.md   | private note |
| ADV-INT-006 | Đánh dấu request đã hỗ trợ xong          | P2       | mentor-request.md   | completed    |
| ADV-INT-007 | Kết thúc mentoring thread                | P2       | mentor-request.md   | closed       |
| ADV-INT-008 | Report hành vi không phù hợp             | P0       | safety-reporting.md | submitted    |

### 3.5. Mentor Content Sharing

| ID           | Use Case                               | Priority | Page Mapping        | UI States  |
| ------------ | -------------------------------------- | -------- | ------------------- | ---------- |
| ADV-CONT-001 | Đăng bài chia sẻ insight               | P1       | composer.md         | submitting |
| ADV-CONT-002 | Đăng bài trả lời câu hỏi phổ biến      | P2       | composer.md         | submitting |
| ADV-CONT-003 | Gắn tag mentor/career/learning         | P2       | composer.md         | selected   |
| ADV-CONT-004 | Chỉnh sửa bài mentor                   | P1       | post-detail.md      | editing    |
| ADV-CONT-005 | Xem phản hồi/comment từ sinh viên      | P1       | post-detail.md      | comments   |
| ADV-CONT-006 | Ghim bài mentor quan trọng             | P3       | profile.md          | pinned     |
| ADV-CONT-007 | Report nội dung phản hồi không phù hợp | P0       | safety-reporting.md | submitted  |

### 3.6. Group Mentoring / Events

| ID          | Use Case                         | Priority | Page Mapping | UI States |
| ----------- | -------------------------------- | -------- | ------------ | --------- |
| ADV-EVT-001 | Tạo group mentoring session      | P2       | events.md    | draft     |
| ADV-EVT-002 | Cấu hình số lượng tham gia       | P2       | events.md    | saved     |
| ADV-EVT-003 | Duyệt danh sách đăng ký          | P2       | events.md    | pending   |
| ADV-EVT-004 | Gửi thông báo đến người tham gia | P2       | events.md    | sent      |
| ADV-EVT-005 | Đóng đăng ký session             | P2       | events.md    | closed    |
| ADV-EVT-006 | Chia sẻ tài liệu sau session     | P2       | events.md    | uploaded  |

## 4. Advisor / Mentor MVP/P1 Use Cases

- ADV-SET-001
- ADV-SET-004
- ADV-PRO-001
- ADV-PRO-002
- ADV-PRO-004
- ADV-REQ-001
- ADV-REQ-002
- ADV-REQ-004
- ADV-REQ-005
- ADV-INT-001
- ADV-INT-002
- ADV-CONT-001

## 5. Advisor / Mentor UX Risks

| Risk                             | Prevention                          |
| -------------------------------- | ----------------------------------- |
| Mentor bị LinkedIn hóa           | Giữ tone supportive, không CV-first |
| Mentor quá tải                   | Availability + request limit        |
| Sinh viên gửi request mơ hồ      | Request form có topic/question      |
| Interaction thiếu an toàn        | Report/block trong conversation     |
| Mentor không rõ trách nhiệm      | Guidelines cho mentor               |
| Giảng viên/advisor ngại tham gia | UI đơn giản, không social quá lố    |
