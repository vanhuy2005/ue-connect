---
title: "Use Case Index"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
last_updated: "2026-05-25"
owner: "Product / UX / Frontend Team"
related:
  - "../product-overview.md"
  - "../feature-list.md"
  - "../feature-priority.md"
  - "../../02-requirements/functional-requirements.md"
  - "../../02-requirements/role-permission-matrix.md"
  - "../../02-requirements/traceability-matrix.md"
  - "../user-flow"
  - "../../04-design/page-specs"
---

# Use Case Index

## 1. Purpose

File này là bản tổng hợp toàn bộ use case chính của UEConnect.

Mục tiêu:

- Xác định đầy đủ các nhóm người dùng.
- Liệt kê các hành vi chính của từng nhóm.
- Ưu tiên use case theo MVP, Phase 2, Phase 3.
- Làm nền để thiết kế user flow, information architecture, page specs và UI states.
- Tránh thiết kế page theo cảm tính khi chưa rõ user cần làm gì.

Use case là tầng đứng trước page specs.

```txt
Use case trả lời: User cần làm gì?
Page spec trả lời: Cần màn hình nào để user làm được việc đó?
Component spec trả lời: Cần UI element nào để tạo màn hình đó?
```

Nếu bỏ qua use case, page specs sẽ biến thành danh sách màn hình “nghe có vẻ cần”, một thể loại tài liệu rất thích hợp để gây bug về sau.

---

## 2. Product Scope

UEConnect là verified student social platform cho HCMUE.

Các product pillar chính:

| Pillar            | Mục tiêu                                                    |
| ----------------- | ----------------------------------------------------------- |
| Social Feed       | Sinh viên đăng bài, đọc bài, bình luận, tương tác           |
| Discovery Profile | Khám phá UEers để làm quen, học cùng, kết nối trong trường  |
| Messaging         | Chat cá nhân, hỗ trợ kết nối sau discovery/feed/profile     |
| Community / Club  | Hỗ trợ lớp, CLB, nhóm học tập, cộng đồng                    |
| Mentor            | Kết nối mentor, alumni, cố vấn học tập, career direction    |
| Identity          | Xác thực UEer bằng mã sinh viên, xây dựng profile đáng tin  |
| Safety            | Report, block, moderation, kiểm duyệt nội dung và tài khoản |
| Admin             | Quản lý user, nội dung, xác thực, báo cáo, hệ thống         |

---

## 3. User Roles

UEConnect có các nhóm user chính:

| Role                     |    Priority | Mô tả                                                   |
| ------------------------ | ----------: | ------------------------------------------------------- |
| Student                  |          P0 | Sinh viên HCMUE năm 1–4, user chính của product         |
| Alumni                   |          P1 | Cựu sinh viên, có thể làm mentor hoặc kết nối cộng đồng |
| Advisor / Mentor         |          P1 | Cố vấn học tập, mentor, người hỗ trợ định hướng         |
| Club / Community Manager |          P2 | Người quản lý CLB, lớp, nhóm học tập                    |
| Admin / Moderator        | P0 vận hành | Quản lý xác thực, nội dung, user, report                |
| Guest                    |    P0 entry | Người chưa đăng nhập, cần hiểu product và đăng ký       |

Ghi chú:

- `Student` là persona trung tâm.
- `Admin` quan trọng về vận hành nhưng không phải persona cảm xúc chính.
- `Alumni` và `Advisor/Mentor` quan trọng cho growth-oriented experience.
- `Club/Community` có thể phát triển sau MVP.
- `Guest` cần được define để onboarding không bị mù.

---

## 4. Priority Definition

| Priority | Ý nghĩa                                               |
| -------- | ----------------------------------------------------- |
| P0       | Bắt buộc cho MVP hoặc vận hành cốt lõi                |
| P1       | Quan trọng, nên có sau MVP hoặc trong MVP mở rộng     |
| P2       | Có giá trị, nhưng có thể phát triển sau               |
| P3       | Nice-to-have, không ảnh hưởng core experience ban đầu |

