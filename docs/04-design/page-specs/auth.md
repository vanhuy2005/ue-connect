---
title: "Authentication Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/user-authentication.md"
related_design_docs:
  - "../04-design/identity-design.md"
related_system_docs:
  - "../05-system-architecture/auth-service.md"
related_database_docs:
  - "../06-database/users-table.md"
related_api_docs:
  - "../07-api/auth-endpoints.md"
---

# Trang Xác Thực Tài Khoản (Authentication)

## 1. Purpose
Trang Xác Thực Tài Khoản cung cấp các phương thức đăng nhập, đăng ký và khôi phục mật khẩu bảo mật cho tất cả các nhóm thành viên trong hệ sinh thái UEConnect. Đây là cửa ngõ bảo vệ toàn bộ dữ liệu nội bộ của trường Đại học Sư phạm TP.HCM.

## 2. Product Context
Để gìn giữ không gian học thuật nghiêm túc, trang xác thực ưu tiên tích hợp cổng định danh HCMUE SSO dành cho cán bộ giảng viên và sinh viên hiện tại, đồng thời mở rộng cổng đăng ký xác minh thủ công bằng bằng cấp hoặc thẻ sinh viên cho cựu sinh viên.

## 3. User Goals
- Đăng nhập nhanh chóng, an toàn bằng tài khoản định danh sinh viên HCMUE (@student.hcmue.edu.vn) hoặc tài khoản đăng ký riêng.
- Đăng ký tài khoản mới dễ dàng, hỗ trợ điền trước thông tin từ cổng dữ liệu trường nếu chọn SSO.
- Lấy lại mật khẩu tự động qua email đã liên kết khi quên thông tin đăng nhập.
- Trải nghiệm đăng nhập một lần (Single Sign-On - SSO) không rườm rà.

## 4. Primary Users
- **Sinh viên đang học**: Sử dụng email sinh viên để đăng nhập tức thì.
- **Giảng viên / Cán bộ trường**: Đăng nhập bằng cổng định danh cán bộ HCMUE.
- **Cựu sinh viên & Đối tác**: Đăng ký và đăng nhập bằng email cá nhân, trải qua quy trình duyệt hồ sơ thủ công.

## 5. Entry Points
- Truy cập trang chủ UEConnect khi chưa đăng nhập hệ thống.
- Bấm vào các liên kết chia sẻ nội bộ yêu cầu quyền thành viên.

## 6. Layout Strategy
Thiết kế trang đăng nhập tinh giản, trang nghiêm, mang đậm bản sắc HCMUE với tông màu xanh dương chủ đạo kết hợp màu trắng kem hiện đại.

### 6.1 Desktop Layout
- Bố cục chia đôi đối xứng (Split Screen Layout):
  - Phía bên trái: Hình ảnh khuôn viên trường Đại học Sư phạm TP.HCM nghệ thuật chất lượng cao kèm theo các câu nói truyền cảm hứng học đường.
  - Phía bên phải: Khung điền thông tin đăng nhập/đăng ký tinh gọn, tối giản, căn giữa hoàn hảo.
- Khoảng cách lề khung nhập liệu: 48px.

### 6.2 Tablet Layout
- Thu gọn nửa màn hình hình ảnh bên trái thành một dải banner mỏng phía trên cùng hoặc ẩn đi.
- Khung điền thông tin chiếm toàn bộ diện tích hiển thị để tối ưu hóa việc nhập liệu trên bàn phím ảo.

### 6.3 Mobile / PWA Layout
- Thiết kế một cột dọc tập trung hoàn toàn vào các trường nhập liệu.
- Khoảng cách lề: 20px.
- Các nút đăng nhập nhanh bằng Google/HCMUE SSO được xếp dạng khối tròn lớn giúp ngón tay dễ bấm chạm (`touch-target 48px`).

