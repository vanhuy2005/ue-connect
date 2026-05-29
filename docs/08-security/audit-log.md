# Audit Log Specification

## 1. Overview
Hệ thống UEConnect thực hiện ghi chép lại toàn bộ các thao tác nhạy cảm liên quan đến tài khoản người dùng, quy trình phê duyệt định danh, hoạt động tương tác mạng xã hội nâng cao, và các hoạt động quản trị hệ thống. Đây là cơ sở dữ liệu bất biến (append-only) phục vụ mục đích giám sát hoạt động, bảo vệ an ninh thông tin, phát hiện lạm dụng và tuân thủ chính sách bảo mật dữ liệu.

---

## 2. Audit Principles
Mọi thiết kế của hệ thống Audit Log tại UEConnect đều dựa trên các nguyên tắc bất biến:
1. **Append-Only (Chỉ thêm, không xóa sửa)**: Bản ghi nhật ký hệ thống sau khi đã tạo thì tuyệt đối không được phép chỉnh sửa, ghi đè hoặc xóa bỏ bởi bất kỳ ai, kể cả tài khoản Admin cấp cao nhất.
2. **Completeness (Tính toàn vẹn)**: Bản ghi nhật ký phải chứa đầy đủ thông tin để tái dựng lại bối cảnh hành động, bao gồm: Ai làm (Actor), Làm cái gì (Action), Làm trên đối tượng nào (Target), Trong ngữ cảnh nào (Context) và Kết quả ra sao.
3. **Privacy by Design (Bảo mật thông tin riêng tư)**: Tuyệt đối không lưu trữ thông tin nhận dạng cá nhân nhạy cảm (PII thô), nội dung tin nhắn riêng tư hoặc mật khẩu trong phần Metadata của Audit Log.
4. **Structured Logging (Ghi log cấu trúc)**: Sử dụng định dạng dữ liệu chuẩn (như JSON) cho phần Metadata để tạo điều kiện thuận lợi cho việc truy vấn, lọc dữ liệu và hiển thị trên giao diện quản trị Admin.

---

## 3. Audit Log Data Model
Bảng `audit_logs` trong cơ sở dữ liệu được thiết kế đồng nhất theo cấu trúc sau:

```txt
id: bigint (hoặc ulid) - Khóa chính tự tăng hoặc ID phân tán bất biến
actor_type: string - Loại tác nhân thực hiện hành động ('user', 'admin', 'moderator', 'system', 'job')
actor_id: bigint (nullable) - ID của tác nhân thực hiện (null nếu actor_type là 'system' hoặc 'job')
action: string - Khóa hành động duy nhất (ví dụ: 'post.created')
target_type: string - Loại đối tượng chịu tác động (ví dụ: 'Post', 'User', 'VerificationRequest')
target_id: bigint (nullable) - ID của đối tượng chịu tác động
context_type: string (nullable) - Loại ngữ cảnh liên quan (ví dụ: 'Comment', 'VerificationEvidence')
context_id: bigint (nullable) - ID của ngữ cảnh liên quan
ip_address: string - Địa chỉ IP của client thực hiện hành động
user_agent: string - User Agent của trình duyệt/thiết bị client
metadata_json: json (nullable) - Lưu trữ các dữ liệu cấu trúc bổ sung (mức khuyến nghị, điểm số tin cậy, lý do, thay đổi trạng thái trước/sau)
created_at: timestamp - Thời điểm ghi nhận hành động (bất biến)
```

---

## 4. Actor / Target / Context Model
Mô hình định danh mối quan hệ trong Audit Log:
- **Actor (Tác nhân)**: Xác định rõ thực thể đứng sau hành động. Có thể là một người dùng cụ thể (`user_id`), một kiểm duyệt viên, hoặc chính hệ thống (`system`) thực hiện các tiến trình tự động.
- **Target (Đối tượng mục tiêu)**: Thực thể chính chịu tác động trực tiếp từ hành động đó. Ví dụ: khi Admin phê duyệt yêu cầu định danh, Target là `VerificationRequest`.
- **Context (Ngữ cảnh bổ sung)**: Giúp làm rõ hơn luồng hành vi. Ví dụ: khi một Job tự động phân tích AI hoàn thành, Target là `VerificationRequest` nhưng Context liên quan trực tiếp là minh chứng thẻ sinh viên `VerificationEvidence`.

---

## 5. Metadata Redaction Rules
Để bảo vệ quyền riêng tư của người dùng, hệ thống áp dụng các quy tắc che giấu/redact nghiêm ngặt sau đối với trường `metadata_json`:
- **Nghiêm cấm lưu trữ**: 
  - File hình ảnh thô hoặc chuỗi mã hóa hình ảnh thô (Base64).
  - Nội dung thô của văn bản trích xuất OCR (trừ khi biến `AI_STORE_RAW_OCR_TEXT=true` được bật cho môi trường phát triển và kiểm thử).
  - Nội dung đầy đủ của tin nhắn cá nhân (`message body`).
  - Đường dẫn đầy đủ của file thẻ sinh viên trên đĩa cứng.
  - Các khóa bí mật, token truy cập, thông tin đăng nhập hoặc mật khẩu.
