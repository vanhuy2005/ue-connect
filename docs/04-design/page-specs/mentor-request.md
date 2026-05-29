---
title: "Mentor Request Tracking Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
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

# Trang Theo Dõi Yêu Cầu Cố Vấn (Mentor Request Tracking)

## 1. Purpose
Trang Theo Dõi Yêu Cầu Cố Vấn là giao diện giúp sinh viên theo dõi, quản lý và cập nhật tiến độ của tất cả các đơn đăng ký xin làm Mentee gửi tới các Cố vấn (Mentors). Trang hiển thị lịch sử gửi đơn, trạng thái xét duyệt hiện tại (Đang chờ, Đã đồng ý, Từ chối, Đã hoàn thành), lý do từ chối (nếu có), và cung cấp lối tắt truy cập nhanh vào phòng chat riêng tư khi yêu cầu được phê duyệt.

## 2. Product Context
Nằm trong giải pháp đảm bảo tính minh bạch, tương tác hai chiều của chương trình Mentorship tại HCMUE, trang này giúp sinh viên tránh cảm giác lo lắng chờ đợi vô ích bằng cách cập nhật liên tục mọi thay đổi trạng thái đơn từ phía Mentor trong thời gian thực.

## 3. User Goals
- Theo dõi danh sách chi tiết các đơn xin cố vấn đã gửi.
- Xem phản hồi phê duyệt hoặc lý do từ chối chi tiết từ phía Mentor để rút kinh nghiệm chỉnh sửa thư đăng ký sau.
- Hủy các yêu cầu đang ở trạng thái chờ duyệt nếu tìm thấy Mentor khác phù hợp hơn.
- Truy cập trực tiếp phòng chat trao đổi riêng khi đơn đăng ký được duyệt thành công.

## 4. Primary Users
- **Sinh viên HCMUE**: Người gửi đơn xin cố vấn và cần theo dõi tiến độ phê duyệt đơn.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Quản lý Cố vấn** (Mentorship) -> Chọn **Yêu cầu đã gửi** (Sent Requests).
- Nhấp trực tiếp vào thông báo hệ thống khi Mentor phê duyệt hoặc từ chối đơn xin cố vấn của sinh viên.

## 6. Layout Strategy
Thiết kế tập trung vào sự rõ ràng, bố cục dạng dòng thời gian tiến trình (Pipeline) trực quan giúp sinh viên nắm bắt trạng thái đơn nhanh nhất.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 760px).
- Các đơn đăng ký hiển thị dưới dạng các thẻ khối lớn xếp chồng dọc.
- Phần đầu mỗi thẻ hiển thị thanh tiến trình 3 bước (Gửi đơn -> Đang duyệt -> Kết quả) bằng màu sắc trực quan.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên là 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình.
- Rút gọn thanh tiến trình 3 bước thành một nhãn trạng thái lớn nổi bật màu sắc ở góc phải trên thẻ đơn.
- Các nút hành động chính (Nhắn tin, Hủy đơn) có kích thước lớn (`touch-target 44px`) được xếp dọc dưới chân thẻ để tay dễ thao tác chạm bấm.

## 7. Information Architecture
- **Bộ lọc trạng thái đơn (Status Tabs)**:
  - Tab "Tất cả", "Đang chờ duyệt", "Đã phê duyệt", "Đã từ chối / Đã đóng".
- **Danh sách thẻ yêu cầu (Request Cards List)**:
  - Mỗi thẻ đơn gồm:
    - Thông tin Mentor: Ảnh đại diện, Tên Mentor, Lĩnh vực cố vấn.
    - Thư nguyện vọng tóm tắt (Cover Letter Preview).
    - Khung giờ đăng ký tư vấn.
    - Nhãn trạng thái (Status Badge) kèm thanh màu hiển thị tiến trình.
    - Lý do từ chối (Chỉ hiển thị nếu đơn bị từ chối).
    - Hàng nút hành động: Nhắn tin cho Mentor (nếu được duyệt), Hủy đơn (nếu đang chờ).

## 8. Core Components
- **Pipeline Progress Bar**: Thanh ngang mỏng thể hiện 3 bước phê duyệt đổi màu mượt mà tùy theo tiến độ thực tế của đơn.
- **Status Badge Indicator**: Nhãn bo tròn màu sắc chỉ trạng thái (Xanh lá: Đã duyệt, Vàng: Đang chờ, Đỏ: Từ chối, Xám: Đã hoàn thành).
- **Cover Letter Accordion**: Khung hiển thị nội dung thư nguyện vọng của sinh viên có thể thu gọn/mở rộng mượt mà.
- **Decline Reason Card**: Khung viền nét đứt màu đỏ nhạt hiển thị lý do Mentor từ chối tư vấn kèm theo lời khuyên xây dựng giúp sinh viên tiến bộ.

## 9. States
### 9.1 Loading
- Hiển thị 3 thẻ đơn trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu từ máy chủ.

### 9.2 Empty
- Khi sinh viên chưa từng gửi bất kỳ yêu cầu xin cố vấn nào:
  - UI Copy: `"Bạn chưa gửi yêu cầu Cố vấn nào."`
  - Mô tả UI Copy: `"Hãy ghé thăm Danh mục Mentor để khám phá những người hướng dẫn xuất sắc và gửi lời mời học hỏi ngay hôm nay nhé!"`

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi kết nối dữ liệu yêu cầu. Vui lòng nhấp để thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo ngoại tuyến ở đầu trang. Các nút bấm hành động (Hủy đơn) tạm thời bị vô hiệu hóa và hiển thị ở trạng thái mờ xám.

