# Single source of truth

1. Product summary
2. Product vision
3. Target users
4. Core problems
5. Product pillars
6. MVP scope
7. Out of scope
8. Success metrics
9. Key constraints
10. Open questions

# 1. Product definition đã chốt

## UEConnect là gì?

```txt
UEConnect là một verified student social platform dành riêng cho HCMUE, được định hướng triển khai thật trong trường và đồng thời dùng làm portfolio case study.
```

UEConnect phục vụ 3 tầm nhìn dài hạn:

```txt
A. Mạng xã hội nội bộ HCMUE
B. Nền tảng kết nối sinh viên trong trường
C. Nền tảng học tập, mentor, cộng đồng cho sinh viên
```

Không làm:

```txt
D. Super app sinh viên
E. Multi-university platform
```

Phạm vi:

```txt
Chỉ dành cho HCMUE.
Không thiết kế multi-university ở giai đoạn hiện tại.
```

---

# 2. Strategic direction

## Core users

Ưu tiên chính:

```txt
Sinh viên HCMUE
Cựu sinh viên
Cố vấn học tập / mentor
CLB / community manager
Admin / moderator
Nhà trường / khoa với vai trò stakeholder triển khai thực tế
```

## Pain points chính

UEConnect giải quyết:

```txt
A. Sinh viên khó làm quen bạn mới trong trường
B. Sinh viên khó tìm bạn cùng môn/cùng khoa
E. Sinh viên thiếu mentor/cố vấn gần gũi
F. Alumni khó kết nối lại với sinh viên
G. CLB khó truyền thông và quản lý cộng đồng
H. Sinh viên năm nhất khó hòa nhập
```

Không ưu tiên ở core problem hiện tại:

```txt
C. Thay thế hoàn toàn Zalo/Messenger cho lớp/CLB
D. Gom toàn bộ thông tin trường
I. Xây dựng professional identity kiểu LinkedIn
J. Social network mở rộng ngoài trường
```

---

# 3. MVP scope đã chốt

Bạn đang xem tất cả module sau là **P0 trong đầu bạn**, tức MVP tham vọng khá lớn. Nói thẳng: đây không còn là MVP “nhỏ xinh”, đây là MVP kiểu “tôi muốn làm nguyên hệ sinh thái nhưng vẫn gọi là MVP để đỡ sợ”. Được, miễn là docs phải rất rõ.

P0 gồm:

```txt
Auth + mã sinh viên verification
Profile setup
Home feed
Create post
Comment
Discovery UEers
Gửi lời chào / connection
Messaging 1:1
Mentor
Community / CLB
Notifications
Settings / privacy
Report / block
Admin verification
Admin moderation
```

P0 mở rộng nhưng cần kiến trúc sẵn:

```txt
Community chat
Realtime notifications
Resource library
Mentor scheduling
```

Public landing page:

```txt
Cần có public landing page tối giản.
Guest vào hiểu UEConnect là gì, vì sao cần verified, sau đó đăng ký/đăng nhập.
```

---

# 4. Verification model

UEConnect dùng verification nghiêm túc, vì trust là xương sống.

## Verification methods

```txt
1. Admin duyệt thủ công
2. User upload minh chứng sinh viên / alumni / advisor
3. Email trường dạng hcmue.edu.vn nếu có
4. Kiểm tra MSSV uniqueness
```

Minh chứng hợp lệ:

```txt
Bảng điểm
Giấy nhập học
Bằng tốt nghiệp
Thẻ sinh viên
Email trường
Tài liệu pháp lý số chứng minh liên hệ với HCMUE
```

Rule quan trọng:

```txt
Một MSSV chỉ được liên kết với một tài khoản duy nhất.
Không thể có 2 account dùng cùng MSSV.
```

Đây phải thành business rule, database constraint, admin review rule, và edge case chính thức. Không chỉ viết cho đẹp rồi hy vọng backend nhớ. Hy vọng không phải design pattern.

---

# 5. Role model đã chốt