## 7. Information Architecture
- **Tab Đăng Nhập (Login)**:
  - Trường Email / Tên đăng nhập.
  - Trường Mật khẩu (có nút hiển thị).
  - Nút "Ghi nhớ đăng nhập" (Remember me).
  - Liên kết "Quên mật khẩu?".
- **Tab Đăng Ký (Register)**:
  - Chọn đối tượng (Sinh viên / Cựu sinh viên / Đối tác).
  - Họ tên, Email, Mật khẩu, Xác nhận mật khẩu.
  - Điều khoản sử dụng và Chính sách bảo mật (Checkbox bắt buộc).
- **Cổng đăng nhập nhanh (Social/SSO login)**:
  - Đăng nhập bằng tài khoản HCMUE Portal.
  - Đăng nhập bằng tài khoản Google.

## 8. Core Components
- **Brand Logo Header**: Logo chính thức của trường Đại học Sư phạm TP.HCM kết hợp chữ UEConnect tinh tế.
- **Form Input Elements**: Ô nhập liệu có nhãn động (Floating labels) tự động thu nhỏ khi có tiêu điểm (focus).
- **Primary Submit Button**: Nút đăng nhập lớn màu xanh đậm thương hiệu HCMUE (`bg-blue-800` hover `bg-blue-900`).
- **SSO Button Card**: Nút đăng nhập tích hợp có biểu tượng cổng Portal trường sắc nét.

## 9. States
### 9.1 Loading
- Khi bấm "Đăng nhập", toàn bộ form mờ đi khoảng 30%, nút gửi chuyển sang trạng thái vô hiệu hóa và hiển thị biểu tượng tải xoay tròn.

### 9.2 Empty
- Các trường nhập liệu trống và người dùng bấm gửi:
  - UI Copy dưới trường Email: `"Vui lòng nhập địa chỉ email của bạn."`
  - UI Copy dưới trường Mật khẩu: `"Vui lòng nhập mật khẩu."`

### 9.3 Error
- **Sai thông tin**: Đăng nhập thất bại do sai mật khẩu hoặc email chưa đăng ký.
  - UI Copy: `"Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại."`
- **Khóa tài khoản**: Tài khoản bị tạm khóa do vi phạm chính sách kiểm duyệt.
  - UI Copy: `"Tài khoản của bạn đã bị tạm khóa. Vui lòng liên hệ Ban quản trị để biết thêm chi tiết."`

### 9.4 Offline / Reconnecting
- Hiển thị Toast cảnh báo màu cam: `"Không thể đăng nhập. Vui lòng kết nối Internet."` và vô hiệu hóa nút gửi.

### 9.5 Permission Restricted
- Hiển thị khi người dùng cố gắng đăng ký bằng email có tên miền không hợp lệ (không phải `@student.hcmue.edu.vn` khi chọn đối tượng Sinh viên):
  - UI Copy: `"Vui lòng sử dụng email sinh viên do trường HCMUE cung cấp để đăng ký."`

### 9.6 Success / Completed
- Đăng nhập thành công:
  - Chuyển hướng mượt mà về trang Bảng tin (`home-feed`) kèm hiệu ứng chuyển cảnh Fade-in trong vòng 300ms.

## 10. Interaction Design
### 10.1 Hover
- Các nút đăng nhập bằng cổng xã hội thay đổi màu nền từ xám trắng sang xám nhạt (`bg-gray-100`) kèm viền nổi bật.

### 10.2 Focus
- Khi nhấp chọn ô nhập liệu, đường viền chuyển sang màu xanh dương chủ đạo của HCMUE và hiển thị bóng mờ nhẹ xung quanh ô nhập (`ring-2 ring-blue-500`).

### 10.3 Press / Tap
- Các nút bấm khi nhấp chọn sẽ co lại 2% kích thước để tạo cảm giác cơ học trực quan.

### 10.4 Optimistic UI
- Không áp dụng cho quy trình xác thực vì lý do bảo mật nghiêm ngặt.

### 10.5 Menu / Sheet
- Hướng dẫn đăng ký cho cựu sinh viên chưa có email trường được mở trong một BottomSheet chi tiết để tránh làm mất luồng đăng ký hiện tại.

