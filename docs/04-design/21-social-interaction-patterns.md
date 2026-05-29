---
title: "Social Interaction Patterns"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-29"
owner: "Product Design / UI Design / Frontend"
depends_on:
  - "00-design-foundation-roadmap.md"
  - "03-color-system.md"
  - "08-shadow-elevation-system.md"
  - "10-icon-system.md"
  - "12-component-primitives.md"
  - "13-component-variants.md"
  - "14-interaction-states.md"
  - "15-motion-system.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
---

# 21. Social Interaction Patterns

## 1. Purpose

Tài liệu này định nghĩa hệ thống tương tác mạng xã hội (social interaction patterns) chính thức của UEConnect, lấy cảm hứng từ các quy chuẩn trải nghiệm nội dung và phản hồi mượt mà của Threads/Facebook.

Mục tiêu cốt lõi là làm cho UEConnect trở nên sống động, có tính phản hồi tức thì (emotional feedback) nhưng vẫn duy trì sự nghiêm túc, tinh tế, tối giản học thuật của Đại học Sư phạm TP.HCM (HCMUE).

---

## 2. Interaction Direction

Tương tác trên UEConnect cần đảm bảo các tiêu chí định hướng:

```txt
responsive (phản hồi tức thì)
calm (điềm tĩnh, tinh tế)
social (kết nối xã hội cao)
trusted (đáng tin cậy)
modern (hiện đại)
content-first (nội dung là trung tâm)
mobile-native (tối ưu hóa di động)
HCMUE-rooted (gắn liền cốt cách Sư phạm)
```

UEConnect **tuyệt đối tránh**:

```txt
dead static UI (giao diện tĩnh, đơ)
random hover colors (màu hover tuỳ tiện)
slow transitions (hiệu ứng chuyển động chậm chạp)
overly large cards (các thẻ nội dung quá to nặng)
heavy shadows everywhere (lạm dụng shadow/elevation)
inconsistent icons (biểu tượng không nhất quán)
unclear action states (trạng thái tương tác mờ nhạt)
copying Threads/Facebook blindly (sao chép mù quáng)
dating-app-like animation (hiệu ứng kiểu app hẹn hò)
```

---

## 3. Threads-Inspired Patterns to Learn

Phân tích và tài liệu hoá các mô thức trải nghiệm (UX patterns) từ Threads được áp dụng vào UEConnect:

### 3.1. Content-first feed

* **Thiết kế dòng chảy nội dung (Social Stream):** Loại bỏ thiết kế "card nổi" (heavy card) quá dày đặc với viền dày và đổ bóng nặng. Mỗi bài viết là một đơn vị nội dung liên tục.
* **Divider thay vì Shadow:** Sử dụng border nhẹ (`color.border.subtle`) hoặc divider mỏng làm ranh giới phân tách các post, thay vì dùng elevation shadow phức tạp. Chỉ sử dụng shadow khi thực sự cần thiết cho việc xếp lớp (layering) giao diện.
* **Liên kết thị giác (Visual Connection):** Kết nối mạch lạc từ avatar tác giả, tên, timestamp, phần nội dung, hình ảnh media, cho tới thanh hành động (action row) và chuỗi phản hồi (reply chain).
* **Tốc độ quét nhanh (Fast Scanning):** Thiết kế dòng feed có mật độ thông tin tối ưu để người dùng dễ dàng lướt nhanh trên mobile.

### 3.2. Trạng thái phản hồi tức thì (Immediate Action Feedback)

Mọi tương tác xã hội (like, comment, reply, save, hide, report, delete, edit, follow, open/close menu, submit post/comment) phải hỗ trợ đủ 8 trạng thái tương tác sau:

