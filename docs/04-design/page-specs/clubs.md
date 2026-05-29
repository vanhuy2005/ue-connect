---
title: "Clubs Directory Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/clubs-directory.md"
related_design_docs:
  - "../04-design/club-spaces.md"
related_system_docs:
  - "../05-system-architecture/club-service.md"
related_database_docs:
  - "../06-database/clubs-table.md"
related_api_docs:
  - "../07-api/clubs-api.md"
---

# Trang Danh Sách Câu Lạc Bộ (Clubs Directory)

## 1. Purpose
Trang Danh Sách Câu Lạc Bộ là cổng thông tin tổng hợp giới thiệu toàn bộ các Câu lạc bộ, Đội, Nhóm chính quy và không chính quy đang hoạt động tại trường Đại học Sư phạm TP.HCM. Sinh viên có thể tìm kiếm, phân loại theo danh mục sở thích, khoa đào tạo và khám phá những nhóm hoạt động sôi nổi nhất để đăng ký tham gia.

## 2. Product Context
Nhằm thúc đẩy phong trào rèn luyện kỹ năng mềm và tinh thần đoàn kết học đường HCMUE, trang này đóng vai trò như một bản đồ sinh hoạt ngoại khóa năng động, khuyến khích sinh viên tự tin bước ra khỏi giảng đường để giao lưu học hỏi thực tế.

## 3. User Goals
- Khám phá danh sách phong phú các CLB trong trường.
- Lọc nhanh các CLB theo nhóm: Học thuật, Nghệ thuật - Thể thao, Tình nguyện, hoặc theo Khoa chuyên môn.
- Tìm kiếm nhanh CLB bằng từ khóa tên gọi hoặc mục tiêu hoạt động.
- Theo dõi các sự kiện tuyển thành viên lớn từ các CLB tiêu biểu trên trang đầu.

## 4. Primary Users
- **Sinh viên năm nhất**: Tìm kiếm môi trường sinh hoạt phù hợp để hòa nhập cuộc sống đại học mới mẻ.
- **Sinh viên năm hai, năm ba**: Tìm kiếm các câu lạc bộ chuyên ngành học thuật để nâng cao kiến thức chuyên môn và tham gia nghiên cứu khoa học.

## 5. Entry Points
- Nhấp chọn mục **Câu lạc bộ** (Clubs) trên thanh thanh điều hướng chính (Sidebar/Navbar).
- Nhấp vào các biểu ngữ (Banner) quảng bá tuần lễ tuyển quân của CLB ở trang chủ.

## 6. Layout Strategy
Bố cục thẻ lưới thông minh giúp người dùng dễ dàng quét nhanh qua nhiều câu lạc bộ khác nhau mà không bị ngợp thông tin.

### 6.1 Desktop Layout
- Bố cục 2 phần rõ rệt:
  - Thanh bộ lọc ngang (Horizontal Filter Bar) nằm ở trên cùng dưới tiêu đề trang.
  - Lưới hiển thị các thẻ CLB dạng 3 cột hoặc 4 cột tùy kích thước màn hình (`grid-cols-3 lg:grid-cols-4`).
- Khoảng cách lề: 32px.
- Chiều rộng trang tối đa: 1200px.

### 6.2 Tablet Layout
- Bộ lọc chuyển thành danh sách tab cuộn ngang mượt mà.
- Lưới thẻ CLB chuyển thành bố cục 2 cột cân xứng.
- Khoảng cách lề: 24px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền nhẹ.
- Bộ lọc danh mục được đặt trong một thanh trượt nằm ngang cố định dưới thanh tìm kiếm.
- Các thẻ CLB được tối giản thông tin, chỉ hiển thị Logo, Tên viết tắt, Trực thuộc và Số thành viên để tăng diện tích hiển thị.

## 7. Information Architecture
- **Thanh tìm kiếm & Lọc (Search & Filter Area)**:
  - Thanh tìm kiếm thông minh hỗ trợ gõ nhanh gợi ý.
  - Nút lọc danh mục: "Tất cả", "Học thuật", "Nghệ thuật", "Tình nguyện", "Thể thao", "Trực thuộc Khoa".
- **Lưới danh sách CLB (Clubs Grid)**:
  - Mỗi thẻ CLB gồm: Ảnh bìa mờ, Logo nổi, Tên CLB, Số thành viên, Trạng thái đang tuyển thành viên (Recruiting Tag).
- **Mục CLB Nổi Bật (Featured Clubs Carousel)**:
  - Trình chiếu 3-5 CLB hoạt động xuất sắc nhất tháng được Ban chấp hành Đoàn trường phê duyệt.

## 8. Core Components
- **Category Filter Pill**: Các nút lựa chọn danh mục dạng viên nhộng nhỏ nhắn, bo tròn góc, đổi màu nền nổi bật khi được chọn.
- **Club Card View**: Thẻ thông tin CLB tích hợp hiệu ứng đổ bóng khi di chuột.
- **Recruiting Status Badge**: Nhãn màu xanh lá sáng hiển thị chữ `"Đang tuyển thành viên"` hoặc màu xám `"Tạm dừng tuyển"`.
- **Search Auto-complete Box**: Hộp gợi ý từ khóa thông minh hiển thị logo CLB tương ứng ngay khi người dùng gõ từ 2 ký tự.

## 9. States
### 9.1 Loading
- Lưới thẻ hiển thị 6-8 khung trống màu xám nhạt với hiệu ứng Shimmer nhấp nháy 1.5s theo chu kỳ đều đặn.

