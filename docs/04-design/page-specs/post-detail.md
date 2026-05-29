---
title: "Post Detail Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/post-details-replies.md"
related_design_docs:
  - "../04-design/feed-architecture.md"
related_system_docs:
  - "../05-system-architecture/feed-service.md"
related_database_docs:
  - "../06-database/posts-table.md"
related_api_docs:
  - "../07-api/posts-api.md"
---

# Trang Chi Tiết Bài Đăng & Chuỗi Phản Hồi (Post Detail & Replies)

## 1. Purpose
Trang Chi Tiết Bài Đăng & Chuỗi Phản Hồi hiển thị toàn bộ nội dung của một bài viết cụ thể, bao gồm bài viết gốc (Parent Post), chuỗi bình luận lồng nhau phân cấp (Nested Replies) và cung cấp trình soạn thảo bình luận nhanh tự động giãn nở (Auto-expanding Comment Composer) để thúc đẩy các cuộc thảo luận chuyên sâu có tính tương tác cao.

## 2. Product Context
Nhằm duy trì cấu trúc thảo luận học đường mạch lạc của HCMUE và tối ưu tương tác xã hội nhẹ nhàng của Threads, giao diện bài đăng chi tiết sử dụng dòng kẻ chỉ dẫn phân cấp đứng bên trái (Vertical Left Rails) giúp sinh viên dễ dàng theo dõi nguồn gốc của từng phản hồi phụ mà không bị rối mắt.

## 3. User Goals
- Đọc trọn vẹn nội dung bài đăng gốc cùng các hình ảnh/tài liệu học tập chất lượng cao đính kèm.
- Xem chuỗi thảo luận, tranh biện học thuật xếp tầng phân cấp rõ rệt dưới bài viết.
- Gửi bình luận phản hồi nhanh chóng mà không cần chuyển hướng trang.
- Tập trung phản hồi nhanh vào một bình luận cụ thể qua tính năng kích hoạt tiêu điểm phản hồi (Reply-Focus Behavior).

## 4. Primary Users
- **Toàn bộ thành viên UEConnect**: Sinh viên, giảng viên và cựu sinh viên có nhu cầu nghiên cứu học tập, trao đổi sâu về các chủ đề học thuật hoặc phong trào trường.

## 5. Entry Points
- Nhấp trực tiếp vào vùng nội dung hoặc liên kết `"X phản hồi"` dưới thẻ bài đăng bất kỳ trên Bảng tin chính.
- Nhấp chọn thông báo tương tác bình luận mới từ trang Thông báo hệ thống.

## 6. Layout Strategy
Thiết kế tối ưu tính phân cấp thông tin dọc, giúp người dùng dễ dàng quét mắt từ bài đăng chính xuống các nhánh phản hồi phụ.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột tinh tế (chiều rộng tối đa 700px).
- Bài viết gốc (Parent Post) hiển thị với phông chữ lớn hơn 10% so với bảng tin thông thường để làm nổi bật tâm điểm thảo luận.
- Phía dưới là Dòng kẻ phân cấp màu xám nhạt nối liền từ avatar bài đăng gốc xuống các thẻ bình luận con.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng rãi 20px giúp ngón tay dễ chạm bấm.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền mượt mà.
- Trình soạn thảo bình luận nhanh (Comment Composer) hiển thị ở dạng thanh cố định sát đáy màn hình (Sticky Bottom Input), tự động trượt lên trên bàn phím ảo khi người dùng nhấp chọn.
- Đường ray phân cấp đứng bên trái (Left Rails) được thiết kế thanh mảnh (1px màu `#E5E7EB`) để không lấn át không gian chữ của màn hình điện thoại.

## 7. Information Architecture
- **Khu vực Bài viết gốc (Parent Post Area)**:
  - Hiển thị đầy đủ nội dung bài viết, hình ảnh lưới lớn, tệp tài liệu tải xuống.
  - Hàng nút tương tác lớn (Thích, Đăng lại, Lưu).
- **Trình soạn thảo bình luận (Comment Composer Box)**:
  - Hộp nhập liệu tự động giãn nở chiều cao (Auto-expanding textarea) tích hợp nút chèn ảnh và nút "Gửi" nổi bật.
