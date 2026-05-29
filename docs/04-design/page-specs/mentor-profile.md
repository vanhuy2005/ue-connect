---
title: "Mentor Profile Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/mentor-profile.md"
related_design_docs:
  - "../04-design/mentorship-program.md"
related_system_docs:
  - "../05-system-architecture/mentorship-service.md"
related_database_docs:
  - "../06-database/mentors-table.md"
related_api_docs:
  - "../07-api/mentorship-api.md"
---

# Trang Hồ Sơ Người Định Hướng (Mentor Profile)

## 1. Purpose
Trang Hồ Sơ Người Định Hướng (Mentor Profile) là trang thông tin chuyên sâu giới thiệu chi tiết về lý lịch khoa học, quá trình công tác, thành tựu nghề nghiệp, lịch sử hướng dẫn sinh viên, các đánh giá phản hồi (Reviews) từ các thế hệ Mentees trước và lịch nhận tư vấn rảnh rỗi của một Cố vấn cụ thể trong hệ thống UEConnect.

## 2. Product Context
Trang hồ sơ này là trái tim của chương trình Mentorship tại HCMUE. Nó cung cấp đầy đủ dữ liệu minh bạch, tin cậy để sinh viên đối chiếu và đưa ra quyết định đăng ký xin hướng dẫn từ người Cố vấn phù hợp nhất với mục tiêu học tập và phát triển sự nghiệp của mình.

## 3. User Goals
- **Đối với sinh viên**: Tìm hiểu chi tiết kinh nghiệm thực tế của Mentor, xem các phản hồi từ sinh viên khác, kiểm tra lịch trống và đăng ký xin làm Mentee chính thức.
- **Đối với Mentor**: Quản lý trang hồ sơ cá nhân, cập nhật lịch rảnh tư vấn (Availability Calendar), phản hồi các bài đánh giá nhận xét và theo dõi danh sách sinh viên mình đang hỗ trợ định hướng.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần thông tin chi tiết và tin cậy để chọn người Cố vấn phù hợp.
- **Cố vấn (Mentor) chính danh**: Muốn xây dựng uy tín cá nhân và cống hiến tri thức cho thế hệ đàn em đi sau.

## 5. Entry Points
- Nhấp chọn thẻ Mentor từ trang **Danh mục Người định hướng** (Mentor Directory) hoặc từ kết quả tìm kiếm.
- Nhấp vào liên kết tên Mentor trên bài đăng Bảng tin hoặc danh sách Ban cố vấn của một Câu lạc bộ khoa học.

## 6. Layout Strategy
Thiết kế trang trang nghiêm, khoa học, phân chia rõ ràng giữa thông tin lý lịch nghề nghiệp và công cụ tương tác đăng ký lịch rảnh.

### 6.1 Desktop Layout
- Bố cục 2 cột bất đối xứng (65% - 35%):
  - Cột bên trái rộng rãi (65%): Hiển thị Tóm tắt giới thiệu bản thân, Bản đồ hành trình sự nghiệp (Career Timeline), Danh sách kỹ năng chuyên môn, và khối Đánh giá phản hồi (Mentees Reviews).
  - Cột bên phải (35%): Khung tương tác đăng ký cố vấn (Mentorship Application Widget) tích hợp Lịch hiển thị các khung giờ rảnh khả dụng (Availability Slot Picker) và nút gửi đơn đăng ký.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1140px.

### 6.2 Tablet Layout
- Cột tương tác đăng ký bên phải chuyển thành dạng cố định ở lề dưới màn hình hoặc trượt lên thành BottomSheet khi nhấp chọn nút "Đăng ký tư vấn" lớn ở chân trang.
- Khung thông tin sự nghiệp chiếm toàn bộ chiều ngang hiển thị trên màn hình nằm ngang với lề 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc hoàn hảo.
- Các phần thông tin được nhóm gọn gàng thành các mục mở rộng (Accordion Panels) để sinh viên dễ dàng nhấp mở xem từng phần (Giới thiệu, Sự nghiệp, Đánh giá) mà không phải cuộn trang quá dài.
- Nút "Đăng ký cố vấn" luôn hiển thị cố định ở chân màn hình (Sticky Bottom Action Bar) với kích thước lớn dễ chạm (`touch-target 48px`).

