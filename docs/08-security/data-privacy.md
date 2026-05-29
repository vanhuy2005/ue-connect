# Data Privacy Specification

## 1. Overview
UEConnect cam kết bảo vệ thông tin định danh cá nhân (PII) và các minh chứng xác thực nhạy cảm của người dùng. Tài liệu này quy định chi tiết các nguyên tắc bảo mật và quyền riêng tư tuyệt đối áp dụng cho toàn bộ dữ liệu người dùng tại hệ thống, bao gồm quy trình định danh tự động bằng AI, dữ liệu mạng xã hội (posts, comments, connections), và kênh trò chuyện cá nhân (messaging).

---

## 2. Data Classification
Mọi thông tin lưu trữ tại UEConnect được phân loại thành 7 cấp độ bảo mật nghiêm ngặt để xác định quyền truy cập và lưu trữ:

1. **`Public`**: Thông tin hiển thị công khai bên ngoài mà không cần đăng nhập.
   - *Ví dụ*: Trang giới thiệu (landing page), điều khoản sử dụng, trang đăng nhập/đăng ký.
2. **`Community-visible`**: Thông tin chỉ hiển thị cho những người dùng đã vượt qua xác minh danh tính và đang ở trạng thái `active`.
   - *Ví dụ*: Bài đăng công khai trên Home Feed, bình luận dưới bài đăng, tên hiển thị (Display Name), ảnh đại diện (Avatar), khoa ngành học tập.
3. **`Connection-visible`**: Thông tin chỉ hiển thị với những tài khoản đã thiết lập mối quan hệ kết nối bạn bè thành công (`connected`).
   - *Ví dụ*: Thông tin liên hệ phụ (nếu được người dùng cho phép hiển thị trong cài đặt quyền riêng tư), bài đăng ở chế độ giới hạn bạn bè.
4. **`Private`**: Thông tin cá nhân riêng tư chỉ chủ sở hữu tài khoản mới được truy cập.
   - *Ví dụ*: Nội dung tin nhắn trò chuyện 1-1, danh sách các tài khoản đã chặn (`blocklist`), danh sách bài viết đã lưu (`saved posts`), cài đặt quyền riêng tư cá nhân.
5. **`Sensitive`**: Thông tin định danh cá nhân nhạy cảm (PII), cần được bảo vệ đặc biệt và mã hóa khi truyền tải/lưu trữ.
   - *Ví dụ*: Ảnh thẻ sinh viên nộp minh chứng, bảng điểm xác thực tốt nghiệp, văn bản OCR trích xuất từ thẻ sinh viên, Mã số sinh viên (MSSV), số điện thoại liên kết.
6. **`Restricted-admin-only`**: Dữ liệu vận hành hệ thống chỉ dành riêng cho nhân sự quản trị có thẩm quyền.
   - *Ví dụ*: Nhật ký kiểm duyệt (moderation notes), báo cáo vi phạm (reports), lịch sử phê duyệt của Admin, nhật ký hoạt động hệ thống (Audit Logs).
7. **`System-secret`**: Các thông tin bảo mật tối cao của hệ thống.
   - *Ví dụ*: Khóa bí mật API (API Keys), mật khẩu băm (hashed passwords), khóa ứng dụng Microsoft Client Secret, API Keys của các dịch vụ Gemini/OpenRouter.

---

## 3. Personal Data Inventory
Bảng danh mục quản lý dữ liệu cá nhân (PII) được hệ thống thu thập:

