---
title: "Community Channel Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/community-spaces.md"
related_design_docs:
  - "../04-design/messaging-architecture.md"
related_system_docs:
  - "../05-system-architecture/chat-service.md"
related_database_docs:
  - "../06-database/channels-table.md"
related_api_docs:
  - "../07-api/chat-api.md"
---

# Trang Kênh Cộng Đồng (Community Channel)

## 1. Purpose
Trang Kênh Cộng Đồng là không gian thảo luận chuyên sâu, chia sẻ kiến thức, tài liệu học tập và thông báo các hoạt động chính thức dành cho từng phân nhóm, lớp học hoặc câu lạc bộ thuộc hệ sinh thái UEConnect của trường Đại học Sư phạm TP.HCM. Kênh hỗ trợ tổ chức nội dung theo từng chủ đề (Topics) để tránh loãng thông tin.

## 2. Product Context
Nằm trong giải pháp giải quyết vấn đề trôi tin nhắn trên các nền tảng chat thông thường, kênh cộng đồng của UEConnect kết hợp tính thời gian thực của phòng chat và cấu trúc lưu trữ khoa học của diễn đàn, mang lại không gian học thuật có tổ chức cho sinh viên.

## 3. User Goals
- Tham gia thảo luận về các chủ đề chuyên sâu trong lớp hoặc câu lạc bộ.
- Dễ dàng tra cứu các tin nhắn ghim quan trọng từ lớp trưởng, giảng viên hoặc ban chủ nhiệm.
- Tải về các tài liệu học tập, slide bài giảng chia sẻ trong kênh.
- Tạo chủ đề thảo luận mới hoặc tham gia trả lời dưới dạng chuỗi hội thoại (Threads).

## 4. Primary Users
- **Sinh viên trong cùng lớp học / CLB**: Tra cứu tài liệu, trao đổi bài tập nhóm, thảo luận đề tài.
- **Giảng viên / Lớp trưởng / Ban chủ nhiệm**: Đăng thông báo chính thức, ghim bài viết hướng dẫn quan trọng.

## 5. Entry Points
- Nhấp chọn kênh thảo luận cụ thể từ thanh danh sách kênh bên trái trong trang **Nhóm Cộng Đồng** (Community Chat / Spaces).
- Bấm vào thông báo nhắc tên (Mention) hoặc liên kết chia sẻ trực tiếp dẫn tới kênh.

## 6. Layout Strategy
Thiết kế tập trung tối đa vào cấu trúc phân cấp thông tin rõ ràng, hỗ trợ theo dõi các chuỗi thảo luận dài mà không bị mất phương hướng.

### 6.1 Desktop Layout
- Bố cục 3 vùng thông tin chuyên biệt:
  - Vùng 1 bên trái (Sidebar): Danh sách các kênh và chủ đề thảo luận của nhóm.
  - Vùng 2 ở giữa (Main Feed Area): Cột tin nhắn và bài đăng thảo luận chính của kênh được chọn.
  - Vùng 3 bên phải (Detail Drawer): Khung hiển thị tin nhắn ghim, danh sách thành viên kênh hoặc tài liệu đã chia sẻ.
- Khoảng cách lề: 16px. Chiều rộng tối đa: Toàn màn hình (Fluid Layout) để tối ưu hóa không gian làm việc.

### 6.2 Tablet Layout
- Vùng 3 bên phải thu gọn thành dạng ngăn kéo (Drawer) có thể ẩn/hiện bằng nút bấm góc phải trên.
- Sidebar bên trái có thể thu nhỏ thành thanh biểu tượng thu gọn (Collapsed View).

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột tối giản.
- Người dùng chuyển đổi qua lại giữa danh sách kênh và màn hình chat chính bằng cách vuốt ngang màn hình (Swipe gesture).
- Khung nhập tin nhắn tích hợp sẵn nút tải tệp, chèn emoji và định dạng văn bản nhanh.

## 7. Information Architecture
- **Thanh tiêu đề kênh (Channel Header)**:
  - Tên kênh (Ví dụ: `# K45-CNTT-Nghiên-cứu-khoa-học`).
  - Mô tả kênh và số lượng thành viên đang trực tuyến.
