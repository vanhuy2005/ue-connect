---
title: "Messaging Inbox Page"
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

# Trang Hộp Thư Tin Nhắn (Messaging Inbox)

## 1. Purpose
Trang Hộp Thư Tin Nhắn là trung tâm quản lý tất cả các cuộc trò chuyện cá nhân (Direct Messages - DM), tìm kiếm bạn bè trong danh bạ trường để nhắn tin nhanh, thiết lập các nhóm thảo luận học tập và theo dõi danh sách bạn học đang trực tuyến (Online Contacts) trong khuôn viên số UEConnect.

## 2. Product Context
Để xây dựng một không gian giao tiếp thời gian thực mượt mà cho sinh viên HCMUE, trang này được thiết kế theo phong cách giao diện Threads tinh giản, loại bỏ các yếu tố rườm rà của các ứng dụng chat công việc nặng nề, tập trung hoàn toàn vào tốc độ truyền thông điệp và tính kết nối tự nhiên, an toàn.

## 3. User Goals
- Quản lý danh sách toàn bộ các cuộc trò chuyện riêng tư và nhóm chat học tập hiện có.
- Xem nhanh danh sách bạn bè cùng khoa đang trực tuyến để chủ động trao đổi bài vở.
- Tìm kiếm nhanh liên hệ mới theo tên hoặc mã số sinh viên để khởi đầu cuộc trò chuyện.
- Tạo nhóm trò chuyện học tập mới phục vụ thảo luận bài tập lớn hoặc nghiên cứu khoa học.

## 4. Primary Users
- **Toàn bộ thành viên UEConnect**: Học viên, sinh viên, cựu sinh viên và giảng viên có nhu cầu nhắn tin trao đổi riêng tư.

## 5. Entry Points
- Nhấp chọn biểu tượng **Tin nhắn** (Mail/Chat Icon) trên thanh thanh điều hướng chính.
- Nhấp vào các liên kết "Nhắn tin" nhanh từ trang hồ sơ cá nhân của người dùng khác hoặc từ hộp thoại danh thiếp thu nhỏ (Hover Card).

## 6. Layout Strategy
Áp dụng thiết kế Master-Detail chuyên nghiệp để tối ưu hóa không gian hiển thị trên màn hình máy tính lớn.

### 6.1 Desktop Layout
- Bố cục chia đôi cột (Split Panel layout):
  - Cột bên trái (Master List - 35%): Chứa thanh tìm kiếm liên hệ, thanh trượt ngang danh sách bạn bè đang trực tuyến (Active Friends Carousel), và danh sách các thẻ cuộc hội thoại hiện tại xếp dọc.
  - Cột bên phải (Detail Chat Workspace - 65%): Hiển thị phòng trò chuyện chi tiết đang được chọn. Nếu chưa chọn phòng, hiển thị logo UEConnect lớn chìm cùng lời mời gọi thân thiện: `"Hãy chọn một cuộc hội thoại từ danh sách bên trái để bắt đầu trò chuyện."`
- Chiều rộng tối đa: Toàn màn hình (Edge-to-edge layout).

### 6.2 Tablet Layout
- Cột bên phải (Detail Workspace) có thể thu gọn.
- Cột bên trái hiển thị toàn màn hình với khoảng cách lề hai bên rộng 20px giúp tay dễ dàng chạm bấm chọn phòng.

### 6.3 Mobile / PWA Layout
- Bố cục 1 màn hình cuộn dọc. Mặc định hiển thị danh sách hộp thư đến (Inbox List).
- Khi nhấp chọn phòng chat, mở ngay phòng chat chi tiết (Full-screen Chat View) đè lên trên với hiệu ứng chuyển trang mượt mà.
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh tiêu đề điều khiển (Inbox Header)**:
  - Tiêu đề "Hộp thư" lớn, nút "Tạo nhóm chat mới".
  - Thanh tìm kiếm hội thoại thông minh thời gian thực.
- **Thanh trượt bạn bè trực tuyến (Active Contacts Slide)**:
  - Hàng ảnh đại diện tròn của các bạn bè đang online cuộn ngang mượt mà, mỗi ảnh đại diện tích hợp chấm xanh lá nhỏ góc phải dưới.
- **Danh sách hội thoại (Inbox List)**:
  - Thẻ phòng chat: Ảnh đại diện, Tên hiển thị, Tin nhắn cuối cùng tóm tắt kèm mốc thời gian, Bong bóng số lượng tin nhắn chưa đọc đỏ cam.

## 8. Core Components
- **Active Contacts Carousel**: Hàng ảnh đại diện bạn bè đang online cuộn ngang mượt mà, hỗ trợ thao tác vuốt cảm ứng nhạy bén.
- **Unread Message Badge**: Bong bóng tròn đỏ cam hiển thị số lượng tin nhắn chưa đọc nổi bật trên thẻ phòng chat.
- **New Conversation Launcher**: Hộp thoại sạch sẽ hỗ trợ tìm nhanh sinh viên theo tên/mã số sinh viên để tạo phòng chat mới tức thì.
- **Inbox Search Bar**: Ô tìm kiếm thông tin liên hệ và tin nhắn cũ tích hợp bộ lọc thông minh.

## 9. States
### 9.1 Loading
- Danh sách phòng chat hiển thị 5 thẻ trống dạng Shimmer nhấp nháy xám nhẹ chuyển động trượt ngang.

### 9.2 Empty
- Khi người dùng chưa có bất kỳ cuộc hội thoại nào trong hộp thư:
  - UI Copy: `"Hộp thư của bạn đang trống."`
  - Mô tả UI Copy: `"Hãy tìm kiếm bạn bè cùng lớp hoặc giảng viên để gửi tin nhắn chào hỏi và bắt đầu thảo luận nhé!"`

