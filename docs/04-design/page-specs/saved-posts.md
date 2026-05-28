---
title: "Saved Posts Page Specification"
module: "04-design/page-specs"
status: approved
version: "1.0"
last_updated: "2026-05-28"
---

# Saved Posts Page Specification

## 1. Purpose
Trình bày danh sách toàn bộ các bài viết đã được người dùng đánh dấu/lưu lại để xem sau. Hỗ trợ đầy đủ các tính năng tương tác (thích, bỏ thích, bỏ lưu, báo cáo, chỉnh sửa, xóa) tương tự như Trang chủ Bảng tin.

## 2. Layout & Aesthetics
- **Width**: Trực quan hóa dạng cột đơn hẹp lấy cảm hứng từ Threads, giới hạn tối đa `max-w-[640px]` căn giữa màn hình.
- **Header**: Tiêu đề trang nổi bật với biểu tượng `bookmark` màu xanh thương hiệu (`ue-brand`) và đường kẻ chân tinh tế.
- **Empty State**: Khi người dùng chưa lưu bài viết nào, hiển thị hộp thoại trống rỗng thân thiện:
  - Biểu tượng `bookmark` xám nhạt cỡ lớn.
  - Tiêu đề: *Chưa có bài viết đã lưu*
  - Mô tả: *Khi bạn lưu bài viết hữu ích, chúng sẽ xuất hiện tại đây.*
  - Nút chuyển hướng: *Quay lại bảng tin* chỉ về trang Bảng tin chung.

## 3. Scope & Rules
- **Lọc thông tin**: Chỉ hiển thị bài viết được lưu bởi người dùng đang đăng nhập, sắp xếp theo thời gian lưu mới nhất lên đầu (`latest saved first`).
- **Safety Filter**: Loại bỏ hoàn toàn các bài viết có trạng thái bị ẩn, bị xóa bởi kiểm duyệt hoặc chủ sở hữu (`hidden_by_moderation`, `deleted_by_owner`, `deleted_by_moderation`).
- **Idempotency & Reactions**: Đồng bộ đầy đủ các cập nhật thích/lưu thời gian thực. Khi người dùng click bỏ lưu (`unsave`), bài viết sẽ biến mất khỏi danh sách trang Đã lưu một cách mượt mà thông qua hiệu ứng chuyển động mờ dần (`ue-animate-fade-in`).

## 4. UI Smoke & QA Checklist
- [x] Biểu tượng thích (`heart`) và lưu (`bookmark`) hiển thị chính xác trạng thái đã chọn.
- [x] Nút Tùy chọn `more-horizontal` kích hoạt menu thả xuống Alpine.js rõ ràng.
- [x] Touch target cho mọi nút tương tác trên di động đạt tối thiểu 44px.
- [x] Trang hiển thị hoàn hảo ở chế độ prefers-reduced-motion mà không bị Reflow giật lag.
