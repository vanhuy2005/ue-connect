---
title: "Alumni Profile Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/alumni-mentoring.md"
related_design_docs:
  - "../04-design/profile-layouts.md"
related_system_docs:
  - "../05-system-architecture/profile-service.md"
related_database_docs:
  - "../06-database/alumni-details.md"
related_api_docs:
  - "../07-api/alumni-api.md"
---

# Trang Hồ Sơ Cựu Sinh Viên (Alumni Profile)

## 1. Purpose
Trang Hồ Sơ Cựu Sinh Viên là không gian hiển thị thông tin học thuật, hành trình sự nghiệp và các hoạt động cống hiến học đường của các cựu sinh viên HCMUE. Trang này đóng vai trò cầu nối quan trọng, giúp cựu sinh viên kết nối với bạn bè khóa cũ, chia sẻ kinh nghiệm nghề nghiệp và nhận vai trò cố vấn (Mentorship) cho các thế hệ sinh viên đi sau.

## 2. Product Context
Nằm trong lõi kết nối tri thức của UEConnect, trang hồ sơ này không chỉ đơn thuần là một CV trực tuyến mà còn là biểu trưng của sự kế thừa truyền thống. Nó làm nổi bật tinh thần kết nối bền vững của cộng đồng Đại học Sư phạm TP.HCM.

## 3. User Goals
- **Đối với cựu sinh viên**: Cập nhật thông tin công tác hiện tại, khóa học cũ tại HCMUE, đăng ký tham gia làm Mentor, kết nối với bạn bè cùng khóa hoặc các cựu sinh viên nổi bật khác.
- **Đối với sinh viên hiện tại**: Tìm kiếm cơ hội thực tập, định hướng nghề nghiệp, kết nối học hỏi kinh nghiệm thực tế từ các anh chị đi trước thông qua các bài đăng hoặc yêu cầu tư vấn trực tiếp.

## 4. Primary Users
- **Cựu sinh viên HCMUE**: Người chia sẻ kinh nghiệm, tìm kiếm cơ hội hợp tác tuyển dụng.
- **Sinh viên HCMUE**: Người tìm kiếm sự kết nối, hướng nghiệp, học hỏi từ các tấm gương cựu sinh viên xuất sắc.

## 5. Entry Points
- Nhấp vào tên hoặc ảnh đại diện của cựu sinh viên trên Bảng tin (Feed), mục Khám phá (Discovery) hoặc kết quả Tìm kiếm (Search).
- Thông qua các đường dẫn liên kết từ danh sách thành viên Câu lạc bộ cũ hoặc sự kiện cựu sinh viên của khoa.

## 6. Layout Strategy
Kết hợp hài hòa giữa yếu tố học thuật học đường truyền thống và thiết kế mạng xã hội Threads năng động, tối giản.

### 6.1 Desktop Layout
- Bố cục 3 cột không gian rộng:
  - Cột trái: Thẻ tóm tắt thông tin cá nhân cơ bản (Ảnh đại diện, Khóa học tại HCMUE, Nơi công tác hiện tại, Huy hiệu chứng nhận cựu sinh viên).
  - Cột giữa: Dòng thời gian hiển thị các bài đăng, bài chia sẻ kinh nghiệm chuyên môn và các cơ hội nghề nghiệp mà cựu sinh viên đó chia sẻ.
  - Cột phải: Các hoạt động kết nối nổi bật, đề xuất các cựu sinh viên cùng khoa hoặc cùng lĩnh vực nghề nghiệp.
- Khoảng cách lề: 24px, chiều rộng tối đa 1200px.

### 6.2 Tablet Layout
- Thu gọn cột bên phải. Cột thông tin cá nhân bên trái và dòng thời gian ở giữa chia thành tỷ lệ 1:2.
- Khoảng cách lề: 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc duy nhất.
- Ảnh đại diện lớn, thông tin tóm tắt đặt phía dưới.
- Các hành động tương tác chính (Kết nối, Nhắn tin, Yêu cầu Mentor) được hiển thị dạng thanh hành động cố định (Sticky Action Bar) dưới đáy màn hình để tối ưu hóa thao tác chạm bằng ngón tay cái.