1. **Default (Mặc định):** Trạng thái nghỉ bình thường.
2. **Hover (Rê chuột):** Thay đổi sắc độ màu nền nhẹ, tăng độ rõ nét của icon trên desktop.
3. **Pressed/Active (Nhấn giữ/Kích hoạt):** Giảm scale nhẹ (scale-98) tạo cảm giác nút lún xuống vật lý.
4. **Optimistic (Cập nhật lạc quan):** Giao diện thay đổi ngay lập tức (ví dụ: chuyển trạng thái active icon, tăng số đếm) trước khi server trả về kết quả.
5. **Loading (Đang xử lý):** Hiển thị inline spinner hoặc lu mờ nút nhẹ nếu hành động tốn thời gian.
6. **Success (Thành công):** Xác nhận trạng thái mới hoặc ẩn vĩnh viễn nội dung bị xóa.
7. **Failure Rollback (Hoàn tác lỗi):** Trả lại trạng thái ban đầu của giao diện nếu server trả về mã lỗi, kèm theo toast thông báo rõ ràng.
8. **Disabled/Restricted (Bị vô hiệu hoá):** Giảm opacity xuống 40%, không nhận tương tác nếu người dùng bị khoá chức năng hoặc bài viết đã bị đóng bình luận.

### 3.3. Mô thức cập nhật lạc quan (Optimistic UI)

* **Áp dụng:** Áp dụng bắt buộc cho nút **Like (Thích)** và **Save (Lưu bài viết)**. Số lượng lượt thích và trạng thái lưu sẽ thay đổi tức thì ngay khi click/tap.
* **Hoàn tác lỗi (Rollback):** Nếu API trả về thất bại, giao diện sẽ rollback số đếm và màu sắc biểu tượng về trạng thái cũ, đồng thời hiển thị một Toast cảnh báo lỗi (ví dụ: *"Không thể thích bài viết. Vui lòng thử lại sau."*).
* **Destructive Actions:** Các hành động mang tính hủy hoại (Xóa bài viết) cần có hộp thoại xác nhận trước khi thực hiện chứ không áp dụng Optimistic UI thuần túy. Các hành động ẩn/báo cáo có thể tối ưu bằng cách làm mờ hoặc ẩn bài viết ngay lập tức kèm theo Toast hỗ trợ Hoàn tác (Undo).
* **Tuyệt đối không áp dụng:** Tuyệt đối không dùng Optimistic UI cho các thao tác phê duyệt của Quản trị viên (Admin approval) hoặc xác thực danh tính (Identity verification).

### 3.4. Post Action Menu (`...`)

Standard menu hành động cho bài viết được phân tách theo vai trò:

* **Cho người xem thông thường (Viewer):**
  ```txt
  Lưu bài viết (Bookmark post)
  Ẩn bài viết này (Hide post)
  Không quan tâm nội dung tương tự (Mute similar content)
  Báo cáo vi phạm (Report violation)
  Sao chép liên kết (Copy link)
  Theo dõi / Bỏ theo dõi tác giả (Follow / Unfollow author)
  ```
* **Cho chủ sở hữu bài viết (Owner):**
  ```txt
  Sửa bài viết (Edit post)
  Xóa bài viết (Delete post)
  Sao chép liên kết (Copy link)
  Ai có thể xem bài viết (Audience settings)
  ```
* **Cho Quản trị viên / Kiểm duyệt viên (Admin/Moderator):**
  ```txt
  Ẩn bài viết (Hide post)
  Gỡ bài viết (Remove post)
  Xem báo cáo (View report detail)
  Đánh dấu đã xử lý (Mark as moderated)
  Khóa bình luận (Lock comments)
  ```

**Quy chuẩn hành vi (Behavior Rules):**
* **Desktop:** Hiển thị dưới dạng Popover dropdown nhỏ gọn, neo góc trực tiếp vào nút `...`. Animate nhẹ bằng hiệu ứng `fade` + `scale` (scale-96 -> 1). Tự động đóng khi click ra ngoài, nhấn `Escape` hoặc thay đổi route.
* **Mobile:** Hiển thị dưới dạng Bottom Sheet trượt mượt mà từ dưới lên, có drag handle trực quan, khoảng chạm tối thiểu 44px, nhấn vào backdrop nền tối màu để đóng.

### 3.5. Quick Hide Action (`X`)

