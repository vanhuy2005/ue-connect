---
title: "Composer Component Specification"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
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

# Thành Phần Soạn Thảo Tin Đăng (Composer Component)

## 1. Purpose
Thành Phần Soạn Thảo Tin Đăng (Composer) là giao diện nhập liệu cốt lõi cho phép người dùng sáng tạo bài viết mới, viết câu trả lời (replies), thảo luận nhóm, chèn biểu tượng cảm xúc, gắn thẻ bạn bè (Mentions), thêm nhãn chủ đề (Hashtags) và đính kèm hình ảnh hoặc tài liệu học tập trong toàn hệ thống UEConnect.

## 2. Product Context
Kế thừa triết lý giao diện Threads tinh giản và trực quan, Composer của UEConnect chú trọng tối đa vào tốc độ phản hồi, giao diện sạch sẽ không gây mất tập trung, kết hợp với các tiêu chuẩn nghiêm túc về học thuật của Đại học Sư phạm TP.HCM (như đính kèm tài liệu học tập, slide bài giảng).

## 3. User Goals
- Sáng tạo bài viết nhanh chóng với trình soạn thảo tự động co giãn chiều cao (Auto-expanding text area).
- Gắn thẻ bạn bè nhanh bằng cách gõ ký tự `@` và nhãn chủ đề bằng `#`.
- Tải lên hình ảnh sinh động hoặc tài liệu học thuật (PDF, Word, Excel) một cách trực quan.
- Lựa chọn quyền riêng tư cho bài viết trước khi xuất bản (Công khai, Thành viên kết nối, Nội bộ CLB).
- Tự động lưu bản nháp (Draft) để không bị mất nội dung khi mất kết nối đột ngột.

## 4. Primary Users
- **Toàn bộ thành viên UEConnect**: Cần đăng bài viết chia sẻ, thảo luận bài học hoặc trả lời bình luận của bạn bè.

## 5. Entry Points
- Nhấp vào nút **Đăng bài mới** (New Post) ở thanh điều hướng cố định hoặc thanh nhập liệu giả (Fake input composer trigger) trên đầu trang Bảng tin chính.
- Nhấp chọn "Phản hồi" hoặc "Bình luận" dưới bất kỳ bài viết nào trên hệ thống.

## 6. Layout Strategy
Thiết kế tập trung vào sự tập trung tối đa của người dùng, phân tách rõ ràng giữa khu vực soạn thảo văn bản và thanh công cụ đính kèm phương tiện.

### 6.1 Desktop Layout
- Hiển thị dưới dạng một hộp thoại nhỏ (Modal popup) đè lên giữa màn hình với nền tối mờ xung quanh.
- Kích thước chiều rộng cố định: 560px. Chiều cao tự động tăng theo nội dung soạn thảo tối đa đến 450px (vượt quá chiều cao này sẽ xuất hiện thanh cuộn nội bộ mượt mà).
- Khoảng cách lề: 20px.

### 6.2 Tablet Layout
- Tương tự Desktop nhưng có chiều rộng mở rộng 600px để người dùng dễ thao tác trên màn hình cảm ứng lớn.

### 6.3 Mobile / PWA Layout
- Mở rộng hiển thị toàn màn hình (Full-screen Overlay) để tối đa hóa không gian bàn phím ảo di động.
- Nút "Đăng bài" (Post Button) nằm ở góc phải trên thanh tiêu đề của trang để dễ bấm chạm bằng ngón tay.
- Các công cụ đính kèm (Hình ảnh, Tài liệu, Thẻ gắn, Khảo sát) được xếp thành một hàng ngang ngay phía trên bàn phím ảo để ngón tay dễ tiếp cận (`touch-target 44px`).

## 7. Information Architecture
- **Khu vực danh tính (User Identity Row)**:
  - Ảnh đại diện, Tên hiển thị người dùng.
  - Hộp thả xuống chọn đối tượng người xem (Quyền riêng tư: Công khai, Bạn bè...).
- **Khung soạn thảo văn bản chính (Text Input Area)**:
  - Placeholder trực quan gợi ý viết bài (Ví dụ: `"Bạn đang nghĩ gì thế?..."` hoặc `"Chia sẻ tài liệu học tập mới..."`).
  - Hỗ trợ nhập liệu đa dòng.
