# Authorization & RBAC Specification

## 1. Overview
Hệ thống phân quyền của UEConnect được thiết kế dựa trên mô hình **Role-Based Access Control (RBAC)** kết hợp chặt chẽ với **Account State Gates** (các cổng kiểm soát dựa trên trạng thái tài khoản) để kiểm soát quyền truy cập tài nguyên của người dùng. Tài liệu này quy định chi tiết cách thiết lập, phân chia vai trò, danh mục quyền và cách ánh xạ quyền này vào hệ thống thông qua Laravel Policies, Route Middleware, Livewire Volt Gates, và các quy tắc đặc thù của mạng xã hội nội bộ HCMUE.

---

## 2. Authorization Principles
Hệ thống áp dụng các nguyên lý bảo mật nghiêm ngặt sau để đảm bảo tính an toàn:
1. **Least Privilege (Quyền tối thiểu)**: Tất cả người dùng mặc định chỉ có quyền truy cập tối thiểu cần thiết để thực hiện công việc. Mọi tính năng cốt lõi (social feed, messaging, connection) đều bị chặn trừ khi có quyền cụ thể.
2. **Server-Side Enforcement First**: Việc ẩn/hiện thành phần giao diện (UI) chỉ mang tính chất nâng cao trải nghiệm người dùng (UX). Toàn bộ các hành động thực tế (API, Livewire Actions, Route, Database mutation) **bắt buộc phải được kiểm tra quyền ở phía máy chủ**.
3. **State Overrides Role (Trạng thái phủ quyết vai trò)**: Trạng thái tài khoản (ví dụ: `restricted`, `suspended`, `banned`) luôn có mức ưu tiên cao nhất và có thể vô hiệu hóa mọi quyền năng của vai trò (Role) đó ngay lập tức.
4. **Bản ghi bất biến đối với quyền nhạy cảm**: Mọi thao tác gán vai trò, cấp quyền cho quản trị viên, hoặc thay đổi trạng thái tài khoản đều phải được ghi lại trong Audit Log và không thể xóa sửa.

---

## 3. Account State Gates
Trước khi kiểm tra vai trò (Role), hệ thống chạy qua bộ lọc trạng thái tài khoản (`AccountStateGate`). Một người dùng có thể sở hữu Role cao cấp, nhưng nếu trạng thái tài khoản không hợp lệ, hành động sẽ bị chặn đứng tại tầng Middleware hoặc Policy.

### Bảng Trạng Thái Tài Khoản & Giới Hạn Hành Động
| Trạng Thái | Mô Tả | Quyền Hạn Thực Tế | Giới Hạn Chi Tiết |
| :--- | :--- | :--- | :--- |
| `pending_verification` | Đang đợi xác minh định danh học tập | Chỉ xem trang trạng thái xác minh, thiết lập profile cơ bản | **Không** thể đăng bài, comment, kết nối hay gửi tin nhắn. |
| `verified` | Đã xác minh thành công nhưng chưa kích hoạt | Có thể kích hoạt tài khoản | Đi qua màn hình onboarding và chuyển sang `active`. |
| `active` | Tài khoản đã xác minh và đang hoạt động bình thường | Toàn quyền sử dụng các tính năng được phân theo Role | Truy cập Core Social, Connection, Messaging đầy đủ. |
| `restricted` | Tài khoản bị hạn chế tạm thời do vi phạm nhẹ hoặc đang bị cảnh cáo | Chế độ Đọc (Read-only) hạn chế | Có thể xem feed nhưng **không** thể đăng bài, bình luận, gửi tin nhắn mới hay gửi kết nối. |
| `suspended` | Tài khoản bị đình chỉ do hành vi đáng ngờ (ví dụ: trùng MSSV, bằng chứng giả) | Chặn truy cập Core App | Chỉ hiển thị màn hình thông báo đình chỉ và kháng nghị. |
| `banned` | Tài khoản bị khóa vĩnh viễn | Bị cấm hoàn toàn | Bị đăng xuất, không thể đăng nhập hoặc sử dụng bất kỳ route nào ngoại trừ trang liên hệ hỗ trợ. |
| `deactivated` | Tài khoản tự nguyện tạm khóa bởi người dùng | Tạm khóa dữ liệu | Không xuất hiện trong Discovery, không nhận tin nhắn mới. Cần quy trình re-activate để kích hoạt lại. |