- **Dòng thảo luận chính (Discussion Feed)**:
  - Các bài viết/tin nhắn sắp xếp theo dòng thời gian.
  - Trình bày dạng phân cấp: Tin nhắn gốc kèm theo số lượng phản hồi dưới dạng chuỗi (`3 phản hồi`).
- **Thanh công cụ soạn thảo (Composer Row)**:
  - Ô nhập văn bản đa dòng (Rich Text Composer).
  - Các công cụ đính kèm: Hình ảnh, tài liệu (PDF, Word), khảo sát ý kiến (Poll).

## 8. Core Components
- **Thread Reply Indicator**: Nhãn nhỏ dưới mỗi bài đăng hiển thị ảnh đại diện thu nhỏ của những người đã phản hồi và số lượng tin nhắn trong chuỗi.
- **Pin Board Widget**: Bảng ghim các tin nhắn quan trọng hiển thị ở đầu kênh.
- **Resource Attachment Preview**: Thẻ hiển thị xem trước tệp tài liệu đính kèm (định dạng tệp, dung lượng, nút tải xuống).
- **Rich Text Composer**: Hộp soạn thảo văn bản hỗ trợ viết định dạng Markdown cơ bản trực quan.

## 9. States
### 9.1 Loading
- Hiển thị các khối tin nhắn giả lập dạng Shimmer chuyển động mờ nhạt từ dưới lên để mô phỏng dữ liệu đang được tải về theo thời gian thực.

### 9.2 Empty
- Kênh mới lập chưa có cuộc thảo luận nào:
  - UI Copy: `"Chào mừng bạn đến với kênh #tên-kênh!"`
  - Mô tả UI Copy: `"Hãy khởi xướng cuộc thảo luận đầu tiên bằng cách gửi một tin nhắn bên dưới nhé."`

### 9.3 Error
- Lỗi tải tin nhắn do mất mạng:
  - UI Copy: `"Mất kết nối với máy chủ thảo luận. Đang thử kết nối lại..."`

### 9.4 Offline / Reconnecting
- Hiển thị thanh thông báo mỏng màu đỏ ở đầu trang chat. Vô hiệu hóa nút gửi tin nhắn mới và hiển thị biểu tượng đồng hồ chờ bên cạnh các tin nhắn chưa gửi được.

### 9.5 Permission Restricted
- Sinh viên không thuộc lớp học hoặc CLB cố gắng truy cập kênh kín:
  - UI Copy: `"Kênh này thuộc chế độ riêng tư."`
  - Mô tả UI Copy: `"Bạn cần được Ban quản trị hoặc Lớp trưởng phê duyệt tham gia nhóm để xem nội dung."`

### 9.6 Success / Completed
- Tin nhắn gửi đi thành công:
  - Biểu tượng đồng hồ chờ biến mất, thay bằng dấu tích xanh nhỏ chỉ trạng thái đã gửi thành công.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua tin nhắn sẽ hiển thị thanh công cụ nhanh chứa biểu tượng cảm xúc (Quick Emoji Reaction Bar) và nút "Phản hồi theo chuỗi" (Reply in Thread).

### 10.2 Focus
- Ô nhập liệu có vòng viền xanh rõ nét. Phím Tab di chuyển tuần tự qua các tin nhắn để hỗ trợ người dùng khiếm thị dùng trình đọc màn hình.

### 10.3 Press / Tap
- Nhấn giữ tin nhắn trên Mobile mở ra danh mục tùy chọn (Sao chép, Ghim tin nhắn, Phản hồi, Báo cáo vi phạm).

### 10.4 Optimistic UI
- Khi bấm gửi tin nhắn, tin nhắn lập tức xuất hiện trong dòng thảo luận với độ mờ 50% trước khi máy chủ xác nhận lưu thành công để đảm bảo trải nghiệm tức thì không có độ trễ.

### 10.5 Menu / Sheet
- Hộp thoại đính kèm tài liệu mở ra BottomSheet hỗ trợ chọn tệp từ Google Drive hoặc máy cục bộ cực kỳ tiện lợi.

