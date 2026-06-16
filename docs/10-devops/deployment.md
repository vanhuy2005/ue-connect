# Hướng dẫn Cấu hình Realtime (Nginx & Supervisor) trên Production

Tài liệu này hướng dẫn cách cấu hình chạy ngầm Reverb và cấu hình Nginx Reverse Proxy trên môi trường Production cho dự án **UEConnect**.

---

## Cách 1: Sử dụng Docker (Khuyên dùng - Cấu hình mặc định của dự án)

Nếu dự án của bạn được triển khai thông qua **Docker trên Render** (theo đúng thiết kế mặc định trong `Dockerfile` và `README.md`):

1. **Không cần cấu hình thủ công:**
   Hệ thống đã tự động cài đặt và chạy các cấu hình này thông qua các file có sẵn trong thư mục `docker/`:
   - [supervisord.conf](file:///c:/laragon/www/ue-connect/docker/supervisord.conf): Đã khai báo chương trình `[program:reverb]` để chạy ngầm và tự khởi động lại Reverb trên cổng `8080`.
   - [nginx.conf.template](file:///c:/laragon/www/ue-connect/docker/nginx.conf.template): Đã cấu hình Nginx tự động phát hiện request WebSocket nâng cấp (Upgrade: websocket) gửi tới đường dẫn `/app/` và proxy ngược về cổng `8080`.
2. **Lưu ý Quan Trọng về SSL:**
   Render tự động xử lý chứng chỉ SSL tại tầng Ingress Load Balancer (cổng 443). Do đó, bạn chỉ cần cấu hình chính xác biến môi trường trên Dashboard của Render.

---

## Cách 2: Triển khai trực tiếp trên VPS Ubuntu (Không dùng Docker)

Nếu bạn deploy trực tiếp ứng dụng lên VPS chạy Ubuntu chạy Nginx và PHP-FPM thông thường, bạn cần thiết lập thủ công Nginx và Supervisor như dưới đây:

### 1. Cấu hình Supervisor để chạy ngầm `reverb:start`

Tạo file cấu hình Supervisor mới trên VPS:
```bash
sudo nano /etc/supervisor/conf.d/ueconnect-reverb.conf
```

Dán nội dung sau vào file:
```ini
[program:ueconnect-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
stopasgroup=true
killasgroup=true
```
*(Thay đổi đường dẫn `/var/www/html` nếu thư mục gốc dự án của bạn nằm ở vị trí khác).*

Sau đó, chạy các lệnh sau để cập nhật Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ueconnect-reverb:*
```

---

### 3. Cấu hình Nginx Reverse Proxy với SSL (HTTPS & WSS)

Mở file cấu hình Nginx của site:
```bash
sudo nano /etc/nginx/sites-available/ueconnect
```

Cấu hình khối `server` hỗ trợ SSL và định tuyến WebSocket về Reverb:
```nginx
server {
    listen 80;
    server_name ueconnect.io.vn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ueconnect.io.vn;

    root /var/www/html/public;
    index index.php index.html;

    charset utf-8;

    # Cấu hình SSL (Certbot / Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/ueconnect.io.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ueconnect.io.vn/privkey.pem;

    # Hỗ trợ cấu hình bảo mật SSL tiêu chuẩn
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Định tuyến WebSocket Reverb
    location ~ ^/app/ {
        error_page 418 = @reverb;
        
        # Nếu là request WebSocket, chuyển hướng tới Reverb
        if ($http_upgrade ~* "websocket") {
            return 418;
        }

        # Nếu không phải WebSocket, đẩy cho Laravel xử lý thông thường
        try_files $uri $uri/ /index.php?$query_string;
    }

    location @reverb {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
    }

    # Định tuyến Laravel thông thường
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Sau khi dán cấu hình, kiểm tra và reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```