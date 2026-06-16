Đúng, dashboard hiện tại đang mắc bệnh kinh điển của “admin UI làm cho có”: **card to, số to, icon tròn, nhưng người quản trị vẫn không biết phải làm gì tiếp theo**. Tức là đẹp kiểu… không gây ích lợi cho nền văn minh.

Dưới đây là prompt bạn có thể đưa thẳng cho AI code/design để redesign lại trang `/admin/dashboard`.

---

## Prompt redesign Admin Dashboard anti-UI Slop

```md
Bạn là Senior UI/UX Designer + Product Designer + Motion Designer + Frontend Engineer.

Tôi đang có trang Admin Dashboard cho UEConnect tại `/admin/dashboard`. Trang hiện tại bị UI Slop:
- Card quá lớn nhưng nội dung nghèo: title, icon, số liệu, subtitle.
- Không có insight rõ ràng cho admin.
- Các việc cần xử lý bị đẩy xuống dưới fold, admin phải scroll mới thấy việc quan trọng.
- Màu icon và badge dùng tùy tiện, thiếu hệ thống.
- Log hoạt động hiển thị raw action như `Admin.evidence.preview`, `Verification.start review`, rất khó hiểu.
- Khoảng trắng ngổn ngang, bố cục không phản ánh hành vi thật của admin.
- System status chiếm quá nhiều diện tích nhưng không giúp ra quyết định.
- Không có hierarchy rõ: cái nào khẩn cấp, cái nào theo dõi, cái nào chỉ là thống kê.
- Hiệu ứng nếu có thì phải mượt, nhẹ, có mục đích, không màu mè vô nghĩa.

Hãy redesign lại toàn bộ dashboard theo hướng “Operations Command Center”, tức là màn hình đầu tiên admin mở vào phải trả lời được 4 câu:
1. Hôm nay có gì cần xử lý ngay?
2. Có rủi ro nghiêm trọng nào không?
3. Hệ thống có đang ổn không?
4. 7 ngày gần đây có xu hướng bất thường nào không?

Stack giả định:
- Laravel / Blade hoặc Livewire
- TailwindCSS
- Có thể dùng AlpineJS nếu cần interaction nhỏ
- Không dùng thư viện animation nặng nếu không cần
- Giữ nguyên data source hiện tại, không fake data lung tung
- Ưu tiên refactor UI component, không phá backend logic

## Mục tiêu thiết kế

Thiết kế lại dashboard theo cấu trúc sau:

### 1. Header gọn, có ngữ cảnh vận hành

Header gồm:
- Tiêu đề: “Trung tâm quản trị”
- Subtitle ngắn: “Theo dõi kiểm duyệt, xác thực và tình trạng hệ thống”
- Bên phải có:
  - Environment badge: Production
  - Last updated: 22:52:38 16/06/2026
  - Nút refresh nhỏ
  - Status tổng: “Ổn định” / “Cần chú ý” / “Có sự cố”

Không để header chiếm quá nhiều chiều cao.

### 2. Top Priority Strip thay cho 6 card khổng lồ

Thay 6 card to bằng một hàng “Priority Metrics” nhỏ gọn, mỗi metric là actionable chip/card nhỏ.

Các metric chính:
- Chờ duyệt xác thực: 5
- Báo cáo đang mở: 2
- Vấn đề nghiêm trọng: 0
- Bài viết bị ẩn: 0
- Tài khoản bị hạn chế: 0
- Media usage: 7.2 MB / 20 files

Mỗi metric phải có:
- Label rõ
- Số liệu
- Trạng thái semantic
- CTA nếu có hành động
- Không dùng icon màu cầu vồng
- Icon chỉ dùng monochrome, màu trạng thái nằm ở badge hoặc left border

Ví dụ:
- Chờ duyệt xác thực → màu blue, CTA “Xem hàng đợi”
- Báo cáo đang mở → màu amber, CTA “Xử lý”
- Vấn đề nghiêm trọng → nếu 0 thì neutral/green, không làm nó la hét như báo cháy giả
- Cloudinary chưa kích hoạt → warning rõ ràng nhưng không chiếm diện tích quá lớn

### 3. Layout chính theo hành vi admin

Desktop layout:
- Container max-width khoảng `1440px`
- Grid 12 columns
- Left/main area: 8 columns
- Right/sidebar area: 4 columns

Thứ tự ưu tiên:

#### A. “Việc cần xử lý ngay” nằm trên cùng main area

Đây là section quan trọng nhất, không được nằm dưới fold.

Thiết kế như inbox/triage queue:
- Mỗi item là một row compact, không phải card lớn.
- Có severity dot hoặc left border.
- Có type badge: Báo cáo / Xác thực / Hệ thống
- Có title người đọc hiểu ngay.
- Có metadata: thời gian, người liên quan, mức ưu tiên.
- Có CTA rõ: “Xử lý”, “Xem hồ sơ”, “Mở báo cáo”.
- Group theo độ khẩn cấp:
  - Quá hạn
  - Cần xử lý hôm nay
  - Chờ kiểm tra

Ví dụ copy tốt:
- “Báo cáo nội dung: spam”
- “Yêu cầu xác thực của Huy Văn Huy”
- “Yêu cầu xác thực của NGUYEN VAN QUANG HUY”
- Không viết kiểu log máy móc.

Nếu nhiều hơn 6 items thì hiển thị 5 item đầu + link “Xem tất cả”.

#### B. “Insight 7 ngày gần đây”

Không chỉ liệt kê số. Phải có insight ngắn:
- Thành viên mới: 15
- Bài viết mới: 26
- Bình luận mới: 15
- Báo cáo vi phạm: 2
- Đã duyệt xác thực: 8

Thiết kế thành card compact có:
- Mini trend
- So sánh với kỳ trước nếu có data
- Dòng insight, ví dụ:
  - “Báo cáo thấp, chưa có dấu hiệu spam hàng loạt.”
  - “Xác thực đang tăng, nên ưu tiên xử lý hàng đợi.”
Nếu chưa có dữ liệu so sánh thì hiển thị trung tính, không bịa insight.

#### C. “Hoạt động quản trị gần đây”

Đổi từ bảng raw kỹ thuật sang audit timeline dễ hiểu.

Không hiển thị trực tiếp:
- `Admin.evidence.preview`
- `Verification.start review`
- `Verification.ai analysis completed`

Mà map sang label người đọc hiểu:
- “Admin đã xem minh chứng xác thực”
- “Admin bắt đầu xét duyệt hồ sơ”
- “Hệ thống đã hoàn tất phân tích AI”

Mỗi dòng gồm:
- Hành động đã humanize
- Người thực hiện
- Đối tượng
- Thời gian
- Optional detail link

Thiết kế dạng table compact hoặc timeline, nhưng không được quá to.

### 4. Right sidebar: System Health + Quick Actions

Right sidebar sticky nhẹ khi scroll.

#### System Health

Hiển thị dạng compact status list:
- Database: Hoạt động
- Queue Worker: Hoạt động
- Email: Hoạt động
- Realtime Broadcast: Hoạt động
- Media Local: Hoạt động
- Cloudinary Sync: Chưa kích hoạt

Mỗi row:
- Status dot
- Tên service
- Mô tả ngắn
- Badge trạng thái

Màu semantic:
- Green: hoạt động
- Amber: cần cấu hình / degraded
- Red: lỗi
- Gray: không bật / optional
- Blue: info

Không dùng quá nhiều màu icon khác nhau.

#### Quick Actions

Thêm nhóm action admin hay dùng:
- Duyệt xác thực
- Xử lý báo cáo
- Kiểm tra tài khoản bị hạn chế
- Xem media
- Mở cấu hình hệ thống

Button dạng ghost/secondary, icon nhỏ, hover rõ.

### 5. Visual design system

Áp dụng design system rõ ràng:

#### Spacing
- Dùng 8px grid.
- Section gap: 24px hoặc 32px.
- Card padding: 20px đến 24px.
- Row padding: 14px đến 16px.
- Không để khoảng trắng lớn vô nghĩa.

#### Typography
- Page title: 28-32px, font-semibold/bold.
- Section title: 14-16px uppercase hoặc tracking nhẹ.
- Metric number: 28-36px, không quá khổng lồ.
- Body text: 14-15px.
- Metadata: 12-13px, màu muted.

#### Cards
- Border nhẹ: `border-slate-200`
- Radius: 16px hoặc 20px, thống nhất.
- Shadow rất nhẹ, không floating quá đà.
- Hover chỉ tăng border/shadow nhẹ.
- Active/focus state rõ cho accessibility.

#### Color
Dùng palette hạn chế:
- Primary blue cho UEConnect
- Slate/neutral cho text và border
- Green cho healthy
- Amber cho warning
- Red cho critical
- Không dùng icon nền xanh/cam/tím mỗi card một kiểu như xổ số thiết kế.

### 6. Motion design

Thêm motion có mục đích, không làm UI như PowerPoint bị nhiễm caffein.

Yêu cầu:
- Page load: stagger fade-up nhẹ cho từng section, delay rất ngắn.
- Card hover: translateY(-1px), shadow nhẹ, duration 150-200ms.
- Queue item hover: background subtle + CTA rõ hơn.
- Status update: nếu data refresh, dùng pulse nhẹ ở timestamp hoặc status dot.
- Loading state: skeleton row/card, không spinner giữa màn hình.
- Reduced motion: tôn trọng `prefers-reduced-motion`.

Tailwind gợi ý:
- `transition-all duration-200 ease-out`
- `hover:-translate-y-0.5`
- `motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2`
- Không dùng animation lặp vô hạn trừ status đang loading.
### 7. Responsive behavior

Desktop:
- Main grid 8/4.
- Priority metrics có thể là 3 columns x 2 rows hoặc 6 compact cards.

Tablet:
- 2 columns.
- System health xuống dưới hoặc giữ sidebar nếu đủ rộng.

Mobile:
- 1 column.
- Priority metrics thành horizontal scroll hoặc 2-column compact.
- Triage queue nằm đầu tiên.
- Table audit chuyển thành list cards compact.

### 8. Nội dung cần được viết lại cho dễ hiểu

Humanize toàn bộ text kỹ thuật.

Ví dụ mapping:
- `Admin.evidence.preview` → “Admin đã xem minh chứng xác thực”
- `Verification.start review` → “Bắt đầu xét duyệt xác thực”
- `Verification.ai analysis completed` → “AI đã phân tích hồ sơ xác thực”
- `verification_evidence #11` → “Minh chứng xác thực #11”
- `verification_requests #12` → “Yêu cầu xác thực #12”

