---
title: "Conversation Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/messaging.md"
related_design_docs:
  - "../04-design/messaging-architecture.md"
related_system_docs:
  - "../05-system-architecture/chat-service.md"
related_database_docs:
  - "../06-database/messages-table.md"
related_api_docs:
  - "../07-api/chat-api.md"
---

# Trang Giao Diện Trò Chuyện Chi Tiết (Conversation Detail)

## 1. Purpose
Trang Giao Diện Trò Chuyện Chi Tiết (Conversation) là không gian nhắn tin trực tiếp thời gian thực (Direct Messaging) giữa hai cá nhân hoặc trong một nhóm chat nhỏ. Trang hỗ trợ trao đổi văn bản, hình ảnh, chia sẻ vị trí, gửi tài liệu nghiên cứu học thuật và thả biểu cảm cảm xúc (Emoji Reactions) trực tiếp lên tin nhắn.

## 2. Product Context
Nhằm đảm bảo trải nghiệm giao tiếp tức thì và liền mạch của sinh viên HCMUE, trang này được tối ưu hóa tối đa về hiệu năng truyền tải tin nhắn, tốc độ phản hồi và bảo vệ quyền riêng tư cá nhân theo tinh thần học đường văn minh, không spam.

## 3. User Goals
- Gửi và nhận tin nhắn văn bản tức thời với độ trễ dưới 100ms.
- Chia sẻ nhanh hình ảnh chụp slide bài giảng hoặc tài liệu ôn tập định dạng PDF.
- Theo dõi trạng thái hoạt động của đối phương (Trực tuyến, ngoại tuyến, đang gõ chữ).
- Thả cảm xúc biểu cảm nhanh lên tin nhắn để phản hồi ngắn gọn.
- Báo cáo hoặc chặn tài khoản trực tiếp từ thanh công cụ nếu phát hiện hành vi quấy rối hoặc lừa đảo.

## 4. Primary Users
- **Toàn bộ sinh viên, giảng viên và cựu sinh viên HCMUE**: Cần trao đổi công việc, bài tập nhóm hoặc tư vấn hướng nghiệp cá nhân trực tiếp.

## 5. Entry Points
- Nhấp chọn một hội thoại bất kỳ từ trang **Trung tâm Trò chuyện Cộng đồng** (Community Chat Hub).
- Bấm nút **Nhắn tin** (Message) từ trang hồ sơ cá nhân của người dùng khác.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật dòng tin nhắn, ẩn đi các chi tiết thừa để người dùng tập trung hoàn toàn vào nội dung trò chuyện.

### 6.1 Desktop Layout
- Hiển thị trong khung nội dung chi tiết bên phải của giao diện Chat Hub hoặc chế độ toàn màn hình mượt mà.
- Chiều cao cố định chiếm 100% chiều cao cửa sổ hiển thị trình duyệt.
- Dòng tin nhắn cuộn dọc tự động hiển thị tin nhắn mới nhất ở dưới cùng.
- Khoảng cách lề tin nhắn: 20px.

### 6.2 Tablet Layout
- Tương tự Desktop, tự động căn chỉnh khoảng cách lề hai bên rộng rãi để ngón tay dễ dàng nhấp chọn tin nhắn.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột tràn màn hình hoàn toàn đè lên trên danh sách phòng chat.
- Thanh công cụ trên đầu chứa nút "Quay lại" lớn ở bên trái và ảnh đại diện kèm tên đối phương ở giữa.
- Hộp thoại nhập tin nhắn cố định ở đáy màn hình, tự động đẩy lên phía trên khi bàn phím ảo xuất hiện để tránh che khuất dòng chat.

## 7. Information Architecture
- **Thanh công cụ tiêu đề (Chat Header)**:
  - Ảnh đại diện đối phương, Tên hiển thị, Trạng thái hoạt động (Đang hoạt động, Hoạt động X phút trước).
  - Nút gọi thoại/gọi video (nếu tích hợp).
  - Nút Tùy chọn (Menu chứa các hành động: Xem hồ sơ, Tắt thông báo, Tìm kiếm tin nhắn, Báo cáo vi phạm, Chặn).
- **Dòng hội thoại chính (Message Stream Area)**:
  - Tin nhắn sắp xếp theo dòng thời gian từ cũ đến mới.
  - Phân chia bố cục rõ rệt: Tin nhắn của đối phương nằm bên trái (Nền xám nhạt, chữ đen); tin nhắn của mình nằm bên phải (Nền xanh dương HCMUE, chữ trắng).
