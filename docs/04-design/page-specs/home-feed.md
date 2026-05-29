---
title: "Home Feed Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/home-feed.md"
related_design_docs:
  - "../04-design/feed-architecture.md"
related_system_docs:
  - "../05-system-architecture/feed-service.md"
related_database_docs:
  - "../06-database/posts-table.md"
related_api_docs:
  - "../07-api/posts-api.md"
---

# Trang Bảng Tin Chính (Home Feed)

## 1. Purpose
Trang Bảng Tin Chính là trang chủ cốt lõi của UEConnect, nơi sinh viên, cựu sinh viên và giảng viên tiếp cận các tin tức mới nhất, thảo luận học thuật sôi nổi, thông báo quan trọng từ Đoàn trường và đề xuất kết nối đồng môn. Bảng tin được thiết kế tối ưu hóa khả năng đọc nhanh, tương tác gọn nhẹ và giữ chân người dùng thông minh.

## 2. Product Context
Để xây dựng một không gian mạng xã hội học đường chuẩn mực HCMUE nhưng không hề nhàm chán, Home Feed áp dụng triết lý thiết kế tối giản, tập trung tối đa vào nội dung của Threads. Bảng tin kết hợp hài hòa giữa bài đăng học thuật, hoạt động câu lạc bộ phong phú và tin tức chính thống từ trường.

## 3. User Goals
- Cập nhật nhanh chóng các cuộc thảo luận, tài liệu học thuật và tin tức đời sống sinh viên.
- Thích (Like), chia sẻ (Repost) và bình luận nhanh các bài đăng thú vị.
- Lưu trữ (Save/Bookmark) các bài đăng chia sẻ tài liệu ôn thi bổ ích để xem lại sau.
- Ẩn nhanh các bài viết không phù hợp hoặc báo cáo nội dung vi phạm tiêu chuẩn cộng đồng trường.
- Đăng bài viết chia sẻ trạng thái cá nhân ngay lập tức từ thanh soạn thảo nhanh trên đầu bảng tin.

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Sinh viên, giảng viên, cựu sinh viên có nhu cầu tiếp nhận thông tin và giao lưu học thuật thời gian thực.

## 5. Entry Points
- Truy cập trực tiếp địa chỉ gốc của ứng dụng (Trang chủ) sau khi đăng nhập thành công.
- Nhấp chọn biểu tượng **Trang chủ** (Home Icon) trên thanh điều hướng chính.

## 6. Layout Strategy
Áp dụng cấu trúc bố cục tập trung tối đa vào nội dung bài viết (Content-First Spacing), loại bỏ các chi tiết thừa thãi gây nhiễu thị giác.

### 6.1 Desktop Layout
- Bố cục 3 cột kinh điển sắc nét:
  - Cột trái (Sidebar điều hướng): Thanh điều hướng tối giản bo tròn các góc.
  - Cột ở giữa (Main Feed Area): Chiếm 55% chiều rộng, hiển thị dòng bài viết cuộn vô tận. Khung nội dung bài viết có khoảng cách an toàn (Padding) 16px, khoảng cách giữa các thẻ bài đăng (Post Cards) là 12px để tạo độ thoáng đãng tối ưu cho mắt.
  - Cột phải (Widgets Sidebar): Chiếm 25% chiều rộng, hiển thị Bảng tin nhắn nhanh, Đề xuất kết nối bạn học và Lịch sự kiện mini sắp tới.
- Chiều rộng trang tối đa: 1100px.

### 6.2 Tablet Layout
- Tự động ẩn cột bên phải (Widgets Sidebar).
- Cột ở giữa hiển thị toàn màn hình với khoảng cách lề rộng rãi 20px giúp ngón tay dễ dàng nhấp chọn nút tương tác.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền nhẹ.
- Khoảng cách lề trái phải của bài đăng tối ưu hóa còn 12px để nhường diện tích hiển thị tối đa cho nội dung văn bản và hình ảnh đính kèm.
- Các nút tương tác (Thích, Bình luận, Chia sẻ, Lưu) được thiết kế phình to nhẹ (`touch-target 44px`) và xếp hàng ngang mượt mà dưới bài viết.

## 7. Information Architecture
- **Thanh soạn thảo nhanh (Quick Composer Header)**:
  - Ảnh đại diện thu nhỏ, nút kích hoạt mở hộp soạn thảo chính: `"Bạn đang nghĩ gì thế?..."`
- **Dòng bài viết (Feed Card List)**:
  - Thẻ bài viết (Post Card) gồm:
    - Khu vực Tác giả: Ảnh đại diện, Tên hiển thị, Huy hiệu xác thực, Thời gian đăng bài (Dạng rút gọn: `2g`, `15ph`).
    - Khu vực Nội dung: Văn bản bài đăng (Markdown hỗ trợ), Hình ảnh đính kèm (Lưới 1-4 ảnh mượt mà).
    - Hàng nút tương tác: Thích (Like), Bình luận (Comment), Đăng lại (Repost), Lưu trữ (Save).
    - Dòng phản hồi: Số lượt thích và số bình luận (Ví dụ: `15 thích • 4 phản hồi`).

