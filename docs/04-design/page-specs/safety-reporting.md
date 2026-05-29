---
title: "Safety Reporting Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
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

# Trang Báo Cáo An Toàn & Tiêu Chuẩn Cộng Đồng (Safety Reporting)

## 1. Purpose
Trang Báo Cáo An Toàn & Tiêu Chuẩn Cộng Đồng (Safety Reporting) là giao diện gửi yêu cầu phản ánh hành vi vi phạm, quấy rối, spam, lừa đảo, giả mạo thông tin định danh học đường hoặc chia sẻ các nội dung độc hại vi phạm Quy tắc ứng xử văn minh của trường Đại học Sư phạm TP.HCM.

## 2. Product Context
Để xây dựng một không gian học đường lành mạnh, tin cậy tuyệt đối cho UEConnect, trang Báo cáo an toàn cung cấp công cụ tự vệ văn minh, kín đáo cho sinh viên. Hệ thống cam kết tiếp nhận, xử lý bảo mật mọi thông tin phản ánh và đưa về trung tâm kiểm duyệt tự động phê duyệt xử phạt.

## 3. User Goals
- Gửi báo cáo ẩn danh bảo mật về các bài viết, bình luận hoặc tài khoản vi phạm tiêu chuẩn cộng đồng trường.
- Lựa chọn nhanh các nhóm lý do vi phạm rõ ràng (Spam, Xúc phạm danh dự giảng viên/sinh viên, Nội dung nhạy cảm...).
- Đính kèm minh chứng hình ảnh (Ảnh chụp màn hình quấy rối/chat riêng tư) để tăng tính xác thực cho đơn báo cáo.
- Theo dõi tiến trình tiếp nhận đơn phản ánh và nhận thông báo kết quả xử lý từ Ban quản trị.

## 4. Primary Users
- **Mọi người dùng trên hệ thống**: Sinh viên, giảng viên bị quấy rối hoặc phát hiện nội dung độc hại muốn gửi đơn phản ánh bảo mật.

## 5. Entry Points
- Nhấp chọn **Báo cáo vi phạm** (Report Post/User) từ danh mục tùy chọn ba chấm trên bài viết, bình luận hoặc trang cá nhân của người dùng khác.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật các trường điền thông tin rõ ràng, trang nghiêm, tạo cảm giác an tâm và tin cậy cao.

### 6.1 Desktop Layout
- Hiển thị dưới dạng một hộp thoại lớn ở chính giữa màn hình (Modal Popup) đè lên nền mờ tối.
- Kích thước chiều rộng cố định: 520px.
- Chiều cao tự động tăng theo nội dung soạn thảo tối đa đến 580px.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ chạm bấm.

### 6.3 Mobile / PWA Layout
- Mở rộng hiển thị toàn màn hình (Full-screen Overlay) để tay dễ thao tác và bàn phím ảo hiển thị thoải mái nhất.
- Nút "Gửi báo cáo" (Submit Report) nằm ở góc phải trên cùng thanh tiêu đề. Nút "Hủy" (Cancel) ở góc trái trên cùng để tay dễ chạm bấm.
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thông tin nguồn vi phạm (Target Content Information)**:
  - Khung hiển thị tóm tắt nội dung bị báo cáo (Tên tác giả, đoạn văn bản trích dẫn ngắn).
- **Phân loại lý do vi phạm (Violation Reason Categories)**:
  - Danh sách nút chọn (Radio Buttons) các lý do: "Nội dung Spam / Tin rác", "Xúc phạm / Bắt nạt học đường", "Thông tin giả mạo định danh trường", "Nội dung nhạy cảm / Không phù hợp tác phong sư phạm", "Lý do khác".
- **Thư giải trình chi tiết (Additional Details)**:
  - Ô nhập liệu văn bản tự do để người dùng miêu tả rõ bối cảnh vi phạm.
- **Tải ảnh minh chứng (Proof Upload Area)**:
  - Khung tải lên ảnh chụp màn hình (Screenshot) đính kèm minh chứng vi phạm (Tối đa 2 ảnh).

## 8. Core Components
- **Violation Category Radio Group**: Nhóm nút lựa chọn lý do vi phạm bo tròn góc sắc nét, tự động đổi màu nền khi người dùng nhấp chọn.
- **Screenshot Drag-and-Drop Area**: Khung kéo thả hình ảnh minh chứng đính kèm mượt mà, hiển thị ảnh nhỏ xem trước (Thumbnail).
- **Submit Report Button**: Nút gửi đơn màu đỏ sẫm (`bg-red-700` hover `bg-red-800`) báo hiệu hành động nghiêm túc liên quan đến an toàn.
- **Report Success Board**: Màn hình hiển thị thông báo gửi đơn thành công mượt mà kèm biểu tượng khiên bảo vệ xanh lá.

## 9. States
### 9.1 Loading
- Khi bấm "Gửi báo cáo", nút chuyển sang trạng thái mờ, hiển thị vòng tròn xoay tải dữ liệu và vô hiệu hóa khả năng nhấp chọn để tránh gửi trùng lặp.

### 9.2 Empty
- Khi người dùng chưa chọn bất kỳ lý do vi phạm nào:
  - Nút "Gửi báo cáo" bị vô hiệu hóa (disabled) mờ xám.

### 9.3 Error
- **Lỗi tải tệp tin**: Tải ảnh minh chứng vượt quá dung lượng cho phép (Tối đa 5MB) hoặc sai định dạng tệp:
  - UI Copy dưới khung tải ảnh: `"Dung lượng hình ảnh vượt quá giới hạn (Tối đa 5MB). Vui lòng chọn ảnh khác."`
