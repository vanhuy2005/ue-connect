---
title: "Moderation Queue Page"
module: "04-design/page-specs/admin"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P1"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/safety-reporting.md"
related_design_docs:
  - "../04-design/moderation-system.md"
related_system_docs:
  - "../05-system-architecture/moderation-rules.md"
related_database_docs:
  - "../06-database/violations-table.md"
related_api_docs:
  - "../07-api/violations-api.md"
---

# Hàng Đợi Kiểm Duyệt Nội Dung Tự Động (Moderation Queue)

## 1. Purpose
Trang Hàng Đợi Kiểm Duyệt Nội Dung Tự Động (Moderation Queue) là công cụ chuyên sâu dành cho Cán bộ Phòng Công tác Sinh viên HCMUE để kiểm tra, phê duyệt hoặc gỡ bỏ các bài viết, bình luận, tệp tài nguyên bị hệ thống quét từ cấm tự động giữ lại (System Flagged Content) trước khi được hiển thị công khai hoặc xử lý ngay khi có lượt cắm cờ (Flagged) tăng vọt từ sinh viên trường.

## 2. Product Context
Nằm trong giải pháp đảm bảo tính minh bạch, văn minh và nghiêm túc học đường của UEConnect, trang hàng đợi kiểm duyệt hoạt động như bộ lọc thông minh đầu vào, bảo vệ không gian thảo luận khỏi các tin rác quảng cáo, nội dung thô tục, bôi nhọ sư phạm hoặc tài liệu học thuật sai lệch chất lượng.

## 3. User Goals
- Kiểm tra danh sách bài đăng và bình luận bị hệ thống gắn cờ cảnh báo (Flagged list) hoặc giữ lại chờ duyệt.
- Lọc nội dung theo nhóm: "Bài đăng", "Bình luận", "Tài nguyên học thuật", "Tệp tin đính kèm".
- Đọc nội dung bị gắn cờ và xem từ khóa nhạy cảm bị bôi đỏ cảnh báo (Highlighted triggers).
- Áp dụng thao tác xử lý hàng loạt (Bulk Actions): Phê duyệt công khai (Approve) hoặc Xóa vĩnh viễn (Delete) chỉ bằng một thao tác nhấp chọn hộp kiểm.

## 4. Primary Users
- **Cán bộ Phòng Công tác Sinh viên**: Người phụ trách kiểm soát chất lượng nội dung và vận hành an toàn cộng đồng trực tuyến.

## 5. Entry Points
- Nhấp chọn mục **Hàng đợi kiểm duyệt** (Moderation Queue) trên thanh Sidebar điều hướng của Cổng quản trị Admin.

## 6. Layout Strategy
Thiết kế tập trung tối đa vào giao diện danh sách bảng hoặc lưới thẻ nén khoa học, tối ưu hóa không gian hiển thị để dễ dàng quét mắt nhanh hàng chục nội dung.

### 6.1 Desktop Layout
- Bố cục trung tâm 1 cột lớn (chiều rộng tối đa 1140px):
  - Phía trên là thanh lọc chuyên đề (Mức độ nghiêm trọng, Loại nội dung) và thanh công cụ thao tác hàng loạt (Bulk Actions Bar: Chọn tất cả, Duyệt hàng loạt, Xóa hàng loạt).
  - Phía dưới là Danh sách thẻ nội dung xếp chồng dọc, mỗi thẻ hiển thị chi tiết nội dung gốc và lý do bị gắn cờ rõ ràng.
- Khoảng cách lề: 24px.