* **Desktop:** Nút `X` đóng nhanh bài viết chỉ xuất hiện khi hover chuột vào khung bài viết hoặc bài viết nhận focus-within để giữ feed sạch sẽ.
* **Mobile:** Không hiển thị nút `X` vĩnh viễn trên feed để tránh làm UI rối mắt. Hành động ẩn nằm gọn trong menu `...`.
* **Undo Toast:** Mọi hành động ẩn bài viết phải đi kèm Toast xác nhận: *"Đã ẩn bài viết này. [Hoàn tác]"*.

### 3.6. Comment and Reply Threading

* **LEFT RAIL & INDENTATION:** Các bình luận cha nằm sát lề trái, các bình luận con (reply) được thụt đầu dòng (indentation) vừa phải và liên kết với nhau bằng một dải phân cách dọc mảnh (left rail) mờ nhẹ (`color.border.subtle`).
* **Focus Composer:** Khi nhấn nút "Phản hồi" (Reply), màn hình sẽ tự động focus vào ô nhập bình luận (composer), điền sẵn tag tên của người nhận phản hồi.
* **Mượt mà:** Bình luận mới gửi thành công sẽ được trượt và hiện dần lên (fade-slide) mượt mà tại cuối chuỗi phản hồi.
* **Thu gọn:** Các chuỗi phản hồi quá dài sẽ được thu gọn tự động, chỉ hiển thị kèm theo nút bấm *"Xem thêm 5 phản hồi..."*.

### 3.7. Composer Interaction

Trạng thái và hành vi của hộp soạn thảo bài viết/bình luận:
* **Trạng thái:** `idle` -> `focused` -> `typing` -> `valid/invalid` -> `submitting` -> `submitted/failed`.
* **Mở rộng mềm mại:** Hộp text area tự động giãn chiều cao (auto-expand) nhẹ nhàng khi nhận focus.
* **Chỉ số giới hạn ký tự:** Bộ đếm ký tự chỉ hiển thị khi bài viết đạt mức cảnh báo (còn 50 ký tự cuối cùng trước khi vượt giới hạn).
* **Submit Action:** Nút "Đăng" bị vô hiệu hoá nếu không có nội dung hợp lệ. Khi click, hiển thị inline spinner trên nút, khoá nhập liệu tạm thời.
* **Xử lý kết quả:** Nếu thành công, composer xóa sạch chữ và bài viết xuất hiện ngay trên feed. Nếu thất bại, giữ lại toàn bộ chữ để người dùng không bị mất nội dung soạn thảo.

### 3.8. Toast and Undo

* **Sử dụng:** Áp dụng cho các phản hồi hành động nhanh: Lưu bài viết, ẩn bài viết, báo cáo vi phạm, sao chép liên kết thành công, xoá bài viết, hoàn tác thành công.
* **Thời gian hiển thị:** Từ 3 đến 5 giây, sau đó tự động biến mất bằng hiệu ứng slide-out hoặc fade-out nhẹ nhàng.
* **Hỗ trợ Undo:** Bắt buộc có nút "Hoàn tác" rõ ràng trong toast đối với các thao tác ẩn bài, lưu bài, xoá bài viết.
* **Vị trí hiển thị trên Mobile:** Phải xuất hiện ở phía dưới nhưng nằm hoàn toàn trên thanh Bottom Navigation để không chặn các nút điều hướng chính.

### 3.9. Skeleton and Loading

* **Áp dụng:** Dùng skeleton loading cho feed bài viết, danh sách bình luận, thẻ xem trước trang cá nhân, danh sách bài viết đã lưu, bảng thao tác admin.
* **Tránh lạm dụng:** Không sử dụng các vòng xoay spinner chiếm trọn màn hình (full-page spinner) trừ khi trang đó hoàn toàn chưa có bất kỳ dữ liệu cũ nào được cache.

### 3.10. Empty States

* **Nguyên tắc:** Các trang trống (Ví dụ: danh sách bài viết đã lưu trống, feed không có nội dung, chưa có thông báo) phải được thiết kế trang nhã để không làm vỡ bố cục hiển thị.
* **Yêu cầu cấu trúc:** Mỗi empty state bắt buộc chứa:
  1. Biểu tượng minh họa nhẹ nhàng dạng vẽ nét (outline icon).
  2. Tiêu đề ngắn gọn, rõ ràng (Title).
  3. Dòng mô tả giải thích ngắn (Short helper text).
  4. Nút hành động kêu gọi nếu phù hợp (Primary/Secondary action button).

