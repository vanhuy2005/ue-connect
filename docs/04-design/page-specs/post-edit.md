---
title: "Post Edit Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/post-creation.md"
related_design_docs:
  - "../04-design/editor-interactions.md"
related_system_docs:
  - "../05-system-architecture/media-storage.md"
related_database_docs:
  - "../06-database/posts-table.md"
related_api_docs:
  - "../07-api/posts-api.md"
---

# Trang Chỉnh Sửa Bài Viết (Post Edit)

## 1. Purpose
Trang Chỉnh Sửa Bài Viết cung cấp giao diện trực quan cho phép tác giả bài viết thay đổi nội dung văn bản, cập nhật nhãn chủ đề (Hashtags), điều chỉnh quyền riêng tư, chỉnh sửa hoặc xóa bớt các phương tiện hình ảnh/tài liệu đã đính kèm trước đó trong toàn hệ thống UEConnect.

## 2. Product Context
Để đảm bảo tính nhất quán và văn minh của thông tin học đường HCMUE, tính năng chỉnh sửa bài viết được thiết kế tinh giản, dễ thao tác và ghi nhận vết chỉnh sửa một cách minh bạch nhằm tránh các trường hợp thay đổi nội dung tiêu cực sau khi đã có nhiều sinh viên tương tác.

## 3. User Goals
- Sửa nhanh các lỗi chính tả, câu từ trong bài viết đã đăng tải.
- Xóa bớt hình ảnh đính kèm cũ hoặc thay thế tài liệu đính kèm ôn tập mới.
- Thay đổi đối tượng người xem (Quyền riêng tư) bài đăng khi cần thiết.
- Hủy bỏ các thay đổi và khôi phục nội dung gốc an toàn nếu đổi ý.

## 4. Primary Users
- **Tác giả bài viết**: Người dùng (Sinh viên, Giảng viên, Cựu sinh viên) đã đăng tải bài viết và muốn chỉnh sửa thông tin của mình.

## 5. Entry Points
- Nhấp chọn **Chỉnh sửa bài viết** (Edit Post) từ danh mục tùy chọn ba chấm (Post Menu) ở góc phải của bài viết thuộc quyền sở hữu của người dùng.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật nội dung cũ cần chỉnh sửa, giao diện tinh giản gần gũi với trình soạn thảo gốc (Composer).

### 6.1 Desktop Layout
- Hiển thị dưới dạng một hộp thoại nổi ở chính giữa màn hình (Modal Popup) đè lên trên nền mờ tối để người dùng tập trung hoàn toàn.
- Kích thước chiều rộng cố định: 560px.
- Khoảng cách lề: 20px.

### 6.2 Tablet Layout
- Tương tự Desktop, chiều rộng mở rộng 600px để người dùng dễ thao tác trên màn hình cảm ứng lớn.

### 6.3 Mobile / PWA Layout
- Mở rộng hiển thị toàn màn hình (Full-screen Overlay) để tối đa hóa không gian soạn thảo trên bàn phím ảo di động.
- Nút "Lưu thay đổi" (Save Changes) nằm ở góc phải trên cùng thanh tiêu đề. Nút "Hủy" (Cancel) ở góc trái trên cùng để tay dễ chạm bấm.

## 7. Information Architecture
- **Khu vực Thông tin tác giả (Author Header Row)**:
  - Ảnh đại diện, Tên hiển thị người dùng.
  - Hộp thả xuống chọn đối tượng người xem (Quyền riêng tư).
- **Khung soạn thảo văn bản (Text Editor Area)**:
  - Ô nhập liệu văn bản tự động co giãn chiều cao hiển thị nội dung cũ sẵn có của bài viết.
  - Bộ đếm số lượng ký tự còn lại dạng vòng tròn tiến độ.
- **Lưới xem trước ảnh & tài liệu đính kèm (Attachments List)**:
  - Hiển thị các hình ảnh/tài liệu đang đính kèm bài viết kèm nút bấm "X" nhỏ màu đỏ ở góc phải trên cùng mỗi ảnh để người dùng xóa bớt mượt mà.

