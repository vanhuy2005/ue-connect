---
title: "Admin Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
actor: "Admin / Moderator"
priority: "P0 Operations"
last_updated: "2026-05-25"
---

# Admin Use Cases

## 1. Purpose

File này liệt kê toàn bộ use case dành cho `Admin` và `Moderator`.

Admin không phải persona cảm xúc chính của UEConnect, nhưng là role bắt buộc để hệ thống vận hành an toàn, đáng tin và có khả năng mở rộng.

Nếu Student là trái tim của product, Admin là hệ miễn dịch. Không có hệ miễn dịch thì social platform rất nhanh biến thành cái chợ spam có avatar.

---

## 2. Actor Definition

```txt
Actor: Admin / Moderator
Mô tả: Người quản trị hệ thống, kiểm duyệt tài khoản, nội dung, report, role và community.
Mục tiêu: Đảm bảo UEConnect an toàn, đáng tin, đúng scope HCMUE.
```

## 3. Admin Use Case Catalog

### 3.1. Admin Authentication & Access Control

| ID           | Use Case                      | Priority | Page Mapping             | UI States      |
| ------------ | ----------------------------- | -------- | ------------------------ | -------------- |
| ADM-AUTH-001 | Đăng nhập admin panel         | P0       | admin/auth.md            | loading, error |
| ADM-AUTH-002 | Đăng xuất admin               | P0       | admin/auth.md            | confirm        |
| ADM-AUTH-003 | Phân quyền admin/moderator    | P0       | admin/role-management.md | saved          |
| ADM-AUTH-004 | Kiểm tra quyền truy cập       | P0       | permission-states.md     | denied         |
| ADM-AUTH-005 | Xem lịch sử đăng nhập admin   | P1       | admin/audit-log.md       | empty          |
| ADM-AUTH-006 | Bật/tắt tài khoản admin       | P1       | admin/role-management.md | confirm        |
| ADM-AUTH-007 | Quản lý role-based permission | P0       | admin/role-management.md | updated        |

### 3.2. Account Verification

| ID          | Use Case                          | Priority | Page Mapping                         | UI States      |
| ----------- | --------------------------------- | -------- | ------------------------------------ | -------------- |
| ADM-VER-001 | Xem danh sách tài khoản chờ duyệt | P0       | admin/account-verification-review.md | empty, loading |
| ADM-VER-002 | Xem chi tiết hồ sơ xác thực       | P0       | admin/account-verification-detail.md | loading        |
| ADM-VER-003 | Kiểm tra mã sinh viên             | P0       | admin/account-verification-detail.md | valid/invalid  |
| ADM-VER-004 | Duyệt tài khoản student           | P0       | admin/account-verification-detail.md | approved       |
| ADM-VER-005 | Từ chối tài khoản                 | P0       | admin/account-verification-detail.md | rejected       |
| ADM-VER-006 | Yêu cầu bổ sung thông tin         | P0       | admin/account-verification-detail.md | need more info |
| ADM-VER-007 | Duyệt lại tài khoản bị reject     | P1       | admin/account-verification-review.md | reopened       |
| ADM-VER-008 | Phát hiện mã sinh viên trùng      | P0       | admin/account-verification-detail.md | duplicate      |
| ADM-VER-009 | Khóa tài khoản nghi ngờ giả mạo   | P0       | admin/user-management.md             | suspended      |
| ADM-VER-010 | Ghi chú nội bộ khi duyệt          | P1       | admin/account-verification-detail.md | saved          |
| ADM-VER-011 | Lọc queue theo trạng thái         | P0       | admin/account-verification-review.md | filtered       |
| ADM-VER-012 | Bulk approve/reject có kiểm soát  | P2       | admin/account-verification-review.md | confirm        |

### 3.3. User Management

| ID           | Use Case                             | Priority | Page Mapping             | UI States      |
| ------------ | ------------------------------------ | -------- | ------------------------ | -------------- |
| ADM-USER-001 | Xem danh sách user                   | P0       | admin/user-management.md | loading, empty |
| ADM-USER-002 | Tìm kiếm user                        | P0       | admin/user-management.md | no result      |
| ADM-USER-003 | Lọc user theo role/trạng thái/khoa   | P0       | admin/user-management.md | filtered       |
| ADM-USER-004 | Xem chi tiết user                    | P0       | admin/user-detail.md     | loading        |
| ADM-USER-005 | Khóa tài khoản                       | P0       | admin/user-detail.md     | confirm        |
| ADM-USER-006 | Mở khóa tài khoản                    | P0       | admin/user-detail.md     | confirm        |
| ADM-USER-007 | Cảnh cáo user                        | P0       | admin/user-detail.md     | warning sent   |
| ADM-USER-008 | Gắn role cho user                    | P0       | admin/user-detail.md     | updated        |
| ADM-USER-009 | Gỡ role khỏi user                    | P0       | admin/user-detail.md     | updated        |
| ADM-USER-010 | Xem activity history                 | P1       | admin/user-detail.md     | empty          |
| ADM-USER-011 | Xem report history của user          | P0       | admin/user-detail.md     | empty          |
| ADM-USER-012 | Reset verification status            | P1       | admin/user-detail.md     | confirm        |
| ADM-USER-013 | Đánh dấu user cần theo dõi           | P1       | admin/user-detail.md     | flagged        |
| ADM-USER-014 | Xóa hoặc deactivate user theo policy | P1       | admin/user-detail.md     | confirmation   |

