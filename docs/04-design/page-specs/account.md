---
title: "Account Settings Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/account-management.md"
related_design_docs:
  - "../04-design/settings-navigation.md"
related_system_docs:
  - "../05-system-architecture/auth-system.md"
related_database_docs:
  - "../06-database/users-table.md"
related_api_docs:
  - "../07-api/account-api.md"
---

# Trang Thiết Lập Tài Khoản (Account Settings)

## 1. Purpose
Trang Thiết Lập Tài Khoản là nơi người dùng (sinh viên, cựu sinh viên, giảng viên/mentors) thực hiện quản lý các thông tin cốt lõi liên quan đến tài khoản bảo mật cá nhân, thông tin đăng nhập, thiết lập thông báo, thay đổi mật khẩu và quản lý quyền trạng thái tài khoản.

## 2. Product Context
Nằm trong hệ sinh thái UEConnect, trang này giúp củng cố tính tin cậy và minh bạch. Nó cung cấp các công cụ trực quan để quản lý thông tin riêng tư cá nhân theo đúng tinh thần học đường nghiêm túc của HCMUE kết hợp với sự hiện đại của nền tảng Threads.

## 3. User Goals
- Thay đổi mật khẩu an toàn và thiết lập xác thực 2 yếu tố (2FA).
- Cấu hình tần suất nhận thông báo qua email và hệ thống đẩy (push notifications).
- Đồng bộ hoặc cập nhật email liên kết sinh viên (@student.hcmue.edu.vn).
- Thực hiện yêu cầu tạm khóa hoặc xóa vĩnh viễn tài khoản khi không còn nhu cầu sử dụng.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần đổi mật khẩu hoặc liên kết tài khoản định danh trường.
- **Cựu sinh viên & Mentors**: Quản lý thông tin bảo mật và liên kết bảo mật bên ngoài.

## 5. Entry Points
- Nhấp vào biểu tượng Avatar ở thanh điều hướng dưới (Mobile) hoặc góc phải trên (Desktop) -> Chọn **Cài đặt tài khoản** (Thiết lập).
- Chuyển hướng trực tiếp từ link email cảnh báo bảo mật hệ thống.

## 6. Layout Strategy
Áp dụng cấu trúc bố cục chia cột khoa học, linh hoạt nhằm đảm bảo các trường nhập liệu dài không bị loãng.

### 6.1 Desktop Layout
- Sử dụng bố cục 2 cột (Sidebar bên trái chứa menu danh mục cài đặt, Khung nội dung chính bên phải chứa các form nhập liệu).
- Grid hệ thống: 12-column, sidebar chiếm 3 columns, main area chiếm 9 columns.
- Khoảng cách an toàn (Padding): 32px.

### 6.2 Tablet Layout
- Sidebar thu gọn thành thanh menu ngang dạng tab cuộn (Horizontal Scrollable Tabs).
- Khung nội dung chính căn giữa màn hình với khoảng cách an toàn 24px.

### 6.3 Mobile / PWA Layout
- Bố cục một cột duy nhất theo chiều dọc.
- Sử dụng BottomSheet cho các thao tác xác nhận nhạy cảm như "Xóa tài khoản" hoặc "Tạm khóa".
- Khoảng cách an toàn: 16px.

## 7. Information Architecture
- **Nhóm bảo mật (Security)**:
  - Thay đổi mật khẩu (Mật khẩu hiện tại, Mật khẩu mới, Xác nhận mật khẩu mới).
  - Xác thực 2 lớp (Bật/Tắt).
- **Nhóm thông báo (Notifications)**:
  - Thông báo tương tác (Thích, Phản hồi, Kết nối mới).
  - Thông báo email định kỳ.
- **Trạng thái tài khoản (Account State)**:
  - Tạm khóa tài khoản.
  - Yêu cầu xóa tài khoản.

## 8. Core Components
- **Input Text Field**: Trường nhập mật khẩu tích hợp nút ẩn/hiển thị mật khẩu (Icon Mắt).
- **Toggle Switch**: Điều chỉnh bật/tắt nhận thông báo và 2FA.
- **Danger Button**: Nút "Tạm khóa tài khoản" màu hổ phách, "Xóa tài khoản" màu đỏ đậm (`bg-red-600` hover `bg-red-700`).
- **Standard Button**: Nút "Lưu thay đổi" màu chủ đạo HCMUE (`bg-blue-800`).
- **Toast**: Thông báo kết quả lưu thành công hoặc thất bại.

## 9. States
### 9.1 Loading
- Hiển thị Skeleton cho các form nhập liệu trong lúc tải thông tin từ API.
- Hiển thị Spinner nhỏ nằm trên nút "Lưu thay đổi" khi đang gửi yêu cầu cập nhật.

### 9.2 Empty
- Không áp dụng cho cấu trúc cài đặt. Tuy nhiên, nếu không có thiết bị xác thực nào được đăng ký 2FA, hiển thị trạng thái trống kèm nút: "Thêm phương thức xác thực".

### 9.3 Error
- **Lỗi nghiệp vụ**: Nhập sai mật khẩu hiện tại. Hiển thị thông báo màu đỏ dưới input: `"Mật khẩu hiện tại không chính xác. Vui lòng kiểm tra lại."`
- **Lỗi kết nối**: `"Mất kết nối mạng. Không thể cập nhật thiết lập của bạn."`

### 9.4 Offline / Reconnecting
- Vô hiệu hóa (disable) toàn bộ các nút lưu thay đổi và toggle.
- Hiển thị banner mỏng ở đầu trang: `"Bạn đang ngoại tuyến. Các thiết lập sẽ được lưu khi kết nối lại."`

