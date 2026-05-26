---
title: "Onboarding Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0"
actors:
  - Guest
  - Student
related_use_cases:
  - GST-INTRO-001
  - GST-SIGN-001
  - GST-SIGN-003
  - STU-AUTH-001
  - STU-AUTH-002
  - STU-ONB-003
---

# Onboarding Flow

## 1. Purpose

Flow này mô tả toàn bộ hành trình từ lúc user chưa có tài khoản đến khi trở thành `Verified UEer` và có thể sử dụng UEConnect.

Onboarding của UEConnect không chỉ là signup. Nó phải giải thích rõ:

- UEConnect là gì.
- Vì sao cần xác thực bằng mã sinh viên.
- User sẽ nhận được giá trị gì sau khi tham gia.
- Cần tạo profile như thế nào để cộng đồng tin tưởng.
- Trạng thái tài khoản đang pending, approved hoặc rejected.

Onboarding là nơi tạo trust đầu tiên. Nếu làm mơ hồ, user sẽ nghi ngờ ngay. Và nghi ngờ là thứ duy nhất con người làm rất giỏi trên internet.

---

## 2. Actors

| Actor   | Role                                                         |
| ------- | ------------------------------------------------------------ |
| Guest   | Người chưa đăng nhập, muốn tìm hiểu hoặc đăng ký             |
| Student | Người đang tạo tài khoản và xác thực                         |
| Admin   | Người duyệt tài khoản, không trực tiếp trong user flow chính |

---

## 3. Entry Points

User có thể vào onboarding từ:

- Landing page.
- Nút `Tạo tài khoản`.
- Link được bạn bè chia sẻ.
- QR code từ sự kiện HCMUE.
- Trang login khi user chưa có tài khoản.
- Redirect khi user truy cập protected page nhưng chưa login.

---

## 4. High-level Flow

```txt
Guest
→ Xem giới thiệu UEConnect
→ Chọn tạo tài khoản
→ Nhập thông tin đăng ký
→ Nhập mã sinh viên
→ Xác minh / gửi yêu cầu duyệt
→ Account Pending
→ Admin Review
→ Approved
→ Profile Setup
→ Privacy Setup
→ Home Feed
```

## 5. Main Flow

### 5.1. Step 1 — Product Introduction

User Goal

Hiểu UEConnect là gì và có đáng đăng ký không.

UI Requirements
Hero ngắn gọn.
Value proposition rõ.
CTA chính: Tạo tài khoản.
CTA phụ: Đăng nhập.
Trust note: Chỉ dành cho cộng đồng HCMUE đã xác thực.
Content Points
Kết nối UEers trong trường.
Khám phá bạn cùng khoa, cùng môn, cùng mục tiêu.
Đăng bài, thảo luận, nhắn tin và tìm mentor.
Xác thực bằng mã sinh viên để giữ cộng đồng an toàn.

### 5.2. Step 2 — Account Creation

User Goal

Tạo tài khoản UEConnect.

Required Inputs
Họ và tên.
Email hoặc số điện thoại.
Mật khẩu.
Mã sinh viên.
Đồng ý Terms / Community Guidelines.
UX Rules
Không hỏi quá nhiều ở bước đầu.
Không bắt user hoàn thiện profile ngay trong signup.
Giải thích vì sao cần mã sinh viên.
Password field phải có show/hide.
Form phải có validation rõ.
Error Cases
Error Treatment
Email đã tồn tại Hiển thị lỗi + link đăng nhập
Mã sinh viên đã được dùng Báo lỗi nghiêm túc, gợi ý liên hệ hỗ trợ
Mật khẩu yếu Hiển thị rule cụ thể
Thiếu checkbox Terms Scroll/focus đến checkbox
Network error Cho retry, không xóa dữ liệu đã nhập

### 5.3. Step 3 — Verification Submission

User Goal

Gửi thông tin để được xác thực UEer.

Possible Verification Modes
Mode Description
Auto Check Hệ thống kiểm tra mã sinh viên nếu có database
Manual Review Admin duyệt thủ công
Extra Evidence User upload minh chứng nếu cần
UI Requirements
Hiển thị trạng thái rõ.
Không dùng loading vô hạn.
Có thông báo: Tài khoản của bạn đang chờ duyệt.
Có estimated review time nếu có.
Có support link.

### 5.4. Step 4 — Account Pending

User Goal