---

## 4. Motion Tokens

Cập nhật hệ thống token chuyển động chính thức của UEConnect trong CSS variables và Tailwind config:

### CSS Variables (`:root`)
```css
:root {
  --motion-duration-instant: 75ms;
  --motion-duration-fast: 120ms;
  --motion-duration-base: 180ms;
  --motion-duration-slow: 240ms;
  --motion-duration-sheet: 280ms;

  --motion-ease-standard: cubic-bezier(0.2, 0, 0, 1);
  --motion-ease-out: cubic-bezier(0, 0, 0.2, 1);
  --motion-ease-in: cubic-bezier(0.4, 0, 1, 1);
  --motion-ease-emphasized: cubic-bezier(0.2, 0, 0, 1.2);

  --motion-press-scale: 0.98;
  --motion-popover-scale-from: 0.96;
  --motion-like-scale: 1.16;
}
```

### Tailwind Config Aliases
```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      transitionDuration: {
        instant: '75ms',
        fast: '120ms',
        base: '180ms',
        slow: '240ms',
        sheet: '280ms',
      },
      transitionTimingFunction: {
        standard: 'cubic-bezier(0.2, 0, 0, 1)',
        out: 'cubic-bezier(0, 0, 0.2, 1)',
        in: 'cubic-bezier(0.4, 0, 1, 1)',
        emphasized: 'cubic-bezier(0.2, 0, 0, 1.2)',
      }
    }
  }
}
```

---

## 5. Component Updates

Mở rộng đặc tả cho các components tương tác chính:

### 5.1. ActionButton & IconButton
* **Purpose:** Các nút kích hoạt hành động nhanh trên giao diện.
* **States:** Default (xám nhạt), Hover (nền tối hơn chút), Press (scale-98).
* **A11y:** Phải có `aria-label` nếu chỉ dùng icon đơn độc.

### 5.2. PostActionButton (Nút tương tác bài viết)
* **Purpose:** Thích (Like), Bình luận (Comment), Lưu (Save), Chia sẻ (Share).
* **Selected Like:** Trực quan hóa bằng biểu tượng trái tim đỏ hoặc xanh đậm (tùy cài đặt hệ thống), scale nhảy nhẹ (max scale-116 trong 120ms) tạo cảm giác cực kỳ sinh động và "đã tay".
* **Selected Save:** Biểu tượng bookmark được fill đầy màu, đổi sắc độ để biểu thị trạng thái đã lưu rõ ràng.

### 5.3. PostMenu & DropdownMenu
* **Desktop Popover:** Neo vào nút `...`, rộng tối đa 320px, có animate mở bằng scale-96 và fade-in.
* **Mobile Bottom Sheet:** Chiều cao tự thích ứng, chạm tối thiểu 44px, có thanh drag handle ở đỉnh.
* **Accessibility:** Hỗ trợ điều hướng bằng phím mũi tên và tắt mở nhanh bằng phím `Escape`.

### 5.4. Toast & UndoToast
* **Purpose:** Phản hồi thông tin hành động.
* **Undo button:** Phải được tạo kiểu dạng liên kết chữ có màu sắc nổi bật, khoảng chạm vừa tay.
* **Accessibility:** Tự động dùng `role="status"` để trình độ màn hình thông báo phi cản trở.

---

## 6. Social Interaction State Matrix

Hệ thống ma trận tương tác của UEConnect:

| Hành động (Action) | Tác nhân kích hoạt (Trigger) | Phản hồi giao diện tức thì (Immediate UI) | Hoạt động phía Server (Server Action) | Trạng thái Thành công (Success UI) | Trạng thái Thất bại (Failure UI) | aria-label / ARIA Announcement | Chế độ giảm chuyển động (Reduced Motion) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Thích bài viết (Like Post)** | Click / Tap nút Like | Icon chuyển trạng thái Active, đếm số lượt +1, nút thu nhỏ nhẹ (scale-98). | Livewire: `toggleLike()` | Giữ nguyên trạng thái đã chọn. | Rollback icon và số đếm về cũ, hiển thị Toast báo lỗi. | *"Đã thích bài viết"* / *"Đã bỏ thích bài viết"* | Tắt hiệu ứng scale, chỉ thay đổi màu và trạng thái icon. |
| **Lưu bài viết (Save Post)** | Click / Tap nút Save | Icon Bookmark chuyển sang trạng thái fill đầy, cập nhật lạc quan. | Livewire: `toggleSave()` | Giữ trạng thái lưu, hiện Toast báo thành công kèm nút Hoàn tác. | Rollback trạng thái icon, hiển thị Toast báo lỗi. | *"Đã lưu bài viết"* / *"Đã bỏ lưu bài viết"* | Tắt mọi hiệu ứng scale hoặc slide, chỉ đổi trạng thái icon tức thì. |
| **Ẩn bài viết (Hide Post)** | Click nút `X` hoặc chọn "Ẩn bài" | Làm mờ và ẩn nhanh bài viết khỏi dòng feed bài đăng tức thì. | Livewire: `hidePost()` | Ẩn hoàn toàn bài đăng, hiện Toast *"Đã ẩn bài viết. [Hoàn tác]"*. | Phục hồi lại bài viết cũ, báo lỗi qua Toast. | *"Đã ẩn bài viết"* | Bài viết biến mất ngay lập tức không qua hiệu ứng fade/slide. |
| **Gửi bình luận (Submit Comment)** | Click nút Gửi bài / Phím Enter | Nút chuyển sang trạng thái Loading với inline spinner, khóa tạm ô nhập chữ. | Livewire: `submitComment()` | Làm trống composer, chèn bình luận mới vào danh sách kèm hiệu ứng fade-in. | Mở khóa composer, giữ nguyên chữ viết dở để user thử lại, hiện Toast lỗi. | *"Đã đăng bình luận thành công"* | Bình luận mới hiện ra tức thì không qua hiệu ứng trượt. |
| **Mở Menu bài viết (Open Post Menu)** | Click / Tap nút `...` | Hiện Popover neo (desktop) hoặc trượt Bottom Sheet (mobile). | Không tốn tài nguyên server | Menu hiển thị hoàn toàn. | N/A | *"Menu hành động bài viết"* | Hiện menu tức thì không qua hiệu ứng trượt và giãn nở scale. |

---

## 7. Accessibility Requirements (A11y)

* **Aria Labels:** Tất cả icon button tương tác (Like, Comment, Save, Share, Close, Menu) bắt buộc chứa thuộc tính `aria-label` chi tiết.
* **Aria State Indicators:** Nút Like và Save phải phản ánh đúng trạng thái qua thuộc tính `aria-pressed="true/false"`.
* **Focus Trapping:** Khi Bottom Sheet trên mobile được mở rộng, bắt buộc khoá tiêu điểm bàn phím (focus trap) bên trong nó cho đến khi đóng lại.
* **Escape to Close:** Nhấn nút `Escape` phải đóng ngay lập tức bất kỳ dropdown menu, popover, hoặc bottom sheet nào đang mở.
* **Prefers-reduced-motion:** Nếu trình duyệt cấu hình giảm chuyển động, vô hiệu hóa hoàn toàn mọi hiệu ứng scale nhảy (like scale pop), slide-in của bottom sheet và transition của dropdown.

---

## 8. Responsive Requirements

### 8.1. Feed Desktop Layout
* **Trái (Left Sidebar):** Sidebar cố định để điều hướng trang.
* **Giữa (Feed Column):** Căn giữa hoàn toàn, độ rộng tối đa 720px để giữ trải nghiệm đọc tập trung nhất.
* **Post Menu:** Hiển thị dưới dạng Popover neo nhỏ gọn kế bên nút bấm.
* **Composer:** Soạn thảo ngay trực tiếp trong feed thẻ bài viết (inline card).

