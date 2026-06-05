# Hướng Dẫn Cấu Hình Resend Mail cho UEConnect

Tài liệu này hướng dẫn cách thay thế SMTP Office 365 bằng **Resend** cho ứng dụng UEConnect, đồng thời hướng dẫn thiết lập các bản ghi DNS cần thiết để email có thể gửi đến bất kỳ hộp thư nào (bao gồm Gmail và Outlook).

---

## 1. Lý do thay thế SMTP Office 365
- **Giới hạn kết nối**: Office 365 thường xuyên chặn các kết nối SMTP AUTH từ các dải IP của đám mây như Render/Azure, gây ra lỗi kết nối timeout hoặc xác thực thất bại.
- **Rủi ro bảo mật**: Việc lưu trữ mật khẩu cá nhân/tài khoản giáo dục trực tiếp trong tệp cấu hình `.env` tăng rủi ro rò rỉ tài khoản.
- **Độ tin cậy**: Resend cung cấp dịch vụ phân phối email tối ưu, theo dõi trạng thái gửi thư, thống kê tỷ lệ mở/nhấp chuột và tích hợp hoàn hảo với Laravel qua API SDK chính thức.

---

## 2. Các bước thiết lập trên Resend

### Bước 2.1: Đăng ký tài khoản và Lấy API Key
1. Truy cập [Resend.com](https://resend.com) và đăng ký tài khoản.
2. Tại trang quản trị, truy cập mục **API Keys** và chọn **Create API Key**.
3. Đặt tên và chọn quyền ghi (`Sending Access`).
4. Sao chép API Key được tạo (ví dụ: `re_9bZc45Gd_3hUicCGjkgRRDeWY7nzxLQ5G`).

### Bước 2.2: Thêm và Xác minh Tên miền (Domain Verification)
> [!IMPORTANT]
> Mặc định, nếu bạn không xác minh tên miền, Resend chỉ cho phép gửi email kiểm thử tới chính địa chỉ email đăng ký tài khoản của bạn (Sandbox Mode). Bạn **phải** xác minh tên miền gửi thư để có thể gửi email tới Gmail hoặc các hòm thư Outlook khác.

1. Tại thanh điều hướng trái của Resend, chọn **Domains** -> **Add Domain**.
2. Nhập tên miền gửi thư của bạn (ví dụ: `mail.ueconnect.edu.vn` hoặc `ueconnect.edu.vn`).
3. Chọn khu vực gửi thư (ví dụ: `us-east-1`).
4. Resend sẽ hiển thị danh sách các bản ghi DNS mà bạn cần cấu hình tại trang quản lý tên miền (như Cloudflare, GoDaddy, v.v.).

---

## 3. Cấu hình Bản ghi DNS (DKIM, SPF, DMARC)

Để đảm bảo email gửi đi từ Resend không bị đánh dấu là Thư rác (Spam) và có thể đi qua bộ lọc của Gmail/Outlook, hãy cấu hình các bản ghi DNS sau đây trong trang quản lý tên miền của bạn:

### 3.1. Bản ghi DKIM (DomainKeys Identified Mail)
Resend sẽ cung cấp bản ghi TXT (hoặc CNAME tùy thời điểm) để xác thực chữ ký số.
- **Loại (Type)**: `TXT`
- **Tên (Name/Host)**: `resend._domainkey` (hoặc `resend._domainkey.send.ue-connect` tùy theo nhà quản lý DNS của bạn)
- **Giá trị (Value/Content)**: Nhập toàn bộ chuỗi khóa công khai `p=MIGfMA...` do Resend cung cấp.

### 3.2. Bản ghi SPF & MX (Enable Sending)
Để cho phép gửi thư và định tuyến phản hồi, bạn cần cấu hình các bản ghi sau cho tên miền phụ (subdomain) gửi thư (ví dụ: `send.ue-connect`):

1. **Bản ghi MX (Mail Exchanger)**:
   - **Loại (Type)**: `MX`
   - **Tên (Name/Host)**: `send.ue-connect` (hoặc `send` tùy theo nhà quản lý DNS)
   - **Giá trị (Value/Content)**: `10 feedback-smtp.us-east-1.amazonses.com` (hoặc máy chủ MX do Resend chỉ định)
   - **Độ ưu tiên (Priority)**: `10` (hoặc `Auto`)

2. **Bản ghi SPF (Sender Policy Framework)**:
   - **Loại (Type)**: `TXT`
   - **Tên (Name/Host)**: `send.ue-connect` (hoặc `send` tùy theo nhà quản lý DNS)
   - **Giá trị (Value/Content)**: `v=spf1 include:amazonses.com ~all` (hoặc chuỗi SPF do Resend cung cấp)

### 3.3. Bản ghi DMARC (Domain-based Message Authentication)
DMARC giúp xác thực sự nhất quán của SPF và DKIM. Thêm một bản ghi TXT:
- **Loại (Type)**: `TXT`
- **Tên (Name/Host)**: `_dmarc.send.ue-connect` (hoặc `_dmarc` dưới cấu hình của tên miền phụ `send`)
- **Giá trị (Value/Content)**: `v=DMARC1; p=none;`

---

## 4. Cấu hình Môi trường trong Laravel

Sau khi tên miền được Resend xác nhận trạng thái **Verified**, hãy cập nhật cấu hình môi trường.

### 4.1. Tệp `.env` (Local Development)
Cập nhật các biến cấu hình sau trong tệp `.env` ở local:
```env
MAIL_MAILER=resend
RESEND_API_KEY=re_9bZc45Gd_3hUicCGjkgRRDeWY7nzxLQ5G

# Địa chỉ gửi thư đã được xác minh trên Resend
MAIL_FROM_ADDRESS=no-reply@mail.ueconnect.edu.vn
MAIL_FROM_NAME="UEConnect"
```

### 4.2. Biến môi trường trên Render (Production)
Đăng nhập vào bảng điều khiển Render, truy cập dịch vụ UEConnect của bạn, chọn **Environment** và thiết lập/bổ sung các khoá:
- `MAIL_MAILER` = `resend`
- `RESEND_API_KEY` = `re_9bZc45Gd_3hUicCGjkgRRDeWY7nzxLQ5G`
- `MAIL_FROM_ADDRESS` = `no-reply@mail.ueconnect.edu.vn` (Hoặc địa chỉ email bất kỳ thuộc tên miền đã xác minh).
- `MAIL_FROM_NAME` = `UEConnect`

---

## 5. Kiểm tra Hoạt động (Testing & Verification)

Để đảm bảo hệ thống gửi thư hoạt động chính xác từ Laravel, bạn có thể thực hiện kiểm tra bằng lệnh Artisan:

### 5.1. Sử dụng Artisan Tinker
Mở terminal tại thư mục dự án và chạy:
```bash
php artisan tinker
```
Sau đó gửi một mail thử nghiệm:
```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Chào mừng bạn đến với UEConnect! Đây là email kiểm tra hệ thống gửi thư qua Resend.', function ($message) {
    $message->to('4901104055@student.hcmue.edu.vn')
            ->subject('UEConnect - Kiểm tra gửi thư qua Resend');
});
```
Nếu lệnh chạy thành công và trả về `null` hoặc danh sách người nhận mà không có lỗi (exception), email đã được gửi thành công. Hãy kiểm tra hộp thư nhận để xác thực.
