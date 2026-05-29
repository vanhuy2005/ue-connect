---
title: "Saved Profiles Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
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

# Trang Danh Sách Hồ Sơ Đã Lưu (Saved Profiles Bookmarks)

## 1. Purpose
Trang Danh Sách Hồ Sơ Đã Lưu hiển thị toàn bộ danh sách các hồ sơ chân dung Cố vấn (Mentors), cựu sinh viên tiêu biểu, hoặc giảng viên mà người dùng đã bấm lưu trữ (Bookmark) trước đó để theo dõi hoạt động, phục vụ việc xin kết nối tư vấn định hướng lâu dài hoặc tham khảo lý lịch sự nghiệp.

## 2. Product Context
Nằm trong giải pháp tối ưu hóa khả năng kết nối mạng lưới cố vấn đồng môn tại HCMUE, trang này đóng vai trò như một danh bạ học thuật cá nhân hóa, giúp sinh viên không bị thất lạc thông tin các Mentor giỏi giữa hàng trăm người dùng khác.

## 3. User Goals
- Quản lý danh sách các hồ sơ Mentor và cựu sinh viên đã lưu trữ một cách gọn gàng.
- Lọc hồ sơ đã lưu theo Khoa đào tạo cũ, Lĩnh vực chuyên môn hoặc Trạng thái rảnh tư vấn.
- Gửi nhanh yêu cầu kết nối hoặc xin làm Mentee trực tiếp từ giao diện thẻ danh sách.
- Hủy lưu trữ nhanh các hồ sơ không còn nhu cầu theo dõi.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần lưu trữ danh bạ Mentor tiềm năng phục vụ nghiên cứu và hướng nghiệp lâu dài.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Hồ sơ đã lưu** (Saved Profiles).
- Nhấp vào lối tắt "Đã lưu" trên thanh điều hướng cá nhân của trang cá nhân.

## 6. Layout Strategy
Thiết kế tập trung vào sự rõ ràng, bố cục dạng lưới thẻ chân dung sang trọng tương tự trang Directory nhưng được tối giản hóa để phục vụ danh mục cá nhân.

### 6.1 Desktop Layout
- Bố cục lưới 3 cột cân đối (`grid-cols-3`) hiển thị các thẻ chân dung Mentor đã lưu.
- Phía trên là thanh tìm kiếm nhanh nội bộ và bộ lọc thả xuống phân loại theo Chuyên môn/Khoa.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1140px.

### 6.2 Tablet Layout
- Lưới chuyển sang bố cục 2 cột gọn gàng sắc nét giúp ngón tay dễ chạm bấm.
- Khoảng cách lề: 20px.