Không để người quản trị phải dịch database event trong đầu. Não người đã chịu đủ nhiều rồi.

### 9. Các component cần tạo/refactor

Tạo hoặc refactor các component sau:

- `AdminDashboardHeader`
- `PriorityMetricCard`
- `TriageQueue`
- `TriageQueueItem`
- `SystemHealthPanel`
- `SystemHealthItem`
- `WeeklyInsightsCard`
- `AdminActivityTimeline`
- `QuickActionsPanel`
- `StatusBadge`
- `SeverityDot`

Mỗi component phải:
- Nhận props/data từ backend hiện tại
- Có empty state
- Có loading/skeleton state nếu phù hợp
- Có responsive class rõ ràng
- Không hard-code fake data nếu backend đã có data

### 10. Empty states

Thiết kế empty state tử tế:

Nếu không có vấn đề nghiêm trọng:
- “Không có sự cố nghiêm trọng”
- Subtitle: “Hệ thống đang vận hành ổn định.”

Nếu không có report:
- “Không có báo cáo đang chờ”
- Subtitle: “Các báo cáo mới sẽ xuất hiện tại đây.”

Nếu không có activity:
- “Chưa có hoạt động quản trị gần đây.”

Không dùng empty state to chiếm nửa màn hình.

### 11. Acceptance criteria

