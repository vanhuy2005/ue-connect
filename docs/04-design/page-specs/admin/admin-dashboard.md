---
title: "Admin Dashboard Page"
module: "04-design/page-specs/admin"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/admin-dashboard.md"
related_design_docs:
  - "../04-design/admin-portal.md"
related_system_docs:
  - "../05-system-architecture/admin-service.md"
related_database_docs:
  - "../06-database/admin-tables.md"
related_api_docs:
  - "../07-api/admin-api.md"
---

# Trang Bảng Điều Khiển Quản Trị (Admin Dashboard)

## 1. Purpose
Trang Bảng Điều Khiển Quản Trị (Admin Dashboard) là trung tâm đầu não cung cấp cho Ban quản trị trường (Admin, Phòng Công tác Sinh viên) các báo cáo phân tích số liệu thời gian thực (Real-time Analytics), biểu đồ thống kê tăng trưởng thành viên, tình hình hoạt động kết nối Cố vấn (Mentorship), số lượng tệp tài liệu trong Thư viện tài nguyên và tổng số đơn phản ánh vi phạm an toàn chờ xử lý trên UEConnect.

## 2. Product Context
Để đảm bảo vận hành thông suốt và an toàn cho toàn bộ hệ sinh thái UEConnect của trường Đại học Sư phạm TP.HCM, trang bảng điều khiển quản trị được thiết kế khoa học, trình bày trực quan các chỉ số đo lường hiệu năng cốt lõi (KPIs), giúp Ban quản lý đưa ra các quyết định điều hành chính xác và kịp thời.

## 3. User Goals
- Theo dõi tức thời các số liệu vận hành hệ thống then chốt (Tổng số người dùng, số lượng đăng ký mới, số đơn xác thực học thuật chờ duyệt, số báo cáo vi phạm).
- Phân tích xu hướng tăng trưởng thành viên và mức độ tương tác thông qua biểu đồ trực quan (Charts/Graphs).
- Truy cập nhanh đến các phân hệ quản lý chuyên sâu (Duyệt đơn xác thực, Xử lý báo cáo vi phạm, Kiểm duyệt nội dung Thư viện).
- Theo dõi nhật ký hoạt động hệ thống (System Logs overview) để kịp thời phát hiện và khắc phục các sự cố kỹ thuật.

## 4. Primary Users
- **Quản trị viên hệ thống (System Admin)**: Quản lý hạ tầng kỹ thuật và cấp phát quyền hạn.
- **Cán bộ Phòng Công tác Sinh viên**: Quản lý xét duyệt thông tin học thuật, kết nối Mentor và xử lý kỷ luật.

## 5. Entry Points
- Nhấp chọn **Cổng quản trị** (Admin Portal) hiển thị độc quyền trên thanh điều hướng chính đối với các tài khoản có quyền Quản trị viên (`admin` role).

## 6. Layout Strategy
Thiết kế bố cục kiểu Bảng điều khiển lưới mô-đun (Modular Grid Dashboard layout) sang trọng, gọn gàng, mang lại khả năng bao quát thông tin tốt nhất.

### 6.1 Desktop Layout
- Bố cục chia 2 phần rõ rệt:
  - Sidebar điều hướng bên trái (20%): Chứa danh mục các phân hệ quản trị (Tổng quan, Duyệt xác thực, Báo cáo vi phạm, Thư viện tài nguyên, Nhật ký hệ thống).
  - Khung nội dung chính bên phải (80%):
    - Hàng thẻ số liệu thống kê nhanh (Card Row): 4 thẻ lớn chứa số liệu đếm số nổi bật kèm biểu tượng màu sắc.
    - Khu vực biểu đồ (Charts Area): Lưới 2 cột chứa Biểu đồ tăng trưởng người dùng mới và Đồ thị tương tác kết nối Mentor.
    - Hàng hoạt động gần đây (Recent Activities Table) xếp ngang phía dưới cùng.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1280px.