## 8. Core Components
- **Auto-expanding Editor Textarea**: Ô nhập liệu văn bản tự động giãn nở chiều cao theo độ dài ký tự sửa đổi.
- **Save Changes Button**: Nút "Lưu" màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`) hoặc chuyển sang màu xám mờ nếu chưa có thay đổi nào so với nội dung cũ.
- **Cancel Button**: Nút "Hủy" màu xám trung tính dạng chữ trơn hoặc nút đóng chéo (X).
- **Attachment Remover Icon**: Nút xóa nhanh hình ảnh đính kèm dạng hình tròn nhỏ màu đen bán trong suốt chứa chữ chéo trắng nổi đè lên góc ảnh.

## 9. States
### 9.1 Loading
- Khi bấm "Lưu", nút chuyển sang trạng thái mờ, hiển thị vòng tròn xoay tải dữ liệu và vô hiệu hóa khả năng nhấp chọn để tránh lưu trùng lặp.

### 9.2 Empty
- Trường hợp người dùng xóa sạch toàn bộ nội dung bài viết và không đính kèm phương tiện nào:
  - Nút "Lưu" bị vô hiệu hóa (disabled). Hiển thị dòng cảnh báo: `"Nội dung bài viết không được để trống."`

### 9.3 Error
- **Lỗi lưu bài viết**: Lưu bài đăng thất bại do lỗi đường truyền:
  - UI Copy: `"Không thể lưu thay đổi lúc này. Vui lòng kiểm tra kết nối mạng."`
- **Lỗi vi phạm từ cấm**: Bài viết sửa đổi chứa từ ngữ thô tục vi phạm bộ lọc của trường:
  - UI Copy: `"Bài viết chứa từ ngữ không phù hợp. Vui lòng điều chỉnh lại câu từ."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo ngoại tuyến ở đầu trình soạn thảo. Vô hiệu hóa nút "Lưu thay đổi" và hiển thị ở trạng thái mờ xám.

### 9.5 Permission Restricted
- Người dùng cố tình chỉnh sửa bài viết của người khác bằng cách thay đổi ID bài viết trên đường dẫn API:
  - Hệ thống chuyển hướng về trang lỗi 403: `"Bạn không có quyền chỉnh sửa bài viết này."`

### 9.6 Success / Completed
- Lưu thay đổi thành công:
  - Hộp thoại chỉnh sửa đóng lại với hiệu ứng thu nhỏ mượt mà.
  - Bài viết trên Bảng tin lập tức được cập nhật nội dung mới thời gian thực kèm nhãn nhỏ `"Đã chỉnh sửa"` bên cạnh mốc thời gian đăng.
  - Hiện Toast thông báo: `"Đã cập nhật bài viết của bạn thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua nút "Xóa ảnh" sẽ làm nút đổi sang màu đỏ rực rỡ và phóng to nhẹ để tay dễ bấm chọn.

### 10.2 Focus
- Ô nhập liệu tự động nhận tiêu điểm (Auto-focus) ngay khi hộp chỉnh sửa mở ra và di chuyển con trỏ chuột xuống dưới cùng của nội dung văn bản cũ.

### 10.3 Press / Tap
- Khi nhấn nút "Hủy" trong khi người dùng đã có thay đổi nội dung so với ban đầu:
  - Hiển thị hộp thoại phụ xác nhận (Unsaved Changes Dialog): `"Hủy bỏ thay đổi? Những chỉnh sửa của bạn sẽ không được lưu."` với tùy chọn `"Tiếp tục sửa"` (màu xanh) và `"Hủy bỏ"` (màu đỏ).

### 10.4 Optimistic UI
- Sau khi bấm "Lưu thay đổi" thành công, cập nhật ngay nội dung mới của bài viết lên giao diện Bảng tin của chính người dùng trước khi nhận phản hồi xác thực lưu hoàn tất từ máy chủ.

### 10.5 Menu / Sheet
- Hộp thoại cảnh báo hủy bỏ thay đổi mở ra BottomSheet mượt mà dưới đáy màn hình di động dễ bấm chọn.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động lưu chỉnh sửa để đảm bảo tính nhất quán của dữ liệu cơ sở dữ liệu.

### 10.7 Motion
- Hiệu ứng biến mất trượt mượt mà của thẻ ảnh khi người dùng bấm nút xóa đính kèm trong vòng 150ms. Hoạt ảnh mở Modal diễn ra trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Hộp thoại chỉnh sửa hỗ trợ phím đóng nhanh `Escape` (nếu chưa có thay đổi nội dung thì đóng ngay; nếu có thay đổi thì mở hộp thoại cảnh báo).
- Đầy đủ nhãn `aria-label` cho nút xóa đính kèm.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Trình chỉnh sửa mở rộng 100% diện tích chiều ngang và dọc màn hình để tay dễ thao tác và bàn phím ảo hiển thị thoải mái nhất.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `post_id` (integer, primary)
  - `edited_content` (text, max: 500 characters)
  - `edited_privacy` (enum: `public`, `connections`, `private`)
  - `remaining_media_ids` (array of integers)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadPostForEditing(postId)`
  - `updatePostContent(postId, content, privacy, remainingMedia)`
  - `removeAttachment(mediaId)`