| Dữ Liệu | Loại Bảo Mật | Mục Đích Sử Dụng | Thời Gian Lưu Trữ |
| :--- | :---: | :--- | :--- |
| **Họ và tên** | Sensitive | Xác minh định danh chính chủ | Trong suốt vòng đời hoạt động của tài khoản. |
| **MSSV** | Sensitive | Bảo đảm tính duy nhất của danh tính | Trong suốt vòng đời hoạt động của tài khoản (mã hóa một chiều trong DB nếu cần thiết). |
| **Ảnh thẻ sinh viên** | Sensitive | Minh chứng xác thực danh tính | Lưu tại private disk, xóa hoặc lưu trữ lạnh sau khi phê duyệt thành công. |
| **Văn bản OCR** | Sensitive | Đối khớp thông tin tự động bằng AI | Chỉ lưu trữ tạm thời trong bộ nhớ cache để đối khớp, xóa ngay sau khi Job hoàn tất (trừ khi `AI_STORE_RAW_OCR_TEXT=true`). |
| **Tin nhắn 1-1** | Private | Phục vụ kết nối giao tiếp | Lưu trữ cho đến khi người dùng tự xóa hoặc yêu cầu xóa tài khoản. |
| **Địa chỉ IP** | Restricted | Bảo mật hệ thống, phát hiện lạm dụng | Lưu trữ tối đa 1 năm trong Audit Log. |

---

## 4. Verification Evidence Privacy
Quy trình bảo vệ minh chứng định danh học tập:
- **Ổ đĩa riêng tư (Private Storage)**: Tất cả hình ảnh chụp từ camera hoặc file tài liệu tải lên đều phải được lưu trữ trên disk `private` (không công khai qua thư mục `public`).
- **Đường dẫn bảo mật**: Lưu tại `verifications/{user_id}/captures/{uuid}.jpg`.
- **Phục vụ qua Controller kiểm soát**: File minh chứng chỉ được truy xuất bởi Quản trị viên có thẩm quyền thông qua route bảo mật `admin.verification.evidence` sau khi đã kiểm tra quyền `review_verification`. Hệ thống nghiêm cấm tạo liên kết công khai (symbolic link) trực tiếp tới các tệp tin này.

---

## 5. Camera Capture Privacy
- **Không nhận diện khuôn mặt**: Hệ thống chỉ yêu cầu chụp trực tiếp mặt trước/sau của thẻ sinh viên để trích xuất văn bản thô. Không thực hiện nhận diện sinh trắc học hoặc phân tích khuôn mặt (Face Recognition) để tránh thu thập dữ liệu sinh trắc học nhạy cảm của sinh viên.
- **Khung hướng dẫn đặt thẻ**: Giao diện UI camera cung cấp khung chữ nhật hướng dẫn đặt thẻ vừa vặn để giảm thiểu tối đa việc chụp lọt các thông tin thừa xung quanh như khuôn mặt người chụp hay môi trường phía sau.

---

## 6. AI Verification Privacy
- **Xử lý cục bộ mặc định**: Quy trình phân tích mặc định sử dụng OCR cục bộ (Tesseract) và Ollama chạy trực tiếp trên máy chủ riêng của UEConnect để đảm bảo dữ liệu không bao giờ rời khỏi hạ tầng của trường.
- **Không lưu dữ liệu thô vào Log hệ thống**: Không lưu hình ảnh, chuỗi base64 hoặc văn bản OCR thô trực tiếp vào log files của Laravel để tránh rò rỉ thông tin qua logs hệ thống thông thường.
- **Che giấu thông tin nhạy cảm**: Khi thực hiện ghi log kết quả so khớp, các thông tin nhạy cảm (như Họ tên, Mã sinh viên) phải được che giấu hoặc ẩn đi một phần (redact).

---

## 7. Third-Party AI Fallback Rules
- **Tắt theo mặc định**: Các dịch vụ AI bên ngoài (Gemini Flash, OpenRouter Vision) bị **tắt hoàn toàn theo mặc định** (`AI_ALLOW_EXTERNAL_PROVIDER=false`).
- **Quyền riêng tư bên thứ ba**: Chỉ kích hoạt khi có sự cấu hình chủ động từ Quản trị viên (`AI_ALLOW_EXTERNAL_PROVIDER=true`) để phục vụ như giải pháp thay thế khi máy chủ cục bộ quá tải. Khi gửi ảnh thẻ qua API bên ngoài, hệ thống chỉ gửi dữ liệu ảnh qua kết nối HTTPS được mã hóa và không chia sẻ kèm bất kỳ thông tin định danh nào khác của tài khoản người dùng (như email, IP, ID). Việc sử dụng nhà cung cấp bên ngoài phải được hiển thị rõ ràng cho admin trong kết quả phân tích.

