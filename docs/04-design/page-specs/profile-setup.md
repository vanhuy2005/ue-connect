---
title: "Profile Setup Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/profile.md"
related_design_docs:
  - "../04-design/profile-layouts.md"
related_system_docs:
  - "../05-system-architecture/profile-service.md"
related_database_docs:
  - "../06-database/users-table.md"
related_api_docs:
  - "../07-api/profile-api.md"
---

# Trang Thiết Lập Hồ Sơ Lần Đầu (Profile Setup Wizard)

## 1. Purpose
Trang Thiết Lập Hồ Sơ Lần Đầu (Profile Setup Wizard) là giao diện Onboarding dạng từng bước (Step-by-step Wizard) giúp sinh viên, cựu sinh viên và giảng viên mới đăng ký tài khoản nhanh chóng hoàn thiện các thông tin cơ bản: Tải ảnh đại diện, điền Bio tự giới thiệu học đường, lựa chọn các lĩnh vực quan tâm học thuật và đăng ký tham gia Mạng lưới cố vấn (Mentorship) trước khi chính thức gia nhập cộng đồng UEConnect.

## 2. Product Context
Để xây dựng một mạng lưới đồng môn HCMUE có tính định danh cao và gắn kết tự nhiên, quy trình thiết lập hồ sơ lần đầu đóng vai trò quyết định, khuyến khích người dùng điền đầy đủ thông tin bằng các bước tối giản, đồ họa học đường gần gũi và giao diện mượt mà theo chuẩn Threads.

## 3. User Goals
- Hoàn tất 3 bước thiết lập hồ sơ cốt lõi nhanh gọn dưới 2 phút.
- Thiết lập ảnh đại diện và ảnh bìa cá tính nhưng vẫn đảm bảo tác phong học đường.
- Điền tiểu sử Bio ngắn gọn và chọn nhanh các lĩnh vực quan tâm nghiên cứu học thuật.
- Kích hoạt trạng thái "Sẵn sàng làm Mentor" (nếu là cựu sinh viên/giảng viên) để tham gia mạng lưới định hướng nghề nghiệp.

## 4. Primary Users
- **Người dùng mới đăng ký tài khoản thành công**: Sinh viên khóa mới, cựu sinh viên vừa tham gia mạng lưới hoặc giảng viên mới của trường.

## 5. Entry Points
- Tự động chuyển hướng (Redirect trigger) ngay sau khi đăng ký tài khoản mới thành công hoặc đăng nhập lần đầu tiên mà hệ thống phát hiện trường `is_setup_completed` là `false`.

## 6. Layout Strategy
Thiết kế bố cục hộp thoại lớn căn giữa màn hình (Central Card Wizard Layout) mang lại cảm giác tập trung tối đa, giảm bớt căng thẳng thị giác cho người dùng mới.

### 6.1 Desktop Layout
- Bố cục hộp thoại đứng ở trung tâm màn hình nền xám kem mềm mại.
- Kích thước chiều rộng cố định: 540px. Chiều cao tự động điều chỉnh theo các bước tối đa 600px.
- Phía trên cùng hiển thị thanh chỉ báo tiến trình 3 bước (Progress Indicator Bar).
- Khoảng cách lề: 32px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 24px giúp ngón tay dễ dàng nhấp chọn nút tương tác.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột dọc tràn màn hình hoàn toàn để tối đa hóa không gian hiển thị và gõ phím.
- Thanh tiến trình 3 bước được thu gọn thành dạng chấm tròn nhỏ mượt mà ở đầu trang.
- Nút "Tiếp tục" (Next Button) và "Quay lại" (Back Button) luôn cố định ở đáy màn hình di động dễ bấm chạm (`touch-target 48px`).

## 7. Information Architecture
- **Bước 1: Hình ảnh cá nhân (Avatar & Bio)**:
  - Tải ảnh đại diện tròn (Avatar), ảnh bìa chữ nhật bo tròn góc.
  - Ô nhập Bio tự giới thiệu ngắn (Ví dụ: `"Chào mọi người! Mình là tân sinh viên Khoa Sư phạm Tiếng Anh K49..."`).
