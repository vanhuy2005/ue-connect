---
title: "Club Detail Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/clubs-management.md"
related_design_docs:
  - "../04-design/club-spaces.md"
related_system_docs:
  - "../05-system-architecture/club-service.md"
related_database_docs:
  - "../06-database/clubs-table.md"
related_api_docs:
  - "../07-api/clubs-api.md"
---

# Trang Chi Tiết Câu Lạc Bộ (Club Detail)

## 1. Purpose
Trang Chi Tiết Câu Lạc Bộ cung cấp không gian tương tác, thông tin học thuật, giới thiệu các hoạt động nổi bật, sự kiện sắp diễn ra và danh sách thành viên Ban điều hành của một Câu lạc bộ / Đội / Nhóm cụ thể thuộc trường Đại học Sư phạm TP.HCM. Trang này giúp sinh viên dễ dàng tìm hiểu và đăng ký tham gia CLB yêu thích.

## 2. Product Context
CLB/Đội/Nhóm là một phần không thể thiếu trong đời sống sinh viên HCMUE. Trang chi tiết CLB đóng vai trò kết nối trực tuyến các hoạt động phong trào, nghiên cứu khoa học và kỹ năng sống từ thực tế vào hệ sinh thái số của UEConnect.

## 3. User Goals
- **Đối với sinh viên**: Tìm hiểu mục tiêu hoạt động của CLB, xem các bài đăng, ảnh hoạt động, sự kiện sắp tới và đăng ký tham gia làm thành viên chính thức.
- **Đối với Ban chủ nhiệm CLB**: Quản lý nội dung giới thiệu, đăng tải bài viết tuyển thành viên, lên lịch sự kiện giao lưu, duyệt đơn ứng tuyển của các sinh viên mới.

## 4. Primary Users
- **Sinh viên HCMUE**: Người muốn tham gia sinh hoạt ngoại khóa và phát triển kỹ năng.
- **Ban điều hành CLB**: Admin quản lý trang CLB và điều phối các hoạt động phong trào.

## 5. Entry Points
- Nhấp chọn thẻ CLB bất kỳ từ danh sách trang **Danh sách Câu lạc bộ** (Clubs Directory).
- Nhấp chọn từ các thẻ gợi ý CLB nổi bật trên Bảng tin chính hoặc từ trang thông tin cá nhân của một thành viên Ban chủ nhiệm.

## 6. Layout Strategy
Thiết kế tập trung làm nổi bật bản sắc riêng của từng Câu lạc bộ thông qua màu sắc chủ đạo linh hoạt và hình ảnh hoạt động năng động.

### 6.1 Desktop Layout
- Bố cục 3 cột chuyên nghiệp:
  - Cột trái (Sidebar): Khung thông tin tổng quan CLB (Logo, Tên CLB, Trực thuộc Khoa/Đoàn trường, Số lượng thành viên, Liên kết mạng xã hội chính thức, Nút Đăng ký tham gia).
  - Cột giữa: Dòng thời gian hiển thị bài viết hoạt động, hình ảnh và tab sự kiện sắp diễn ra.
  - Cột phải: Danh sách Ban chủ nhiệm CLB và các hoạt động tình nguyện mới nhất.
- Khoảng cách lề: 24px. Chiều rộng tối đa: 1140px.

### 6.2 Tablet Layout
- Cột bên phải thu gọn xuống dưới chân trang.
- Cột trái và cột giữa chia tỷ lệ 35% - 65% cân đối trên màn hình nằm ngang.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc liên tục.
- Ảnh đại diện CLB lớn, hiển thị mượt cùng với hiệu ứng thu nhỏ thanh Header khi cuộn màn hình lên (Sticky Collapse Header).
- Nút "Đăng ký thành viên" luôn được cố định ở chân màn hình (Sticky Bottom Button) để tối ưu hóa tỷ lệ chuyển đổi đăng ký.