---

## 5. Student Use Case Summary

File chi tiết:

```txt
student-use-cases.md
```

Student là user chính của UEConnect.

### P0 Student Use Cases

| ID      | Use Case                            | Mục tiêu                                   |
| ------- | ----------------------------------- | ------------------------------------------ |
| STU-001 | Đăng ký tài khoản bằng mã sinh viên | Tạo tài khoản verified UEer                |
| STU-002 | Đăng nhập                           | Truy cập vào hệ thống                      |
| STU-003 | Hoàn thiện profile cá nhân          | Xây dựng identity đáng tin                 |
| STU-004 | Xem home feed                       | Đọc bài viết và hoạt động cộng đồng        |
| STU-005 | Tạo bài viết                        | Chia sẻ nội dung học tập/cộng đồng/cá nhân |
| STU-006 | Bình luận bài viết                  | Tham gia thảo luận                         |
| STU-007 | Tương tác bài viết                  | Like, save, share/send                     |
| STU-008 | Khám phá UEers                      | Tìm bạn cùng khoa, cùng môn, cùng sở thích |
| STU-009 | Gửi lời chào / yêu cầu kết nối      | Bắt đầu quan hệ social                     |
| STU-010 | Nhắn tin cá nhân                    | Giao tiếp sau khi kết nối                  |
| STU-011 | Xem profile người khác              | Hiểu người dùng trước khi kết nối          |
| STU-012 | Nhận thông báo                      | Theo dõi tương tác quan trọng              |
| STU-013 | Báo cáo nội dung/người dùng         | Bảo vệ an toàn cộng đồng                   |
| STU-014 | Quản lý quyền riêng tư cơ bản       | Kiểm soát thông tin cá nhân                |

### P1 Student Use Cases

| ID      | Use Case                  | Mục tiêu                             |
| ------- | ------------------------- | ------------------------------------ |
| STU-015 | Tìm mentor                | Nhận hỗ trợ học tập/career           |
| STU-016 | Gửi câu hỏi cho mentor    | Bắt đầu tương tác mentor             |
| STU-017 | Tham gia cộng đồng/CLB    | Kết nối theo nhóm                    |
| STU-018 | Tìm bạn cùng môn học      | Học cùng                             |
| STU-019 | Lưu profile UEer          | Xem lại người quan tâm               |
| STU-020 | Quản lý danh sách kết nối | Theo dõi network cá nhân             |
| STU-021 | Cập nhật career interest  | Cá nhân hóa mentor/career suggestion |

### P2 Student Use Cases

| ID      | Use Case                       | Mục tiêu                          |
| ------- | ------------------------------ | --------------------------------- |
| STU-022 | Tạo nhóm học tập               | Tổ chức học chung                 |
| STU-023 | Đăng ký sự kiện CLB            | Tham gia hoạt động                |
| STU-024 | Chia sẻ tài liệu có kiểm soát  | Hỗ trợ học tập, đảm bảo bản quyền |
| STU-025 | Tạo poll trong cộng đồng       | Tương tác nhóm                    |
| STU-026 | Tùy chỉnh discovery preference | Cá nhân hóa khám phá              |

---

## 6. Alumni Use Case Summary

File chi tiết:

```txt
alumni-use-cases.md
```

### P1 Alumni Use Cases

| ID      | Use Case                           | Mục tiêu                                    |
| ------- | ---------------------------------- | ------------------------------------------- |
| ALU-001 | Đăng ký/xác thực alumni            | Tham gia hệ thống với vai trò cựu sinh viên |
| ALU-002 | Tạo alumni profile                 | Thể hiện ngành, kinh nghiệm, chuyên môn     |
| ALU-003 | Đăng bài chia sẻ kinh nghiệm       | Hỗ trợ sinh viên                            |
| ALU-004 | Trở thành mentor                   | Nhận mentor request                         |
| ALU-005 | Trả lời câu hỏi của sinh viên      | Hỗ trợ học tập/career                       |
| ALU-006 | Nhắn tin với sinh viên được phép   | Giao tiếp có kiểm soát                      |
| ALU-007 | Tham gia community theo khoa/ngành | Kết nối cộng đồng cựu sinh viên             |