---

## 4. Roles
Mỗi người dùng trong hệ thống UEConnect sở hữu một vai trò chính (Primary Role) được xác định trực tiếp thông qua quy trình xác thực định danh (Verification).

1. **`guest`**: Người dùng chưa đăng nhập hệ thống.
2. **`registered_user`**: Người dùng đã đăng ký tài khoản qua email nhưng chưa hoàn tất hoặc bị từ chối xác minh định danh (chưa có Role chính thức).
3. **`verified_student`**: Sinh viên hiện tại của HCMUE, đã được phê duyệt thẻ sinh viên/MSSV hợp lệ.
4. **`verified_alumni`**: Cựu sinh viên HCMUE, đã xác minh thông tin tốt nghiệp hoặc cựu sinh viên.
5. **`verified_teacher_advisor`**: Giảng viên hoặc cố vấn học tập của trường, có email đuôi giáo viên hoặc tài liệu cố vấn được phê duyệt.
6. **`external_mentor`**: Cố vấn chuyên môn bên ngoài, được mời hoặc phê duyệt bởi admin nhằm hỗ trợ cựu sinh viên và sinh viên.
7. **`moderator`**: Kiểm duyệt viên nội dung, phụ trách xử lý báo cáo vi phạm, ẩn bài viết/comment xấu.
8. **`admin`**: Quản trị viên hệ thống, quản lý người dùng, xử lý phê duyệt định danh, cấu hình các tham số vận hành.
9. **`super_admin`**: Quản trị viên cấp cao nhất, có quyền quản lý vai trò của admin khác, can thiệp cấu hình hệ thống chuyên sâu.
10. **`system`**: Tác nhân hệ thống, thực hiện các tác vụ tự động, cron jobs, background workers, AI analysis.

---

## 5. Permission Catalog
Các quyền trong hệ thống được quản lý tập trung và phân cấp rõ ràng theo các nhóm chức năng cốt lõi:

### Nhóm 1: System & Account
- `view_app`: Quyền truy cập vào giao diện ứng dụng chính (chỉ dành cho tài khoản `active`).
- `manage_own_profile`: Cập nhật thông tin cá nhân, cài đặt riêng tư của bản thân.
- `submit_verification`: Gửi yêu cầu xác minh định danh kèm minh chứng.
- `review_verification`: Xem danh sách và chi tiết các yêu cầu xác minh trong queue.
- `approve_verification`: Chấp thuận yêu cầu định danh của người dùng và gán Role.
- `reject_verification`: Từ chối yêu cầu định danh kèm lý do chi tiết.
- `manage_roles`: Gán vai trò (Role) cho người dùng (chỉ Super Admin).
- `manage_permissions`: Thay đổi hoặc cấp quyền đặc thù cho các vai trò (chỉ Super Admin).

### Nhóm 2: Social Feed & Posts
- `create_post`: Tạo bài viết mới trên feed.
- `update_own_post`: Cập nhật hoặc chỉnh sửa nội dung bài viết do mình đăng.
- `delete_own_post`: Xóa bài viết của bản thân.
- `view_post`: Xem chi tiết bài viết (bao gồm cả bài viết giới hạn nếu thuộc phạm vi chia sẻ).
- `hide_own_feed_post`: Tự ẩn bài viết của mình khỏi feed cá nhân hoặc feed chung.
- `report_content`: Gửi báo cáo vi phạm bài viết hoặc bình luận.
- `moderate_content`: Quyền ẩn, xóa hoặc khôi phục bài viết/bình luận của bất kỳ ai do vi phạm quy chuẩn.
- `manage_reports`: Xem, duyệt và đóng các báo cáo vi phạm trong hàng đợi kiểm duyệt.

### Nhóm 3: Comments
- `create_comment`: Viết bình luận dưới các bài đăng hợp lệ.
- `update_own_comment`: Sửa bình luận của bản thân trong vòng thời gian quy định.
- `delete_own_comment`: Xóa bình luận của bản thân.