- **Khu vực đính kèm phương tiện (Media Attachments Preview)**:
  - Lưới hiển thị các hình ảnh/tài liệu đã chọn kèm nút xóa nhanh dạng chéo (X).
- **Thanh công cụ dưới cùng (Bottom Toolbar)**:
  - Các nút biểu tượng: Đính kèm hình ảnh (Image), Đính kèm tài liệu (Document), Biểu tượng cảm xúc (Emoji Picker), Cuộc thăm dò ý kiến (Poll), Thẻ bắt đầu bằng `#`.
  - Bộ đếm số lượng ký tự còn lại (Character Counter) dạng vòng tròn tiến độ.

## 8. Core Components
- **Auto-expanding Textarea**: Ô nhập liệu tự động điều chỉnh chiều cao theo độ dài văn bản mà không bị giật lag màn hình.
- **Character Count Ring**: Vòng tròn nhỏ chuyển từ màu xanh dương sang màu vàng và đỏ khi số lượng ký tự tiến sát giới hạn tối đa (500 ký tự).
- **Media Preview Grid**: Lưới sắp xếp hình ảnh thông minh dạng lưới mảnh (Grid 2x2 hoặc hàng ngang cuộn).
- **Mention/Hashtag Autocomplete Box**: Hộp danh sách gợi ý thành viên hiện lên tức thì khi người dùng gõ `@` kèm chữ cái tìm kiếm.

## 9. States
### 9.1 Loading
- Khi bấm "Đăng bài", nút chuyển sang trạng thái mờ, hiển thị biểu tượng vòng tròn xoay tròn và vô hiệu hóa khả năng nhấp chọn để tránh gửi bài viết trùng lặp.

### 9.2 Empty
- Khi khung soạn thảo hoàn toàn trống rỗng:
  - Nút "Đăng" bị mờ đi và bị vô hiệu hóa (disabled).
  - Placeholder mặc định: `"Chia sẻ kiến thức hoặc cập nhật hôm nay của bạn..."`

### 9.3 Error
- **Vượt quá dung lượng tệp**: Sinh viên đính kèm tệp tài liệu vượt quá 20MB.
  - UI Copy: `"Dung lượng tệp tài liệu vượt quá giới hạn cho phép (Tối đa 20MB)."`