- **Chuỗi bình luận xếp tầng (Nested Replies Thread)**:
  - Thẻ bình luận con (Comment Item) gồm: Ảnh đại diện thu nhỏ, Tên người bình luận, nội dung bình luận, nút phản hồi phụ, thời gian đăng.
  - Đường ray đứng bên trái (Vertical Left Rails) liên kết trực quan các bình luận lồng nhau.

## 8. Core Components
- **Vertical Left Rails**: Đường kẻ đứng mảnh 1px màu xám dịu mắt kết nối từ vị trí ảnh đại diện cha xuống ảnh đại diện con để chỉ rõ cấp bậc của bình luận phụ.
- **Auto-expanding Comment Composer**: Trình nhập bình luận tự động điều chỉnh tăng chiều cao theo số lượng dòng văn bản gõ vào, không tạo thanh cuộn nội bộ khó chịu.
- **Reply Focus Highlighter**: Hiệu ứng làm sáng mờ màu nền của bình luận đích được nhấp phản hồi để người dùng biết rõ mình đang trả lời ai.
- **Parent Post Card**: Thẻ bài đăng gốc hiển thị trang nghiêm nổi bật ở đầu trang.

## 9. States
### 9.1 Loading
- Hiển thị bài đăng gốc đầy đủ, các chuỗi bình luận phía dưới được giả lập bằng 3 dòng Skeleton Shimmer nhấp nháy tuần hoàn.

### 9.2 Empty
- Khi bài đăng gốc chưa có bình luận nào:
  - UI Copy: `"Chưa có bình luận nào."`
  - Mô tả UI Copy: `"Hãy là người đầu tiên khởi đầu cuộc thảo luận bằng cách viết bình luận bên dưới nhé!"`

### 9.3 Error
- Lỗi tải chuỗi bình luận do rớt mạng:
  - UI Copy: `"Không thể tải danh sách bình luận. Vui lòng tải lại trang."`

### 9.4 Offline / Reconnecting
- Ô nhập bình luận bị khóa mờ (disabled) và hiển thị thông báo: `"Bạn đang ngoại tuyến. Không thể gửi bình luận lúc này."`

### 9.5 Permission Restricted
- Trường hợp tác giả bài viết giới hạn quyền bình luận (chỉ cho bạn bè hoặc thành viên kết nối bình luận):
  - Ô soạn thảo bình luận biến mất, thay bằng dòng chữ mỏng: `"Tác giả đã giới hạn quyền bình luận cho bài đăng này."`

### 9.6 Success / Completed
- Bình luận gửi đi thành công:
  - Bình luận mới lập tức trượt mượt mà vào vị trí đầu tiên của chuỗi phản hồi dưới bài viết gốc.
  - Ô nhập bình luận tự động xóa sạch chữ và co chiều cao về kích thước 1 dòng mặc định.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua đường ray phân cấp đứng bên trái (Left Rails) sẽ làm đổi màu đường ray sang màu xanh dương nhạt để báo hiệu trực quan kết nối cha con của luồng thảo luận.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh đậm thương hiệu bao quanh ô nhập bình luận và các nút tương tác khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap (Reply-Focus Behavior)
- Khi sinh viên nhấp chọn nút **"Phản hồi"** trên một bình luận con bất kỳ:
  - Màn hình tự động cuộn nhẹ (Scroll-to-view) đưa ô nhập bình luận chính vào tiêu điểm.
  - Bình luận con đích được làm nổi bật bằng cách chuyển sang màu nền vàng nhạt ấm áp (`bg-amber-50`) biến mất dần trong 2 giây (Fade-out highlight) để định vị thị giác rõ ràng cho người dùng.
  - Trên ô nhập bình luận hiển thị nhãn nhỏ: `"Đang trả lời @username"` kèm nút X để hủy nhanh.

### 10.4 Optimistic UI
- Khi bấm gửi bình luận, chèn ngay bình luận mới đó vào chuỗi hiển thị dưới bài viết gốc tức thì với độ mờ 40% trước khi máy chủ xác nhận lưu thành công để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hộp thoại ba chấm trên mỗi bình luận con mở ra BottomSheet mượt mà trên Mobile chứa các tùy chọn: (Sao chép văn bản bình luận, Báo cáo bình luận vi phạm chính sách học đường, Xóa bình luận nếu là chủ sở hữu).

