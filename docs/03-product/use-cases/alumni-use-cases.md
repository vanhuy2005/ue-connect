---
title: "Alumni Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
actor: "Alumni"
priority: "P1"
last_updated: "2026-05-25"
---

# Alumni Use Cases

## 1. Purpose

File này liệt kê use case dành cho `Alumni`, tức cựu sinh viên HCMUE.

Alumni có thể tham gia UEConnect với vai trò:

- Người chia sẻ kinh nghiệm.
- Mentor.
- Thành viên cộng đồng HCMUE.
- Người hỗ trợ career/internship một cách có kiểm duyệt.

Alumni giúp UEConnect có chiều sâu growth-oriented, nhưng không được biến product thành LinkedIn mini. Loài người đã có đủ nơi để khoe chức danh rồi.

---

## 2. Actor Definition

```txt
Actor: Alumni
Mô tả: Cựu sinh viên HCMUE đã được xác thực hoặc được admin duyệt.
Mục tiêu: Kết nối lại cộng đồng, mentor sinh viên, chia sẻ kinh nghiệm, hỗ trợ định hướng.
```

## 3. Alumni Use Case Catalog

### 3.1. Alumni Verification

| ID          | Use Case                                     | Priority | Page Mapping      | UI States         |
| ----------- | -------------------------------------------- | -------- | ----------------- | ----------------- |
| ALU-VER-001 | Đăng ký với vai trò alumni                   | P1       | auth.md           | default           |
| ALU-VER-002 | Cung cấp thông tin cựu sinh viên             | P1       | verification.md   | validation        |
| ALU-VER-003 | Upload minh chứng alumni nếu cần             | P1       | verification.md   | uploading         |
| ALU-VER-004 | Chờ admin duyệt alumni                       | P1       | account-status.md | pending           |
| ALU-VER-005 | Nhận kết quả duyệt alumni                    | P1       | account-status.md | approved/rejected |
| ALU-VER-006 | Bổ sung thông tin khi bị yêu cầu             | P1       | verification.md   | need more info    |
| ALU-VER-007 | Chuyển từ student sang alumni sau tốt nghiệp | P2       | settings.md       | review            |

### 3.2. Alumni Profile

| ID          | Use Case                            | Priority | Page Mapping      | UI States |
| ----------- | ----------------------------------- | -------- | ----------------- | --------- |
| ALU-PRO-001 | Tạo alumni profile                  | P1       | profile-setup.md  | saving    |
| ALU-PRO-002 | Thêm ngành học/khoa/khóa tốt nghiệp | P1       | profile-edit.md   | saved     |
| ALU-PRO-003 | Thêm lĩnh vực chuyên môn            | P1       | profile-edit.md   | saved     |
| ALU-PRO-004 | Thêm kinh nghiệm làm việc ngắn gọn  | P1       | profile-edit.md   | saved     |
| ALU-PRO-005 | Thêm mentor availability            | P1       | mentor-profile.md | saved     |
| ALU-PRO-006 | Cập nhật bio alumni                 | P1       | profile-edit.md   | saved     |
| ALU-PRO-007 | Chọn lĩnh vực có thể hỗ trợ         | P1       | mentor-profile.md | saved     |
| ALU-PRO-008 | Preview profile như sinh viên thấy  | P1       | profile.md        | preview   |
| ALU-PRO-009 | Ẩn một số thông tin career          | P2       | privacy.md        | saved     |

### 3.3. Alumni Feed & Content

| ID           | Use Case                       | Priority | Page Mapping        | UI States  | Status     |
| ------------ | ------------------------------ | -------- | ------------------- | ---------- | ---------- |
| ALU-FEED-001 | Xem home feed                  | P1       | home-feed.md        | loading    | built      |
| ALU-FEED-002 | Đăng bài chia sẻ kinh nghiệm   | P1       | home-feed.md        | submitting | built      |
| ALU-FEED-003 | Đăng bài career insight        | P1       | home-feed.md        | submitting | built      |
| ALU-FEED-004 | Bình luận bài viết sinh viên   | P1       | post-detail.md      | submitting | built      |
| ALU-FEED-005 | Trả lời câu hỏi học tập/career | P1       | post-detail.md      | reply      | built      |
| ALU-FEED-006 | Lưu bài viết quan tâm          | P2       | saved-posts.md      | saved      | built      |
| ALU-FEED-007 | Report nội dung không phù hợp  | P0       | safety-reporting.md | submitted  | built      |