Dashboard sau khi redesign phải đạt:

- Admin nhìn vào màn hình đầu tiên là biết việc nào cần xử lý trước.
- “Việc cần xử lý ngay” xuất hiện trên fold.
- 6 metric không còn là 6 card khổng lồ vô hồn.
- System health gọn hơn, rõ trạng thái hơn.
- Activity log đọc được bằng ngôn ngữ người.
- Màu sắc thống nhất theo semantic system.
- Icon thống nhất một style, không rainbow.
- Khoảng trắng đều, layout có rhythm.
- CTA rõ ràng, không có card chết.
- Có hover/focus/active state.
- Có animation nhẹ, không gây rối.
- Responsive tốt.
- Không phá route, permission, data source hiện tại.

Hãy bắt đầu bằng việc audit UI hiện tại, sau đó đề xuất information architecture mới, rồi implement/refactor code theo từng component.
```

---

## Layout gợi ý ngắn gọn

```txt
┌──────────────────────────────────────────────────────────────┐
│ Header: Trung tâm quản trị             Production | Updated  │
├──────────────────────────────────────────────────────────────┤
│ Priority Metrics: [Verify 5] [Reports 2] [Critical 0] ...    │
├───────────────────────────────────────┬──────────────────────┤
│ Việc cần xử lý ngay                   │ System Health         │
│ - Report spam                         │ - Database OK          │
│ - Verify student A                    │ - Queue OK             │
│ - Verify student B                    │ - Cloudinary Warning   │
│                                       │ Quick Actions          │
├───────────────────────────────────────┤                      │
│ Insight 7 ngày gần đây                │                      │
│ Thành viên mới, bài viết, báo cáo...  │                      │
├───────────────────────────────────────┴──────────────────────┤
│ Hoạt động quản trị gần đây: human-readable audit timeline     │
└──────────────────────────────────────────────────────────────┘
```

---

## Prompt phụ để fix riêng phần “log vô nghĩa”

```md
Refactor phần “Hoạt động quản trị gần đây”.