### P2 Alumni Use Cases

| ID      | Use Case                        | Mục tiêu                      |
| ------- | ------------------------------- | ----------------------------- |
| ALU-008 | Chia sẻ cơ hội internship/job   | Hỗ trợ career, cần kiểm duyệt |
| ALU-009 | Tổ chức AMA/session             | Kết nối theo sự kiện          |
| ALU-010 | Đánh dấu lĩnh vực có thể mentor | Cá nhân hóa mentor matching   |

---

## 7. Advisor / Mentor Use Case Summary

File chi tiết:

```txt
advisor-use-cases.md
```

Advisor/Mentor có thể là cố vấn học tập, giảng viên, alumni mentor hoặc senior student mentor.

### P1 Advisor Use Cases

| ID      | Use Case                               | Mục tiêu                       |
| ------- | -------------------------------------- | ------------------------------ |
| ADV-001 | Tạo mentor/advisor profile             | Công khai lĩnh vực hỗ trợ      |
| ADV-002 | Quản lý availability                   | Cho biết khi nào có thể hỗ trợ |
| ADV-003 | Nhận mentor request                    | Tiếp nhận yêu cầu từ sinh viên |
| ADV-004 | Chấp nhận/từ chối request              | Kiểm soát workload             |
| ADV-005 | Trả lời câu hỏi                        | Hỗ trợ sinh viên               |
| ADV-006 | Gửi tài nguyên định hướng              | Chia sẻ link, note, checklist  |
| ADV-007 | Xem profile sinh viên trước khi hỗ trợ | Có ngữ cảnh tư vấn             |

### P2 Advisor Use Cases

| ID      | Use Case                    | Mục tiêu                   |
| ------- | --------------------------- | -------------------------- |
| ADV-008 | Tạo bài viết mentor insight | Chia sẻ kinh nghiệm        |
| ADV-009 | Tổ chức group mentoring     | Hỗ trợ nhiều sinh viên     |
| ADV-010 | Theo dõi lịch sử tư vấn     | Continuity trong mentoring |

---

## 8. Club / Community Use Case Summary

File chi tiết:

```txt
club-community-use-cases.md
```

### P2 Club / Community Use Cases

| ID      | Use Case                  | Mục tiêu                 |
| ------- | ------------------------- | ------------------------ |
| COM-001 | Tạo trang CLB/cộng đồng   | Giới thiệu nhóm          |
| COM-002 | Quản lý thành viên        | Thêm/xóa/phân quyền      |
| COM-003 | Đăng bài cộng đồng        | Thông báo hoặc thảo luận |
| COM-004 | Tạo kênh/chủ đề thảo luận | Tổ chức nội dung         |
| COM-005 | Ghim bài quan trọng       | Giữ thông tin chính      |
| COM-006 | Tạo sự kiện               | Mời sinh viên tham gia   |
| COM-007 | Kiểm duyệt nội dung nhóm  | Giữ cộng đồng an toàn    |
| COM-008 | Chat nhóm                 | Trao đổi realtime        |

### P3 Club / Community Use Cases

| ID      | Use Case                      | Mục tiêu           |
| ------- | ----------------------------- | ------------------ |
| COM-009 | Role nâng cao trong community | Admin, mod, member |
| COM-010 | Poll / vote                   | Lấy ý kiến nhóm    |
| COM-011 | Resource library              | Lưu tài liệu nhóm  |
| COM-012 | Attendance / event check-in   | Quản lý sự kiện    |

---

## 9. Admin / Moderator Use Case Summary

File chi tiết:

```txt
admin-use-cases.md
```

