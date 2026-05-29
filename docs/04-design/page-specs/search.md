---
title: "Search Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/search.md"
related_design_docs:
  - "../04-design/search-mechanics.md"
related_system_docs:
  - "../05-system-architecture/search-indexing.md"
related_database_docs:
  - "../06-database/posts-table.md"
related_api_docs:
  - "../07-api/search-api.md"
---

# Trang Tìm Kiếm Toàn Cầu (Global Search)

## 1. Purpose
Trang Tìm Kiếm Toàn Cầu (Global Search) cung cấp công cụ tra cứu thông tin đa năng, cho phép người dùng tìm kiếm tức thời trên toàn hệ thống UEConnect: Từ các bài đăng Bảng tin, hồ sơ thành viên (Bạn bè, Mentor, Giảng viên), thông tin các Câu lạc bộ học thuật, các kênh trò chuyện công khai, cho đến các tài liệu hữu ích trong Thư viện tài nguyên.

## 2. Product Context
Để giúp sinh viên nhanh chóng tiếp cận nguồn tri thức và mạng lưới kết nối khổng lồ của trường HCMUE một cách khoa học, thanh tìm kiếm được thiết kế theo chuẩn tối giản cao của Threads kết hợp bộ máy gợi ý tự động (Autocomplete) và lưu vết lịch sử tìm kiếm thông minh thời gian thực.

## 3. User Goals
- Tra cứu nhanh mọi nội dung học thuật và thông tin bạn bè bằng một ô tìm kiếm duy nhất.
- Phân loại kết quả tìm kiếm rõ ràng theo từng Tab chuyên sâu: "Tất cả", "Bài viết", "Người dùng", "Câu lạc bộ", "Thư viện".
- Xem lại lịch sử tìm kiếm gần đây (Recent Searches) để truy cập lại nhanh chóng các nội dung quan tâm cũ.
- Khám phá các xu hướng tìm kiếm hàng đầu (Trending Topics) của sinh viên toàn trường HCMUE.

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Sinh viên, giảng viên và cựu sinh viên cần tra cứu nhanh thông tin học đường.

## 5. Entry Points
- Nhấp chọn ô **Tìm kiếm** (Search Icon) nổi bật trên thanh thanh điều hướng chính.
- Sử dụng phím tắt thông minh `Ctrl + K` hoặc `Cmd + K` từ bất kỳ vị trí nào trên hệ thống.

