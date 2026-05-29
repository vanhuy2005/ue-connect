---
title: "Community Chat Hub Page"
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
  - "../06-database/chats-table.md"
related_api_docs:
  - "../07-api/chat-api.md"
---

# Trang Trung Tâm Trò Chuyện Cộng Đồng (Community Chat Hub)

## 1. Purpose
Trang Trung Tâm Trò Chuyện Cộng Đồng là cổng điều hướng trung tâm kết nối toàn bộ các kênh chat lớp học, nhóm nghiên cứu khoa học, câu lạc bộ và các cuộc hội thoại riêng tư (Direct Messages) của người dùng trên toàn hệ thống UEConnect. Trang này giúp theo dõi nhanh tất cả thông báo tin nhắn chưa đọc và điều hướng tức thì đến các phòng thảo luận tương ứng.

## 2. Product Context
Để tối ưu hóa hiệu quả trao đổi thông tin trong môi trường học đường số HCMUE, trang này được thiết kế theo mô hình tối giản tương tự như trung tâm nhắn tin của Threads kết hợp với cách tổ chức danh sách phòng thông minh để sinh viên không bỏ lỡ lịch học hay thông báo khẩn cấp từ Đoàn khoa.

## 3. User Goals
- Xem toàn bộ danh sách phòng trò chuyện riêng tư và kênh cộng đồng đang tham gia.
- Phát hiện nhanh các phòng có tin nhắn mới qua bong bóng thông báo (Unread Badges).
- Tìm kiếm nhanh người dùng khác trong trường để khởi đầu một cuộc trò chuyện mới.
- Tạo nhóm chat học tập mới và mời các bạn học cùng lớp tham gia thảo luận.

## 4. Primary Users
- **Toàn bộ học viên, sinh viên và cựu sinh viên HCMUE**: Cần giữ liên lạc với giảng viên, nhóm bài tập lớn hoặc bạn bè cùng khóa học.

## 5. Entry Points
- Nhấp chọn biểu tượng **Trò chuyện** (Chat Icon) ở thanh công cụ chính (Desktop Header / Mobile Bottom Bar).
- Chuyển hướng trực tiếp khi nhấp vào thông báo tin nhắn mới ở ngoài màn hình khóa.

## 6. Layout Strategy
Áp dụng thiết kế lưới phân vùng linh hoạt để đảm bảo thao tác chuyển đổi phòng diễn ra trơn tru nhất.

### 6.1 Desktop Layout
- Bố cục chia đôi màn hình (Split Panel Layout - Master-Detail):
  - Sidebar bên trái chiếm 1/3 chiều rộng màn hình: Danh sách các phòng chat xếp dọc, tích hợp thanh tìm kiếm trên cùng và bộ lọc nhanh.
  - Khung bên phải chiếm 2/3 chiều rộng: Hiển thị chi tiết nội dung phòng chat đang chọn. Nếu chưa chọn phòng nào, hiển thị hình ảnh chào mừng sinh viên.
- Không có khoảng cách lề thừa (Edge-to-edge layout) để tận dụng tối đa diện tích màn hình máy tính.

### 6.2 Tablet Layout
- Tương tự Desktop nhưng Sidebar bên trái tự động thu gọn phần chữ, chỉ hiển thị ảnh đại diện phòng và chấm tròn thông báo tin nhắn chưa đọc.

### 6.3 Mobile / PWA Layout
- Bố cục 1 màn hình duy nhất. Mặc định hiển thị danh sách tất cả các phòng trò chuyện.
- Khi người dùng nhấp chọn một phòng chat bất kỳ, màn hình chuyển tiếp mượt mà sang giao diện phòng chat chi tiết (Full-screen Chat View) đè lên trên danh sách phòng. Vuốt từ lề trái màn hình sang phải để quay lại danh sách phòng.

## 7. Information Architecture
- **Header thanh điều khiển (Control Header)**:
  - Tiêu đề "Trò chuyện" lớn, nút "Tạo nhóm chat mới".
  - Thanh tìm kiếm hội thoại thời gian thực.
- **Bộ lọc nhanh (Quick Filters)**:
  - Tab "Tất cả", "Cá nhân" (Direct Messages), "Nhóm học tập & CLB", "Chưa đọc".
