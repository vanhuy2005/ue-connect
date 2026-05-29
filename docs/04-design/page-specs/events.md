---
title: "Events Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/events-management.md"
related_design_docs:
  - "../04-design/event-planner.md"
related_system_docs:
  - "../05-system-architecture/event-service.md"
related_database_docs:
  - "../06-database/events-table.md"
related_api_docs:
  - "../07-api/events-api.md"
---

# Trang Sự Kiện Học Đường (Events)

## 1. Purpose
Trang Sự Kiện Học Đường là trung tâm đăng tải, tìm kiếm và lên kế hoạch tham gia các sự kiện, hoạt động tình nguyện (Mùa hè xanh, Tiếp sức mùa thi), hội thảo học thuật cấp Khoa/Trường, và các buổi giao lưu văn nghệ, thể thao do các Câu lạc bộ thuộc Đại học Sư phạm TP.HCM tổ chức.

## 2. Product Context
Để tối ưu hóa các hoạt động phong trào và học thuật phong phú tại HCMUE, trang Sự Kiện hỗ trợ sinh viên nhanh chóng nắm bắt lịch hoạt động, đăng ký tham gia (RSVP) và đồng bộ lịch trình cá nhân một cách chuyên nghiệp, giúp tăng tỷ lệ tham gia thực tế của sinh viên.

## 3. User Goals
- Xem danh sách trực quan các sự kiện sắp diễn ra trong trường và khoa đào tạo của mình.
- Lọc sự kiện theo danh mục: Hội thảo, Tình nguyện, Tuyển thành viên CLB, Văn nghệ - Thể thao.
- Thực hiện đăng ký tham gia nhanh (RSVP) với các tùy chọn: "Tham gia" (Going), "Quan tâm" (Interested), "Không tham gia".
- Tự động đồng bộ lịch sự kiện đã đăng ký vào Google Calendar cá nhân hoặc tải xuống tệp lịch định dạng `.ics`.

## 4. Primary Users
- **Sinh viên HCMUE**: Người tham gia sự kiện để rèn luyện kỹ năng, tích lũy điểm rèn luyện (ĐRL).
- **Ban chấp hành Đoàn trường / Đoàn khoa / Ban chủ nhiệm CLB**: Người lên kế hoạch, đăng tải thông tin, quản lý danh sách đăng ký tham gia sự kiện của sinh viên.

## 5. Entry Points
- Nhấp chọn **Sự kiện** (Events) trên thanh thanh điều hướng chính.
- Bấm vào các thông báo nhắc nhở sự kiện sắp bắt đầu hoặc từ liên kết bài đăng chia sẻ trên Bảng tin chính.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật dòng thời gian và các mốc ngày tháng tổ chức sự kiện rõ ràng, mạch lạc.

### 6.1 Desktop Layout
- Bố cục lưới 3 cột hiện đại:
  - Cột bên trái (25%): Bộ lọc danh mục sự kiện và lịch mini dạng tháng (Calendar Widget) hiển thị trực quan các ngày có sự kiện.
  - Cột ở giữa (50%): Dòng hiển thị danh sách thẻ sự kiện sắp diễn ra xếp theo trình tự thời gian sớm nhất lên đầu.
  - Cột bên phải (25%): Danh sách các sự kiện nổi bật thu hút nhiều sinh viên quan tâm nhất và sự kiện cá nhân đã đăng ký tham gia (My Agenda).
- Khoảng cách lề: 24px. Chiều rộng tối đa: 1200px.

### 6.2 Tablet Layout
- Cột bên phải chuyển sang dạng ngăn kéo (Drawer) có thể thu gọn.
- Bộ lọc và dòng thẻ sự kiện ở giữa hiển thị cân đối trên màn hình nằm ngang với lề 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc liên tục.
- Trên cùng là Bộ lọc nhanh dạng viên nhộng (Filter Pills) cuộn ngang mượt mà.
- Các thẻ sự kiện được thiết kế tối ưu hiển thị dọc, ảnh bìa sự kiện tỉ lệ 16:9 sắc nét, nút RSVP nổi bật dưới chân thẻ (`touch-target 44px`).

## 7. Information Architecture
- **Header Thanh điều khiển (Control Header)**:
  - Nút "Tạo sự kiện mới" (Chỉ hiển thị cho tài khoản có quyền Admin/Ban tổ chức).
  - Hộp tìm kiếm sự kiện theo từ khóa.