### 9.3 Error
- Lỗi tải danh sách hộp thư do mất kết nối mạng:
  - UI Copy: `"Không thể tải danh sách tin nhắn. Đang thử kết nối lại..."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu danh sách. Các nút bấm hành động tạo nhóm chat mới tạm thời bị vô hiệu hóa.

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho tất cả mọi người dùng đã đăng nhập hợp lệ.

### 9.6 Success / Completed
- Dữ liệu tải xong mượt mà, tự động sắp xếp các phòng có tin nhắn mới nhất hoặc chưa đọc lên vị trí trên cùng của danh sách.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ phòng chat sẽ làm đổi màu nền sang xám kem dịu (`bg-gray-50`) và hiển thị các phím tắt hành động nhanh (Ghim, Tắt tiếng).

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các thẻ phòng chat khi di chuyển bằng phím Tab di chuyển tiêu điểm. Hỗ trợ phím mũi tên Lên/Xuống để duyệt danh sách nhanh.

### 10.3 Press / Tap
- Thao tác nhấp chọn phòng chat sẽ kích hoạt mở phòng chat chi tiết mượt mà trong vòng 150ms. Trên di động, chuyển màn hình trượt sang phải.

### 10.4 Optimistic UI
- Khi tạo nhóm chat mới, lập tức chèn phòng chat mới đó lên đầu danh sách hiển thị tạm thời trước khi nhận phản hồi tạo thành công từ máy chủ để người dùng bắt đầu trò chuyện ngay.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ lâu (Long-press) vào thẻ phòng chat trên di động để hiển thị thực đơn thao tác nhanh dạng BottomSheet (Đánh dấu đã đọc, Ghim nhóm, Tắt thông báo, Rời khỏi cuộc trò chuyện).

### 10.6 Toast / Undo
- Hành động "Rời khỏi nhóm chat" thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh vị trí nhóm trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng trượt ngang mượt mà của thanh trượt bạn bè trực tuyến. Hoạt ảnh chuyển dịch vị trí của các thẻ phòng khi có tin nhắn mới lên đầu danh sách trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh đại diện trong thanh trượt trực tuyến phải có nhãn mô tả chi tiết: `aria-label="[Tên bạn bè] đang trực tuyến"`.
- Hỗ trợ phím nóng `Ctrl + G` để mở nhanh hộp thoại tạo nhóm chat mới từ bất kỳ đâu trên trang.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tự động ẩn hoàn toàn thanh trượt bạn bè trực tuyến để nhường không gian hiển thị cho danh sách phòng chat chính, giúp giao diện gọn gàng, không bị kéo dài.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `inbox_list` (array of objects: `room_id`, `room_name`, `avatar_url`, `last_message`, `unread_count`, `is_online`, `is_pinned`)
  - `active_friends` (array of objects: `user_id`, `name`, `avatar_url`)

## 14. API / Action Requirements
- Gọi Livewire / Real-time Action:
  - `fetchInboxList()`
  - `pinChatRoom(roomId)`
  - `muteRoomNotifications(roomId)`
  - `launchNewConversation(userId)`

## 15. Authorization / Privacy Rules
- Bảo mật hộp thư cá nhân tuyệt đối: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền truy cập xem danh sách tin nhắn cá nhân này. Mọi truy vấn trái phép đều bị hệ thống chặn và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `inbox_viewed`: Ghi nhận mỗi lần người dùng mở xem hộp thư tin nhắn.
- `direct_conversation_started`: Ghi nhận sự kiện tạo cuộc trò chuyện cá nhân mới.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị chấm trạng thái hoạt động (Online Indicator) chính xác và đồng bộ theo thời gian thực để sinh viên biết được bạn học có đang online để trao đổi bài tập nhóm hay không.
- **Không được làm**: Cho phép gửi lời mời nhắn tin tự động hoặc spam hàng loạt tin nhắn rác gây phiền phức cho không gian giao tiếp lành mạnh học đường.

## 18. Acceptance Criteria
- Cập nhật danh sách phòng chat và số lượng tin nhắn chưa đọc thời gian thực chính xác tuyệt đối mà không cần tải lại toàn bộ trang web.
- Tìm kiếm nhanh liên hệ hiển thị kết quả chính xác theo tên hoặc mã số sinh viên trong vòng dưới 200ms.
- Thiết kế thích ứng mượt mà trên cả màn hình Desktop và di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra bong bóng số tin nhắn chưa đọc tự động giảm trừ hoặc biến mất ngay khi nhấp vào phòng chat đó.
- [ ] Xác minh thanh trượt bạn bè trực tuyến hiển thị đúng những người dùng đang online thực tế.
- [ ] Thử tạo nhóm chat mới với 3 thành viên và kiểm tra xem phòng chat nhóm có được tạo đúng cấu trúc không.
- [ ] Đảm bảo giao diện chuyển đổi Dark Mode hiển thị rõ chữ và không bị lóa mắt.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu trạng thái trực tuyến (Online presence tracking) thông qua Redis Key-Value Store để đạt tốc độ xử lý nhanh nhất và giảm tải tối đa cho cơ sở dữ liệu MySQL cốt lõi.
- Thiết kế hệ thống thông báo đẩy thông minh sử dụng Firebase Cloud Messaging (FCM) kết hợp WebSockets để tối ưu hóa thời lượng pin trên thiết bị di động của sinh viên.
---