- **Danh sách phòng trò chuyện (Chat Rooms List)**:
  - Thẻ phòng gồm: Ảnh đại diện nhóm/cá nhân, Tên phòng, Tin nhắn cuối cùng kèm thời gian, Chấm xanh chỉ trạng thái trực tuyến (Online Indicator), Bong bóng số lượng tin nhắn chưa đọc (Unread Count).

## 8. Core Components
- **Online Indicator Dot**: Chấm xanh lá cây nhỏ sáng rực rỡ (`bg-green-500` có hiệu ứng gợn sóng nhẹ ở viền) nằm ở góc dưới bên phải ảnh đại diện người dùng để báo hiệu họ đang trực tuyến.
- **Unread Count Badge**: Vòng tròn màu đỏ cam chứa số lượng tin nhắn chưa đọc nổi bật ở góc phải thẻ phòng chat.
- **New Chat Modal**: Hộp thoại sạch sẽ hỗ trợ tìm kiếm nhanh sinh viên theo tên hoặc mã số sinh viên để bắt đầu cuộc trò chuyện.
- **Empty State Hub**: Hiển thị hình minh họa hai chiếc điện thoại kết nối sóng vô tuyến nhẹ nhàng khi người dùng chưa có cuộc trò chuyện nào.

## 9. States
### 9.1 Loading
- Danh sách phòng hiển thị 5-6 dòng placeholder mờ nhạt với các vòng tròn và thanh Shimmer màu xám sáng quét ngang định kỳ.

### 9.2 Empty
- Khi người dùng mới chưa kết nối với ai và chưa tham gia nhóm nào:
  - UI Copy: `"Chưa có cuộc trò chuyện nào."`
  - Mô tả UI Copy: `"Hãy tìm kiếm bạn bè trong trường hoặc thầy cô giáo để bắt đầu trao đổi bài học nhé!"`

### 9.3 Error
- Lỗi tải danh sách phòng chat do mất kết nối mạng:
  - UI Copy: `"Không thể tải danh sách trò chuyện. Đang thử kết nối lại..."`

### 9.4 Offline / Reconnecting
- Hiển thị banner mỏng màu xám sẫm ở đầu danh sách: `"Đang hoạt động ngoại tuyến. Tin nhắn mới sẽ hiển thị khi kết nối lại."`

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho tất cả tài khoản đã đăng nhập hợp lệ.

### 9.6 Success / Completed
- Danh sách hội thoại tải đầy đủ, tự động sắp xếp các phòng có tin nhắn mới nhất hoặc tin nhắn chưa đọc lên vị trí trên cùng.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ phòng chat sẽ làm đổi màu nền thẻ sang màu xám kem dịu mắt (`bg-gray-50` hoặc `dark:bg-zinc-900`) và hiển thị nút tùy chọn phụ (Ghim phòng chat, Tắt tiếng thông báo).

### 10.2 Focus
- Sử dụng phím Tab di chuyển qua danh sách phòng sẽ hiển thị viền bao quanh nét liền màu xanh đậm thương hiệu. Hỗ trợ phím mũi tên Lên/Xuống để di chuyển nhanh giữa các phòng chat.

### 10.3 Press / Tap
- Thao tác nhấp chọn phòng chat trên Mobile sẽ mở phòng ngay lập tức với hiệu ứng trượt từ phải sang trái (Slide-in-right) trong vòng 200ms.

### 10.4 Optimistic UI
- Khi bấm "Ghim phòng chat lên đầu", thẻ phòng lập tức di chuyển lên vị trí ưu tiên cao nhất trước khi máy chủ xác nhận lưu trạng thái ghim thành công.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ lâu (Long-press) vào thẻ phòng chat trên Mobile để hiển thị thực đơn thao tác nhanh dạng BottomSheet (Đánh dấu đã đọc, Ghim nhóm, Tắt thông báo, Rời khỏi cuộc trò chuyện).

### 10.6 Toast / Undo
- Hành động "Tắt tiếng thông báo nhóm" thành công sẽ hiện Toast màu xanh kèm nút "Hoàn tác" để khôi phục nhanh âm thanh thông báo.