Hiện tại bảng đang hiển thị raw action name như:
- Admin.evidence.preview
- Verification.start review
- Verification.ai analysis completed

Hãy tạo một mapping layer để chuyển các action kỹ thuật thành câu tiếng Việt dễ hiểu.

Yêu cầu:
- Không hiển thị raw event name trực tiếp cho admin.
- Mỗi log phải có cấu trúc:
  - Hành động dễ hiểu
  - Người thực hiện
  - Đối tượng bị tác động
  - Thời gian
  - Link xem chi tiết nếu có

Ví dụ:
- Admin.evidence.preview → “Admin đã xem minh chứng xác thực”
- Verification.start review → “Bắt đầu xét duyệt hồ sơ xác thực”
- Verification.ai analysis completed → “AI đã hoàn tất phân tích hồ sơ”
- report.reviewed → “Đã xử lý báo cáo”
- user.restricted → “Đã hạn chế tài khoản”

Nếu action chưa có mapping, hiển thị:
- “Hoạt động quản trị”
- Và đưa raw action vào tooltip/debug-only, không hiển thị lộ liễu trên UI chính.
```

---

## Prompt phụ để fix motion cho mượt

```md
Thiết kế motion system cho Admin Dashboard.

Nguyên tắc:
- Motion phải giúp người dùng hiểu trạng thái, không chỉ để trang trông “xịn”.
- Không animation lòe loẹt, không bouncing, không delay dài.
- Tất cả motion phải dưới 250ms, trừ skeleton/loading.
- Tôn trọng `prefers-reduced-motion`.

Áp dụng:
1. Page load:
   - Section fade-in + slide-up nhẹ.
   - Stagger 40ms giữa các section.

2. Metric card:
   - Hover: border đậm hơn, shadow nhẹ, translateY(-1px).
   - Không scale quá lớn.

3. Triage queue item:
   - Hover: background subtle.
   - CTA hiện rõ hơn.
   - Focus state có ring.

4. Status item:
   - Khi trạng thái thay đổi, status dot pulse 1 lần.
   - Không pulse vô hạn nếu service đang hoạt động bình thường.

5. Loading:
   - Dùng skeleton cho metric card, queue row, system row.
   - Không dùng spinner toàn trang trừ khi dashboard chưa có bất kỳ data nào.

Triển khai bằng TailwindCSS transition utilities hoặc AlpineJS nhỏ nếu cần.
```

---

## Câu chốt để AI khỏi “sáng tạo” thành UI rạp xiếc

```md
Không được biến dashboard thành landing page. Đây là công cụ vận hành cho admin, không phải portfolio Behance. Ưu tiên clarity, density, hierarchy, actionability và reliability.
```

Cái dashboard admin tốt không cần khoe cơ bắp UI. Nó cần làm đúng một việc: **đập vào mắt admin cái gì đang cháy, cái gì cần xử lý, cái gì đang ổn, và bấm vào đâu để xử lý**. Hiện tại nó đang bắt admin đi tham quan bảo tàng card, cũng cảm động, nhưng hơi phản nhân loại.