---

## 8. Profile Privacy
- **Cài đặt riêng tư của người dùng**: Cung cấp các tùy chọn cho phép người dùng tự cấu hình khả năng hiển thị profile của mình trong phân hệ Discovery (ví dụ: ẩn profile khỏi danh sách gợi ý, chỉ cho phép bạn bè đã kết nối xem đầy đủ thông tin liên hệ).
- **Ẩn thông tin nhạy cảm công khai**: Không bao giờ hiển thị trực tiếp MSSV, Email thật hoặc Số điện thoại lên giao diện profile công khai của cộng đồng.

---

## 9. Social Post Privacy
- **Quyền kiểm soát của tác giả**: Khi tạo bài đăng, người dùng có quyền chọn đối tượng hiển thị (`verified_users`, `connections_only`).
- **Xử lý khi xóa bài đăng**: Khi người dùng nhấn Xóa bài đăng, hệ thống sẽ thực hiện cập nhật trạng thái `status = 'deleted'`. Bài đăng lập tức biến mất khỏi feed của tất cả mọi người và giao diện người dùng. Bản ghi thô trong cơ sở dữ liệu sẽ được xóa vật lý (hard delete) sau thời hạn 30 ngày (lưu trữ tạm để phục vụ kiểm duyệt nếu bài đăng đang bị báo cáo vi phạm).

---

## 10. Comment Privacy
- Bình luận của người dùng kế thừa trực tiếp quyền riêng tư của bài đăng cha. Nếu bài đăng cha chuyển sang trạng thái bị ẩn hoặc bị xóa, toàn bộ bình luận bên dưới bài đăng đó cũng lập tức không thể truy cập bởi bất kỳ ai.
- Người dùng có toàn quyền xóa bình luận của mình bất kỳ lúc nào để bảo vệ tiếng nói cá nhân.

---

## 11. Connection Privacy
- Danh sách bạn bè/kết nối của mỗi người dùng mặc định là thông tin riêng tư (`Private`), không hiển thị công khai cho toàn cộng đồng nhằm tránh việc khai thác mối quan hệ xã hội trái phép.
- Lời mời kết nối bị từ chối sẽ được chuyển trạng thái lặng lẽ, không gửi thông báo "từ chối" cho người gửi lời mời để bảo vệ trải nghiệm riêng tư của người nhận.

---

## 12. Messaging Privacy
- **Tin nhắn riêng tư tuyệt đối**: Nội dung tin nhắn trò chuyện 1-1 chỉ hiển thị với hai người tham gia trực tiếp cuộc trò chuyện.
- **Không duyệt tin nhắn**: Nhân sự quản trị (Admin, Moderator) không có giao diện hay quyền đọc nội dung hội thoại cá nhân của người dùng trên trang dashboard thông thường. Quyền truy xuất tin nhắn để điều tra chỉ được kích hoạt trong trường hợp khẩn cấp liên quan đến an toàn hoặc pháp lý, yêu cầu quy trình phê duyệt nghiêm ngặt và hành động này bắt buộc phải được ghi lại trong Audit Log để giám sát chéo.
- **Không lưu tin nhắn vào logs**: Tuyệt đối không bao giờ in nội dung tin nhắn của người dùng ra log file của ứng dụng Laravel.

---

