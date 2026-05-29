---
title: "User Profile Page"
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

# Trang Hồ Sơ Cá Nhân Người Dùng (User Profile)

## 1. Purpose
Trang Hồ Sơ Cá Nhân Người Dùng hiển thị đầy đủ thông tin định danh học đường, khóa học, khoa chuyên môn, danh sách các bài viết đã đăng tải, lịch sử hoạt động phản hồi của một thành viên trên UEConnect. Đây là tấm danh thiếp điện tử giúp sinh viên và giảng viên tự tin kết nối và giao lưu học thuật.

## 2. Product Context
Nằm trong giải pháp đảm bảo tính minh bạch, nghiêm túc và đáng tin cậy của Đại học Sư phạm TP.HCM, trang hồ sơ cá nhân hiển thị rõ ràng nhãn phân loại học đường (Sinh viên, Giảng viên, Cựu sinh viên) đã qua xác thực từ trường học, loại bỏ hoàn toàn các tài khoản ảo giả mạo.

## 3. User Goals
- **Đối với chính chủ**: Tự xem hồ sơ cá nhân, cập nhật ảnh đại diện, ảnh bìa, viết phần tự giới thiệu ngắn (Bio) sinh động và theo dõi số lượng bạn bè kết nối.
- **Đối với khách ghé thăm**: Tìm hiểu ngành học, niên khóa của người dùng để kết nối bạn bè, gửi tin nhắn trò chuyện trực tiếp, hoặc đăng ký xin tư vấn định hướng (nếu đối phương là Mentor).

## 4. Primary Users
- **Toàn bộ cộng đồng UEConnect**: Sinh viên, giảng viên, cựu sinh viên có nhu cầu xây dựng hình ảnh cá nhân và kết nối đồng môn.

## 5. Entry Points
- Nhấp vào ảnh đại diện hoặc tên người dùng từ Bảng tin chính, khung chat hoặc danh sách thành viên Câu lạc bộ.
- Bấm chọn **Trang cá nhân** (Profile Icon) trên thanh điều hướng chính.

## 6. Layout Strategy
Thiết kế trang trang nghiêm, sạch sẽ, phân bố không gian cân đối đan xen giữa bản sắc học đường và tính cá nhân phóng khoáng.

### 6.1 Desktop Layout
- Bố cục 2 cột sang trọng (chiều rộng tối đa 1000px):
  - Cột bên trái (Sidebar - 30%): Khung thông tin cá nhân cơ bản (Ảnh đại diện lớn, Họ tên, Nhãn đối tượng học đường, Khoa, Khóa, nút chỉnh sửa hồ sơ hoặc nút kết nối nhanh).
  - Cột bên phải (Main Stream Area - 70%): Bố cục tab ngang chuyển đổi mượt mà giữa `"Bài viết đã đăng"` và `"Bình luận / Phản hồi"` xếp dọc cuộn vô tận.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Sidebar bên trái thu gọn phần chữ mô tả chi tiết, chỉ hiển thị ảnh chân dung và nhãn đối tượng.
