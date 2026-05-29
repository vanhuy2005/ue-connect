# Data Privacy Specification

## 1. Overview
UEConnect cam kết bảo vệ thông tin định danh cá nhân (PII) và các minh chứng xác thực nhạy cảm của người dùng. Với việc triển khai xác thực tự động bằng AI ở phiên bản v2, tài liệu này quy định các nguyên tắc bảo mật và quyền riêng tư tuyệt đối đối với ảnh chụp thẻ sinh viên và quy trình xử lý dữ liệu.

## 2. Storage Guidelines
- **Ổ đĩa riêng tư (Private Storage)**: Tất cả hình ảnh chụp từ camera hoặc file tài liệu tải lên đều phải được lưu trữ trên disk `private` (không công khai qua thư mục `public`).
- **Đường dẫn bảo mật**: Lưu tại `verifications/{user_id}/captures/{uuid}.jpg`.
- **Phục vụ qua Controller kiểm soát**: File minh chứng chỉ được truy xuất bởi Quản trị viên có thẩm quyền thông qua route bảo mật `admin.verification.evidence` sau khi đã kiểm tra quyền `review_verification`.

## 3. Camera Capture Privacy
- **Không nhận diện khuôn mặt**: Hệ thống chỉ yêu cầu chụp trực tiếp mặt trước/sau của thẻ sinh viên để trích xuất văn bản thô. Không thực hiện nhận diện sinh trắc học hoặc phân tích khuôn mặt (Face Recognition).
- **Khung hướng dẫn**: Giao diện UI camera cung cấp khung chữ nhật hướng dẫn đặt thẻ vừa vặn để giảm thiểu việc lọt các thông tin thừa xung quanh.

## 4. Local AI & Data Isolation
- **Xử lý cục bộ mặc định**: Quy trình phân tích mặc định sử dụng OCR cục bộ (Tesseract) và Ollama chạy trực tiếp trên máy chủ.
- **Không lưu dữ liệu thô vào Log hệ thống**: Không lưu hình ảnh, chuỗi base64 hoặc văn bản OCR thô trực tiếp vào log files của Laravel để tránh rò rỉ thông tin qua logs.
- **Che dấu thông tin nhạy cảm**: Khi thực hiện log kết quả so khớp, các thông tin nhạy cảm (như Họ tên, Mã sinh viên) phải được che dấu hoặc ẩn đi một phần (redact).

## 5. Third-Party Fallback Guard
- **Tắt theo mặc định**: Các dịch vụ AI bên ngoài (Gemini Flash, OpenRouter Vision) bị **tắt hoàn toàn theo mặc định** (`AI_ALLOW_EXTERNAL_PROVIDER=false`).
- **Quyền riêng tư bên thứ ba**: Chỉ kích hoạt khi có sự cấu hình chủ động từ Quản trị viên và chỉ gửi ảnh thẻ để xử lý dưới dạng API được mã hóa HTTPS, không chia sẻ bất kỳ thông tin định danh nào khác của tài khoản người dùng.