- **Cho phép lưu trữ cấu trúc**:
  - Mã bài viết (`post_id`), mã bình luận (`comment_id`), mã cuộc trò chuyện (`conversation_id`).
  - Điểm tin cậy AI (`confidence_score`), danh sách rủi ro (`risk_flags`), đề xuất kiểm duyệt (`recommendation`).
  - Trạng thái cũ và trạng thái mới (ví dụ: `old_status: 'pending'`, `new_status: 'approved'`).
  - Lý do từ chối hoặc lý do ẩn bài viết do Admin/Moderator nhập.

---

## 6. Verification Events
Các sự kiện ghi nhận trong quy trình phê duyệt định danh danh tính của người dùng:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `verification.submitted` | User | Người dùng nộp mới hồ sơ xác minh định danh học tập |
| `verification.start_review` | Admin | Bắt đầu mở xem chi tiết hồ sơ định danh của người dùng |
| `verification.approve` | Admin | Chấp thuận yêu cầu định danh của người dùng, gán Role chính thức |
| `verification.reject` | Admin | Từ chối yêu cầu định danh kèm lý do từ chối cụ thể |
| `verification.need_more_information` | Admin | Yêu cầu người dùng bổ sung thêm tài liệu minh chứng |
| `verification.mark_conflict` | Admin | Đánh dấu hồ sơ bị trùng lặp/xung đột mã sinh viên với tài khoản khác |
| `verification.suspend_suspicious` | Admin | Đình chỉ tài khoản do phát hiện dấu hiệu giả mạo danh tính |
| `verification.evidence_viewed` | Admin | Ghi nhận mỗi lần Admin click xem/preview ảnh minh chứng thẻ sinh viên |
| `verification.evidence_downloaded` | Admin | Ghi nhận hoạt động tải xuống file minh chứng (chỉ cho phép khi có quyền đặc biệt) |

---

## 7. AI Verification Events
Các sự kiện xử lý tự động bởi mô-đun phân tích trí tuệ nhân tạo (AI):

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `verification.ai_analysis_completed` | System (Job) | Tiến trình AI hoàn tất phân tích OCR và so khớp thông tin thẻ |
| `verification.ai_analysis_failed` | System (Job) | Tiến trình AI phân tích thất bại do ảnh mờ, lỗi kết nối hoặc định dạng sai |

*Chi tiết Metadata phân tích AI hoàn thành bao gồm:* Mức độ khuyến nghị (`recommendation`), điểm số tin cậy (`confidence_score`), và danh sách cảnh báo rủi ro (`risk_flags`).

---

## 8. Authentication / Account Events
Theo dõi các sự kiện bảo mật đăng nhập và thay đổi trạng thái tài khoản cốt lõi:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `auth.login` | User | Đăng nhập thành công vào ứng dụng |
| `auth.logout` | User | Đăng xuất khỏi ứng dụng |
| `auth.microsoft_login_success` | User | Đăng nhập thành công bằng liên kết Microsoft Institutional OAuth |
| `auth.microsoft_login_failed` | User | Đăng nhập bằng Microsoft OAuth thất bại do lỗi tài khoản hoặc domain cấm |
| `account.status_changed` | Admin | Thủ công thay đổi trạng thái tài khoản người dùng |
| `account.suspended` | Admin | Đình chỉ tạm thời hoạt động của tài khoản người dùng |
| `account.reactivated` | Admin | Khôi phục hoạt động bình thường cho tài khoản bị khóa trước đó |

---

## 9. RBAC / Admin Events
Nhật ký gán quyền và các hành động hành chính đặc quyền:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `rbac.role_assigned` | Super Admin | Gán vai trò mới cho tài khoản người dùng |
| `rbac.role_removed` | Super Admin | Thu hồi vai trò hiện tại của tài khoản người dùng |
| `rbac.permission_granted` | Super Admin | Cấp quyền đặc thù trực tiếp cho một tài khoản |
| `rbac.permission_revoked` | Super Admin | Thu hồi quyền đặc thù của tài khoản |
| `admin.user_viewed` | Admin | Admin mở xem trang chi tiết thông tin người dùng trong trang quản trị |
| `admin.profile_updated` | Admin | Admin thực hiện cập nhật thông tin profile của người dùng khác |

---