## 13. Post Sharing Privacy
- **Ngăn chặn rò rỉ quyền hạn**: Khi một bài viết được chia sẻ qua tin nhắn, hệ thống kiểm tra quyền xem của cả người gửi và người nhận. Nếu người nhận không có quyền xem bài viết gốc (do bị tác giả bài viết chặn hoặc bài đăng ở chế độ kết nối riêng tư), hệ thống sẽ không hiển thị nội dung xem trước của bài viết đó trong khung chat, thay vào đó hiển thị thông báo: *"Bài viết không khả dụng do giới hạn quyền riêng tư."*
- **Tác động khi thay đổi trạng thái**: Nếu bài viết gốc sau đó bị ẩn, bị xóa hoặc bị kiểm duyệt viên khóa, nội dung chia sẻ cũ trong phòng chat cũng tự động cập nhật sang trạng thái không hiển thị để đảm bảo tính đồng bộ và riêng tư.

---

## 14. Admin Access Privacy
- **Nguyên tắc Đặc Quyền Tối Thiểu (Least Privilege)**: Nhân viên quản trị chỉ được cấp quyền xem dữ liệu nhạy cảm khi thực sự cần thiết để thực hiện công việc phê duyệt hoặc kiểm duyệt.
- **Giám sát hoạt động của Admin**: Mọi thao tác truy xuất dữ liệu nhạy cảm của Admin (như click xem ảnh thẻ sinh viên, tải xuống bảng điểm, xem hồ sơ báo cáo) đều được ghi nhật ký bất biến tự động (Audit Log) kèm địa chỉ IP và User Agent để ngăn chặn hành vi lạm quyền xem thông tin sinh viên bừa bãi.

---

## 15. Logs and Redaction
- Hệ thống áp dụng một lớp lọc (Middleware/Logger Formatter) tự động rà quét các từ khóa nhạy cảm trước khi ghi ra file log vật lý (`storage/logs/laravel.log`).
- Các trường dữ liệu nhạy cảm như `password`, `token`, `mssv`, `credit_card` luôn bị che giấu bằng chuỗi ký tự thay thế (ví dụ: `[REDACTED]`).

---

## 16. File Storage and Access
- **Tách biệt phân vùng lưu trữ**: Hình ảnh đại diện (avatar) công khai được lưu tại thư mục `public` để phục vụ nhanh cho giao diện. Ngược lại, toàn bộ minh chứng định danh (`evidence`) bắt buộc lưu tại thư mục `private` trong `storage/app`.
- **Cấp quyền truy xuất tạm thời (Temporary URL)**: Việc hiển thị ảnh thẻ cho Admin trong hàng đợi duyệt chỉ sử dụng cơ chế cấp URL tạm thời có chữ ký bảo mật (signed temporary URLs) có thời gian hết hạn cực ngắn (ví dụ: 2 phút) để tránh việc liên kết bị sao chép ra ngoài.

---

## 17. Retention and Deletion
Chính sách lưu trữ và xóa bỏ dữ liệu cốt lõi tại UEConnect:
- **Minh chứng định danh (Evidence)**: Giữ lại trong suốt quá trình tài khoản hoạt động để làm cơ sở đối chiếu khi xảy ra tranh chấp danh tính. Khi tài khoản bị xóa, tệp tin minh chứng tương ứng sẽ bị xóa vật lý hoàn toàn khỏi ổ đĩa trong vòng 30 ngày.
- **Văn bản OCR**: Không lưu trữ lâu dài nếu không bật cấu hình cấu hình lưu trữ OCR thô.
- **Tin nhắn và Bài đăng**: Khi người dùng yêu cầu xóa vĩnh viễn tài khoản (Account Deletion):
  - Tài khoản chuyển sang trạng thái `deactivated` trong 30 ngày để người dùng có cơ hội khôi phục nếu muốn.
  - Sau 30 ngày, hệ thống thực hiện xóa vật lý hoàn toàn (Hard Delete) toàn bộ thông tin profile, bài đăng, bình luận, và các mối liên kết kết nối của tài khoản đó. Các tin nhắn trong cuộc trò chuyện 1-1 sẽ được ẩn vĩnh viễn đối với bên còn lại dưới dạng "Người dùng UEConnect đã xóa tài khoản".