### 3.4. Content Moderation

| ID          | Use Case                      | Priority | Page Mapping              | UI States      |
| ----------- | ----------------------------- | -------- | ------------------------- | -------------- |
| ADM-MOD-001 | Xem moderation queue          | P0       | admin/moderation-queue.md | empty          |
| ADM-MOD-002 | Xem report detail             | P0       | admin/report-detail.md    | loading        |
| ADM-MOD-003 | Duyệt bài viết bị report      | P0       | admin/report-detail.md    | pending        |
| ADM-MOD-004 | Ẩn bài viết                   | P0       | admin/report-detail.md    | hidden         |
| ADM-MOD-005 | Khôi phục bài viết            | P1       | admin/report-detail.md    | restored       |
| ADM-MOD-006 | Xóa bài viết vi phạm          | P0       | admin/report-detail.md    | deleted        |
| ADM-MOD-007 | Duyệt comment bị report       | P0       | admin/report-detail.md    | pending        |
| ADM-MOD-008 | Ẩn/xóa comment                | P0       | admin/report-detail.md    | hidden/deleted |
| ADM-MOD-009 | Duyệt profile bị report       | P0       | admin/report-detail.md    | pending        |
| ADM-MOD-010 | Ẩn thông tin profile vi phạm  | P0       | admin/report-detail.md    | hidden         |
| ADM-MOD-011 | Gửi cảnh báo đến user vi phạm | P0       | admin/report-detail.md    | warning sent   |
| ADM-MOD-012 | Escalate report nghiêm trọng  | P0       | admin/report-detail.md    | escalated      |
| ADM-MOD-013 | Đóng report không vi phạm     | P0       | admin/report-detail.md    | dismissed      |
| ADM-MOD-014 | Thêm note xử lý report        | P1       | admin/report-detail.md    | saved          |
| ADM-MOD-015 | Gộp report trùng              | P2       | admin/moderation-queue.md | merged         |

### 3.5. Safety & Policy

| ID           | Use Case                     | Priority | Page Mapping                  | UI States         |
| ------------ | ---------------------------- | -------- | ----------------------------- | ----------------- |
| ADM-SAFE-001 | Quản lý community guidelines | P0       | admin/policy-management.md    | editing           |
| ADM-SAFE-002 | Quản lý rule report reason   | P0       | admin/policy-management.md    | saved             |
| ADM-SAFE-003 | Cấu hình keyword flagging    | P1       | admin/policy-management.md    | updated           |
| ADM-SAFE-004 | Xem nội dung bị auto-flag    | P1       | admin/moderation-queue.md     | flagged           |
| ADM-SAFE-005 | Quản lý block/ban policy     | P1       | admin/policy-management.md    | saved             |
| ADM-SAFE-006 | Quản lý appeal policy        | P1       | admin/policy-management.md    | saved             |
| ADM-SAFE-007 | Xem danh sách blocked users  | P1       | admin/user-management.md      | filtered          |
| ADM-SAFE-008 | Xử lý appeal của user        | P1       | admin/appeal-management.md    | approved/rejected |
| ADM-SAFE-009 | Gửi thông báo policy update  | P2       | admin/system-announcements.md | sent              |

### 3.6. Mentor / Alumni Verification

| ID          | Use Case                              | Priority | Page Mapping                 | UI States      |
| ----------- | ------------------------------------- | -------- | ---------------------------- | -------------- |
| ADM-MEN-001 | Xem danh sách mentor/alumni chờ duyệt | P1       | admin/mentor-verification.md | empty          |
| ADM-MEN-002 | Xem hồ sơ mentor/alumni               | P1       | admin/mentor-detail.md       | loading        |
| ADM-MEN-003 | Duyệt mentor                          | P1       | admin/mentor-detail.md       | approved       |
| ADM-MEN-004 | Từ chối mentor                        | P1       | admin/mentor-detail.md       | rejected       |
| ADM-MEN-005 | Yêu cầu bổ sung thông tin mentor      | P1       | admin/mentor-detail.md       | need more info |
| ADM-MEN-006 | Tạm ẩn mentor khỏi gợi ý              | P1       | admin/mentor-detail.md       | hidden         |
| ADM-MEN-007 | Xem report về mentor                  | P0       | admin/report-detail.md       | pending        |
| ADM-MEN-008 | Quản lý lĩnh vực mentor               | P2       | admin/mentor-taxonomy.md     | saved          |

