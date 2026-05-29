---
title: "Privacy Settings Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/privacy-controls.md"
related_design_docs:
  - "../04-design/privacy-controls.md"
related_system_docs:
  - "../05-system-architecture/privacy-service.md"
related_database_docs:
  - "../06-database/blocks-table.md"
related_api_docs:
  - "../07-api/blocks-api.md"
---

# Trang Cài Đặt Quyền Riêng Tư (Privacy Settings)

## 1. Purpose
Trang Cài Đặt Quyền Riêng Tư cung cấp cho người dùng các công cụ quản lý bảo mật cá nhân, kiểm soát quyền hiển thị hồ sơ (Công khai / Riêng tư), điều chỉnh quyền nhận tin nhắn từ người lạ, bật/tắt khả năng xuất hiện trong mục tìm kiếm Khám phá, và quản lý liên kết danh sách tài khoản đã chặn trong hệ sinh thái UEConnect.

## 2. Product Context
Trong môi trường học đường số HCMUE, việc bảo mật thông tin cá nhân và bảo vệ sinh viên khỏi các nguy cơ quấy rối trực tuyến là vô cùng quan trọng. Trang cài đặt quyền riêng tư được thiết kế trực quan, dễ thao tác theo tiêu chuẩn bảo mật hiện đại của Threads, mang lại sự an tâm tối đa cho sinh viên.

## 3. User Goals
- Chuyển đổi trạng thái tài khoản giữa chế độ Công khai (mọi người xem hồ sơ) và Riêng tư (chỉ bạn bè kết nối xem hồ sơ).
- Giới hạn quyền gửi lời mời nhắn tin và kết nối (Chỉ nhận từ bạn bè cùng khóa/cùng lớp).
- Bật/tắt tùy chọn xuất hiện trong công cụ Khám phá đề xuất bạn học (Discovery Visibility).
- Truy cập nhanh trang Danh sách chặn để bỏ chặn hoặc thêm tài khoản chặn mới.

## 4. Primary Users
- **Toàn bộ học viên, sinh viên, cựu sinh viên và giảng viên HCMUE**: Có nhu cầu bảo mật thông tin riêng tư và thiết lập không gian giao tiếp cá nhân an toàn.

## 5. Entry Points
- Nhấp chọn **Cài đặt tài khoản** -> Chọn mục **Quyền riêng tư & Bảo mật** (Privacy Settings).

