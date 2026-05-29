---
title: "Resource Library Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/resource-sharing.md"
related_design_docs:
  - "../04-design/resource-library.md"
related_system_docs:
  - "../05-system-architecture/resource-service.md"
related_database_docs:
  - "../06-database/resources-table.md"
related_api_docs:
  - "../07-api/resources-api.md"
---

# Trang Thư Viện Tài Nguyên Học Thuật (Resource Library)

## 1. Purpose
Trang Thư Viện Tài Nguyên Học Thuật là kho lưu trữ, chia sẻ và tìm kiếm các tài liệu ôn tập, slide bài giảng, đề thi mẫu, đề cương chi tiết môn học và tài liệu nghiên cứu khoa học được chia sẻ công khai bởi giảng viên và các bạn sinh viên xuất sắc trong trường Đại học Sư phạm TP.HCM.

## 2. Product Context
Nhằm giải quyết vấn đề thiếu hụt và phân mảnh tài liệu học tập chính quy của sinh viên HCMUE, trang Thư viện tài nguyên cung cấp một kho lưu trữ tập trung có phân loại khoa học, xếp hạng uy tín và hỗ trợ tải xuống an toàn, nâng cao tinh thần tự học tự nghiên cứu.

## 3. User Goals
- Tra cứu nhanh slide bài giảng, đề thi môn học theo từng học kỳ.
- Lọc tài liệu theo Khoa đào tạo, Bộ môn, hoặc loại tài liệu (PDF, Slide, Đề thi).
- Đánh giá xếp hạng mức độ hữu ích (Rating) và viết nhận xét ngắn gọn dưới mỗi tài liệu.
- Chia sẻ cống hiến tài liệu học tập bổ ích của bản thân lên hệ thống thông qua biểu mẫu tải lên an toàn.
- Lưu trữ (Bookmark) tài liệu hay vào tủ sách cá nhân để ôn tập lâu dài.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần tìm kiếm nguồn tài liệu ôn thi và nghiên cứu chuẩn xác.
- **Giảng viên HCMUE**: Đăng tải tài liệu giảng dạy, slide bài giảng tham khảo chính quy cho sinh viên.

## 5. Entry Points
- Nhấp chọn mục **Thư viện** (Library Icon) trên thanh điều hướng chính.
- Bấm chọn từ các liên kết giới thiệu tài liệu ôn thi học kỳ mới nổi bật trên Bảng tin chính.

## 6. Layout Strategy
Bố cục thẻ lưới thông tin khoa học giúp sinh viên dễ dàng tra cứu, đối chiếu và tải xuống tài liệu mong muốn.

### 6.1 Desktop Layout
- Bố cục 2 phần rõ rệt:
  - Sidebar bên trái chiếm 25% chiều rộng: Chứa bộ lọc chi tiết theo Khoa chuyên ngành, Môn học cụ thể, Định dạng tệp tin, và Xếp hạng đánh giá.
  - Lưới hiển thị thẻ tài liệu bên phải chiếm 75% chiều rộng: Trình bày dạng lưới 3 cột (`grid-cols-3`) hiển thị các thẻ tài liệu trực quan.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1200px.

### 6.2 Tablet Layout
- Sidebar bộ lọc bên trái chuyển thành một thanh lọc nhanh dạng cuộn ngang (Horizontal scroll) nằm ở đầu trang.
- Lưới thẻ tài liệu chuyển thành bố cục 2 cột cân đối sắc nét.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền mượt mà.
- Trên cùng là ô tìm kiếm tích hợp nút mở bộ lọc nâng cao dạng BottomSheet.
- Các thẻ tài liệu được tối giản hóa thông tin, hiển thị biểu tượng định dạng tệp tin lớn bên trái (Ví dụ: Icon PDF đỏ, Word xanh), thông tin tên tài liệu và nút tải xuống nhanh ở bên phải (`touch-target 44px`).

## 7. Information Architecture
- **Thanh tìm kiếm & Lọc (Search & Filter Section)**:
  - Ô tìm kiếm đa năng theo tên tài liệu hoặc tên tác giả chia sẻ.
- **Lưới danh sách tài liệu (Resources Grid)**:
  - Thẻ tài liệu gồm: Ảnh xem trước trang đầu (Thumbnail) hoặc biểu tượng định dạng tệp lớn, Tiêu đề tài liệu, Tên người chia sẻ, Số lượng lượt tải xuống (Downloads), Điểm đánh giá trung bình (Stars).
  - Nút tải xuống (Download Button) và nút lưu trữ nhanh.
- **Bảng vàng cống hiến (Contributor Board)**:
  - Danh sách top 5 thành viên đóng góp nhiều tài liệu chất lượng nhất tháng hiển thị ở góc phải Desktop.