- Khung bài viết bên phải mở rộng tỷ lệ chiếm 75% chiều rộng nằm ngang màn hình với lề 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn viền mượt mà.
- Phần đầu trang hiển thị ảnh bìa rộng tỉ lệ 16:9, ảnh đại diện tròn lớn nổi đè lên góc dưới.
- Các nút hành động chính (Kết nối, Nhắn tin, Yêu cầu Mentor) được hiển thị dạng hàng ngang nổi bật ngay dưới phần giới thiệu tiểu sử (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thông tin nhận diện học đường (Academic Info Header)**:
  - Họ tên đầy đủ, Tên tài khoản `@username`, Huy hiệu xác minh chính danh.
  - Nhãn phân loại đối tượng: "Sinh viên", "Giảng viên", "Cựu sinh viên" kèm mã số sinh viên / mã cán bộ ẩn mờ.
  - Khoa chuyên ngành (Ví dụ: Khoa Toán - Tin học, Lớp K46.CNTT.A).
  - Số lượng người kết nối (Ví dụ: `152 kết nối`).
- **Tab hoạt động (Activity Tabs)**:
  - Tab "Bài viết": Lưới hiển thị các bài viết chia sẻ cá nhân.
  - Tab "Phản hồi": Lịch sử các bình luận thảo luận trong cộng đồng.
- **Tự giới thiệu bản thân (Biography Box)**:
  - Dòng tự giới thiệu ngắn về sở thích, mục tiêu học tập và link liên kết trang cá nhân bên ngoài.

## 8. Core Components
- **Academic Role Badge**: Nhãn nhỏ màu sắc phân loại đối tượng (Sinh viên: Xanh lá nhạt, Cựu sinh viên: Xanh ngọc bích, Giảng viên: Hổ phách sang trọng).
- **Verified Account Badge**: Huy hiệu tích xanh chính danh xác nhận tài khoản đã qua đối chiếu cơ sở dữ liệu trường HCMUE Portal.
- **Connection Action Trigger**: Nút bấm thay đổi linh hoạt trạng thái (Kết nối / Đã kết nối / Đang chờ duyệt) đổi màu viền mượt mà.
- **Biography Accordion**: Khung hiển thị phần giới thiệu bản thân tự động thu gọn nếu viết quá dài, thêm nút bấm "Xem thêm".

## 9. States
### 9.1 Loading
- Hiển thị hiệu ứng mờ nhòe (Blur-up) cho ảnh bìa và ảnh đại diện. Khung thông tin học tập và danh sách bài viết hiển thị các thanh Skeleton xám nhẹ nhấp nháy Shimmer liên tục.

### 9.2 Empty
- Khi người dùng chưa đăng bất kỳ bài viết nào trên trang cá nhân của họ:
  - UI Copy: `"Chưa có bài viết nào."`
  - Mô tả UI Copy: `"Những bài viết bạn đăng tải trên UEConnect sẽ xuất hiện ở đây."`

### 9.3 Error
- Lỗi tải thông tin trang do ID hồ sơ không hợp lệ hoặc tài khoản đã bị khóa vĩnh viễn:
  - UI Copy: `"Không thể tải thông tin hồ sơ. Tài khoản này không tồn tại hoặc đã bị khóa do vi phạm chính sách kiểm duyệt."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu xám ở đầu trang. Các tính năng Kết nối nhanh và gửi Tin nhắn bị khóa mờ xám.

### 9.5 Permission Restricted
- Trường hợp người dùng đặt chế độ hồ sơ "Riêng tư" mà khách truy cập chưa kết nối bạn bè:
  - Ẩn hoàn toàn tab Bài viết và Phản hồi, hiển thị biểu tượng ổ khóa lớn mờ cùng dòng chữ: `"Tài khoản này ở chế độ Riêng tư."`
  - Mô tả UI Copy: `"Hãy gửi lời mời kết nối để xem các bài viết và bình luận của họ nhé!"`

### 9.6 Success / Completed
- Đã gửi yêu cầu kết nối thành công:
  - Nút chuyển trạng thái sang màu xám nhạt với nhãn `"Đang chờ duyệt"`.
  - Hiện Toast thông báo: `"Đã gửi yêu cầu kết nối thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua Huy hiệu xác minh sẽ hiển thị popover giải thích: `"Hồ sơ đã được xác minh bằng mã số sinh viên chính thức của trường."`

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các nút tương tác (Kết nối, Nhắn tin) và các tab hoạt động khi người dùng di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp nút "Kết nối" sẽ chuyển trạng thái ngay lập tức trên di động kết hợp rung nhẹ phản hồi xúc giác. Bấm chọn nút Nhắn tin sẽ tự động mở phòng chat chi tiết tương ứng.

### 10.4 Optimistic UI
- Khi bấm "Kết nối", nút lập tức chuyển sang trạng thái "Đang chờ duyệt" hoặc "Đã kết nối" trong vòng 100ms trước khi nhận phản hồi API máy chủ để tạo trải nghiệm tương tác cực nhanh.

### 10.5 Menu / Sheet
- Nút chia sẻ trang cá nhân mở ra BottomSheet mượt mà trên Mobile chứa tùy chọn: Sao chép liên kết hồ sơ cá nhân, Chia sẻ mã QR hồ sơ hoặc Báo cáo vi phạm.

### 10.6 Toast / Undo
- Hành động hủy lời mời kết nối thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh lời mời trong vòng 4 giây.

### 10.7 Motion
- Hiệu ứng thu phóng nhẹ ảnh đại diện tròn khi người dùng rê chuột qua (`scale-105 duration-300`). Chuyển tab hoạt động trượt mượt mà.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi ảnh bìa và ảnh đại diện phải có thuộc tính `alt` mô tả (Ví dụ: `alt="Ảnh đại diện của sinh viên Nguyễn Văn A"`).
- Hỗ trợ phím Space / Enter để kích hoạt chuyển đổi giữa các tab hoạt động mượt mà.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Ảnh bìa co dãn tỉ lệ 16:9 sắc nét, ảnh đại diện nổi bật đè lên góc dưới ảnh bìa, phần thông tin họ tên và nhãn phân loại học đường hiển thị rõ nét ngay dưới để tối ưu hóa diện tích hiển thị dọc.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `profile_user_id` (integer, primary)
  - `full_name` (string)
  - `role_type` (enum: `student`, `alumni`, `mentor`, `faculty`)
  - `faculty_name` (string)
  - `class_code` (string)
  - `biography` (text)
  - `is_private` (boolean)
  - `connection_status` (enum: `none`, `pending`, `connected`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadUserProfileData()`
  - `toggleConnectRequest(userId)`
  - `fetchUserPosts(userId)`
  - `fetchUserReplies(userId)`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin hồ sơ: Chỉ chính người dùng sở hữu tài khoản mới có quyền cập nhật ảnh bìa, ảnh đại diện, viết Bio mới. Các tài khoản khác chỉ có quyền xem thông tin công khai hoặc thông tin giới hạn tùy theo thiết lập riêng tư của chủ hồ sơ.

## 16. Analytics / Audit Events
- `profile_page_viewed`: Ghi nhận số lượt mở xem chi tiết hồ sơ cá nhân.
- `profile_share_clicked`: Theo dõi số lượt chia sẻ liên kết hồ sơ ra bên ngoài.

## 17. Do / Don't
- **Nên làm**: Luôn hiển thị thông tin Khoa đào tạo của sinh viên nổi bật để tạo sự nhận diện và gắn kết học thuật đồng môn tốt nhất trong trường HCMUE.
- **Không được làm**: Cho phép hiển thị công khai thông tin lý lịch cá nhân nhạy cảm (như Mã số sinh viên đầy đủ, Số điện thoại cá nhân, Email cá nhân) đối với người dùng lạ chưa kết nối bạn bè nhằm bảo vệ sinh viên khỏi các hành vi lừa đảo trực tuyến.

## 18. Acceptance Criteria
- Hiển thị đầy đủ, chính xác thông tin học đường, ảnh bìa, ảnh đại diện, và tiểu sử Bio của người dùng.
- Tab Bài viết và Phản hồi tải đúng dữ liệu, hỗ trợ phân trang cuộn vô tận mượt mà.
- Trạng thái kết nối bạn bè đồng bộ tức thời trên cơ sở dữ liệu và hệ thống chat trò chuyện.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nhãn đối tượng học đường (Sinh viên, Giảng viên...) hiển thị đúng màu sắc phân loại.
- [ ] Xác minh tài khoản riêng tư ẩn bài viết thành công khi khách chưa kết nối truy cập.
- [ ] Thử nghiệm bấm nút kết nối nhanh và đảm bảo đổi trạng thái nút chính xác mượt mà.
- [ ] Đảm bảo giao diện responsive hiển thị cân đối ảnh bìa và ảnh đại diện trên màn hình di động đứng và ngang.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu đệm (Caching) hồ sơ cá nhân trong Redis với thời gian hết hạn (TTL) là 15 phút để tăng tốc độ phản hồi trang khi có lượt truy cập lớn.
- Tích hợp công nghệ nén ảnh tự động tại client trước khi tải lên làm ảnh đại diện để giảm dung lượng tải của máy chủ và tăng tốc độ hiển thị hình ảnh trên di động.
---