### 9.2 Empty
- Không tìm thấy CLB nào khớp với từ khóa hoặc bộ lọc:
  - UI Copy: `"Không tìm thấy Câu lạc bộ nào phù hợp."`
  - Mô tả UI Copy: `"Hãy thử thay đổi từ khóa tìm kiếm hoặc chọn một danh mục lọc khác xem sao nhé."`

### 9.3 Error
- Lỗi tải danh sách do sự cố máy chủ:
  - UI Copy: `"Đã xảy ra lỗi khi tải danh sách Câu lạc bộ. Vui lòng tải lại trang hoặc thử lại sau."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang, cho phép người dùng xem dữ liệu đã lưu đệm từ trước.

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho tất cả mọi người dùng truy cập.

### 9.6 Success / Completed
- Dữ liệu tải xong mượt mà, tự động cập nhật danh sách CLB mới nhất theo thuật toán xếp hạng hoạt động năng nổ.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ CLB sẽ nâng nhẹ độ cao của thẻ (`-translate-y-1`) và làm nổi bật logo CLB cùng nút "Xem chi tiết".

### 10.2 Focus
- Sử dụng khung viền nét đứt màu xanh đậm thương hiệu bao quanh bộ lọc danh mục và thẻ CLB khi điều khiển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp chọn thẻ CLB chuyển hướng mượt mà đến trang chi tiết CLB tương ứng với hiệu ứng chuyển trang trượt 200ms.

### 10.4 Optimistic UI
- Khi bấm chọn nhanh bộ lọc danh mục, lọc tức thì các CLB có sẵn trong bộ nhớ đệm cục bộ (Client-side cache) trước khi gửi yêu cầu đồng bộ chính xác dữ liệu từ máy chủ.

### 10.5 Menu / Sheet
- Hỗ trợ nút bộ lọc nâng cao mở ra BottomSheet trên Mobile để lựa chọn chi tiết CLB theo từng Khoa cụ thể trong trường.

### 10.6 Toast / Undo
- Không áp dụng trực tiếp cho danh sách, trừ phi người dùng thực hiện lưu nhanh CLB ưa thích (Bookmark).

### 10.7 Motion
- Hiệu ứng trượt ngang của băng truyền (Carousel) giới thiệu các CLB nổi bật sử dụng CSS Transition mượt mà, hỗ trợ thao tác vuốt cảm ứng (Swipe gesture) tự nhiên trên thiết bị di động.

## 11. Accessibility Requirements
- Mọi thẻ CLB đều có cấu trúc tiêu đề `<h3>` rõ ràng hỗ trợ trình đọc màn hình dễ dàng duyệt danh sách.
- Hỗ trợ phím di chuyển mũi tên trái/phải để duyệt qua danh mục bộ lọc dạng tab.

## 12. Responsive Rules
- Màn hình cực rộng (>1440px): Tự động mở rộng lưới thành 5 cột.
- Màn hình di động đứng (<480px): Chuyển hoàn toàn sang bố cục danh sách dọc 1 cột với khoảng cách lề hai bên là 12px để tăng diện tích hiển thị hình ảnh Logo.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `clubs_list` (array of objects: `id`, `name`, `logo_url`, `category`, `member_count`, `is_recruiting`)
  - `selected_category` (string, default: `all`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `filterClubs(category)`
  - `searchClubs(keyword)`
  - `toggleSaveClub(clubId)`

## 15. Authorization / Privacy Rules
- Trang danh sách CLB được mở công khai cho tất cả mọi người, kể cả khách chưa đăng nhập hệ thống (chỉ được xem danh sách và thông tin công khai, không được đăng ký gia nhập).

## 16. Analytics / Audit Events
- `clubs_directory_viewed`: Ghi nhận số lượt truy cập trang danh mục CLB.
- `club_category_filtered`: Ghi nhận danh mục bộ lọc nào được người dùng quan tâm sử dụng nhiều nhất.

## 17. Do / Don't
- **Nên làm**: Hiển thị thẻ tuyển thành viên (Recruiting Tag) rõ ràng nổi bật để thu hút sinh viên đăng ký đúng thời điểm.
- **Không được làm**: Cho phép hiển thị các CLB ảo không có thật hoặc đã bị nhà trường đình chỉ hoạt động trong danh sách công khai.

## 18. Acceptance Criteria
- Hiển thị đầy đủ, chính xác danh sách các câu lạc bộ đang hoạt động trong hệ thống.
- Tính năng tìm kiếm theo từ khóa hoạt động nhanh, lọc kết quả chính xác trong vòng dưới 200ms.
- Bố cục responsive hoạt động ổn định, không bị méo hình ảnh hay tràn khung trên mọi trình duyệt phổ biến.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng lọc CLB hoạt động chính xác khi chọn các danh mục khác nhau.
- [ ] Xác minh các CLB bị ẩn hoặc khóa bởi Admin không xuất hiện trong danh sách này.
- [ ] Đảm bảo hiệu ứng Shimmer loading hiển thị đúng cấu trúc lưới trước khi dữ liệu được tải về hoàn chỉnh.
- [ ] Thử nghiệm thao tác vuốt Carousel CLB nổi bật trên các thiết bị di động Android và iOS.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ tải trang trì hoãn (Lazy loading) hoặc phân trang vô hạn (Infinite Scroll) thông qua Livewire để tối ưu hóa hiệu năng tải danh sách khi số lượng câu lạc bộ tăng lên nhiều.
- Lưu trữ bộ đệm danh sách CLB bằng Redis với thời gian hết hạn (TTL) là 30 phút để tăng tốc độ phản hồi trang đối với lưu lượng truy cập lớn từ sinh viên.
---