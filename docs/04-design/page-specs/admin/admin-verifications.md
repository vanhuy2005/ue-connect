---
title: "Admin Verifications Management Page"
module: "04-design/page-specs/admin"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/identity-verification.md"
related_design_docs:
  - "../04-design/verification-flows.md"
related_system_docs:
  - "../05-system-architecture/verification-service.md"
related_database_docs:
  - "../06-database/verifications-table.md"
related_api_docs:
  - "../07-api/verifications-api.md"
---

# Trang Phê Duyệt Xác Thực Học Thuật (Admin Verifications Management)

## 1. Purpose
Trang Phê Duyệt Xác Thực Học Thuật cung cấp cho Cán bộ Phòng Công tác Sinh viên HCMUE công cụ chuyên sâu để kiểm tra, đối chiếu lý lịch khai báo của sinh viên với cơ sở dữ liệu HCMUE Portal chính thức thông qua giao diện đối chiếu song song (Split Screen view), phê duyệt cấp phát Huy hiệu xác minh chính danh hoặc từ chối đơn kèm lý do giải trình chi tiết.

## 2. Product Context
Để xây dựng một không gian an toàn, tin cậy tuyệt đối cho UEConnect, quy trình thẩm định hồ sơ được thiết kế cực kỳ nghiêm túc, khoa học, giúp Cán bộ duyệt đơn nhanh chóng xử lý hàng trăm yêu cầu mỗi ngày mà không bị nhầm lẫn thông tin.

## 3. User Goals
- Xem danh sách toàn bộ các yêu cầu xác thực học thuật đang chờ xét duyệt (Pending Claims List).
- Sử dụng giao diện đối chiếu song song: Một bên hiển thị ảnh minh chứng thẻ sinh viên/bằng tốt nghiệp tải lên, một bên hiển thị kết quả truy vấn tự động từ cơ sở dữ liệu HCMUE Portal.
- Phê duyệt nhanh đơn đăng ký hợp lệ chỉ bằng một nhấp chuột.
- Từ chối đơn không hợp lệ (ảnh mờ, sai thông tin) và gửi phản hồi hướng dẫn sửa đổi nhanh cho sinh viên thông qua các biểu mẫu lý do soạn sẵn (Rejection templates).

## 4. Primary Users
- **Cán bộ Phòng Công tác Sinh viên**: Người phụ trách thẩm định, đối chiếu hồ sơ học thuật và phê duyệt cấp Huy hiệu xác minh.

## 5. Entry Points
- Nhấp chọn mục **Duyệt xác thực** (Verifications) trên thanh Sidebar điều hướng của Cổng quản trị Admin.

## 6. Layout Strategy
Thiết kế tập trung tối đa vào giao diện chia đôi màn hình đối chiếu song song (Split Screen Workspace Layout) mang lại hiệu quả so sánh trực quan cao nhất.

### 6.1 Desktop Layout
- Bố cục 2 phần linh hoạt:
  - Danh sách đơn chờ bên trái (30% chiều rộng): Danh sách dạng cột cuộn dọc chứa các thẻ yêu cầu rút gọn xếp chồng.
  - Không gian đối chiếu bên phải (70% chiều rộng): Chia đôi song song (Split Screen):
    - Cột trái đối chiếu (35%): Hiển thị ảnh minh chứng thẻ sinh viên phóng to có khả năng thu phóng (Zoom-in/out).
    - Cột phải đối chiếu (35%): Khung đối sánh dữ liệu Portal trường và biểu mẫu ra quyết định (Approve / Reject buttons).
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1366px (Tối ưu màn hình ngang văn phòng).

### 6.2 Tablet Layout
- Bố cục danh sách đơn chuyển thành dải băng ngang ở đầu trang.
- Khung đối chiếu song song chuyển thành giao diện tab cuộn dọc xếp chồng để hiển thị ảnh minh chứng rõ nét nhất.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tối giản.
- Khách hàng là Cán bộ quản trị ưu tiên duyệt đơn trên máy tính để bàn văn phòng. Giao diện Mobile đóng vai trò xem nhanh trạng thái phê duyệt và xử lý nhanh các đơn khẩn cấp qua hộp thoại trượt BottomSheet (`touch-target 44px`).

