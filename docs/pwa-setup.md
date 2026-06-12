# Thiết lập PWA cho UEConnect

UEConnect đã được trang bị tính năng Progressive Web App (PWA) giúp người dùng có thể cài đặt ứng dụng web lên màn hình chính (Home Screen) của điện thoại/desktop với trải nghiệm như ứng dụng gốc.

## Các tính năng chính
- **Cài đặt nhanh**: Hỗ trợ hiển thị banner nhắc nhở cài đặt trên Android/iOS và một trang Landing chuyên dụng (`/install`).
- **Offline fallback**: Màn hình hiển thị trạng thái "Ngoại tuyến" khi thiết bị mất mạng.
- **Service Worker caching**: Tối ưu tốc độ tải trang bằng cách lưu trữ các static assets (JS, CSS, fonts, hình ảnh) ở phía client.
- **Maskable Icons**: Đảm bảo biểu tượng ứng dụng tương thích tốt với mọi hình dạng icon trên thiết bị Android hiện đại.

---

## 1. Danh sách các file cấu hình PWA

- `public/manifest.json`: Chứa metadata cho web app (Tên, màu chủ đạo, danh sách icons).
- `public/sw.js`: Service worker xử lý caching, offline mode và tiếp nhận Push Notifications.
- `public/offline.html`: Trang tĩnh hiển thị khi không có kết nối internet.
- `resources/js/pwa.js`: Quản lý việc đăng ký Service Worker và Alpine store `$store.pwa` (xử lý logic `beforeinstallprompt` và tracking sự kiện cài đặt).
- `resources/views/pwa/install.blade.php`: Trang chủ đích (landing page) chuyên biệt cho việc hướng dẫn cài đặt PWA (Truy cập qua URL `/install`).
- `resources/views/components/pwa/install-banner.blade.php`: Banner hiển thị gợi ý cài đặt trên các trang web cho user mới.
- `scripts/generate-pwa-icons.js`: Script hỗ trợ tự động cắt/resize file icon gốc thành nhiều kích cỡ cho manifest.

---

## 2. Cách tạo lại Icons (Khi thay đổi Logo)

Nếu sau này dự án thay đổi file logo gốc (`public/images/brand/app-icon-nobg.png`), bạn có thể dễ dàng chạy lại script để cập nhật tất cả icon cho PWA.

1. Đảm bảo bạn đã cài đặt thư viện `sharp` trong dự án:
   ```bash
   npm install sharp --save-dev
   ```
2. Chạy lệnh:
   ```bash
   node scripts/generate-pwa-icons.js
   ```
Toàn bộ icons trong thư mục `public/icons` sẽ được thay thế bởi phiên bản mới bao gồm cả phiên bản maskable.

---

## 3. Cách Test PWA (Local & Production)

### 3.1 Sử dụng Chrome DevTools (Desktop)
1. Mở trang chủ ứng dụng (hoặc trang `/install`).
2. Mở **Chrome DevTools** (F12) > tab **Application**.
3. Tại phần **Manifest**, đảm bảo mọi thông tin đã hiển thị đúng và không có lỗi (không bị 404 icons).
4. Tại phần **Service workers**, đảm bảo `sw.js` đang ở trạng thái **Activated and is running**. 
5. Bạn có thể check ô **Offline** trong DevTools và reload trang để xem giao diện `offline.html` có hoạt động không.

### 3.2 Lighthouse Audit
1. Mở **Chrome DevTools** > tab **Lighthouse**.
2. Chọn **Progressive Web App** và chạy Audit.
3. Đảm bảo đạt được mục tiêu: *Manifest and service worker meet the installability requirements*.

### 3.3 Test trên Thiết bị thực (Mobile)
> **Lưu ý quan trọng**: PWA chỉ hoạt động khi website được phục vụ qua **HTTPS** hoặc trên **localhost**.

- **Android (Chrome)**: Truy cập trang web, banner nhắc nhở sẽ hiện ra dưới cùng màn hình (nếu chưa từng bấm "Không hiện lại"). Bấm "Cài đặt ngay" sẽ gọi prompt mặc định của Android.
- **iOS (Safari)**: iOS không hỗ trợ tự động hiển thị prompt. Hãy điều hướng tới `/install` và làm theo hướng dẫn Add to Home Screen được trình bày trực quan trên trang.

---

## 4. Lưu ý khi Deploy (Production)

- **HTTPS là bắt buộc**: Service Worker sẽ không đăng ký (register) thành công nếu domain không có chứng chỉ SSL hợp lệ.
- **Không Cache các Route API/Auth**: Hiện tại `sw.js` đã được thiết lập để bỏ qua (pass-through) các request bắt đầu bằng `/api/`, `/admin/` hoặc `/livewire/`. Hãy đảm bảo duy trì cấu trúc này nếu bạn bổ sung thêm middleware mới.
- **Cập nhật bộ nhớ đệm (Cache-Busting)**: Khi update version PWA, hãy vào `public/sw.js` và cập nhật hằng số `CACHE_NAME` (ví dụ từ `ue-connect-pwa-v1` sang `v2`) để kích hoạt chu trình dọn dẹp cache cũ của Service Worker.
