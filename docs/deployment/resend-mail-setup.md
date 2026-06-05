# Hướng Dẫn Cấu Hìn Resend Mail & Cloudflare cho UEConnect

Tài liệu này hướng dẫn cách thay thế SMTP Office 365 bằng **Resend**, cấu hình DNS qua **Cloudflare** cho tên miền mới **`ueconnect.io.vn`** (đăng ký qua Mắt Bão) và Render.

---

## 1. Các bước liên kết tên miền từ Mắt Bão sang Cloudflare
Hiện tại, tên miền `ueconnect.io.vn` đang dùng Name Server mặc định của Mắt Bão (`ns1.matbao.vn`, `ns2.matbao.vn`). Bạn cần đổi sang Cloudflare để quản lý DNS dễ dàng và nhanh chóng:

1. Truy cập [Cloudflare Dashboard](https://dash.cloudflare.com) -> Nhấp **Add a Site** -> Nhập: `ueconnect.io.vn`.
2. Chọn gói **Free** ($0) và nhấn Tiếp tục.
3. Cloudflare sẽ cấp cho bạn một cặp Name Server mới (ví dụ: `adam.ns.cloudflare.com` và `eva.ns.cloudflare.com`).
4. Truy cập trang quản trị **Mắt Bão** -> Vào mục **Quản lý tên miền** -> Chọn tên miền `ueconnect.io.vn` -> Chọn tab **Name Server** -> Nhấp thay đổi Name Server và dán cặp Name Server của Cloudflare vào đó.
5. Đợi vài phút để cập nhật đồng bộ hoàn tất.

---

## 2. Thêm tên miền gửi thư vào Resend
1. Đăng nhập [Resend.com](https://resend.com), chọn **Domains** -> Nhấp **Add Domain**.
2. Ô **Domain Name**, nhập: `send.ueconnect.io.vn` (Sử dụng tên miền phụ `send` chuyên dành cho mail giống như cấu hình cũ của bạn tại `tasket.io.vn`).
3. Chọn Region (ví dụ: `us-east-1` hoặc `ap-northeast-1`).
4. Nhấp **Add**. Resend sẽ cung cấp cho bạn 3 bản ghi DNS bao gồm **DKIM (TXT)**, **SPF (TXT)**, và **MX**.

---

## 3. Cấu hình Bản ghi DNS trên Cloudflare

Truy cập mục **DNS** -> **Records** của tên miền `ueconnect.io.vn` trên Cloudflare và thêm các bản ghi sau:

### 3.1. Bản ghi phục vụ gửi Email qua Resend
1. **Bản ghi DKIM (Xác thực chữ ký số)**:
   - **Type**: `TXT` (hoặc `CNAME` tùy thuộc vào hiển thị trên Resend)
   - **Name**: `resend._domainkey.send` (hoặc `resend._domainkey.send.ueconnect.io.vn`)
   - **Content**: Nhập chuỗi khóa công khai `p=MIGfMA...` do Resend cấp.
   - **Proxy status**: Tắt (DNS only)

2. **Bản ghi SPF (Xác thực máy chủ gửi mail)**:
   - **Type**: `TXT`
   - **Name**: `send` (hoặc `send.ueconnect.io.vn`)
   - **Content**: `v=spf1 include:amazonses.com ~all` (hoặc giá trị SPF do Resend cấp)
   - **Proxy status**: Tắt (DNS only)

3. **Bản ghi MX (Định tuyến phản hồi email)**:
   - **Type**: `MX`
   - **Name**: `send` (hoặc `send.ueconnect.io.vn`)
   - **Mail server**: `feedback-smtp.us-east-1.amazonses.com` (hoặc máy chủ từ Resend)
   - **Priority**: `10`
   - **Proxy status**: Tắt (DNS only)

4. **Bản ghi DMARC (Bảo vệ tên miền chống mạo danh)**:
   - **Type**: `TXT`
   - **Name**: `_dmarc.send` (hoặc `_dmarc.send.ueconnect.io.vn`)
   - **Content**: `v=DMARC1; p=none;`
   - **Proxy status**: Tắt (DNS only)

### 3.2. Bản ghi trỏ Web App về Render
Để chạy ứng dụng chính và API, bạn thêm các bản ghi sau trên Cloudflare:
1. **Bản ghi A (cho tên miền chính)**:
   - **Type**: `A`
   - **Name**: `@` (hoặc `ueconnect.io.vn`)
   - **IPv4 address**: `216.24.57.1` (IP Load Balancer của Render)
   - **Proxy status**: Bật hoặc Tắt (khuyên dùng Tắt - DNS only khi mới xác minh SSL trên Render, sau đó bật lại)

2. **Bản ghi CNAME (cho www)**:
   - **Type**: `CNAME`
   - **Name**: `www` (hoặc `www.ueconnect.io.vn`)
   - **Target**: `ue-connect.onrender.com` (địa chỉ Render của dự án)
   - **Proxy status**: Bật hoặc Tắt

---

## 4. Cấu hình Môi trường trong Laravel

Sau khi các bản ghi DNS trên Cloudflare chuyển sang trạng thái hoạt động và Resend báo màu xanh lá **Verified**, hãy cập nhật cấu hình môi trường của bạn.

### 4.1. Tệp `.env` ở Local
```env
MAIL_MAILER=resend
RESEND_API_KEY=re_9bZc45Gd_3hUicCGjkgRRDeWY7nzxLQ5G

# Sử dụng tên miền phụ đã verify trên Resend
MAIL_FROM_ADDRESS=no-reply@send.ueconnect.io.vn
MAIL_FROM_NAME="UEConnect"
```

### 4.2. Biến môi trường trên Render (Production)
Tại trang quản trị dịch vụ Render -> Chọn mục **Environment** -> Bổ sung/cập nhật các biến:
- `MAIL_MAILER` = `resend`
- `RESEND_API_KEY` = `re_9bZc45Gd_3hUicCGjkgRRDeWY7nzxLQ5G`
- `MAIL_FROM_ADDRESS` = `no-reply@send.ueconnect.io.vn`
- `MAIL_FROM_NAME` = `UEConnect`

---

## 5. Thêm Custom Domain vào Render
1. Trên Render Dashboard -> Vào dịch vụ `ue-connect` -> Chọn mục **Settings** -> Cuộn xuống **Custom Domains**.
2. Nhấp **Add Custom Domain** và điền `ueconnect.io.vn` rồi lưu lại.
3. Nhấp thêm lần nữa và điền `www.ueconnect.io.vn` để hỗ trợ cả hai đường dẫn truy cập.
4. Render sẽ tự động xác minh DNS và cấp chứng chỉ SSL HTTPS miễn phí cho ứng dụng của bạn.