## 8. Core Components
- **File Format Icon**: Biểu tượng nhỏ nổi bật hiển thị rõ nét định dạng tệp (PDF, PPTX, DOCX, ZIP) giúp người dùng nhận diện nhanh.
- **Download Quick Trigger**: Nút tải xuống nhanh màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`) hoặc biểu tượng mũi tên tải xuống sắc nét.
- **Resource Upload Modal**: Hộp thoại tải tài liệu mới lên hệ thống, yêu cầu điền tên tài liệu, môn học, mô tả ngắn gọn và kéo thả tệp tin.
- **Star Rating Display**: Khối hiển thị điểm số sao đánh giá vàng kim lấp lánh (range: 1-5).

## 9. States
### 9.1 Loading
- Lưới thẻ hiển thị 6 thẻ trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu.

### 9.2 Empty
- Không tìm thấy tài liệu nào khớp với từ khóa hoặc bộ lọc:
  - UI Copy: `"Không tìm thấy tài liệu phù hợp."`
  - Mô tả UI Copy: `"Hãy thử thay đổi tiêu chí lọc hoặc gõ từ khóa tìm kiếm tổng quát hơn nhé."`

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi kết nối dữ liệu thư viện. Vui lòng thử lại sau."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng tải xuống và chia sẻ tài liệu mới tạm thời bị khóa mờ xám.

### 9.5 Permission Restricted
- Tài liệu thuộc nhóm tài nguyên nội bộ nâng cao dành riêng cho giảng viên:
  - Hiển thị biểu tượng ổ khóa mờ kèm thông báo: `"Tài liệu này giới hạn cho Giảng viên."`

### 9.6 Success / Completed
- Tải tệp tin về thiết bị thành công:
  - Số lượng lượt tải xuống trên thẻ tài liệu tự động tăng lên 1 đơn vị.
  - Hiện Toast thông báo xanh dịu: `"Đã tải xuống tài liệu thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ tài liệu sẽ làm thẻ đổi sang màu xám kem dịu (`bg-gray-50`) và phóng to nhẹ ảnh đại diện xem trước. Di chuột qua nút tải xuống sẽ hiển thị dung lượng tệp tin.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút tải xuống và nút lưu trữ khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Thao tác nhấp nút "Chia sẻ tài liệu" sẽ mở ra biểu mẫu tải lên dạng Slide-in mượt mà từ góc phải màn hình Desktop hoặc BottomSheet trên Mobile.

### 10.4 Optimistic UI
- Khi bấm lưu tài liệu (Bookmark), biểu tượng lưu chuyển sang màu xanh dương ngay lập tức trước khi nhận phản hồi API máy chủ để tạo cảm giác phản hồi tức thì.

### 10.5 Menu / Sheet
- Hộp thoại tải tài liệu mới lên hệ thống được thiết kế dạng BottomSheet chi tiết trên di động, tích hợp thanh tiến trình (ProgressBar) hiển thị dung lượng đã tải lên theo thời gian thực.

### 10.6 Toast / Undo
- Hành động lưu tài liệu thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để hủy nhanh lưu trữ trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng trượt co giãn mượt mà của Resource Upload Modal khi mở rộng. Hoạt ảnh chuyển đổi hình ảnh mượt mà bằng CSS transition.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh đại diện tài liệu phải có thuộc tính `alt` mô tả (Ví dụ: `alt="Ảnh xem trước của tài liệu [Tên tài liệu]"`).
- Hỗ trợ đầy đủ phím di chuyển bàn phím qua các mục bộ lọc.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Lưới thẻ tài liệu chuyển sang bố cục danh sách dọc 1 cột với khoảng cách lề hai bên là 12px để tăng diện tích hiển thị dọc của thẻ, tránh bị tràn chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `resources_list` (array of objects: `id`, `title`, `author_name`, `file_format`, `file_size`, `download_count`, `rating_avg`, `is_saved`)
  - `selected_faculty` (string, default: `all`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadResources(filterData)`
  - `downloadResource(resourceId)`
  - `toggleSaveResource(resourceId)`
  - `uploadNewResource(resourceData)`

## 15. Authorization / Privacy Rules
- Bảo mật quyền chia sẻ: Tất cả tài liệu tải lên từ phía sinh viên đều phải đi qua hàng rào kiểm duyệt tự động quét mã độc và được Ban quản trị trường phê duyệt chất lượng trước khi hiển thị công khai trên Thư viện.

## 16. Analytics / Audit Events
- `resource_library_viewed`: Ghi nhận lượt mở xem trang thư viện.
- `resource_downloaded`: Ghi nhận sự kiện tải tài liệu thành công kèm mã tài liệu tương ứng để đánh giá độ hữu ích của tài nguyên học thuật.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị định dạng tệp và dung lượng tệp tin rõ ràng trước khi tải xuống để sinh viên chủ động băng thông mạng di động.
- **Không được làm**: Cho phép đăng tải các tài liệu vi phạm bản quyền sách giáo trình chính quy của nhà xuất bản mà chưa được sự đồng ý của tác giả.

## 18. Acceptance Criteria
- Hiển thị danh sách tài liệu đầy đủ, chính xác theo đúng mốc thời gian đăng tải và phân loại học thuật.
- Quy trình tải xuống tệp tin hoạt động ổn định, nén và trả đúng định dạng tệp tin.
- Giao diện thích ứng sắc nét và hiển thị mượt mà trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng tải tệp PDF thành công và hiển thị đúng biểu tượng định dạng tệp.
- [ ] Xác minh số lượng lượt tải xuống tăng lên 1 đơn vị sau khi tải tệp thành công.
- [ ] Thử nghiệm đính kèm tệp tài liệu ôn thi môn học ở Bước tải lên và đảm bảo biểu mẫu gửi đúng dữ liệu.
- [ ] Đảm bảo các tài liệu bị ẩn hoặc xóa bởi Admin không xuất hiện trong danh mục công khai này.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu đệm (Caching) thông minh cho các tài liệu nổi bật bằng Redis và thiết lập thuật toán phân phối bài viết cá nhân hóa nhẹ dựa trên danh sách bạn bè kết nối hiện tại của sinh viên.
- Thiết kế hệ thống tự động kiểm duyệt tài liệu tải lên sử dụng công nghệ nhận diện chữ (OCR) đơn giản để phát hiện các tài liệu vi phạm quy tắc chính trị hoặc đạo đức học đường trường Đại học Sư phạm TP.HCM.
---
