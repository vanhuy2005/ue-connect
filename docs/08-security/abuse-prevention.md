# Abuse Prevention Specification

## 1. Overview
Hệ thống UEConnect là mạng xã hội nội bộ dành riêng cho cộng đồng HCMUE. Để bảo vệ không gian thảo luận lành mạnh, ngăn ngừa hành vi gian lận danh tính, spam nội dung, quấy rối cá nhân và lạm dụng tính năng báo cáo vi phạm, tài liệu này quy định chi tiết chiến lược phòng chống lạm dụng (Abuse Prevention). Các biện pháp kỹ thuật được triển khai bao gồm giới hạn tần suất (Rate Limiting), kiểm soát trạng thái lòng tin (Trust Levels), phát hiện tín hiệu bất thường tự động và quy trình xử lý kiểm duyệt tối ưu.

---

## 2. Abuse Principles
Các nguyên lý nền tảng trong việc thiết lập hệ thống phòng chống lạm dụng tại UEConnect:
1. **Prevention at the Gate (Ngăn chặn từ cổng vào)**: Quy trình định danh danh tính sinh viên là tuyến phòng thủ đầu tiên và quan trọng nhất để triệt tiêu tài khoản giả mạo hàng loạt (bot/spam networks).
2. **Graceful Degradation (Suy giảm quyền hạn mềm dẻo)**: Khi phát hiện dấu hiệu lạm dụng, hệ thống ưu tiên hạn chế quyền hạn tạm thời (chuyển sang `restricted` state hoặc áp dụng cool-down) thay vì khóa tài khoản vĩnh viễn ngay lập tức, để giảm thiểu trường hợp chặn nhầm (false positives).
3. **No UI-Only Safeguards**: Toàn bộ cơ chế chặn, rate limit, và validation bắt buộc phải được xử lý tại Server-Side. Không bao giờ tin cậy vào Client-Side.
4. **Traceability (Tính truy vết)**: Mọi hành động kích hoạt rate limit hoặc tự động hạn chế tài khoản đều được ghi log để hỗ trợ khiếu nại và tối ưu hóa hệ thống.

---

## 3. Abuse Types
Hệ thống UEConnect chủ động phòng chống các hình thức lạm dụng sau:
- **Fake Identity (Gian lận danh tính)**: Sử dụng MSSV giả, thông tin cá nhân sai lệch để vượt qua xác minh.
- **Repeated Failed Verification (Cố tình gửi xác minh liên tục)**: Gửi hồ sơ định danh sai nhiều lần nhằm làm nghẽn hàng đợi duyệt của Admin hoặc dò tìm kẽ hở hệ thống.
- **Uploading Fake Evidence (Tải lên minh chứng giả mạo)**: Sử dụng ảnh thẻ photoshop, ảnh mạng hoặc tài liệu bị chỉnh sửa để gian lận.
- **Spam Posts (Spam bài viết)**: Đăng liên tục các bài viết rác, quảng cáo không đúng mục đích học tập/hướng nghiệp.
- **Spam Comments (Spam bình luận)**: Bình luận hàng loạt nội dung vô nghĩa, link rác hoặc spam icon.
- **Harassment (Quấy rối)**: Gửi tin nhắn quấy rối, xúc phạm cá nhân hoặc spam lời mời kết nối đến người khác.
- **Mass Connection Requests (Spam lời mời kết nối)**: Gửi hàng loạt lời mời kết nối cho những người không quen biết trong thời gian ngắn.
- **Message Spam (Spam tin nhắn)**: Gửi hàng loạt tin nhắn giống nhau cho nhiều người hoặc quấy rối trong phòng chat 1-1.
- **Share-post Spam (Spam chia sẻ bài viết)**: Liên tục chia sẻ một bài viết qua tin nhắn nhằm gây phiền nhiễu cho người nhận.
- **Report Abuse (Lạm dụng báo cáo)**: Cố tình báo cáo sai sự thật các bài đăng/bình luận của người khác do tư thù cá nhân.
- **Duplicate Reports (Báo cáo trùng lặp)**: Một người hoặc nhóm người liên tục gửi hàng loạt báo cáo cho cùng một mục tiêu trong thời gian ngắn.
- **Ban Evasion (Lách lệnh cấm)**: Tạo tài khoản mới bằng email phụ để hoạt động tiếp sau khi tài khoản chính bị ban.
- **Impersonation (Mạo danh)**: Đặt tên hiển thị hoặc sử dụng hình ảnh của người khác (giáo viên, cố vấn hoặc sinh viên nổi bật) để lừa đảo.
- **Posting Sensitive Personal Data (Lộ lọt thông tin nhạy cảm)**: Đăng tải số điện thoại, địa chỉ, ảnh minh chứng nhạy cảm của bản thân hoặc người khác lên feed công khai.