## Role hệ thống

Mỗi user có **một role hệ thống chính**:

```txt
Student
Alumni
Advisor
Admin
```

Quy tắc:

```txt
Student / Alumni / Advisor được xác thực ban đầu và cố định theo identity.
Admin là role quản trị đặc biệt.
Student có thể tự chuyển thành Alumni theo cơ chế năm học / tốt nghiệp.
```

## Permission phụ

Ngoài role hệ thống, admin có thể grant thêm permission/assignment:

```txt
Club Manager
Community Moderator
Mentor Permission
Verification Reviewer
Content Moderator
System Admin Permission
```

Điểm quan trọng:

```txt
Role chính xác định identity.
Permission phụ xác định quyền thao tác.
```

Đây là hướng tốt hơn “một user có 7 role lung tung”. Nó sạch hơn cho enterprise permission. Thật khó tin, nhưng phân quyền rõ từ đầu có thể cứu cả đời dev sau này.

---

# 6. Community / CLB model

Community/CLB được quản lý chặt.

Quy tắc:

```txt
Admin tạo community/CLB chính thức.
User có thể đề xuất community/CLB qua mail hoặc chat riêng.
Club Manager có thể được admin gán quyền tạo/quản lý community.
Community/CLB vẫn cần admin duyệt trước khi public.
Club Manager permission phản ánh cơ cấu quản lý thật của CLB tại HCMUE.
```

Cấu trúc permission nên chuẩn bị:

```txt
Club Owner
Club Manager
Club Moderator
Club Member
General Student
Admin
```

---

# 7. Safety policy

Nội dung bị cấm:

```txt
Nội dung vi phạm bản quyền
Không tôn trọng chất xám
Dating / sexual content
Quấy rối
Spam
Giả mạo danh tính
Lộ thông tin cá nhân
Tài liệu vi phạm bản quyền
Ngôn từ công kích
Scam / lừa đảo
Nội dung chính trị nhạy cảm trong môi trường trường học
```

Report model:

```txt
User report → Admin review → Admin action
Auto-hide nếu nhiều report
Keyword flagging
Kết hợp nhiều cơ chế
```

Admin cần:

```txt
Moderation queue
Report detail
Auto-hide threshold
Keyword flagging
Manual action
Audit log
Appeal / review later nếu cần
```

---

# 8. Tech direction đã chốt

Stack chính:

```txt
Laravel
Blade
TailwindCSS
Vite
MS SQL Server
CSS Modules nếu cần
PWA
```

Bạn định hướng UEConnect là:

```txt
PWA có thể cài như app, có widget/shortcut, hoạt động tốt trên desktop và mobile mà không cần tải native app.
```

## Tech recommendation nhanh

| Mảng                 | Khuyến nghị                                           | Lý do                                                                                                                                                |
| -------------------- | ----------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| Backend              | Laravel                                               | Hợp stack bạn chọn, mạnh về auth, queue, broadcasting, validation                                                                                    |
| Frontend rendering   | Blade + partial components                            | Phù hợp server-rendered app, dễ làm nhanh, dễ kiểm soát                                                                                              |
| Styling              | TailwindCSS + CSS variables                           | Hợp design token system, dễ maintain                                                                                                                 |
| Asset build          | Vite                                                  | Laravel có plugin chính thức và Blade directive để load asset dev/production. ([Laravel][1])                                                         |
| Tailwind integration | Tailwind official Laravel guide                       | Tailwind có hướng dẫn Laravel với Vite và `@vite` trong Blade. ([Tailwind CSS][2])                                                                   |
| Auth web             | Laravel starter kit / Fortify-based auth              | Laravel starter kits cung cấp route/controller/view cho register/login và dùng Fortify cho authentication. ([Laravel][3])                            |
| API/PWA future       | Laravel Sanctum                                       | Sanctum hỗ trợ API token auth bằng bảng token và Authorization header, hợp nếu sau này mở API/mobile/PWA nâng cao. ([Laravel][4])                    |
| Realtime             | Laravel Reverb trước, Pusher/Ably sau nếu cần managed | Laravel Reverb là first-party WebSocket server cho Laravel, tích hợp với broadcasting. ([Laravel][5])                                                |
| Broadcast drivers    | Reverb / Pusher / Ably                                | Laravel broadcasting hỗ trợ Reverb, Pusher, Ably và log driver. ([Laravel][6])                                                                       |
| Database             | MS SQL Server                                         | Hợp yêu cầu SQL Server Audit và enterprise planning                                                                                                  |
| Audit DB             | SQL Server Audit + app-level audit_logs               | SQL Server Audit hỗ trợ audit server-level và database-level action; có thể tạo server audit và database audit specification. ([Microsoft Learn][7]) |