### 6.2 Tablet Layout
- Sidebar điều hướng bên trái thu gọn thành thanh thực đơn nhỏ chỉ hiển thị các biểu tượng (Icon-only sidebar).
- Lưới biểu đồ chuyển thành bố cục xếp chồng dọc 1 cột để bảo toàn kích thước đồ thị rõ nét.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình hoàn toàn.
- Sidebar ẩn đi, thay bằng một nút thực đơn góc trái trên cùng (Hamburger Menu) trượt mở ngăn kéo điều hướng (Navigation Drawer).
- Các thẻ số liệu thống kê được thiết kế dạng khối tròn bo góc nhỏ gọn, cuộn ngang tiện lợi (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Hàng thẻ số liệu thống kê nhanh (Statistics Cards Row)**:
  - Tổng số thành viên (Active Users), Tổng kết nối bạn bè (Active Connections), Số đơn xác thực chờ duyệt (Pending Verifications), Số báo cáo vi phạm cần xử lý (Unresolved Reports).
- **Khu vực Biểu đồ tăng trưởng (Growth Charts Section)**:
  - Biểu đồ đường (Line Chart) biểu diễn số lượng sinh viên đăng ký mới theo tuần/tháng.
  - Biểu đồ cột (Bar Chart) thống kê số lượng tài liệu học thuật được tải lên theo từng Khoa đào tạo của trường.
- **Nhật ký hoạt động gần đây (Recent Audit Logs Table)**:
  - Bảng ghi nhận 5 hành động quản trị gần nhất: Thời gian, Quản trị viên thực hiện, hành động, Trạng thái (Thành công / Thất bại).

## 8. Core Components
- **Dashboard Stat Card**: Thẻ số liệu đếm lớn, nổi bật số lượng chữ số, tích hợp biểu tượng màu sắc tương ứng (Ví dụ: Đỏ cam cho vi phạm, Xanh ngọc cho thành viên).
- **Chart Container Wrapper**: Khung chứa biểu đồ tương tác, hỗ trợ bộ chọn bộ lọc thời gian (Hôm nay, 7 ngày qua, 30 ngày qua) và hiển thị tooltip thông tin chi tiết khi rê chuột qua điểm dữ liệu.
- **Audit Logs Table**: Bảng hiển thị dữ liệu lịch sử hoạt động quản trị sạch sẽ, hỗ trợ phân trang nhanh.
- **Admin Navigation Sidebar**: Thanh điều hướng phẳng nằm cố định lề trái màn hình, làm nổi bật phân hệ đang được chọn.

## 9. States
### 9.1 Loading
- Toàn bộ các thẻ số liệu thống kê và khung biểu đồ hiển thị các khung hộp trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải số liệu API từ máy chủ.

### 9.2 Empty
- Không áp dụng cho trang bảng điều khiển quản trị trung tâm này.

### 9.3 Error
- Lỗi tải số liệu phân tích do sự cố máy chủ API:
  - UI Copy trên các khối biểu đồ: `"Không thể tải dữ liệu phân tích. Vui lòng tải lại trang."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa tất cả bộ lọc thời gian biểu đồ và các nút hành động điều hướng.

### 9.5 Permission Restricted
- Người dùng thường (Sinh viên, Cựu sinh viên chưa được phân quyền) cố tình gõ trực tiếp liên kết trang quản trị:
  - Hệ thống tự động chặn và hiển thị màn hình lỗi 403: `"Bạn không có quyền truy cập khu vực quản trị này."`

### 9.6 Success / Completed
- Dữ liệu tải xong mượt mà, tự động vẽ các đường biểu đồ trực quan sắc nét và đồng bộ số liệu đếm số thời gian thực chính xác tuyệt đối.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các cột biểu đồ sẽ làm sáng cột và hiển thị Popover nhỏ chứa số lượng chính xác tại điểm dữ liệu đó (Tooltip details). Di chuột qua dòng trong bảng nhật ký sẽ đổi màu nền sang xám kem dịu (`bg-gray-50`).

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút chọn bộ lọc thời gian biểu đồ khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn bộ lọc thời gian biểu đồ sẽ làm biểu đồ vẽ lại đường dữ liệu mượt mà, chuyển động nhanh dưới 150ms cực kỳ chuyên nghiệp.

### 10.4 Optimistic UI
- Khi bấm chọn bộ lọc thời gian, vẽ ngay các đường lưới trống giả lập trước khi nhận đầy đủ dữ liệu mảng vẽ thực tế từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Thực đơn điều hướng trên di động trượt mở ra một ngăn kéo điều hướng (Navigation Drawer) mượt mà từ lề trái màn hình dễ chạm bấm.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động quản trị an toàn trên bảng điều khiển.

### 10.7 Motion
- Hoạt ảnh vẽ biểu đồ (Chart drawing animations) trơn tru, co giãn mượt mà khi thay đổi kích thước cửa sổ trình duyệt bằng CSS transition.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi biểu đồ phải đi kèm bảng dữ liệu đọc (Alt Data Table) ẩn dưới giao diện để hỗ trợ các trình đọc màn hình dễ dàng phân tích số liệu cho người khiếm thị.
- Hỗ trợ phím Escape để đóng nhanh ngăn kéo điều hướng di động.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Toàn bộ biểu đồ và bảng nhật ký chuyển hoàn toàn sang bố cục dọc 1 cột cuộn mượt mà, các thẻ số liệu thống kê thu nhỏ chữ để dành không gian hiển thị số đếm rõ ràng nhất.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `total_active_users` (integer)
  - `total_connections` (integer)
  - `pending_verifications_count` (integer)
  - `unresolved_reports_count` (integer)
  - `registration_growth_data` (array of objects: `date`, `count`)
  - `resource_department_data` (array of objects: `department`, `count`)

## 14. API / Action Requirements
- Gọi Livewire / Analytics Action:
  - `fetchDashboardStats()`
  - `fetchGrowthChartData(timeframeFilter)`
  - `fetchDepartmentChartData()`
  - `fetchRecentAuditLogs()`

## 15. Authorization / Privacy Rules
- Bảo mật quyền truy cập tối đa: Chỉ các tài khoản được gán quyền Quản trị viên (`admin` role) thông qua Middleware bảo mật nghiêm ngặt mới được quyền xem trang bảng điều khiển này. Mọi hành vi cố tình xâm nhập đều bị ghi lại nhật ký bảo mật và gửi cảnh báo đỏ về máy chủ trung tâm.

## 16. Analytics / Audit Events
- `admin_dashboard_viewed`: Ghi nhận mỗi lần quản trị viên mở xem trang bảng điều khiển để đánh giá tần suất vận hành.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị thời gian đồng bộ dữ liệu gần nhất (Ví dụ: `"Cập nhật 2 phút trước"`) để quản trị viên nắm rõ tính mới của số liệu thống kê.
- **Không được làm**: Cho phép hiển thị mật khẩu hoặc mã khóa bảo mật của máy chủ công khai trong bất kỳ khối nhật ký nào trên trang bảng điều khiển.

## 18. Acceptance Criteria
- Bảng điều khiển quản trị tải và hiển thị chính xác toàn bộ số liệu thống kê nhanh từ cơ sở dữ liệu.
- Các biểu đồ thống kê vẽ đúng đường dữ liệu, hỗ trợ chuyển đổi bộ lọc thời gian mượt mà.
- Quyền truy cập được kiểm soát nghiêm ngặt, chặn đứng mọi hành vi truy cập trái phép từ tài khoản thường.

## 19. QA / UAT Checklist
- [ ] Kiểm tra các thẻ số đếm thống kê hiển thị đúng giá trị thực tế trong các bảng cơ sở dữ liệu.
- [ ] Xác minh biểu đồ đường tự động vẽ lại chính xác khi thay đổi bộ lọc thời gian sang "7 ngày qua".
- [ ] Thử nghiệm gõ đường dẫn trang quản trị từ tài khoản sinh viên thường và đảm bảo hệ thống chặn đúng lỗi 403.
- [ ] Đảm bảo giao diện responsive hiển thị cân đối và không bị vỡ khung biểu đồ trên di động.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `Chart.js` hoặc `ApexCharts` kết hợp cùng Livewire để tối ưu hóa hiệu năng vẽ đồ thị phía Client cực nhẹ và mượt mà.
- Thiết kế cơ chế tự động làm mới số liệu (Polling) sau mỗi 60 giây bằng Livewire để đảm bảo số liệu đếm số luôn cập nhật mới nhất mà không gây quá tải cho máy chủ cơ sở dữ liệu.
---
