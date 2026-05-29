---
title: "Connection Management Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/connections.md"
related_design_docs:
  - "../04-design/navigation-layout.md"
related_system_docs:
  - "../05-system-architecture/connection-service.md"
related_database_docs:
  - "../06-database/connections-table.md"
related_api_docs:
  - "../07-api/connections-api.md"
---

# Trang Quản Lý Kết Nối (Connection Management)

## 1. Purpose
Trang Quản Lý Kết Nối là trung tâm điều phối toàn bộ các mối quan hệ bạn bè, đồng môn và kết nối học đường của người dùng trên UEConnect. Tại đây, sinh viên và giảng viên có thể xem danh sách kết nối hiện tại, phê duyệt các lời mời kết nối đang chờ (Incoming Requests), xem các yêu cầu đã gửi (Outgoing Requests) và khám phá danh sách gợi ý kết nối thông minh (Suggestions).

## 2. Product Context
Để xây dựng một mạng lưới đồng môn bền chặt và tin cậy cho Đại học Sư phạm TP.HCM, trang này được thiết kế để loại bỏ cảm giác nặng nề của việc "kết bạn" truyền thống, thay vào đó là phong cách "kết nối" (Connect) năng động tương tự Threads, đề cao tính chủ động tìm kiếm bạn học và người định hướng (Mentors).

## 3. User Goals
- Phê duyệt nhanh các lời mời kết nối từ bạn bè cùng khóa hoặc sinh viên trong trường.
- Quản lý và thu hồi các lời mời kết nối đã gửi đi nếu không còn nhu cầu.
- Xem danh mục phân loại bạn bè rõ ràng theo Khoa, Niên khóa để dễ dàng tra cứu thông tin học tập.
- Tiếp cận các gợi ý kết nối chất lượng cao dựa trên sự tương đồng về ngành học hoặc sở thích nghiên cứu khoa học.

## 4. Primary Users
- **Toàn bộ học viên, sinh viên, cựu sinh viên và giảng viên HCMUE**: Cần thiết lập vòng tròn kết nối học đường để phục vụ học tập, nghiên cứu và phát triển sự nghiệp.

## 5. Entry Points
- Nhấp chọn **Kết nối** (Connections) trên thanh thanh điều hướng chính.
- Bấm vào thông báo: `"Bạn có một yêu cầu kết nối mới từ..."` trên thanh thông báo hệ thống.

## 6. Layout Strategy
Sử dụng bố cục dạng thẻ danh sách rõ ràng, khoa học giúp người dùng thực hiện duyệt phê duyệt hàng loạt một cách mượt mà nhất.

### 6.1 Desktop Layout
- Bố cục 2 cột cân xứng:
  - Cột bên trái chiếm 60% chiều rộng: Hiển thị các Lời mời kết nối đang chờ phê duyệt (Incoming Requests) và Lời mời đã gửi (Sent Requests) sắp xếp dạng danh sách dọc.
  - Cột bên phải chiếm 40% chiều rộng: Hiển thị danh sách Gợi ý kết nối (People You May Know) dạng thẻ lưới nhỏ gọn.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1000px.

### 6.2 Tablet Layout
- Cột Gợi ý kết nối chuyển sang vị trí dưới cùng chân trang.
- Danh sách yêu cầu đang chờ chiếm trọn chiều ngang màn hình nằm ngang với lề 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc hoàn hảo.
- Sử dụng thanh trượt chuyển tab ngang cố định ở trên đầu màn hình: "Đang chờ duyệt", "Bạn bè của tôi", "Gợi ý kết nối".
- Các nút hành động "Đồng ý" và "Từ chối" trên thẻ yêu cầu có kích thước lớn (`touch-target 44px`) và khoảng cách an toàn rộng để tránh ngón tay chạm nhầm nút.

## 7. Information Architecture
- **Thanh tiêu đề trung tâm (Header Panel)**:
  - Tổng số lượng kết nối hiện tại.
  - Thanh tìm kiếm nhanh bạn bè theo tên hoặc ngành học.
