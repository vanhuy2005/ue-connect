---
title: "Notifications Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/notifications.md"
related_design_docs:
  - "../04-design/notifications-system.md"
related_system_docs:
  - "../05-system-architecture/notification-service.md"
related_database_docs:
  - "../06-database/notifications-table.md"
related_api_docs:
  - "../07-api/notifications-api.md"
---

# Trang Thông Báo Hệ Thống (Notifications)

## 1. Purpose
Trang Thông Báo Hệ Thống (Notifications) là trung tâm tổng hợp toàn bộ các thông báo tương tác cá nhân (Thích bài viết, Bình luận, Đề xuất kết nối, Lời mời tham gia nhóm), nhắc tên (Mentions), và các thông báo chính thức từ Nhà trường, Đoàn Hội, Ban chủ nhiệm Khoa dành cho người dùng trên UEConnect.

## 2. Product Context
Để xây dựng một kênh truyền thông thông suốt, tức thì và văn minh trong trường Đại học Sư phạm TP.HCM, trang thông báo giúp sinh viên nhanh chóng nắm bắt lịch học, lịch thi, điểm rèn luyện và các tương tác bạn bè thời gian thực theo phong cách dòng chảy tối giản của Threads.

## 3. User Goals
- Tiếp nhận và theo dõi đầy đủ các thông báo tương tác cá nhân học đường.
- Phân loại nhanh các thông báo theo nhóm: "Tất cả", "Nhắc tên", "Bình luận", "Kết nối", "Hệ thống".
- Đánh dấu đã đọc tất cả thông báo chỉ bằng một thao tác bấm chạm để dọn dẹp hộp thư đến.
- Điều hướng nhanh đến đúng bài viết, phòng chat, hoặc sự kiện gốc tương ứng khi nhấp vào thông báo.

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Sinh viên, giảng viên và cựu sinh viên cần cập nhật thông tin tương tác học đường thời gian thực.

## 5. Entry Points
- Nhấp chọn biểu tượng **Thông báo** (Bell/Notification Icon) trên thanh điều hướng chính.
- Nhấp trực tiếp vào thông báo đẩy ngoài màn hình khóa của thiết bị di động.

## 6. Layout Strategy
Thiết kế tối giản, tập trung vào dòng chảy thông báo mượt mà giúp người dùng dễ dàng quét quét nhanh thông tin trong vòng vài giây.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 680px).
- Thanh bộ lọc ngang (Horizontal Filter Bar) nằm ở trên cùng dưới tiêu đề trang.
- Danh sách thông báo dạng thẻ dọc (List View) hiển thị các thông báo sắp xếp theo thời gian mới nhất lên đầu.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên là 20px giúp ngón tay dễ dàng nhấp chọn.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột tràn màn hình hoàn toàn.
- Thanh bộ lọc nhanh được đặt trong một thanh trượt ngang cố định ở trên đầu màn hình dưới tiêu đề.
- Kích thước thẻ thông báo được thiết kế tối giản, ảnh đại diện lớn (44px) và các chữ rõ ràng giúp tay dễ thao tác chạm bấm (`touch-target 44px`).

## 7. Information Architecture
- **Header Thanh điều khiển (Control Header)**:
  - Tiêu đề "Thông báo" lớn, nút "Đánh dấu tất cả đã đọc" (Mark all as read).
- **Bộ lọc nhanh (Filters Carousel)**:
  - Tab "Tất cả", "Phản hồi", "Nhắc tên", "Kết nối", "Nhà trường".
- **Danh sách thông báo (Notifications List)**:
  - Mỗi thẻ thông báo gồm:
    - Ảnh đại diện tác giả tương tác hoặc Logo trường (nếu là thông báo hệ thống).
    - Biểu tượng phân loại nhỏ (Icon Overlay) ở góc dưới ảnh đại diện (Ví dụ: Trái tim đỏ cho Lượt thích, Bong bóng xanh cho Bình luận, Phím cộng cho Kết nối mới).
    - Nội dung văn bản thông báo: Tên người tương tác + Hành động + Trích dẫn nội dung gốc ngắn gọn.
    - Thời gian nhận thông báo (Ví dụ: `2g`, `15ph`).
    - Chấm tròn xanh chỉ trạng thái chưa đọc (Unread Indicator).

## 8. Core Components
- **Notification Type Icon Overlay**: Biểu tượng nhỏ nổi đè lên góc avatar để người dùng dễ nhận diện loại tương tác mà không cần đọc hết chữ.
- **Mark All Read Button**: Phím tắt nhỏ gọn giúp dọn dẹp nhanh hộp thông báo chưa đọc.
- **Redirect Wrapper Card**: Thẻ thông báo thông minh tự động bao gói liên kết điều hướng mượt mà đến phần tử gốc.
- **Unread Status Dot**: Chấm tròn xanh dương thẫm nhỏ hiển thị bên phải thẻ thông báo chỉ trạng thái chưa đọc.

## 9. States
### 9.1 Loading
- Danh sách hiển thị 5 thẻ thông báo trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu.

### 9.2 Empty
- Khi người dùng không có thông báo nào trong danh mục lọc:
  - UI Copy: `"Bạn chưa có thông báo nào."`
  - Mô tả UI Copy: `"Mọi thông báo về lượt thích, bình luận, kết nối học đường và tin tức trường sẽ xuất hiện tại đây."`