## 7. Information Architecture
- **Thông tin nhận diện cốt lõi**:
  - Họ tên, niên khóa (Ví dụ: K41 - Khoa Công nghệ Thông tin), vị trí công tác hiện tại (Ví dụ: Senior Software Engineer tại VNG).
  - Huy hiệu xác thực cựu sinh viên từ phòng Công tác Sinh viên HCMUE.
- **Thông tin chuyên môn & Hướng nghiệp**:
  - Lĩnh vực hoạt động, kỹ năng nổi bật.
  - Trạng thái nhận hỗ trợ (Sẵn sàng làm Mentor, Sẵn sàng tuyển dụng, Sẵn sàng chia sẻ chuyên đề).
- **Dòng thời gian hoạt động**:
  - Tab "Bài viết": Các bài chia sẻ kinh nghiệm, tin tuyển dụng.
  - Tab "Câu trả lời": Lịch sử giải đáp các câu hỏi trong cộng đồng.

## 8. Core Components
- **Verified Badge**: Huy hiệu xanh ngọc bích xác thực cựu sinh viên chính danh HCMUE.
- **Career Timeline**: Bản đồ tóm tắt hành trình nghề nghiệp từ lúc tốt nghiệp trường Sư phạm đến hiện tại.
- **Action Buttons Group**:
  - Nút "Kết nối / Đã kết nối" (Primary / Secondary style).
  - Nút "Yêu cầu Mentor" (Chỉ hiển thị nếu cựu sinh viên có bật trạng thái làm Mentor).
- **Empty State Component**: Hiển thị khi cựu sinh viên chưa có bài viết chia sẻ nào, sử dụng minh họa quyển sách mở cùng thông điệp mời gọi viết bài.

## 9. States
### 9.1 Loading
- Sử dụng hiệu ứng mờ nhòe (Blur-up) cho ảnh đại diện và ảnh bìa trong quá trình tải. Các dòng thông tin nghề nghiệp được hiển thị dưới dạng thanh màu xám chuyển động nhẹ (Shimmer loading effect).

### 9.2 Empty
- Khi tab "Bài viết" không có dữ liệu:
  - UI Copy: `"Cựu sinh viên này chưa đăng tải bài chia sẻ nào."`

### 9.3 Error
- Lỗi tải hồ sơ cá nhân do mất kết nối hoặc tài khoản đã bị khóa vĩnh viễn:
  - UI Copy: `"Không thể tải thông tin hồ sơ. Hồ sơ này không tồn tại hoặc đã bị ẩn."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo màu xám ở đầu trang: `"Bạn đang xem phiên bản lưu ngoại tuyến của hồ sơ này."`

### 9.5 Permission Restricted
- Nếu người dùng chưa đăng nhập hệ thống hoặc tài khoản bị hạn chế quyền kết nối, các nút tương tác chuyển sang màu xám mờ và hiển thị biểu tượng ổ khóa.

### 9.6 Success / Completed
- Đã gửi yêu cầu kết nối thành công:
  - UI Copy: `"Đã gửi lời mời kết nối thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua Huy hiệu xác thực sẽ hiện Popover giải thích chi tiết: `"Tài khoản cựu sinh viên đã được xác minh bởi phòng Công tác Học sinh - Sinh viên HCMUE."`

### 10.2 Focus
- Toàn bộ các nút chức năng chính và các tab chuyển đổi danh mục đều có khung nét liền 2px màu xanh ngọc bích khi người dùng sử dụng bàn phím để di chuyển (Tab).

### 10.3 Press / Tap
- Thao tác bấm vào "Yêu cầu Mentor" sẽ mở ra biểu mẫu điền lý do tư vấn trực quan dạng slide-in mượt mà từ góc phải trên Desktop hoặc BottomSheet trên Mobile.

### 10.4 Optimistic UI
- Khi bấm "Kết nối", nút chuyển ngay sang trạng thái "Đã gửi lời mời" hoặc "Đã kết nối" ngay lập tức, đồng thời tăng số lượng người kết nối lên 1 đơn vị trước khi nhận phản hồi từ máy chủ.

### 10.5 Menu / Sheet
- Hỗ trợ lưu trữ/chia sẻ nhanh hồ sơ qua mã QR Code độc quyền của UEConnect được hiển thị trực tiếp trong một Popover sạch sẽ.

