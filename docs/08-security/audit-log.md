# Audit Log Specification

## 1. Overview
Hệ thống UEConnect thực hiện ghi chép lại toàn bộ các thao tác nhạy cảm liên quan đến tài khoản người dùng và quy trình phê duyệt định danh. Đây là cơ sở dữ liệu bất biến (append-only) phục vụ mục đích giám sát hoạt động và bảo mật.

## 2. New Action Keys
Hệ thống bổ sung khóa hành động tự động mới:
- **`verification.ai_analysis_completed`**: 
  - **Mô tả**: Được kích hoạt tự động ngay khi Job phân tích AI trong nền hoàn thành nhiệm vụ OCR và so khớp.
  - **Actor**: `system` (ActorId = null, ActorType = 'system').
  - **Target**: `VerificationRequest` (targetId = ID của yêu cầu).
  - **Context**: `VerificationEvidence` (contextId = ID của minh chứng).
  - **Metadata**: Lưu trữ kết quả phân tích gồm: mức khuyến nghị (`recommendation`), điểm số tin cậy (`confidence_score`), và danh sách cảnh báo rủi ro (`risk_flags`).

## 3. Auditable Verification Events
Dưới đây là danh sách các hành động định danh được ghi nhận:

| Action Key | Triggered By | Description |
|---|---|---|
| `verification.start_review` | Admin | Bắt đầu mở xem chi tiết hồ sơ định danh |
| `verification.approve` | Admin | Chấp thuận yêu cầu định danh của người dùng |
| `verification.reject` | Admin | Từ chối yêu cầu định danh kèm lý do |
| `verification.need_more_information` | Admin | Yêu cầu người dùng bổ sung tài liệu |
| `verification.mark_conflict` | Admin | Đánh dấu hồ sơ bị trùng lặp/xung đột |
| `verification.suspend_suspicious` | Admin | Đình chỉ tài khoản do phát hiện giả mạo |
| `verification.ai_analysis_completed` | System | Tiến trình AI hoàn tất OCR và so khớp |

## 4. Metadata Snapshot Guidelines
- **Snapshot trước và sau (Before & After)**: Khi Admin thay đổi trạng thái hồ sơ hoặc thông tin người dùng, hệ thống chụp lại trạng thái của request hoặc user dưới định dạng JSON để phục vụ việc đối chiếu khi có tranh chấp dữ liệu.
- **Không lưu dữ liệu nhạy cảm**: Snapshots ghi nhận thay đổi trạng thái tài khoản, vai trò và thông tin học tập của người dùng, loại bỏ hoặc mã hóa đường dẫn tập tin hoặc ảnh thô.