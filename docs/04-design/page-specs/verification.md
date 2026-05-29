---
title: "Academic Verification Submission Page"
module: "04-design/page-specs"
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

# Trang Gửi Yêu Cầu Xác Thực Học Thuật (Academic Verification Submission)

## 1. Purpose
Trang Gửi Yêu Cầu Xác Thực Học Thuật là cổng tiếp nhận các thông tin và tài liệu minh chứng chính quy (Thẻ sinh viên, Bằng tốt nghiệp, Bảng điểm chính thức) từ phía người dùng để Phòng Công tác Sinh viên trường đối chiếu, phê duyệt và cấp phát Huy hiệu xác minh chính danh (Verified Badge) trên hệ thống UEConnect.

## 2. Product Context
Để đảm bảo môi trường UEConnect là một khuôn viên số tuyệt đối tin cậy, nghiêm túc và lành mạnh, quy trình xác thực học thuật giúp ngăn chặn triệt để vấn đề giả danh sinh viên HCMUE, tạo cơ sở pháp lý vững chắc cho các chương trình Mentorship hướng nghiệp đồng môn chất lượng cao.

## 3. User Goals
- Gửi thông tin đăng ký xác thực danh tính học thuật nhanh chóng, rõ ràng.
- Tải lên hình ảnh minh chứng chính quy sắc nét (Thẻ sinh viên còn hạn, Bằng tốt nghiệp hoặc chứng nhận tốt nghiệp tạm thời).
- Theo dõi tiến trình thẩm định đơn xác thực trực quan (Đang chờ duyệt, Đang đối chiếu Portal, Đã duyệt, Bị từ chối).
- Nhận phản hồi hướng dẫn chỉnh sửa bổ sung minh chứng nếu đơn bị từ chối phê duyệt.

## 4. Primary Users
- **Sinh viên chưa xác thực**: Cần xác thực danh tính để tham gia hoạt động các Câu lạc bộ học thuật kín và gửi đơn xin Mentor.
- **Cựu sinh viên**: Cần xác thực lý lịch cựu học viên để kích hoạt tính năng đăng ký làm Mentor hướng nghiệp.

## 5. Entry Points
- Nhấp chọn liên kết **Xác thực tài khoản** (Get Verified) trên Banner thông báo đầu trang cá nhân chưa xác thực.
- Chọn danh mục **Cài đặt** -> Chọn mục **Xác thực học đường** (Verification).

## 6. Layout Strategy
Thiết kế trang trang nghiêm, sạch sẽ, bố cục từng phần rõ rệt tạo cảm giác an tâm và chuyên nghiệp.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 640px).
- Phần đầu là dòng thông điệp giới thiệu lợi ích của Huy hiệu xác thực kèm biểu tượng Huy hiệu lớn vàng kim rực rỡ.
- Phía dưới là các khối biểu mẫu điền thông tin và khung tải tài liệu xếp dọc bo tròn góc tinh tế.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ dàng nhấp chọn.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình mượt mà.
- Khung tải tệp tin minh chứng (File Upload zone) được thiết kế lớn, tích hợp nút chụp ảnh trực tiếp từ camera di động sắc nét (`touch-target 48px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Giới thiệu lợi ích (Benefits Header Block)**:
  - Thông điệp truyền cảm hứng: `"Xác thực tài khoản để nhận Huy hiệu xanh chính danh trường và mở khóa toàn bộ tính năng kết nối cố vấn."`
- **Thông tin khai báo học thuật (Academic Declaration Form)**:
  - Chọn Khoa đào tạo, Chuyên ngành học cụ thể, Niên khóa (Hộp chọn thả xuống).
  - Nhập Mã số sinh viên / Mã số học viên (Ô nhập liệu bắt buộc).
- **Tải tệp minh chứng (Documents Upload Area)**:
  - Tải lên ảnh mặt trước + mặt sau Thẻ sinh viên hoặc Bằng tốt nghiệp (Drag-and-Drop area).
- **Trạng thái phê duyệt (Pipeline Progress Tracking)**:
  - Hiển thị tiến trình xét duyệt đơn hiện tại: Ngày gửi đơn, Trạng thái (Pending / Under Review / Approved / Rejected kèm lý do từ chối rõ ràng).

## 8. Core Components
- **Drag-and-Drop File Upload Dropzone**: Khung tải tệp tin minh chứng nét đứt màu xanh nhạt thương hiệu, tích hợp thanh tiến trình tải lên (Progress Bar) thời gian thực.
- **Verification Pipeline Stepper**: Thanh hiển thị tiến độ xét duyệt đơn trực quan đổi màu sắc theo mốc thời gian thực tế.
- **Academic Selection Fields**: Các hộp chọn ngành học, niên khóa bo tròn góc tinh tế, hỗ trợ tìm kiếm nhanh gõ từ khóa.
- **Submit Claim Button**: Nút "Gửi yêu cầu xác thực" màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`) hoặc vô hiệu hóa mờ nếu chưa điền đủ thông tin.