### Nhóm 4: Connections
- `send_connection_request`: Gửi lời mời kết nối (Greeting/Connection) tới người khác.
- `manage_own_connections`: Chấp nhận, từ chối, hủy kết nối của bản thân.
- `block_user`: Chặn người dùng khác để ngăn chặn mọi tương tác.

### Nhóm 5: Messaging & Sharing
- `view_conversation`: Xem danh sách hội thoại và nội dung tin nhắn trong phòng chat tham gia.
- `send_message`: Gửi tin nhắn 1-1 trong cuộc trò chuyện hợp lệ.
- `delete_own_message`: Thu hồi/Xóa tin nhắn ở phía mình hoặc cho cả hai bên (thu hồi).
- `share_post_to_message`: Chia sẻ liên kết bài đăng trực tiếp vào tin nhắn 1-1.

### Nhóm 6: Administrative Operations
- `view_admin_dashboard`: Xem tổng quan số liệu thống kê tại trang quản trị.
- `view_audit_logs`: Truy cập và tìm kiếm nhật ký hoạt động hệ thống nâng cao.

---

## 6. Role-Permission Matrix
Bảng ma trận ánh xạ vai trò và quyền hạn chi tiết trong hệ thống UEConnect:

| Permission | Guest | Registered (Pending) | Verified Student | Verified Alumni | Teacher Advisor | External Mentor | Moderator | Admin | Super Admin |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `view_app` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manage_own_profile`| ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `submit_verification`| ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `review_verification`| ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| `approve_verification`| ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| `reject_verification`| ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| `manage_roles` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `create_post` | ❌ | ❌ | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ✅ |
| `update_own_post` | ❌ | ❌ | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ✅ |
| `delete_own_post` | ❌ | ❌ | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ✅ |
| `report_content` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `moderate_content` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| `manage_reports` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| `create_comment` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `send_connection` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `send_message` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `share_post_to_msg` | ❌ | ❌ | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ✅ |
| `view_admin_dash` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| `view_audit_logs` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

*Chú thích:*
- ✅: Được phép hoàn toàn (Allowed).
- ❌: Bị từ chối hoàn toàn (Denied).
- ⚠️: Có điều kiện (Conditional) - ví dụ: Mentor chỉ được đăng bài trong các forum định hướng nghề nghiệp, không đăng trên Home Feed tự do; post sharing bị giới hạn bởi quyền hiển thị gốc.

---

## 7. Feature Access Matrix
Quy định mức độ tiếp cận các phân hệ chức năng dựa trên sự kết hợp giữa **Role** và **Account State**:

| Feature Surface | Guest | Registered (Pending) | Verified (Active) | Restricted User | Suspended User | Banned User |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Landing & Auth Pages** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Verification Submit Portal**| ❌ | ✅ | ❌ (Đã xác minh)| ❌ | ❌ | ❌ |
| **Social Home Feed (Read)** | ❌ | ❌ | ✅ | ✅ (Bị giới hạn) | ❌ | ❌ |
| **Social Feed (Create/React)**| ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Comments (Read)** | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Comments (Write/Edit)** | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Connection (View List)** | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Connection (Send Request)** | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Messaging 1-1 (Send)** | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Messaging 1-1 (Read)** | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Admin Operations Panel** | ❌ | ❌ | ⚠️ (Chỉ Admin)| ❌ | ❌ | ❌ |

---

## 8. Policy Mapping
Hệ thống sử dụng các Laravel Policy để cô lập và xử lý logic kiểm tra quyền cho từng Model cụ thể. Mỗi class Policy bắt buộc kiểm tra cả vai trò người dùng lẫn trạng thái tài khoản.

### 1. `UserPolicy`
- `view`: Kiểm tra xem user hiện tại có quyền xem thông tin chi tiết user khác không.
- `update`: Người dùng tự cập nhật tài khoản của mình.
- `manage`: Admin quản lý trạng thái tài khoản người dùng (`suspended`, `restricted`).

### 2. `ProfilePolicy`
- `view`: Người dùng đang `active` hoặc `restricted` có thể xem profile của người khác (trừ khi bị chặn).
- `update`: Chỉ chủ sở hữu profile mới có quyền sửa đổi.

### 3. `VerificationRequestPolicy`
- `create` / `submit`: Người dùng phải ở trạng thái `registered_user` và chưa có request nào đang `pending` hoặc `approved`.
- `view`: Chỉ chủ sở hữu request hoặc người dùng có quyền `review_verification` được xem.
- `update`: Chủ sở hữu sửa đổi khi trạng thái là `need_more_information`.
- `approve` / `reject` / `conflict`: Phải có quyền `review_verification` và trạng thái tài khoản admin là `active`.

### 4. `VerificationEvidencePolicy`
- `view` / `download`: Nghiêm cấm tuyệt đối truy cập công khai. Chỉ cho phép chủ sở hữu minh chứng hoặc Admin đang trực tiếp đánh giá yêu cầu đó truy cập (thông qua route bảo mật chứa kiểm tra phiên làm việc).

### 5. `PostPolicy`
- `view`: Chỉ cho phép khi bài đăng ở trạng thái `active` và người xem không bị tác giả chặn (hoặc ngược lại). Nếu là bài đăng giới hạn (như `connections_only`), người xem phải là bạn bè đã kết nối.
- `create`: Yêu cầu quyền `create_post` và trạng thái tài khoản là `active`.
- `update` / `delete`: Chỉ cho phép chủ sở hữu bài đăng (`author_id == user_id`) sửa/xóa trong vòng thời gian quy định (ví dụ: tối đa 60 phút để sửa).
- `moderate`: Yêu cầu quyền `moderate_content`.

### 6. `CommentPolicy`
- `create`: Người dùng `active` và có quyền xem bài đăng cha.
- `update` / `delete`: Chỉ chủ sở hữu comment hoặc tác giả bài đăng cha (đối với hành động xóa).
- `moderate`: Kiểm duyệt viên ẩn comment vi phạm.

### 7. `ReportPolicy`
- `create`: Người dùng báo cáo nội dung của người khác (nghiêm cấm tự báo cáo nội dung của bản thân). Trạng thái tài khoản phải là `active`.
- `manage`: Quyền `manage_reports` để xử lý các báo cáo vi phạm.

### 8. `ConnectionPolicy`
- `send`: Người gửi và người nhận đều phải ở trạng thái `active` và chưa có quan hệ kết nối hay chặn nhau nào trước đó.
- `accept` / `decline` / `cancel`: Chỉ người trong cuộc mới có quyền thực hiện.

### 9. `ConversationPolicy`
- `view`: Người dùng phải là một trong hai bên tham gia trực tiếp cuộc trò chuyện (`participant_1` hoặc `participant_2`).
- `share`: Yêu cầu cả hai người dùng vẫn đang duy trì quan hệ kết nối hợp lệ.

### 10. `MessagePolicy`
- `send`: Cả hai người dùng phải là bạn bè (đã kết nối), tài khoản `active` và không chặn nhau.
- `delete`: Chỉ người gửi mới được thu hồi tin nhắn của chính họ.

### 11. `BlockPolicy`
- `create` / `delete`: Cho phép người dùng chặn hoặc bỏ chặn bất kỳ ai (trừ tài khoản Admin/System).

### 12. `AuditLogPolicy`
- `view`: Chỉ cho phép Super Admin hoặc Admin có quyền `view_audit_logs` truy cập dữ liệu nhật ký hệ thống.

---

## 9. Route Middleware Mapping
Để ngăn chặn các truy cập trái phép ngay từ lớp định tuyến (Routing), hệ thống áp dụng các Laravel Middleware tiêu chuẩn sau:

```php
// routes/web.php hoặc routes/api.php

