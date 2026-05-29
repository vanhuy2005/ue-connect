---
title: "Saved Posts Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/post-creation.md"
related_design_docs:
  - "../04-design/feed-architecture.md"
related_system_docs:
  - "../05-system-architecture/feed-service.md"
related_database_docs:
  - "../06-database/posts-table.md"
related_api_docs:
  - "../07-api/posts-api.md"
---

# Trang Bài Viết Đã Lưu (Saved Posts Bookmarks)

## 1. Purpose
Trang Bài Viết Đã Lưu là không gian lưu trữ cá nhân hóa giúp người dùng tập hợp, phân loại và dễ dàng đọc lại tất cả các bài đăng chia sẻ kinh nghiệm học tập, tài liệu ôn thi bổ ích hoặc các thảo luận chuyên sâu từ Bảng tin chung mà họ đã bấm lưu trữ (Bookmark) trước đó trên UEConnect.

## 2. Product Context
Để hỗ trợ sinh viên học tập hiệu quả, tránh bị trôi mất các bài viết chất lượng cao giàu hàm lượng tri thức giữa dòng chảy liên tục của Bảng tin, tính năng Lưu bài viết được thiết kế tinh giản, dễ thao tác theo phong cách Threads nhưng bổ sung khả năng phân loại theo chủ đề học đường tiện lợi.

## 3. User Goals
- Quản lý danh sách toàn bộ các bài viết đã lưu trữ một cách khoa học.
- Lọc bài viết đã lưu theo các nhóm chuyên đề: "Tất cả", "Tài liệu học tập", "Kinh nghiệm hướng nghiệp", "Hoạt động Đoàn hội".
- Hủy lưu trữ nhanh bài viết khi đã đọc xong hoặc không còn nhu cầu tham khảo.
- Chia sẻ nhanh bài viết hay cho bạn bè thông qua phím tắt sao chép liên kết.

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Học viên, sinh viên, cựu sinh viên và giảng viên có nhu cầu lưu trữ và nghiên cứu bài viết hay lâu dài.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Bài viết đã lưu** (Saved Posts).
- Nhấp vào lối tắt "Đã lưu" trên thanh điều hướng cá nhân của trang cá nhân.