- **Thẻ Sự Kiện Chi Tiết (Event Card)**:
  - Ảnh bìa sự kiện, Ngày tháng tổ chức nổi bật ở góc trái.
  - Tiêu đề sự kiện, Đơn vị tổ chức (Ví dụ: Đoàn Khoa CNTT).
  - Địa điểm tổ chức (Phòng học, Hội trường, hoặc Link Zoom).
  - Số lượng sinh viên đã đăng ký tham gia.
  - Trình điều khiển RSVP (Nút chuyển trạng thái: Đăng ký / Đã đăng ký).

## 8. Core Components
- **Date Indicator Badge**: Khối vuông nhỏ màu đỏ trắng hiển thị tháng và ngày sắc nét ở góc thẻ sự kiện để người dùng dễ nhận diện dòng thời gian.
- **RSVP Control Buttons**: Nhóm nút bấm chuyển đổi trạng thái tham gia tinh gọn (Going - Interested - Decline).
- **Calendar Sync Dropdown**: Nút mở danh mục tùy chọn đồng bộ nhanh sang lịch Google hoặc Apple Calendar.
- **Interactive Map Widget**: Khung bản đồ nhỏ hiển thị vị trí cơ sở tổ chức sự kiện của HCMUE (Cơ sở 1 An Dương Vương hoặc Cơ sở 2 Lê Văn Sỹ).

## 9. States
### 9.1 Loading
- Hiển thị các khối thẻ sự kiện trống dạng Shimmer nhấp nháy chuyển động xám nhẹ trong lúc tải dữ liệu từ máy chủ.

### 9.2 Empty
- Khi không có sự kiện nào sắp tổ chức trong bộ lọc đang chọn:
  - UI Copy: `"Hiện tại chưa có sự kiện nào sắp diễn ra."`
  - Mô tả UI Copy: `"Hãy thử đổi bộ lọc hoặc quay lại kiểm tra vào thời gian tới nhé!"`

### 9.3 Error
- Lỗi tải thông tin sự kiện do mất kết nối:
  - UI Copy: `"Đã xảy ra lỗi khi tải lịch sự kiện. Vui lòng thử lại sau."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo màu xám ở đầu trang. Các tính năng RSVP và đồng bộ lịch tạm thời bị khóa, người dùng chỉ xem được các sự kiện đã tải sẵn trong bộ nhớ đệm trước đó.

### 9.5 Permission Restricted
- Sinh viên cố gắng xem thông tin sự kiện nội bộ của một CLB kín mà họ chưa là thành viên:
  - UI Copy: `"Sự kiện này chỉ dành cho thành viên Câu lạc bộ."`
  - Mô tả UI Copy: `"Bạn cần gia nhập Câu lạc bộ để xem chi tiết hoạt động này."`

### 9.6 Success / Completed
- RSVP thành công:
  - Thẻ sự kiện đổi trạng thái nút sang màu xanh lá cây đậm nét.
  - Hiện Toast thông báo: `"Đăng ký tham gia sự kiện thành công!"` kèm tùy chọn "Thêm vào Google Calendar".

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua ảnh bìa thẻ sự kiện sẽ kích hoạt phóng to mượt mà (`scale-102`) và tăng nhẹ độ đổ bóng dưới chân thẻ để tạo chiều sâu trực quan sinh động.

### 10.2 Focus
- Sử dụng phím Tab di chuyển qua danh sách sự kiện sẽ hiển thị viền bao ngoài nét đứt màu xanh đậm thương hiệu xung quanh các nút RSVP và nút đồng bộ lịch.

### 10.3 Press / Tap
- Nhấp nút RSVP sẽ mở ra một BottomSheet nhỏ trên di động hiển thị các lựa chọn: `"Tôi sẽ tham gia"`, `"Tôi quan tâm"`, `"Hủy đăng ký"`.

### 10.4 Optimistic UI
- Khi bấm chọn nhanh trạng thái RSVP "Tham gia", tăng ngay số lượng sinh viên đăng ký hiển thị trên thẻ lên 1 đơn vị và đổi màu sắc nút ngay lập tức trước khi nhận phản hồi xác nhận từ máy chủ.

### 10.5 Menu / Sheet
- Hộp thoại tạo sự kiện mới mở ra biểu mẫu điền thông tin chi tiết dạng Slide-in mượt mà từ góc phải màn hình Desktop.

### 10.6 Toast / Undo
- Hành động hủy đăng ký tham gia sự kiện thành công hiển thị Toast thông báo kèm nút "Hoàn tác" để khôi phục nhanh đăng ký trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng trượt lướt mượt mà khi người dùng nhấp chọn lịch ngày tháng trên Calendar Widget để lọc nhanh các sự kiện tương ứng.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nút bấm RSVP đều đi kèm nhãn mô tả chi tiết: `aria-label="Đăng ký tham gia sự kiện [Tên sự kiện]"`.
- Hỗ trợ đầy đủ phím di chuyển bàn phím qua các ngày trong Calendar Widget.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Ảnh bìa sự kiện tự động co tỉ lệ 16:9 tràn viền mượt mà, thông tin ngày tháng được đặt gọn gàng phía dưới tiêu đề để tăng diện tích hiển thị dọc của thẻ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `events_list` (array of objects: `id`, `title`, `description`, `organizer`, `start_time`, `location`, `rsvp_count`, `user_rsvp_status`)
  - `selected_date` (string, yyyy-mm-dd format)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadEvents(filterData)`
  - `updateRsvpStatus(eventId, status)`
  - `exportToGoogleCalendar(eventId)`
  - `generateIcsFile(eventId)`