// 1. Bảo vệ toàn bộ Core App - Yêu cầu đăng nhập và tài khoản ở trạng thái ACTIVE
Route::middleware(['auth', 'verified.active'])->group(function () {
    Route::get('/app/home', [HomeController::class, 'index'])->name('home');
    Route::resource('posts', PostController::class)->except(['index', 'show']);
    Route::resource('comments', CommentController::class)->only(['store', 'update', 'destroy']);
    Route::post('/connections/request', [ConnectionController::class, 'sendRequest'])->name('connections.request');
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');
});

// 2. Phân hệ định danh - Dành riêng cho tài khoản cần xác minh
Route::middleware(['auth', 'verified.pending'])->group(function () {
    Route::get('/verification/status', [VerificationController::class, 'status'])->name('verification.status');
    Route::get('/verification/setup', [VerificationController::class, 'setup'])->name('verification.setup');
    Route::post('/verification/submit', [VerificationController::class, 'submit'])->name('verification.submit');
});

// 3. Phân hệ quản trị và kiểm duyệt - Phân quyền dựa trên Roles và Permissions
Route::middleware(['auth', 'role:admin|moderator|super_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Yêu cầu quyền cụ thể qua middleware của Spatie Permission
    Route::middleware(['can:review_verification'])->group(function () {
        Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('admin.verifications.index');
        Route::post('/verifications/{id}/approve', [AdminVerificationController::class, 'approve']);
        Route::post('/verifications/{id}/reject', [AdminVerificationController::class, 'reject']);
    });
    
    Route::middleware(['can:moderate_content'])->group(function () {
        Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::post('/posts/{id}/hide', [AdminModerationController::class, 'hidePost']);
    });
});
```

---

## 10. Livewire / Volt Authorization Rules
Do hệ thống sử dụng Livewire Volt (Single File Components), việc bảo vệ các Action bất đồng bộ phía Client cực kỳ quan trọng. Mọi action trong file Volt phải tuân thủ việc kiểm tra quyền trước khi thực thi logic:

```php
use function Livewire\Volt\{state, rules, protect};
use App\Models\Post;