## 8. Core Components
- **PostCard**: Thẻ bài viết tinh tế sử dụng màu nền trắng tinh khiết (`bg-white` hoặc `dark:bg-zinc-950`) với viền mỏng xám nhạt (`border-gray-100`).
- **Interaction Row**: Hàng nút tương tác biểu tượng (Icons) tối giản, đổi màu sắc sinh động khi kích hoạt (Thích chuyển sang màu đỏ trái tim, Lưu chuyển sang màu xanh dương).
- **Post Option Popover / BottomSheet**: Hộp thoại mở ra khi bấm vào biểu tượng ba chấm góc phải bài viết chứa các thao tác: "Ẩn bài viết", "Lưu bài đăng", "Sao chép liên kết", "Báo cáo nội dung".
- **Skeleton Feed**: Khung xương giả lập cấu trúc bài viết (Avatar tròn, thanh chữ nhật dài ngắn khác nhau) nhấp nháy chuyển động 1.5s chu kỳ trong lúc chờ tải dữ liệu.

## 9. States
### 9.1 Loading (Skeleton Feeds)
- Khi vừa mở trang hoặc cuộn xuống cuối trang để tải thêm bài viết (Infinite Scroll), hiển thị 3 thẻ bài viết trống dạng Skeleton Feed mượt mà để duy trì cấu trúc giao diện ổn định, không bị giật màn hình.

### 9.2 Empty
- Trường hợp không có bài viết nào trên bảng tin (Ví dụ: Lọc tab "Bạn bè" khi chưa kết nối với ai):
  - UI Copy: `"Bảng tin đang trống."`
  - Mô tả UI Copy: `"Hãy kết nối thêm nhiều bạn bè và tham gia các Câu lạc bộ để cập nhật nhiều tin tức bổ ích nhé!"`

### 9.3 Error
- Lỗi tải bài viết do mất kết nối mạng đột ngột:
  - UI Copy: `"Đã xảy ra lỗi khi kết nối dữ liệu bảng tin. Vui lòng nhấp để tải lại."`

### 9.4 Offline / Reconnecting (Error Rollback Toasts)
- Hiển thị Toast thông báo đỏ cam ở cuối màn hình: `"Mất kết nối mạng. Không thể cập nhật bảng tin."`
- Mọi hành động tương tác (Like, Save) diễn ra trong lúc ngoại tuyến bị lỗi sẽ tự động hoàn trả trạng thái cũ (Rollback) kèm thông báo Toast: `"Thao tác thất bại. Đã khôi phục trạng thái."`

### 9.5 Permission Restricted
- Trường hợp bài đăng thuộc nhóm CLB riêng tư hoặc tài khoản của tác giả đã chuyển sang chế độ riêng tư (Private Account) mà người dùng chưa theo dõi:
  - Hiển thị thẻ bài viết trống kèm biểu tượng khóa mờ cùng dòng chữ: `"Bài viết này thuộc tài khoản riêng tư."`

### 9.6 Success / Completed
- Dữ liệu tải xong hoàn chỉnh, tự động cập nhật các bài đăng mới nhất theo thuật toán dòng thời gian thông minh (Chronological with high weight on connections).

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua tên tác giả sẽ hiển thị danh thiếp thông tin thu nhỏ (Hover Card Card Preview) sau 500ms chứa ảnh đại diện lớn, thông tin ngành học và nút kết nối nhanh.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh đậm thương hiệu bao quanh các thẻ bài viết và các nút hành động khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap (Immediate Like/Save Optimistic Feedback)
- Khi bấm nút **Thích (Like)**: Trái tim lập tức nảy nhẹ (Pulse animation) và chuyển sang màu đỏ rực rỡ, đồng thời số lượt thích hiển thị tăng lên 1 đơn vị ngay lập tức mà không đợi API xác nhận từ máy chủ.
- Khi bấm nút **Lưu (Save)**: Biểu tượng lưu chuyển ngay sang màu xanh dương thẫm chứa đầy.
- Thao tác nhấn giữ bài viết mở nhanh thực đơn hành động.

### 10.4 Optimistic UI
- **Quick Hide Action (Ẩn nhanh bài viết)**: Khi bấm nút "Ẩn bài viết" từ menu ba chấm, thẻ bài viết đó lập tức trượt co lại chiều cao về 0px và biến mất khỏi màn hình trong vòng 150ms. Hiển thị một dòng thay thế siêu mỏng: `"Đã ẩn bài đăng này."` kèm nút "Hoàn tác" (Undo).

### 10.5 Menu / Sheet (Popover/Sheet Post Menus)
- Trên Desktop: Bấm nút ba chấm mở Popover menu nhỏ thả xuống sát cạnh biểu tượng ba chấm.
- Trên Mobile: Mở BottomSheet trượt lên từ đáy màn hình chiếm 40% chiều cao, chứa các nút bấm hành động lớn xếp dọc trực quan giúp tay dễ bấm.

