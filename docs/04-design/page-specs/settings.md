---
title: "Settings Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/settings.md"
related_design_docs:
  - "../04-design/settings-navigation.md"
related_system_docs:
  - "../05-system-architecture/settings-service.md"
related_database_docs:
  - "../06-database/users-table.md"
related_api_docs:
  - "../07-api/settings-api.md"
---

# Trang Cài Đặt Hệ Thống (Settings Index)

## 1. Purpose
Trang Cài Đặt Hệ Thống (Settings Index) là trung tâm điều hướng cấu hình toàn bộ tài khoản người dùng trên UEConnect, cho phép sinh viên và giảng viên điều chỉnh các tùy chọn bảo mật, thông báo đẩy, thay đổi giao diện (Nền sáng / Nền tối / Theo hệ thống), cấu hình ngôn ngữ hiển thị và truy cập nhanh các dịch vụ hỗ trợ học đường.

## 2. Product Context
Nằm trong triết lý xây dựng ứng dụng tiện lợi, tinh giản theo chuẩn Threads, trang cài đặt loại bỏ hoàn toàn các tầng lớp thực đơn đa cấp phức tạp. Thay vào đó, nó trình bày cấu trúc cây phẳng, rõ ràng, giúp người dùng dễ dàng tìm thấy thiết lập mong muốn trong vòng tối đa 2 lần chạm bấm.

## 3. User Goals
- Truy cập nhanh các liên kết chỉnh sửa thông tin cá nhân và cài đặt quyền riêng tư bảo mật.
- Bật/tắt các loại thông báo đẩy theo nhu cầu (Thông báo thích bài, bình luận, tin nhắn, sự kiện trường).
- Thay đổi chủ đề giao diện (Theme configuration) tương thích tốt nhất với thị giác cá nhân.
- Tiếp cận nhanh trang hỗ trợ, báo cáo kỹ thuật và chính sách sử dụng dịch vụ của hệ thống.

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Có nhu cầu thay đổi trải nghiệm hiển thị và bảo mật tài khoản.

## 5. Entry Points
- Nhấp chọn biểu tượng **Cài đặt** (Settings Icon) ở góc phải trên cùng trang cá nhân của mình.
- Bấm chọn **Cài đặt** từ thanh thực đơn điều hướng chân trang (Mobile Navigation Drawer).

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật các dòng thiết lập thoáng đãng, phân chia rõ ràng theo từng phân nhóm chức năng lớn bằng các khoảng trống hợp lý.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột thanh lịch (chiều rộng tối đa 620px).
- Các dòng cài đặt được xếp dọc, mỗi dòng gồm biểu tượng chức năng trái, tiêu đề lớn, mô tả phụ ẩn mờ và biểu tượng mũi tên chỉ sang phải ở lề phải báo hiệu liên kết chuyển trang.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ dàng nhấp chọn.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình mượt mà.
- Khoảng cách chạm nút rộng rãi (`touch-target 44px`) và phông chữ rõ ràng giúp tay dễ thao tác chạm gõ nhanh mà không bị nhầm lẫn.
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thông tin tài khoản chính (Account Identity Overview)**:
  - Hiển thị tóm tắt ảnh đại diện tròn nhỏ, Họ tên người dùng, nhãn đối tượng học đường (Sinh viên/Mentor) và lối tắt "Chỉnh sửa hồ sơ".
- **Nhóm cài đặt cốt lõi (Core Preferences Group)**:
  - Cài đặt quyền riêng tư (Privacy Settings Link).
  - Cấu hình thông báo (Notifications Settings Link).
  - Cấu hình giao diện (Appearance / Theme Config).
  - Cấu hình ngôn ngữ (Language - Tiếng Việt / English).
- **Nhóm bảo mật & Hỗ trợ (Safety & Support Group)**:
  - Danh sách tài khoản đã chặn (Blocked Users Link).
  - Hỗ trợ & Phản hồi kỹ thuật (Help & Feedback Link).
  - Điều khoản dịch vụ & Chính sách bảo mật (Terms & Privacy Link).
- **Hành động hệ thống (System Actions)**:
  - Nút Đăng xuất (Log Out Button) màu đỏ cam ấm áp nằm ở dưới cùng trang.

## 8. Core Components
- **Navigation Row Item**: Dòng liên kết cài đặt tích hợp biểu tượng sắc nét trái và mũi tên chỉ dẫn phải mượt mà.
- **Theme Mode Selector Button**: Hộp chọn 3 tùy chọn tròn nhỏ (Sáng / Tối / Hệ thống) đổi màu nền khi người dùng nhấp chọn.
- **Log Out Trigger**: Nút đăng xuất lớn màu đỏ nhạt tạo cảm giác nghiêm túc, an toàn.
- **Language Picker Dropdown**: Hộp chọn ngôn ngữ hiển thị bo tròn góc tinh tế.

## 9. States
### 9.1 Loading
- Hiển thị các dòng cài đặt trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu tài khoản từ máy chủ.

### 9.2 Empty
- Không áp dụng cho giao diện cài đặt hệ thống này.