### 10.6 Toast / Undo
- Cho phép người dùng bấm "Hủy yêu cầu" ngay lập tức từ thanh Toast thông báo vừa gửi lời mời kết nối.

### 10.7 Motion
- Hiệu ứng thu phóng nhẹ ảnh đại diện khi di chuột qua (`scale-105 duration-300`). Chuyển đổi mượt mà giữa các tab hoạt động với hiệu ứng trượt gạch chân ngang (Active Indicator).

## 11. Accessibility Requirements
- Kích thước chạm tối thiểu của mọi nút tương tác trên thiết bị di động đạt chuẩn 48px x 48px.
- Hỗ trợ đầy đủ nhãn mô tả `aria-label` cho các liên kết mạng xã hội bên ngoài (LinkedIn, Facebook).

## 12. Responsive Rules
- Màn hình rộng (>1024px): Bố cục đa cột sang trọng.
- Màn hình nhỏ (<768px): Ảnh bìa (Cover photo) tự động co tỷ lệ 16:9, ảnh đại diện nổi bật đè lên góc dưới bên trái ảnh bìa.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `user_id` (integer, primary)
  - `class_code` (string, ví dụ: "K43.CNTT.A")
  - `company` (string)
  - `position` (string)
  - `skills` (array of strings)
  - `is_mentor` (boolean)
  - `connection_status` (enum: `none`, `pending_sent`, `pending_received`, `connected`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `connectWithAlumni(alumniId)`
  - `disconnectAlumni(alumniId)`
  - `requestMentorship(alumniId, purposeText)`
  - `toggleMentorAvailability()`

## 15. Authorization / Privacy Rules
- Cựu sinh viên có quyền ẩn các thông tin cá nhân nhạy cảm như số điện thoại hoặc email đối với những người dùng chưa kết nối hoặc chưa được xác thực tài khoản sinh viên trường.

## 16. Analytics / Audit Events
- `alumni_profile_viewed`: Theo dõi số lượt truy cập hồ sơ của cựu sinh viên (ẩn danh tính người xem trừ khi được cho phép).
- `mentorship_requested_from_profile`: Ghi nhận sự kiện khi sinh viên gửi yêu cầu hỗ trợ định hướng từ trang cá nhân này.

## 17. Do / Don't
- **Nên làm**: Khuyến khích cựu sinh viên điền đầy đủ niên khóa học tại HCMUE để tạo sự gắn kết đồng môn thân thiết.
- **Không được làm**: Cho phép hiển thị các lời mời kết nối giả mạo hoặc tự động gửi hàng loạt không có kiểm soát gây phiền hà cho cựu sinh viên.

## 18. Acceptance Criteria
- Hiển thị đầy đủ và chính xác tất cả thông tin học tập cũ và công tác hiện tại của cựu sinh viên.
- Chức năng gửi lời mời kết nối và yêu cầu Mentor hoạt động trơn tru, lưu đúng trạng thái vào cơ sở dữ liệu.
- Thiết kế thích ứng hoàn hảo trên mọi kích thước màn hình mà không bị tràn chữ hay chồng lấp ảnh.

## 19. QA / UAT Checklist
- [ ] Kiểm tra huy hiệu xác thực hiển thị đúng và chỉ dành cho tài khoản đã qua xác minh từ trường.
- [ ] Thử nghiệm gửi yêu cầu Mentor và đảm bảo đối phương nhận được thông báo ngay lập tức qua hệ thống thời gian thực (Real-time).
- [ ] Đảm bảo các tab "Bài viết" và "Câu trả lời" chuyển đổi mà không cần tải lại toàn bộ trang (Livewire spa-like experience).
- [ ] Xác minh hiển thị ảnh bìa không bị méo mó trên các thiết bị di động có màn hình tỉ lệ dài như iPhone X/14/15.

## 20. AI Agent Implementation Notes
- Sử dụng Eloquent Polymorphic Relations để liên kết thông tin Hồ sơ giữa sinh viên và cựu sinh viên.
- Sử dụng bộ đệm (Caching) cho thông tin trang cá nhân cựu sinh viên nổi tiếng để giảm số lượng truy vấn database khi có lượt xem đột biến từ sinh viên trong các đợt hướng nghiệp.
