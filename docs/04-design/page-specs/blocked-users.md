---
title: "Blocked Users Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/user-blocking.md"
related_design_docs:
  - "../04-design/privacy-controls.md"
related_system_docs:
  - "../05-system-architecture/privacy-service.md"
related_database_docs:
  - "../06-database/blocks-table.md"
related_api_docs:
  - "../07-api/blocks-api.md"
---

# Trang Danh Sách Người Dùng Bị Chặn (Blocked Users)

## 1. Purpose
Trang Danh Sách Người Dùng Bị Chặn cho phép thành viên quản lý quyền riêng tư và an toàn cá nhân bằng cách theo dõi danh sách những tài khoản họ đã chặn (Block). Tại đây, người dùng có thể tìm kiếm nhanh và thực hiện bỏ chặn (Unblock) để khôi phục khả năng tương tác thông thường.

## 2. Product Context
Trong môi trường học đường số của HCMUE, việc bảo vệ sinh viên khỏi các hành vi quấy rối, bắt nạt mạng hoặc spam là ưu tiên hàng đầu. Giao diện này cung cấp một công cụ kiểm soát quyền riêng tư đơn giản, an toàn và tức thì theo tiêu chuẩn ứng dụng mạng xã hội Threads hiện đại.

## 3. User Goals
- Xem danh sách trực quan tất cả các tài khoản đã chặn.
- Tìm kiếm một tài khoản cụ thể trong danh sách chặn.
- Bỏ chặn nhanh một tài khoản bằng nút thao tác trực tiếp kèm theo xác nhận bảo vệ.

## 4. Primary Users
- **Mọi người dùng trên hệ thống**: Những người muốn quản lý mối quan hệ và không gian thảo luận cá nhân của họ.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Quyền riêng tư & Bảo mật** -> Chọn **Tài khoản đã chặn** (Blocked Accounts).

## 6. Layout Strategy
Thiết kế tập trung tối đa vào sự tối giản, giúp người dùng dễ dàng thao tác tìm kiếm và bỏ chặn nhanh mà không bị rối mắt.

