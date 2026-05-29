---
title: "Mentor Directory Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/mentor-directory.md"
related_design_docs:
  - "../04-design/mentorship-program.md"
related_system_docs:
  - "../05-system-architecture/mentorship-service.md"
related_database_docs:
  - "../06-database/mentors-table.md"
related_api_docs:
  - "../07-api/mentorship-api.md"
---

# Trang Danh Mục Người Định Hướng (Mentor Directory)

## 1. Purpose
Trang Danh Mục Người Định Hướng (Mentor Directory) là cổng thông tin tổng hợp giới thiệu danh sách các cựu sinh viên tiêu biểu, giảng viên giàu kinh nghiệm và chuyên gia đối tác của trường Đại học Sư phạm TP.HCM đã qua xác thực chính thức để làm cố vấn học tập, định hướng nghề nghiệp và chia sẻ kỹ năng sống cho sinh viên.

## 2. Product Context
Nằm trong giải pháp nâng cao chất lượng hướng nghiệp và kết nối việc làm thực tế của UEConnect, trang này đóng vai trò cầu nối rút ngắn khoảng cách giữa nhà trường và doanh nghiệp. Nó giúp sinh viên dễ dàng tiếp cận nguồn tri thức thực tiễn từ các anh chị đi trước một cách tin cậy và có tổ chức.

## 3. User Goals
- Khám phá danh sách các Mentors chất lượng cao theo từng lĩnh vực nghề nghiệp (Giáo dục, Công nghệ, Kinh tế...).
- Lọc Mentors theo Khoa đào tạo cũ tại HCMUE, số năm kinh nghiệm hoặc trạng thái sẵn sàng tư vấn.
- Xem đánh giá nhận xét từ các sinh viên đã được hướng dẫn trước đó.
- Gửi nhanh yêu cầu cố vấn (Mentorship Request) trực tiếp từ giao diện thẻ.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần tìm kiếm người hướng dẫn ôn thi, định hướng đề tài nghiên cứu khoa học, hoặc tìm kiếm cơ hội thực tập, việc làm.
- **Cán bộ Trung tâm Hỗ trợ Sinh viên**: Theo dõi, quản lý và điều phối mạng lưới cố vấn học đường của trường.

## 5. Entry Points
- Nhấp chọn mục **Cố vấn** (Mentors) trên thanh thanh điều hướng chính.
- Nhấp chọn từ liên kết giới thiệu chương trình Cố vấn đồng môn (Mentorship Campaign) được hiển thị trên Bảng tin chính.

## 6. Layout Strategy
Thiết kế tập trung vào sự chuyên nghiệp, tin cậy cao, sử dụng cấu trúc lưới thẻ chân dung sang trọng, khoa học.

### 6.1 Desktop Layout
- Bố cục chia 2 phần rõ rệt:
  - Sidebar bộ lọc bên trái (25%): Chứa các công cụ lọc chi tiết theo Chuyên môn, Khoa đào tạo cũ, Số năm kinh nghiệm, và Trạng thái sẵn sàng tư vấn.
  - Lưới hiển thị thẻ Mentor bên phải (75%): Trình bày dạng lưới 3 cột (`grid-cols-3`) hiển thị các thẻ chân dung Mentor nổi bật.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1200px.

### 6.2 Tablet Layout
- Sidebar bộ lọc bên trái chuyển thành một thanh lọc nhanh dạng cuộn ngang (Horizontal scroll) nằm ở đầu trang.
- Lưới thẻ Mentor chuyển thành 2 cột cân đối giúp hình ảnh chân dung hiển thị sắc nét.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc liên tục.
- Trên cùng là ô tìm kiếm tích hợp nút mở bộ lọc nâng cao dạng BottomSheet.
- Các thẻ Mentor được tối giản, hiển thị ảnh chân dung bo tròn góc gọn gàng bên trái, thông tin lĩnh vực và nút "Đăng ký tư vấn" lớn ở bên phải (`touch-target 44px`).