### 10.6 Toast / Undo
- Xóa tin nhắn do chính mình gửi đi sẽ hiển thị Toast xác nhận thành công kèm nút "Hoàn tác" khôi phục tin nhắn trong vòng 3 giây.

### 10.7 Motion
- Tin nhắn mới xuất hiện sẽ trượt nhẹ từ dưới lên với hiệu ứng Spring mượt mà. Đóng mở thanh thông tin bên phải bằng hiệu ứng trượt mượt 200ms.

## 11. Accessibility Requirements
- Trình soạn thảo văn bản hỗ trợ phím nóng `Ctrl + Enter` (hoặc `Cmd + Enter`) để gửi tin nhắn nhanh.
- Đầy đủ thuộc tính `aria-label` cho tất cả các nút biểu tượng cảm xúc và tải tệp đính kèm.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Mặc định ẩn hoàn toàn Sidebar danh sách kênh, người dùng nhấp vào nút Hamburger góc trái trên để trượt mở danh sách kênh.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `channel_id` (integer, primary)
  - `channel_name` (string)
  - `messages_list` (array of objects: `id`, `user`, `content`, `attachments`, `reactions`, `thread_count`)
  - `pinned_messages` (array of integers)

## 14. API / Action Requirements
- Gọi Livewire / Real-time Action:
  - `sendMessage(channelId, contentText, attachments)`
  - `deleteMessage(messageId)`
  - `togglePinMessage(messageId)`
  - `addReaction(messageId, emoji)`

## 15. Authorization / Privacy Rules
- Quyền hạn phân cấp chi tiết: Chỉ có Ban chủ nhiệm/Lớp trưởng mới có quyền ghim tin nhắn, tạo cuộc khảo sát ý kiến hoặc xóa tin nhắn của thành viên khác. Sinh viên thường chỉ có quyền sửa/xóa tin nhắn của chính mình.

## 16. Analytics / Audit Events
- `channel_message_sent`: Ghi nhận số lượng tin nhắn trao đổi trong kênh.
- `channel_file_downloaded`: Theo dõi số lượt tải tài liệu học tập để đánh giá mức độ hữu ích của tài nguyên.

## 17. Do / Don't
- **Nên làm**: Cho phép tìm kiếm tin nhắn cũ trong kênh theo từ khóa và bộ lọc người gửi để tiết kiệm thời gian tra cứu.
- **Không được làm**: Cho phép gửi hàng loạt tệp tin có dung lượng quá lớn (vượt quá 50MB) trên hệ thống chat thời gian thực để tránh nghẽn băng thông.

## 18. Acceptance Criteria
- Tin nhắn được gửi nhận theo thời gian thực (Real-time) chính xác giữa tất cả các thành viên đang trực tuyến trong kênh thông qua WebSockets.
- Hiển thị và tải về thành công các tệp tin đính kèm.
- Các chuỗi hội thoại phụ (Threads) được nhóm riêng biệt, không làm gián đoạn dòng chat chính của kênh.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng gửi tin nhắn và hiển thị tức thì trên các thiết bị khác nhau đang cùng mở kênh.
- [ ] Xác minh tính năng ghim tin nhắn đưa đúng tin nhắn vào bảng ghim đầu trang.
- [ ] Thử nghiệm đính kèm nhiều định dạng tệp khác nhau (PDF, DOCX, ZIP, PNG) để kiểm tra tính tương thích.
- [ ] Đảm bảo cơ chế Optimistic UI hoạt động chuẩn xác và không bị nhân đôi tin nhắn khi mạng chập chờn.

## 20. AI Agent Implementation Notes
- Tích hợp `Laravel Echo` kết hợp với `Pusher` hoặc `Laravel Reverb` để phát sóng (broadcast) các tin nhắn thời gian thực cực kỳ ổn định.
- Lưu trữ các tệp đính kèm học tập trong thư mục riêng phân loại theo từng mã lớp học để dễ dàng quét quét kiểm tra mã độc tự động từ hệ thống.
