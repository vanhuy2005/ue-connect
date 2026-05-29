# Tesseract OCR Setup (Windows / Laragon)

Tài liệu này hướng dẫn cài đặt và thiết lập Tesseract OCR cục bộ cho môi trường phát triển Laragon trên Windows.

## 1. Cài đặt Tesseract OCR trên Windows

1. Truy cập trang tải xuống chính thức của UB-Mannheim:
   👉 [UB-Mannheim Tesseract OCR Installer](https://github.com/UB-Mannheim/tesseract/wiki)
2. Tải bản cài đặt mới nhất dành cho Windows (64-bit).
3. Chạy file cài đặt:
   - Thư mục cài đặt mặc định: `C:\Program Files\Tesseract-OCR`
   - **Quan trọng**: Trong bước chọn ngôn ngữ bổ sung (Additional language data), hãy tích chọn **Vietnamese** (`vie`) và đảm bảo **English** (`eng`) cũng được chọn.
4. Hoàn tất quá trình cài đặt.

## 2. Thêm Tesseract vào biến môi trường PATH

Hệ thống cần gọi lệnh `tesseract` trực tiếp từ PHP. Bạn cần cấu hình PATH trên Windows:

1. Mở ô tìm kiếm Windows và nhập: `Environment Variables` (Biến môi trường).
2. Chọn **Edit the system environment variables** → click **Environment Variables...**
3. Trong mục **System variables**, tìm biến có tên **Path** và click **Edit...**
4. Click **New** và dán đường dẫn: `C:\Program Files\Tesseract-OCR`
5. Click **OK** liên tục để lưu các thiết lập.
6. **Khởi động lại Laragon** và terminal để cập nhật thay đổi.

## 3. Xác minh cài đặt

Mở PowerShell hoặc Command Prompt mới và chạy lệnh sau để kiểm tra:

```bash
tesseract --version
```

Kết quả hiển thị phiên bản Tesseract và danh sách thư viện là thành công. Để kiểm tra danh sách ngôn ngữ khả dụng:

```bash
tesseract --list-langs
```

Đảm bảo kết quả hiển thị có cả `vie` và `eng`.

## 4. Bật AI Verification trong môi trường Development (`.env`)

Mở tệp `.env` dự án của bạn và điều chỉnh cấu hình như sau:

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=local_hybrid
AI_OCR_ENGINE=tesseract
AI_OLLAMA_ENABLED=true
```

## 5. Cơ chế dự phòng khi không cài Tesseract (Fail-Safe)

Nếu máy chủ phát triển chưa được cài đặt Tesseract, hệ thống sẽ **không bị crash hay gặp lỗi nghiêm trọng**.
- **Cơ chế**: Tiến trình chạy Job sẽ bắt được ngoại lệ `ProcessFailedException`, ghi nhận cảnh báo vào log file.
- **Kết quả**: Yêu cầu xác thực vẫn sẽ được tạo, trạng thái AI được đánh dấu `manual_review_required` với mã cảnh báo rủi ro `ocr_unavailable`.
- **Duyệt thủ công**: Giáo vụ hoàn toàn có thể chủ động kiểm tra bằng mắt ảnh chụp và duyệt hồ sơ bình thường.

---

# 1. Tesseract OCR nằm ở đâu trong AI Verification?

Tesseract không phải “AI hiểu giấy tờ”. Nó chỉ làm nhiệm vụ **đọc chữ trong ảnh**.

Pipeline thực tế của UEConnect sẽ là:

```txt
Ảnh thẻ sinh viên chụp từ camera
→ Tesseract OCR đọc chữ
→ Laravel extract MSSV / họ tên / khoa / khóa
→ Ollama 1.5B chuẩn hóa OCR text nếu bật
→ Matching service so khớp với dữ liệu user nhập
→ Lưu AI analysis result
→ Admin xem kết quả và duyệt thủ công
```

Tesseract làm bước này:

```txt
Ảnh thẻ sinh viên → OCR text
```

Ví dụ:

```txt
TRƯỜNG ĐẠI HỌC SƯ PHẠM TP.HCM
NGUYỄN VĂN QUANG HUY
MSSV: 49.01.104.055
KHOA CÔNG NGHỆ THÔNG TIN
KHÓA 49
```

Sau đó Laravel mới xử lý tiếp. Đừng kỳ vọng Tesseract tự biết “người này nên được duyệt”. Nó chỉ đọc chữ, không làm trưởng phòng giáo vụ.

---

# 2. Tài liệu Tesseract của bạn nên bổ sung gì?

Phần bạn viết đã ổn. Tôi sẽ bổ sung thêm các mục còn thiếu để bạn đưa vào `docs/setup-tesseract.md`.

## Bản hoàn chỉnh đề xuất

````md
# Tesseract OCR Setup for UEConnect AI Verification

Tài liệu này hướng dẫn cài đặt Tesseract OCR cục bộ cho môi trường phát triển UEConnect trên Windows/Laragon.

Tesseract được dùng trong tính năng AI Verification để đọc chữ từ ảnh thẻ sinh viên được chụp bằng camera. Tesseract chỉ thực hiện OCR, không tự duyệt hồ sơ và không thay thế quyết định của quản trị viên.

---

## 1. Vai trò của Tesseract trong UEConnect

Pipeline AI Verification:

```txt
Camera capture
→ Private evidence storage
→ Tesseract OCR
→ Field extraction
→ Optional Ollama normalization
→ Matching engine
→ Admin review
```

Tesseract chỉ làm bước:

```txt
Image → OCR text
```

Ví dụ OCR text:

```txt
TRƯỜNG ĐẠI HỌC SƯ PHẠM TP.HCM
NGUYỄN VĂN A
MSSV: 49.01.104.055
KHOA CÔNG NGHỆ THÔNG TIN
KHÓA 49
```

UEConnect sẽ dùng OCR text này để trích xuất:

```txt
full_name
student_code
faculty
academic_program
cohort
school_name
```

---

## 2. Cài đặt Tesseract OCR trên Windows

1. Truy cập trang tải xuống của UB-Mannheim:

   [https://github.com/UB-Mannheim/tesseract/wiki](https://github.com/UB-Mannheim/tesseract/wiki)

2. Tải bản cài đặt Windows 64-bit mới nhất.

3. Chạy installer.

4. Thư mục cài đặt mặc định:

```txt
C:\Program Files\Tesseract-OCR
```

5. Ở bước chọn ngôn ngữ bổ sung, chọn:

```txt
English - eng
Vietnamese - vie
```

Nếu không chọn `vie`, OCR tiếng Việt sẽ kém hơn hoặc không chạy đúng với tham số ngôn ngữ `vie`.

---

## 3. Thêm Tesseract vào PATH

1. Mở Windows Search.
2. Tìm `Environment Variables`.
3. Chọn `Edit the system environment variables`.
4. Click `Environment Variables...`.
5. Trong `System variables`, chọn `Path`.
6. Click `Edit...`.
7. Click `New`.
8. Thêm:

```txt
C:\Program Files\Tesseract-OCR
```

9. Lưu lại.
10. Khởi động lại Laragon, terminal, PowerShell hoặc VS Code terminal.

---

## 4. Kiểm tra cài đặt

Mở PowerShell mới:

```powershell
tesseract --version
```

Nếu thành công, bạn sẽ thấy version Tesseract.

Kiểm tra ngôn ngữ:

```powershell
tesseract --list-langs
```

Cần thấy:

```txt
eng
vie
```

Nếu không thấy `vie`, cần cài thêm Vietnamese traineddata.

---

## 5. Test OCR bằng ảnh thật

Tạo thư mục test, ví dụ:

```txt
C:\ocr-test
```

Đặt ảnh thẻ sinh viên test:

```txt
C:\ocr-test\student-card.jpg
```

Chạy OCR tiếng Việt + tiếng Anh:

```powershell
tesseract C:\ocr-test\student-card.jpg C:\ocr-test\output -l vie+eng
```

Sau đó mở:

```txt
C:\ocr-test\output.txt
```

Nếu text đọc được MSSV / họ tên / trường, Tesseract hoạt động ổn.

---

## 6. Cấu hình UEConnect `.env`

Mặc định local/dev nên để mock:

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock
```

Khi muốn test OCR thật:

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=local_hybrid
AI_OCR_ENGINE=tesseract
AI_OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:1.5b
```

Nếu chưa muốn dùng Ollama:

```env
AI_OLLAMA_ENABLED=false
```

Khi đó hệ thống chỉ dùng:

```txt
Tesseract OCR + rule-based extractor + matching service
```

---

## 7. Queue worker

AI Verification chạy qua queue job, không chạy trực tiếp trong request.

Vì `.env` dùng:

```env
QUEUE_CONNECTION=database
```

cần đảm bảo đã có bảng jobs:

```bash
php artisan queue:table
php artisan migrate
```

Khi test AI Verification, cần chạy queue worker:

```bash
php artisan queue:work
```

Hoặc trong lúc dev có thể chạy:

```bash
php artisan queue:listen
```

Nếu không chạy queue worker, hồ sơ vẫn được tạo nhưng AI job sẽ nằm chờ trong bảng `jobs`.

---

## 8. Các lệnh cần chạy sau khi đổi `.env`

Sau khi chỉnh `.env`, chạy:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

Nếu có migration mới:

```bash
php artisan migrate
```

Nếu có queue:

```bash
php artisan queue:work
```

---

## 9. Fail-safe khi chưa cài Tesseract

Nếu Tesseract chưa được cài hoặc PHP không gọi được lệnh `tesseract`, hệ thống không được crash.

Expected behavior:

```txt
AI analysis status = manual_review_required
risk_flags includes ocr_unavailable
verification request vẫn được tạo
admin vẫn duyệt thủ công
```

Không được:

```txt
fail verification submission
log raw OCR text
log image/base64
approve user tự động
```

---

## 10. Lưu ý bảo mật

Tesseract chạy local nên ảnh không bị gửi ra provider bên ngoài.

Nhưng evidence vẫn là dữ liệu cá nhân, nên phải giữ các rule:

```txt
Ảnh lưu private disk
Không public URL
Không log ảnh/base64
Không log OCR text
Không nhận diện khuôn mặt
AI không tự approve
Admin là người quyết định cuối
```
````

---

# 3. Giải thích phần `.env` AI Verification của bạn

Đoạn này:

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock
```

nghĩa là:

```txt
AI Verification đang tắt.
Nếu có gọi AI thì dùng mock provider.
Không cần Tesseract.
Không cần Ollama.
Không cần Gemini/OpenRouter.
An toàn cho local dev.
```

Đây là default đúng.

---

## Khi nào dùng `mock`?

Dùng khi:

```txt
mới clone repo
chưa cài Tesseract
chưa chạy Ollama
muốn test UI/admin flow
muốn chạy test suite ổn định
```

Mock không đọc ảnh thật. Nó chỉ giả lập kết quả để app không sập vì thiếu OCR.

Cấu hình:

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock
```

Hoặc nếu muốn test mock job thật:

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=mock
```

---

## Khi nào dùng `local_hybrid`?

Dùng khi bạn đã có:

```txt
Tesseract OCR
Ollama đang chạy
Model qwen2.5:1.5b đã pull
Queue worker đang chạy
```

Cấu hình:

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=local_hybrid
AI_OCR_ENGINE=tesseract
AI_OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:1.5b
```

Luồng:

```txt
Tesseract đọc chữ từ ảnh
→ Ollama chuẩn hóa OCR text thành JSON
→ Laravel matching
```

Nếu Ollama không chạy, hệ thống nên fallback:

```txt
Tesseract OCR + rule-based extractor
```

Nếu cả Tesseract cũng thiếu:

```txt
manual_review_required
```

---

# 4. Giải thích từng biến AI

## Core AI

```env
AI_VERIFICATION_ENABLED=false
```

Bật/tắt toàn bộ AI verification.

```txt
false = không dispatch AI job
true = sau khi có evidence hợp lệ sẽ dispatch AI job
```

---

```env
AI_VERIFICATION_PROVIDER=mock
```

Chọn provider chính.

Các giá trị nên có:

```txt
mock
local_hybrid
gemini_flash
openrouter
```

Khuyến nghị:

```txt
dev mới clone: mock
demo local: local_hybrid
demo cloud fallback: local_hybrid + fallback Gemini/OpenRouter
production privacy-first: local_hybrid
```

---

## Camera capture

```env
AI_CAPTURE_SESSION_TTL_MINUTES=10
```

Phiên chụp ảnh hết hạn sau 10 phút.

```txt
User bấm bắt đầu chụp
→ server tạo capture session
→ user phải chụp trong 10 phút
```

---

```env
AI_CAPTURE_MAX_ATTEMPTS=5
```

Cho phép chụp lại tối đa 5 lần.

---

```env
AI_CAPTURE_MIN_WIDTH=640
AI_CAPTURE_MIN_HEIGHT=360
```

Ảnh chụp phải có kích thước tối thiểu. Nếu thấp hơn thì OCR rất dễ tệ, rồi AI sẽ đọc như người vừa ngủ dậy.

---

```env
AI_CAPTURE_JPEG_QUALITY=0.9
```

Chất lượng ảnh khi canvas xuất JPEG. `0.9` là ổn cho OCR.

---

## OCR local

```env
AI_OCR_ENGINE=tesseract
```

Chọn OCR engine.

Hiện tại:

```txt
tesseract = gọi tesseract local trên Windows/server
```

Sau này có thể thêm:

```txt
paddleocr
```

---

```env
AI_PADDLEOCR_SERVICE_URL=
```

Nếu sau này bạn tách OCR thành Python service:

```env
AI_PADDLEOCR_SERVICE_URL=http://127.0.0.1:8001
```

Còn hiện tại để trống là được.

---

## Ollama

```env
AI_OLLAMA_ENABLED=true
```

Bật Ollama để xử lý OCR text.

Nếu `false`, hệ thống chỉ dùng rule-based extractor.

---

```env
OLLAMA_BASE_URL=http://127.0.0.1:11434
```

URL Ollama local.

Kiểm tra Ollama:

```powershell
ollama list
ollama run qwen2.5:1.5b
```

Nếu Ollama serve riêng:

```powershell
ollama serve
```

---

```env
OLLAMA_MODEL=qwen2.5:1.5b
```

Model dùng cho text normalization.

Model này không đọc ảnh. Nó chỉ nhận OCR text.

---

```env
OLLAMA_TIMEOUT_SECONDS=20
```

Nếu Ollama quá 20 giây không trả lời, job fallback. Không nên treo vô tận, vì máy tính không xấu hổ khi lãng phí thời gian của bạn.

---

## Matching threshold

```env
AI_VERIFICATION_LIKELY_MATCH_THRESHOLD=0.85
```

Nếu confidence >= 0.85:

```txt
recommendation = likely_match
```

Không auto approve.

---

```env
AI_VERIFICATION_MANUAL_REVIEW_THRESHOLD=0.65
```

Nếu score khoảng 0.65 đến dưới 0.85:

```txt
recommendation = manual_review
```

---

```env
AI_VERIFICATION_SUSPICIOUS_THRESHOLD=0.45
```

Nếu dưới khoảng này:

```txt
recommendation = reject_recommended hoặc suspicious
```

Admin vẫn quyết.

---

## External fallback

```env
AI_EXTERNAL_FALLBACK_ENABLED=false
AI_ALLOW_EXTERNAL_PROVIDER=false
```

Hai biến này đang tắt, rất đúng.

Muốn dùng Gemini/OpenRouter thì cả hai phải bật:

```env
AI_EXTERNAL_FALLBACK_ENABLED=true
AI_ALLOW_EXTERNAL_PROVIDER=true
```

Nhưng chỉ nên dùng với ảnh test/demo, không dùng giấy tờ thật nếu chưa có privacy policy rõ ràng.

---

```env
AI_EXTERNAL_FALLBACK_PROVIDERS=
```

Bạn đang để trống. Nếu muốn fallback:

```env
AI_EXTERNAL_FALLBACK_PROVIDERS=gemini_flash,openrouter
```

---

```env
AI_FALLBACK_SKIP_CONFIDENCE=0.75
```

Nếu local confidence >= 0.75 thì không gọi external fallback.

Ví dụ:

```txt
Local OCR + matching score = 0.82
→ không cần Gemini/OpenRouter

Local score = 0.50
→ nếu fallback bật, thử Gemini/OpenRouter
```

---

## Gemini

```env
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash
```

Dùng cho fallback vision nếu bật external.

Lưu ý:

```txt
Không bật mặc định.
Không dùng ảnh giấy tờ thật với free tier nếu chưa có chính sách privacy rõ.
```

---

## OpenRouter

```env
OPENROUTER_API_KEY=
OPENROUTER_VISION_MODEL=
```

OpenRouter cần model vision cụ thể. Không phải model nào cũng đọc ảnh.

Nếu model không support image input:

```txt
fail safely → manual_review
```

---

# 5. Cấu hình bạn nên dùng hiện tại

Vì bạn **chưa cài Tesseract**, `.env` hiện tại nên để:

```env
AI_VERIFICATION_ENABLED=false
AI_VERIFICATION_PROVIDER=mock
```

Khi agent dev xong UI + mock flow, bạn test trước bằng mock.

Sau khi cài Tesseract:

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=local_hybrid
AI_OCR_ENGINE=tesseract
AI_OLLAMA_ENABLED=false
```

Test OCR trước **không bật Ollama** để giảm biến số. Đừng debug Tesseract + Ollama + queue + camera cùng lúc, trừ khi bạn thích tự tạo boss fight.

Sau khi OCR ổn:

```env
AI_OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:1.5b
```

Sau khi local ổn mới thử fallback:

```env
AI_EXTERNAL_FALLBACK_ENABLED=true
AI_ALLOW_EXTERNAL_PROVIDER=true
AI_EXTERNAL_FALLBACK_PROVIDERS=gemini_flash,openrouter
GEMINI_API_KEY=...
```

---

# 6. Các lệnh chạy khi test tính năng này

## Terminal 1: Laravel app

```bash
php artisan serve --host=localhost --port=8000
```

## Terminal 2: Vite

```bash
npm run dev
```

## Terminal 3: Queue worker

```bash
php artisan queue:work
```

## Terminal 4: Ollama, nếu dùng

```powershell
ollama serve
```

Hoặc kiểm tra model:

```powershell
ollama run qwen2.5:1.5b
```

## Kiểm tra Tesseract

```powershell
tesseract --version
tesseract --list-langs
```

---

# 7. Checklist test từng mức

## Mức 1: Không cần Tesseract

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=mock
```

Check:

```txt
[ ] User mở verification/start.
[ ] Chọn camera hoặc upload.
[ ] Evidence được tạo.
[ ] AI mock result được tạo.
[ ] Admin thấy AI panel.
[ ] Request không auto approve.
```

---

## Mức 2: Tesseract, không Ollama

```env
AI_VERIFICATION_ENABLED=true
AI_VERIFICATION_PROVIDER=local_hybrid
AI_OCR_ENGINE=tesseract
AI_OLLAMA_ENABLED=false
```

Check:

```txt
[ ] Camera capture tạo ảnh.
[ ] Tesseract đọc OCR text.
[ ] Nếu ảnh rõ, MSSV được extract.
[ ] Nếu OCR fail, result manual_review.
[ ] Không crash job.
```

---

## Mức 3: Tesseract + Ollama

```env
AI_OLLAMA_ENABLED=true
```

Check:

```txt
[ ] Ollama nhận OCR text.
[ ] Ollama trả JSON hợp lệ.
[ ] Nếu JSON lỗi, fallback rule-based.
[ ] Không gửi ảnh cho Ollama.
```

---

## Mức 4: External fallback

```env
AI_EXTERNAL_FALLBACK_ENABLED=true
AI_ALLOW_EXTERNAL_PROVIDER=true
AI_EXTERNAL_FALLBACK_PROVIDERS=gemini_flash,openrouter
```

Check:

```txt
[ ] Local confidence thấp thì gọi Gemini/OpenRouter.
[ ] Nếu Gemini fail thì thử OpenRouter.
[ ] Nếu cả hai fail thì manual_review.
[ ] Không crash.
[ ] Admin thấy provider nào đã dùng.
```

---

# 8. Gợi ý sửa nhẹ phần `.env` của bạn

Phần AI hiện tại ổn, nhưng tôi sẽ thêm 2 biến này cho rõ:

```env
AI_VERIFICATION_STUDENT_CARD_ONLY=true
AI_CAMERA_CAPTURE_REQUIRED=false
```

Vì bạn đã chốt:

```txt
Camera và Upload là 2 option riêng
Camera mới chạy AI
Upload thì manual review
```

Do đó `AI_CAMERA_CAPTURE_REQUIRED=false` hợp lý hơn nếu hiểu là “bắt buộc tất cả phải camera”. Nếu muốn nghĩa khác thì đặt tên rõ hơn:

```env
AI_CAMERA_CAPTURE_RECOMMENDED=true
AI_UPLOAD_FALLBACK_ALLOWED=true
```

Tôi thích tên này hơn:

```env
AI_CAMERA_CAPTURE_RECOMMENDED=true
AI_UPLOAD_FALLBACK_ALLOWED=true
```

Rõ ràng, ít gây hiểu lầm, không bắt developer đoán ý như đọc thơ hiện đại.

---

# 9. Prompt ngắn để bảo agent update docs/config

```md
Update the AI Verification docs/config with these clarifications:

1. Default local/dev config must remain:
   AI_VERIFICATION_ENABLED=false
   AI_VERIFICATION_PROVIDER=mock

2. Camera capture and upload are two separate evidence options:
   - camera evidence can run AI analysis if supported
   - uploaded evidence goes manual review by default

3. Add Windows/Laragon Tesseract setup guide:
   - install UB-Mannheim Tesseract
   - select eng + vie language data
   - add C:\Program Files\Tesseract-OCR to PATH
   - verify with tesseract --version
   - verify languages with tesseract --list-langs

4. Add fail-safe behavior:
   - missing Tesseract must not crash job
   - result manual_review_required
   - risk flag ocr_unavailable

5. Explain provider modes:
   - mock = no OCR, test/dev safe
   - local_hybrid = Tesseract + optional Ollama
   - gemini_flash/openrouter = external fallback, disabled by default

6. Add required runtime commands:
   - php artisan queue:work
   - npm run dev
   - php artisan serve
   - ollama serve if Ollama is enabled

7. Add privacy warning:
   - no public evidence URL
   - no image/base64 logs
   - no OCR text logs
   - external fallback must be explicitly enabled
```