Admin không phải persona chính về cảm xúc, nhưng là role quan trọng để product vận hành an toàn.

### P0 Admin Use Cases

| ID      | Use Case                           | Mục tiêu                                     |
| ------- | ---------------------------------- | -------------------------------------------- |
| ADM-001 | Duyệt tài khoản mới                | Đảm bảo verified UEer                        |
| ADM-002 | Kiểm tra mã sinh viên              | Ngăn tài khoản giả                           |
| ADM-003 | Quản lý user                       | Xem, khóa, mở khóa, cập nhật trạng thái      |
| ADM-004 | Xử lý report                       | Kiểm duyệt nội dung/người dùng bị báo cáo    |
| ADM-005 | Ẩn/xóa nội dung vi phạm            | Giữ cộng đồng an toàn                        |
| ADM-006 | Quản lý appeal                     | Cho user khiếu nại quyết định                |
| ADM-007 | Xem moderation queue               | Theo dõi việc cần xử lý                      |
| ADM-008 | Quản lý role                       | Student, alumni, mentor, club manager, admin |
| ADM-009 | Audit log                          | Lưu lịch sử thao tác quan trọng              |
| ADM-010 | Quản lý policy/community guideline | Cập nhật luật cộng đồng                      |

### P1 Admin Use Cases

| ID      | Use Case                    | Mục tiêu                     |
| ------- | --------------------------- | ---------------------------- |
| ADM-011 | Quản lý club/community      | Duyệt cộng đồng              |
| ADM-012 | Quản lý mentor verification | Duyệt mentor/alumni          |
| ADM-013 | Dashboard thống kê          | Theo dõi health của platform |
| ADM-014 | Content keyword flagging    | Phát hiện nội dung rủi ro    |
| ADM-015 | Quản lý thông báo hệ thống  | Gửi thông báo quan trọng     |

---

## 10. Guest Use Case Summary

File chi tiết:

```txt
guest-use-cases.md
```

Guest là người chưa đăng nhập hoặc chưa có tài khoản.

### P0 Guest Use Cases

| ID      | Use Case                           | Mục tiêu                 |
| ------- | ---------------------------------- | ------------------------ |
| GST-001 | Xem landing/onboarding             | Hiểu UEConnect là gì     |
| GST-002 | Bắt đầu đăng ký                    | Tạo tài khoản            |
| GST-003 | Hiểu yêu cầu xác thực mã sinh viên | Biết vì sao cần verified |
| GST-004 | Đăng nhập                          | Truy cập hệ thống        |
| GST-005 | Xem guideline/privacy cơ bản       | Tăng trust trước signup  |

### P1 Guest Use Cases

| ID      | Use Case                            | Mục tiêu                               |
| ------- | ----------------------------------- | -------------------------------------- |
| GST-006 | Quên mật khẩu                       | Khôi phục tài khoản                    |
| GST-007 | Kiểm tra trạng thái duyệt tài khoản | Biết account đang pending hay rejected |
| GST-008 | Liên hệ hỗ trợ                      | Xử lý vấn đề đăng ký                   |

---

## 11. Use Case to Page Mapping

Bảng này giúp chuyển từ use case sang page specs.

| Use Case Group                | Required Page Specs                                               |
| ----------------------------- | ----------------------------------------------------------------- |
| Signup / Login / Verification | `onboarding.md`, `auth.md`, `verification.md`                     |
| Home Feed                     | `home-feed.md`, `post-detail.md`, `composer.md`                   |
| Discovery                     | `discovery.md`, `discovery-profile-detail.md`                     |
| Profile                       | `profile.md`, `profile-edit.md`, `profile-setup.md`               |
| Messaging                     | `messaging.md`, `conversation.md`                                 |
| Notification                  | `notifications.md`                                                |
| Mentor                        | `mentor.md`, `mentor-profile.md`, `mentor-request.md`             |
| Community / Club              | `clubs.md`, `club-detail.md`, `community-chat.md`                 |
| Settings / Privacy            | `settings.md`, `privacy.md`, `account.md`                         |
| Safety / Report               | `safety-reporting.md`, `report-flow.md`, `blocked-users.md`       |
| Admin / Moderation            | `admin-dashboard.md`, `moderation-queue.md`, `user-management.md` |

