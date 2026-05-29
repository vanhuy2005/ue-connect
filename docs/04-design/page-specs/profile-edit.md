---
title: "Profile Edit Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/profile.md"
related_design_docs:
  - "../04-design/profile-layouts.md"
related_system_docs:
  - "../05-system-architecture/profile-service.md"
related_database_docs:
  - "../06-database/users-table.md"
related_api_docs:
  - "../07-api/profile-api.md"
---

# Trang Chỉnh Sửa Hồ Sơ Cá Nhân (Profile Edit)

## 1. Purpose
Trang Chỉnh Sửa Hồ Sơ Cá Nhân (Profile Edit) cung cấp cho người dùng các công cụ trực quan để thay đổi ảnh đại diện (Avatar), ảnh bìa (Cover photo), chỉnh sửa phần tự giới thiệu ngắn (Bio), bổ sung liên kết trang cá nhân bên ngoài (LinkedIn, Facebook), và cập nhật thông tin sở thích học tập trong hệ thống UEConnect.

## 2. Product Context
Nằm trong giải pháp nâng cao tính định danh và tương tác mượt mà tại HCMUE, trang này được thiết kế để đơn giản hóa tối đa quy trình cập nhật thông tin cá nhân theo đúng phong cách tối giản của Threads, đồng thời tuân thủ các quy tắc bảo vệ thông tin học thuật cốt lõi.

## 3. User Goals
- Thay đổi ảnh đại diện và ảnh bìa chất lượng cao dễ dàng bằng thao tác kéo thả tệp.
- Viết tiểu sử tự giới thiệu cá nhân ngắn gọn, sinh động.
- Liên kết nhanh các tài khoản học thuật chuyên sâu (LinkedIn, Google Scholar) để phục vụ hướng nghiệp.
- Lưu trữ thông tin thay đổi an toàn hoặc hủy bỏ chỉnh sửa khôi phục trạng thái cũ nếu đổi ý.

## 4. Primary Users
- **Toàn bộ học viên, sinh viên, cựu sinh viên và giảng viên HCMUE**: Cần cập nhật thông tin cá nhân thường kỳ để tạo uy tín tương tác.

## 5. Entry Points
- Nhấp chọn nút **Chỉnh sửa hồ sơ** (Edit Profile) hiển thị nổi bật trên chính trang Hồ sơ cá nhân của người dùng.
- Chọn danh mục **Thiết lập tài khoản** -> Chọn **Chỉnh sửa thông tin hồ sơ**.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật khu vực cập nhật hình ảnh trực quan, giao diện thoáng đãng giúp tay dễ nhập liệu.

### 6.1 Desktop Layout
- Bố cục một cột trung tâm thanh lịch có chiều rộng tối đa là 640px.
- Các trường nhập liệu dài được xếp dọc cân đối, khu vực thay đổi ảnh đại diện tròn hiển thị nổi bật ở đầu trang.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ chạm bấm.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình.
- Nút "Lưu" nằm ở góc phải trên cùng thanh tiêu đề cố định. Nút "Hủy" ở góc trái trên cùng để tay dễ chạm bấm.
- Các ô nhập liệu có khoảng cách an toàn rộng rãi để tránh ngón tay chạm nhầm.
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Khu vực cập nhật hình ảnh (Media Upload Area)**:
  - Khung ảnh bìa chữ nhật bo tròn góc nhẹ, nút tải ảnh lên nằm ở góc dưới bên phải ảnh bìa.
  - Vòng tròn ảnh đại diện nổi đè lên góc dưới trái ảnh bìa, nút tải ảnh đại diện mới dạng máy ảnh nhỏ nằm đè trên avatar.
- **Các trường thông tin cá nhân (Profile Fields)**:
  - Họ tên hiển thị (Display Name).
  - Tự giới thiệu bản thân (Bio - Giới hạn 160 ký tự).
  - Liên kết cá nhân (Website/Social Links).
- **Thông tin học thuật cố định (Locked Academic Fields)**:
  - Hiển thị mờ các trường không cho phép tự sửa đổi (Khoa đào tạo, Lớp sinh hoạt, Mã số sinh viên) kèm nhãn nhỏ: `"Liên hệ Phòng Đào tạo để cập nhật thông tin này"`.

## 8. Core Components
- **Image Cropper Modal**: Hộp thoại cắt xén ảnh trực quan giúp người dùng căn chỉnh tỉ lệ ảnh đại diện tròn (1:1) hoặc ảnh bìa (16:9) đẹp mắt trước khi tải lên.
- **Bio Textarea Field**: Ô nhập liệu phần giới thiệu bản thân có bộ đếm ký tự giảm lùi trực quan thời gian thực (160 -> 0 ký tự).
- **Save Changes Button**: Nút "Lưu thay đổi" màu xanh dương thẫm (`bg-blue-800` hover `bg-blue-900`).
- **Discard Warning Modal**: Hộp thoại cảnh báo thay đổi chưa lưu (Unsaved Changes Dialog) xuất hiện khi người dùng bấm Hủy chỉnh sửa.

## 9. States
### 9.1 Loading
- Khi bấm "Lưu", nút chuyển sang trạng thái mờ, hiển thị vòng tròn xoay tải dữ liệu và vô hiệu hóa khả năng nhấp chọn để tránh lưu trùng lặp.

### 9.2 Empty
- Trường hợp người dùng xóa sạch phần Bio và liên kết (Được phép trống):
  - Hệ thống cho phép lưu bình thường và hiển thị placeholder mặc định trên trang hồ sơ.

### 9.3 Error
- **Lỗi tải ảnh quá nặng**: Người dùng tải ảnh đại diện vượt quá 5MB.
  - UI Copy: `"Dung lượng hình ảnh vượt quá giới hạn cho phép (Tối đa 5MB)."`