---

## 4. Trust Levels
Hệ thống phân chia mức độ lòng tin (Trust Levels) của tài khoản người dùng để tự động điều chỉnh ngưỡng kiểm soát:
1. **Level 0: New User (Chưa xác minh)**: Tài khoản mới đăng ký email trường nhưng chưa nộp hồ sơ hoặc hồ sơ đang chờ duyệt. Quyền hạn: Bị giới hạn hoàn toàn, chỉ xem trang nộp hồ sơ.
2. **Level 1: Newly Verified (Mới xác minh)**: Tài khoản đã được phê duyệt định danh dưới 30 ngày. Quyền hạn: Đầy đủ tính năng mạng xã hội nhưng áp dụng ngưỡng Rate Limit nghiêm ngặt hơn.
3. **Level 2: Trusted Member (Thành viên tin cậy)**: Tài khoản hoạt động trên 30 ngày, không có lịch sử vi phạm tiêu chuẩn cộng đồng hoặc bị báo cáo hợp lệ. Quyền hạn: Ngưỡng Rate Limit được nới rộng.
4. **Level 3: Core Contributor / Mentor / Advisor**: Giảng viên, cựu sinh viên tiêu biểu, mentor được phê duyệt hoặc người dùng có uy tín cao. Quyền hạn: Hầu như không bị giới hạn tần suất thông thường, được hệ thống ưu tiên hiển thị.

---

## 5. Rate Limit Matrix
Bảng ma trận giới hạn tần suất hành động (Rate Limit) áp dụng tại Server-Side. Đây là các tham số mặc định cho phiên bản MVP và có thể tùy chỉnh trong file cấu hình `.env` hoặc hệ thống quản trị:

| Hành Động | Giới Hạn Tối Đa | Khung Thời Gian | Phạm Vi (Scope) | Trạng Thái Lỗi / Hành Vi Xử Lý |
| :--- | :---: | :---: | :--- | :--- |
| **Verification Submission** | 3 lần | 24 giờ | `user_id` | `429 Too Many Requests` kèm thông báo giới hạn nộp hồ sơ trong ngày. |
| **Camera Capture Session** | 5 lần | Mỗi request | `session` | Chặn chụp tiếp từ camera thiết bị, bắt buộc đợi 30 phút hoặc liên hệ hỗ trợ. |
| **Post Create** | 10 bài | 1 giờ | `user_id` | Chặn tạo bài viết mới trên feed. |
| **Comment Create** | 30 bình luận | 1 giờ | `user_id` | Tạm khóa quyền bình luận trong 15 phút. |
| **Report Content** | 20 lượt | 24 giờ | `user_id` | Chặn gửi báo cáo mới; đồng thời chặn hoàn toàn nếu hệ thống phát hiện báo cáo trùng lặp cho cùng 1 mục tiêu. |
| **Connection Request** | 5 lời mời / 20 lời mời | 1 giờ / 24 giờ | `user_id` | Lỗi `429`, chặn gửi kết nối mới để tránh quét hàng loạt tài khoản. |
| **Message Send** | 60 tin nhắn / 300 tin nhắn | 1 giờ / 24 giờ | `user_id` & `conversation_id` | Tạm dừng quyền gửi tin nhắn trong hội thoại đó (15 phút). |
| **Share Post to Message** | 10 lượt / 30 lượt | 1 giờ / 24 giờ | `user_id` | Chặn chia sẻ bài đăng qua chat tạm thời. |
| **Block User** | 50 tài khoản | 24 giờ | `user_id` | Ngưỡng an toàn chống quét API chặn hàng loạt. |

