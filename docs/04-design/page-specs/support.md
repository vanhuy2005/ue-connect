---
title: "Support / Help Center Page"
module: "04-design/page-specs"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P2"
owner: "Product Design / Frontend / Product"
related_feature_specs:
  - "../03-product/feature-specs/support.md"
related_design_docs:
  - "../04-design/support-channels.md"
related_system_docs:
  - "../05-system-architecture/ticketing-service.md"
related_database_docs:
  - "../06-database/tickets-table.md"
related_api_docs:
  - "../07-api/tickets-api.md"
---

# Trang Trung Tâm Hỗ Trợ & Giải Đáp Học Đường (Support / Help Center)

## 1. Purpose
Trang Trung Tâm Hỗ Trợ & Giải Đáp Học Đường cung cấp cổng thông tin hướng dẫn sử dụng hệ thống, tra cứu các câu hỏi thường gặp (FAQs) về quy chế đào tạo, điểm rèn luyện, đăng ký môn học trường HCMUE, đồng thời hỗ trợ gửi đơn phản ánh kỹ thuật (Support Ticket) đính kèm hình ảnh minh chứng và kết nối trực tiếp với Đội ngũ hỗ trợ kỹ thuật trực ban.

## 2. Product Context
Nhằm rút ngắn khoảng cách giữa sinh viên và các bộ phận hỗ trợ kỹ thuật, hành chính trong trường Đại học Sư phạm TP.HCM, trang Trung tâm hỗ trợ được thiết kế tinh giản, thân thiện, mang lại giải pháp tháo gỡ khó khăn tức thì, chuyên nghiệp và có tổ chức cao.

## 3. User Goals
- Tra cứu nhanh lời giải cho các vấn đề thường gặp liên quan đến tài khoản và học thuật trường.
- Gửi nhanh phiếu yêu cầu hỗ trợ kỹ thuật (Support Ticket) kèm theo hình ảnh lỗi hiển thị trực quan.
- Theo dõi danh sách phiếu yêu cầu đã gửi cùng trạng thái phản hồi xử lý của kỹ thuật viên.
- Trò chuyện trực tuyến (Live Chat) trực tiếp với nhân viên hỗ trợ trực ban trong giờ hành chính.

## 4. Primary Users
- **Sinh viên, Giảng viên HCMUE**: Cần giải đáp thắc mắc kỹ thuật hoặc thủ tục hành chính học đường.
- **Kỹ thuật viên Trung tâm CNTT trường**: Tiếp nhận, phản hồi và giải quyết các khiếu nại kỹ thuật của sinh viên.

## 5. Entry Points
- Nhấp chọn **Cài đặt** -> Chọn mục **Hỗ trợ & Trợ giúp** (Help & Support).
- Nhấp vào biểu tượng dấu chấm hỏi nhỏ (Help Icon) ở góc dưới cùng thanh điều hướng chính.

## 6. Layout Strategy
Thiết kế trang rõ ràng, phân nhóm khoa học giữa cổng tra cứu tự động FAQs và cổng tương tác trực tiếp gửi phiếu yêu cầu.

### 6.1 Desktop Layout
- Bố cục 2 phần cân đối xếp chồng:
  - Phần trên: Thanh tìm kiếm FAQs lớn nổi bật giữa nền xanh dương nhạt thương hiệu, kèm lưới 3 thẻ danh mục hỗ trợ phổ biến (Tài khoản, Học thuật, Kỹ thuật).
  - Phần dưới: Chia đôi cột (60% - 40%):
    - Bên trái (60%): Danh sách câu hỏi thường gặp FAQs xếp dọc dạng mở rộng (Accordion).
    - Bên phải (40%): Biểu mẫu gửi phiếu hỗ trợ nhanh và nút kết nối trò chuyện trực tuyến Live Chat.
- Khoảng cách lề: 24px. Chiều rộng trang tối đa: 1140px.

### 6.2 Tablet Layout
- Khung biểu mẫu hỗ trợ bên phải chuyển thành dạng xếp chồng nằm dưới danh sách FAQs.
- Chiều ngang trang mở rộng với lề hai bên là 20px giúp ngón tay dễ chạm bấm.

### 6.3 Mobile / PWA Layout
- Bố cục 1 cột cuộn dọc tràn màn hình mượt mà.
- FAQs chuyển thành dạng Accordion khép kín để tiết kiệm diện tích.
- Nút "Gửi yêu cầu hỗ trợ" lớn nổi bật nằm ở chân trang, trượt mở BottomSheet biểu mẫu điền thông tin chi tiết (`touch-target 44px`).
- Khoảng cách lề: 16px.

## 7. Information Architecture
- **Thanh tìm kiếm FAQs (FAQs Search Bar)**:
  - Tìm kiếm nhanh câu hỏi theo từ khóa (Ví dụ: `Lấy lại mật khẩu`, `Cổng Portal`).
