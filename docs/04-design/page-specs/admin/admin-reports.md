---
title: "Admin Reports Management Page"
module: "04-design/page-specs/admin"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/safety-reporting.md"
related_design_docs:
  - "../04-design/moderation-system.md"
related_system_docs:
  - "../05-system-architecture/moderation-rules.md"
related_database_docs:
  - "../06-database/violations-table.md"
related_api_docs:
  - "../07-api/violations-api.md"
---

# Trang Quản Lý Báo Cáo Vi Phạm & Kỷ Luật (Admin Reports Management)

## 1. Purpose
Trang Quản Lý Báo Cáo Vi Phạm & Kỷ Luật cung cấp cho Ban quản trị trường và Cán bộ Phòng Công tác Sinh viên HCMUE công cụ chuyên sâu để tiếp nhận, thẩm định và xử lý các đơn báo cáo quấy rối, bắt nạt học đường hoặc vi phạm Quy tắc ứng xử văn minh trong khuôn viên số UEConnect, áp dụng các chế tài kỷ luật chính xác (Cảnh cáo, Khóa tài khoản tạm thời, Khóa vĩnh viễn) và khôi phục sự an toàn cho cộng đồng sinh viên.

## 2. Product Context
Để xây dựng một không gian giáo dục lành mạnh, văn minh, Phòng Công tác Sinh viên cần một trung tâm xử lý vi phạm khoa học, bảo mật ẩn danh tuyệt đối cho người gửi báo cáo, cho phép Cán bộ nhanh chóng ra quyết định kỷ luật dựa trên các minh chứng chụp màn hình rõ ràng.

## 3. User Goals
- Theo dõi toàn bộ danh sách báo cáo vi phạm mới gửi đến sắp xếp theo mức độ nghiêm trọng (Priority/Severity).
- Thẩm định chi tiết nội dung bị báo cáo (bài viết vi phạm, bình luận thô tục, ảnh nhạy cảm) đính kèm minh chứng.
- Thực hiện nhanh các hành động xử lý kỷ luật: Bỏ qua (Dismiss), Cảnh cáo người dùng (Warn), Gỡ bỏ nội dung (Remove content), Khóa tài khoản vi phạm (Ban account).
- Theo dõi lịch sử kỷ luật tích lũy của tài khoản bị báo cáo để đưa ra hình thức phạt tăng nặng phù hợp.

## 4. Primary Users
- **Cán bộ Phòng Công tác Sinh viên**: Người phụ trách kiểm duyệt, tiếp nhận báo cáo vi phạm an toàn và phê duyệt kỷ luật tài khoản vi phạm.

## 5. Entry Points
- Nhấp chọn mục **Báo cáo vi phạm** (Reports) trên thanh Sidebar điều hướng của Cổng quản trị Admin.

## 6. Layout Strategy
Thiết kế bố cục kiểu Bảng điều khiển kiểm duyệt chuyên sâu (Moderation Workspace Layout) phân chia rõ ràng giữa hàng đợi báo cáo và không gian thẩm định minh chứng.

### 6.1 Desktop Layout
- Bố cục chia đôi cột linh hoạt (35% - 65%):
  - Cột bên trái (Hàng đợi báo cáo - 35%): Chứa danh sách các thẻ báo cáo chờ xử lý xếp dọc.
  - Cột bên phải (Khung thẩm định chi tiết - 65%): Hiển thị chi tiết nội dung bị báo cáo, minh chứng ảnh đính kèm có hỗ trợ thu phóng và bảng thông tin lịch sử vi phạm của bị đơn.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1280px.