---

## 6. Verification Abuse Controls
- **Giới hạn số lần nộp lại**: Nếu hồ sơ định danh bị từ chối 3 lần liên tiếp, hệ thống sẽ tự động khóa tính năng nộp lại trong vòng 7 ngày và chuyển trạng thái yêu cầu sang `conflict` để Admin xem xét thủ công.
- **Bảo vệ Camera Capture**: Khóa phiên chụp nếu phát hiện người dùng liên tục cố tình gửi ảnh đen, ảnh trống, hoặc lặp lại thao tác chụp lỗi 5 lần liên tiếp. Quy định này ngăn chặn bot tấn công API upload ảnh thẻ.

---

## 7. Feed / Post Abuse Controls
- **Bộ lọc từ khóa cấm (Spam/Toxic Filter)**: Khi người dùng tạo bài đăng, hệ thống chạy nội dung qua bộ lọc từ khóa thô (blacklisted keywords) tại Server-Side. Bài viết chứa từ ngữ vi phạm nghiêm trọng sẽ bị chuyển ngay sang trạng thái `auto_hidden` (chờ duyệt) và gửi cảnh báo tới tác giả mà không hiển thị công khai.
- **Ngăn chặn nội dung trùng lặp (Duplicate Detection)**: Sử dụng thuật toán so khớp chuỗi thô để chặn người dùng đăng cùng một nội dung (hoặc nội dung giống nhau >90%) lên feed liên tục trong vòng 10 phút.

---

## 8. Comment Abuse Controls
- **Cooldown bình luận**: Áp dụng thời gian chờ (cooldown) tối thiểu 5 giây giữa hai lần bình luận liên tiếp của cùng một người dùng để triệt tiêu bot spam bình luận tự động.
- **Giới hạn độ dài & ký tự**: Bình luận tối đa 1000 ký tự. Hệ thống tự động từ chối các bình luận chứa chuỗi ký tự lặp lại vô nghĩa (ví dụ: "aaaaa..." dài quá mức).

---

## 9. Reporting Abuse Controls
- **Nghiêm cấm tự báo cáo**: Hệ thống kiểm tra ID tác giả và ID người báo cáo ở tầng Policy. Người dùng không thể tự báo cáo nội dung của chính mình.
- **Chặn báo cáo trùng lặp**: Nếu User A đã gửi một báo cáo đang ở trạng thái `pending` cho Bài viết X, hệ thống sẽ chặn đứng mọi nỗ lực gửi thêm báo cáo mới của User A cho Bài viết X đó, trả về thông báo: *"Bạn đã báo cáo nội dung này và hệ thống đang xử lý."*
- **Hạn chế báo cáo ảo**: Nếu tài khoản gửi quá 5 báo cáo sai sự thật liên tiếp (được Admin xác định và đóng báo cáo dạng "Dismissed"), quyền gửi báo cáo của người dùng đó sẽ bị vô hiệu hóa trong 30 ngày.
- **Tính bảo mật của báo cáo**: Toàn bộ thông tin báo cáo vi phạm là bảo mật tuyệt đối. Người bị báo cáo không thể biết ai là người đã báo cáo mình để ngăn chặn xung đột/trả thù nội bộ.

---