### 3.4. Alumni Mentoring

| ID          | Use Case                            | Priority | Page Mapping        | UI States  |
| ----------- | ----------------------------------- | -------- | ------------------- | ---------- |
| ALU-MEN-001 | Bật vai trò mentor                  | P1       | mentor-profile.md   | enabled    |
| ALU-MEN-002 | Nhận mentor request                 | P1       | mentor-request.md   | pending    |
| ALU-MEN-003 | Xem thông tin sinh viên gửi request | P1       | mentor-request.md   | loading    |
| ALU-MEN-004 | Chấp nhận mentor request            | P1       | mentor-request.md   | accepted   |
| ALU-MEN-005 | Từ chối mentor request              | P1       | mentor-request.md   | declined   |
| ALU-MEN-006 | Trả lời câu hỏi mentor              | P1       | conversation.md     | sending    |
| ALU-MEN-007 | Gửi tài nguyên hỗ trợ               | P2       | conversation.md     | attachment |
| ALU-MEN-008 | Tạm dừng nhận request               | P1       | mentor-profile.md   | paused     |
| ALU-MEN-009 | Quản lý số lượng request            | P2       | mentor-dashboard.md | full       |
| ALU-MEN-010 | Report tương tác không phù hợp      | P0       | safety-reporting.md | submitted  |

### 3.5. Alumni Community

| ID          | Use Case                           | Priority | Page Mapping   | UI States      |
| ----------- | ---------------------------------- | -------- | -------------- | -------------- |
| ALU-COM-001 | Tham gia cộng đồng alumni          | P2       | clubs.md       | joined         |
| ALU-COM-002 | Tham gia community theo khoa/ngành | P2       | club-detail.md | joined         |
| ALU-COM-003 | Đăng bài trong alumni community    | P2       | composer.md    | permission     |
| ALU-COM-004 | Tham gia AMA/event                 | P2       | events.md      | registered     |
| ALU-COM-005 | Tạo session chia sẻ                | P3       | events.md      | pending review |
| ALU-COM-006 | Kết nối với alumni khác            | P2       | discovery.md   | connected      |

### 3.6. Opportunity Sharing

| ID          | Use Case                                 | Priority | Page Mapping              | UI States       | Status      |
| ----------- | ---------------------------------------- | -------- | ------------------------- | --------------- | ----------- |
| ALU-OPP-001 | Chia sẻ cơ hội internship/job            | P2       | home-feed.md              | review required | built       |
| ALU-OPP-002 | Gắn tag lĩnh vực cho cơ hội              | P2       | home-feed.md              | selected        | built       |
| ALU-OPP-003 | Cập nhật cơ hội đã đăng                  | P2       | post-detail.md            | editing         | future      |
| ALU-OPP-004 | Đánh dấu cơ hội hết hạn                  | P2       | post-detail.md            | expired         | future      |
| ALU-OPP-005 | Admin duyệt opportunity trước khi public | P2       | admin/moderation-queue.md | pending         | future      |
| ALU-OPP-006 | Trả lời câu hỏi về cơ hội                | P2       | post-detail.md            | comment         | built (via post-comment) |

## 4. Alumni MVP/P1 Use Cases

- ALU-VER-001
- ALU-VER-004
- ALU-PRO-001
- ALU-PRO-003
- ALU-PRO-005
- ALU-FEED-002
- ALU-MEN-001
- ALU-MEN-002
- ALU-MEN-004
- ALU-MEN-005
- ALU-MEN-006

## 5. Alumni UX Risks

| Risk                               | Prevention                        |
| ---------------------------------- | --------------------------------- |
| Alumni profile quá giống LinkedIn  | Giữ bio ngắn, mentor-friendly     |
| Career content biến thành spam job | Cần review/moderation             |
| Sinh viên bị áp lực career         | Tone hỗ trợ, không tuyển dụng hóa |
| Mentor bị quá tải                  | Availability + pause request      |
| Alumni không rõ vai trò            | Badge alumni/mentor rõ            |