### 6.2 Tablet Layout
- Hàng đợi báo cáo bên trái thu gọn thành thanh dọc thu nhỏ (Icons-only).
- Cột bên phải thẩm định chi tiết mở rộng chiếm toàn bộ chiều ngang hiển thị trên màn hình với lề 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tối giản.
- Khách hàng là Cán bộ quản trị ưu tiên duyệt đơn trên máy tính để bàn văn phòng. Giao diện di động đóng vai trò xem nhanh và xử lý khẩn cấp qua các hộp thoại BottomSheet dễ thao tác (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Danh sách báo cáo (Reports Queue)**:
  - Mỗi thẻ báo cáo gồm: Mã báo cáo, Loại vi phạm (Spam/Hate speech/Harassment), Đối tượng bị báo cáo, Mức độ ưu tiên màu sắc (Cao: Đỏ rực, Trung bình: Cam, Thấp: Vàng).
- **Khung thẩm định chi tiết (Report Details Panel)**:
  - Khối thông tin: Người báo cáo (Ẩn danh đối với người ngoài), Người bị báo cáo, Nội dung gốc vi phạm (Văn bản trích dẫn).
  - Khối minh chứng (Proof Gallery): Lưới hiển thị ảnh chụp màn hình minh chứng vi phạm, bấm vào để mở hộp thoại phóng to ảnh.
  - Lịch sử vi phạm (Infraction History): Số lần tài khoản này bị cảnh cáo hoặc gỡ bài viết trước đây (Ví dụ: `Đã cảnh cáo: 2 lần`).
- **Khung xử lý hành động (Moderation Actions Box)**:
  - Hàng nút hành động: "Bỏ qua" (Xám), "Cảnh cáo người dùng" (Cam), "Gỡ bài viết" (Đỏ nhạt), "Khóa tài khoản" (Đỏ sẫm).

## 8. Core Components
- **Priority Severity Tag**: Nhãn màu sắc phân cấp mức độ nghiêm trọng của báo cáo để Cán bộ ưu tiên xử lý các trường hợp quấy rối nghiêm trọng lên hàng đầu.
- **Proof Lightbox Viewer**: Khung phóng to xem ảnh minh chứng chụp màn hình mượt mà, hỗ trợ xoay ảnh và vẽ ghi chú.
- **Sanction Decision Trigger**: Hộp thoại ra quyết định kỷ luật, yêu cầu viết lý do kỷ luật và chọn thời hạn khóa tài khoản (3 ngày, 7 ngày, Vĩnh viễn).
- **User Violation Counter**: Bản ghi nhận lịch sử vi phạm của tài khoản bị báo cáo giúp Cán bộ xác định các trường hợp tái phạm liên tục.

## 9. States
### 9.1 Loading
- Toàn bộ các thẻ hàng đợi báo cáo và khung chi tiết hiển thị các thanh Skeleton xám nhẹ nhấp nháy Shimmer liên tục trong lúc tải dữ liệu.

### 9.2 Empty
- Khi không còn đơn báo cáo nào chờ xử lý trong hàng đợi:
  - UI Copy ở giữa màn hình:
    - UI Copy: `"Hàng đợi báo cáo vi phạm đang trống."`
    - Mô tả UI Copy: `"Môi trường học đường UEConnect đang vô cùng an toàn và lành mạnh. Tuyệt vời!"` kèm biểu tượng khiên bảo vệ xanh lá rực rỡ.

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi tải danh sách báo cáo. Vui lòng tải lại trang."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa toàn bộ các nút hành động kỷ luật và tính năng gửi quyết định.

### 9.5 Permission Restricted
- Chỉ dành cho cán bộ Phòng Công tác Sinh viên có quyền Kiểm duyệt (`moderator` / `admin` role). Các tài khoản khác bị chặn tuyệt đối qua Middleware bảo mật.

### 9.6 Success / Completed
- Xử lý kỷ luật thành công:
  - Thẻ báo cáo vi phạm biến mất khỏi hàng đợi mượt mà.
  - Hiện Toast thông báo xanh dịu: `"Đã áp dụng chế tài kỷ luật đối với tài khoản [Họ tên] thành công!"`
  - Hệ thống tự động gửi thông báo cảnh cáo và khóa tài khoản tương ứng, gỡ bỏ nội dung vi phạm khỏi Bảng tin chính ngay lập tức.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ đơn hàng đợi bên trái sẽ làm thẻ đổi màu nền sang xám kem dịu (`bg-gray-100`) để báo hiệu trạng thái sẵn sàng chọn thẩm định.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu đỏ sẫm bao quanh các nút ra quyết định kỷ luật khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn nút "Khóa tài khoản" sẽ lập tức kích hoạt hộp thoại xác nhận chi tiết chứa hộp chọn thời hạn khóa tài khoản và ô viết lý do kỷ luật trong vòng dưới 100ms.

### 10.4 Optimistic UI
- Khi bấm xử lý kỷ luật, thẻ báo cáo lập tức biến mất khỏi hàng đợi hiển thị bên trái tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hộp thoại quyết định kỷ luật trên di động mở ra một BottomSheet mượt mà chứa các nút bấm hành động lớn dễ chạm bấm nhanh chóng.

### 10.6 Toast / Undo
- Hành động "Bỏ qua báo cáo" thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh đơn về trạng thái chờ xử lý trong vòng 5 giây nếu cán bộ lỡ tay bấm nhầm.

### 10.7 Motion
- Hiệu ứng co ngắn và trượt biến mất của thẻ đơn hàng đợi diễn ra trong vòng 200ms cực kỳ mượt mà. Hoạt ảnh mở Modal phóng ảnh trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh minh chứng phải có mô tả thay thế chi tiết cho trình đọc màn hình: `alt="Hình ảnh minh chứng báo cáo vi phạm đính kèm của [Tên người báo cáo]"`.
- Hỗ trợ phím Escape để đóng nhanh hộp thoại quyết định kỷ luật.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị dữ liệu lịch sử vi phạm để tránh bị tràn màn hình, ưu tiên hiển thị nội dung vi phạm gốc và hàng nút hành động ghim cố định ở đáy.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `pending_reports` (array of objects: `report_id`, `category`, `reported_user`, `reporter_name`, `severity`, `created_at`)
  - `active_report_details` (object: `report_id`, `reported_user_id`, `reported_user_name`, `reported_content`, `proof_images`, `user_infractions_count`)

## 14. API / Action Requirements
- Gọi Livewire / Admin Action:
  - `fetchPendingReportsQueue()`
  - `loadReportDetails(reportId)`
  - `dismissSafetyReport(reportId)`
  - `executeModerationAction(reportId, actionType, reasonText, banDuration)`
  - `undoReportDecision(reportId)`

## 15. Authorization / Privacy Rules
- Bảo mật ẩn danh tuyệt đối: Danh tính của người gửi báo cáo hoàn toàn được giấu kín đối với người bị báo cáo. Chỉ có Ban quản trị trường (Admin, Moderator) mới có quyền truy cập xem thông tin người gửi trong hệ thống quản lý để đối chiếu phê duyệt đơn. Tuyệt đối không hiển thị công khai các thông tin này lên trang cá nhân hay bất kỳ kênh truyền thông nào.

## 16. Analytics / Audit Events
- `moderation_action_executed`: Ghi nhận sự kiện thực hiện hành động kỷ luật thành công kèm theo mã số cán bộ duyệt để đánh giá hiệu suất hành chính của trường.
- `report_dismissed`: Ghi nhận sự kiện bỏ qua đơn báo cáo vi phạm.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị lịch sử vi phạm (infraction history) của bị đơn rõ ràng để Cán bộ duyệt đơn có cơ sở ra quyết định tăng nặng hình thức phạt phù hợp đối với các trường hợp cố tình tái phạm liên tục.
- **Không được làm**: Cho phép hiển thị công khai nội dung báo cáo hoặc thông tin người gửi lên bất kỳ trang mạng xã hội nào ngoài hệ thống quản lý chính thức của trường.

## 18. Acceptance Criteria
- Danh sách đơn chờ và thông tin chi tiết tải chính xác dữ liệu từ cơ sở dữ liệu.
- Tính năng phê duyệt kỷ luật hoạt động ổn định và cập nhật đúng trạng thái tài khoản.
- Cơ chế "Hoàn tác" hoạt động trơn tru, không gây gián đoạn hay trùng lặp dữ liệu API.

## 19. QA / UAT Checklist
- [ ] Kiểm tra các thẻ đơn hàng đợi đổi màu sắc chính xác theo mức độ nghiêm trọng.
- [ ] Xác minh tệp ảnh minh chứng tải lên thành công và hiển thị ảnh xem trước sắc nét.
- [ ] Thử nghiệm xử lý kỷ luật và đảm bảo hệ thống gửi đúng thư thông báo về tài khoản bị đơn.
- [ ] Đảm bảo chỉ cán bộ có quyền mới mở được trang quản trị này.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình Livewire Component kết hợp cùng Alpine.js để quản lý trạng thái của hàng đợi báo cáo nhằm đạt hiệu năng giao diện phản hồi nhanh nhất.
- Thiết kế hệ thống tự động khóa tài khoản tạm thời (Auto-ban execution) dựa trên thời hạn khóa tài khoản đã chọn để đảm bảo tính nghiêm túc của quy trình kỷ luật.
---