- **Audit Logs**: Giữ nguyên tính chất append-only bất biến lâu dài để phục vụ mục đích an ninh.

---

## 18. Data Export
- Hệ thống cung cấp tính năng cho phép người dùng gửi yêu cầu xuất dữ liệu cá nhân (Data Export) theo đúng quyền tự quyết dữ liệu.
- Định dạng xuất tệp tin là JSON hoặc ZIP chứa hình ảnh bài đăng và lịch sử hoạt động thô của người dùng đó. Tiến trình tạo tệp xuất dữ liệu được xử lý trong nền (Background Job) và liên kết tải về bảo mật sẽ được gửi riêng qua email đăng ký của người dùng, tự động hết hạn sau 24 giờ.

---

## 19. User Blocking and Visibility
- **Quyền ẩn mình**: Khi User A chặn User B, hệ thống thiết lập bộ lọc hiển thị hai chiều tuyệt đối ở tầng cơ sở dữ liệu.
- **Bảo mật trạng thái chặn**: Tuyệt đối không hiển thị bất kỳ danh sách hoặc thông báo nào cho thấy User B đang bị User A chặn. Mọi yêu cầu gọi dữ liệu từ User B hướng tới User A đều nhận phản hồi giống như User A không tồn tại trên hệ thống để bảo vệ sự an toàn riêng tư của User A.

---

## 20. Incident Handling
Quy trình phản ứng khi phát hiện sự cố rò rỉ dữ liệu cá nhân (Data Breach):
1. **Cô lập sự cố (Containment)**: Tạm thời ngắt kết nối các API hoặc phân hệ bị ảnh hưởng để ngăn chặn rò rỉ lan rộng.
2. **Đánh giá mức độ ảnh hưởng**: Xác định danh sách người dùng và các loại dữ liệu nhạy cảm bị rò rỉ.
3. **Báo cáo và Thông báo**: Báo cáo sự cố lên Ban Quản lý Dự án UEConnect và thông báo bằng email riêng tới những người dùng bị ảnh hưởng trong vòng 72 giờ kèm theo các khuyến nghị bảo mật tài khoản (như đổi mật khẩu, kích hoạt lại xác thực).
4. **Khắc phục và Khôi phục**: Vá lỗ hổng bảo mật, khôi phục hệ thống từ bản sao lưu an toàn và tiến hành kiểm tra bảo mật độc lập trước khi mở lại phân hệ.

---

## 21. Testing Requirements
Các kịch bản kiểm thử bắt buộc đối với quyền riêng tư dữ liệu:
- **Test Private Storage Access**: Giả lập truy cập trực tiếp bằng URL vào file minh chứng để đảm bảo máy chủ trả về lỗi `403/404`.
- **Test Data Export Expiry**: Kiểm tra xem liên kết xuất dữ liệu cá nhân có bị vô hiệu hóa hoàn toàn sau chính xác 24 giờ hay không.
- **Test Hard Delete**: Giả lập quy trình xóa tài khoản hoàn tất và kiểm tra cơ sở dữ liệu xem thông tin MSSV, hình ảnh của người dùng đó có thực sự bị xóa khỏi các bảng dữ liệu hoạt động hay không.

---

## 22. Implementation Notes
- **Mã hóa trường nhạy cảm trong Database**: Sử dụng tính năng mã hóa dữ liệu tích hợp của Laravel (`protected $casts = ['mssv' => 'encrypted']`) cho các trường dữ liệu định danh như MSSV để tăng thêm một lớp bảo mật phòng trường hợp database bị lộ thô.
- **Thời hạn Temporary URL**: Cấu hình thời hạn của temporary URL cho minh chứng thông qua biến môi trường `EVIDENCE_URL_EXPIRY_SECONDS=120`.
- **Thực thi Pint**: Chạy lệnh `vendor/bin/pint --format agent` để đảm bảo code liên quan đến bảo mật dữ liệu tuân thủ chuẩn định dạng mã nguồn PHP của dự án.