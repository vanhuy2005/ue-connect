---
title: "Post Detail Page Specification"
module: "04-design/page-specs"
status: approved
version: "1.0"
last_updated: "2026-05-28"
---

# Post Detail Page Specification

## 1. Purpose
Định nghĩa cấu trúc giao diện chi tiết bài viết và luồng thảo luận (Comment/Reply). Trang chi tiết cung cấp một không gian tập trung giúp các UEer tham gia tương tác, trao đổi thông tin sâu sắc theo sơ đồ phân cấp gọn gàng và an toàn.

## 2. Layout & Hierarchy
- **Width**: Cột đơn tối đa `max-w-[640px]` căn giữa đồng bộ với Trang chủ Bảng tin.
- **Back Button**: Nút quay lại bảng tin nổi bật ở đầu trang với biểu tượng `arrow-left`.
- **Original Post**:
  - Hiển thị bài viết gốc với đầy đủ thông tin: Ảnh đại diện tác giả, tên, nhãn xác thực (`check-circle`), nhãn khoa/chuyên ngành (ví dụ: *Student · Khoa Công nghệ thông tin*), thời gian đăng bài tương đối (`diffForHumans`), trạng thái đã chỉnh sửa (`status: edited`).
  - Menu thả xuống Alpine.js (`more-horizontal`) quản lý các hành động Chỉnh sửa/Xóa đối với chủ sở hữu và Báo cáo vi phạm đối với người dùng khác.
- **Comment Composer**:
  - Form viết bình luận công khai hoặc phản hồi bình luận cấp 1.
  - Ô nhập liệu dạng tự co giãn với bộ đếm ký tự giới hạn `0/1000`.
- **Discussion List**:
  - Giao diện thảo luận phân cấp chuẩn Threads: Ảnh đại diện bên trái, nội dung và các nút phản hồi bên phải.
  - Kết nối trực quan: Một đường kẻ dọc mảnh (`border-l border-slate-100`) liên kết ảnh đại diện của bài viết gốc và các bình luận/phản hồi để tạo mạch giao tiếp trực quan.
  - **Giới hạn phản hồi cấp 1**: Bình luận chỉ được lồng tối đa 1 cấp để đảm bảo tính dễ đọc trên màn hình di động hẹp. Không cho phép lồng nhiều cấp sâu hơn.

## 3. Moderation & State Rules
- **Safety Filters**: Chỉ hiển thị các bình luận và phản hồi có trạng thái `PUBLISHED` hoặc `EDITED`. Các nội dung bị ẩn do kiểm duyệt sẽ bị loại bỏ khỏi luồng hiển thị của người dùng thông thường.
- **Deleted Comments Placeholder**:
  - Nếu một bình luận bị xóa (`status: deleted_by_owner` hoặc bị ẩn bởi admin) nhưng **đã có phản hồi con hoạt động**: Thay thế nội dung bình luận bằng thẻ thông báo: *"Bình luận này không còn khả dụng."* với biểu tượng `eye-off` xám nhạt để bảo toàn cấu trúc phân cấp phản hồi.
  - Nếu bình luận bị xóa và **không có phản hồi con**: Ẩn hoàn toàn khỏi danh sách hiển thị.

## 4. UI Polish & QA Checklist
- [x] Lỗi click nút Thích ở chi tiết bài viết tác động đúng lượt thích của Bài viết, không gây xung đột lượt thích bình luận cùng ID.
- [x] Ô nhập liệu chỉnh sửa inline giãn nở tự nhiên và có nút Hủy / Lưu rõ ràng.
- [x] Hộp thoại xác nhận xóa bài viết/bình luận tùy chỉnh (Custom Modal) hiển thị mượt mà với hiệu ứng `ue-animate-scale-in` và lớp phủ mờ `backdrop-blur-xs`.
- [x] Khoảng cách chạm (Touch target) di động đạt tối thiểu 44px.