### 6.1 Desktop Layout
- Bố cục một cột trung tâm thanh thoát có chiều rộng tối đa là 600px.
- Thanh tìm kiếm nằm ở trên cùng, phía dưới là danh sách dạng thẻ dọc (List Card View) hiển thị các tài khoản bị chặn.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên là 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột tràn màn hình.
- Kích thước ảnh đại diện thu nhỏ nhẹ (40px) để dành không gian cho tên hiển thị và nút bỏ chặn lớn (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh tìm kiếm (Search Bar)**:
  - Trường nhập văn bản tìm kiếm tên người dùng bị chặn.
- **Danh sách tài khoản (Blocked Users List)**:
  - Ảnh đại diện (Avatar).
  - Tên hiển thị (Display Name) và Tên tài khoản (Username `@username`).
  - Nhãn đối tượng học đường (Sinh viên / Cựu sinh viên / Giảng viên).
  - Nút "Bỏ chặn" (Unblock Button) nổi bật.

## 8. Core Components
- **Search Input Field**: Có biểu tượng Kính lúp phóng to bên trái và nút xóa nhanh chữ bên phải.
- **User Block Row Card**: Thẻ thông tin thành viên bị chặn tối giản với đường viền mỏng phía dưới.
- **Unblock Confirm Modal**: Hộp thoại xác nhận yêu cầu bỏ chặn nhằm tránh các thao tác chạm nhầm ngoài ý muốn.
- **Empty State**: Hiển thị hình vẽ tấm khiên bảo vệ màu xanh da trời nhạt khi danh sách chặn trống.

## 9. States
### 9.1 Loading
- Hiển thị 4-5 dòng avatar và tên ở dạng thanh Shimmer xám nhạt đang chuyển động trượt nhẹ.

### 9.2 Empty
- Khi người dùng chưa chặn bất kỳ ai:
  - UI Copy: `"Danh sách chặn của bạn đang trống."`
  - Mô tả UI Copy: `"Những tài khoản bạn chặn sẽ xuất hiện ở đây. Họ sẽ không thể xem hồ sơ hoặc nhắn tin cho bạn."`

### 9.3 Error
- Lỗi kết nối mạng khi thực hiện bỏ chặn:
  - UI Copy: `"Không thể thực hiện bỏ chặn lúc này. Vui lòng kiểm tra lại kết nối mạng."`

### 9.4 Offline / Reconnecting
- Vô hiệu hóa nút "Bỏ chặn" và hiển thị thông báo mỏng ở chân trang: `"Tính năng bỏ chặn chỉ hoạt động khi bạn trực tuyến."`

### 9.5 Permission Restricted
- Không áp dụng cho trang thiết lập cá nhân này.

### 9.6 Success / Completed
- Bỏ chặn thành công một tài khoản:
  - Hiển thị Toast thông báo: `"Đã bỏ chặn @username thành công."` kèm theo nút "Hoàn tác" (Undo) hiển thị trong vòng 4 giây.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua nút "Bỏ chặn" sẽ chuyển màu nền nút sang màu xám nhạt (`bg-gray-100`) và hiển thị viền rõ nét để báo hiệu khả năng nhấp chọn.

### 10.2 Focus
- Vòng viền Focus 2px nét đứt màu xanh đậm thương hiệu bao quanh ô tìm kiếm hoặc nút Bỏ chặn khi sử dụng phím Tab di chuyển.

### 10.3 Press / Tap
- Khi nhấn chọn "Bỏ chặn", mở ra Modal xác nhận ở giữa màn hình (Desktop) hoặc BottomSheet trượt lên mượt mà (Mobile).

### 10.4 Optimistic UI
- Khi bấm xác nhận bỏ chặn trong Modal, ẩn ngay lập tức dòng tài khoản đó khỏi danh sách hiển thị tạm thời trước khi máy chủ hoàn tất cập nhật. Nếu máy chủ báo lỗi, hiển thị lại dòng đó kèm Toast báo lỗi.

### 10.5 Menu / Sheet
- Hỗ trợ BottomSheet trên thiết bị di động để hiển thị hộp thoại xác nhận bỏ chặn nhanh gọn.

### 10.6 Toast / Undo
- Toast bỏ chặn hiển thị 4 giây ở góc dưới màn hình, tích hợp nút bấm "Hoàn tác" để chặn lại ngay lập tức nếu người dùng đổi ý.

### 10.7 Motion
- Hiệu ứng biến mất (Fade-out & Slide-up) của dòng tài khoản khi bị xóa khỏi danh sách chặn diễn ra trong vòng 200ms cực kỳ mượt mà.

## 11. Accessibility Requirements
- Thẻ mô tả hình ảnh đại diện của người bị chặn: `alt="Ảnh đại diện của @username"`.
- Đầy đủ thuộc tính `aria-live="polite"` cho danh sách chặn để thông báo cho trình đọc màn hình khi danh sách thay đổi hoặc có thao tác bỏ chặn thành công.

## 12. Responsive Rules
- Thích ứng hoàn hảo trên mọi kích thước màn hình. Trên di động, nút bỏ chặn chuyển thành dạng nút thu nhỏ chỉ chứa chữ "Bỏ chặn" hoặc biểu tượng nhỏ gọn để không lấn át tên người dùng.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `blocked_users_list` (array of objects: `user_id`, `name`, `username`, `avatar_url`, `role_label`)
  - `search_query` (string)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadBlockedUsers(searchQuery)`
  - `unblockUser(userId)`
  - `undoUnblockUser(userId)`

## 15. Authorization / Privacy Rules
- Chỉ có chủ tài khoản đăng nhập mới có quyền xem và chỉnh sửa danh sách chặn cá nhân này (`auth` middleware). Không cho phép bất cứ ai truy cập danh sách chặn của người khác.

## 16. Analytics / Audit Events
- `blocked_list_viewed`: Ghi nhận số lượt xem trang danh sách chặn.
- `user_unblocked_from_settings`: Ghi nhận mã tài khoản được bỏ chặn thành công cùng thời gian thao tác.

## 17. Do / Don't
- **Nên làm**: Luôn cung cấp hộp thoại xác nhận trước khi thực hiện bỏ chặn để tránh các hành vi bấm nhầm đáng tiếc.
- **Không được làm**: Cho phép người bị chặn nhận được thông báo rằng họ vừa bị chặn hoặc vừa được bỏ chặn từ người khác. Quy trình chặn hoàn toàn kín đáo.

## 18. Acceptance Criteria
- Danh sách hiển thị chính xác toàn bộ danh sách tài khoản đã chặn trong cơ sở dữ liệu.
- Tính năng tìm kiếm lọc dữ liệu thời gian thực (Debounce 300ms) chính xác theo tên và tên tài khoản.
- Tính năng bỏ chặn hoạt động đúng đắn và cập nhật quan hệ kết nối giữa hai tài khoản ngay lập tức.

## 19. QA / UAT Checklist
- [ ] Xác minh danh sách chặn hiển thị đúng tên hiển thị và nhãn phân loại học đường.
- [ ] Thử nghiệm tìm kiếm bằng tiếng Việt có dấu và không dấu hoạt động khớp kết quả chính xác.
- [ ] Kiểm tra tính năng "Hoàn tác" chặn hoạt động ổn định và đưa tài khoản trở lại danh sách chặn tức thì.
- [ ] Đảm bảo Modal xác nhận hiển thị cân đối ở giữa màn hình trên mọi kích thước thiết bị di động.

## 20. AI Agent Implementation Notes
- Sử dụng Livewire kết hợp cùng thuộc tính `wire:key` trên mỗi dòng thẻ thành viên để tối ưu hóa quá trình cập nhật DOM của Livewire khi có phần tử bị xóa đi khỏi danh sách.
- Thiết kế bảng `blocks` trong cơ sở dữ liệu với các chỉ mục (indexes) tối ưu trên cột `user_id` và `blocked_user_id` để tăng tốc độ tìm kiếm truy vấn.