## 7. Information Architecture
- **Phần đầu trang (Profile Header)**:
  - Ảnh chân dung lớn, Họ tên, Huy hiệu xác minh vàng kim.
  - Vị trí công tác hiện tại, Niên khóa học cũ tại HCMUE (Ví dụ: Cựu sinh viên K39 Khoa Sư phạm Tiếng Anh).
  - Số sao đánh giá trung bình và số lượng sinh viên đã hướng dẫn thành công.
- **Hành trình sự nghiệp (Career Journey)**:
  - Bản đồ thời gian (Timeline) ghi nhận các mốc công tác và thành tựu nổi bật của Mentor.
- **Đánh giá phản hồi (Mentee Reviews Section)**:
  - Danh sách bài nhận xét từ sinh viên: Điểm số sao, nội dung nhận xét chi tiết, thời gian hướng dẫn.
- **Khung lịch rảnh (Availability Grid)**:
  - Các ô hiển thị khung giờ trống khả dụng trong tuần (Ví dụ: "Thứ Hai: 19:00 - 20:30").

## 8. Core Components
- **Career Path Timeline**: Dòng thời gian hiển thị lịch sử làm việc mượt mà sử dụng các chấm tròn nối nhau bằng đường kẻ mảnh đứng.
- **Rating Breakdowns**: Đồ thị thanh ngang hiển thị chi tiết số lượng đánh giá theo cấp độ sao (5 sao, 4 sao...).
- **Availability Slot Picker**: Lưới hiển thị các khung giờ rảnh của Mentor dưới dạng các nút bấm nhỏ đổi màu viền khi người dùng nhấp chọn.
- **Mentorship Request Form Sheet**: Biểu mẫu viết thư bày tỏ nguyện vọng xin cố vấn mở ra dạng BottomSheet cực kỳ sạch sẽ và tinh tế.

## 9. States
### 9.1 Loading
- Hiển thị hiệu ứng Shimmer mờ nhòe cho ảnh chân dung và các dòng mô tả công việc. Khung chọn lịch rảnh hiển thị 4 ô nút bấm trống dạng placeholder xám nhạt nhấp nháy tuần hoàn.

### 9.2 Empty
- Khi Mentor chưa có bài đánh giá nào từ sinh viên:
  - UI Copy: `"Chưa có đánh giá nào từ sinh viên."`
  - Mô tả UI Copy: `"Những phản hồi từ sinh viên sau khi hoàn thành khóa hướng dẫn sẽ xuất hiện tại đây."`

### 9.3 Error
- Lỗi tải thông tin hồ sơ do sự cố mạng hoặc ID hồ sơ không hợp lệ:
  - UI Copy: `"Không thể tải thông tin hồ sơ Mentor. Vui lòng kiểm tra kết nối mạng và thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo ngoại tuyến ở đầu trang. Khung lịch rảnh tạm thời bị vô hiệu hóa và hiển thị ở trạng thái mờ xám.

### 9.5 Permission Restricted
- Sinh viên đã dùng hết giới hạn số đơn đăng ký xin Mentor trong tháng (Tối đa 3 đơn mỗi tháng):
  - Khung đăng ký hiển thị thông điệp cảnh báo: `"Bạn đã đạt giới hạn gửi yêu cầu Cố vấn trong tháng này."` và vô hiệu hóa nút gửi đơn.

### 9.6 Success / Completed
- Đơn xin làm Mentee được gửi đi thành công:
  - Hiện Toast thông báo xanh dịu: `"Yêu cầu xin cố vấn của bạn đã được gửi đến Mentor thành công!"` kèm âm thanh phản hồi nhẹ.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các ô khung giờ rảnh khả dụng sẽ làm sáng viền ô và hiển thị dòng chữ: `"Nhấp chọn khung giờ này"`.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút chọn khung giờ và nút gửi đơn khi người dùng di chuyển bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn khung giờ rảnh khả dụng sẽ kích hoạt chuyển màu nền ô sang màu xanh dương thẫm và lưu giá trị chọn tức thì. Nhấp chọn "Đăng ký cố vấn" sẽ mở BottomSheet biểu mẫu trơn tru.

### 10.4 Optimistic UI
- Khi sinh viên nhấp chọn khung giờ khả dụng, cập nhật ngay trạng thái giao diện của ô giờ đó thành "Đang chọn" mà không bị gián đoạn hay đơ giao diện.

### 10.5 Menu / Sheet
- Hộp thoại viết thư xin cố vấn được thiết kế tối giản, hỗ trợ viết định dạng chữ lớn, ô nhập liệu rộng rãi có nhãn động thu phóng mượt mà.

### 10.6 Toast / Undo
- Đăng ký gửi đơn thành công cho phép sinh viên bấm nút "Hủy đơn" trực tiếp trên Toast thông báo trong vòng 4 giây nếu muốn sửa lại nội dung thư.

### 10.7 Motion
- Hiệu ứng chuyển động nảy nhẹ của các sao đánh giá khi di chuột qua. Hoạt ảnh chuyển tiếp mở rộng của các Accordion Panels diễn ra mượt mà trong vòng 200ms.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi bài đánh giá phải có thuộc tính `aria-label` hiển thị số điểm đánh giá (Ví dụ: `aria-label="Đánh giá 5 sao từ sinh viên Nguyễn Văn A"`).
- Hỗ trợ đầy đủ phím đóng `Escape` để thoát nhanh khỏi BottomSheet biểu mẫu đăng ký.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Ảnh chân dung Mentor hiển thị ở giữa màn hình, lịch rảnh khả dụng chuyển thành danh sách dọc cuộn đơn giản và nút gửi đơn luôn ghim mượt dưới chân màn hình điện thoại.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `mentor_id` (integer, primary)
  - `bio_summary` (text)
  - `career_timeline` (array of objects: `year`, `title`, `company`, `description`)
  - `available_slots` (array of objects: `slot_id`, `day_of_week`, `start_time`, `end_time`)
  - `reviews` (array of objects: `reviewer_name`, `rating`, `comment`, `created_at`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadMentorProfileContent()`
  - `selectAvailabilitySlot(slotId)`
  - `submitMentorshipApplication(mentorId, slotId, letterText)`
  - `cancelApplication(applicationId)`

