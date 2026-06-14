# PRD: Career Pathway

**Trạng thái:** Active
**Mức độ ưu tiên:** P1
**Người phụ trách:** Product Team

## 1. Product summary (Tóm tắt sản phẩm)
Career Pathway là tính năng cốt lõi của UE-Connect, cung cấp một lộ trình học tập và phát triển nghề nghiệp toàn diện cho sinh viên trường Đại học Sư phạm TP.HCM (HCMUE). Đây không chỉ là nơi hiển thị chương trình khung, mà là một nền tảng kết nối kiến thức cộng đồng và chia sẻ định hướng nghề nghiệp.

## 2. Positioning statement (Tuyên ngôn định vị)
Career Pathway định vị là một "Community-powered academic and career roadmap platform" - Nền tảng lộ trình học thuật và nghề nghiệp được thúc đẩy bởi sức mạnh cộng đồng. 
Công thức cốt lõi: `Official curriculum data + Community course knowledge + User-generated career positions + Senior/alumni pathway sharing = Career Pathway`.

## 3. Problem statement (Vấn đề cần giải quyết)
- Sinh viên thiếu cái nhìn tổng quan về chương trình học 4 năm (chỉ biết môn của học kỳ hiện tại).
- Không có nơi lưu trữ và truyền đạt "kinh nghiệm học tập" (môn này nên học ai, tài liệu nào tốt, cần chú ý gì) từ khóa trước cho khóa sau.
- Sinh viên mông lung về định hướng nghề nghiệp, không biết ngành học của mình có thể làm những vị trí nào, cần chuẩn bị kỹ năng gì.
- Thiếu kết nối giữa lộ trình học tập trên trường và yêu cầu thực tế của nhà tuyển dụng.

## 4. Goals (Mục tiêu)
- Số hóa 100% chương trình đào tạo của HCMUE thành dạng cây (worktree) trực quan.
- Tạo không gian cho cộng đồng sinh viên, cựu sinh viên đóng góp kiến thức môn học.
- Hỗ trợ sinh viên tự xây dựng lộ trình nghề nghiệp cá nhân dựa trên chương trình chuẩn.

## 5. Non-goals (Ngoài mục tiêu)
- Không thay thế hệ thống quản lý đào tạo (Edusoft/UIS) của nhà trường.
- Không cho phép đăng ký học phần hay xem điểm trực tiếp tại đây.
- Không làm job portal tuyển dụng trực tiếp trong giai đoạn này.

## 6. User personas (Chân dung người dùng)
- **Tân sinh viên (Freshmen):** Cần biết lộ trình 4 năm, tìm hiểu các môn học sắp tới.
- **Sinh viên năm 3, 4 (Seniors):** Cần tìm hiểu định hướng nghề nghiệp, kỹ năng cần thiết để ra trường.
- **Cựu sinh viên (Alumni):** Chia sẻ lộ trình thực tế, kinh nghiệm học tập và làm việc.
- **Admin/Moderator:** Quản lý dữ liệu chương trình khung, kiểm duyệt nội dung cộng đồng đóng góp.

## 7. Product layers (Các tầng sản phẩm)
Hệ thống gồm 3 tầng (layers):
1. **Official Academic Roadmap (Layer 1):** Dữ liệu chuẩn thức từ nhà trường (khóa, khoa, ngành, chương trình, học kỳ, học phần). Chỉ xem, không thể sửa đổi bởi người dùng thường.
2. **Community Knowledge Layer (Layer 2):** Kiến thức bổ trợ từ cộng đồng (kỹ năng, review môn học, tài liệu, project mẫu).
3. **Career Position Builder (Layer 3):** Xây dựng vị trí việc làm (role) và lộ trình phát triển nghề nghiệp thực tế do cựu sinh viên và chuyên gia đóng góp.

## 8. MVP scope (Phạm vi MVP)
- Giai đoạn MVP **chỉ tập trung vào Layer 1 (Official Academic Roadmap)**.
- Public các chương trình đào tạo đạt trạng thái `ready` và `ready_with_missing_descriptions`.
- Ẩn các chương trình chưa hoàn thiện hoặc bị lỗi cấu trúc.
- Cho phép người dùng duyệt qua hệ thống thư mục: Khóa -> Khoa -> Ngành -> Chương trình đào tạo.
- Hiển thị cây học kỳ và thông tin cơ bản của các học phần.

## 9. Functional requirements (Yêu cầu chức năng)
- Xem danh sách bộ lọc (Filter) theo Khóa, Khoa, Ngành.
- Hiển thị danh sách chương trình đào tạo thỏa mãn bộ lọc.
- Xem chi tiết một chương trình đào tạo dưới dạng Worktree (cây học kỳ).
- Xem thông tin chi tiết của một học phần (tên, mã, số tín chỉ, loại môn, nhóm kiến thức, mô tả).
- (Layer 2+3 - Future) Chức năng đóng góp, vote, report.

