---
title: "Club / Community Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
actor: "Club / Community Manager"
priority: "P2"
last_updated: "2026-05-25"
---

# Club / Community Use Cases

## 1. Purpose

File này liệt kê use case dành cho CLB, lớp, nhóm học tập và cộng đồng trong UEConnect.

Community/Club là module quan trọng để UEConnect không chỉ là feed cá nhân, nhưng ở MVP có thể phát triển sau core feed, discovery, messaging và mentor.

---

## 2. Actor Definition

```txt
Actor: Club / Community Manager
Mô tả: Người đại diện hoặc quản lý CLB, lớp, nhóm học tập, cộng đồng sinh viên.
Mục tiêu: Tạo không gian cộng đồng, đăng thông báo, tổ chức thảo luận, quản lý thành viên.
```

## 3. Club / Community Use Case Catalog

### 3.1. Community Creation

| ID          | Use Case                       | Priority | Page Mapping   | UI States        |
| ----------- | ------------------------------ | -------- | -------------- | ---------------- |
| COM-CRE-001 | Tạo community/CLB mới          | P2       | clubs.md       | draft            |
| COM-CRE-002 | Nhập thông tin community       | P2       | club-detail.md | validation       |
| COM-CRE-003 | Upload avatar/cover community  | P2       | club-detail.md | uploading        |
| COM-CRE-004 | Chọn loại community            | P2       | clubs.md       | selected         |
| COM-CRE-005 | Gửi yêu cầu duyệt community    | P2       | clubs.md       | pending          |
| COM-CRE-006 | Xem trạng thái duyệt community | P2       | clubs.md       | pending/rejected |
| COM-CRE-007 | Bổ sung thông tin community    | P2       | clubs.md       | need more info   |
| COM-CRE-008 | Chỉnh sửa thông tin community  | P2       | club-detail.md | saving           |

### 3.2. Community Membership

| ID          | Use Case                        | Priority | Page Mapping   | UI States |
| ----------- | ------------------------------- | -------- | -------------- | --------- |
| COM-MEM-001 | Xem danh sách thành viên        | P2       | club-detail.md | empty     |
| COM-MEM-002 | Duyệt yêu cầu tham gia          | P2       | club-detail.md | pending   |
| COM-MEM-003 | Chấp nhận thành viên            | P2       | club-detail.md | accepted  |
| COM-MEM-004 | Từ chối thành viên              | P2       | club-detail.md | declined  |
| COM-MEM-005 | Mời thành viên                  | P2       | club-detail.md | invited   |
| COM-MEM-006 | Gỡ thành viên                   | P2       | club-detail.md | confirm   |
| COM-MEM-007 | Gán role community              | P2       | club-detail.md | updated   |
| COM-MEM-008 | Gỡ role community               | P2       | club-detail.md | updated   |
| COM-MEM-009 | Rời community                   | P2       | club-detail.md | confirm   |
| COM-MEM-010 | Block thành viên khỏi community | P2       | club-detail.md | blocked   |

### 3.3. Community Feed & Posts

| ID           | Use Case                       | Priority | Page Mapping        | UI States  |
| ------------ | ------------------------------ | -------- | ------------------- | ---------- |
| COM-POST-001 | Đăng bài trong community       | P2       | composer.md         | submitting |
| COM-POST-002 | Đăng thông báo community       | P2       | club-detail.md      | submitting |
| COM-POST-003 | Ghim bài quan trọng            | P2       | club-detail.md      | pinned     |
| COM-POST-004 | Chỉnh sửa bài community        | P2       | post-detail.md      | editing    |
| COM-POST-005 | Xóa bài community              | P2       | post-detail.md      | confirm    |
| COM-POST-006 | Ẩn bài vi phạm trong community | P2       | club-detail.md      | hidden     |
| COM-POST-007 | Comment trong bài community    | P2       | post-detail.md      | submitting |
| COM-POST-008 | Report bài community           | P0       | safety-reporting.md | submitted  |
| COM-POST-009 | Lọc bài theo topic             | P3       | club-detail.md      | filtered   |