## 15. Authorization / Privacy Rules
- Chỉ sinh viên chính quy đã xác thực tài khoản sinh viên trường mới có quyền xem lịch trống khả dụng và gửi đơn xin cố vấn. Người dùng khách chỉ được xem thông tin lý lịch công khai của Mentor.

## 16. Analytics / Audit Events
- `mentor_profile_viewed`: Ghi nhận số lượt mở xem chi tiết hồ sơ Mentor.
- `mentorship_application_initiated`: Ghi nhận sự kiện sinh viên nhấp nút đăng ký cố vấn để đánh giá mức độ quan tâm đối với chương trình.

## 17. Do / Don't
- **Nên làm**: Cho phép Mentor phản hồi công khai các bài đánh giá nhận xét dưới dạng bình luận phụ (Replies) để tạo sự tương tác hai chiều văn minh, xây dựng.
- **Không được làm**: Cho phép sinh viên gửi đơn xin cố vấn liên tục cho cùng một Mentor khi đơn trước đó vẫn đang ở trạng thái `"Đang chờ duyệt"`.

## 18. Acceptance Criteria
- Hiển thị đầy đủ, chính xác toàn bộ thông tin sự nghiệp, lịch rảnh và phản hồi đánh giá của Mentor.
- Khung chọn giờ rảnh hoạt động chính xác, không bị trùng lặp giờ và gửi đúng đơn đăng ký về máy chủ cơ sở dữ liệu.
- Thiết kế thích ứng mượt mà, sắc nét trên mọi loại màn hình thiết bị di động và máy tính.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng chọn giờ rảnh và đảm bảo thông tin giờ chọn hiển thị đúng trong biểu mẫu đăng ký.
- [ ] Xác minh tính năng gửi đơn đăng ký hoạt động hoàn hảo, gửi thông báo đẩy (push) ngay lập tức về tài khoản của Mentor.
- [ ] Thử nghiệm gửi đơn khi đã đạt giới hạn 3 đơn trong tháng và đảm bảo hệ thống chặn chính xác, hiển thị cảnh báo trực quan.
- [ ] Đảm bảo giao diện chuyển đổi Dark Mode hiển thị rõ chữ và không bị lóa mắt.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu trữ thời gian thực (Real-time DB) để tự động khóa tạm thời khung giờ rảnh của Mentor ngay khi có sinh viên nhấp chọn, tránh trường hợp 2 sinh viên cùng đăng ký trùng lặp một khung giờ tại cùng một thời điểm.
- Thiết kế Model `MentorshipApplication` có cơ chế tự động gửi email thông báo nhắc lịch cho cả Mentor và sinh viên trước giờ hẹn tư vấn thực tế 2 giờ đồng hồ.
---