## 15. Authorization / Privacy Rules
- Chỉ có tài khoản của Ban chấp hành Đoàn trường hoặc Ban chủ nhiệm CLB được phê duyệt quyền (Moderator/Admin) mới có quyền tạo mới, chỉnh sửa thông tin hoặc xóa sự kiện công khai trên hệ thống.

## 16. Analytics / Audit Events
- `event_page_viewed`: Ghi nhận số lượt mở xem danh sách sự kiện.
- `event_rsvp_changed`: Ghi nhận sự thay đổi trạng thái đăng ký tham gia sự kiện của sinh viên cùng mã sự kiện tương ứng.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị thông tin liên hệ của Ban tổ chức sự kiện (Số điện thoại, Email) rõ ràng ở cuối thẻ chi tiết sự kiện để sinh viên dễ dàng liên hệ giải đáp thắc mắc.
- **Không được làm**: Cho phép tự động phê duyệt đăng ký tham gia cho sinh viên đã có lịch tham gia sự kiện khác bị trùng hoàn toàn thời gian tổ chức trên hệ thống (Trùng lịch sinh hoạt). Hệ thống tự động phát hiện và hiển thị cảnh báo trùng lịch trực quan.

## 18. Acceptance Criteria
- Hiển thị danh sách sự kiện đầy đủ, chính xác theo đúng mốc thời gian diễn ra.
- Quy trình RSVP hoạt động ổn định, lưu chính xác trạng thái tham gia vào cơ sở dữ liệu và đồng bộ lịch Google Calendar hoàn hảo.
- Bản đồ chỉ đường hoạt động chính xác dẫn đúng vị trí cơ sở tổ chức của trường HCMUE.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nút RSVP đổi trạng thái chính xác và cập nhật ngay lập tức số lượng sinh viên tham gia hiển thị trên thẻ.
- [ ] Xác minh tệp lịch `.ics` tải xuống thành công và import đúng giờ, đúng múi giờ vào ứng dụng lịch của điện thoại di động.
- [ ] Thử nghiệm tìm kiếm sự kiện theo tên và kiểm tra kết quả trả về có chính xác không.
- [ ] Đảm bảo bộ cảnh báo trùng lịch hoạt động đúng khi đăng ký 2 sự kiện diễn ra cùng giờ.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `FullCalendar.js` hoặc SVG vẽ lịch mini tuần hoàn tự chọn gọn nhẹ để tối ưu hóa tốc độ tải trang.
- Thiết kế Model `Event` liên kết chặt chẽ với cơ sở dữ liệu học tập của sinh viên để tự động cộng điểm rèn luyện (ĐRL) tích lũy của sinh viên sau khi Ban tổ chức quét mã QR điểm danh (Check-in) thành công tại sự kiện thực tế.
---