Biết tài khoản đang ở trạng thái nào.

Pending Screen Must Show
Trạng thái: Đang chờ xác thực.
Mô tả ngắn lý do.
Thông tin đã gửi.
CTA phụ: Cập nhật thông tin.
CTA phụ: Liên hệ hỗ trợ.
Không cho vào full product nếu chưa approved.
UX Risk

Không để user pending mà không biết chuyện gì xảy ra. Đó là cách nhanh nhất để user nghĩ hệ thống hỏng, mà đôi khi họ đúng.

### 5.5. Step 5 — Approved

User Goal

Bắt đầu sử dụng UEConnect.

UI Requirements
Confirmation screen.
Badge Verified UEer.
CTA: Hoàn thiện hồ sơ.
Giải thích profile giúp kết nối tốt hơn.

### 5.6. Step 6 — Profile Setup

Required Fields
Avatar.
Khoa.
Ngành.
Khóa.
Lớp nếu có.
Bio ngắn.
Mục tiêu sử dụng UEConnect.
Optional Fields
Sở thích học tập.
Môn đang quan tâm.
CLB.
Mentor/career interest.
Social links nếu policy cho phép.
UX Rules
Chia thành nhiều step.
Có progress indicator.
Cho skip các field optional.
Giải thích field nào public/private.
Không public toàn bộ mã sinh viên.

### 5.7. Step 7 — First Home Feed

User Goal

Vào product và biết làm gì tiếp.

First Feed Should Include
Welcome card.
Gợi ý hoàn thiện profile.
Gợi ý UEers cùng khoa.
Gợi ý tạo bài đầu tiên.
Gợi ý khám phá mentor hoặc cộng đồng.

## 6. Alternative Flows

### 6.1. Account Rejected

Submit verification
→ Admin rejects
→ User sees reason
→ User edits information
→ Resubmit
→ Pending
Required UI
Rejection reason rõ.
Không dùng copy đổ lỗi.
CTA: Cập nhật và gửi lại.
Link support.

### 6.2. Duplicate Student ID

User enters student ID
→ System detects duplicate
→ User cannot continue
→ User sees support action
Required UI
Message rõ: Mã sinh viên này đã được sử dụng.
CTA: Báo cáo vấn đề.
Không tiết lộ thông tin tài khoản đang dùng mã đó.

### 6.3. User Skips Optional Profile Fields

Approved
→ Profile setup
→ User skips optional fields
→ Home feed
→ Profile completion reminder later
UX Rule

Không khóa product vì thiếu field optional.

## 7. Edge Cases

Case Required Treatment
User refresh giữa signup Preserve data nếu có thể
Upload failed Retry, không reset form
Pending quá lâu Show support CTA
Admin cần thêm info Account status chuyển Need more information
User chưa accepted terms Không cho submit
User vào protected route khi pending Redirect account status
User bị suspended Show account status, không vào app

## 8. Required Pages

Page Purpose
onboarding.md Product intro
auth.md Login/signup
verification.md Student ID verification
account-status.md Pending/rejected/approved
profile-setup.md Setup profile
privacy.md Initial privacy choice
home-feed.md First product destination

## 9. Required Components

Button.
Input.
Password input.
Checkbox.
Stepper.
Avatar uploader.
File uploader.
Alert.
Status card.
Progress indicator.
Verified badge.
Toast.
Modal confirmation.

## 10. Required States

State Description
Default Form ready
Loading Submit/register/checking
Error Validation/server error
Pending Waiting for admin approval
Rejected Verification rejected
Need More Info Admin requests more information
Approved Account verified
Empty No profile fields yet
Offline Cannot submit
Permission denied Pending user accessing protected page

## 11. Success Metrics

Signup completion rate.
Verification submission rate.
Account approval rate.
Profile setup completion rate.
Time from signup to first feed visit.
Support requests related to verification.
Drop-off per onboarding step.

## 12. UX Checklist

- User hiểu UEConnect là verified HCMUE social platform.
- User hiểu vì sao cần mã sinh viên.
- Form không hỏi quá nhiều từ đầu.
- Pending/rejected/approved states rõ.
- Không public toàn bộ mã sinh viên.
- Profile setup có skip cho field optional.
- Mobile onboarding dùng step layout gọn.
- Error không làm mất dữ liệu user đã nhập.
- CTA chính luôn rõ.
- Không dùng dating language.