### 10.7 Motion
- Hiệu ứng chuyển động đẩy thẻ phòng có tin nhắn mới lên đầu danh sách diễn ra mượt mà nhờ công nghệ sắp xếp lại vị trí động (FLIP Animation) trong 300ms.

## 11. Accessibility Requirements
- Trình đọc màn hình phải đọc chính xác cấu trúc: `"[Tên phòng], [Tin nhắn cuối cùng], [Thời gian], [Số tin nhắn chưa đọc] tin nhắn chưa đọc"`.
- Đầy đủ thuộc tính `aria-selected` cho tab bộ lọc nhanh đang được kích hoạt.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị tin nhắn cuối cùng để tránh bị tràn màn hình, chỉ hiển thị tóm tắt ngắn gọn dưới 30 ký tự.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `rooms_list` (array of objects: `room_id`, `room_name`, `avatar_url`, `last_message`, `unread_count`, `is_online`, `is_pinned`)
  - `active_filter` (enum: `all`, `direct`, `group`, `unread`)

## 14. API / Action Requirements
- Gọi Livewire / Real-time Action:
  - `fetchChatRooms(filter)`
  - `pinChatRoom(roomId)`
  - `muteNotifications(roomId, duration)`
  - `createNewGroupChat(memberIds, groupName)`

## 15. Authorization / Privacy Rules
- Mỗi cuộc trò chuyện riêng tư (Direct Message) đều được mã hóa và bảo mật nghiêm ngặt. Chỉ có chính 2 thành viên trong phòng chat mới có quyền truy xuất lịch sử trò chuyện.

## 16. Analytics / Audit Events
- `chat_hub_opened`: Ghi nhận số lượt người dùng mở trung tâm tin nhắn.
- `group_chat_created`: Ghi nhận sự kiện tạo nhóm chat học tập mới của sinh viên.

## 17. Do / Don't
- **Nên làm**: Luôn đồng bộ trạng thái đã đọc tin nhắn thời gian thực giữa các thiết bị khác nhau của cùng một người dùng (ví dụ: đọc trên Máy tính thì trên Điện thoại cũng phải biến mất chấm đỏ thông báo chưa đọc).
- **Không được làm**: Cho phép hiển thị công khai nội dung tin nhắn nhạy cảm ở dạng thông báo đẩy khi người dùng đã tắt tùy chọn xem trước tin nhắn trong phần cài đặt bảo mật.

## 18. Acceptance Criteria
- Cập nhật số lượng tin nhắn chưa đọc thời gian thực chính xác tuyệt đối mà không cần tải lại toàn bộ trang web.
- Tìm kiếm nhanh trong danh sách trò chuyện hiển thị kết quả chính xác theo tên phòng chat hoặc tên thành viên.
- Bố cục chia đôi cột hoạt động ổn định trên màn hình Desktop và chuyển đổi mượt mà sang 1 cột trên Mobile.

## 19. QA / UAT Checklist
- [ ] Kiểm tra bong bóng số tin nhắn chưa đọc tự động giảm trừ hoặc biến mất ngay khi nhấp vào phòng chat đó.
- [ ] Xác minh tính năng lọc danh sách theo "Chưa đọc" hiển thị đúng và đầy đủ các phòng có tin nhắn mới.
- [ ] Thử nghiệm ghim liên tiếp 3 phòng chat lên đầu và kiểm tra thứ tự sắp xếp có chính xác không.
- [ ] Kiểm tra chấm trạng thái hoạt động (Online Indicator) có cập nhật đúng thời gian thực khi bạn chat đăng nhập/đăng xuất hệ thống hay không.

## 20. AI Agent Implementation Notes
- Sử dụng Redis làm cơ sở dữ liệu lưu trạng thái trực tuyến (Key-Value TTL) của người dùng để giảm tải truy vấn trực tiếp vào SQL Database cốt lõi.
- Thiết kế hệ thống thông báo đẩy thông minh sử dụng Firebase Cloud Messaging (FCM) kết hợp WebSockets để tối ưu hóa thời lượng pin trên thiết bị di động của sinh viên.
---