## 10. Social Feed Events
Ghi nhận các hoạt động tương tác bài viết trên feed:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `post.created` | User | Tạo mới bài đăng trên Home Feed |
| `post.updated` | User | Cập nhật nội dung bài đăng của bản thân |
| `post.deleted_by_owner` | User | Người dùng tự chủ động xóa bài đăng của mình |
| `post.hidden_by_user` | User | Tự ẩn bài đăng của mình khỏi bảng tin chung |
| `post.restored_from_hidden` | User | Khôi phục bài đăng đã ẩn trước đó về trạng thái hiển thị bình thường |
| `post.hidden_by_moderator` | Moderator | Kiểm duyệt viên ẩn bài đăng vi phạm quy chuẩn cộng đồng |
| `post.removed_by_moderator` | Moderator | Kiểm duyệt viên xóa vĩnh viễn bài đăng vi phạm nghiêm trọng |
| `post.shared_to_message` | User | Chia sẻ liên kết bài đăng vào tin nhắn 1-1 |
| `post.visibility_changed` | User | Thay đổi chế độ hiển thị bài đăng (ví dụ: chuyển sang Connections-only) |

---

## 11. Comment Events
Nhật ký bình luận thảo luận:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `comment.created` | User | Tạo bình luận mới dưới bài viết |
| `comment.updated` | User | Sửa bình luận của bản thân |
| `comment.deleted_by_owner` | User | Tự xóa bình luận của mình |
| `comment.hidden_by_moderator` | Moderator | Kiểm duyệt viên ẩn bình luận vi phạm |
| `comment.reply_created` | User | Tạo bình luận phản hồi (reply) cho bình luận khác |

---

## 12. Report / Moderation Events
Xử lý các báo cáo vi phạm nội dung và tác vụ kiểm duyệt:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `report.created` | User | Gửi báo cáo vi phạm mới (bài đăng, bình luận) |
| `report.duplicate_blocked` | System | Hệ thống tự động chặn đứng lượt báo cáo trùng lặp cho cùng mục tiêu |
| `report.dismissed` | Moderator | Bác bỏ báo cáo vi phạm, đánh giá nội dung hợp lệ |
| `report.action_taken` | Moderator | Xử lý báo cáo bằng cách ẩn/xóa nội dung hoặc hạn chế tài khoản |
| `moderation.post_hidden` | Moderator | Thực thi ẩn bài đăng vi phạm |
| `moderation.comment_hidden` | Moderator | Thực thi ẩn bình luận vi phạm |
| `moderation.user_restricted` | Moderator | Tạm thời hạn chế quyền tạo nội dung của tài khoản vi phạm |

---

## 13. Connection Events
Nhật ký quản lý lời mời và trạng thái kết nối 1-1:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `connection.request_sent` | User | Gửi lời mời kết nối tới người dùng khác |
| `connection.request_accepted` | User | Chấp nhận lời mời kết nối (chuyển sang trạng thái connected) |
| `connection.request_declined` | User | Từ chối lời mời kết nối của đối phương |
| `connection.request_cancelled` | User | Hủy bỏ lời mời kết nối đã gửi trước đó khi chưa được duyệt |
| `connection.removed` | User | Đơn phương hủy kết nối bạn bè hiện tại |
| `user.blocked` | User | Nhấn chặn tài khoản người dùng khác |
| `user.unblocked` | User | Nhấn bỏ chặn tài khoản người dùng khác |

---

## 14. Messaging Events
Các hành động xảy ra trong phân hệ tin nhắn trò chuyện cá nhân:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `conversation.created` | User | Khởi tạo phòng chat 1-1 mới sau khi kết nối thành công |
| `message.sent` | User | Gửi tin nhắn mới |
| `message.deleted_by_sender` | User | Thu hồi tin nhắn đã gửi |
| `message.blocked_due_to_policy`| System | Chặn đứng tin nhắn do phát hiện vi phạm chính sách hoặc chặn lẫn nhau |
| `conversation.marked_read` | User | Đánh dấu đã đọc toàn bộ tin nhắn trong hội thoại |
| `conversation.archived` | User | Lưu trữ cuộc trò chuyện để ẩn khỏi danh sách chính |
| `message.shared_post_sent` | User | Gửi tin nhắn chứa đối tượng bài viết chia sẻ |

---

## 15. Post Sharing Events
- Khi người dùng thực hiện chia sẻ một bài viết hợp lệ vào tin nhắn, hệ thống sẽ kích hoạt sự kiện `post.shared_to_message` và tạo ra bản ghi tin nhắn tương ứng kích hoạt sự kiện `message.shared_post_sent`.
- **Thông tin Metadata lưu trữ**: `shared_post_id`, `recipient_user_id`, `conversation_id`. Nội dung thô của bài đăng không được ghi lại tại đây để tránh dữ liệu trùng lặp và rò rỉ khi bài đăng gốc bị chỉnh sửa.

---