- **Bước 2: Định danh học thuật (Academic Identity)**:
  - Chọn lớp sinh hoạt, khóa đào tạo cũ/mới.
  - Nhập mã số sinh viên / mã cán bộ để kích hoạt Huy hiệu xác minh chính danh từ trường.
- **Bước 3: Lĩnh vực quan tâm (Interests Selection)**:
  - Lưới hiển thị các thẻ danh mục học thuật và phong trào (Nghiên cứu khoa học, Hoạt động Đoàn hội, Thể thao nghệ thuật, Kỹ năng mềm).
  - Người dùng chọn tối thiểu 3 mục quan tâm để hệ thống tối ưu hóa thuật toán đề xuất Bảng tin.

## 8. Core Components
- **Progress Stepper Indicator**: Thanh tiến trình 3 bước hiển thị trực quan mốc bước đang thực hiện bằng các vòng tròn số đổi màu mượt mà.
- **Interests Grid Picker**: Lưới thẻ lựa chọn sở thích học thuật đổi màu nền sáng nổi bật kèm biểu tượng tích chọn khi nhấp kích hoạt.
- **Academic Verification Input**: Trường điền mã số sinh viên tích hợp nút xác thực liên thông cổng HCMUE Portal thời gian thực.
- **Wizard Controller Buttons**: Nhóm nút điều hướng "Tiếp tục" (Next) màu xanh dương thẫm và "Quay lại" (Back) màu xám nhạt nằm sát đáy giao diện.

## 9. States
### 9.1 Loading
- Hiển thị spinner xoay tròn nhỏ ở giữa nút "Tiếp tục" khi đang gửi yêu cầu xác thực Portal trường hoặc đang lưu trữ hồ sơ thiết lập.

### 9.2 Empty
- Khi người dùng chưa chọn đủ tối thiểu 3 sở thích ở Bước 3:
  - Nút "Hoàn tất" bị vô hiệu hóa (disabled). Hiển thị dòng chữ nhỏ: `"Vui lòng chọn tối thiểu 3 lĩnh vực bạn quan tâm."`