## 6. Layout Strategy
Thiết kế tối giản, tập trung hoàn toàn vào nội dung bài đăng gốc, loại bỏ các chi tiết thừa thãi để tối ưu hóa không gian đọc.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 680px) giúp tập trung tầm mắt tốt nhất.
- Bộ lọc ngang phân loại chuyên đề (Horizontal Category Filter) nằm cố định ở đầu trang.
- Các bài viết đã lưu hiển thị xếp chồng dọc nối tiếp nhau mượt mà.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ dàng nhấp chọn.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình mượt mà.
- Các nút tương tác chia sẻ và hủy lưu có kích thước lớn dễ chạm (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh tiêu đề điều khiển (Inbox Header)**:
  - Tiêu đề "Bài viết đã lưu" lớn, kèm số lượng bài viết lưu trữ hiện tại.
- **Thanh phân loại nhanh (Category Tabs)**:
  - Tab "Tất cả", "Học tập", "Hướng nghiệp", "Đời sống sinh viên".
- **Dòng chảy bài viết (Saved Posts Stream)**:
  - Mỗi thẻ bài viết gồm: Ảnh đại diện tác giả, Tên hiển thị, Vị trí khoa chuyên ngành, nội dung bài viết gốc ngắn gọn (trích dẫn rút gọn), biểu tượng ruy băng Bookmark vàng kim rực rỡ ở góc phải trên.
  - Hàng nút tương tác chân thẻ: Thích, Bình luận, Chia sẻ liên kết.

## 8. Core Components
- **Category Filter Tabs**: Hàng nút tròn nhỏ xếp ngang màu xám nhạt, tự động chuyển màu xanh dương đậm khi nhấp chọn lọc chuyên đề học tập.
- **Bookmark ribbon (Active)**: Biểu tượng ruy băng màu vàng kim rực rỡ báo hiệu bài viết đã được lưu trữ an toàn, bấm chọn để hủy lưu mượt mà.
- **Post Share Popover**: Khung danh mục nhỏ mở ra nhanh chóng khi bấm chia sẻ chứa liên kết sao chép đường dẫn bài viết.

## 9. States
### 9.1 Loading
- Dòng chảy hiển thị 3 thẻ bài viết trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu.

### 9.2 Empty
- Khi người dùng chưa lưu trữ bất kỳ bài viết nào:
  - UI Copy: `"Chưa có bài viết đã lưu."`
  - Mô tả UI Copy: `"Mọi bài đăng bổ ích bạn bấm lưu trữ trên Bảng tin chính sẽ xuất hiện ở đây để đọc lại bất cứ lúc nào."`

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi kết nối dữ liệu. Vui lòng nhấp để thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng tương tác hủy lưu và chia sẻ tạm thời bị khóa mờ xám.

### 9.5 Permission Restricted
- Trường hợp bài viết gốc đã bị chính tác giả xóa hoặc bị Admin khóa do vi phạm chính sách kiểm duyệt:
  - Thẻ bài viết hiển thị mờ xám cùng dòng chữ: `"Bài viết này không còn khả dụng hoặc đã bị gỡ bỏ."` kèm nút "Xóa khỏi danh sách đã lưu".

### 9.6 Success / Completed
- Hủy lưu bài viết thành công:
  - Thẻ bài viết biến mất khỏi dòng chảy mượt mà.
  - Hiện Toast thông báo: `"Đã xóa bài viết khỏi danh sách lưu trữ!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ bài viết sẽ làm thay đổi nhẹ màu nền sang màu xám kem dịu (`bg-gray-50`) và làm nổi rõ viền thẻ. Di chuột qua biểu tượng ruy băng Bookmark sẽ hiển thị dòng chữ: `"Hủy lưu bài viết này"`.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút tương tác khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp chọn biểu tượng ruy băng Bookmark sẽ kích hoạt chuyển đổi màu sang xám nhạt mượt mà và thực hiện rút thẻ bài viết khỏi dòng chảy hiển thị.

### 10.4 Optimistic UI
- Khi bấm hủy lưu bài viết, thẻ bài viết lập tức co ngắn lại và biến mất khỏi dòng chảy hiển thị tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hỗ trợ nhấn giữ lâu (Long-press) vào thẻ bài viết trên di động để hiển thị thực đơn thao tác nhanh dạng BottomSheet (Xem bài viết gốc, Sao chép liên kết, Hủy lưu).

### 10.6 Toast / Undo
- Hành động hủy lưu thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh bài viết về vị trí cũ trong danh sách trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng biến mất (Fade-out & Slide-up) của thẻ bài viết khi bị hủy lưu diễn ra trong vòng 200ms cực kỳ mượt mà.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi biểu tượng Bookmark phải có thuộc tính `aria-label` mô tả: `aria-label="Xóa bài viết của [Tên tác giả] khỏi danh sách đã lưu"`.
- Hỗ trợ đầy đủ phím di chuyển bàn phím qua các mục thẻ.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị trích dẫn nội dung bài viết để tránh bị tràn màn hình, chỉ hiển thị tóm tắt ngắn gọn dưới 80 ký tự.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `saved_posts_list` (array of objects: `post_id`, `author_name`, `avatar_url`, `faculty_name`, `body_preview`, `created_at`, `category_type`)
  - `total_saved_count` (integer)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchSavedPosts(categoryFilter)`
  - `toggleSavePostStatus(postId)`
  - `sharePostLink(postId)`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin lưu trữ cá nhân tuyệt đối: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền xem danh sách bài viết đã lưu cá nhân của họ. Mọi truy vấn trái phép từ tài khoản khác đều bị hệ thống chặn và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `saved_posts_page_viewed`: Ghi nhận lượt mở xem danh sách bài viết đã lưu.
- `saved_post_removed`: Ghi nhận sự kiện hủy lưu bài viết cống hiến học thuật.

## 17. Do / Don't
- **Nên làm**: Luôn tự động cập nhật nội dung bài viết đã lưu (số lượt thích, lượt bình luận) theo thời gian thực để người dùng nắm bắt tiến độ thảo luận mới nhất của bài viết.
- **Không được làm**: Cho phép hiển thị lại bài viết đã bị xóa hoàn toàn khỏi cơ sở dữ liệu bởi tác giả gốc; thay vào đó, hãy tự động gỡ bỏ khỏi danh sách đã lưu của tất cả người dùng khác để bảo vệ tính nhất quán thông tin.

## 18. Acceptance Criteria
- Danh sách bài viết đã lưu hiển thị đầy đủ, chính xác theo đúng mốc thời gian lưu trữ thực tế.
- Thao tác hủy lưu và cơ chế "Hoàn tác" hoạt động ổn định, không gây trùng lặp dữ liệu API.
- Bố cục thích ứng sắc nét trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra biểu tượng Bookmark đổi trạng thái chính xác khi bấm hủy lưu.
- [ ] Xác minh tính năng "Hoàn tác" khôi phục đúng bài viết về vị trí cũ trong danh sách.
- [ ] Thử nghiệm bấm nút chia sẻ nhanh và đảm bảo liên kết được sao chép thành công vào bộ nhớ tạm (Clipboard).
- [ ] Đảm bảo các bài viết đã bị tác giả xóa không xuất hiện trong danh mục này.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu đệm (Caching) thông minh cho danh sách bài viết đã lưu cá nhân trong Redis với thời gian hết hạn (TTL) là 10 phút để tối ưu tốc độ tải trang.
- Thiết kế cơ chế phân trang (Pagination) mượt mà hoặc cuộn vô hạn để tối ưu hiệu năng tải trang khi sinh viên có số lượng bài viết đã lưu lớn hơn 30 bài viết.
---