- **Danh sách câu hỏi thường gặp (FAQs Accordion List)**:
  - Phân nhóm câu hỏi: Tài khoản, Đăng ký môn học, Học phí, Điểm rèn luyện.
  - Mỗi câu hỏi gồm: Tiêu đề câu hỏi, nội dung giải đáp định dạng chữ rõ ràng, kèm liên kết ngoài đến trang web chính thức của trường.
- **Cổng gửi phiếu hỗ trợ (Support Ticket Form)**:
  - Tiêu đề phiếu, Loại sự cố (Hộp thả xuống), Mô tả chi tiết sự cố.
  - Khung tải ảnh chụp màn hình lỗi (Screenshot Upload).
- **Lịch sử yêu cầu hỗ trợ (Support Tickets History)**:
  - Danh sách phiếu đã gửi: Mã phiếu, Tiêu đề, Ngày gửi, Trạng thái (Đang xử lý / Đã giải quyết).

## 8. Core Components
- **FAQs Accordion Card**: Thẻ câu hỏi tự động thu gọn/mở rộng mượt mà khi nhấp chọn, sử dụng biểu tượng mũi tên quay đầu sinh động.
- **Ticket Form Panel**: Khung điền thông tin gửi sự cố kỹ thuật sạch sẽ, trực quan.
- **Live Chat Trigger**: Nút trò chuyện trực tuyến nổi bật màu xanh lá cây rực rỡ báo hiệu nhân viên đang trực ban sẵn sàng hỗ trợ.
- **Screenshot Upload Dropzone**: Khung kéo thả hình ảnh minh chứng đính kèm mượt mà, hiển thị ảnh nhỏ xem trước (Thumbnail).

## 9. States
### 9.1 Loading
- Danh sách câu hỏi và biểu mẫu lịch sử hiển thị các thanh Skeleton xám nhẹ nhấp nháy Shimmer liên tục trong lúc tải dữ liệu.

### 9.2 Empty
- Khi người dùng chưa từng gửi bất kỳ phiếu hỗ trợ nào:
  - Lịch sử hiển thị dòng chữ mỏng: `"Bạn chưa gửi yêu cầu hỗ trợ nào."`

### 9.3 Error
- **Lỗi tải tệp tin quá dung lượng**: Tải ảnh minh chứng vượt quá dung lượng cho phép (Tối đa 5MB) hoặc sai định dạng tệp:
  - UI Copy dưới khung tải ảnh: `"Dung lượng hình ảnh vượt quá giới hạn (Tối đa 5MB). Vui lòng chọn ảnh khác."`
- **Lỗi gửi đơn**: Gửi phiếu thất bại do rớt mạng:
  - UI Copy: `"Không thể gửi yêu cầu hỗ trợ lúc này. Vui lòng kiểm tra kết nối mạng."`

### 9.4 Offline / Reconnecting
- Hiển thị dải băng mỏng màu cam cảnh báo ngoại tuyến ở đầu trang. Vô hiệu hóa nút gửi phiếu và tính năng Live Chat trực tuyến.

### 9.5 Permission Restricted
- Tính năng gửi đơn và Live Chat yêu cầu người dùng đăng nhập tài khoản hợp lệ. Khách vãng lai chỉ được phép đọc danh sách câu hỏi thường gặp FAQs.

### 9.6 Success / Completed
- Gửi phiếu hỗ trợ thành công:
  - Phiếu mới lập tức xuất hiện ở đầu danh sách Lịch sử yêu cầu hỗ trợ với trạng thái `"Đang chờ tiếp nhận"`.
  - Hiện Toast thông báo xanh dịu: `"Gửi yêu cầu hỗ trợ thành công! Đội ngũ kỹ thuật đã được thông báo."`

## 10. Interaction Design
### 10.1 Hover
- Di chuột qua câu hỏi FAQs Accordion sẽ làm sáng viền thẻ và đổi màu nền sang màu xám kem dịu (`bg-gray-50`) để báo hiệu khả năng nhấp chọn mở rộng.

### 10.2 Focus
- Vòng viền Focus nét đứt 2px màu xanh ngọc bích bao quanh các câu hỏi Accordion và ô nhập liệu khi di chuyển tiêu điểm bằng phím Tab di chuyển.

### 10.3 Press / Tap
- Thao tác nhấp câu hỏi Accordion sẽ kích hoạt hoạt ảnh mở rộng mượt mà trải dài nội dung câu trả lời trong vòng 200ms.

### 10.4 Optimistic UI
- Khi bấm chọn gửi phiếu hỗ trợ, chèn ngay thông tin phiếu mới vào bảng Lịch sử hiển thị tạm thời trước khi nhận phản hồi xác nhận lưu hoàn tất từ máy chủ để tạo cảm giác tốc độ phản hồi cực nhanh dưới 100ms.