- **Thanh soạn thảo tin nhắn (Input Composer)**:
  - Ô nhập liệu văn bản tích hợp biểu tượng chèn ảnh và tệp tài liệu bên trái, biểu tượng cảm xúc bên phải.
  - Biểu tượng nút Gửi (Send Button) chuyển màu sắc sinh động khi người dùng bắt đầu nhập ký tự.

## 8. Core Components
- **Message Bubble**: Bong bóng tin nhắn bo tròn các góc mềm mại, hiển thị đẹp mắt định dạng Markdown và đường liên kết tự động nhận diện (Auto-link preview).
- **Typing Indicator**: Chỉ báo đang soạn thảo dạng ba chấm nhỏ chuyển động nhấp nhô tuần hoàn mượt mà bên góc trái màn hình.
- **Emoji Reaction Panel**: Thanh bong bóng nhỏ xuất hiện phía trên tin nhắn khi bấm giữ lâu để người dùng nhấp chọn nhanh biểu cảm biểu tượng cảm xúc (👍, ❤️, 😂, 😮, 😢, 🙏).
- **Sticky Date Divider**: Thanh phân cách ngày tháng (Ví dụ: "Hôm nay", "Hôm qua", "25 Tháng 5, 2026") luôn ghim mờ ở trên cùng dòng tin nhắn khi cuộn trang.

## 9. States
### 9.1 Loading
- Hiển thị spinner xoay tròn nhỏ ở giữa dòng tin nhắn cũ khi người dùng cuộn ngược lên trên đầu để tải thêm lịch sử trò chuyện (Infinite Scroll Backwards).

### 9.2 Empty
- Khi hai người mới bắt đầu cuộc hội thoại và chưa gửi bất kỳ tin nhắn nào:
  - UI Copy: `"Bắt đầu cuộc trò chuyện với @username"`
  - Mô tả UI Copy: `"Hãy gửi lời chào thân thiện để bắt đầu chia sẻ kiến thức nhé!"`