## 10. Connection Abuse Controls
- **Ngưỡng lời mời chờ phản hồi (Pending Request Limit)**: Một tài khoản chỉ được phép duy trì tối đa 50 lời mời kết nối ở trạng thái `pending` (chưa được người nhận chấp nhận hoặc từ chối). Nếu đạt ngưỡng này, người dùng bắt buộc phải hủy bớt các lời mời cũ trước khi gửi thêm lời mời mới. Biện pháp này triệt tiêu hoàn toàn hành vi gửi kết nối bừa bãi.
- **Chặn gửi lại ngay lập tức**: Khi User B từ chối (`decline`) lời mời kết nối của User A, User A sẽ bị áp dụng thời gian chờ là **7 ngày** trước khi hệ thống cho phép gửi lại lời mời kết nối tiếp theo tới User B.

---

## 11. Messaging Abuse Controls
- **Rào cản bạn bè**: Nghiêm cấm tuyệt đối việc nhắn tin tự do. Chỉ những tài khoản đã kết nối (`connected`) mới có thể bắt đầu nhắn tin.
- **Quyền chặn tức thời (Instant Block)**: Bất kỳ bên nào cũng có thể nhấn Chặn (`block`) đối phương trực tiếp từ giao diện nhắn tin. Khi đã chặn, API gửi tin nhắn sẽ chặn đứng cuộc trò chuyện ngay lập tức ở tầng Policy.
- **Chặn gửi tin hàng loạt (Anti-Mass-Messaging)**: UEConnect phiên bản hiện tại **không hỗ trợ tính năng gửi tin nhắn nhóm hoặc gửi tin nhắn hàng loạt** cho nhiều người cùng lúc để triệt tiêu spam quảng cáo/marketing.

---

## 12. Post Sharing Abuse Controls
- **Kiểm soát khả năng chia sẻ**: Người dùng chỉ được chia sẻ bài viết có trạng thái hiển thị hợp lệ (`verified_users` hoặc chế độ mà người nhận có quyền xem).
- **Giới hạn số lần chia sẻ cùng một bài đăng**: Chặn người dùng chia sẻ liên tục **cùng một bài đăng** vào **cùng một cuộc trò chuyện** nhiều hơn 3 lần trong vòng 5 phút (cooldown chia sẻ).

---

## 13. Blocking and Safety Controls
- **Hủy bỏ quan hệ tức thì**: Khi User A chặn User B, hệ thống tự động xóa bỏ mối liên kết bạn bè trong bảng `connections` (chuyển sang trạng thái `inactive` hoặc xóa bản ghi) và thiết lập bản ghi chặn trong bảng `blocks`.
- **Ẩn dấu vết hoàn toàn**: Cơ chế chặn của UEConnect là chặn ẩn danh hai chiều. Người bị chặn sẽ không nhận được bất kỳ thông báo nào cho biết họ bị chặn, hệ thống chỉ đơn giản trả về các trạng thái trống (empty states) hoặc lỗi không tìm thấy tài nguyên (`404 Not Found`) khi họ cố gắng truy cập profile của người chặn.

---

## 14. Moderator / Admin Workflow
Quy trình xử lý khi phát hiện lạm dụng bởi nhân sự kiểm duyệt:
1. **Moderation Queue**: Tất cả các báo cáo vi phạm được đẩy vào danh sách kiểm duyệt chung theo thứ tự ưu tiên (dựa trên số lượng lượt báo cáo trùng lặp cho cùng một bài đăng).
2. **Hành động xử lý nhanh**: Kiểm duyệt viên có quyền ẩn nội dung vi phạm ngay lập tức (`moderation.post_hidden` hoặc `moderation.comment_hidden`). Hành động này có hiệu lực tức thì trên feed của toàn bộ người dùng.
3. **Xem xét tài khoản**: Nếu bài đăng chứa nội dung độc hại nghiêm trọng hoặc vi phạm pháp luật, kiểm duyệt viên chuyển yêu cầu lên Admin cấp cao để tiến hành hạn chế (`restricted`) hoặc đình chỉ (`suspended`) tài khoản tác giả.