### 3.7. Community / Club Management

| ID          | Use Case                      | Priority | Page Mapping                  | UI States |
| ----------- | ----------------------------- | -------- | ----------------------------- | --------- |
| ADM-COM-001 | Duyệt community/CLB mới       | P2       | admin/community-management.md | pending   |
| ADM-COM-002 | Xem chi tiết community        | P2       | admin/community-detail.md     | loading   |
| ADM-COM-003 | Khóa community vi phạm        | P2       | admin/community-detail.md     | confirm   |
| ADM-COM-004 | Gỡ quyền community manager    | P2       | admin/community-detail.md     | confirm   |
| ADM-COM-005 | Duyệt sự kiện CLB             | P2       | admin/event-review.md         | approved  |
| ADM-COM-006 | Ẩn bài viết community vi phạm | P2       | admin/report-detail.md        | hidden    |
| ADM-COM-007 | Quản lý category community    | P2       | admin/community-management.md | saved     |

### 3.8. Dashboard & Analytics

| ID           | Use Case                   | Priority | Page Mapping             | UI States |
| ------------ | -------------------------- | -------- | ------------------------ | --------- |
| ADM-DASH-001 | Xem admin dashboard        | P1       | admin/admin-dashboard.md | loading   |
| ADM-DASH-002 | Xem số lượng user mới      | P1       | admin/admin-dashboard.md | empty     |
| ADM-DASH-003 | Xem số tài khoản chờ duyệt | P0       | admin/admin-dashboard.md | alert     |
| ADM-DASH-004 | Xem số report chưa xử lý   | P0       | admin/admin-dashboard.md | alert     |
| ADM-DASH-005 | Xem content growth         | P2       | admin/admin-dashboard.md | chart     |
| ADM-DASH-006 | Xem active users           | P2       | admin/admin-dashboard.md | chart     |
| ADM-DASH-007 | Xem safety health          | P1       | admin/admin-dashboard.md | risk      |
| ADM-DASH-008 | Export report              | P2       | admin/admin-dashboard.md | exporting |

### 3.9. System Announcement

| ID          | Use Case                       | Priority | Page Mapping                  | UI States |
| ----------- | ------------------------------ | -------- | ----------------------------- | --------- |
| ADM-ANN-001 | Tạo thông báo hệ thống         | P1       | admin/system-announcements.md | draft     |
| ADM-ANN-002 | Gửi thông báo đến toàn bộ user | P1       | admin/system-announcements.md | confirm   |
| ADM-ANN-003 | Gửi thông báo theo role/khoa   | P2       | admin/system-announcements.md | segment   |
| ADM-ANN-004 | Lên lịch thông báo             | P2       | admin/system-announcements.md | scheduled |
| ADM-ANN-005 | Thu hồi thông báo              | P2       | admin/system-announcements.md | recalled  |
| ADM-ANN-006 | Xem lịch sử thông báo          | P1       | admin/system-announcements.md | empty     |

### 3.10. Audit Log

| ID          | Use Case                          | Priority | Page Mapping       | UI States |
| ----------- | --------------------------------- | -------- | ------------------ | --------- |
| ADM-AUD-001 | Xem audit log                     | P0       | admin/audit-log.md | loading   |
| ADM-AUD-002 | Lọc audit theo admin              | P1       | admin/audit-log.md | filtered  |
| ADM-AUD-003 | Lọc audit theo action             | P1       | admin/audit-log.md | filtered  |
| ADM-AUD-004 | Xem chi tiết action               | P1       | admin/audit-log.md | detail    |
| ADM-AUD-005 | Export audit log                  | P2       | admin/audit-log.md | exporting |
| ADM-AUD-006 | Theo dõi thay đổi role/permission | P0       | admin/audit-log.md | critical  |

## 4. Admin MVP Use Cases

Bắt buộc có:

- ADM-AUTH-001
- ADM-AUTH-003
- ADM-VER-001
- ADM-VER-002
- ADM-VER-004
- ADM-VER-005
- ADM-VER-008
- ADM-USER-001
- ADM-USER-004
- ADM-USER-005
- ADM-MOD-001
- ADM-MOD-002
- ADM-MOD-004
- ADM-MOD-006
- ADM-SAFE-001
- ADM-AUD-001

## 5. Admin UX Risks

| Risk                            | Prevention                           |
| ------------------------------- | ------------------------------------ |
| Admin panel quá sơ sài          | Có queue, filter, detail, audit      |
| Duyệt nhầm tài khoản            | Có confirmation, duplicate detection |
| Xóa nội dung thiếu log          | Audit log bắt buộc                   |
| Moderator abuse                 | Role permission + audit              |
| Report bị bỏ sót                | Dashboard alert + queue              |
| User không biết lý do bị reject | Admin phải nhập reason/template      |
