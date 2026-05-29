---
title: "Account Status Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/account-status.md"
related_design_docs:
  - "../04-design/moderation-system.md"
related_system_docs:
  - "../05-system-architecture/moderation-rules.md"
related_database_docs:
  - "../06-database/violations-table.md"
related_api_docs:
  - "../07-api/violations-api.md"
---

# Trang Trạng Thái Tài Khoản (Account Status)

## 1. Purpose
Trang Trạng Thái Tài Khoản cung cấp cho người dùng cái nhìn toàn diện, minh bạch về trạng thái hoạt động hiện tại của tài khoản, lịch sử các vi phạm tiêu chuẩn cộng đồng HCMUE (nếu có), các điểm cảnh cáo tích lũy, các hạn chế đang áp dụng và hướng dẫn các bước kháng nghị.

## 2. Product Context
Để xây dựng một không gian học đường HCMUE văn minh, lành mạnh và đáng tin cậy, UEConnect áp dụng cơ chế đánh giá điểm hành vi và cảnh cáo tự động. Trang này đảm bảo quyền được biết, tính công bằng và giải trình rõ ràng cho mọi người dùng khi bị xử phạt bởi hệ thống kiểm duyệt nội dung.

## 3. User Goals
- Kiểm tra xem tài khoản có đang ở trạng thái Tốt (Good Standing) hay không.
- Xem chi tiết lý do và thời gian hết hạn của các hình thức kỷ luật (ví dụ: bị cấm đăng bài 7 ngày).
- Xem lịch sử các cảnh cáo đã nhận từ Ban quản trị.
- Gửi yêu cầu kháng nghị (Appeal) kèm theo minh chứng nếu cho rằng có sự nhầm lẫn trong quyết định kiểm duyệt.

## 4. Primary Users
- **Người dùng phổ thông**: Muốn chắc chắn tài khoản của mình không có vi phạm nào.
- **Người dùng đang bị hạn chế**: Xem lý do cụ thể và nộp đơn kháng nghị để khôi phục quyền hoạt động.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Trạng thái tài khoản**.
- Nhấp vào thông báo hệ thống khi tài khoản bị khóa tính năng hoặc nhận cảnh cáo mới.

## 6. Layout Strategy
Thiết kế tập trung vào sự rõ ràng, nghiêm túc, tạo cảm giác an tâm nhưng vẫn đủ tính cảnh báo.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột (Single Column Center Focus).
- Widget hiển thị trạng thái lớn ở phía trên với đồ thị hình khuyên (Circular Progress) chỉ mức độ an toàn của tài khoản.
- Danh sách lịch sử vi phạm chi tiết dạng dòng thời gian (Timeline) ở phía dưới.
- Độ rộng khung nội dung: 720px.

### 6.2 Tablet Layout
- Tương tự Desktop nhưng thu gọn khoảng cách lề.
- Đồ thị trạng thái và bảng lịch sử vi phạm tự động co giãn vừa vặn màn hình.

### 6.3 Mobile / PWA Layout
- Bố cục một cột vuốt dọc toàn màn hình.
- Tiêu đề trạng thái chuyển màu linh hoạt (Xanh lá nếu an toàn, Vàng nếu cảnh cáo nhẹ, Đỏ nếu vi phạm nghiêm trọng).
- Hành động kháng cáo mở ra một BottomSheet chuyên dụng để đính kèm tệp và viết giải trình.

## 7. Information Architecture
- **Phần đầu trang (Header Status)**:
  - Tên tài khoản, nhãn phân loại người dùng (Sinh viên, Giảng viên, Cựu sinh viên).
  - Trạng thái tổng quát: "Bình thường", "Bị hạn chế một phần", "Bị khóa tạm thời".
- **Biểu đồ điểm an toàn (Safety Score Scoreboard)**:
  - Điểm tín nhiệm hành vi (thang điểm 100).
- **Lịch sử vi phạm (Violation History List)**:
  - Loại vi phạm (Nội dung không phù hợp, Spam, Xúc phạm cá nhân...).
  - Thời gian vi phạm và thời gian hết hạn hiệu lực kỷ luật.
  - Trạng thái xử lý kháng cáo.

## 8. Core Components
- **Status Badge**: Nhãn hiển thị trạng thái màu sắc trực quan (Xanh: `#10B981`, Vàng: `#F59E0B`, Đỏ: `#EF4444`).
- **Timeline Card**: Thẻ thông tin hiển thị từng mốc thời gian vi phạm và các nội dung bị ẩn.
- **Appeal Button**: Nút "Gửi kháng nghị" dạng Outline Button (`border-gray-300 hover:bg-gray-50`) nhằm tránh khuyến khích bấm bừa bãi nhưng vẫn dễ tìm thấy.
- **Empty State Component**: Trạng thái sạch (không vi phạm), hiển thị minh họa khiên bảo vệ màu xanh lá cùng thông điệp tích cực.

## 9. States
### 9.1 Loading
- Vòng tròn tải dữ liệu dạng xoay mượt ở giữa màn hình. Lịch sử vi phạm hiển thị 3 dòng placeholder giả.

### 9.2 Empty
- Trạng thái tài khoản hoàn toàn sạch sẽ, không có vi phạm nào:
  - Tiêu đề UI Copy: `"Tài khoản của bạn đang ở trạng thái rất tốt!"`
  - Mô tả UI Copy: `"Cảm ơn bạn đã đóng góp xây dựng cộng đồng UEConnect văn minh, lịch sự và đáng tin cậy."`