### 9.5 Permission Restricted
- Trường hợp tài khoản đang bị hạn chế do vi phạm chính sách kiểm duyệt, hiển thị overlay mờ kèm thông điệp: `"Tài khoản của bạn đang bị khóa một số tính năng thiết lập."`

### 9.6 Success / Completed
- Đổi mật khẩu thành công: Hiện Toast `"Mật khẩu của bạn đã được cập nhật thành công!"` kèm âm thanh phản hồi nhẹ.

## 10. Interaction Design
### 10.1 Hover
- Các nút lưu thay đổi tăng độ đậm màu lên 10%. Các tab cài đặt chuyển sang nền màu xám nhạt (`bg-gray-100` hoặc `dark:bg-zinc-800`).

### 10.2 Focus
- Vòng viền Focus 2px màu xanh thương hiệu HCMUE (`focus:ring-2 focus:ring-blue-600 focus:outline-none`) xung quanh các ô nhập liệu khi dùng phím Tab.

### 10.3 Press / Tap
- Độ trễ tối đa của phản hồi xúc giác: 50ms trên điện thoại di động.
- Nút bấm giảm nhẹ kích thước (scale-95) khi bấm giữ.

### 10.4 Optimistic UI
- Đối với các nút Switch (Bật tắt thông báo), chuyển trạng thái hiển thị của nút ngay lập tức mà không đợi phản hồi API. Nếu API thất bại, rollback lại trạng thái cũ và hiện Toast thông báo lỗi.

### 10.5 Menu / Sheet
- Menu chuyển đổi danh mục mượt mà trên mobile sử dụng Drawer vuốt từ dưới lên.

### 10.6 Toast / Undo
- Toast thành công hiển thị trong vòng 4 giây, hỗ trợ nút bấm "Hoàn tác" (Undo) đối với các hành động như tắt nhận thông báo.

### 10.7 Motion
- Hi ứng chuyển tab mượt mà sử dụng CSS Transition `transition-all duration-200 ease-in-out`. Không giật lag khi chuyển đổi giữa các khung nội dung.

## 11. Accessibility Requirements
- Độ tương phản màu sắc đạt chuẩn WCAG AA (độ tương phản văn bản tối thiểu 4.5:1).
- Hỗ trợ đầy đủ phím di chuyển `Tab` và chọn bằng `Space / Enter`.
- Đầy đủ nhãn `aria-label` cho tất cả các nút ẩn/hiện mật khẩu và toggle.

## 12. Responsive Rules
- Màn hình rộng (>1200px): Chiều rộng tối đa khung nội dung là 800px để chống mỏi mắt.
- Màn hình nhỏ (<768px): Toàn bộ form dàn trải 100% chiều rộng màn hình. Kích thước phông chữ tăng lên tối thiểu 16px để tránh thiết bị iOS tự động thu phóng (auto-zoom) khi nhấp vào input.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `current_password` (string, required)
  - `new_password` (string, required, min: 8)
  - `email_notifications` (boolean)
  - `push_notifications` (boolean)
  - `two_factor_enabled` (boolean)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `updatePassword(currentPassword, newPassword)`
  - `toggleTwoFactor()`
  - `updateNotificationSettings(email, push)`
  - `deactivateAccount()`

## 15. Authorization / Privacy Rules
- Chỉ có chính người dùng sở hữu tài khoản mới có quyền truy cập trang này (`auth` middleware).
- Mọi thao tác thay đổi mật khẩu hoặc xóa tài khoản đều yêu cầu nhập lại mật khẩu hiện tại hoặc mã OTP gửi qua email sinh viên.

## 16. Analytics / Audit Events
- `user_changed_password`: Ghi nhận thời gian và địa chỉ IP khi đổi mật khẩu.
- `two_factor_toggled`: Ghi nhận trạng thái bật tắt bảo mật 2 lớp.
- `account_deactivation_requested`: Nhật ký yêu cầu vô hiệu hóa tài khoản.

## 17. Do / Don't
- **Nên làm**: Cho phép người dùng hiển thị mật khẩu bằng biểu tượng mắt để tránh gõ sai.
- **Không được làm**: Hiển thị mật khẩu dưới dạng văn bản thuần khi không có yêu cầu bấm từ người dùng. Không được lưu trữ mật khẩu chưa mã hóa ở bất kỳ bộ nhớ đệm nào của trình duyệt.

## 18. Acceptance Criteria
- Đảm bảo người dùng có thể đổi mật khẩu và đăng nhập ngay với mật khẩu mới.
- Bật/tắt các tùy chọn thông báo phản hồi ngay lập tức và đồng bộ chính xác với cơ sở dữ liệu.
- Kích thước chạm của các switch và button tối thiểu đạt 44px x 44px.

## 19. QA / UAT Checklist
- [ ] Kiểm tra tính năng xác thực mật khẩu hiện tại có hoạt động chính xác không.
- [ ] Xác minh 2FA có gửi đúng mã về email khi kích hoạt không.
- [ ] Thử nghiệm tắt kết nối mạng khi đang thực hiện bật tắt các Switch và kiểm tra cơ chế khôi phục trạng thái (rollback).
- [ ] Đảm bảo chuyển tab trên Mobile mượt mà không bị tràn màn hình.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `Livewire\Component` để xử lý form đồng bộ dữ liệu thời gian thực.
- Sử dụng Alpine.js cho trạng thái hiển thị/ẩn mật khẩu ở phía client để giảm thiểu số lần render lại của Livewire.