## 7. Information Architecture
- **Thông tin nhận diện CLB**:
  - Logo CLB, Tên đầy đủ, Slogan hoặc mô tả ngắn gọn.
  - Đơn vị trực thuộc quản lý (Ví dụ: Trực thuộc Đoàn trường HCMUE hoặc Khoa Vật lý).
  - Số lượng thành viên hiện tại.
- **Menu Tab Điều Hướng Nội Bộ**:
  - Tab "Giới thiệu": Tầm nhìn, sứ mệnh, điều kiện gia nhập.
  - Tab "Bài viết": Các tin tức, hoạt động thường niên của CLB.
  - Tab "Sự kiện": Lịch sinh hoạt định kỳ, workshop sắp tổ chức.
- **Ban chủ nhiệm**:
  - Họ tên, chức vụ trong CLB (Chủ nhiệm, Phó chủ nhiệm, Trưởng ban Truyền thông) kèm link hồ sơ UEConnect.

## 8. Core Components
- **Join Club Button**: Nút đăng ký tham gia nổi bật màu xanh dương hoặc tùy biến theo màu nhận diện của CLB.
- **Leadership Widget**: Khung hiển thị danh sách Ban điều hành dưới dạng các thẻ chân dung nhỏ gọn.
- **Event Card**: Thẻ thông tin sự kiện sắp diễn ra tích hợp nút "Đăng ký tham gia sự kiện".
- **Empty State Component**: Hiển thị trong tab Sự kiện nếu CLB chưa có sự kiện nào sắp tới, sử dụng hình ảnh chiếc loa mini cùng lời mời đề xuất ý kiến.

## 9. States
### 9.1 Loading
- Toàn bộ giao diện hiển thị hiệu ứng Shimmer mượt mà cho logo, tên CLB và các dòng giới thiệu. Nút đăng ký chuyển sang màu xám nhạt ở trạng thái khóa.

### 9.2 Empty
- Khi tab "Bài viết" không có nội dung:
  - UI Copy: `"Câu lạc bộ chưa đăng tải bài viết nào mới."`

### 9.3 Error
- Lỗi tải thông tin chi tiết CLB do đường truyền yếu hoặc CLB đã bị giải thể:
  - UI Copy: `"Không thể tải thông tin Câu lạc bộ. Vui lòng thử lại sau ít phút hoặc liên hệ Đoàn trường."`

### 9.4 Offline / Reconnecting
- Hiển thị thông báo màu xám ở đầu trang: `"Bạn đang xem dữ liệu lưu trong bộ nhớ tạm."`

### 9.5 Permission Restricted
- Trường hợp CLB đặt chế độ "Thành viên nội bộ", sinh viên chưa gia nhập sẽ thấy biểu tượng ổ khóa ở tab "Bài viết" và thông điệp: `"Tab này chỉ hiển thị cho thành viên chính thức của Câu lạc bộ."`