## Lưu ý quan trọng về audit

Bạn muốn audit toàn bộ user, DDL, setting, admin action. Nên tách làm 2 lớp:

```txt
1. SQL Server Audit
   Ghi nhận server/database-level events, DDL, permission, schema changes, sensitive operations.

2. Application Audit Log
   Ghi nhận nghiệp vụ app:
   admin duyệt user nào,
   ai xóa post nào,
   ai block ai,
   report nào được xử lý,
   role/permission nào được grant.
```

SQL Server Audit rất mạnh ở cấp database engine, nhưng không thay thế được app audit kiểu “Admin A reject tài khoản B vì thiếu minh chứng”. CSDL không tự hiểu drama nghiệp vụ, thật bất tiện.

---

# Project Brief

## 1. Product Summary

UEConnect là một verified student social platform dành riêng cho cộng đồng HCMUE.

Sản phẩm được định hướng như một sản phẩm thật có thể triển khai trong trường, đồng thời được sử dụng như một portfolio case study để thể hiện năng lực product planning, system analysis, UX design, frontend architecture và enterprise documentation.

UEConnect giúp sinh viên HCMUE kết nối, làm quen, đăng bài, khám phá UEers, nhắn tin, tìm mentor, tham gia cộng đồng/CLB và phát triển trong hành trình học tập thông qua một môi trường có xác thực danh tính, kiểm duyệt nội dung và quản lý an toàn.

## 2. Product Vision

UEConnect hướng đến 3 tầm nhìn dài hạn:

```txt
A. Mạng xã hội nội bộ HCMUE
B. Nền tảng kết nối sinh viên trong trường
C. Nền tảng học tập, mentor, cộng đồng cho sinh viên
````

UEConnect không hướng đến:

```txt
D. Super app sinh viên
E. Multi-university platform
```

Tầm nhìn chính:

```txt
UEConnect trở thành social layer đáng tin cậy của đời sống sinh viên HCMUE, nơi sinh viên có thể kết nối đúng người, đúng cộng đồng, đúng mentor và đúng cơ hội trong hành trình đại học.
```

## 3. Product Positioning

UEConnect được định vị là:

```txt
Verified HCMUE Student Social Platform
```

Không phải:

```txt
Dating app
Cổng thông tin sinh viên
Job board
Messenger/Zalo clone
Facebook clone
LinkedIn clone
Discord clone
Landing page giới thiệu trường
```

UEConnect học pattern từ các sản phẩm lớn nhưng không copy bản sắc của chúng:

| Product Reference | Pattern học hỏi                 | Cách áp dụng cho UEConnect                          |
| ----------------- | ------------------------------- | --------------------------------------------------- |
| Threads           | Feed/blogging tối giản          | Home feed content-first                             |
| Facebook          | Comment rõ ràng                 | Discussion và comment thread                        |
| Tinder            | Discovery interaction nhanh     | Khám phá UEers, bỏ dating vibe                      |
| LinkedIn          | Profile/mentor/career structure | Profile đầy đủ nhưng không CV hóa                   |
| Discord           | Community/channel/role thinking | CLB/cộng đồng có quản lý, triển khai sau theo phase |

## 4. Product Promise

Short promise:

```txt
Kết nối và làm quen chuẩn HCMUEr.
```

Expanded promise:

```txt
UEConnect giúp sinh viên HCMUE kết nối bạn cùng khoa, cùng môn, cùng mục tiêu học tập, tìm mentor, tham gia cộng đồng và xây dựng identity sinh viên trong một môi trường đã xác thực.
```

## 5. Target Scope

UEConnect chỉ dành cho HCMUE.

Trong phạm vi hiện tại, hệ thống không thiết kế cho multi-university.

Điều này ảnh hưởng đến:

* Brand identity.
* Verification flow.
* Role model.
* Community/CLB structure.
* Business rules.
* Database schema.
* Admin operation.
* Content policy.

## 6. Core Users

Các nhóm user chính:

| User Group   |      Priority | Description                                               |
| ------------ | ------------: | --------------------------------------------------------- |
| Student      |            P0 | Sinh viên HCMUE, user trung tâm của sản phẩm              |
| Alumni       |            P1 | Cựu sinh viên, có thể chia sẻ kinh nghiệm hoặc làm mentor |
| Advisor      |            P1 | Cố vấn học tập, giảng viên, mentor được xác thực          |
| Admin        | P0 Operations | Quản lý verification, moderation, user, role, audit       |
| Club Manager |            P2 | User được admin grant quyền quản lý CLB/cộng đồng         |
| Guest        |      P0 Entry | Người chưa đăng nhập, cần hiểu sản phẩm và đăng ký        |

## 7. Stakeholders

Stakeholder chính:

```txt
Sinh viên HCMUE
```

Stakeholder triển khai thực tế có thể bao gồm:

```txt
Nhà trường
Khoa
CLB
Cố vấn học tập
Alumni
Admin / moderator
```

Trong tài liệu, cần đặt trường hợp UEConnect có thể được triển khai thật trong môi trường trường học, nên các quyết định về verification, privacy, moderation và audit phải đủ nghiêm túc.

## 8. Core Problems

UEConnect giải quyết các pain point chính:

| ID    | Pain Point                                  |
| ----- | ------------------------------------------- |
| P-001 | Sinh viên khó làm quen bạn mới trong trường |
| P-002 | Sinh viên khó tìm bạn cùng môn/cùng khoa    |
| P-003 | Sinh viên thiếu mentor/cố vấn gần gũi       |
| P-004 | Alumni khó kết nối lại với sinh viên        |
| P-005 | CLB khó truyền thông và quản lý cộng đồng   |
| P-006 | Sinh viên năm nhất khó hòa nhập             |

## 9. Product Pillars

UEConnect gồm 7 product pillars:

| Pillar              | Description                                                     |
| ------------------- | --------------------------------------------------------------- |
| Verified Identity   | Xác thực bằng MSSV, minh chứng sinh viên/alumni/advisor         |
| Social Feed         | Đăng bài, đọc bài, comment, tương tác                           |
| Discovery Profile   | Khám phá UEers, gửi lời chào, kết nối                           |
| Messaging           | Chat 1:1 realtime sau khi có permission phù hợp                 |
| Mentor / Growth     | Tìm mentor, gửi mentor request, định hướng học tập/career       |
| Community / Club    | CLB/cộng đồng có admin approval và role quản lý                 |
| Safety / Moderation | Report, block, keyword flagging, auto-hide, admin action, audit |

## 10. MVP Scope

Các module P0:

```txt
Auth + mã sinh viên verification
Profile setup
Home feed
Create post
Comment
Discovery UEers
Gửi lời chào / connection
Messaging 1:1
Mentor
Community / CLB
Notifications
Settings / privacy
Report / block
Admin verification
Admin moderation
```

## 11. Architecture-ready Future Features

Các feature chưa nhất thiết hoàn thiện trong MVP nhưng cần chuẩn bị kiến trúc:

```txt
Community chat
Realtime notifications
Resource library
Mentor scheduling
```

Các feature này ảnh hưởng đến:

* Database schema.
* Permission model.
* Notification system.
* Realtime infrastructure.
* File/resource policy.
* Moderation pipeline.

## 12. Landing Page Requirement

UEConnect cần public landing page tối giản.

Landing page có nhiệm vụ:

* Giới thiệu UEConnect là gì.
* Giải thích sản phẩm dành cho HCMUE.
* Trình bày lý do cần verified account.
* Dẫn user đến đăng ký/đăng nhập.
* Tạo trust mà không biến thành marketing landing page quá màu mè.

Landing page không phải product UI chính. Product UI vẫn phải neutral-first, content-first.

## 13. Verification Model

UEConnect sử dụng verification nghiêm túc.

### 13.1. Verification Methods

User có thể được xác thực qua:

```txt
Admin duyệt thủ công
Upload minh chứng sinh viên/alumni/advisor
Email trường hcmue.edu.vn nếu có
MSSV uniqueness check
```

### 13.2. Valid Evidence

Các minh chứng hợp lệ có thể gồm:

```txt
Bảng điểm
Giấy nhập học
Bằng tốt nghiệp
Thẻ sinh viên
Email trường
Tài liệu pháp lý số chứng minh danh tính liên quan HCMUE
```

### 13.3. Student ID Uniqueness

Rule bắt buộc:

```txt
Một MSSV chỉ được liên kết với một tài khoản duy nhất.
```

Hệ thống phải ngăn:

```txt
Một MSSV tạo nhiều account
Một user dùng MSSV của người khác
Tài khoản giả mạo sinh viên
Alumni/advisor chưa xác thực truy cập feature cần trust
```

## 14. Role Model

UEConnect dùng mô hình:

```txt
One primary system role + admin-granted permissions
```

### 14.1. Primary System Roles

Mỗi user có một role hệ thống chính:

```txt
Student
Alumni
Advisor
Admin
```

### 14.2. Role Rules

```txt
Student / Alumni / Advisor được xác định theo verification ban đầu.
Student có thể được chuyển thành Alumni theo cơ chế năm học/tốt nghiệp.
Admin là role quản trị đặc biệt.
Một user không tự ý đổi role chính.
```

### 14.3. Admin-granted Permissions

Admin có thể grant thêm quyền:

```txt
Club Manager
Community Moderator
Mentor Permission
Verification Reviewer
Content Moderator
System Admin Permission
```

Role chính thể hiện identity. Permission phụ thể hiện quyền thao tác.

## 15. Community / Club Model

Community/CLB được quản lý bằng cơ chế kiểm duyệt.

Rules:

```txt
Admin có quyền tạo community/CLB chính thức.
User có thể đề xuất community/CLB qua mail hoặc chat riêng.
Club Manager có thể được admin gán quyền tạo/quản lý community.
Community/CLB vẫn cần admin duyệt trước khi public.
Club Manager permission nên phản ánh cơ cấu quản lý thực tế của CLB trong HCMUE.
```

## 16. Safety Policy

Nội dung bị cấm:

```txt
Nội dung vi phạm bản quyền
Không tôn trọng chất xám
Dating / sexual content
Quấy rối
Spam
Giả mạo danh tính
Lộ thông tin cá nhân
Tài liệu vi phạm bản quyền
Ngôn từ công kích
Scam / lừa đảo
Nội dung chính trị nhạy cảm trong môi trường trường học
```

## 17. Report & Moderation Model

Report xử lý theo mô hình kết hợp:

```txt
User report → Admin review → Admin action
Auto-hide nếu nhiều report
Keyword flagging
Manual moderation
Audit log
```

Các moderation action có thể gồm:

```txt
Dismiss report
Hide content
Delete content
Warn user
Suspend user
Ban user
Request more information
Escalate
```

## 18. Audit Requirement

UEConnect cần audit ở 2 lớp:

### 18.1. SQL Server Audit

Dùng để audit server/database-level events:

```txt
DDL changes
Database-level events
Permission changes
Sensitive data access if needed
Security-relevant operations
```

### 18.2. Application Audit Log

Dùng để audit nghiệp vụ:

```txt
Admin approve/reject account
Admin update role/permission
Admin hide/delete content
Admin resolve report
Admin suspend/ban user
User submit report
User upload verification evidence
System auto-hide content
Keyword flagging event
```

Audit log là yêu cầu enterprise quan trọng, không phải optional decoration.

## 19. Success Metrics

UEConnect đo thành công qua:

| Metric                | Meaning                       |
| --------------------- | ----------------------------- |
| Số tài khoản verified | Đo trust adoption             |
| Số bài viết mỗi ngày  | Đo social activity            |
| Số mentor request     | Đo growth/mentor value        |
| Số lời chào được gửi  | Đo discovery/connection value |

Các metric này có thể mở rộng sau:

```txt
Daily active users
7-day retention
30-day retention
Message conversations started
Profile completion rate
Report resolution time
Community active rate
```

## 20. Technical Stack

Stack định hướng:

```txt
Laravel
Blade
TailwindCSS
Vite
MS SQL Server
CSS Modules nếu cần
PWA
```

Recommended supporting services/libraries:

```txt
Laravel Fortify / Starter Kit for authentication foundation
Laravel Sanctum for API/PWA-ready authentication layer
Laravel Reverb for WebSocket realtime features
Laravel Broadcasting for notifications/messages/events
Laravel Queue for async jobs
Laravel Notifications for system/user notifications
SQL Server Audit for database-level audit
Application audit_logs table for business audit
TailwindCSS design tokens for UI consistency
```

## 21. PWA Direction

UEConnect định hướng là PWA.

PWA goals:

```txt
Có thể cài lên thiết bị như app
Hoạt động tốt trên mobile browser
Có app-like navigation
Có offline fallback cơ bản
Có push/realtime notification direction nếu hạ tầng cho phép
Không cần native app trong giai đoạn đầu
```

PWA không thay thế việc thiết kế mobile-first. PWA tệ vẫn chỉ là website đội mũ app.

## 22. Core Constraints

| Constraint                   | Impact                                              |
| ---------------------------- | --------------------------------------------------- |
| Chỉ dành cho HCMUE           | Verification, brand, role, community scope          |
| MSSV unique                  | Database constraint, account recovery, admin review |
| Manual verification          | Cần admin queue, evidence storage, status flow      |
| Safety-sensitive environment | Cần moderation, report, policy, audit               |
| PWA first                    | Cần responsive/mobile-first, installable shell      |
| MS SQL Server                | Cần thiết kế schema và audit phù hợp                |
| Portfolio + real deployment  | Docs phải vừa trình bày tốt vừa thực tế             |

## 23. Out of Scope for Current Vision

Không thuộc tầm nhìn hiện tại:

```txt
Multi-university expansion
Super app sinh viên
Dating feature
Public social network ngoài HCMUE
Job board thuần túy
Native mobile app bắt buộc
Payment system
Marketplace
Anonymous confession
```

## 24. Open Questions

Các câu hỏi cần tiếp tục làm rõ:

```txt
Email hcmue.edu.vn có luôn khả dụng cho mọi student không?
Verification evidence sẽ lưu bao lâu?
Ai trong thực tế có quyền làm admin/moderator?
Cơ chế chuyển Student → Alumni dựa trên năm học hay admin action?
Mentor có phải là role chính hay permission được grant?
Community/CLB official list lấy từ đâu?
Auto-hide threshold là bao nhiêu report?
Keyword flagging dùng rule-based hay AI-assisted?
```

## 25. Final Product Statement

```txt
UEConnect là verified student social platform dành riêng cho HCMUE, giúp sinh viên kết nối, làm quen, đăng bài, khám phá UEers, nhắn tin, tìm mentor và tham gia cộng đồng/CLB trong một môi trường có xác thực danh tính, kiểm duyệt nội dung và audit rõ ràng.
```