### 10.6 Toast / Undo (Error Rollback Toasts)
- Nếu hành động Thích hoặc Ẩn bài đăng bị lỗi do đường truyền mạng chập chờn, hệ thống tự động hoàn trả (Rollback) trạng thái hiển thị của nút bấm về ban đầu tức thì trong vòng 200ms và hiển thị Toast cảnh báo lỗi màu đỏ dịu mắt: `"Không thể lưu tương tác. Đã hoàn tác."`

### 10.7 Motion
- Hoạt ảnh tim nảy (Heart pop scale) sử dụng thuộc tính `transform: scale(1.3)` trượt về `scale(1)` trong 200ms cực kỳ bắt mắt.
- Hiệu ứng trượt kéo để làm mới bảng tin (Pull-to-refresh) trên Mobile có vòng tròn tiến trình (Spinner) trượt mượt mà theo tay người dùng.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nút tương tác đều đi kèm nhãn mô tả chi tiết: `aria-label="Thích bài viết của @username"`, `aria-label="Lưu bài viết"`.
- Hỗ trợ phím nóng `J` để di chuyển xuống bài viết tiếp theo và `K` để quay lại bài viết phía trên (tương tự Twitter/Threads).

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Ảnh đính kèm bài đăng tự động căn giãn tràn chiều ngang màn hình, các nút tương tác sắp xếp thưa hơn để tránh bấm nhầm nút bên cạnh.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `posts_stream` (array of objects: `id`, `author`, `content`, `images`, `like_count`, `comment_count`, `is_liked`, `is_saved`, `created_at`)
  - `page_offset` (integer, default: 0)

## 14. API / Action Requirements
- Gọi Livewire / Real-time Action:
  - `toggleLikePost(postId)`
  - `toggleSavePost(postId)`
  - `hidePostFromFeed(postId)`
  - `loadMorePosts()`

## 15. Authorization / Privacy Rules
- Bảo mật quyền riêng tư: Người dùng không thể xem được các bài đăng thuộc nhóm học tập kín hoặc của tài khoản riêng tư mà họ chưa được phê duyệt kết nối. Mọi truy vấn trái phép qua ID bài đăng đều bị chuyển hướng về trang lỗi 403.

## 16. Analytics / Audit Events
- `home_feed_viewed`: Ghi nhận mỗi lần người dùng mở xem bảng tin chính.
- `post_interaction_performed`: Ghi nhận loại tương tác (Like, Share, Save) cùng mã bài viết tương ứng để tối ưu hóa thuật toán đề xuất nội dung.

## 17. Do / Don't
- **Nên làm**: Thiết lập khoảng cách dòng văn bản bài đăng (Line-height) tối ưu ở mức 1.5 để sinh viên đọc các bài viết học thuật dài không bị mỏi mắt.
- **Không được làm**: Tự động phát âm thanh của các video đính kèm bài viết khi vừa cuộn trang qua. Video phải luôn ở trạng thái tắt tiếng mặc định (Muted default) cho đến khi người dùng nhấp chọn bật âm thanh.

## 18. Acceptance Criteria
- Bảng tin cuộn vô tận (Infinite Scroll) hoạt động mượt mà, không bị lặp bài viết hoặc tải trùng lặp dữ liệu.
- Phản hồi tương tác Thích/Lưu (Optimistic UI) diễn ra tức thời không có độ trễ thị giác.
- Cơ chế khôi phục trạng thái khi lỗi mạng (Rollback) hoạt động ổn định và hiển thị đúng thông báo Toast.

## 19. QA / UAT Checklist
- [ ] Thử nghiệm tương tác Thích/Lưu khi tắt kết nối mạng để kiểm tra cơ chế Rollback hoạt động đúng đắn.
- [ ] Kiểm tra tính năng "Ẩn bài viết" và "Hoàn tác ẩn" xem bài viết có hiển thị lại đúng vị trí cũ không.
- [ ] Xác minh Skeleton Feed hiển thị chuẩn cấu trúc lưới trong lúc tải thêm bài viết khi cuộn xuống dưới cùng.
- [ ] Thử nghiệm kéo để làm mới (Pull-to-refresh) trên các thiết bị di động Android và iOS.

## 20. AI Agent Implementation Notes
- Tận dụng Livewire 4 với thuộc tính `wire:stream` hoặc `Lazy loading` để tải dần nội dung bài viết giúp tăng tốc độ phản hồi trang đầu tiên cực nhanh (Time to First Byte dưới 150ms).
- Sử dụng bộ đệm (Caching) thông minh cho các bài đăng nổi bật bằng Redis và thiết lập thuật toán phân phối bài viết cá nhân hóa nhẹ dựa trên danh sách bạn bè kết nối hiện tại của sinh viên.
---