## 9. States
### 9.1 Loading
- Khi bấm gửi đơn, nút hiển thị spinner xoay tròn nhỏ và toàn bộ các trường nhập liệu bị khóa mờ để tránh người dùng sửa đổi thông tin trong lúc đang truyền dữ liệu lên máy chủ.

### 9.2 Empty
- Khi người dùng chưa tải lên tệp tin minh chứng nào:
  - Nút gửi đơn bị vô hiệu hóa mờ xám. Dòng cảnh báo màu đỏ mỏng dưới khung upload: `"Vui lòng tải lên ít nhất 1 hình ảnh minh chứng chính quy."`

### 9.3 Error
- **Lỗi dung lượng tệp quá lớn**: Tải ảnh vượt quá 5MB hoặc sai định dạng tệp (PDF, JPG, PNG):
  - UI Copy: `"Định dạng tệp không được hỗ trợ hoặc dung lượng vượt quá giới hạn (Tối đa 5MB)."`
- **Lỗi trùng lặp đơn**: Mã số sinh viên đã được sử dụng để xác thực cho một tài khoản khác trên hệ thống:
  - UI Copy dưới input: `"Mã số sinh viên này đã được liên kết với một tài khoản khác. Vui lòng liên hệ hỗ trợ kỹ thuật."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Khóa mờ tính năng tải tệp và gửi đơn.

### 9.5 Permission Restricted
- Tài khoản đã được phê duyệt xác thực thành công trước đó (Huy hiệu xanh đã hoạt động):
  - Ẩn toàn bộ biểu mẫu điền đơn.
  - Hiển thị màn hình thông báo Success Board lớn: `"Tài khoản của bạn đã được xác thực thành công!"` kèm biểu tượng Huy hiệu xanh lá lấp lánh sang trọng.

### 9.6 Success / Completed
- Gửi đơn đăng ký xác thực thành công:
  - Giao diện tự động chuyển trạng thái sang màn hình theo dõi tiến trình phê duyệt (Pipeline Progress Tracking).
  - Hiện Toast thông báo xanh dịu: `"Đơn đăng ký xác thực đã được gửi lên Ban kiểm duyệt thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Rê chuột qua Khung tải tệp (Upload Dropzone) sẽ làm đổi màu viền sang xanh dương đậm rực rỡ và đẩy nhẹ biểu tượng đám mây tải lên lên cao 3px để tạo cảm giác cơ học sinh động.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các ô nhập liệu và khung tải tệp khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn khung tải tệp trên di động sẽ mở ra thực đơn tùy chọn nguồn ảnh nhanh dạng BottomSheet (Chụp ảnh từ Camera, Chọn ảnh từ Thư viện tệp) cực kỳ tiện dụng.

### 10.4 Optimistic UI
- Khi tệp tin được kéo thả vào khung, lập tức hiển thị hình ảnh thu nhỏ xem trước (Thumbnail preview) và thanh tiến trình tải lên giả lập 100% ngay lập tức tại Client trước khi máy chủ hoàn tất lưu trữ để tạo cảm giác tốc độ phản hồi siêu nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hộp chọn ngành học trên di động mở ra một BottomSheet danh sách cuộn dọc tích hợp thanh tìm kiếm thông minh giúp sinh viên dễ dàng chọn đúng ngành học của mình.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động gửi đơn xác thực để bảo vệ tính nhất quán của quy trình kiểm duyệt hành chính trường.