- **Vượt quá số lượng ảnh**: Đính kèm quá 4 hình ảnh trong một bài viết.
  - UI Copy: `"Bạn chỉ có thể đính kèm tối đa 4 hình ảnh trong một bài đăng."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo nhỏ màu xám dưới thanh công cụ: `"Bạn đang ngoại tuyến. Bài viết đã được lưu vào Bản nháp tạm thời."`

### 9.5 Permission Restricted
- Không áp dụng, trang này mở cho toàn bộ thành viên.

### 9.6 Success / Completed
- Đăng bài viết thành công:
  - Hộp soạn thảo biến mất với hiệu ứng thu nhỏ mượt mà.
  - Hiển thị Toast thông báo ở dưới màn hình: `"Đã đăng bài viết của bạn thành công!"` kèm nút "Xem bài viết".

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các biểu tượng công cụ đính kèm sẽ hiển thị ô gợi ý giải thích nhanh (Tooltip): `"Đính kèm tài liệu"`, `"Thêm ảnh"`, `"Khảo sát ý kiến"` dạng nền tối chữ trắng nhỏ gọn.

### 10.2 Focus
- Ô nhập liệu tự động nhận tiêu điểm (Auto-focus) ngay khi hộp soạn thảo vừa mở ra. Sử dụng viền ngoài xanh dịu mắt để tạo sự tập trung.

### 10.3 Press / Tap
- Các nút bấm đính kèm ảnh phản hồi rung nhẹ trên di động khi chạm chọn.

### 10.4 Optimistic UI
- Đối với bài đăng bình luận/trả lời, chèn ngay bình luận đó vào dưới bài viết tức thì với trạng thái mờ nhẹ trong lúc chờ máy chủ xử lý, tạo cảm giác tốc độ phản hồi cực nhanh.

### 10.5 Menu / Sheet
- Hộp chọn quyền riêng tư mở ra một BottomSheet trên di động giúp người dùng dễ dàng chuyển đổi bằng cách nhấp chọn danh mục trực quan.

### 10.6 Toast / Undo
- Đăng bài viết thành công cho phép người dùng bấm "Hoàn tác" (Undo) trong vòng 3 giây để thu hồi bài đăng ngay lập tức nếu phát hiện lỗi chính tả.

### 10.7 Motion
- Hiệu ứng mở Modal dạng phóng to mượt mà (`scale-95` lên `scale-100` trong 150ms). Khi đính kèm ảnh mới, ảnh trượt nhẹ vào lưới hiển thị với CSS transition mượt mà.

## 11. Accessibility Requirements
- Trình soạn thảo văn bản được gắn nhãn `aria-multiline="true"` và `aria-label="Nội dung bài viết"`.
- Hỗ trợ đóng hộp thoại soạn thảo nhanh chóng bằng phím `Escape` tiện lợi.
- Đầy đủ thuộc tính `alt` cho các ảnh đính kèm xem trước.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Toàn bộ hộp thoại soạn thảo mở rộng chiếm 100% diện tích chiều rộng và chiều cao màn hình, bàn phím tự động đẩy giao diện lên không làm che khuất khu vực nhập liệu.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `post_content` (text, max: 500 characters)
  - `privacy_level` (enum: `public`, `connections`, `private`)
  - `media_files` (array of files, max 4 images, max 1 document file)
  - `poll_options` (optional, array of strings)

## 14. API / Action Requirements
- Gọi Livewire / File Upload Action:
  - `createPost(content, privacy, attachments)`
  - `uploadTemporaryMedia(file)`
  - `deleteTemporaryMedia(fileId)`
  - `autocompleteUsers(query)`

## 15. Authorization / Privacy Rules
- Sinh viên chỉ được phép gắn thẻ (Mention) những người dùng đang ở trạng thái hoạt động bình thường, không được phép gắn thẻ những tài khoản đã chặn họ hoặc tài khoản riêng tư chưa kết nối.

## 16. Analytics / Audit Events
- `composer_opened`: Ghi nhận mỗi lần hộp soạn thảo được kích hoạt.
- `post_creation_failed`: Ghi nhận lý do và lỗi khi đăng bài không thành công (ví dụ: vi phạm bộ lọc từ ngữ cấm).

## 17. Do / Don't
- **Nên làm**: Tích hợp bộ lọc từ ngữ thô tục học đường (Profanity Filter) tự động cảnh báo người dùng sửa lại nội dung trước khi bấm đăng bài.
- **Không được làm**: Cho phép bấm nút "Đăng" khi bài viết chỉ toàn khoảng trắng hoặc không có bất kỳ ký tự hữu ích nào.

## 18. Acceptance Criteria
- Trình soạn thảo hoạt động mượt mà không bị trễ chữ (Input lag) kể cả trên các thiết bị di động cấu hình thấp.
- Upload hình ảnh/tài liệu đính kèm hiển thị thanh tiến trình tải lên chính xác theo thời gian thực.
- Tính năng gợi ý từ khóa `@mention` hiện đúng danh sách người dùng phù hợp và điền nhanh khi nhấp chọn.

## 19. QA / UAT Checklist
- [ ] Thử nghiệm soạn thảo bài viết chạm mốc 500 ký tự và kiểm tra cảnh báo đỏ của vòng đếm ký tự.
- [ ] Thử tải lên một tệp PDF nặng 15MB và kiểm tra hiển thị xem trước tệp đính kèm.
- [ ] Kiểm tra khả năng bấm nút `Escape` để đóng hộp thoại và đảm bảo có hộp thoại phụ xác nhận: `"Bạn có muốn hủy bài viết này không? Bài viết chưa được đăng."` để tránh mất bài của sinh viên do bấm nhầm.
- [ ] Xác minh tệp tin tải lên được làm sạch thông tin Exif (thông tin định vị ảnh chụp) để bảo vệ sự riêng tư cho sinh viên.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `Trix Editor` hoặc xây dựng một `contenteditable` tùy biến nhẹ bằng Alpine.js để xử lý định dạng phong phú và hiển thị gợi ý thông minh thời gian thực.
- Thiết kế cơ chế lưu bản nháp tạm thời (Auto-save draft) vào bộ nhớ `localStorage` của trình duyệt mỗi 5 giây một lần để bảo toàn công sức soạn thảo của sinh viên trong trường hợp rớt mạng đột ngột.
---