### 9.5 Permission Restricted
- Không áp dụng cho giao diện cá nhân này.

### 9.6 Success / Completed
- Hủy đơn xin cố vấn thành công:
  - Thẻ đơn biến mất khỏi danh sách chờ mượt mà.
  - Hiện Toast thông báo: `"Đã hủy yêu cầu cố vấn thành công."`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ đơn sẽ làm sáng nhẹ viền thẻ (`border-gray-300`) và hiển thị bóng mờ nhẹ chân thẻ để tạo chiều sâu trực quan.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút bấm hành động (Hủy đơn, Nhắn tin) khi dùng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp "Hủy đơn" sẽ mở ra hộp thoại xác nhận ở giữa màn hình (Desktop) hoặc BottomSheet trượt lên mượt mà (Mobile) để tránh bấm nhầm ngoài ý muốn.

### 10.4 Optimistic UI
- Khi bấm xác nhận hủy đơn, thẻ đơn lập tức biến mất khỏi danh sách hiển thị tạm thời trước khi nhận phản hồi xác nhận từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hỗ trợ BottomSheet trên di động hiển thị hộp thoại xác nhận hủy đơn nhanh gọn, dễ chạm bấm.

### 10.6 Toast / Undo
- Hành động hủy đơn thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh đơn xin trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng biến mất (Fade-out & Slide-up) của thẻ đơn khi bị hủy diễn ra trong vòng 200ms cực kỳ mượt mà.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nhãn trạng thái và nút bấm đều đi kèm nhãn mô tả chi tiết cho các trình đọc màn hình: `aria-label="Hủy yêu cầu gửi tới Mentor [Tên Mentor]"`.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị thư nguyện vọng tóm tắt để tránh bị tràn màn hình, chỉ hiển thị tóm tắt ngắn gọn dưới 50 ký tự và thêm nút "Xem thêm" tiện lợi.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `sent_requests_list` (array of objects: `request_id`, `mentor_name`, `mentor_avatar`, `expertise`, `slot_time`, `status`, `cover_letter`, `decline_reason`)
  - `active_tab` (enum: `all`, `pending`, `approved`, `declined`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchSentRequests(statusFilter)`
  - `cancelMentorshipRequest(requestId)`
  - `undoCancelRequest(requestId)`

## 15. Authorization / Privacy Rules
- Chỉ sinh viên đã gửi đơn mới có quyền truy cập xem trang lịch sử đơn này (`auth` middleware). Mọi yêu cầu truy cập trái phép qua ID đơn đều bị hệ thống chặn và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `mentor_requests_page_viewed`: Ghi nhận lượt mở xem trang theo dõi yêu cầu cố vấn.
- `mentorship_request_cancelled`: Ghi nhận sự kiện sinh viên tự động hủy đơn xin cố vấn.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị lý do Mentor từ chối rõ ràng và đề xuất sinh viên chuyển hướng sang các Mentor khác có cùng chuyên môn đang còn nhiều lịch trống để tăng cơ hội kết nối học tập thành công.
- **Không được làm**: Cho phép hiển thị lại nút "Đăng ký tư vấn" đối với một Mentor khi đơn xin tư vấn trước đó gửi tới họ đã được duyệt thành công và khóa học hướng dẫn đang diễn ra bình thường.

## 18. Acceptance Criteria
- Hiển thị danh sách đơn đăng ký đầy đủ, chính xác theo đúng trạng thái thực tế trong cơ sở dữ liệu.
- Quy trình hủy đơn đang chờ và cơ chế "Hoàn tác" hoạt động ổn định, không gây gián đoạn hay trùng lặp dữ liệu API.
- Bố cục thích ứng sắc nét trên mọi loại màn hình thiết bị di động, máy tính bảng và máy tính để bàn.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nút hủy đơn chuyển trạng thái thẻ chính xác và khôi phục đúng vị trí cũ khi chọn Hoàn tác.
- [ ] Xác minh lý do từ chối hiển thị đúng định dạng và chỉ hiển thị đối với các đơn ở trạng thái "Từ chối".
- [ ] Thử nghiệm nhấp nút "Nhắn tin" trên đơn đã duyệt và đảm bảo mở đúng phòng chat riêng tư với Mentor.
- [ ] Đảm bảo các tab bộ lọc trạng thái chuyển đổi mượt mà không cần tải lại toàn bộ trang web.

## 20. AI Agent Implementation Notes
- Sử dụng cơ chế lắng nghe sự kiện thời gian thực (Real-time Broadcast Listener) thông qua WebSockets để tự động cập nhật trạng thái đơn ngay trên màn hình của sinh viên khi Mentor bấm nút Duyệt/Từ chối từ bảng điều khiển của họ, không cần sinh viên phải tải lại trang.
- Thiết kế cơ sở dữ liệu bảng `mentorship_applications` tối ưu chỉ mục (Composite Indexes) trên cột `student_id` và `status` để đạt hiệu năng truy vấn tải trang nhanh nhất.
---