## 7. Information Architecture
- **Danh sách đơn chờ (Pending Requests List)**:
  - Thẻ yêu cầu rút gọn: Ảnh đại diện người dùng, Họ tên, Mã số sinh viên, Khoa, Ngày gửi đơn.
- **Không gian đối chiếu song song (Split-Screen Workspace)**:
  - Khối Minh chứng (Proof Panel): Ảnh tài liệu sắc nét đính kèm, hỗ trợ thanh trượt thu phóng và xoay ảnh (Rotate/Zoom tools).
  - Khối Portal trường (Portal Query Panel): Hiển thị dữ liệu đối chiếu khớp (Họ tên Portal, Mã số sinh viên, Ngành học Portal, Trạng thái học tập).
  - Khối Hành động (Decision Action Panel): Nút "Phê duyệt" (Xanh lá), Nút "Từ chối" (Đỏ) và hộp thoại viết lý do.

## 8. Core Components
- **Split-Screen Comparer**: Giao diện so sánh đối chiếu song song sắc nét giữa ảnh minh chứng gốc và dữ liệu số chính quy trường.
- **Image Rotator & Zoomer**: Bộ công cụ nhỏ hỗ trợ xoay ảnh 90 độ, thu phóng chi tiết để kiểm tra các con dấu đỏ hoặc chữ mờ trên thẻ sinh viên.
- **Rejection Reason Form**: Biểu mẫu viết thư từ chối tích hợp các lý do mẫu soạn sẵn (Ví dụ: `"Hình ảnh minh chứng bị mờ, không rõ nét"`, `"Mã số sinh viên không trùng khớp với họ tên"`).
- **Portal Sync Status Badge**: Nhãn màu sắc hiển thị độ trùng khớp thông tin (Xanh lá: Khớp 100%, Đỏ: Không tìm thấy mã số sinh viên trên Portal).

## 9. States
### 9.1 Loading
- Khi bấm "Phê duyệt", nút hiển thị spinner nhỏ xoay tròn và vô hiệu hóa hoàn toàn hai nút hành động để tránh việc bấm đúp tạo yêu cầu lưu trùng lặp lên cơ sở dữ liệu.

### 9.2 Empty
- Khi không còn đơn đăng ký xác thực nào chờ duyệt trong danh sách:
  - UI Copy ở giữa không gian đối chiếu:
    - UI Copy: `"Đã hoàn thành duyệt tất cả các đơn xác thực!"`
    - Mô tả UI Copy: `"Hộp thư đơn chờ đang trống. Tuyệt vời!"` kèm biểu tượng cốc cà phê thư giãn dễ chịu.

