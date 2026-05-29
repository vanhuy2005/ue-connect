---
title: "Discovery Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/discovery-engine.md"
related_design_docs:
  - "../04-design/search-discovery.md"
related_system_docs:
  - "../05-system-architecture/recommendation-engine.md"
related_database_docs:
  - "../06-database/recommendations.md"
related_api_docs:
  - "../07-api/discovery-api.md"
---

# Trang Khám Phá Đồng Môn & Hoạt Động (Discovery)

## 1. Purpose
Trang Khám Phá Đồng Môn & Hoạt Động là trung tâm tìm kiếm cơ hội kết nối, đề xuất bạn bè thông minh, gợi ý các câu lạc bộ nổi bật và cập nhật các xu hướng thảo luận học đường (Hashtags) thịnh hành dành cho sinh viên và giảng viên trong khuôn viên số UEConnect của trường Đại học Sư phạm TP.HCM.

## 2. Product Context
Nằm trong lõi thúc đẩy tương tác xã hội học đường của UEConnect, trang Khám Phá giải quyết vấn đề rụt rè kết nối của sinh viên bằng cách chủ động giới thiệu những người bạn có cùng sở thích, cùng ngành học hoặc các anh chị cựu sinh viên xuất sắc đi trước để tạo cầu nối tự nhiên, an toàn.

## 3. User Goals
- Khám phá các hồ sơ sinh viên, cựu sinh viên nổi bật cùng chuyên ngành đào tạo.
- Tìm kiếm nhanh các chủ đề nóng đang được bàn luận nhiều nhất trong trường (Trending Hashtags).
- Tiếp cận danh sách gợi ý CLB có phong trào hoạt động sôi nổi phù hợp với năng khiếu cá nhân.
- Gửi yêu cầu kết nối nhanh hoặc theo dõi các tài khoản thú vị chỉ bằng một thao tác bấm chạm.

## 4. Primary Users
- **Sinh viên HCMUE**: Cần tìm kiếm bạn đồng hành học tập, lập nhóm nghiên cứu khoa học hoặc tham gia hoạt động phong trào Đoàn hội.
- **Tân sinh viên**: Tìm kiếm thông tin và những gương mặt sinh viên tiêu biểu để giao lưu học hỏi.

## 5. Entry Points
- Nhấp chọn biểu tượng **Khám phá** (Discovery / Compass Icon) trên thanh công cụ điều hướng chính (Sidebar / Bottom Navigation).

## 6. Layout Strategy
Áp dụng bố cục phân vùng thông minh dạng lưới mảnh (Grid) giúp việc quét quét nội dung bằng mắt diễn ra nhanh chóng, dễ chịu.

### 6.1 Desktop Layout
- Bố cục 3 cột lớn:
  - Cột bên trái (65%): Danh sách đề xuất hồ sơ thành viên (People Recommendations) hiển thị dạng lưới thẻ 2 cột và Dòng tin hoạt động của họ.
  - Cột bên phải (35%): Chia làm 2 phần: Bảng xu hướng thảo luận (Trending Hashtags) ở trên, danh sách CLB nổi bật (Recommended Clubs) ở dưới.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1120px.

### 6.2 Tablet Layout
- Cột bên phải thu gọn xuống dưới. Cột bên trái hiển thị toàn bộ nội dung danh sách đề xuất thành viên xếp dọc 1 cột thoáng đãng.
- Khoảng cách lề: 20px.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc liên tục.
- Trên cùng là Thanh tìm kiếm lớn tích hợp phím tắt mở bộ lọc.
- Phía dưới là các khối (Sections) nội dung cuộn ngang (Horizontal scroll view) giới thiệu: "Đồng môn gợi ý", "Chủ đề thịnh hành", "Câu lạc bộ đề xuất" giúp tiết kiệm không gian hiển thị dọc của màn hình điện thoại di động.

## 7. Information Architecture
- **Thanh tìm kiếm & Gợi ý từ khóa (Search Bar Area)**:
  - Ô tìm kiếm đa năng (Tìm người, tìm CLB, tìm bài viết).
- **Khối Đề xuất thành viên (Recommended Peer Cards)**:
  - Thẻ chân dung thành viên gồm: Ảnh đại diện, Họ tên, Khóa học/Khoa, Nút "Kết nối" nhanh, Số lượng bạn chung.