## 15. Authorization / Privacy Rules
- Quyền sở hữu nghiêm ngặt: Chỉ chính tác giả bài viết mới có quyền gọi API chỉnh sửa bài viết này. Mọi truy vấn thay đổi từ tài khoản khác đều bị hệ thống ghi nhật ký cảnh báo bảo mật và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `post_edit_opened`: Ghi nhận mỗi lần người dùng mở trình chỉnh sửa bài viết.
- `post_edit_completed`: Ghi nhận sự kiện lưu chỉnh sửa bài viết thành công cùng mốc thời gian sửa đổi.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị nhãn `"Đã chỉnh sửa"` (Edited) công khai bên cạnh thời gian bài đăng để đảm bảo tính minh bạch thông tin trong môi trường học đường.
- **Không được làm**: Cho phép chỉnh sửa các bài viết đã được đăng tải quá 24 giờ đồng hồ hoặc bài đăng đã bị Admin trường khóa do vi phạm kỷ luật.

## 18. Acceptance Criteria
- Trình chỉnh sửa tải và hiển thị chính xác nội dung cũ của bài viết lên các ô nhập liệu tương ứng.
- Quy trình xóa đính kèm ảnh/tài liệu hoạt động trơn tru và cập nhật đúng dữ liệu bài đăng.
- Hộp thoại cảnh báo thay đổi chưa lưu (Unsaved Changes Dialog) hiển thị chính xác khi bấm Hủy.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nội dung cũ hiển thị đúng định dạng Markdown trong ô nhập liệu chỉnh sửa.
- [ ] Xác minh tính năng xóa bớt ảnh đính kèm hoạt động chuẩn xác và cập nhật đúng trên máy chủ.
- [ ] Thử sửa đổi nội dung và bấm nút Hủy để kiểm tra hộp thoại cảnh báo hiển thị đúng cấu trúc chữ.
- [ ] Đảm bảo bài viết sau khi sửa đổi hiển thị đúng nhãn `"Đã chỉnh sửa"` trên Bảng tin chính.

## 20. AI Agent Implementation Notes
- Sử dụng Livewire kết hợp cùng Alpine.js để quản lý trạng thái của dữ liệu cũ và dữ liệu mới sửa đổi nhằm tối ưu hóa hiệu năng so sánh dữ liệu (Dirty State tracking) ngay tại Client mà không cần gửi truy vấn liên tục về máy chủ.
- Thiết kế cơ chế lưu lịch sử chỉnh sửa (Post Edit History) trong cơ sở dữ liệu để Admin trường có thể tra cứu lại nội dung gốc ban đầu trong các trường hợp xảy ra tranh chấp hoặc báo cáo vi phạm chính sách học đường.
---