### 6.2 Tablet Layout
- Tương tự Desktop, căn giữa màn hình với khoảng cách lề hai bên rộng 20px giúp ngón tay dễ dàng nhấp chọn hộp kiểm (Checkboxes).

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tối giản.
- Khách hàng là Cán bộ quản trị ưu tiên duyệt đơn trên máy tính để bàn văn phòng. Giao diện Mobile hỗ trợ xem nhanh tình hình và xử lý nhanh các nội dung khẩn cấp qua hộp thoại BottomSheet tiện dụng (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh công cụ hàng loạt (Bulk Mod Tools)**:
  - Hộp kiểm chọn tất cả (Select All Checkbox).
  - Nút "Duyệt hiển thị" (Approve Selected - Xanh lá), Nút "Gỡ bỏ" (Delete Selected - Đỏ).
- **Danh sách nội dung bị cắm cờ (Flagged Content Cards)**:
  - Mỗi thẻ nội dung gồm:
    - Hộp kiểm đơn lẻ (Single Checkbox).
    - Thông tin tác giả: Tên hiển thị, mã số sinh viên.
    - Nội dung văn bản gốc: Chứa các từ cấm bị bôi đỏ làm nổi bật (Ví dụ: `[Từ cấm]`).
    - Lý do gắn cờ (Flag Reason): Ví dụ: `"Quét tự động: Chứa từ khóa nhạy cảm"`, `"Sinh viên cắm cờ báo cáo"`.
    - Hàng nút hành động nhanh: Duyệt (Tick xanh), Xóa (Thùng rác đỏ).

## 8. Core Components
- **Select All Checkbox Header**: Hộp kiểm lớn ở đầu trang hỗ trợ chọn nhanh toàn bộ các nội dung đang hiển thị trên màn hình.
- **Flagged Keyword Highlighter**: Cơ chế tự động tô màu đỏ rực các từ khóa thô tục vi phạm giúp Cán bộ duyệt đơn nắm bắt lý do vi phạm trong vòng 1 giây.
- **Bulk Action Floating Bar**: Thanh công cụ nổi sát đáy màn hình xuất hiện mượt mà khi người dùng chọn tối thiểu 1 hộp kiểm, chứa các nút hành động duyệt hàng loạt.
- **Filter Dropdown Combos**: Hộp chọn bộ lọc thả xuống phân loại theo Mức độ cảnh báo (Cao/Trung bình/Thấp) và loại nội dung.

## 9. States
### 9.1 Loading
- Danh sách hiển thị 5 thẻ nội dung trống dạng Shimmer nhấp nháy chuyển động xám nhẹ tuần hoàn trong lúc tải dữ liệu.

### 9.2 Empty
- Khi hàng đợi kiểm duyệt sạch bóng nội dung vi phạm:
  - UI Copy ở giữa màn hình:
    - UI Copy: `"Hàng đợi kiểm duyệt sạch sẽ!"`
    - Mô tả UI Copy: `"Tất cả bài đăng và bình luận trên hệ thống đều tuân thủ tác phong học đường văn minh. Tuyệt vời!"` kèm hoạt ảnh chú robot nhỏ đang dọn dẹp vui vẻ.

### 9.3 Error
- Lỗi tải danh sách do sự cố mạng:
  - UI Copy: `"Đã xảy ra lỗi khi tải danh sách kiểm duyệt. Vui lòng tải lại trang."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa toàn bộ hộp kiểm và thanh công cụ thao tác hàng loạt.

### 9.5 Permission Restricted
- Chỉ dành cho cán bộ Phòng Công tác Sinh viên có quyền Kiểm duyệt (`moderator` / `admin` role). Các tài khoản khác bị chặn tuyệt đối qua Middleware bảo mật.

### 9.6 Success / Completed
- Duyệt hiển thị hoặc gỡ bỏ thành công:
  - Các nội dung được chọn biến mất khỏi hàng đợi mượt mà.
  - Hiện Toast thông báo xanh dịu ở góc dưới màn hình: `"Đã duyệt hiển thị X nội dung thành công!"` hoặc `"Đã gỡ bỏ Y nội dung vi phạm!"`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua thẻ nội dung sẽ làm thẻ đổi màu nền sang xám kem dịu (`bg-gray-50`) và hiển thị phím tắt hành động nhanh. Di chuột qua nút Thùng rác đỏ sẽ hiển thị tooltip `"Gỡ bỏ vĩnh viễn"`.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các hộp kiểm và nút hành động khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp chọn hộp kiểm đơn lẻ đổi trạng thái ngay lập tức mượt mà, kích hoạt thanh công cụ nổi trượt từ đáy màn hình lên trong vòng 150ms cực kỳ chuyên nghiệp.

### 10.4 Optimistic UI
- Khi bấm duyệt hiển thị nhanh một nội dung, thẻ nội dung lập tức biến mất khỏi hàng đợi hiển thị tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Hộp thoại quyết định xử lý hàng loạt trên di động mở ra một BottomSheet mượt mà chứa các nút bấm hành động lớn dễ chạm bấm nhanh chóng.

### 10.6 Toast / Undo
- Hành động duyệt hiển thị thành công hiển thị Toast thông báo ở góc dưới màn hình kèm nút "Hoàn tác" để khôi phục nhanh nội dung về trạng thái chờ kiểm duyệt trong vòng 5 giây nếu cán bộ lỡ tay bấm nhầm.

### 10.7 Motion
- Hiệu ứng trượt lên (Slide-up) mượt mà của thanh công cụ nổi Bulk Action Bar. Hoạt ảnh thu nhỏ biến mất của thẻ nội dung khi được duyệt diễn ra trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi hộp kiểm phải đi kèm nhãn mô tả chi tiết cho các trình đọc màn hình: `aria-label="Chọn bài đăng của [Tên tác giả] để duyệt hàng loạt"`.
- Hỗ trợ phím đóng nhanh `Escape` để thoát nhanh khỏi trạng thái chọn tất cả.

## 12. Responsive Rules
- Màn hình di động đứng (<768px): Tối ưu hóa kích thước chữ hiển thị nội dung gốc vi phạm để tránh bị tràn màn hình, chỉ hiển thị tóm tắt ngắn gọn dưới 60 ký tự và thêm nút "Xem thêm" tiện lợi.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `flagged_contents` (array of objects: `content_id`, `author_name`, `content_type`, `body_preview`, `flag_reason`, `severity`)
  - `selected_content_ids` (array of integers)

## 14. API / Action Requirements
- Gọi Livewire / Admin Action:
  - `fetchFlaggedContentsQueue()`
  - `approveFlaggedContent(contentId)`
  - `deleteFlaggedContent(contentId)`
  - `bulkApproveContents(contentIds)`
  - `bulkDeleteContents(contentIds)`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin nghiêm ngặt: Toàn bộ thông tin bài đăng và bình luận bị gắn cờ chỉ hiển thị đối với cán bộ phụ trách kiểm duyệt của trường. Mọi truy cập trái phép từ tài khoản khác đều bị hệ thống chặn đứng và khóa tài khoản vi phạm.

## 16. Analytics / Audit Events
- `moderation_queue_viewed`: Ghi nhận lượt mở xem hàng đợi kiểm duyệt.
- `bulk_moderation_executed`: Ghi nhận sự kiện thực hiện hành động kiểm duyệt hàng loạt thành công kèm theo số lượng nội dung đã xử lý để đánh giá hiệu suất hành chính của trường.

## 17. Do / Don't
- **Nên làm**: Luôn bôi đỏ các từ khóa vi phạm rõ ràng trong văn bản để giúp Cán bộ duyệt đơn nhanh chóng đưa ra quyết định mà không cần đọc hết bài viết dài.
- **Không được làm**: Cho phép hiển thị nội dung thô tục hoặc ảnh nhạy cảm công khai trên Bảng tin chính của trường khi chưa được Cán bộ phê duyệt hợp lệ.

## 18. Acceptance Criteria
- Hàng đợi kiểm duyệt hiển thị đầy đủ, chính xác tất cả các nội dung bị gắn cờ từ cơ sở dữ liệu.
- Cơ chế chọn tất cả và thực hiện thao tác hàng loạt hoạt động trơn tru, không gây gián đoạn hay trùng lặp dữ liệu API.
- Giao diện thích ứng sắc nét và hiển thị mượt mà trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra hộp kiểm chọn tất cả hoạt động chính xác và hiển thị đúng thanh công cụ nổi Bulk Action Bar.
- [ ] Xác minh từ khóa vi phạm được bôi đỏ rõ ràng chính xác trong văn bản thẻ.
- [ ] Thử nghiệm duyệt hàng loạt 5 bài viết và kiểm tra xem có biến mất khỏi danh sách hàng đợi cùng lúc không.
- [ ] Đảm bảo chỉ cán bộ có quyền mới mở được trang quản trị này.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu trữ thời gian thực (WebSockets) để tự động cập nhật hàng đợi ngay trên màn hình của cán bộ khi có sinh viên cắm cờ báo cáo bài viết mới từ Bảng tin chính, không cần tải lại trang.
- Thiết kế hệ thống tự động gán nhãn mức độ nghiêm trọng (Severity tagging) dựa trên mức độ từ cấm trong bài viết để phân phối đơn đến đúng bộ phận phụ trách kiểm duyệt tương ứng một cách khoa học.
---