### 9.3 Error
- Gửi tin nhắn thất bại do mất kết nối mạng đột ngột:
  - Tin nhắn hiển thị biểu tượng chấm than tròn đỏ bên cạnh kèm dòng chữ nhỏ: `"Không thể gửi. Nhấp để thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo nhỏ màu đỏ dưới thanh tiêu đề: `"Đang mất kết nối mạng. Tin nhắn sẽ tự động gửi khi bạn trực tuyến trở lại."`

### 9.5 Permission Restricted
- Trường hợp đối phương đã chặn bạn:
  - Ô soạn thảo tin nhắn bị biến mất hoàn toàn và thay bằng dòng thông báo: `"Bạn không thể gửi tin nhắn cho tài khoản này lúc này."`

### 9.6 Success / Completed
- Tin nhắn gửi thành công và đối phương đã xem:
  - Hiển thị chữ `"Đã xem"` hoặc dấu tích xanh nhỏ dưới góc phải của bong bóng tin nhắn cuối cùng do mình gửi đi.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua tin nhắn sẽ hiển thị biểu tượng Emoji nhanh và nút chuyển tiếp tin nhắn (Forward) bên cạnh.

### 10.2 Focus
- Ô nhập tin nhắn nhận tiêu điểm ngay khi mở phòng chat. Hỗ trợ phím nóng `Escape` để đóng bàn phím hoặc quay lại trang danh sách phòng chat.

### 10.3 Press / Tap
- Nhấn giữ lâu (Long-press) vào bong bóng tin nhắn trên Mobile sẽ kích hoạt rung nhẹ thiết bị và mở ra bảng thả cảm xúc kèm thực đơn hành động nhanh (Sao chép văn bản, Trả lời, Chuyển tiếp, Ghim tin nhắn, Xóa tin nhắn).

### 10.4 Optimistic UI
- Khi bấm nút Gửi, tin nhắn lập tức bay vào dòng chat phía dưới cùng kèm dấu tích xám báo hiệu "Đang gửi" mà không đợi API máy chủ phản hồi. Khi API thành công, tích chuyển sang màu xanh dương thẫm.

### 10.5 Menu / Sheet
- Nút Tùy chọn (Menu) ở góc phải trên cùng mở ra BottomSheet mượt mà trên Mobile chứa các thông tin cá nhân của đối phương và danh mục tùy chỉnh quyền riêng tư.

### 10.6 Toast / Undo
- Xóa tin nhắn ở chế độ "Chỉ xóa ở phía tôi" sẽ hiện Toast thông báo thành công cùng nút "Hoàn tác" khôi phục tin nhắn trong vòng 4 giây.

### 10.7 Motion
- Bong bóng tin nhắn mới xuất hiện trượt từ dưới lên kèm hiệu ứng nảy nhẹ (Elastic Bounce animation) vô cùng sống động. Hiệu ứng hiển thị chỉ báo đang gõ chữ trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Cung cấp mô tả bằng giọng nói cho trình đọc màn hình về trạng thái trực tuyến của đối phương.
- Hỗ trợ phím Tab di chuyển tuần tự qua các dòng tin nhắn và đọc to nội dung văn bản.

## 12. Responsive Rules
- Màn hình di động (<768px): Chiều ngang bong bóng tin nhắn chiếm tối đa 80% chiều rộng màn hình để chừa khoảng cách khoảng trống giúp người dùng dễ dàng vuốt chạm cuộn trang mà không bị kích hoạt nhầm các sự kiện bấm giữ tin nhắn.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `conversation_id` (integer, primary)
  - `recipient_info` (object: `id`, `name`, `avatar_url`, `is_online`)
  - `messages` (array of objects: `id`, `sender_id`, `content`, `media_url`, `created_at`, `reactions`)

## 14. API / Action Requirements
- Gọi Livewire / Real-time Action:
  - `sendChatMessage(conversationId, contentText)`
  - `deleteChatMessage(messageId, deleteType)`
  - `reactToMessage(messageId, emoji)`
  - `broadcastTypingEvent(conversationId, isTyping)`

## 15. Authorization / Privacy Rules
- Bảo mật tuyệt đối: Chỉ có hai người trong cuộc trò chuyện mới có quyền truy cập API lấy lịch sử tin nhắn. Mọi yêu cầu trái phép đều bị hệ thống ghi nhật ký cảnh báo và khóa địa chỉ IP truy cập ngay lập tức.

## 16. Analytics / Audit Events
- `chat_message_sent_direct`: Ghi nhận số lượng tin nhắn cá nhân được trao đổi.
- `chat_call_initiated`: Ghi nhận số cuộc gọi kết nối trong ứng dụng (nếu có).

## 17. Do / Don't
- **Nên làm**: Tự động thu gọn các chuỗi tin nhắn gửi liên tục từ cùng một người dùng trong vòng 1 phút bằng cách ẩn ảnh đại diện lặp lại, chỉ hiển thị ảnh đại diện ở tin nhắn đầu tiên để giao diện thoáng đãng hơn.
- **Không được làm**: Cho phép hiển thị tệp tin tải lên trực tiếp trong dòng chat khi tệp chưa được quét vi-rút bảo mật tự động của hệ thống.

## 18. Acceptance Criteria
- Tin nhắn gửi nhận thời gian thực (Real-time WebSockets) trơn tru, không bị mất tin nhắn khi thay đổi giữa mạng Wifi và 4G di động.
- Trạng thái gõ chữ (Typing Indicator) hiển thị chính xác và biến mất ngay lập tức khi đối phương dừng gõ quá 3 giây hoặc xóa sạch chữ trong ô nhập liệu.
- Lịch sử chat được tải phân trang cuộn ngược (Infinite Scroll Backwards) ổn định, không bị nhảy vị trí cuộn trang đột ngột khi tải xong dữ liệu cũ.

## 19. QA / UAT Checklist
- [ ] Kiểm tra gửi tin nhắn văn bản tiếng Việt có dấu, ký tự đặc biệt và biểu tượng cảm xúc hiển thị đúng định dạng.
- [ ] Xác minh tính năng đính kèm ảnh chụp hoạt động trơn tru và nén ảnh tự động trước khi tải lên để tiết kiệm dung lượng.
- [ ] Thử nghiệm thao tác chặn đối phương và kiểm tra xem cả hai có bị vô hiệu hóa gửi tin nhắn cho nhau ngay lập tức không.
- [ ] Đảm bảo chỉ báo đang gõ chữ hiển thị chính xác khi người dùng gõ từ thiết bị di động sang máy tính.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `Alpine.js` để quản lý trạng thái của ô nhập liệu, Emoji Picker và các hoạt ảnh chuyển động tại client giúp giảm thiểu số lượng sự kiện Livewire gửi về máy chủ gây tốn tài nguyên.
- Sử dụng giải pháp lưu trữ hình ảnh trên Amazon S3 hoặc dịch vụ lưu trữ đám mây tương đương, tích hợp CDN tăng tốc độ tải ảnh xem trước trong khung chat.
---