### 10.5 Menu / Sheet
- Biểu mẫu gửi phiếu trên di động mở ra một BottomSheet chi tiết dạng danh sách tròn nhấp chọn trực quan dễ bấm chạm và vuốt cảm ứng trơn tru.

### 10.6 Toast / Undo
- Không áp dụng tính năng Hoàn tác (Undo) cho hành động gửi phiếu hỗ trợ kỹ thuật để đảm bảo tính đồng bộ dữ liệu sự cố.

### 10.7 Motion
- Hoạt ảnh mở rộng co giãn mượt mà của Accordion FAQs và hiệu ứng trượt mở rộng của biểu mẫu diễn ra trong vòng 200ms trơn tru.

## 11. Accessibility Requirements
- Đạt chuẩn tiếp cận WCAG AA.
- Mỗi câu hỏi Accordion phải có tiêu đề và nhãn `aria-expanded` phản hồi chính xác trạng thái mở rộng của phần tử cho trình đọc màn hình.
- Hỗ trợ phím Space / Enter để kích hoạt đóng/mở Accordion nhanh chóng.

## 12. Responsive Rules
- Màn hình di động đứng (<600px): FAQs và biểu mẫu chuyển hoàn toàn sang bố cục danh sách dọc 1 cột cuộn mượt mà, ô nhập liệu mở rộng tràn viền giúp tay dễ dàng thao tác gõ phím.

## 13. Data Requirements
- Dữ liệu đầu vào:
  - `faqs_list` (array of objects: `faq_id`, `question`, `answer`, `category`)
  - `support_ticket` (object: `title`, `category`, `description`, `proof_image`)
  - `ticket_history` (array of objects: `ticket_id`, `title`, `status`, `created_at`)

## 14. API / Action Requirements
- Gọi Livewire Action:
  - `fetchFaqs(searchQuery)`
  - `submitSupportTicket(ticketData)`
  - `uploadTicketScreenshot(file)`
  - `fetchTicketHistory()`

## 15. Authorization / Privacy Rules
- Bảo mật thông tin hỗ trợ tuyệt đối: Nội dung các phiếu hỗ trợ kỹ thuật và thông tin cá nhân của người gửi chỉ hiển thị đối với chính chủ tài khoản và đội ngũ kỹ thuật viên phụ trách của trường.

## 16. Analytics / Audit Events
- `support_page_viewed`: Ghi nhận lượt mở xem trang trung tâm hỗ trợ.
- `support_ticket_submitted`: Ghi nhận sự kiện gửi phiếu hỗ trợ kỹ thuật thành công kèm theo nhóm sự cố để đánh giá độ ổn định của hệ thống.

## 17. Do / Don't
- **Nên làm**: Luôn cung cấp đường link liên kết ngoài đến trang web Đào tạo chính thức của trường HCMUE dưới các câu trả lời FAQs liên quan đến học thuật để sinh viên có nguồn đối chiếu tin cậy tuyệt đối.
- **Không được làm**: Cho phép chia sẻ thông tin tài khoản mật khẩu của sinh viên công khai trong nội dung câu trả lời FAQs.

## 18. Acceptance Criteria
- Cổng tra cứu câu hỏi FAQs hoạt động ổn định, trả nội dung chính xác và mở Accordion mượt mà.
- Biểu mẫu gửi phiếu hỗ trợ hoạt động tốt, tải ảnh minh chứng lên thành công và cập nhật đúng danh sách Lịch sử.
- Giao diện thích ứng sắc nét và hiển thị mượt mà trên mọi loại màn hình thiết bị di động.

## 19. QA / UAT Checklist
- [ ] Kiểm tra khả năng mở/đóng mượt mà của Accordion FAQs và biểu tượng mũi tên đổi hướng chính xác.
- [ ] Xác minh tệp ảnh lỗi tải lên thành công và hiển thị ảnh xem trước sắc nét.
- [ ] Thử nghiệm gửi phiếu hỗ trợ kỹ thuật và kiểm tra xem có xuất hiện đúng trong bảng Lịch sử yêu cầu hỗ trợ không.
- [ ] Đảm bảo tính năng Live Chat trực tuyến chỉ hiển thị đối với người dùng đã đăng nhập hợp lệ.

## 20. AI Agent Implementation Notes
- Sử dụng công nghệ lưu đệm (Caching) câu hỏi FAQs bằng Redis để tối ưu tốc độ tải trang nhanh nhất dưới 100ms khi có lượng truy cập lớn trong mùa thi cử hoặc đăng ký môn học.
- Thiết kế cơ chế phân loại sự cố tự động (Auto-categorization) dựa trên từ khóa tiêu đề phiếu hỗ trợ để phân phối đơn đến đúng bộ phận phụ trách kỹ thuật tương ứng một cách khoa học.
---