---

## 15. Automated Detection Signals
Hệ thống tự động lắng nghe các tín hiệu bất thường sau để đưa ra cảnh báo hoặc tự động hạn chế tài khoản:
- **Tốc độ đăng bài bất thường**: Tạo trên 5 bài đăng trong vòng 2 phút.
- **Spam báo cáo nhanh**: Gửi liên tiếp 10 báo cáo nội dung khác nhau trong vòng 1 phút.
- **Thay đổi IP đột ngột**: Đăng nhập tài khoản tại hai địa lý cách xa nhau trong khoảng thời gian không thực tế (chống hack tài khoản/chia sẻ tài khoản).

---

## 16. Manual Review Triggers
Các trường hợp hệ thống bắt buộc chuyển sang quy trình đánh giá thủ công bởi Admin:
- Yêu cầu xác minh danh tính bị đánh dấu `conflict` (trùng MSSV với tài khoản khác đã được duyệt).
- Tài khoản bị báo cáo vi phạm bởi trên 5 người dùng khác nhau trong vòng 24 giờ.
- Người dùng gửi đơn kháng nghị (Appeal) sau khi tài khoản bị đình chỉ.

---

## 17. User Feedback and Error Messages
Để tránh việc kẻ xấu dò tìm cơ chế hoạt động của bộ lọc lạm dụng (Reverse Engineering), hệ thống áp dụng các phản hồi trung lập:
- Khi bị rate limit: *"Hành động quá nhanh. Vui lòng thử lại sau ít phút."* (Không hiển thị chi tiết số lượt còn lại hoặc thuật toán tính toán).
- Khi cố tình truy cập nội dung bị chặn/xóa: Trả về lỗi `404 Not Found` thay vì `403 Forbidden` để kẻ xấu không biết tài nguyên đó có tồn tại hay không.

---

## 18. Audit and Metrics
- **Ghi nhật ký lạm dụng**: Mọi hành vi kích hoạt rate limit cấp độ cao hoặc tự động hạn chế tài khoản đều được ghi nhận vào Audit Log với hành động `security.abuse_detected`.
- **Thống kê báo cáo**: Hệ thống theo dõi tỷ lệ báo cáo đúng/sai của từng người dùng để đánh giá mức độ uy tín của tài khoản khi gửi báo cáo trong tương lai.

---

## 19. Testing Requirements
Các kịch bản kiểm thử bắt buộc phải hoàn thành trước khi triển khai:
- **Test Rate Limit**: Giả lập gửi 11 bài đăng liên tiếp trong vòng 1 giờ để đảm bảo bài thứ 11 bị chặn đứng bởi Middleware.
- **Test Block Message**: Giả lập gửi tin nhắn sau khi User B đã chặn User A để đảm bảo tin nhắn không được ghi vào DB và trả về lỗi phân quyền.
- **Test Connection Spam**: Giả lập gửi lời mời kết nối khi đã đạt ngưỡng 50 yêu cầu đang chờ phản hồi để kiểm tra tính chính xác của Gate.

---

## 20. Implementation Notes
- **Cấu hình linh hoạt qua `.env`**: Toàn bộ các giá trị giới hạn (tần số, số lượng lời mời chờ phản hồi, số ngày cooldown) bắt buộc phải được định nghĩa bằng biến môi trường (ví dụ: `LIMIT_POST_PER_HOUR=10`), tuyệt đối không được hardcode trực tiếp trong mã nguồn.
- **Sử dụng Redis Rate Limiter**: Hệ thống tận dụng tính năng giới hạn tần suất mạnh mẽ của Laravel kết hợp với Redis Driver để đạt hiệu năng xử lý cao nhất và tránh tắc nghẽn cơ sở dữ liệu MySQL chính.
- **Quy trình format code**: Luôn chạy lệnh `vendor/bin/pint --format agent` trên các file liên quan trước khi đẩy code lên repository.