## 10. Non-functional requirements (Yêu cầu phi chức năng)
- Hiệu năng: Tốc độ load cây học kỳ < 1s (cần tối ưu cache vì dữ liệu lớn).
- Tính phản hồi (Responsive): Hiển thị tốt worktree trên thiết bị di động (UI đặc thù cho mobile).
- Khả năng mở rộng: Sẵn sàng cho việc map hàng ngàn comments và tài liệu vào từng node học phần.

## 11. Data quality policy (Chính sách chất lượng dữ liệu)
- Tuyệt đối không public các chương trình bị lỗi cấu trúc (P0: `unresolved_semester_structure`, `empty_markdown`, `missing_curriculum_pdf`).
- Chỉ đưa lên official worktree các dữ liệu đã qua Data Quality Gate.

## 12. Data visibility rules (Quy tắc hiển thị dữ liệu)
- Ai cũng có thể xem Layer 1 (Public).
- Các chương trình có status `partial_semester_extraction` bị ẩn với user thường, chỉ admin nhìn thấy để phục hồi.
- Các mô tả môn học bị thiếu (`missing_course_descriptions`) sẽ hiển thị một thông báo "Đang cập nhật" thân thiện.

## 13. Official vs community data separation (Tách biệt dữ liệu chuẩn và cộng đồng)
- Giao diện phải tách biệt rõ ràng phần "Thông tin chuẩn từ nhà trường" (Layer 1) và "Đóng góp từ cộng đồng" (Layer 2).
- Mọi đóng góp cộng đồng không được ghi đè lên dữ liệu chuẩn.
- Dữ liệu chuẩn chỉ có thể được import bằng hệ thống CLI/Admin từ file markdown sinh ra từ PDF gốc.

## 14. AI usage policy (Chính sách sử dụng AI)
- AI được dùng để bóc tách dữ liệu từ PDF ra Markdown (import pipeline).
- Tương lai (Phase 9): AI dùng để gợi ý, gom nhóm, tóm tắt kiến thức cộng đồng, nhưng **không được tự ý ghi đè** dữ liệu official.

## 15. UX direction for future worktree (Định hướng UX cho worktree tương lai)
- Thiết kế dạng bản đồ tư duy (mindmap) hoặc timeline ngang/dọc, tương tự như roadmap.sh.
- Hỗ trợ zoom in/out, pan.
- Nhấn vào từng node (học phần) sẽ mở ra một drawer (bảng trượt) chứa chi tiết thay vì chuyển trang.

## 16. Admin/recovery requirements (Yêu cầu quản trị/phục hồi)
- Hệ thống cần có Admin Dashboard để theo dõi Data Quality Issues.
- Cần có tính năng import lại một chương trình cụ thể thay vì phải import toàn bộ.
- Có giao diện hỗ trợ fix lỗi cấu trúc học kỳ bị gộp (Học kỳ 0).

## 17. Analytics placeholders (Theo dõi dữ liệu)
- Theo dõi số lượt xem của từng chương trình đào tạo.
- Theo dõi số lượt click vào chi tiết từng học phần.
- (Tương lai) Tracking lượt đóng góp tài liệu, số lượng vote hữu ích.

## 18. Acceptance criteria (Tiêu chí nghiệm thu)
- User có thể tìm kiếm và xem cấu trúc các học kỳ của các chương trình "ready" (ít nhất 126 chương trình clean).
- Các chương trình bị lỗi cấu trúc không xuất hiện trên UI của user thường.
- Dữ liệu môn học (số tín chỉ, mã môn) khớp chính xác với file markdown đầu vào.

## 19. Open questions (Câu hỏi còn bỏ ngỏ)
- Việc cập nhật chương trình đào tạo khi nhà trường ra quyết định mới sẽ lưu dưới dạng "phiên bản" hay tạo một record chương trình mới?
- Làm sao để khuyến khích sinh viên đóng góp ở Layer 2 một cách chất lượng (Gamification)?

## 20. Appendix: known issues (Phụ lục: Vấn đề đã biết)
- PDF source files: 968
- Program directories: 322
- Chuandaura PDFs: 321
- Chuongtrinhkhung PDFs: 319
- Quyetdinh PDFs: 320
- Generated roadmap.md files: 319
- Layer 1 import-ready programs: 278
- Clean programs: 126
- Programs needing recovery: 41
  - `unresolved_semester_structure`: 16
  - `empty_markdown`: 2
  - `missing_curriculum_pdf`: 3
  - `partial_semester_extraction`: 23
- Missing course descriptions: 172 programs / 3287 descriptions