### 6.3 Mobile / PWA Layout
- Bố cục danh sách dọc 1 cột cuộn mượt mà.
- Mỗi thẻ hồ sơ hiển thị ảnh chân dung tròn bên trái, thông tin lĩnh vực và biểu tượng Bookmark đổi màu nổi bật ở góc phải để hủy lưu nhanh (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh tìm kiếm nhanh (Quick Search Bar)**:
  - Tra cứu nhanh theo tên Mentor hoặc tên doanh nghiệp họ đang công tác trong danh sách đã lưu.
- **Lưới thẻ hồ sơ (Saved Profiles Grid)**:
  - Thẻ gồm: Ảnh đại diện lớn, Họ tên, Nhãn đối tượng học đường (Mentor/Alumni), Chuyên môn chính, Trạng thái lịch rảnh (Available / Fully Booked).
  - Nút hành động: "Kết nối nhanh", biểu tượng Bookmark màu vàng kim (chỉ trạng thái đã lưu).

## 8. Core Components
- **Bookmark Icon Button**: Biểu tượng ruy băng Bookmark màu vàng kim rực rỡ báo hiệu trạng thái đã lưu, tự động đổi màu khi nhấp chọn để hủy lưu mượt mà.
- **Connection Trigger Button**: Nút "Kết nối" màu xanh dương thương hiệu hoặc nút "Nhắn tin" nếu đã hoàn thành kết nối.
- **Saved Count Indicator**: Dòng chữ nhỏ hiển thị tổng số hồ sơ đã lưu ở đầu trang (Ví dụ: `12 hồ sơ đã lưu`).

## 9. States
### 9.1 Loading
- Hiển thị 6 thẻ hồ sơ trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu.

### 9.2 Empty
- Khi người dùng chưa lưu bất kỳ hồ sơ nào:
  - UI Copy: `"Chưa có hồ sơ đã lưu."`
  - Mô tả UI Copy: `"Hãy khám phá Danh mục Mentor để tìm kiếm những người hướng dẫn xuất sắc và lưu lại hồ sơ của họ tại đây nhé!"`

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi kết nối dữ liệu. Vui lòng nhấp để tải lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng hủy lưu hoặc gửi kết nối tạm thời bị khóa mờ xám.

### 9.5 Permission Restricted
- Chỉ dành cho sinh viên trường đã xác thực tài khoản.

### 9.6 Success / Completed
- Hủy lưu hồ sơ thành công:
  - Thẻ hồ sơ biến mất khỏi danh sách hiển thị mượt mà.
  - Hiện Toast thông báo: `"Đã xóa hồ sơ khỏi danh sách lưu trữ thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ hồ sơ sẽ làm thẻ đổi sang màu xám kem dịu (`bg-gray-50`) và phóng to nhẹ ảnh đại diện xem trước. Di chuột qua biểu tượng Bookmark sẽ hiển thị dòng chữ: `"Hủy lưu hồ sơ này"`.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút bấm tương tác (Kết nối, Hủy lưu) khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp chọn biểu tượng Bookmark vàng kim sẽ kích hoạt chuyển màu xám nhạt mượt mà và thực hiện biến mất thẻ khỏi danh sách.

### 10.4 Optimistic UI
- Khi bấm hủy lưu hồ sơ, thẻ hồ sơ lập tức biến mất khỏi danh sách hiển thị tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ lâu (Long-press) vào thẻ hồ sơ trên di động để hiển thị thực đơn thao tác nhanh dạng BottomSheet (Xem chi tiết hồ sơ, Gửi kết nối, Hủy lưu).

### 10.6 Toast / Undo
- Hành động hủy lưu thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh hồ sơ trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng biến mất (Fade-out & Slide-up) của thẻ hồ sơ khi bị hủy lưu diễn ra trong vòng 200ms cực kỳ mượt mà.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi biểu tượng Bookmark phải có thuộc tính `aria-label` mô tả (Ví dụ: `aria-label="Xóa hồ sơ của [Tên Mentor] khỏi danh sách đã lưu"`).
- Hỗ trợ đầy đủ phím di chuyển bàn phím qua các mục thẻ.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Lưới thẻ tài liệu chuyển sang bố cục danh sách dọc 1 cột với khoảng cách lề hai bên là 12px để tăng diện tích hiển thị dọc của thẻ, tránh bị tràn chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `saved_profiles_list` (array of objects: `profile_id`, `name`, `avatar_url`, `role_badge`, `expertise`, `is_available`)
  - `total_saved` (integer)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchSavedProfiles()`
  - `toggleSaveProfileStatus(profileId)`
  - `quickConnectUser(profileId)`

## 15. Authorization / Privacy Rules
- Bảo mật cá nhân tuyệt đối: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền xem danh sách hồ sơ đã lưu cá nhân của họ. Mọi truy vấn trái phép từ tài khoản khác đều bị hệ thống chặn và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `saved_profiles_page_viewed`: Ghi nhận lượt mở xem danh sách hồ sơ đã lưu.
- `saved_profile_removed`: Ghi nhận sự kiện hủy lưu hồ sơ cựu sinh viên/Mentor.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị trạng thái rảnh tư vấn (Availability Status) của Mentor đã lưu để sinh viên dễ dàng nắm bắt thời điểm vàng để đăng ký xin làm Mentee.
- **Không được làm**: Tự động gửi tin nhắn hoặc lời mời kết nối tự động cho tất cả danh sách hồ sơ đã lưu mà chưa có sự đồng ý thủ công từ sinh viên.

## 18. Acceptance Criteria
- Danh sách hồ sơ đã lưu hiển thị đầy đủ, chính xác theo đúng mốc thời gian lưu trữ thực tế.
- Thao tác hủy lưu và cơ chế "Hoàn tác" hoạt động ổn định, không gây trùng lặp dữ liệu API.
- Bố cục thích ứng sắc nét trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra biểu tượng Bookmark đổi trạng thái chính xác khi bấm hủy lưu.
- [ ] Xác minh tính năng "Hoàn tác" khôi phục đúng thẻ hồ sơ về vị trí cũ trong danh sách.
- [ ] Thử nghiệm bấm nút kết nối nhanh trên thẻ hồ sơ đã lưu và kiểm tra xem có gửi đúng yêu cầu kết nối không.
- [ ] Đảm bảo các hồ sơ đã bị khóa hoặc ẩn hoạt động trên hệ thống tự động biến mất khỏi danh sách này.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình Livewire Component kết hợp cùng Alpine.js để quản lý trạng thái hiển thị của các thẻ hồ sơ nhằm đạt hiệu năng giao diện phản hồi nhanh nhất.
- Thiết kế cơ chế phân trang (Pagination) mượt mà hoặc cuộn vô hạn để tối ưu hiệu năng tải trang khi sinh viên có số lượng hồ sơ đã lưu lớn hơn 30 hồ sơ.
---