## 7. Information Architecture
- **Thanh tìm kiếm & Lọc (Search & Filter Section)**:
  - Tìm kiếm theo tên Mentor hoặc tên doanh nghiệp họ đang công tác.
  - Bộ lọc: Lĩnh vực chuyên môn, Cựu sinh viên Khoa nào, Số năm kinh nghiệm, Trạng thái (Sẵn sàng nhận Mentee / Đầy lịch).
- **Lưới danh sách Mentor (Mentors Grid)**:
  - Thẻ Mentor gồm: Ảnh chân dung chuyên nghiệp, Họ tên, Vị trí công tác hiện tại (Ví dụ: Trưởng phòng Nhân sự tại FPT), Khóa học cũ tại HCMUE.
  - Huy hiệu "Xác minh bởi Trường" (Verified Mentor Badge).
  - Số lượng sinh viên đang hướng dẫn (Mentees) và số sao đánh giá trung bình.

## 8. Core Components
- **Verified Mentor Badge**: Huy hiệu màu vàng kim sang trọng xác nhận thông tin lý lịch của Mentor đã được Phòng Công tác Sinh viên HCMUE đối chiếu và phê duyệt.
- **Expertise Tag**: Nhãn nhỏ màu xanh dương hiển thị lĩnh vực chuyên sâu (Ví dụ: `Giáo dục Tiểu học`, `Lập trình Web`, `Kỹ năng giao tiếp`).
- **Availability Indicator**: Chấm nhỏ hiển thị trạng thái (Xanh lá: Sẵn sàng tư vấn; Xám: Đã đầy lịch nhận Mentee).
- **Quick Request Button**: Nút "Đăng ký tư vấn" màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`) hoặc tùy chọn "Đã đầy" màu xám nhạt vô hiệu hóa.

## 9. States
### 9.1 Loading
- Lưới thẻ hiển thị 6 khung hình Skeleton giả lập ảnh chân dung tròn và các dòng chữ mờ nhấp nháy Shimmer liên tục trong lúc tải dữ liệu.

### 9.2 Empty
- Không tìm thấy Mentor nào khớp với từ khóa tìm kiếm hoặc bộ lọc đang chọn:
  - UI Copy: `"Không tìm thấy Người định hướng phù hợp."`
  - Mô tả UI Copy: `"Hãy thử thay đổi tiêu chí lọc hoặc gõ từ khóa tìm kiếm tổng quát hơn nhé."`

### 9.3 Error
- Lỗi tải danh sách do mất kết nối mạng hoặc sự cố máy chủ:
  - UI Copy: `"Đã xảy ra lỗi khi tải danh sách Cố vấn. Vui lòng thử lại sau."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút bấm đăng ký nhanh và hiển thị thông báo lỗi nếu cố tình nhấp vào.

### 9.5 Permission Restricted
- Chỉ dành cho sinh viên trường đã xác thực tài khoản. Người dùng là khách vãng lai sẽ thấy biểu tượng khóa mờ và yêu cầu xác thực tài khoản sinh viên để sử dụng tính năng kết nối này.

### 9.6 Success / Completed
- Dữ liệu tải xong mượt mà, tự động hiển thị các Mentor được đánh giá cao nhất và có nhiều hoạt động cống hiến nhất lên hàng đầu.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ Mentor sẽ kích hoạt hiệu ứng nâng thẻ nhẹ (`-translate-y-1` kèm đổ bóng rõ nét `shadow-lg`) và hiển thị các kỹ năng chi tiết của họ dưới dạng tooltip.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các bộ lọc và thẻ Mentor khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Nhấp chọn "Đăng ký tư vấn" sẽ kích hoạt hiệu ứng trượt mở BottomSheet biểu mẫu đăng ký nhanh (Mentor Request Form) để sinh viên điền thông tin và lý do xin cố vấn.

### 10.4 Optimistic UI
- Khi bấm chọn lọc danh mục, thực hiện lọc ngay tức thì trên dữ liệu có sẵn tại Client trước khi gửi yêu cầu đồng bộ chính xác dữ liệu từ máy chủ để giao diện phản hồi tức thì dưới 100ms.