### 9.3 Error
- Lỗi tải thông tin trạng thái tài khoản:
  - UI Copy: `"Không thể kết nối đến hệ thống kiểm duyệt. Vui lòng thử lại sau ít phút."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo ngoại tuyến. Người dùng chỉ xem được dữ liệu đã lưu trong Cache cục bộ và không thể gửi đơn kháng nghị mới.

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho chính chủ tài khoản để tự theo dõi.

### 9.6 Success / Completed
- Đơn kháng nghị gửi đi thành công:
  - UI Copy: `"Kháng nghị của bạn đã được tiếp nhận. Đội ngũ kiểm duyệt HCMUE sẽ xem xét và phản hồi trong vòng 24 - 48 giờ làm việc."`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các sự kiện vi phạm trên dòng thời gian sẽ làm sáng thẻ đó lên (`shadow-md` và tăng nhẹ độ đậm của viền).

### 10.2 Focus
- Phím Tab di chuyển tuần tự qua các nút "Xem chi tiết vi phạm" và "Gửi kháng nghị" với khung viền nét đứt màu xanh đậm thương hiệu.

### 10.3 Press / Tap
- Nhấp chọn kháng cáo sẽ có phản hồi xúc giác nhẹ (Haptic feedback) và mở trực tiếp BottomSheet từ dưới lên với hiệu ứng trượt 250ms.

### 10.4 Optimistic UI
- Khi bấm "Gửi kháng nghị", cập nhật ngay trạng thái dòng thời gian thành "Đang chờ duyệt kháng nghị" mà không bị đơ giao diện.

### 10.5 Menu / Sheet
- Hỗ trợ xem các điều khoản vi phạm chi tiết ngay trong giao diện ứng dụng thông qua modal popup thu nhỏ để không chuyển trang.

### 10.6 Toast / Undo
- Sử dụng Toast màu xanh lá dịu mắt để thông báo nộp kháng cáo thành công.

### 10.7 Motion
- Hiệu ứng chuyển động vẽ biểu đồ tròn từ 0% đến điểm hiện tại khi trang vừa được tải xong (Animation Spring 0.6s).

## 11. Accessibility Requirements
- Độ tương phản màu sắc của nhãn cảnh báo đỏ và vàng đạt tối thiểu 4.5:1.
- Mọi hình ảnh minh họa trạng thái phải đi kèm thẻ mô tả chi tiết `alt` cho các trình đọc màn hình (Screen Readers).

## 12. Responsive Rules
- Màn hình nhỏ dưới 600px: Đồ thị hình khuyên thu nhỏ kích thước từ 180px xuống 120px, nội dung bảng lịch sử chuyển sang định dạng danh sách cuộn dọc đơn giản.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `account_id` (integer)
  - `safety_score` (integer, range: 0-100)
  - `violations` (array of objects: `id`, `category`, `reported_at`, `status`, `expires_at`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchAccountStatus()`
  - `submitAppeal(violationId, explanationText, attachmentUrl)`
  - `cancelAppeal(appealId)`

## 15. Authorization / Privacy Rules
- Chỉ có chủ tài khoản hoặc Quản trị viên cấp cao của trường (Admin, Moderator) mới có quyền truy cập xem trang này. Thông tin vi phạm hoàn toàn được bảo mật, không hiển thị công khai trên hồ sơ cá nhân.

## 16. Analytics / Audit Events
- `account_status_viewed`: Ghi nhận khi người dùng kiểm tra trạng thái vi phạm của họ.
- `appeal_submitted`: Ghi nhận mã vi phạm kháng nghị, nội dung kháng nghị và thời gian nộp.

## 17. Do / Don't
- **Nên làm**: Giải thích chi tiết và trích dẫn rõ ràng câu từ hoặc bài đăng cụ thể nào đã vi phạm quy tắc ứng xử để người dùng tự rút kinh nghiệm.
- **Không được làm**: Sử dụng các từ ngữ mang tính chỉ trích hay phán xét nặng nề. Hãy duy trì ngôn ngữ học đường chuẩn mực, khách quan và mang tính xây dựng.

## 18. Acceptance Criteria
- Hiển thị chính xác điểm tín nhiệm hiện tại và danh sách đầy đủ các vi phạm đang hiệu lực.
- Nút gửi đơn kháng nghị chỉ hoạt động đối với các vi phạm chưa hết hạn và chưa từng được kháng nghị thành công trước đó.
- Giao diện đáp ứng tốt trên cả nền tối (Dark Mode) và nền sáng (Light Mode).

## 19. QA / UAT Checklist
- [ ] Xác minh điểm tín nhiệm thay đổi chính xác khi có vi phạm mới được ghi nhận trong cơ sở dữ liệu.
- [ ] Thử nghiệm gửi tệp đính kèm kháng nghị (định dạng PDF/JPG) đạt giới hạn dung lượng 5MB.
- [ ] Kiểm tra khả năng điều hướng bàn phím qua dòng thời gian vi phạm.
- [ ] Đảm bảo thiết bị di động hiển thị thông tin vi phạm cuộn mượt mà không bị vỡ bố cục.

## 20. AI Agent Implementation Notes
- Sử dụng Chart.js hoặc SVG vẽ thủ công để tạo vòng tròn điểm tín nhiệm nhẹ và phản hồi nhanh.
- Thiết kế Model `Violation` liên kết trực tiếp với bảng `Users` và `ModerationLogs`. Xử lý lưu các tài liệu kháng cáo vào Cloud Storage an toàn của trường HCMUE.