## 6. Layout Strategy
Thiết kế tập trung vào sự tối giản, các danh mục cấu hình được xếp dọc thoáng đãng giúp người dùng dễ dàng quét mắt đọc hiểu và chọn nhanh.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 620px).
- Các thiết lập được nhóm thành các nhóm chức năng lớn bằng các hộp viền mỏng bo tròn góc mượt mà.
- Nút bật tắt (Toggle Switches) nằm thẳng hàng lề phải thẻ thiết lập để dễ nhấp chuột.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp tay dễ dàng chạm bấm.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình.
- Các nút bật/tắt Toggle có kích thước phình to dễ chạm (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Trạng thái tài khoản (Account Privacy)**:
  - Tùy chọn bật/tắt: "Tài khoản riêng tư" (Private Account Toggle).
- **Quyền nhắn tin & Kết nối (Message & Connection Permissions)**:
  - Quyền nhận tin nhắn: Nhận từ "Mọi người", "Chỉ bạn bè kết nối", "Không nhận từ ai".
  - Quyền nhận lời mời kết nối: "Mọi người", "Bạn của bạn bè".
- **Khám phá & Tìm kiếm (Discovery & Search Control)**:
  - Tùy chọn bật/tắt: "Cho phép xuất hiện trong mục Khám phá".
- **Quản lý danh sách chặn (Blocked Accounts)**:
  - Dòng liên kết điều hướng nhanh sang trang Blocked Users.

## 8. Core Components
- **Toggle Switch Button**: Nút gạt bật/tắt trạng thái chuyển động mượt màu xanh dương HCMUE khi bật, màu xám khi tắt.
- **Select Option Dropdown**: Hộp chọn danh mục thả xuống bo tròn góc tinh tế.
- **Privacy Info Card**: Khung thẻ thông tin hiển thị các mô tả chi tiết, ngắn gọn dưới mỗi thiết lập để sinh viên hiểu rõ tác động bảo mật.
- **Link Indicator Icon**: Biểu tượng mũi tên nhỏ chỉ sang phải báo hiệu liên kết chuyển trang.

## 9. States
### 9.1 Loading
- Hiển thị các khối thiết lập trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải trạng thái cấu hình từ máy chủ.

### 9.2 Empty
- Không áp dụng cho trang thiết lập quyền riêng tư.

### 9.3 Error
- Lỗi kết nối mạng khi thực hiện gạt nút Toggle:
  - Nút Toggle lập tức gạt khôi phục lại trạng thái cũ (Rollback).
  - Hiện Toast thông báo lỗi màu đỏ cam: `"Không thể cập nhật thiết lập riêng tư lúc này. Vui lòng kiểm tra kết nối mạng."`

### 9.4 Offline / Reconnecting
- Toàn bộ nút Toggle và hộp chọn bị khóa mờ xám. Hiển thị thông báo: `"Bạn đang ngoại tuyến. Các thay đổi riêng tư chỉ hoạt động khi bạn trực tuyến."`

### 9.5 Permission Restricted
- Không áp dụng cho giao diện cài đặt cá nhân này.

### 9.6 Success / Completed
- Cập nhật thiết lập thành công:
  - Nút Toggle chuyển màu mượt mà.
  - Hiện Toast thông báo ở góc dưới màn hình: `"Đã cập nhật thiết lập riêng tư thành công!"` trong vòng 3 giây.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua các khung thẻ cài đặt sẽ làm màu nền thẻ đổi sang màu xám kem dịu (`bg-gray-50`) để báo hiệu khả năng tương tác nhấp chọn.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút Toggle và hộp chọn khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap (Optimistic UI switch)
- Thao tác nhấp gạt nút Toggle sẽ làm nút chuyển trạng thái hiển thị và đổi màu sắc ngay lập tức trước khi nhận phản hồi xác nhận từ máy chủ cơ sở dữ liệu để tạo cảm giác tốc độ phản hồi cực nhanh dưới 50ms.

### 10.4 Optimistic UI
- Đối với các switch bật tắt chế độ riêng tư, chuyển trạng thái hiển thị của nút ngay lập tức. Nếu API thất bại, rollback lại trạng thái ban đầu và hiện Toast thông báo lỗi mạng.

### 10.5 Menu / Sheet
- Hộp chọn quyền nhắn tin trên di động mở ra một BottomSheet chi tiết dạng danh sách tròn nhấp chọn trực quan dễ bấm chạm.

### 10.6 Toast / Undo
- Cập nhật thiết lập thành công hiển thị Toast thông báo ở góc dưới màn hình. Hỗ trợ nút "Hoàn tác" đối với các thao tác chuyển đổi tài khoản Riêng tư để khôi phục nhanh chế độ Công khai ban đầu.

### 10.7 Motion
- Hoạt ảnh trượt ngang mượt mà của thanh tròn nhỏ bên trong nút Toggle khi được gạt bật/tắt diễn ra trong vòng 150ms cực kỳ dễ chịu cho mắt.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi nút Toggle phải có thuộc tính `aria-checked` phản hồi chính xác trạng thái bật/tắt và thẻ mô tả `aria-label` chi tiết (Ví dụ: `aria-label="Bật chế độ tài khoản riêng tư"`).
- Hỗ trợ phím Space / Enter để kích hoạt gạt Toggle nhanh chóng.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Kích thước chữ mô tả dưới mỗi thiết lập thu nhỏ nhẹ (12px) để dành khoảng trống tối đa cho tiêu đề cài đặt và nút Toggle gạt, tránh bị chồng lấp chữ.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `is_private_account` (boolean)
  - `message_permission` (enum: `everyone`, `connections`, `nobody`)
  - `is_discoverable` (boolean)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadPrivacySettings()`
  - `updateAccountPrivacy(isPrivate)`
  - `updateMessagePermission(permission)`
  - `updateDiscoverableStatus(isDiscoverable)`

## 15. Authorization / Privacy Rules
- Bảo mật quyền sở hữu: Chỉ chính người dùng đăng nhập hợp lệ mới có quyền xem và sửa đổi thiết lập riêng tư cá nhân này. Mọi truy vấn trái phép đều bị hệ thống chặn và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `privacy_settings_viewed`: Ghi nhận lượt mở xem trang cài đặt riêng tư.
- `account_privacy_toggled`: Ghi nhận sự thay đổi trạng thái tài khoản công khai/riêng tư để đánh giá xu hướng bảo mật của sinh viên trường.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị giải thích chi tiết tác động của việc chuyển tài khoản sang chế độ Riêng tư (Ví dụ: "Khi bật, chỉ những người bạn đã phê duyệt kết nối mới có thể xem bài đăng và danh sách bạn bè của bạn") để sinh viên nắm rõ thông tin.
- **Không được làm**: Cho phép công khai thông tin cá nhân nhạy cảm của sinh viên (như Email sinh viên, Số điện thoại) khi tài khoản được chuyển sang chế độ Riêng tư.

## 18. Acceptance Criteria
- Các nút Toggle và hộp chọn hoạt động ổn định, lưu chính xác trạng thái cài đặt vào cơ sở dữ liệu.
- Tài khoản khi chuyển sang chế độ Riêng tư sẽ tự động chặn khả năng xem bài đăng từ các tài khoản chưa kết nối trong toàn hệ thống.
- Giao diện thích ứng sắc nét và hiển thị mượt mà trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nút gạt Toggle hoạt động chính xác và lưu đúng giá trị boolean vào bảng users cơ sở dữ liệu.
- [ ] Xác minh tài khoản riêng tư không xuất hiện bài viết trên Bảng tin của người lạ chưa kết nối.
- [ ] Thử nghiệm tắt mạng và gạt Toggle để kiểm tra cơ chế Rollback hoạt động ổn định không.
- [ ] Đảm bảo liên kết sang trang Blocked Users dẫn đúng địa chỉ và truyền đúng mã thông tin.

## 20. AI Agent Implementation Notes
- Sử dụng Livewire kết hợp cùng Alpine.js để quản lý trạng thái của các nút Toggle gạt thời gian thực tại Client nhằm đạt hiệu năng giao diện phản hồi nhanh nhất.
- Thiết kế hệ thống Middleware bảo mật nghiêm ngặt để chặn tất cả các yêu cầu đọc dữ liệu bài đăng của tài khoản riêng tư từ phía người dùng chưa kết nối tại tầng API cốt lõi.
---