### 10.5 Menu / Sheet
- Bộ lọc nâng cao trên Mobile mở ra một BottomSheet chi tiết phân loại theo từng Khoa cụ thể trong trường HCMUE giúp sinh viên dễ tìm thấy đồng môn khóa trên.

### 10.6 Toast / Undo
- Đăng ký gửi yêu cầu thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Xem đơn đăng ký".

### 10.7 Motion
- Hiệu ứng trượt ngang của các danh mục lọc mượt mà. Lưới thẻ Mentor co giãn mượt mà bằng CSS Transition khi thay đổi số lượng cột hiển thị.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh chân dung Mentor phải có thẻ mô tả chi tiết: `alt="Ảnh chân dung chuyên nghiệp của Mentor [Họ tên]"`.
- Hỗ trợ đầy đủ phím di chuyển mũi tên để chọn nhanh giữa các kỹ năng trong hộp chọn bộ lọc.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Lưới thẻ Mentor tự động chuyển sang bố cục danh sách dọc 1 cột với khoảng cách lề hai bên là 12px để tăng diện tích hiển thị dọc của thẻ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `mentors_list` (array of objects: `id`, `name`, `avatar_url`, `company`, `experience_years`, `rating`, `is_available`, `faculty_name`)
  - `selected_expertise` (string, default: `all`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `filterMentors(expertise, faculty, availability)`
  - `searchMentors(keyword)`
  - `submitQuickMentorshipRequest(mentorId, data)`

## 15. Authorization / Privacy Rules
- Chỉ sinh viên chính quy đã đăng nhập và được hệ thống xác thực tài khoản mới có quyền xem thông tin chi tiết và gửi yêu cầu kết nối với Mentor.

## 16. Analytics / Audit Events
- `mentor_directory_viewed`: Ghi nhận lượt mở trang danh mục Mentor.
- `mentor_search_performed`: Theo dõi các từ khóa chuyên môn được sinh viên tìm kiếm nhiều nhất để gợi ý trường mở thêm các chuyên đề bồi dưỡng kỹ năng tương ứng.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị số lượng cựu sinh viên đang làm Mentor để khích lệ lòng tự hào và tinh thần học hỏi nối tiếp của các thế hệ sinh viên HCMUE.
- **Không được làm**: Cho phép hiển thị thông tin liên hệ riêng tư của Mentor (như Số điện thoại cá nhân, Địa chỉ nhà) công khai trên trang danh sách để tránh các hành vi quấy rối hoặc lạm dụng dữ liệu.

## 18. Acceptance Criteria
- Hiển thị đầy đủ, chính xác danh sách các Mentor đã qua phê duyệt xác thực.
- Bộ lọc và thanh tìm kiếm hoạt động ổn định, trả về kết quả chính xác dưới 300ms.
- Bố cục Responsive hoạt động mượt mà không bị vỡ giao diện trên mọi kích thước thiết bị.

## 19. QA / UAT Checklist
- [ ] Kiểm tra huy hiệu xác thực hiển thị đúng và chỉ dành cho tài khoản Mentor đã qua kiểm duyệt lý lịch từ trường.
- [ ] Xác minh tính năng lọc hoạt động chính xác khi thay đổi đồng thời cả Lĩnh vực và Khoa đào tạo cũ.
- [ ] Thử nghiệm đăng ký tư vấn và đảm bảo biểu mẫu được gửi thành công, ghi đúng dữ liệu vào cơ sở dữ liệu.
- [ ] Đảm bảo các Mentor ở trạng thái khóa hoạt động không xuất hiện trong danh mục công khai này.

## 20. AI Agent Implementation Notes
- Sử dụng thuật toán sắp xếp (Ranking algorithm) dựa trên điểm đánh giá trung bình và tỷ lệ phản hồi đơn đăng ký của Mentor để ưu tiên hiển thị những Mentor hoạt động tích cực lên hàng đầu, giúp tăng chất lượng trải nghiệm cho sinh viên.
- Cấu hình chỉ mục tối ưu (Optimized indexes) cho các trường tìm kiếm `expertise` và `company` trong bảng `mentors` cơ sở dữ liệu để đạt hiệu năng truy vấn cao nhất.
---