### 10.6 Toast / Undo
- Lỗi đăng nhập hiển thị dạng hộp thông báo lắc nhẹ (Shake animation) để thu hút sự chú ý của người dùng.

### 10.7 Motion
- Hiệu ứng trượt chuyển đổi qua lại giữa tab "Đăng nhập" và "Đăng ký" mượt mà (Slide & Fade transition).

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Thứ tự điều hướng Tab hợp lý từ trên xuống dưới, từ trái qua phải.
- Hỗ trợ phím `Enter` để kích hoạt gửi biểu mẫu từ bất kỳ ô nhập liệu nào.

## 12. Responsive Rules
- Màn hình di động đứng: Ẩn hoàn toàn hình ảnh trang trí, biểu mẫu đăng nhập căn giữa chiếm trọn màn hình. Khoảng cách lề trái phải là 16px.

## 13. Data Requirements
- Dữ liệu đăng nhập:
  - `email` (string, email format)
  - `password` (string)
  - `remember_me` (boolean)
- Dữ liệu đăng ký:
  - `name` (string, max: 255)
  - `email` (string, unique)
  - `password` (string, min: 8)
  - `role_type` (enum: `student`, `alumni`, `mentor`)

## 14. API / Action Requirements
- Gọi Livewire / Fortify Action:
  - `login(email, password, remember)`
  - `register(name, email, password, roleType)`
  - `sendPasswordResetLink(email)`

## 15. Authorization / Privacy Rules
- Bảo vệ chống tấn công dò mật khẩu (Brute force protection): Tự động khóa tạm thời IP hoặc tài khoản sau 5 lần nhập sai mật khẩu liên tiếp trong vòng 5 phút kèm mã Captcha kiểm tra.

## 16. Analytics / Audit Events
- `login_attempted`: Ghi nhận mỗi lần thử đăng nhập (thành công hoặc thất bại).
- `sso_login_clicked`: Ghi nhận số lượt sử dụng cổng đăng nhập định danh trường HCMUE.
- `registration_completed`: Ghi nhận người dùng mới đăng ký thành công cùng nhóm phân loại tương ứng.

## 17. Do / Don't
- **Nên làm**: Luôn kiểm tra định dạng email sinh viên thời gian thực khi người dùng đang nhập để báo lỗi sớm nhất có thể.
- **Không được làm**: Cho phép đăng ký mật khẩu quá đơn giản (như `12345678` hoặc trùng khớp với tên email đăng ký).

## 18. Acceptance Criteria
- Cổng đăng nhập một lần (SSO) tích hợp ổn định và phản hồi thông tin chính xác.
- Quy trình đăng ký tự động phân loại đúng quyền hạn của người dùng trong cơ sở dữ liệu.
- Mật khẩu người dùng được mã hóa an toàn bằng thuật toán Bcrypt hoặc Argon2 trước khi lưu trữ.

## 19. QA / UAT Checklist
- [ ] Xác minh đăng nhập thành công bằng email sinh viên chính thức của HCMUE.
- [ ] Kiểm tra liên kết "Quên mật khẩu" có gửi đúng email chứa mã token khôi phục không.
- [ ] Thử nghiệm nhập sai mật khẩu liên tục để kích hoạt cơ chế khóa bảo vệ Captcha.
- [ ] Kiểm tra giao diện hiển thị trên các trình duyệt Safari di động có bị lỗi che khuất bàn phím ảo hay không.

## 20. AI Agent Implementation Notes
- Sử dụng gói `Laravel\Fortify` hoặc `Laravel\Breeze` làm khung bảo mật xác thực nền tảng.
- Tận dụng Livewire kết hợp cùng Alpine.js để thiết lập các hiệu ứng chuyển đổi biểu mẫu không cần tải lại trang, giúp giảm tối đa tỷ lệ bỏ cuộc giữa chừng của người dùng trong quá trình đăng ký.
