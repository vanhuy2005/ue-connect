---
title: "Guest Use Cases"
module: "03-product/use-cases"
product: "UEConnect"
version: "1.0"
status: "draft"
actor: "Guest"
priority: "P0 Entry"
last_updated: "2026-05-25"
---

# Guest Use Cases

## 1. Purpose

File này liệt kê use case dành cho `Guest`, tức người chưa đăng nhập, chưa có tài khoản hoặc đang trong quá trình đăng ký.

Guest flow rất quan trọng vì nó quyết định user có hiểu UEConnect là gì và có tin tưởng để đăng ký hay không.

---

## 2. Actor Definition

```txt
Actor: Guest
Mô tả: Người chưa đăng nhập hoặc chưa có tài khoản UEConnect.
Mục tiêu: Hiểu product, đăng ký, đăng nhập, xác thực tài khoản.
```

## 3. Guest Use Case Catalog

### 3.1. Product Introduction

| ID            | Use Case                            | Priority | Page Mapping        | UI States     |
| ------------- | ----------------------------------- | -------- | ------------------- | ------------- |
| GST-INTRO-001 | Xem landing/onboarding page         | P0       | onboarding.md       | default       |
| GST-INTRO-002 | Hiểu UEConnect là gì                | P0       | onboarding.md       | default       |
| GST-INTRO-003 | Xem lợi ích chính                   | P0       | onboarding.md       | default       |
| GST-INTRO-004 | Xem cách UEConnect bảo vệ cộng đồng | P0       | onboarding.md       | trust section |
| GST-INTRO-005 | Xem yêu cầu xác thực mã sinh viên   | P0       | verification.md     | info          |
| GST-INTRO-006 | Xem điều khoản/community guidelines | P0       | safety-reporting.md | default       |
| GST-INTRO-007 | Xem privacy note trước khi đăng ký  | P0       | privacy.md          | default       |
| GST-INTRO-008 | Xem FAQ đăng ký                     | P1       | onboarding.md       | accordion     |

### 3.2. Sign Up

| ID           | Use Case                        | Priority | Page Mapping      | UI States   |
| ------------ | ------------------------------- | -------- | ----------------- | ----------- |
| GST-SIGN-001 | Bắt đầu đăng ký                 | P0       | auth.md           | default     |
| GST-SIGN-002 | Nhập thông tin cơ bản           | P0       | auth.md           | validation  |
| GST-SIGN-003 | Nhập mã sinh viên               | P0       | verification.md   | validation  |
| GST-SIGN-004 | Tạo mật khẩu                    | P0       | auth.md           | strength    |
| GST-SIGN-005 | Đồng ý điều khoản               | P0       | auth.md           | required    |
| GST-SIGN-006 | Submit form đăng ký             | P0       | auth.md           | loading     |
| GST-SIGN-007 | Nhận trạng thái account pending | P0       | account-status.md | pending     |
| GST-SIGN-008 | Upload minh chứng bổ sung       | P0       | verification.md   | upload      |
| GST-SIGN-009 | Sửa thông tin đăng ký bị sai    | P1       | account-status.md | editing     |
| GST-SIGN-010 | Nhận rejection reason           | P0       | account-status.md | rejected    |
| GST-SIGN-011 | Gửi lại yêu cầu duyệt           | P1       | account-status.md | resubmitted |

### 3.3. Login

| ID            | Use Case                          | Priority | Page Mapping      | UI States  |
| ------------- | --------------------------------- | -------- | ----------------- | ---------- |
| GST-LOGIN-001 | Mở login page                     | P0       | auth.md           | default    |
| GST-LOGIN-002 | Đăng nhập bằng email/mã sinh viên | P0       | auth.md           | loading    |
| GST-LOGIN-003 | Đăng nhập thất bại                | P0       | auth.md           | error      |
| GST-LOGIN-004 | Account pending khi login         | P0       | account-status.md | pending    |
| GST-LOGIN-005 | Account rejected khi login        | P0       | account-status.md | rejected   |
| GST-LOGIN-006 | Account suspended khi login       | P0       | account-status.md | suspended  |
| GST-LOGIN-007 | Quên mật khẩu                     | P0       | auth.md           | email sent |
| GST-LOGIN-008 | Reset password                    | P0       | auth.md           | success    |
| GST-LOGIN-009 | Token reset hết hạn               | P1       | auth.md           | expired    |
| GST-LOGIN-010 | Chuyển sang signup                | P0       | auth.md           | default    |

### 3.4. Account Status

| ID           | Use Case                    | Priority | Page Mapping      | UI States |
| ------------ | --------------------------- | -------- | ----------------- | --------- |
| GST-STAT-001 | Xem trạng thái pending      | P0       | account-status.md | pending   |
| GST-STAT-002 | Xem trạng thái approved     | P0       | account-status.md | approved  |
| GST-STAT-003 | Xem trạng thái rejected     | P0       | account-status.md | rejected  |
| GST-STAT-004 | Xem lý do bị từ chối        | P0       | account-status.md | reason    |
| GST-STAT-005 | Bổ sung thông tin           | P0       | verification.md   | upload    |
| GST-STAT-006 | Liên hệ hỗ trợ đăng ký      | P1       | support.md        | submitted |
| GST-STAT-007 | Xem thời gian dự kiến duyệt | P1       | account-status.md | info      |
| GST-STAT-008 | Hủy yêu cầu đăng ký         | P2       | account-status.md | confirm   |

### 3.5. Public Safety & Trust

| ID           | Use Case                        | Priority | Page Mapping        | UI States |
| ------------ | ------------------------------- | -------- | ------------------- | --------- |
| GST-SAFE-001 | Xem community guidelines        | P0       | safety-reporting.md | default   |
| GST-SAFE-002 | Xem privacy policy              | P0       | privacy.md          | default   |
| GST-SAFE-003 | Xem cách hệ thống xác thực UEer | P0       | verification.md     | info      |
| GST-SAFE-004 | Xem quy định bản quyền tài liệu | P1       | safety-reporting.md | info      |
| GST-SAFE-005 | Liên hệ support                 | P1       | support.md          | submitted |

## 4. Guest MVP Use Cases

- GST-INTRO-001
- GST-INTRO-002
- GST-INTRO-005
- GST-SIGN-001
- GST-SIGN-003
- GST-SIGN-006
- GST-SIGN-007
- GST-LOGIN-001
- GST-LOGIN-002
- GST-LOGIN-007
- GST-STAT-001
- GST-STAT-003
- GST-SAFE-001
- GST-SAFE-002

## 5. Guest UX Risks

| Risk                           | Prevention                          |
| ------------------------------ | ----------------------------------- |
| User không hiểu app làm gì     | Landing/onboarding rõ 3 value chính |
| User sợ nhập mã sinh viên      | Giải thích trust/privacy            |
| Account pending gây hoang mang | Có account status page              |
| Rejected không biết sửa gì     | Có reason + resubmit                |
| Landing quá giống marketing AI | Neutral-first, rõ product value     |