## 6. Layout Strategy
Thiết kế tập trung tối đa vào ô nhập liệu lớn và khu vực hiển thị lịch sử / xu hướng sạch sẽ trước khi gõ từ khóa.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 680px).
- Ô tìm kiếm lớn bo tròn góc mềm mại tích hợp biểu tượng Kính lúp ở đầu và phím tắt đóng (X) ở cuối.
- Khung hiển thị Lịch sử tìm kiếm gần đây và Xu hướng học đường nằm ngay dưới dạng lưới thẻ 2 cột cân đối trước khi có từ khóa.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ dàng chạm bấm.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền mượt mà.
- Ô tìm kiếm tự động nhận tiêu điểm (Auto-focus) và mở rộng tràn ngang màn hình, tích hợp nút "Hủy" chữ trơn sát bên phải để tay dễ chạm thoát nhanh (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Khu vực nhập từ khóa (Search Input Area)**:
  - Ô tìm kiếm lớn tích hợp cơ chế Autocomplete thông minh hiển thị tối đa 5 gợi ý thả xuống (Dropdown suggestions) tức thời khi gõ từ 2 ký tự.
- **Trang thái tĩnh ban đầu (Default View - Pre-search)**:
  - Khối "Lịch sử tìm kiếm gần đây" (Recent Searches) kèm nút "Xóa tất cả" (Clear All).
  - Khối "Chủ đề đang hot" (Trending Topics) hiển thị dưới dạng các nhãn nhỏ kèm số lượng tìm kiếm (Ví dụ: `#NghienCuuKhoaHoc`, `#DaiHocSuPham`).
- **Trang thái hiển thị kết quả (Results View)**:
  - Thanh tab chuyển đổi ngang phân loại: "Tất cả", "Bài viết", "Mọi người", "Câu lạc bộ", "Tài liệu".
  - Danh sách kết quả xếp dọc tương ứng với mỗi phân loại tab.

## 8. Core Components
- **Global Search Input**: Ô nhập liệu lớn tích hợp phím tắt đóng nhanh (X) và biểu tượng Kính lúp thông minh.
- **Recent Search Item**: Dòng lịch sử cũ hiển thị ảnh xoay tròn nhỏ lịch sử bên trái và nút xóa đơn lẻ (X) nhỏ bên phải để người dùng dọn dẹp danh mục tìm kiếm.
- **Trending Keyword Tag**: Nhãn tròn màu xám nhạt hiển thị các từ khóa xu hướng nổi bật, tự động gán từ khóa vào ô tìm kiếm khi nhấp chọn.
- **Search Result Item Wrapper**: Thẻ hiển thị kết quả tìm kiếm đa năng tự động bao gói các phần tử (Avatar người dùng, ảnh bài đăng tóm tắt, tên CLB) phù hợp với loại kết quả tương ứng.

## 9. States
### 9.1 Loading
- Khi đang thực hiện tìm kiếm, hiển thị vòng tròn xoay tải dữ liệu nhỏ ở góc phải ô tìm kiếm. Phía dưới hiển thị 3 thẻ trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn.

### 9.2 Empty
- Không tìm thấy kết quả nào khớp với từ khóa tìm kiếm:
  - UI Copy: `"Không tìm thấy kết quả phù hợp."`
  - Mô tả UI Copy: `"Hãy thử kiểm tra lại chính tả hoặc thay đổi từ khóa tìm kiếm tổng quát hơn nhé."`

### 9.3 Error
- Lỗi truy vấn công cụ tìm kiếm do sự cố máy chủ:
  - UI Copy: `"Đã xảy ra lỗi khi truy vấn kết quả. Vui lòng nhấp để thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa tính năng tìm kiếm trực tuyến, chỉ cho phép tìm kiếm trên dữ liệu đã lưu đệm cục bộ tại Client.

### 9.5 Permission Restricted
- Các bài viết thuộc nhóm học tập riêng tư hoặc tài khoản riêng tư chưa kết nối bạn bè sẽ tự động được lọc loại bỏ khỏi kết quả tìm kiếm công khai này để bảo vệ quyền riêng tư của thành viên.

### 9.6 Success / Completed
- Hiển thị đầy đủ, chính xác toàn bộ kết quả tìm kiếm theo đúng phân loại tab. Từ khóa tìm kiếm được tô màu nổi bật (Highlighting) trong nội dung văn bản kết quả để người dùng dễ nhận diện.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua dòng lịch sử cũ sẽ làm dòng chuyển sang nền màu xám kem dịu (`bg-gray-50`) và hiển thị rõ nét nút xóa đơn lẻ.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh ô tìm kiếm và các tab kết quả khi di chuyển bằng phím Tab di chuyển tiêu điểm. Hỗ trợ phím mũi tên Lên/Xuống để duyệt nhanh qua các gợi ý autocomplete.

### 10.3 Press / Tap
- Thao tác nhấp chọn nhãn xu hướng Trending Tag sẽ tự động điền từ khóa đó vào ô tìm kiếm và kích hoạt truy vấn ngay lập tức mượt mà dưới 150ms.

### 10.4 Optimistic UI
- Khi bấm xóa một dòng lịch sử tìm kiếm, dòng đó lập tức biến mất khỏi danh sách hiển thị tạm thời trước khi nhận phản hồi xác nhận lưu từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 50ms.

### 10.5 Menu / Sheet
- Công cụ tìm kiếm Autocomplete hiển thị dạng thực đơn thả xuống (Dropdown) mượt mà có bóng mờ bên dưới ô tìm kiếm trên Desktop, và mở rộng toàn màn hình di động dễ bấm chọn.

### 10.6 Toast / Undo
- Hành động "Xóa tất cả lịch sử tìm kiếm" thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh lịch sử cũ trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng trượt mở rộng mượt mà của thanh tab kết quả khi chuyển từ trạng thái Pre-search sang Results View diễn ra trong vòng 200ms trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Ô tìm kiếm có thuộc tính `aria-autocomplete="list"` và `aria-expanded` để phản hồi chính xác trạng thái mở rộng của danh sách gợi ý.
- Hỗ trợ phím đóng nhanh `Escape` để đóng nhanh danh sách gợi ý Autocomplete.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Thu nhỏ khoảng cách lề hai bên, ô tìm kiếm tự động co dãn tràn viền và nút Hủy nằm sát bên phải giúp tay dễ dàng chạm thoát nhanh.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `search_query` (string, required)
  - `search_category` (enum: `all`, `posts`, `users`, `clubs`, `resources`)
  - `recent_searches` (array of strings)
  - `trending_tags` (array of strings)

## 14. API / Action Requirements
- Gọi Livewire / Search Action:
  - `autocompleteSearch(query)`
  - `executeGlobalSearch(query, category)`
  - `clearRecentSearchItem(itemIndex)`
  - `clearAllSearchHistory()`
  - `undoClearSearchHistory()`

## 15. Authorization / Privacy Rules
- Bảo mật lịch sử tìm kiếm cá nhân tuyệt đối: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền xem danh sách lịch sử tìm kiếm gần đây cá nhân của họ. Mọi truy vấn trái phép từ tài khoản khác đều bị hệ thống chặn và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `search_performed`: Ghi nhận sự kiện tìm kiếm thành công kèm từ khóa tìm kiếm và chuyên mục để đánh giá nhu cầu quan tâm thực tế của sinh viên trường.

## 17. Do / Don't
- **Nên làm**: Luôn tô đậm màu chữ (Highlighting) của từ khóa tìm kiếm khớp trong văn bản kết quả để giúp sinh viên đối chiếu thông tin nhanh và chính xác nhất.
- **Không được làm**: Lưu trữ lịch sử tìm kiếm của người dùng lạ hoặc khách vãng lai chưa đăng nhập hợp lệ vào cơ sở dữ liệu hệ thống.

## 18. Acceptance Criteria
- Ô tìm kiếm và gợi ý Autocomplete hoạt động ổn định, trả gợi ý chính xác trong vòng dưới 200ms.
- Phân loại kết quả theo từng Tab chính xác theo đúng mốc thời gian thực tế trong cơ sở dữ liệu.
- Tính năng xóa lịch sử tìm kiếm gần đây hoạt động chính xác và đồng bộ tức thời trên cơ sở dữ liệu.

## 19. QA / UAT Checklist
- [ ] Kiểm tra gợi ý Autocomplete tự động hiển thị khi gõ từ 2 ký tự trở lên.
- [ ] Xác minh phím tắt `Ctrl + K` kích hoạt mở rộng ô tìm kiếm thành công từ mọi trang.
- [ ] Thử nghiệm bấm nút "Xóa tất cả" và bấm "Hoàn tác" để kiểm tra tính ổn định của dữ liệu lịch sử.
- [ ] Đảm bảo các bài viết riêng tư không xuất hiện trong kết quả tìm kiếm công khai này.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ tìm kiếm văn bản toàn văn (Full-text Search indexing) kết hợp chỉ mục tối ưu (Composite Indexes) trên cơ sở dữ liệu để đạt hiệu năng truy vấn tìm kiếm nhanh và chính xác nhất dưới 150ms.
- Tận dụng thuật toán tìm kiếm mờ (Fuzzy Search logic) nhẹ tại Client để tự động sửa lỗi chính tả gõ phím nhanh của sinh viên, tăng tỷ lệ tìm kiếm thành công.
---