### 9.3 Error
- **Lỗi xác thực mã số sinh viên**: Nhập sai mã số sinh viên không tồn tại trong danh sách dữ liệu Portal:
  - UI Copy dưới input: `"Mã số sinh viên không chính xác hoặc chưa được liên kết. Vui lòng kiểm tra lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút bấm chuyển bước và hiển thị thông báo lỗi nếu cố tình nhấp chọn.

### 9.5 Permission Restricted
- Trường hợp người dùng cố tình chuyển hướng bỏ qua trang Setup bằng cách gõ trực tiếp liên kết trang Bảng tin trên thanh URL:
  - Hệ thống tự động Middleware chặn lại và chuyển hướng bắt buộc quay lại trang Profile Setup cho đến khi hoàn thành xong các bước.

### 9.6 Success / Completed
- Hoàn thành thiết lập hồ sơ thành công:
  - Hiển thị màn hình chào mừng sinh viên sống động kèm hiệu ứng pháo hoa giấy (Confetti) tung bay rực rỡ trong vòng 2 giây.
  - Tự động chuyển hướng người dùng sang trang Bảng tin chính (`home-feed`) với trạng thái tài khoản hoạt động bình thường.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các thẻ sở thích học thuật sẽ làm thẻ đổi màu nền sang màu xanh dương nhạt (`bg-blue-50`) và phóng to nhẹ để tạo cảm giác cơ học sinh động.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các ô nhập liệu và các thẻ chọn sở thích khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn thẻ sở thích sẽ đổi màu nền sang màu xanh dương thẫm chứa biểu tượng tích chọn xanh lá rực rỡ, kèm rung nhẹ phản hồi xúc giác trên thiết bị di động.

### 10.4 Optimistic UI
- Khi bấm chọn nhanh các sở thích, lưu giá trị chọn ngay tại bộ nhớ tạm cục bộ thời gian thực để thay đổi màu sắc hiển thị tức thì dưới 50ms mà không đợi máy chủ phản hồi.

### 10.5 Menu / Sheet
- Không áp dụng, trang này được thiết kế tối giản dạng biểu mẫu độc lập để tối đa hóa sự tập trung.

### 10.6 Toast / Undo
- Không áp dụng cho quy trình Onboarding từng bước cốt lõi này.

### 10.7 Motion
- Hoạt ảnh chuyển đổi mượt mà giữa các bước bằng hiệu ứng trượt trơn tru từ phải sang trái (Slide-in-right) cực kỳ bắt mắt trong vòng 250ms CSS Transition.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi bước thiết lập đều có tiêu đề `<h2>` rõ ràng hỗ trợ trình đọc màn hình.
- Hỗ trợ đầy đủ phím di chuyển mũi tên để chọn nhanh giữa các thẻ sở thích.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Lưới thẻ sở thích chuyển sang bố cục 2 cột nhỏ gọn thay vì 4 cột để không bị tràn viền và tay dễ nhấp chọn.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `temp_avatar` (file, optional)
  - `nickname_bio` (string, max: 160)
  - `student_id_code` (string, optional)
  - `selected_interest_ids` (array of integers, min: 3)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `verifyStudentId(studentId)`
  - `saveSetupStepOne(avatar, bio)`
  - `saveSetupStepTwo(studentId, classCode)`
  - `completeSetupWizard(interestIds)`

## 15. Authorization / Privacy Rules
- Bảo mật quyền truy cập: Chỉ chính tài khoản mới đăng ký và chưa hoàn thành cài đặt lần đầu mới được phép truy cập trang Setup này. Hệ thống tự động kiểm tra cột `is_setup_completed` trong bảng `users` để điều phối quyền truy cập chính xác tuyệt đối.

## 16. Analytics / Audit Events
- `profile_setup_wizard_started`: Ghi nhận mỗi lần người dùng bắt đầu quy trình onboarding.
- `profile_setup_step_completed`: Ghi nhận mốc bước hoàn thành (Step 1, Step 2) để theo dõi và tối ưu hóa trải nghiệm người dùng mới.
- `profile_setup_wizard_finished`: Ghi nhận sự kiện hoàn thành thiết lập thành công.

## 17. Do / Don't
- **Nên làm**: Luôn cung cấp nút bấm "Bỏ qua bước này" (Skip step) đối với các thông tin không bắt buộc (như liên kết mạng xã hội) để tránh gây ức chế làm gián đoạn trải nghiệm người dùng mới.
- **Không được làm**: Cho phép hoàn tất thiết lập khi sinh viên chưa điền đầy đủ các thông tin bắt buộc cốt lõi (như Tên hiển thị).

## 18. Acceptance Criteria
- Quy trình 3 bước hoạt động trơn tru, lưu đầy đủ và chính xác tất cả thông tin thiết lập vào cơ sở dữ liệu.
- Tính năng liên thông Portal xác thực mã số sinh viên hoạt động chính xác và tự động gán Huy hiệu xác minh tương ứng.
- Hiệu ứng chuyển động giữa các bước mượt mà không bị gián đoạn hay vỡ giao diện trên mọi kích thước thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra thanh tiến trình chuyển đổi màu sắc chính xác theo mốc bước thực tế.
- [ ] Xác minh tính năng liên thông Portal Portal trường hiển thị đúng họ tên sinh viên khi nhập đúng mã số sinh viên.
- [ ] Thử nghiệm nhấp "Hoàn tất" khi chọn dưới 3 sở thích và đảm bảo hệ thống chặn chính xác, hiển thị cảnh báo trực quan.
- [ ] Đảm bảo sau khi hoàn tất, hệ thống chuyển hướng đúng về trang Bảng tin chính và không cho phép quay lại trang Setup nữa.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình Livewire Multi-step Form Component để quản lý trạng thái của toàn bộ quy trình thiết lập trong một lớp PHP duy nhất mượt mà và dễ kiểm soát lỗi.
- Sử dụng thư viện `canvas-confetti` cực nhẹ để vẽ hiệu ứng tung pháo hoa giấy rực rỡ tại client khi hoàn tất thiết lập, tạo thiện cảm lớn và niềm tự hào học đường cho sinh viên mới.
---