### 9.3 Error
- Lỗi kết nối API liên thông cổng Portal trường học:
  - Khối Portal hiển thị thông báo lỗi màu đỏ cam: `"Không thể đồng bộ dữ liệu Portal trường lúc này. Vui lòng nhấp để thử kết nối lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút Phê duyệt và Từ chối.

### 9.5 Permission Restricted
- Chỉ dành cho tài khoản cán bộ Phòng Công tác Sinh viên có quyền duyệt xác thực (`moderator` / `admin` role). Các tài khoản khác bị chặn tuyệt đối qua Middleware bảo mật.

### 9.6 Success / Completed
- Duyệt đơn thành công:
  - Đơn đã duyệt biến mất khỏi danh sách chờ bên trái mượt mà.
  - Hiện Toast thông báo xanh dịu ở góc màn hình: `"Đã phê duyệt xác thực tài khoản của sinh viên [Họ tên] thành công!"`
  - Tài khoản sinh viên được duyệt lập tức nhận Huy hiệu xác minh vàng kim hiển thị công khai.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ đơn chờ bên trái sẽ làm thẻ đổi màu nền sang xám kem dịu (`bg-gray-100`) để báo hiệu trạng thái sẵn sàng chọn duyệt.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút ra quyết định (Approve, Reject) khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn nút "Từ chối" sẽ lập tức kích hoạt hộp thoại phụ trượt lên chứa các mẫu lý do viết sẵn để Cán bộ duyệt đơn nhanh chóng lựa chọn trong vòng dưới 100ms.

### 10.4 Optimistic UI
- Khi bấm duyệt đơn, thẻ đơn chờ lập tức biến mất khỏi danh sách hiển thị bên trái tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hộp thoại viết lý do từ chối trên di động mở ra một BottomSheet mượt mà chứa các nút bấm lý do mẫu dễ chạm bấm nhanh chóng.

### 10.6 Toast / Undo
- Duyệt đơn thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh đơn về trạng thái chờ trong vòng 5 giây nếu cán bộ lỡ tay bấm nhầm nút duyệt.

### 10.7 Motion
- Hiệu ứng biến mất (Fade-out & Slide-up) của thẻ đơn chờ khi được duyệt diễn ra trong vòng 200ms cực kỳ mượt mà. Hoạt ảnh xoay ảnh minh chứng trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh minh chứng phải có mô tả thay thế chi tiết cho trình đọc màn hình: `alt="Hình ảnh minh chứng thẻ sinh viên tải lên của [Họ tên]"`
- Hỗ trợ đầy đủ phím đóng nhanh `Escape` để thoát nhanh khỏi hộp thoại từ chối.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị dữ liệu đối chiếu Portal để tránh bị tràn màn hình, ưu tiên hiển thị ảnh minh chứng toàn màn hình và hàng nút hành động ghim cố định ở đáy.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `pending_claims` (array of objects: `claim_id`, `name`, `student_id`, `avatar_url`, `created_at`)
  - `active_claim_details` (object: `claim_id`, `name`, `student_id`, `faculty`, `major`, `graduation_year`, `proof_url`, `portal_sync_data`)

## 14. API / Action Requirements
- Gọi Livewire / Admin Action:
  - `fetchPendingClaimsList()`
  - `loadClaimDetails(claimId)`
  - `approveVerificationClaim(claimId)`
  - `rejectVerificationClaim(claimId, reasonCode, customReasonText)`
  - `undoClaimDecision(claimId)`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin nghiêm ngặt: Toàn bộ ảnh minh chứng học thuật chỉ hiển thị cho Cán bộ Phòng Công tác Sinh viên phụ trách duyệt đơn. Mọi hành vi cố tình xuất hoặc tải ảnh minh chứng ra bên ngoài mạng lưới trường đều bị hệ thống chặn đứng và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `verification_approved`: Ghi nhận sự kiện phê duyệt xác thực thành công kèm theo mã số cán bộ duyệt để đánh giá hiệu suất hành chính của trường.
- `verification_rejected`: Ghi nhận sự kiện từ chối đơn xác thực kèm lý do từ chối để tối ưu hóa biểu mẫu Onboarding sinh viên.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị lý do từ chối rõ ràng và gợi ý các chỉnh sửa cụ thể để sinh viên dễ dàng bổ sung minh chứng chính xác ở lần đăng ký sau.
- **Không được làm**: Cho phép hiển thị công khai thông tin lý lịch cá nhân nhạy cảm của sinh viên lên bất kỳ trang mạng xã hội nào ngoài hệ thống quản lý chính thức của trường.

## 18. Acceptance Criteria
- Danh sách đơn chờ và thông tin chi tiết tải chính xác dữ liệu từ cơ sở dữ liệu.
- Tính năng đồng bộ đối chiếu Portal hoạt động ổn định và hiển thị đúng độ khớp thông tin.
- Cơ chế phê duyệt, từ chối và "Hoàn tác" hoạt động trơn tru, không gây gián đoạn hay trùng lặp dữ liệu API.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng thu phóng và xoay ảnh minh chứng hoạt động tốt, không giật lag giao diện.
- [ ] Xác minh tính năng "Hoàn tác" khôi phục đúng trạng thái đơn chờ trên danh sách bên trái.
- [ ] Thử nghiệm từ chối đơn với lý do mẫu và đảm bảo hệ thống gửi đúng thư thông báo về tài khoản sinh viên.
- [ ] Đảm bảo chỉ cán bộ có quyền mới mở được trang quản trị này.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu đệm (Caching) danh sách đơn chờ trong Redis để tối ưu tốc độ tải trang nhanh nhất dưới 100ms.
- Thiết kế hệ thống tự động kiểm duyệt tài liệu tải lên sử dụng công nghệ nhận diện chữ (OCR) đơn giản để phát hiện các tài liệu vi phạm quy tắc chính trị hoặc đạo đức học đường trường Đại học Sư phạm TP.HCM.
---