- **Khối Xu hướng học đường (Trending Topics)**:
  - Danh sách các Hashtags thịnh hành sắp xếp theo số lượng bài viết thảo luận trong tuần (Ví dụ: `#NghiênCứuKhoaHọc2026`, `#HCMUE_Volunteer`, `#SưPhạmTinHọc`).
- **Khối Câu lạc bộ đề xuất (Suggested Groups)**:
  - Thẻ thông tin CLB gồm: Logo CLB, Tên CLB, Số thành viên và nút ứng tuyển nhanh.

## 8. Core Components
- **Discovery Profile Card**: Thẻ thông tin cá nhân thu gọn tích hợp hiệu ứng đổ bóng mờ và nút kết nối nhanh dạng Outline xanh dương thương hiệu.
- **Trending Hashtag Row**: Dòng hiển thị thẻ hashtag đi kèm số bài đăng tương tác mượt mà.
- **Quick Connect Button**: Nút bấm kết nối nhanh có thiết kế thông minh tự động chuyển đổi trạng thái khi bấm.
- **Shimmer Grid Placeholder**: Khung trống màu xám nhạt chuyển động nhấp nháy mô phỏng cấu trúc thẻ trong lúc tải dữ liệu.

## 9. States
### 9.1 Loading
- Hiển thị hiệu ứng Shimmer mượt mà cho lưới thẻ chân dung và danh sách xu hướng. Nút kết nối nhanh hiển thị ở trạng thái khóa.

### 9.2 Empty
- Trường hợp không có đề xuất nào phù hợp (Hiếm khi xảy ra, ví dụ khi hệ thống mới thiết lập):
  - UI Copy: `"Hệ thống đang chuẩn bị các đề xuất tốt nhất cho bạn."`
  - Mô tả UI Copy: `"Hãy cập nhật thêm thông tin cá nhân và kỹ năng trong phần Thiết lập hồ sơ để nhận các gợi ý kết nối chuẩn xác nhất."`