### 9.6 Success / Completed
- Đăng ký gia nhập thành công:
  - UI Copy: `"Đơn đăng ký của bạn đã được gửi đến Ban chủ nhiệm CLB duyệt thành công!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua ảnh sự kiện hoặc thẻ Ban chủ nhiệm sẽ phóng to nhẹ (`scale-102`) kết hợp đổ bóng nhẹ mềm mại dưới thẻ để tạo chiều sâu giao diện.

### 10.2 Focus
- Vòng viền màu xanh ngọc bích bao quanh các phần tử điều hướng chính khi người dùng di chuyển tiêu điểm bằng phím Tab.

### 10.3 Press / Tap
- Khi nhấn nút "Đăng ký tham gia", mở ngay biểu mẫu đăng ký thu nhỏ dạng BottomSheet tiện lợi để người dùng điền thông tin nhanh (Họ tên, Mã số sinh viên, Lý do gia nhập).

### 10.4 Optimistic UI
- Sau khi bấm đăng ký gia nhập CLB, nút chuyển ngay sang trạng thái `"Đang chờ duyệt"` mà không làm treo giao diện.

### 10.5 Menu / Sheet
- Nút chia sẻ CLB mở ra danh mục tùy chọn (Sao chép liên kết, Chia sẻ qua Messenger, Tải mã QR kết nối của CLB).

### 10.6 Toast / Undo
- Toast gửi đơn ứng tuyển thành công hỗ trợ tùy chọn hủy đơn ứng tuyển ngay lập tức nếu sinh viên muốn thay đổi quyết định.

### 10.7 Motion
- Hiệu ứng trượt tab nội bộ mượt mà trong vòng 150ms. Sự kiện cuộn màn hình thu nhỏ Header chuyển tiếp trơn tru bằng CSS Transition.

## 11. Accessibility Requirements
- Hỗ trợ đầy đủ các thẻ mô tả hình ảnh cho Logo và ảnh bìa CLB.
- Các tab chức năng được lập chỉ mục phím Tab tuần tự hợp lý.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Logo CLB hiển thị ở giữa màn hình đè lên ảnh bìa, các thông tin thống kê số lượng thành viên chuyển thành hàng ngang nhỏ gọn ngay dưới tên CLB.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `club_id` (integer, primary)
  - `club_name` (string)
  - `description` (text)
  - `member_count` (integer)
  - `membership_status` (enum: `non_member`, `pending`, `member`, `leader`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `applyForMembership(clubId, reasonText)`
  - `cancelMembershipApplication(clubId)`
  - `leaveClub(clubId)`
  - `postToClubTimeline(clubId, contentText)`

## 15. Authorization / Privacy Rules
- Chỉ có Ban chủ nhiệm CLB mới có quyền chỉnh sửa thông tin mô tả, logo, ảnh bìa và đăng bài viết chính thức dưới danh nghĩa CLB. Sinh viên thường chỉ có quyền thảo luận dưới dạng bình luận hoặc gửi bài viết chờ duyệt.

## 16. Analytics / Audit Events
- `club_detail_page_viewed`: Ghi nhận lượt truy cập trang chi tiết CLB.
- `club_membership_applied`: Ghi nhận thông tin sinh viên gửi đơn ứng tuyển gia nhập CLB.

## 17. Do / Don't
- **Nên làm**: Cho phép tùy biến ảnh bìa CLB để thể hiện màu sắc sinh hoạt đặc trưng riêng biệt.
- **Không được làm**: Cho phép tự động phê duyệt hàng loạt đơn gia nhập mà không thông qua bất kỳ bước kiểm tra mã số sinh viên nào đối với các CLB chính quy học thuật.

## 18. Acceptance Criteria
- Hiển thị đầy đủ thông tin CLB, Ban chủ nhiệm và các sự kiện đang hoạt động ổn định.
- Quy trình nộp đơn ứng tuyển hoạt động hoàn hảo, gửi thông báo ngay lập tức đến tài khoản của Ban chủ nhiệm qua hệ thống Real-time.
- Thích ứng bố cục hiển thị sắc nét trên cả điện thoại màn hình nhỏ và màn hình máy tính lớn.

## 19. QA / UAT Checklist
- [ ] Kiểm tra nút đăng ký gia nhập đổi trạng thái chính xác đối với từng đối tượng người dùng.
- [ ] Xác minh các tab "Giới thiệu", "Bài viết", "Sự kiện" hiển thị đúng dữ liệu phân loại tương ứng.
- [ ] Đảm bảo Ban chủ nhiệm thực hiện duyệt đơn ứng tuyển thành công và quyền hạn thành viên mới được cập nhật ngay lập tức.
- [ ] Thử nghiệm giao diện hiển thị trên máy tính bảng ở cả chế độ xoay dọc và xoay ngang.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình quan hệ nhiều-nhiều (Many-to-Many Relationship) với thuộc tính Pivot chứa vai trò thành viên (`role`) và trạng thái duyệt (`status`) để quản lý thành viên CLB tối ưu nhất trong cơ sở dữ liệu Laravel.
- Sử dụng Livewire Listener để lắng nghe các sự kiện gửi đơn gia nhập mới và đẩy thông báo đẩy (push notification) theo thời gian thực về bảng điều khiển của Ban chủ nhiệm.