### 10.7 Motion
- Hoạt ảnh chuyển màu mượt mà của thanh tiến độ xét duyệt phê duyệt đơn và hiệu ứng co giãn ảnh xem trước diễn ra trong vòng 200ms trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ô nhập liệu và nút tải tệp đều đi kèm nhãn mô tả chi tiết cho các trình đọc màn hình: `aria-label="Tải lên hình ảnh mặt trước thẻ sinh viên"`.
- Hỗ trợ phím Escape để thoát nhanh khỏi danh sách lựa chọn ngành học.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Khung tải tệp minh chứng mở rộng tối đa diện tích, tích hợp nút chụp ảnh trực tiếp lớn giúp sinh viên dễ thao tác bằng ngón tay cái, tránh bị tràn chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `user_id` (integer, primary)
  - `faculty_id` (integer, required)
  - `major_name` (string, required)
  - `graduation_year` (integer, required)
  - `student_id_code` (string, required)
  - `proof_document_file` (file attachment, required)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `submitVerificationClaim(claimData)`
  - `uploadTemporaryProofFile(file)`
  - `fetchCurrentClaimStatus()`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin minh chứng tối đa: Toàn bộ hình ảnh thẻ sinh viên, bằng tốt nghiệp tải lên hệ thống được mã hóa lưu trữ an toàn và chỉ có Phòng Công tác Sinh viên (Admin) mới có quyền truy cập xem tệp tin để đối chiếu phê duyệt đơn. Tuyệt đối không hiển thị công khai các hình ảnh này lên trang cá nhân hay bất kỳ kênh truyền thông nào.

## 16. Analytics / Audit Events
- `verification_claim_submitted`: Ghi nhận sự kiện gửi đơn xác thực học thuật thành công kèm mốc thời gian để đánh giá tốc độ giải quyết hành chính của trường.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị lý do từ chối rõ ràng và đề xuất sinh viên chuyển hướng sang các Mentor khác có cùng chuyên môn đang còn nhiều lịch trống để tăng cơ hội kết nối học tập thành công.
- **Không được làm**: Cho phép gửi đơn xác thực mới khi đơn trước đó vẫn đang ở trạng thái `"Đang chờ duyệt"`.

## 18. Acceptance Criteria
- Biểu mẫu điền đơn hoạt động ổn định, lưu chính xác tất cả thông tin đăng ký vào cơ sở dữ liệu.
- Tính năng tải tệp minh chứng hoạt động chuẩn xác, kiểm tra đúng định dạng tệp và dung lượng tệp tin.
- Hệ thống gửi thông báo đẩy (push notifications) tức thời về tài khoản của sinh viên ngay khi Phòng Công tác Sinh viên bấm phê duyệt hoặc từ chối đơn.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng tải tệp tin minh chứng thành công và hiển thị đúng ảnh nhỏ xem trước.
- [ ] Xác minh tính năng gửi đơn đăng ký hoạt động hoàn hảo, lưu đúng dữ liệu vào bảng `verifications` cơ sở dữ liệu.
- [ ] Thử nghiệm gửi đơn khi thông tin học thuật bị thiếu và đảm bảo hệ thống chặn chính xác, vô hiệu hóa nút gửi đơn.
- [ ] Đảm bảo giao diện chuyển đổi trạng thái phê duyệt (Pending/Under Review) hiển thị rõ ràng chính xác.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu trữ tệp tin an toàn (Secure S3 bucket storage) tích hợp trong Laravel để lưu tệp minh chứng mã hóa, tự động tạo đường dẫn tạm thời (Temporary URL) có thời hạn 5 phút khi Admin trường truy cập thẩm định đơn nhằm ngăn chặn tuyệt đối rò rỉ dữ liệu.
- Thiết kế hệ thống thông báo đẩy thông minh sử dụng Firebase Cloud Messaging (FCM) kết hợp WebSockets để tối ưu hóa thời lượng pin trên thiết bị di động của sinh viên khi nhận thông tin duyệt đơn.
---