- **Lỗi gửi đơn**: Gửi đơn thất bại do rớt mạng:
  - UI Copy: `"Không thể gửi đơn báo cáo lúc này. Vui lòng kiểm tra kết nối mạng."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút "Gửi báo cáo" và khung tải ảnh minh chứng.

### 9.5 Permission Restricted
- Trường hợp người dùng cố tình gửi đơn báo cáo liên tục nhiều lần (Spam báo cáo) cho cùng một bài viết trong vòng 5 phút:
  - Hệ thống tự động chặn và hiển thị thông điệp: `"Bạn đã gửi báo cáo cho nội dung này trước đó. Đơn đang được Ban quản trị xem xét."`

### 9.6 Success / Completed
- Gửi báo cáo thành công:
  - Hộp thoại điền thông tin đóng lại. Mở ra màn hình thông báo Success Board mượt mà:
    - UI Copy: `"Đã gửi báo cáo thành công!"`
    - Mô tả UI Copy: `"Cảm ơn bạn đã chủ động cống hiến xây dựng cộng đồng UEConnect văn minh, lịch sự. Ban quản trị sẽ xác minh nội dung và xử lý trong vòng 24 giờ."`
    - Nút bấm `"Đóng"` đưa người dùng quay lại giao diện chính.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các nút chọn lý do vi phạm sẽ làm thay đổi nhẹ màu nền sang màu xám nhạt (`bg-gray-100`) để báo hiệu khả năng tương tác.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu đỏ sẫm bao quanh các nút chọn lý do và ô nhập liệu khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp chọn lý do vi phạm đổi trạng thái ngay lập tức mượt mà, nút chọn sáng lên kèm rung nhẹ phản hồi xúc giác trên thiết bị di động.

### 10.4 Optimistic UI
- Không áp dụng cho quy trình báo cáo an toàn vì tính chất bảo mật và yêu cầu xác thực nghiêm ngặt từ máy chủ cơ sở dữ liệu.

### 10.5 Menu / Sheet
- Giao diện điền báo cáo được thiết kế dạng BottomSheet chi tiết trên di động, trượt từ dưới lên chiếm 80% chiều cao màn hình giúp tay dễ dàng thao tác gõ phím.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động gửi báo cáo vi phạm để đảm bảo tính nghiêm túc của quy trình kỷ luật.

### 10.7 Motion
- Hiệu ứng trượt co giãn mượt mà của Modal khi mở rộng. Hoạt ảnh chuyển đổi hình ảnh xem trước trơn tru bằng CSS transition.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nút chọn lý do vi phạm phải đi kèm nhãn mô tả chi tiết cho các trình đọc màn hình.
- Hỗ trợ phím đóng nhanh `Escape` để thoát nhanh khỏi hộp thoại nếu người dùng muốn hủy bỏ hành động.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Trình chỉnh sửa mở rộng 100% diện tích chiều ngang và dọc màn hình để tay dễ thao tác và bàn phím ảo hiển thị thoải mái nhất, tránh bị tràn chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `target_type` (enum: `post`, `comment`, `user`)
  - `target_id` (integer)
  - `violation_category` (string, required)
  - `explanation` (text)
  - `proof_images` (array of files, max 2 images)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `initializeReport(targetType, targetId)`
  - `submitSafetyReport(targetType, targetId, category, explanation, tempProofs)`
  - `uploadReportProof(file)`

## 15. Authorization / Privacy Rules
- Bảo mật ẩn danh tuyệt đối: Danh tính của người gửi báo cáo hoàn toàn được giấu kín đối với người bị báo cáo. Chỉ có Ban quản trị trường (Admin, Moderator) mới có quyền truy cập xem thông tin người gửi trong hệ thống quản lý.

## 16. Analytics / Audit Events
- `safety_report_submitted`: Ghi nhận sự kiện gửi báo cáo vi phạm thành công cùng nhóm phân loại vi phạm tương ứng để đánh giá tình hình an toàn học đường.

## 17. Do / Don't
- **Nên làm**: Luôn cung cấp đầy đủ thông tin giải thích ngắn gọn dưới mỗi lý do vi phạm để sinh viên chọn đúng phân loại vi phạm, giúp Ban quản trị duyệt đơn nhanh hơn.
- **Không được làm**: Cho phép hiển thị công khai nội dung báo cáo hoặc thông tin người gửi lên bất kỳ kênh thảo luận hay trang cá nhân nào.

## 18. Acceptance Criteria
- Hộp thoại báo cáo hiển thị chính xác thông tin tóm tắt của đối tượng bị báo cáo.
- Quy trình chọn lý do, điền giải thích và tải ảnh minh chứng hoạt động trơn tru.
- Gửi đơn báo cáo thành công ghi đúng dữ liệu vào cơ sở dữ liệu và chuyển đơn về trung tâm kiểm duyệt tự động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra các nút chọn lý do vi phạm đổi trạng thái chính xác và đồng bộ.
- [ ] Xác minh tệp ảnh chụp màn hình tải lên thành công và hiển thị đúng ảnh nhỏ xem trước.
- [ ] Thử nghiệm gửi đơn báo cáo và đảm bảo hiển thị đúng màn hình thông báo gửi thành công (Success Board).
- [ ] Đảm bảo chỉ báo đang kết nối mạng hoạt động chính xác khi bấm gửi đơn.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình Livewire Component kết hợp cùng Alpine.js để quản lý trạng thái của biểu mẫu tải lên nhằm đạt hiệu năng giao diện phản hồi nhanh nhất.
- Thiết kế Model `SafetyReport` liên kết trực tiếp với bảng `Users` và `ModerationLogs` để Admin trường dễ dàng điều phối và xử lý kỷ luật tài khoản vi phạm chính xác nhất.
---