state(['body' => '']);

// Đảm bảo action 'savePost' được bảo vệ bằng middleware
$savePost = protect(function () {
    // 1. Kiểm tra Gate chung của tài khoản active
    if (! auth()->user()->isActive()) {
        abort(403, 'Tài khoản của bạn hiện đang bị hạn chế hoặc chưa xác minh.');
    }

    // 2. Chạy validation
    $this->validate([
        'body' => 'required|max:500',
    ]);

    // 3. Thực thi lưu trữ
    auth()->user()->posts()->create([
        'body' => $this->body,
        'status' => 'active'
    ]);

    $this->dispatch('post-created');
});
```

---

## 11. Admin / Moderator Rules
1. **Không can thiệp hội thoại**: Tuyệt đối không cấp quyền mặc định cho Admin hoặc Moderator vào đọc nội dung tin nhắn 1-1 của người dùng thông qua giao diện quản trị, trừ trường hợp có yêu cầu điều tra đặc biệt kèm mã phiên được hệ thống ghi log bảo mật cấp cao.
2. **Quy trình hai bước đối với tài khoản bị khóa**: Moderator chỉ có quyền ẩn bài đăng hoặc bình luận và gửi yêu cầu khóa tài khoản lên hệ thống. Quyền đình chỉ (`suspend`) hoặc cấm (`ban`) tài khoản người dùng thuộc về cấp độ Admin trở lên.
3. **Audit bắt buộc**: Mọi hành động kiểm duyệt của Admin/Moderator đều bắt buộc nhập lý do (Reason) hệ thống mới cho phép thực thi, lý do này sẽ lưu thẳng vào cơ sở dữ liệu Audit Log.

---

## 12. Verification-Specific Authorization
Quy trình định danh sinh viên/cựu sinh viên/cố vấn yêu cầu bảo vệ quyền riêng tư tuyệt đối:
- **Người nộp hồ sơ**: Chỉ được quyền `create` (nộp mới) và `update` (khi trạng thái là `need_more_information`). Không được quyền xem hoặc thay đổi dữ liệu của người khác.
- **Quyền xem minh chứng (Evidence)**: Minh chứng dạng hình ảnh thẻ sinh viên hay bảng điểm chỉ được tải lên ổ đĩa `private`. File này không có đường dẫn trực tiếp (symlink) ra thư mục public. Route truy xuất file bắt buộc kiểm tra quyền của Admin thông qua Gate `review_verification` và chỉ tồn tại tạm thời trong phiên làm việc.

---

## 13. Social Feed Authorization
Quy tắc hiển thị bài đăng trên Home Feed được thực thi chặt chẽ ở lớp Database Query:
- **Bắt buộc hoạt động**: Chỉ lấy các bài viết có `status = 'active'` từ các tác giả có trạng thái tài khoản là `active` hoặc `verified`.
- **Loại trừ chặn hai chiều**: Hệ thống tự động loại bỏ mọi bài viết từ những người dùng nằm trong danh sách chặn (`blocklist`) của người xem hiện tại, và ngược lại (người xem nằm trong danh sách chặn của tác giả).
- **Quyền riêng tư bài viết**: Nếu bài đăng thiết lập chế độ hiển thị `connections_only` (chỉ bạn bè), hệ thống sẽ so khớp mối quan hệ kết nối giữa người xem và tác giả trước khi trả về bản ghi.

---

## 14. Comment Authorization
- **Quyền bình luận**: Chỉ dành cho người dùng `active` và đối với các bài viết mà người đó được quyền xem hợp lệ.
- **Quyền chỉnh sửa/xóa**:
  - Người dùng chỉ được sửa bình luận của chính mình.
  - Người dùng được quyền xóa bình luận của chính mình.
  - **Tác giả bài đăng gốc** có quyền xóa bất kỳ bình luận nào bên dưới bài viết của họ (quyền sở hữu không gian thảo luận cá nhân).
  - Moderator/Admin có quyền ẩn/xóa bình luận dựa trên quyền `moderate_content`.

---

## 15. Connection Authorization
- **Kết nối 1-1**: Hai người dùng chỉ có thể gửi lời mời kết nối nếu cả hai đều ở trạng thái `active`, không có yêu cầu nào đang chờ xử lý giữa hai bên, và không có quan hệ chặn (block).
- **Hủy kết nối**: Bất kỳ bên nào cũng có quyền đơn phương hủy kết nối bất kỳ lúc nào mà không cần sự đồng ý của bên còn lại. Khi hủy kết nối, mọi quyền xem nội dung giới hạn bạn bè (`connections_only`) hoặc quyền gửi tin nhắn lập tức bị chấm dứt.

---

## 16. Messaging Authorization
Để ngăn chặn hành vi quấy rối, phân hệ nhắn tin 1-1 áp dụng các quy tắc nghiêm ngặt:
- **Điều kiện nhắn tin**: Chỉ cho phép nhắn tin khi và chỉ khi hai người dùng **đã hoàn tất kết nối** (trạng thái kết nối là `connected` hoặc `accepted`), cả hai đều đang hoạt động bình thường (`active`), và không chặn nhau.
- **Chặn tức thời**: Ngay khi một bên nhấn Chặn (`block`), quyền gửi tin nhắn của cả hai bên lập tức bị vô hiệu hóa. Phía người bị chặn sẽ nhận mã lỗi hoặc thông báo không thể gửi tin nhắn.
- **Hủy kết nối**: Khi hủy kết nối bạn bè, phòng chat hiện tại sẽ tự động chuyển sang chế độ **Đọc (Read-only)**. Người dùng có thể xem lại lịch sử tin nhắn cũ nhưng không thể gửi tin nhắn mới trừ khi kết nối lại thành công.

---

## 17. Post Sharing Authorization
Khi người dùng chia sẻ một bài viết qua tin nhắn:
- **Quyền chia sẻ**: Người gửi bắt buộc phải có quyền xem bài viết gốc đó tại thời điểm chia sẻ.
- **Quyền hiển thị phía người nhận**: Người nhận tin nhắn chỉ có thể xem nội dung xem trước (preview) hoặc nhấp vào xem chi tiết bài đăng đó nếu họ cũng có quyền xem bài viết đó (tức là họ không bị tác giả bài viết chặn, bài viết không ở chế độ chỉ bạn bè mà người nhận không phải bạn bè của tác giả).
- **Trạng thái bài đăng gốc**: Nếu bài viết gốc bị ẩn, xóa hoặc bị kiểm duyệt viên khóa lại, giao diện tin nhắn chung sẽ tự động hiển thị trạng thái "Nội dung không hiển thị" hoặc "Bài viết đã bị xóa" thay thế cho nội dung thô ban đầu để tránh rò rỉ thông tin.

---

## 18. Blocking Rules
Tính năng chặn (`Block`) có tính chất cưỡng chế tuyệt đối trên toàn hệ thống:
- **Chặn hai chiều (Two-way exclusion)**: Ngay khi User A chặn User B:
  - User A không thể nhìn thấy bài đăng, comment, profile hay tin nhắn của User B.
  - User B không thể nhìn thấy bất kỳ dấu vết nào của User A trên hệ thống.
  - Toàn bộ kết nối bạn bè hiện tại (nếu có) bị hủy vĩnh viễn và không thể khôi phục tự động ngay cả khi bỏ chặn.
- **Chặn ở tầng API**: Mọi API tìm kiếm, danh sách kết nối, gợi ý bạn bè, feed query bắt buộc phải lồng thêm điều kiện loại trừ: `whereNotExists` trên bảng `blocks` cho cả hai chiều `blocker_id` và `blocked_id`.

---

## 19. Abuse / Rate Limit Integration
Hệ thống tích hợp kiểm tra tần suất hành động để ngăn chặn việc lạm dụng quyền hạn:
- **Kiểm tra Rate Limit ở tầng Policy**: Trước khi trả về kết quả cho phép thực hiện hành động, Policy sẽ gọi dịch vụ Rate Limiting để kiểm tra xem người dùng có đang spam hành động đó hay không (ví dụ: gửi 50 yêu cầu kết nối liên tục). Nếu vượt ngưỡng, Policy sẽ trả về kết quả phủ quyết kèm mã lỗi lạm dụng quyền.
- **Hạn chế tự động**: Khi hệ thống phát hiện hành vi lạm dụng liên tục, trạng thái tài khoản sẽ tự động chuyển đổi từ `active` sang `restricted` trong thời gian ngắn (ví dụ: 24 giờ) mà không cần sự can thiệp thủ công của Admin.

---

## 20. Testing Requirements
Mọi quy tắc phân quyền bắt buộc phải được kiểm thử tự động thông qua PHPUnit Feature Tests.
- **Test Happy Path**: Đảm bảo người dùng `verified` + `active` thực hiện đầy đủ các tính năng social bình thường.
- **Test Boundary / Security Breach**:
  - Test việc người dùng ở trạng thái `pending_verification` cố tình gọi API tạo bài đăng hoặc gửi tin nhắn để đảm bảo máy chủ từ chối với mã lỗi `403 Forbidden`.
  - Test việc gửi tin nhắn khi đã chặn nhau để đảm bảo trả về lỗi phân quyền.
  - Test việc truy xuất hình ảnh thẻ sinh viên bằng tài khoản thường để đảm bảo không bị rò rỉ dữ liệu.
- **Test Concurrency**: Đảm bảo hai Admin không thể phê duyệt cùng một MSSV trùng lặp đồng thời nhờ cơ chế khóa giao dịch (`database transactions` + `lockForUpdate`).

---

## 21. Implementation Notes
- **Sử dụng Laravel Spatie Permission**: Hệ thống tận dụng gói thư viện Spatie để quản lý vai trò và quyền hạn, lưu trữ trực tiếp vào các bảng `roles`, `permissions`, `model_has_roles`, `model_has_permissions`.
- **Cơ chế Caching**: Danh mục quyền của người dùng được cache lại trong Redis/Memcached với thời gian hết hạn ngắn hoặc tự động xóa cache (clear cache triggers) khi vai trò của người dùng thay đổi, tránh việc truy vấn cơ sở dữ liệu lặp lại trên mỗi request.
- **Kiểm tra tính thống nhất**: Định kỳ chạy lệnh phân tích mã nguồn (`vendor/bin/pint`) và chạy bộ kiểm thử (`php artisan test`) để đảm bảo không có lỗ hổng phân quyền nào mới phát sinh trong quá trình phát triển các tính năng social tiếp theo.