- **Tab Lời mời đang chờ (Incoming Requests)**:
  - Danh sách thẻ thành viên gồm: Ảnh đại diện, Tên, Lớp/Khoa học tập, Số lượng bạn chung (Mutual Connections), Nút Đồng ý (Accept) màu xanh HCMUE, Nút Từ chối (Decline) màu xám nhạt.
- **Tab Gợi ý kết nối (Suggestions)**:
  - Danh sách thẻ gợi ý ghi rõ lý do đề xuất (Ví dụ: "Học cùng lớp K45 Công nghệ Thông tin", "Cùng mối quan tâm Nghiên cứu Khoa học").

## 8. Core Components
- **Connection Card**: Thẻ thông tin thành viên chứa ảnh đại diện, tên, thông tin ngành học và nút hành động nhanh.
- **Accept Button**: Nút "Đồng ý" màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`).
- **Decline Button**: Nút "Xóa" màu xám trung tính (`bg-gray-100` hover `bg-gray-200`).
- **Tab Menu Switcher**: Thanh chuyển tab bo tròn góc tinh tế đổi màu nền trượt mượt mà theo tab được kích hoạt.

## 9. States
### 9.1 Loading
- Hiển thị 3-4 dòng thẻ trống dạng Shimmer với các ô hình tròn (Avatar) và thanh chữ nhật (Tên, Lớp học) nhấp nháy nhè nhẹ trong lúc tải dữ liệu.

### 9.2 Empty
- Khi người dùng không có lời mời kết nối nào đang chờ:
  - UI Copy: `"Bạn không có lời mời kết nối nào."`
  - Mô tả UI Copy: `"Hãy khám phá mục gợi ý hoặc tìm kiếm bạn học để mở rộng vòng tròn kết nối của mình nhé!"`

### 9.3 Error
- Lỗi thao tác đồng ý kết nối do sự cố mạng:
  - UI Copy: `"Không thể phê duyệt kết nối lúc này. Vui lòng kiểm tra lại kết nối Internet."`

### 9.4 Offline / Reconnecting
- Vô hiệu hóa tất cả các nút tương tác (Đồng ý, Xóa, Gửi kết nối) và hiển thị banner thông báo mỏng ở chân trang.

### 9.5 Permission Restricted
- Không áp dụng cho giao diện cá nhân này.

### 9.6 Success / Completed
- Đồng ý kết nối thành công:
  - Thẻ người dùng biến mất khỏi danh sách chờ với hiệu ứng Fade-out.
  - Hiện Toast thông báo: `"Bạn và @username đã trở thành kết nối của nhau!"` kèm nút "Nhắn tin ngay".

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua nút "Đồng ý" sẽ tăng độ đậm của màu nút lên 10%. Di chuột qua ảnh đại diện thành viên sẽ hiện danh thiếp thông tin tóm tắt (Hover Card Preview).

### 10.2 Focus
- Vòng viền Focus 2px màu xanh đậm thương hiệu HCMUE xung quanh các nút bấm hành động khi duyệt danh sách bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấn nút "Đồng ý" hoặc "Từ chối" sẽ có phản hồi xúc giác nhẹ (Haptic feedback) trên các thiết bị di động để người dùng nhận diện thao tác thành công tức thì.

### 10.4 Optimistic UI
- Khi bấm "Đồng ý" hoặc "Từ chối", thẻ kết nối lập tức mờ đi và biến mất khỏi danh sách hiển thị thời gian thực trong vòng 100ms trước khi nhận phản hồi từ API máy chủ để tạo cảm giác phản hồi tức thì không có độ trễ.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ thẻ bạn bè trong danh sách "Bạn bè của tôi" trên di động để mở BottomSheet chứa các tùy chọn nhanh: (Nhắn tin, Xem hồ sơ, Hủy kết nối, Chặn tài khoản).

### 10.6 Toast / Undo
- Hành động "Hủy kết nối" thành công hiển thị Toast thông báo kèm nút "Hoàn tác" để khôi phục nhanh mối quan hệ kết nối trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng chuyển dịch vị trí của các thẻ phía dưới trượt nhẹ lên thay thế cho thẻ vừa biến mất (FLIP Animation) diễn ra trong 250ms vô cùng trơn tru, dễ chịu cho mắt.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nút "Đồng ý" và "Từ chối" đều đi kèm nhãn mô tả chi tiết: `aria-label="Đồng ý lời mời kết nối từ @username"`.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Thu nhỏ khoảng cách giữa các thẻ danh sách xuống còn 8px để tiết kiệm diện tích hiển thị dọc của màn hình điện thoại.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `incoming_requests` (array of objects: `id`, `name`, `username`, `avatar_url`, `mutual_count`)
  - `sent_requests` (array of objects: `id`, `name`, `username`, `avatar_url`)
  - `suggestions` (array of objects: `id`, `name`, `username`, `avatar_url`, `reason`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `acceptRequest(requestId)`
  - `declineRequest(requestId)`
  - `cancelSentRequest(requestId)`
  - `sendConnectionRequest(userId)`

## 15. Authorization / Privacy Rules
- Chỉ có chủ sở hữu tài khoản mới có quyền xem, phê duyệt hoặc từ chối các yêu cầu kết nối cá nhân này. Danh sách lời mời đã gửi/nhận hoàn toàn riêng tư.

## 16. Analytics / Audit Events
- `connection_request_accepted`: Ghi nhận sự kiện phê duyệt kết nối mới thành công.
- `connection_request_sent`: Theo dõi số lượng lời mời kết nối được gửi đi để đánh giá mức độ chủ động tương tác của sinh viên.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị lý do gợi ý kết nối rõ ràng để sinh viên nhận thức được mối quan hệ quen thuộc (ví dụ: Học cùng lớp, cùng khoa).
- **Không được làm**: Cho phép tự động gửi hàng loạt lời mời kết nối không giới hạn trong ngày (Spam) để tránh quấy rối không gian học đường lành mạnh. Hệ thống tự động giới hạn tối đa 50 lời mời kết nối được gửi mỗi ngày cho mỗi tài khoản.

## 18. Acceptance Criteria
- Cập nhật số lượng kết nối chính xác và đồng bộ tức thời trên cơ sở dữ liệu.
- Phê duyệt kết nối tự động cập nhật mối quan hệ kết nối bạn bè hai chiều trong hệ thống trò chuyện.
- Lưới gợi ý kết nối thông minh hiển thị các tài khoản có tính tương quan cao trong cùng trường HCMUE.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nút "Đồng ý" có chuyển trạng thái thẻ thành công và xóa thẻ khỏi danh sách chờ không.
- [ ] Xác minh tính năng tìm kiếm bạn bè lọc đúng và hiển thị chính xác danh sách bạn bè hiện tại.
- [ ] Thử nghiệm bấm nút "Từ chối" và kiểm tra xem tài khoản đó có thể gửi lại lời mời kết nối sau đó hay không.
- [ ] Đảm bảo hiệu ứng chuyển tab mượt mà không bị tải lại toàn bộ trang web trên thiết bị di động Safari và Chrome.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ cơ sở dữ liệu quan hệ với các chỉ mục kép (Composite Indexes) trên cột `user_id` và `connected_user_id` của bảng `connections` để tối ưu hóa tốc độ tải trang.
- Sử dụng thuật toán gợi ý kết nối (Recommendation algorithm) dựa trên các trọng số chung: Cùng khoa (Trọng số 0.5), Cùng niên khóa (Trọng số 0.3), Cùng sở thích sinh hoạt CLB (Trọng số 0.2) để trả về kết quả gợi ý chất lượng nhất.
---