---

## 12. Suggested Page Specs Inventory

Dựa trên use cases, `page-specs/` nên có các file sau.

### P0 Page Specs

```txt
page-specs/
├── page-spec-index.md
├── onboarding.md
├── auth.md
├── verification.md
├── profile-setup.md
├── home-feed.md
├── post-detail.md
├── composer.md
├── discovery.md
├── profile.md
├── messaging.md
├── conversation.md
├── notifications.md
├── settings.md
├── privacy.md
├── safety-reporting.md
```

### P1 Page Specs

```txt
page-specs/
├── mentor.md
├── mentor-profile.md
├── mentor-request.md
├── alumni-profile.md
├── saved-profiles.md
├── connection-management.md
├── account-status.md
```

### P2 Page Specs

```txt
page-specs/
├── clubs.md
├── club-detail.md
├── community-chat.md
├── community-channel.md
├── events.md
├── resource-library.md
```

### Admin Page Specs

```txt
page-specs/admin/
├── admin-dashboard.md
├── moderation-queue.md
├── user-management.md
├── account-verification-review.md
├── report-detail.md
├── role-management.md
├── audit-log.md
├── system-announcements.md
```

---

## 13. Use Case Detail Template

Mỗi use case chi tiết nên dùng format sau:

```md
## UC-ID — Use Case Name

### Priority

P0 / P1 / P2 / P3

### Actor

Ai thực hiện use case này.

### Goal

User muốn đạt điều gì.

### Trigger

Điều gì khiến user bắt đầu use case.

### Preconditions

Điều kiện cần có trước khi use case xảy ra.

### Main Flow

1. User ...
2. System ...
3. User ...
4. System ...

### Alternative Flows

- Nếu user chưa xác thực...
- Nếu hệ thống lỗi...
- Nếu không có dữ liệu...

### Error / Edge Cases

- Network error
- Permission denied
- Invalid input
- Account pending
- Content moderated

### Required UI

- Page
- Component
- State

### Data Needed

- User data
- Post data
- Profile data
- Verification data

### Acceptance Criteria

- [ ] ...
- [ ] ...
```

---

## 14. Enterprise Use Case Rules

Khi viết use case cho UEConnect, luôn kiểm tra:

- Use case có actor rõ không?
- Use case có goal rõ không?
- Có priority không?
- Có main flow không?
- Có alternative flow không?
- Có error/edge case không?
- Có mapping sang page/component/state không?
- Có liên quan đến safety/privacy không?
- Có phù hợp với brand attributes không?
- Có tránh dating language không?

---

## 15. MVP Use Case Set

MVP tối thiểu nên gồm:

```txt
STU-001 Đăng ký tài khoản bằng mã sinh viên
STU-002 Đăng nhập
STU-003 Hoàn thiện profile cá nhân
STU-004 Xem home feed
STU-005 Tạo bài viết
STU-006 Bình luận bài viết
STU-007 Tương tác bài viết
STU-008 Khám phá UEers
STU-009 Gửi lời chào / yêu cầu kết nối
STU-010 Nhắn tin cá nhân
STU-011 Xem profile người khác
STU-012 Nhận thông báo
STU-013 Báo cáo nội dung/người dùng
ADM-001 Duyệt tài khoản mới
ADM-004 Xử lý report
ADM-005 Ẩn/xóa nội dung vi phạm
GST-001 Xem onboarding
GST-002 Bắt đầu đăng ký
GST-003 Hiểu yêu cầu xác thực mã sinh viên
```

Nếu MVP không có những use case này, product sẽ thiếu xương sống. Và product thiếu xương sống thì chỉ còn lại một landing page biết giả vờ là app.

---