### 9.3 Error
- Lỗi kết nối mạng khi thực hiện cập nhật tùy chọn ngôn ngữ hoặc chủ đề giao diện:
  - Hệ thống tự động khôi phục lại trạng thái hiển thị cũ.
  - Hiện Toast thông báo lỗi: `"Không thể cập nhật cấu hình thiết lập. Vui lòng kiểm tra kết nối mạng."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng cập nhật cài đặt tạm thời bị khóa mờ xám.

### 9.5 Permission Restricted
- Không áp dụng cho giao diện cài đặt cá nhân này.

### 9.6 Success / Completed
- Đăng xuất thành công:
  - Xóa sạch mã phiên đăng nhập (Session tokens) an toàn.
  - Chuyển hướng người dùng về trang Đăng nhập (`auth`) mượt mà kèm Toast thông báo: `"Bạn đã đăng xuất tài khoản an toàn."`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua mỗi dòng thiết lập sẽ làm đổi màu nền sang màu xám kem dịu (`bg-gray-50`) và đẩy nhẹ biểu tượng mũi tên phải sang bên phải 2px để báo hiệu khả năng tương tác.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các dòng liên kết khi di chuyển bằng phím Tab di chuyển tiêu điểm. Hỗ trợ phím Enter để kích hoạt chuyển hướng trang nhanh.

### 10.3 Press / Tap
- Thao tác nhấp chọn đăng xuất sẽ mở ra hộp thoại xác nhận ở giữa màn hình (Desktop) hoặc BottomSheet trượt lên mượt mà (Mobile) để tránh người dùng bấm nhầm thoát tài khoản.

### 10.4 Optimistic UI
- Khi bấm chuyển đổi chủ đề hiển thị (ví dụ sang Nền tối), giao diện hệ thống lập tức thay đổi tông màu (Dark Mode class injection) thời gian thực dưới 50ms trước khi nhận phản hồi ghi nhận cấu hình thành công từ máy chủ.

### 10.5 Menu / Sheet
- Hộp thoại xác nhận đăng xuất mở ra BottomSheet mượt mà dưới đáy màn hình di động dễ bấm chọn với tùy chọn `"Đăng xuất"` (màu đỏ) và `"Hủy"` (màu xanh).

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động đăng xuất để đảm bảo tính an toàn bảo mật tuyệt đối cho tài khoản.

### 10.7 Motion
- Hoạt ảnh chuyển màu chủ đề mượt mà bằng CSS Transition trong vòng 300ms dễ chịu cho thị giác, tránh việc chớp sáng làm lóa mắt.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi dòng liên kết cài đặt phải có thuộc tính mô tả chi tiết cho các trình đọc màn hình: `aria-label="Cài đặt [Tên cài đặt], nhấp để mở rộng"`.
- Hỗ trợ phím Escape để đóng nhanh BottomSheet xác nhận đăng xuất.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị mô tả phụ dưới mỗi dòng thiết lập để dành khoảng trống tối đa cho tiêu đề, tránh bị chồng chéo chữ hiển thị trên màn hình dọc hẹp.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `user_theme` (enum: `light`, `dark`, `system`)
  - `user_language` (enum: `vi`, `en`)
  - `notification_preferences` (array of booleans)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadUserSettings()`
  - `updateLanguagePreference(languageCode)`
  - `updateThemePreference(themeMode)`
  - `performLogout()`

## 15. Authorization / Privacy Rules
- Bảo mật quyền sở hữu: Chỉ chính chủ tài khoản đăng nhập hợp lệ mới được truy cập trang cài đặt cá nhân này. Mọi truy vấn thay đổi dữ liệu trái phép đều bị hệ thống chặn đứng và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `settings_page_viewed`: Ghi nhận mỗi lần người dùng mở xem trang cài đặt hệ thống.
- `theme_changed`: Theo dõi tỉ lệ lựa chọn Dark Mode/Light Mode để tối ưu hóa thiết kế giao diện màu sắc trong tương lai.

## 17. Do / Don't
- **Nên làm**: Luôn cung cấp đầy đủ thông tin giải thích ngắn gọn dưới mỗi dòng thiết lập để người dùng hiểu rõ tác động cấu hình hệ thống.
- **Không được làm**: Cho phép tự động lưu mật khẩu đăng nhập hoặc các dữ liệu thẻ thanh toán của sinh viên công khai trong phần cài đặt mặc định để tránh nguy cơ rò rỉ dữ liệu.

## 18. Acceptance Criteria
- Các dòng cài đặt hoạt động ổn định, điều hướng chính xác đến các phân nhóm thiết lập tương ứng.
- Tính năng chuyển đổi màu sắc chủ đề hiển thị (Theme) hoạt động mượt mà và lưu chính xác giá trị vào LocalStorage.
- Quy trình đăng xuất xóa sạch mã phiên đăng nhập an toàn và điều hướng đúng về trang Đăng nhập.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng điều hướng chính xác của tất cả dòng liên kết cài đặt.
- [ ] Xác minh tính năng chuyển đổi ngôn ngữ hiển thị cập nhật chính xác toàn bộ chữ trên giao diện.
- [ ] Thử nghiệm bấm nút Đăng xuất và đảm bảo hộp thoại xác nhận hiển thị đúng cấu trúc chữ.
- [ ] Đảm bảo sau khi đăng xuất thành công, bấm nút Quay lại (Back browser) không thể truy cập lại trang cài đặt này nữa.

## 20. AI Agent Implementation Notes
- Tận dụng Alpine.js để quản lý trạng thái chuyển đổi chủ đề Dark Mode trực tiếp tại Client bằng cách thêm/bớt class `dark` vào thẻ `html` chính, mang lại tốc độ phản hồi tức thì và mượt mà nhất.
- Thiết kế cơ cấu bảo mật kiểm tra Token (Sanctum Tokens revocation) chặt chẽ tại tầng Backend khi thực hiện đăng xuất để đảm bảo xóa sạch dấu vết đăng nhập trên máy chủ cơ sở dữ liệu.
---