### 9.3 Error
- Lỗi tải thông tin trang do mất kết nối mạng:
  - UI Copy: `"Không thể tải thông tin khám phá. Vui lòng kiểm tra kết nối mạng và tải lại trang."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Các tính năng tương tác nhanh (như nút Kết nối nhanh) tạm thời bị vô hiệu hóa.

### 9.5 Permission Restricted
- Không áp dụng, trang này mở công khai cho tất cả mọi người dùng đã xác thực thành viên.

### 9.6 Success / Completed
- Dữ liệu tải xong mượt mà, hiển thị danh sách đồng môn đề xuất được sắp xếp tối ưu theo thuật toán gợi ý của trường.

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ hồ sơ đề xuất sẽ kích hoạt hiệu ứng zoom ảnh đại diện nhẹ (`scale-105`) và hiển thị nút "Xem hồ sơ chi tiết". Di chuột qua hashtag xu hướng sẽ làm đổi màu nền sang màu xám nhạt (`bg-gray-100`).

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các thẻ thành viên và các nút hành động khi di chuyển tiêu điểm bằng phím Tab.

### 10.3 Press / Tap
- Thao tác nhấp chọn thẻ thành viên sẽ mở trang cá nhân của họ tức thì với hiệu ứng chuyển trang mượt mà. Bấm nút "Kết nối" nhanh sẽ có phản hồi rung nhẹ trên màn hình cảm ứng di động.

### 10.4 Optimistic UI
- Khi bấm nút "Kết nối" nhanh trên thẻ đề xuất, nút lập tức chuyển sang trạng thái `"Đã gửi yêu cầu"` và ẩn thẻ khỏi danh sách đề xuất trong vòng 200ms để người dùng tiếp tục duyệt các gợi ý khác mà không bị đứt quãng trải nghiệm.

### 10.5 Menu / Sheet
- Hỗ trợ BottomSheet trên di động để tùy chỉnh bộ lọc khám phá (Lọc theo Khoa, Lọc theo Niên khóa, Chỉ hiển thị Cựu sinh viên).

### 10.6 Toast / Undo
- Đã gửi yêu cầu kết nối nhanh thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để hủy nhanh lời mời vừa gửi đi.

### 10.7 Motion
- Hiệu ứng trượt ngang của danh sách cuộn ngang trên di động diễn ra trơn tru với lực cản quán tính tự nhiên (CSS `-webkit-overflow-scrolling: touch`).

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi thẻ gợi ý đồng môn phải có thẻ tiêu đề `<h4>` rõ ràng hỗ trợ trình đọc màn hình dễ dàng duyệt qua danh sách.
- Hỗ trợ phím Space / Enter để bấm chọn nhanh các hashtag xu hướng.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): Các khối thông tin chuyển thành dạng hàng cuộn ngang riêng biệt giúp giao diện gọn gàng, không bị kéo dài lê thê theo chiều dọc.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `recommended_users` (array of objects: `id`, `name`, `username`, `avatar_url`, `class_info`, `mutual_count`)
  - `trending_hashtags` (array of objects: `tag_name`, `post_count`)
  - `suggested_clubs` (array of objects: `id`, `name`, `logo_url`, `member_count`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `loadDiscoveryContent()`
  - `sendQuickConnectionRequest(userId)`
  - `dismissRecommendation(userId)`
  - `applyDiscoveryFilter(filterData)`

## 15. Authorization / Privacy Rules
- Trang Khám Phá chỉ hiển thị thông tin của những người dùng đã bật tùy chọn "Cho phép xuất hiện trong mục Khám phá" trong phần cài đặt quyền riêng tư cá nhân. Những tài khoản đặt chế độ hoàn toàn ẩn danh sẽ không bao giờ xuất hiện tại đây.

## 16. Analytics / Audit Events
- `discovery_page_viewed`: Ghi nhận lượt mở trang khám phá.
- `recommendation_card_clicked`: Ghi nhận sự kiện người dùng nhấp xem hồ sơ từ đề xuất.
- `quick_connection_sent`: Theo dõi số lượng lời mời kết nối thành công từ tính năng kết nối nhanh.

## 17. Do / Don't
- **Nên làm**: Luôn ưu tiên hiển thị gợi ý những người dùng có chung đặc điểm học tập (cùng khoa, cùng lớp) lên hàng đầu để tạo độ tin cậy kết nối cao.
- **Không được làm**: Cho phép hiển thị lặp đi lặp lại một tài khoản đã bị người dùng bấm "Bỏ qua" (Dismiss) nhiều lần trong danh sách đề xuất.

## 18. Acceptance Criteria
- Hiển thị đầy đủ, chính xác danh sách đề xuất đồng môn, hashtag thịnh hành và câu lạc bộ gợi ý.
- Tính năng gửi yêu cầu kết nối nhanh hoạt động hoàn hảo, lưu chính xác trạng thái vào cơ sở dữ liệu.
- Thiết kế thích ứng mượt mà trên mọi loại màn hình di động, máy tính bảng và máy tính để bàn.

## 19. QA / UAT Checklist
- [ ] Kiểm tra danh sách đề xuất thay đổi chính xác khi thay đổi tùy chọn bộ lọc Khoa.
- [ ] Xác minh nút "Bỏ qua" trên thẻ đề xuất xóa thẻ đó khỏi giao diện thời gian thực thành công.
- [ ] Thử nghiệm bấm nhanh liên tiếp 3 nút "Kết nối" và đảm bảo hệ thống không bị đơ hoặc gửi trùng lặp yêu cầu API.
- [ ] Đảm bảo các hashtag xu hướng dẫn đúng đến trang kết quả tìm kiếm tương ứng khi nhấp chọn.

## 20. AI Agent Implementation Notes
- Sử dụng mô hình lọc cộng tác (Collaborative Filtering) đơn giản kết hợp dữ liệu thuộc tính học tập (Khoa, Lớp, Khóa) để tính toán điểm tương đồng (Similarity Score) giữa các người dùng trong cơ sở dữ liệu MySQL để trả về danh sách đề xuất tối ưu nhất.
- Sử dụng công nghệ lưu đệm (Caching) kết quả đề xuất cá nhân hóa trong Redis với thời gian hết hạn (TTL) là 1 giờ để giảm thiểu tải tính toán cho máy chủ cơ sở dữ liệu khi có nhiều sinh viên truy cập cùng lúc.
---