### 3.4. Community Chat / Channel

| ID           | Use Case                      | Priority | Page Mapping         | UI States |
| ------------ | ----------------------------- | -------- | -------------------- | --------- |
| COM-CHAT-001 | Mở community chat             | P2       | community-chat.md    | loading   |
| COM-CHAT-002 | Gửi tin nhắn trong community  | P2       | community-chat.md    | sending   |
| COM-CHAT-003 | Tạo channel/chủ đề            | P3       | community-channel.md | created   |
| COM-CHAT-004 | Ghim tin nhắn quan trọng      | P3       | community-chat.md    | pinned    |
| COM-CHAT-005 | Xóa tin nhắn vi phạm          | P2       | community-chat.md    | deleted   |
| COM-CHAT-006 | Mute community chat           | P2       | community-chat.md    | muted     |
| COM-CHAT-007 | Report tin nhắn community     | P0       | safety-reporting.md  | submitted |
| COM-CHAT-008 | Tìm kiếm trong community chat | P3       | community-chat.md    | no result |

### 3.5. Events

| ID          | Use Case                        | Priority | Page Mapping        | UI States  |
| ----------- | ------------------------------- | -------- | ------------------- | ---------- |
| COM-EVT-001 | Tạo sự kiện CLB                 | P2       | events.md           | draft      |
| COM-EVT-002 | Cập nhật thông tin sự kiện      | P2       | events.md           | saving     |
| COM-EVT-003 | Gửi sự kiện chờ duyệt           | P2       | events.md           | pending    |
| COM-EVT-004 | Mở đăng ký sự kiện              | P2       | events.md           | open       |
| COM-EVT-005 | Đóng đăng ký sự kiện            | P2       | events.md           | closed     |
| COM-EVT-006 | Xem danh sách đăng ký           | P2       | events.md           | empty      |
| COM-EVT-007 | Gửi thông báo đến người đăng ký | P2       | events.md           | sent       |
| COM-EVT-008 | Hủy sự kiện                     | P2       | events.md           | cancelled  |
| COM-EVT-009 | Check-in sự kiện                | P3       | events.md           | checked in |
| COM-EVT-010 | Report sự kiện                  | P0       | safety-reporting.md | submitted  |

### 3.6. Resource Library

| ID          | Use Case                          | Priority | Page Mapping              | UI States   |
| ----------- | --------------------------------- | -------- | ------------------------- | ----------- |
| COM-RES-001 | Tạo thư viện tài nguyên           | P3       | resource-library.md       | default     |
| COM-RES-002 | Upload tài liệu                   | P3       | resource-library.md       | uploading   |
| COM-RES-003 | Thêm mô tả bản quyền              | P3       | resource-library.md       | required    |
| COM-RES-004 | Duyệt tài liệu trước khi public   | P3       | admin/moderation-queue.md | pending     |
| COM-RES-005 | Tải tài liệu                      | P3       | resource-library.md       | downloading |
| COM-RES-006 | Report tài liệu vi phạm bản quyền | P0       | safety-reporting.md       | submitted   |
| COM-RES-007 | Xóa tài liệu                      | P3       | resource-library.md       | confirm     |

## 4. Club / Community MVP/P2 Use Cases

- COM-CRE-001
- COM-CRE-005
- COM-MEM-001
- COM-MEM-002
- COM-POST-001
- COM-POST-003
- COM-CHAT-001
- COM-CHAT-002
- COM-EVT-001

## 5. Club / Community UX Risks

| Risk                                 | Prevention                       |
| ------------------------------------ | -------------------------------- |
| Community quá giống Discord phức tạp | MVP chỉ cần feed + chat đơn giản |
| CLB spam thông báo                   | Permission + moderation          |
| Tài liệu vi phạm bản quyền           | Copyright warning + report       |
| Quản lý role rối                     | Role đơn giản: owner/mod/member  |
| Community làm loãng core product     | Đưa vào Phase 2                  |