- **Lỗi đường truyền**: Lưu hồ sơ thất bại do lỗi kết nối mạng:
  - UI Copy: `"Không thể lưu thông tin lúc này. Vui lòng kiểm tra kết nối mạng và thử lại."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút "Lưu thay đổi" và nút cập nhật hình ảnh.

### 9.5 Permission Restricted
- Không áp dụng cho giao diện cài đặt cá nhân này.

### 9.6 Success / Completed
- Lưu thay đổi thành công:
  - Chuyển hướng người dùng về trang Hồ sơ cá nhân của họ mượt mà.
  - Hiện Toast thông báo: `"Cập nhật hồ sơ của bạn thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua nút tải ảnh (Máy ảnh nhỏ) sẽ làm nút đổi sang nền tối mờ bán trong suốt và phóng to nhẹ để tay dễ bấm chọn.

### 10.2 Focus
- Ô nhập liệuDisplay Name tự động nhận tiêu điểm (Auto-focus) ngay khi mở trang. Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các ô nhập liệu khi di chuyển bằng phím Tab di chuyển tiêu điểm.

### 10.3 Press / Tap
- Khi nhấn nút "Hủy" trong khi người dùng đã có thay đổi nội dung (ví dụ đã gõ thêm Bio):
  - Hiển thị hộp thoại phụ xác nhận: `"Hủy bỏ thay đổi? Những chỉnh sửa của bạn sẽ không được lưu."` với tùy chọn `"Tiếp tục sửa"` (màu xanh) và `"Hủy bỏ"` (màu đỏ).

### 10.4 Optimistic UI
- Sau khi bấm "Lưu thay đổi" thành công, cập nhật ngay các thông tin mới (như Bio, ảnh đại diện mới) lên trang cá nhân của chính người dùng trước khi nhận phản hồi xác thực lưu hoàn tất từ máy chủ.

### 10.5 Menu / Sheet
- Hộp thoại cảnh báo hủy bỏ thay đổi mở ra BottomSheet mượt mà dưới đáy màn hình di động dễ bấm chọn.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động lưu chỉnh sửa để đảm bảo tính nhất quán của cơ sở dữ liệu.

### 10.7 Motion
- Hiệu ứng trượt co giãn mượt mà của Image Cropper Modal khi mở rộng. Hoạt ảnh chuyển đổi ảnh đại diện trơn tru bằng CSS transition.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Hỗ trợ phím đóng nhanh `Escape` để thoát nhanh khỏi hộp thoại cắt xén ảnh.
- Đầy đủ nhãn `aria-label` cho tất cả các nút tải ảnh lên và ô nhập Bio.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Trình chỉnh sửa mở rộng 100% diện tích chiều ngang và dọc màn hình để tay dễ thao tác và bàn phím ảo hiển thị thoải mái nhất, tránh bị tràn chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `user_id` (integer, primary)
  - `edited_name` (string, max: 255)
  - `edited_bio` (string, max: 160)
  - `edited_website` (string, url format)
  - `avatar_file` (image file, optional)
  - `cover_file` (image file, optional)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadProfileDataForEditing()`
  - `updateUserProfile(name, bio, website, tempAvatar, tempCover)`
  - `uploadTemporaryImage(file, type)`

## 15. Authorization / Privacy Rules
- Quyền sở hữu nghiêm ngặt: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền gọi API chỉnh sửa hồ sơ này. Mọi truy vấn thay đổi từ tài khoản khác đều bị hệ thống chặn và ghi nhật ký cảnh báo bảo mật.

## 16. Analytics / Audit Events
- `profile_edit_opened`: Ghi nhận mỗi lần người dùng mở trình chỉnh sửa hồ sơ.
- `profile_edit_completed`: Ghi nhận sự kiện cập nhật hồ sơ thành công cùng mốc thời gian sửa đổi.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị thông tin học thuật cố định ở dạng đọc (Read-only) mờ và giải thích rõ quy trình cập nhật thông tin qua phòng Đào tạo để tránh việc sinh viên tự sửa sai lệch lớp học, mã số sinh viên.
- **Không được làm**: Cho phép đăng tải các hình ảnh đại diện nhạy cảm vi phạm quy tắc đạo đức nhà giáo / học đường. Hệ thống tự động tích hợp API kiểm duyệt ảnh nhạy cảm trước khi lưu.

## 18. Acceptance Criteria
- Trình chỉnh sửa tải và hiển thị chính xác thông tin hồ sơ cũ lên các ô nhập liệu tương ứng.
- Quy trình cắt ảnh và tải lên ảnh đại diện / ảnh bìa hoạt động trơn tru.
- Hộp thoại cảnh báo thay đổi chưa lưu (Unsaved Changes Dialog) hiển thị chính xác khi bấm Hủy.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng cắt ảnh hoạt động chính xác và lưu đúng kích thước tỉ lệ ảnh.
- [ ] Xác minh ô Bio tự động đếm ngược ký tự chính xác thời gian thực.
- [ ] Thử sửa đổi nội dung và bấm nút Hủy để kiểm tra hộp thoại cảnh báo hiển thị đúng cấu trúc chữ.
- [ ] Đảm bảo hồ sơ cá nhân sau khi sửa đổi hiển thị đúng nội dung mới trên trang cá nhân của sinh viên.

## 20. AI Agent Implementation Notes
- Sử dụng thư viện `Cropper.js` tích hợp trong Livewire bằng Alpine.js để xử lý cắt ảnh cực nhẹ ngay tại client.
- Thiết kế Model `UserProfile` có cơ chế tự động đồng bộ ảnh đại diện nhỏ sang bảng tin nhắn và bình luận trò chuyện để hiển thị đồng nhất.
---