### 10.6 Toast / Undo
- Xóa bình luận thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh bình luận trong vòng 3 giây.

### 10.7 Motion
- Hoạt ảnh co giãn chiều cao của trình soạn thảo bình luận diễn ra mượt mà không bị giật khung hình (`transition-all duration-200 ease-out`). Bình luận mới xuất hiện trượt từ dưới lên nhẹ nhàng.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Ô nhập bình luận tự động giãn nở có thuộc tính `aria-label="Viết bình luận của bạn"`.
- Hỗ trợ phím nóng `Escape` để hủy nhanh trạng thái Phản hồi cụ thể (Reply-Focus) và đưa ô nhập về chế độ bình luận bài đăng chính mặc định.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Thu nhỏ khoảng thụt lề (Indentation padding) của bình luận con cấp 2 xuống còn tối đa 16px để tránh bài viết bị dồn ép quá mức về phía lề phải làm vỡ bố cục chữ hiển thị trên màn hình hẹp.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `post_id` (integer, primary)
  - `parent_post` (object: `id`, `author`, `content`, `media`, `like_count`)
  - `replies_tree` (nested array of objects: `reply_id`, `parent_id`, `author`, `content`, `created_at`, `children`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadPostDetails(postId)`
  - `submitComment(postId, parentCommentId, commentContent)`
  - `deleteComment(commentId)`
  - `toggleLikeComment(commentId)`

## 15. Authorization / Privacy Rules
- Bảo mật quyền truy cập bài đăng: Sinh viên không thể xem chi tiết bài đăng và bình luận thuộc các nhóm học tập kín hoặc của tài khoản riêng tư mà họ chưa được phê duyệt kết nối trước đó.

## 16. Analytics / Audit Events
- `post_detail_viewed`: Ghi nhận lượt xem trang chi tiết bài đăng.
- `comment_submitted`: Ghi nhận sự kiện gửi bình luận thành công kèm mã bài đăng và mã bình luận cha tương ứng.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị nhãn `"Đang trả lời @username"` rõ ràng trên trình soạn thảo khi người dùng kích hoạt tiêu điểm phản hồi để tránh viết nhầm ngữ cảnh thảo luận.
- **Không được làm**: Cho phép lồng bình luận con vô hạn cấp độ trên di động. Giới hạn tối đa 3 cấp độ lồng nhau (Parent -> Comment -> Sub-comment), các phản hồi sâu hơn sẽ được xếp bằng hàng với bình luận cấp 3 và hiển thị nhãn `@mention` chỉ hướng để tránh vỡ giao diện dọc di động.

## 18. Acceptance Criteria
- Hiển thị bài đăng gốc đầy đủ, chính xác cùng chuỗi bình luận lồng nhau phân cấp đúng sơ đồ đường ray kết nối bên trái.
- Trình nhập bình luận tự động giãn nở chiều cao mượt mà theo nội dung gõ thực tế và co lại đúng kích thước ban đầu sau khi gửi thành công.
- Tính năng Reply-Focus hoạt động chuẩn xác, cuộn màn hình và làm nổi bật màu nền bình luận đích mượt mà.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng tự động giãn nở của ô bình luận khi gõ đoạn văn dài trên 10 dòng chữ.
- [ ] Xác minh tính năng Reply-Focus cuộn màn hình và hiển thị đúng nhãn `"Đang trả lời"` trên các thiết bị di động.
- [ ] Thử nghiệm gửi bình luận và kiểm tra xem bình luận mới có trượt vào đúng phân nhánh con của bình luận cha không.
- [ ] Kiểm tra hiển thị đường kẻ phân cấp đứng bên trái (Left Rails) rõ ràng, sắc nét trên cả giao diện sáng (Light) và tối (Dark Mode).

## 20. AI Agent Implementation Notes
- Sử dụng mô hình cấu hình đệ quy (Recursive Blade Component) trong Laravel Livewire để render chuỗi bình luận lồng nhau một cách sạch sẽ, dễ bảo trì nhất.
- Tận dụng Alpine.js để tính toán chiều cao tự động cho ô nhập liệu textarea phía client nhằm loại bỏ hoàn toàn độ trễ hiển thị và giật lag giao diện khi sinh viên gõ phím nhanh.
---