### 8.2. Feed Mobile Layout
* **Không dùng desktop sidebar:** Thay thế bằng Bottom Navigation mượt mà ở đáy màn hình.
* **Post Menu:** Mở rộng thành Bottom Sheet trượt mượt mà từ đáy lên với góc chạm lớn (>44px).
* **Toast placement:** Luôn nổi ở phía trên thanh Bottom Nav để không đè lên các nút tab chính.
* **Saved Posts:** Bố cục hiển thị dọc gọn gàng (single column), không để tràn lề ngang, empty state cân đối hoàn hảo trong viewport.

---

## 9. Icon System Update

Quy định biểu tượng hành động thống nhất (sử dụng hệ icon Lucide/Heroicons nhất quán):

```txt
Thích (Like)     →  Heart (outline cho mặc định, filled đỏ/xanh đậm cho active)
Bình luận (Reply) →  MessageCircle (outline mỏng)
Lưu (Save)       →  Bookmark (outline cho mặc định, filled cho active)
Chia sẻ (Share)   →  Send / Share (outline mỏng)
Menu phụ (...)   →  Ellipsis / MoreHorizontal
Ẩn bài (Hide)    →  EyeOff / X (cho nút đóng nhanh)
Báo cáo (Report) →  Flag / AlertTriangle
Xóa bài (Delete) →  Trash (danger color)
Sửa bài (Edit)   →  Pencil
```

---

## 10. Implementation Guidance

Mẫu lộ trình triển khai chi tiết cho kỹ sư frontend:

### Phase 1: Token & Primitives Foundation
1. Định nghĩa các CSS variables chuyển động (`--motion-*`) trong file style chính `index.css`.
2. Đăng ký các Tailwind extend key tương ứng cho duration và easing trong file `tailwind.config.js`.
3. Kiểm tra và chuẩn hóa các Blade component nền tảng: `x-ui.button`, `x-ui.icon-button`, `x-ui.dropdown`, `x-ui.toast` để sẵn sàng nhận token mới.

### Phase 2: Feed Interaction Polish
1. Cập nhật giao diện thanh hành động của bài viết trên Home Feed và Trang chi tiết bài viết.
2. Thêm visual states nổi bật cho nút Like/Save, thiết lập Optimistic UI cập nhật tức thì.
3. Tích hợp menu `...` popover trên màn hình desktop.
4. Cài đặt nút `X` ẩn nhanh bài đăng kèm Toast hỗ trợ Undo.
5. Cân chỉnh trang chi tiết bình luận, hỗ trợ thụt đầu dòng (indentation) mượt mà cho chuỗi reply.

### Phase 3: Mobile Interaction Polish
1. Tích hợp Bottom Sheet thay thế Popover cho menu `...` trên thiết bị di động.
2. Đảm bảo Toast hiển thị đúng khoảng cách phía trên Bottom Navigation.
3. Sửa lỗi tràn chiều rộng của Composer và thẻ bài viết trên màn hình nhỏ.
4. Tích hợp hỗ trợ `prefers-reduced-motion` qua CSS/Tailwind classes.

### Phase 4: QA & User Acceptance Testing (UAT)
Tiến hành kiểm tra theo danh sách sau:
* [ ] Di chuột (hover) trên bài viết không làm chữ biến mất hay đổi màu sai.
* [ ] Nhấp Thích (Like) làm icon chuyển màu đỏ/nổi bật và tăng số lượt tức thì.
* [ ] Nhấp Lưu (Save) cập nhật lạc quan và hiện Toast thành công có nút Hoàn tác.
* [ ] Thao tác ẩn bài viết hoạt động mượt mà và phục hồi được nếu bấm Hoàn tác.
* [ ] Menu `...` hiển thị dạng Dropdown ở Desktop và dạng Bottom Sheet trượt ở Mobile.
* [ ] Hộp composer bình luận hoạt động tốt trên thiết bị di động, không bị vỡ hoặc trượt khỏi khung nhìn.
* [ ] Bàn phím có thể điều hướng được menu dropdown, nhấn phím `Escape` tự đóng menu.
* [ ] Bật chế độ "Reduced Motion" trong cài đặt hệ thống làm tắt toàn bộ các hiệu ứng trượt/phóng to lướt nhẹ.
