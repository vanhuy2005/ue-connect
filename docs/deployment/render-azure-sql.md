# Hướng Dẫn Deploy Laravel + SQL Server (Azure SQL) lên Render qua Docker

Tài liệu này hướng dẫn chi tiết các bước thiết lập cơ sở dữ liệu **Azure SQL Database** và deploy ứng dụng **UEConnect** lên **Render Web Service** bằng **Docker**.

---

## BƯỚC 1: TẠO CSDL AZURE SQL DATABASE

Ứng dụng của bạn sử dụng SQL Server, do đó chúng ta sẽ dùng Azure SQL Database để lưu trữ dữ liệu.

### 1. Tạo Resource trong Azure Portal
1. Truy cập [Azure Portal](https://portal.azure.com/).
2. Chọn **Create a resource** → Tìm kiếm và chọn **SQL Database** (hoặc nhấn **Azure SQL Database** trên sidebar).
3. Điền cấu hình ở tab **Basics**:
   - **Subscription**: Chọn Subscription của bạn.
   - **Resource group**: Chọn hoặc tạo mới (ví dụ: `ue-connect-rg`).
   - **Database name**: Điền `ueconnect`.
   - **Server**: Nhấn *Create new* và cấu hình server mới:
     - **Server name**: Điền một tên duy nhất trên toàn cầu (ví dụ: `ueconnect-sql-vanhuy2005`).
     - **Location**: Chọn **Southeast Asia** (Singapore) hoặc **East Asia** (Hong Kong) để gần với Render Region Singapore, giảm độ trễ (latency).
     - **Authentication method**: Chọn **Use SQL authentication**.
     - **Server admin login**: Điền `ueconnect_admin`.
     - **Password**: Nhập một mật khẩu cực kỳ mạnh.

### 2. Cấu hình Compute + Storage (Free Tier)
1. Tại phần **Compute + storage**, tìm kiếm xem tài khoản của bạn có được áp dụng chương trình **Free database offer** hay không.
2. Nếu có, hãy kích hoạt gói Free (tối đa **100,000 vCore seconds serverless tier** và **32GB storage** miễn phí mỗi tháng).
3. Nếu không có gói Free, hãy cấu hình gói **Serverless** với cấu hình tối thiểu (vCore tối thiểu 0.5) để tối ưu chi phí và tránh bị tính phí cao bất ngờ.
4. > [!CAUTION]
   > Nếu cổng Azure yêu cầu cấu hình trả phí đắt đỏ mà không có tùy chọn Free/Serverless giá rẻ, hãy dừng lại kiểm tra gói Subscription của bạn trước khi bấm Create.

### 3. Thiết lập Mạng & Tường lửa (Networking)
1. Chuyển sang tab **Networking**:
   - **Connectivity method**: Chọn **Public endpoint**.
   - **Allow Azure services and resources to access this server**: Chọn **Yes** (để các dịch vụ của Azure có thể truy cập).
2. **Cấu hình Firewall Rules cho Render**:
   Do Render Free tier không có IP Outbound cố định, để chạy thử nghiệm (Demo/Test), bạn cần cho phép truy cập từ mọi nơi:
   - Thêm quy tắc tường lửa (Firewall rule):
     - **Rule Name**: `AllowAllRender`
     - **Start IP**: `0.0.0.0`
     - **End IP**: `255.255.255.255`
   - > [!WARNING]
     > Việc mở `0.0.0.0/0` là không an toàn cho môi trường Production thực tế. Đây chỉ là giải pháp tạm thời cho demo trên gói dịch vụ Free. Nếu chạy thật, bạn nên cân nhắc sử dụng dịch vụ static outbound IP hoặc mạng riêng tư (Private Link/VPC).

3. Nhấn **Review + create** và đợi Azure khởi tạo xong CSDL.

---

## BƯỚC 2: LẤY THÔNG TIN KẾT NỐI

Sau khi database tạo xong, truy cập vào database:
1. Vào mục **Connection strings** → chọn tab **PHP** hoặc **ODBC**.
2. Trích xuất các thông tin kết nối và điền vào các biến môi trường Laravel sau:

```env
DB_CONNECTION=sqlsrv
DB_HOST=tcp:<tên-server-của-bạn>.database.windows.net
DB_PORT=1433
DB_DATABASE=ueconnect
DB_USERNAME=ueconnect_admin
DB_PASSWORD=<mật-khẩu-đã-đặt>
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false
```

---

## BƯỚC 3: THIẾT LẬP WEB SERVICE TRÊN RENDER

Khi bạn tạo một Web Service mới trên Render:

### 1. Cấu hình cơ bản (Basic Settings)
- **Name**: `ue-connect`
- **Language**: `Docker` (Render không hiển thị sẵn PHP runtime native, Docker giúp ta cài đầy đủ PHP 8.3 + ODBC Driver + SQL Server extension).
- **Branch**: `deploy-render` (hoặc nhánh bạn đẩy code lên).
- **Region**: `Singapore` (để gần cơ sở dữ liệu Azure SQL đã tạo ở Singapore).
- **Root Directory**: Để trống (vì mã nguồn nằm ở root thư mục dự án).
- **Instance Type**: `Free`
- **Dockerfile Path**: `Dockerfile`
- **Docker Build Context**: `.`

### 2. Thiết lập Biến Môi trường (Environment Variables)
Truy cập tab **Environment** trên Render và cấu hình các biến môi trường sau:

| Tên Biến | Giá trị khuyến nghị / Ví dụ | Ghi chú |
| :--- | :--- | :--- |
| `APP_NAME` | `UEConnect` | Tên ứng dụng |
| `APP_ENV` | `production` | Môi trường chạy thực tế |
| `APP_DEBUG` | `false` | Tắt chế độ debug để bảo mật |
| `APP_KEY` | `base64:/756JDsxLYevSog0ahC9sNPshtNj1pIqWFcxzbdtifY=` | Chìa khóa mã hóa (đã gen sẵn) |
| `APP_URL` | `https://ue-connect.onrender.com` | Link Web Service của bạn trên Render |
| `DB_CONNECTION` | `sqlsrv` | Sử dụng SQL Server |
| `DB_HOST` | `tcp:<tên-server>.database.windows.net` | Host của Azure SQL |
| `DB_PORT` | `1433` | Port SQL Server mặc định |
| `DB_DATABASE` | `ueconnect` | Tên CSDL đã tạo |
| `DB_USERNAME` | `ueconnect_admin` | Admin username |
| `DB_PASSWORD` | `<mật-khẩu-của-bạn>` | Mật khẩu cơ sở dữ liệu |
| `DB_ENCRYPT` | `yes` | Bắt buộc mã hóa để kết nối Azure SQL |
| `DB_TRUST_SERVER_CERTIFICATE`| `false` | Khuyến nghị false cho Azure SQL |
| `CACHE_STORE` | `database` | Lưu cache vào database |
| `SESSION_DRIVER` | `database` | Lưu session vào database |
| `QUEUE_CONNECTION` | `sync` | Đồng bộ hàng đợi (Sync) |
| `BROADCAST_CONNECTION` | `log` | Chế độ log WebSocket |
| `MAIL_MAILER` | `log` | Tránh lỗi gửi mail nếu chưa cấu hình SMTP |
| `FILESYSTEM_DISK` | `r2_public` | Lưu trữ file công khai trên Cloudflare R2 |
| `MEDIA_STORAGE_STRATEGY` | `r2_cloudinary` | Kết hợp R2 và Cloudinary |
| `MEDIA_R2_ENABLED` | `true` | |
| `MEDIA_CLOUDINARY_ENABLED` | `true` | |
| `MEDIA_DISK` | `r2_public` | |
| `PRIVATE_MEDIA_DISK` | `r2_private` | |
| `R2_ACCOUNT_ID` | `<cloudflare-r2-account-id>` | ID tài khoản Cloudflare |
| `R2_ACCESS_KEY_ID` | `<access-key-id>` | API Key R2 |
| `R2_SECRET_ACCESS_KEY` | `<secret-access-key>` | API Secret R2 |
| `R2_REGION` | `auto` | |
| `R2_PUBLIC_BUCKET` | `ueconnect-public-media` | |
| `R2_PRIVATE_BUCKET` | `ueconnect-private-media` | |
| `R2_ENDPOINT` | `https://<account-id>.r2.cloudflarestorage.com`| |
| `CLOUDINARY_CLOUD_NAME` | `<cloudinary-cloud-name>` | |
| `CLOUDINARY_API_KEY` | `<cloudinary-api-key>` | |
| `CLOUDINARY_API_SECRET`| `<cloudinary-api-secret>` | |
| `CLOUDINARY_SECURE` | `true` | |

## BƯỚC 4: THỰC THI MIGRATIONS VÀ LỆNH ARTISAN (KHÔNG DÙNG SHELL)

Vì Render gói **Free không hỗ trợ truy cập Shell** (Web shell chỉ mở khi bạn nâng cấp lên gói Starter trở lên), bạn hãy áp dụng các giải pháp thay thế dưới đây để chạy Migration và Seed dữ liệu:

### Giải pháp 1: Tự động chạy Migration khi khởi động (Đã được thiết lập sẵn)
Chúng tôi đã tích hợp lệnh Migration trực tiếp vào câu lệnh khởi chạy trong tệp tin `Dockerfile`:
`CMD php artisan migrate --force && php artisan optimize:clear && ...`

- **Cách hoạt động**: Mỗi khi Render build xong, container khởi động lên hoặc tự động "thức giấc" sau khi ngủ đông, container sẽ tự chạy lệnh `php artisan migrate --force` trước khi boot web server.
- **Ưu điểm**: Hoàn toàn tự động, rảnh tay và an toàn. Nếu cơ sở dữ liệu đã đầy đủ các bảng cũ, lệnh này sẽ tự bỏ qua mà không làm mất dữ liệu.

### Giải pháp 2: Chạy dữ liệu Seeder hoặc các lệnh khác thông qua Web Route tạm thời
Nếu bạn cần chạy dữ liệu mẫu seed (ví dụ: `php artisan db:seed`) hoặc dọn cache thủ công, hãy viết một route tạm thời trong file `routes/web.php` trên máy của bạn:

1. Mở file `routes/web.php` và thêm route này:
   ```php
   use Illuminate\Support\Facades\Artisan;

   Route::get('/run-artisan', function () {
       // Bảo mật route bằng token
       if (request('token') !== 'ueconnect_secret_token_2026') {
           abort(403, 'Unauthorized');
       }

       $command = request('command', 'migrate');
       
       // Chỉ cho phép các lệnh an toàn
       if (!in_array($command, ['migrate', 'db:seed', 'optimize:clear', 'config:clear'])) {
           return 'Lệnh không được hỗ trợ để chạy qua Web.';
       }

       // Thực thi lệnh artisan
       Artisan::call($command, ['--force' => true]);
       return '<pre>' . Artisan::output() . '</pre>';
   });
   ```
2. Commit thay đổi này lên GitHub.
3. Khi Render deploy bản mới thành công, bạn mở trình duyệt truy cập:
   `https://ue-connect.onrender.com/run-artisan?command=db:seed&token=ueconnect_secret_token_2026`
   để chạy Seeder từ xa và xem kết quả xuất ra trực tiếp trên trình duyệt.
4. **Lưu ý bảo mật**: Sau khi thiết lập xong dữ liệu và kiểm tra ứng dụng chạy trơn tru, bạn hãy xóa (hoặc comment) đoạn route này đi và commit lại lên GitHub để tránh lỗ hổng bảo mật.

---

## CÁC HẠN CHẾ CỦA RENDER FREE TIER

> [!IMPORTANT]
> Hãy lưu ý các hạn chế sau của gói Free trên Render để không bất ngờ khi vận hành:
> 1. **Tự động ngủ đông (Spin-down)**: Nếu không có lượt truy cập trong vòng 15 phút, Render sẽ tắt container. Lượt truy cập tiếp theo sẽ mất từ **50 đến 90 giây** để hệ thống khởi động lại container (Cold Start).
> 2. **Bộ nhớ tạm thời (Ephemeral Storage)**: Ổ đĩa local của container trên Render gói Free là ổ đĩa tạm thời. Khi container khởi động lại hoặc rebuild, mọi file tải lên thư mục `storage/` sẽ bị xóa sạch. Do đó, việc cấu hình lưu trữ qua Cloudflare R2 & Cloudinary là bắt buộc.
> 3. **Giới hạn băng thông và phần cứng**: Gói Free có RAM và CPU rất hạn chế (512MB RAM). Việc build Docker image từ đầu (cài dependencies và build CSS/JS) có thể mất từ 5-10 phút.
> 4. **IP động**: Render Web Service gói Free không cung cấp IP tĩnh (Static Outbound IP). Mọi kết nối đến Azure SQL Database đều đi qua IP động, nên bạn bắt buộc phải cấu hình tường lửa Azure SQL mở rộng (`0.0.0.0 - 255.255.255.255`).