## 16. Privacy / Data Access Events
Các hoạt động truy xuất thông tin mang tính riêng tư cao:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `privacy.evidence_accessed` | Admin | Admin truy xuất xem trực tiếp ảnh thẻ sinh viên hoặc minh chứng nhạy cảm |
| `privacy.audit_log_viewed` | Admin | Xem nhật ký hoạt động hệ thống nâng cao qua trang quản trị |
| `privacy.export_requested` | User | Người dùng yêu cầu xuất dữ liệu cá nhân (Data Export) |
| `privacy.deletion_requested` | User | Người dùng yêu cầu xóa tài khoản và dữ liệu cá nhân liên quan |

---

## 17. Security / Abuse Events
Phát hiện và ngăn chặn các hành vi tấn công lạm dụng:

| Action Key | Triggered By | Description |
| :--- | :--- | :--- |
| `security.rate_limit_triggered`| System | Kích hoạt giới hạn tần suất hành động của người dùng ở mức độ cao |
| `security.abuse_detected` | System | Hệ thống tự động phát hiện hành vi tấn công/spam hoặc dò tìm API |
| `security.ip_lockout` | System | Chặn tạm thời địa chỉ IP do gửi quá nhiều request lỗi liên tục |

---

## 18. Retention Rules
Quy định thời gian lưu trữ nhật ký Audit Log bảo mật:
- **Thời gian lưu trữ tối thiểu**: Audit Log là nhật ký bảo mật cốt lõi, bắt buộc phải lưu trữ tối thiểu **365 ngày** (1 năm) ở cơ sở dữ liệu hoạt động.
- **Lưu trữ lưu trữ dài hạn (Archiving)**: Sau 365 ngày, dữ liệu nhật ký cũ sẽ được hệ thống nén lại và chuyển sang kho lưu trữ lạnh (Cold storage) để tiết kiệm tài nguyên nhưng vẫn đảm bảo khả năng khôi phục khi có yêu cầu điều tra pháp lý. Dữ liệu lạnh được giữ tối thiểu **3 năm** trước khi bị tự động hủy bỏ.

---

## 19. Admin Viewing Rules
- **Kiểm soát quyền truy cập**: Chỉ tài khoản có Role `super_admin` hoặc có Permission `view_audit_logs` mới được quyền truy cập giao diện xem Audit Log.
- **Tính năng Tìm kiếm & Bộ lọc**: Giao diện quản trị Audit Log hỗ trợ lọc nhanh theo: Tác nhân (`actor_id`), Hành động (`action`), Địa chỉ IP (`ip_address`), và Khoảng thời gian (`date_range`).
- **Ghi nhật ký xem nhật ký**: Bản thân mỗi lần Admin mở trang xem Audit Log sẽ tự động kích hoạt sự kiện `privacy.audit_log_viewed` để đảm bảo không ai có thể âm thầm theo dõi nhật ký hệ thống mà không bị phát hiện.

---

## 20. Testing Requirements
Các kịch bản kiểm thử bắt buộc đối với mô-đun Audit Log:
- **Test Auto-creation**: Đảm bảo khi một bài viết được tạo thành công, hệ thống tự động chèn 1 bản ghi tương ứng vào bảng `audit_logs` với đầy đủ thông tin IP và User Agent.
- **Test Redaction Verification**: Tạo bài viết chứa thông tin nhạy cảm, giả lập phân tích AI, sau đó truy vấn cơ sở dữ liệu `audit_logs` để xác thực rằng phần Metadata đã được redact hoàn hảo, không lọt bất kỳ chuỗi base64 hay MSSV thô nào.
- **Test Append-Only Constraint**: Viết kiểm thử giả lập hành động cập nhật (Update) hoặc xóa (Delete) trực tiếp trên Model `AuditLog` và chứng minh rằng database sẽ ném ngoại lệ ngăn chặn (hoặc ném lỗi ở tầng Model Policy).

---

## 21. Implementation Notes
- **Lắng nghe sự kiện (Laravel Eloquent Observers)**: Tận dụng các Eloquent Observers (ví dụ: `PostObserver`, `CommentObserver`, `ConnectionObserver`) để tự động bắt các hành động `created`, `updated`, `deleted` và tạo bản ghi Audit Log một cách tự động, sạch sẽ mà không làm bẩn logic trong Controller.
- **Hàng đợi ghi log (Queue Logging)**: Để không gây ảnh hưởng đến hiệu năng phản hồi của người dùng (Response latency), việc chèn bản ghi Audit Log vào database MySQL nên được xử lý bất đồng bộ thông qua Laravel Queue (`dispatch(new LogAuditEventJob(...))`).
- **Chạy Pint định kỳ**: Sử dụng `vendor/bin/pint --format agent` để đảm bảo code ghi log tuân thủ đúng quy chuẩn PHP của dự án.