### 9.3 Error
- Lỗi tải thông báo do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi tải thông báo. Vui lòng nhấp để thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng đánh dấu đã đọc tạm thời bị vô hiệu hóa.

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho tất cả mọi người dùng đã đăng nhập hợp lệ.

### 9.6 Success / Completed
- Đánh dấu đã đọc thành công:
  - Chấm tròn xanh biến mất mượt mà.
  - Số lượng thông báo chưa đọc trên biểu tượng Bell ở thanh điều hướng giảm trừ chính xác tương ứng.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ thông báo sẽ làm đổi màu nền thẻ sang màu xám kem dịu (`bg-gray-50`) và hiển thị phím tắt hành động nhanh (Đánh dấu đã đọc, Ẩn thông báo này).

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các thẻ thông báo khi di chuyển bằng phím Tab di chuyển tiêu điểm. Hỗ trợ phím Enter để kích hoạt điều hướng nhanh.

### 10.3 Press / Tap
- Thao tác nhấp chọn thẻ thông báo sẽ đánh dấu đã đọc ngay lập tức và chuyển hướng người dùng đến bài viết chi tiết hoặc phòng chat tương ứng trong vòng 150ms cực kỳ mượt mà.

### 10.4 Optimistic UI
- Khi bấm chọn "Đánh dấu tất cả đã đọc", toàn bộ chấm xanh chưa đọc trên danh sách lập tức biến mất và số đếm thông báo trên Bell điều hướng giảm về 0 ngay lập tức trước khi nhận phản hồi xác nhận từ máy chủ.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ lâu (Long-press) vào thẻ thông báo trên di động để hiển thị thực đơn thao tác nhanh dạng BottomSheet (Đánh dấu đã đọc, Tắt thông báo từ người dùng này, Báo cáo vi phạm).

### 10.6 Toast / Undo
- Hành động "Ẩn thông báo này" thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh thông báo trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng biến mất của chấm tròn chưa đọc trơn tru bằng CSS Transition. Hoạt ảnh chuyển tab bộ lọc nhanh trượt mượt mà.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi thẻ thông báo phải có thuộc tính `aria-label` hiển thị nội dung thông báo đầy đủ kèm trạng thái đã đọc hay chưa (Ví dụ: `aria-label="Nguyễn Văn A đã thích bài viết của bạn, 2 giờ trước, chưa đọc"`).
- Hỗ trợ phím Escape để đóng nhanh BottomSheet tùy chọn.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị trích dẫn nội dung gốc để tránh bị tràn màn hình, chỉ hiển thị tóm tắt ngắn gọn dưới 40 ký tự.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `notifications_list` (array of objects: `id`, `sender_info`, `action_type`, `target_url`, `is_read`, `created_at`, `body_preview`)
  - `active_filter` (enum: `all`, `replies`, `mentions`, `connections`, `system`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchNotifications(filter)`
  - `markAsRead(notificationId)`
  - `markAllNotificationsAsRead()`
  - `deleteNotification(notificationId)`

## 15. Authorization / Privacy Rules
- Bảo mật tuyệt đối: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền truy cập xem danh sách thông báo cá nhân này. Mọi truy vấn trái phép đều bị hệ thống chặn và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `notifications_page_viewed`: Ghi nhận lượt mở xem trang thông báo.
- `notification_redirect_performed`: Ghi nhận sự kiện người dùng nhấp thông báo để điều hướng đến nguồn gốc nhằm đánh giá mức độ tương tác.

## 17. Do / Don't
- **Nên làm**: Nhóm các thông báo tương tự nhau lại thành một dòng duy nhất để tránh loãng trang (Ví dụ: `"Nguyễn Văn A và 3 người khác đã thích bài viết của bạn"` thay vì hiển thị 4 dòng thông báo riêng biệt).
- **Không được làm**: Gửi thông báo rác hoặc quảng cáo ngoài lề không liên quan đến học tập hoặc hoạt động chính quy của trường HCMUE.

## 18. Acceptance Criteria
- Đánh dấu đã đọc hoạt động chính xác và đồng bộ tức thời số đếm trên thanh điều hướng chính.
- Tìm kiếm nhanh thông báo hiển thị kết quả chính xác theo tên người tương tác hoặc nội dung tóm tắt.
- Giao diện đáp ứng tốt trên cả nền tối (Dark Mode) và nền sáng (Light Mode).

## 19. QA / UAT Checklist
- [ ] Kiểm tra chấm tròn chưa đọc biến mất chính xác và cập nhật ngay lập tức số lượng thông báo chưa đọc trên Bell điều hướng.
- [ ] Xác minh các thông báo nhóm (grouped notifications) gộp đúng số lượng người tương tác.
- [ ] Thử nghiệm nhấp vào thông báo và đảm bảo điều hướng đúng đến bài viết chi tiết hoặc phòng chat tương ứng.
- [ ] Đảm bảo chỉ báo đang kết nối mạng hoạt động chính xác khi có thông báo mới đẩy về (Real-time Push notifications).

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ thông báo đẩy thời gian thực (Real-time Push) thông qua WebSockets kết hợp Redis để đạt tốc độ phản hồi nhanh nhất dưới 100ms.
- Thiết kế cơ chế phân trang vô tận (Infinite Scroll) thông qua Livewire để tối ưu hóa hiệu năng tải danh sách thông báo khi người dùng có lịch sử hoạt động lớn.
---
