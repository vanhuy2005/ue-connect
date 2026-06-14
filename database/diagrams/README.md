# Tài liệu Cấu trúc Cơ sở dữ liệu UE Connect

Tài liệu này giải thích chi tiết cấu trúc cơ sở dữ liệu của dự án UE Connect gồm 108 bảng, được phân nhóm theo các phân hệ chức năng tương ứng. Mỗi phân hệ đi kèm một liên kết dẫn tới file sơ đồ cấu trúc trực quan (định dạng Mermaid `.mmd`).

## Danh sách các phân hệ chức năng

* [Authentication & User Profiles (Xác thực & Hồ sơ cá nhân)](#auth-user)
* [Social Feed & Content Interactions (Bảng tin & Tương tác bài đăng)](#social-feed)
* [Realtime Chat & Messaging (Tin nhắn & Trò chuyện trực tuyến)](#messaging)
* [Verification & System Governance (Kiểm duyệt & Quản trị hệ thống)](#verification-admin)
* [Communities, Suggestions & Events (Cộng đồng & Sự kiện nhóm)](#communities)
* [Academic Programs & Faculty Structure (Khung đào tạo & Khoa chuyên môn)](#academic)
* [Mentorship & Advising (Hệ thống Cố vấn & Mentor)](#mentorship)
* [AI Chatbot & Document Search (AI Chatbot & Tra cứu thông tin)](#ai-chatbot)
* [Career Development & Pathways (Lộ trình Hướng nghiệp & Kỹ năng)](#career)
* [System & Job Queues (Hệ thống & Hàng đợi công việc)](#system)
* [Sơ đồ toàn thể cơ sở dữ liệu (Master Schema)](original/schema.mmd)

---

## <a id="auth_user"></a>Authentication & User Profiles (Xác thực & Hồ sơ cá nhân)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/auth_user.mmd) | [Bản gốc (Đầy đủ)](original/auth_user.mmd)

### Bảng `USERS`
**Mô tả**: Lưu trữ thông tin tài khoản người dùng cốt lõi trong hệ thống.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `email` | `string` | - | Required | Địa chỉ email |
| `email_verified_at` | `timestamp` | - | Nullable | Địa chỉ email |
| `password` | `string` | - | Required | Mật khẩu bảo mật đã mã hóa |
| `remember_token` | `string` | - | Required | Token lưu trạng thái ghi nhớ đăng nhập |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `account_status` | `string` | - | Required | Trạng thái hoạt động |
| `account_status_reason` | `text` | - | Nullable | Trạng thái hoạt động |
| `account_restricted_until` | `timestamp` | - | Nullable | Số lượng / Bộ đếm tích lũy |
| `last_login_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `intended_identity_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `last_seen_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |

### Bảng `PASSWORD_RESET_TOKENS`
**Mô tả**: Lưu các token khôi phục mật khẩu của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `email` | `string` | `PK` | Required | Địa chỉ email |
| `token` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin token |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |

### Bảng `SESSIONS`
**Mô tả**: Lưu phiên làm việc (session) của người dùng truy cập.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `string` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `ip_address` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin ip_address |
| `user_agent` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin user_agent |
| `payload` | `longText` | - | Required | Trường dữ liệu lưu trữ thông tin payload |
| `last_activity` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin last_activity |

### Bảng `USER_IDENTITY_PROVIDERS`
**Mô tả**: Lưu liên kết mạng xã hội hoặc nhà cung cấp định danh bên ngoài (Social Login).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `provider_name` | `string` | - | Required | Tên hiển thị |
| `provider_user_id` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin provider_user_id |
| `provider_tenant_id` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin provider_tenant_id |
| `provider_email` | `string` | - | Nullable | Địa chỉ email |
| `linked_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `last_login_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `PROFILES`
**Mô tả**: Hồ sơ thông tin cơ bản của người dùng (tên hiển thị, avatar, bio, vai trò).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `display_name` | `string` | - | Required | Tên hiển thị |
| `avatar_media_file_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MEDIA_FILES` |
| `bio` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin bio |
| `role_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `profile_status` | `string` | - | Required | Trạng thái hoạt động |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `discoverable` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin discoverable |
| `profile_completed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `STUDENT_PROFILES`
**Mô tả**: Thông tin chi tiết dành cho tài khoản vai trò Sinh viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `profile_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `PROFILES` |
| `student_code` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin student_code |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `academic_program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `ACADEMIC_PROGRAMS` |
| `cohort` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cohort |
| `current_year` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin current_year |
| `class_name` | `string` | - | Nullable | Tên hiển thị |
| `learning_goals` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin learning_goals |
| `career_orientation` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin career_orientation |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `ALUMNI_PROFILES`
**Mô tả**: Thông tin chi tiết dành cho tài khoản vai trò Cựu sinh viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `profile_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `PROFILES` |
| `faculty_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `academic_program_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `ACADEMIC_PROGRAMS` |
| `cohort` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cohort |
| `graduation_year` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin graduation_year |
| `current_position` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin current_position |
| `current_organization` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin current_organization |
| `industry` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin industry |
| `career_summary` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin career_summary |
| `willing_to_mentor` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin willing_to_mentor |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `ADVISOR_PROFILES`
**Mô tả**: Thông tin chi tiết dành cho tài khoản vai trò Cố vấn học tập / Giảng viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `profile_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `PROFILES` |
| `faculty_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `department` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin department |
| `title` | `string` | - | Nullable | Tiêu đề |
| `office_location` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin office_location |
| `advising_areas` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin advising_areas |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `is_academic_advisor` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_academic_advisor |
| `advised_class_codes` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin advised_class_codes |

### Bảng `BLOCKED_USERS`
**Mô tả**: Danh sách chặn người dùng tương tác.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `blocker_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `blocked_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin reason |
| `source_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `source_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_id |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `USER_FOLLOWS`
**Mô tả**: Mối quan hệ theo dõi (Follow) giữa các người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `follower_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `following_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `TEMPORARY_AVATARS`
**Mô tả**: Lưu ảnh đại diện tạm thời đang chờ xử lý.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `previous_media_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MEDIA` |
| `current_media_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MEDIA` |
| `expires_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `PROFILE_PRIVACY_SETTINGS`
**Mô tả**: Thiết lập quyền riêng tư hiển thị thông tin hồ sơ.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `profile_visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin profile_visibility |
| `show_major` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_major |
| `show_cohort` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_cohort |
| `show_class_code` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_class_code |
| `show_bio` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_bio |
| `show_interests` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_interests |
| `show_connection_goals` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_connection_goals |
| `show_communities` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_communities |
| `show_career_info` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_career_info |
| `show_mentor_topics` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin show_mentor_topics |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `mentions_preference` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin mentions_preference |

### Bảng `NOTIFICATION_PREFERENCES`
**Mô tả**: Thiết lập nhận thông báo (email, push, in-app) của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `in_app_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin in_app_enabled |
| `browser_push_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin browser_push_enabled |
| `email_enabled` | `boolean` | - | Required | Địa chỉ email |
| `greeting_notifications` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin greeting_notifications |
| `message_notifications` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin message_notifications |
| `mentor_notifications` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin mentor_notifications |
| `community_notifications` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin community_notifications |
| `safety_notifications` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin safety_notifications |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `push_messages_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_messages_enabled |
| `push_greetings_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_greetings_enabled |
| `push_mentor_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_mentor_enabled |
| `push_community_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_community_enabled |
| `push_verification_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_verification_enabled |
| `push_admin_announcements_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_admin_announcements_enabled |
| `push_mentions` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_mentions |
| `push_comments` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_comments |
| `push_connections` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_connections |
| `push_messages` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_messages |
| `push_system` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin push_system |
| `email_mentions` | `boolean` | - | Required | Địa chỉ email |
| `email_comments` | `boolean` | - | Required | Địa chỉ email |
| `email_connections` | `boolean` | - | Required | Địa chỉ email |
| `email_messages` | `boolean` | - | Required | Địa chỉ email |
| `email_system` | `boolean` | - | Required | Địa chỉ email |
| `email_marketing` | `boolean` | - | Required | Địa chỉ email |

### Bảng `USER_CONTENT_PREFERENCES`
**Mô tả**: Tùy chọn hiển thị nội dung trên bảng tin của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `prioritize_academic_content` | `boolean` | - | Required | Nội dung chi tiết |
| `hide_reported_content` | `boolean` | - | Required | Nội dung chi tiết |
| `reduce_sensitive_content` | `boolean` | - | Required | Nội dung chi tiết |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `PUSH_SUBSCRIPTIONS`
**Mô tả**: Đăng ký nhận thông báo đẩy trên trình duyệt.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `endpoint` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin endpoint |
| `public_key` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin public_key |
| `auth_token` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin auth_token |
| `content_encoding` | `string` | - | Required | Nội dung chi tiết |
| `user_agent` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin user_agent |
| `browser_name` | `string` | - | Nullable | Tên hiển thị |
| `device_name` | `string` | - | Nullable | Tên hiển thị |
| `last_used_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `failed_attempts` | `unsignedInteger` | - | Required | Trường dữ liệu lưu trữ thông tin failed_attempts |
| `revoked_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="social_feed"></a>Social Feed & Content Interactions (Bảng tin & Tương tác bài đăng)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/social_feed.mmd) | [Bản gốc (Đầy đủ)](original/social_feed.mmd)

### Bảng `POSTS`
**Mô tả**: Lưu trữ các bài đăng chia sẻ của người dùng trên bảng tin.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `body` | `text` | - | Required | Nội dung chi tiết |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `edited_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `published_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `media_url` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `scope_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin scope_id |
| `pinned_by` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |

### Bảng `COMMENTS`
**Mô tả**: Lưu bình luận dưới các bài đăng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `post_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `parent_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `COMMENTS` |
| `body` | `text` | - | Required | Nội dung chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `edited_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `POST_LIKES`
**Mô tả**: Lưu thông tin lượt thích bài đăng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `post_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `POST_SAVES`
**Mô tả**: Lưu thông tin lượt lưu bài đăng của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `post_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `COMMENT_LIKES`
**Mô tả**: Lưu lượt thích các bình luận.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `comment_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `COMMENTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `REPORTS`
**Mô tả**: Lưu trữ báo cáo vi phạm bài đăng, bình luận hoặc người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `reporter_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `target_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `target_id` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin target_id |
| `reason` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin reason |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `POST_HIDES`
**Mô tả**: Danh sách bài đăng bị ẩn đi bởi người dùng cụ thể.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `post_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `POST_REPOSTS`
**Mô tả**: Lưu trữ thông tin chia sẻ lại bài đăng (repost).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `post_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `OPPORTUNITIES`
**Mô tả**: Bảng lưu trữ thông tin cơ hội nghề nghiệp đi kèm bài đăng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `post_id` | `foreignId` | `PK`, `FK` | Required | Khóa ngoại liên kết đến bảng `POSTS` |
| `is_expired` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_expired |
| `category` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin category |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MEDIA`
**Mô tả**: Quản lý thực thể media dùng cho các mô hình đa phương tiện mới.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `uuid` | `uuid` | - | Required | Trường dữ liệu lưu trữ thông tin uuid |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `mediable` | `nullableMorphs` | - | Required | Trường dữ liệu lưu trữ thông tin mediable |
| `primary_provider` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin primary_provider |
| `primary_disk` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin primary_disk |
| `primary_path` | `string` | - | Required | Đường dẫn lưu trữ hoặc URL |
| `delivery_provider` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin delivery_provider |
| `delivery_url` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `storage_strategy` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin storage_strategy |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `original_filename` | `string` | - | Required | Tên hiển thị |
| `mime_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `extension` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin extension |
| `size_bytes` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin size_bytes |
| `width` | `unsignedInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin width |
| `height` | `unsignedInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin height |
| `checksum_sha256` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin checksum_sha256 |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `MEDIA_VARIANTS`
**Mô tả**: Các biến thể phiên bản kích thước/định dạng khác nhau của tệp media.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `media_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MEDIA` |
| `variant_name` | `string` | - | Required | Tên hiển thị |
| `provider` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin provider |
| `disk` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin disk |
| `path` | `string` | - | Required | Đường dẫn lưu trữ hoặc URL |
| `url` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `mime_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `size_bytes` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin size_bytes |
| `width` | `unsignedInteger` | - | Required | Trường dữ liệu lưu trữ thông tin width |
| `height` | `unsignedInteger` | - | Required | Trường dữ liệu lưu trữ thông tin height |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `cloudinary_public_id` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_public_id |
| `cloudinary_version` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_version |
| `cloudinary_secure_url` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `cloudinary_format` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_format |
| `cloudinary_bytes` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_bytes |
| `cloudinary_resource_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `cloudinary_synced_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `cloudinary_sync_status` | `string` | - | Required | Trạng thái hoạt động |
| `cloudinary_error_code` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_error_code |
| `cloudinary_error_message` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cloudinary_error_message |

### Bảng `MEDIA_FILES`
**Mô tả**: Quản lý thông tin tệp tin phương tiện tải lên hệ thống.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `owner_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `disk` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin disk |
| `path` | `string` | - | Required | Đường dẫn lưu trữ hoặc URL |
| `original_name` | `string` | - | Required | Tên hiển thị |
| `mime_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `extension` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin extension |
| `size_bytes` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin size_bytes |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `file_category` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin file_category |
| `checksum` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin checksum |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

---

## <a id="messaging"></a>Realtime Chat & Messaging (Tin nhắn & Trò chuyện trực tuyến)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/messaging.mmd) | [Bản gốc (Đầy đủ)](original/messaging.mmd)

### Bảng `CONVERSATIONS`
**Mô tả**: Phiên hội thoại chat giữa các thành viên hoặc nhóm.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `conversation_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `direct_user_low_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `direct_user_high_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `last_message_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin last_message_id |
| `last_message_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `mentor_request_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin mentor_request_id |
| `source_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_id |

### Bảng `CONVERSATION_PARTICIPANTS`
**Mô tả**: Thành viên tham gia phiên hội thoại chat.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `conversation_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CONVERSATIONS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `participant_role` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin participant_role |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `joined_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `left_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `last_read_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `muted_until` | `timestamp` | - | Nullable | Trường dữ liệu lưu trữ thông tin muted_until |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MESSAGES`
**Mô tả**: Nội dung tin nhắn trong các cuộc hội thoại.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `conversation_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CONVERSATIONS` |
| `sender_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `body` | `text` | - | Nullable | Nội dung chi tiết |
| `message_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `shared_post_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `POSTS` |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `edited_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `reply_to_message_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MESSAGES` |
| `forwarded_from_message_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MESSAGES` |
| `recalled_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `recalled_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |

### Bảng `CONVERSATION_USER_SETTINGS`
**Mô tả**: Cài đặt cá nhân của người dùng cho từng cuộc hội thoại (biệt danh, tắt thông báo).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `conversation_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CONVERSATIONS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `target_user_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `nickname` | `string` | - | Nullable | Tên hiển thị |
| `muted_until` | `timestamp` | - | Nullable | Trường dữ liệu lưu trữ thông tin muted_until |
| `is_restricted` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_restricted |
| `deleted_at` | `timestamp` | - | Nullable | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CONVERSATION_PINNED_MESSAGES`
**Mô tả**: Các tin nhắn được ghim trong cuộc hội thoại.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `conversation_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CONVERSATIONS` |
| `message_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MESSAGES` |
| `pinned_by` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `GREETINGS`
**Mô tả**: Lời chào kết nối gửi giữa các thành viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `sender_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `receiver_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `message` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin message |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `decline_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin decline_reason |
| `accepted_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `declined_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `expires_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CONNECTIONS`
**Mô tả**: Danh sách kết nối bạn bè/mạng lưới đã thiết lập thành công.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_one_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `user_two_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `source_greeting_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `GREETINGS` |
| `connected_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `disconnected_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="verification_admin"></a>Verification & System Governance (Kiểm duyệt & Quản trị hệ thống)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/verification_admin.mmd) | [Bản gốc (Đầy đủ)](original/verification_admin.mmd)

### Bảng `VERIFICATION_REQUESTS`
**Mô tả**: Danh sách các yêu cầu xác thực danh tính từ người dùng gửi lên Admin.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `role_requested` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin role_requested |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `submitted_name` | `string` | - | Required | Tên hiển thị |
| `submitted_student_code` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_student_code |
| `submitted_faculty_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `submitted_academic_program_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `ACADEMIC_PROGRAMS` |
| `submitted_cohort` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_cohort |
| `submitted_email` | `string` | - | Required | Địa chỉ email |
| `submitted_note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_note |
| `assigned_admin_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `submitted_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `reviewed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `expires_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `requested_identity_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `submitted_graduation_year` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_graduation_year |
| `submitted_old_student_email` | `string` | - | Nullable | Địa chỉ email |
| `submitted_position` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_position |
| `submitted_organization` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_organization |
| `review_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_reason |
| `review_instruction` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_instruction |
| `submitted_is_academic_advisor` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin submitted_is_academic_advisor |
| `submitted_advised_class_codes` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin submitted_advised_class_codes |

### Bảng `VERIFICATION_EVIDENCES`
**Mô tả**: Minh chứng đi kèm yêu cầu xác thực (ảnh thẻ sinh viên, quyết định, v.v.).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `verification_request_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_REQUESTS` |
| `media_file_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MEDIA_FILES` |
| `evidence_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `evidence_link` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin evidence_link |
| `user_note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin user_note |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `review_note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_note |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `capture_method` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin capture_method |
| `captured_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `capture_session_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `EVIDENCE_CAPTURE_SESSIONS` |
| `client_user_agent` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin client_user_agent |
| `image_quality_score` | `decimal` | - | Nullable | Trường dữ liệu lưu trữ thông tin image_quality_score |

### Bảng `VERIFICATION_REVIEW_ACTIONS`
**Mô tả**: Lịch sử thao tác phê duyệt hoặc từ chối yêu cầu xác thực của Admin.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `verification_request_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_REQUESTS` |
| `admin_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `action_key` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin action_key |
| `reason` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin reason |
| `instruction` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin instruction |
| `before_snapshot_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin before_snapshot_json |
| `after_snapshot_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin after_snapshot_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |

### Bảng `EVIDENCE_CAPTURE_SESSIONS`
**Mô tả**: Phiên chụp và tải minh chứng xác thực trực tiếp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `verification_request_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `VERIFICATION_REQUESTS` |
| `session_token_hash` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin session_token_hash |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `required_evidence_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `started_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `expires_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `completed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `failed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `attempt_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `client_user_agent` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin client_user_agent |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `EVIDENCE_ANALYSIS_JOBS`
**Mô tả**: Tiến trình phân tích tự động minh chứng xác thực bằng AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `verification_request_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_REQUESTS` |
| `verification_evidence_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_EVIDENCES` |
| `media_file_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MEDIA_FILES` |
| `provider` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin provider |
| `model_name` | `string` | - | Nullable | Tên hiển thị |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `attempt_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `queued_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `started_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `finished_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `failed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `error_code` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin error_code |
| `error_message` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin error_message |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `EVIDENCE_ANALYSIS_RESULTS`
**Mô tả**: Kết quả phân tích minh chứng của AI (độ khớp tên, mã sinh viên, độ tin cậy).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `analysis_job_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `EVIDENCE_ANALYSIS_JOBS` |
| `verification_request_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_REQUESTS` |
| `verification_evidence_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `VERIFICATION_EVIDENCES` |
| `document_type_detected` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `document_type_confidence` | `decimal` | - | Nullable | Phân loại/Kiểu bản ghi |
| `ocr_text` | `longText` | - | Nullable | Trường dữ liệu lưu trữ thông tin ocr_text |
| `extracted_fields_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin extracted_fields_json |
| `match_result_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin match_result_json |
| `risk_flags_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin risk_flags_json |
| `confidence_score` | `decimal` | - | Nullable | Trường dữ liệu lưu trữ thông tin confidence_score |
| `recommendation` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin recommendation |
| `review_summary` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_summary |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `AUDIT_LOGS`
**Mô tả**: Nhật ký hệ thống ghi lại các hành động nhạy cảm hoặc quan trọng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `actor_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `actor_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `action_key` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin action_key |
| `target_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `target_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin target_id |
| `context_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `context_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin context_id |
| `before_snapshot_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin before_snapshot_json |
| `after_snapshot_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin after_snapshot_json |
| `reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin reason |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `ip_address` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin ip_address |
| `user_agent` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin user_agent |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |

### Bảng `SUPPORT_TICKETS`
**Mô tả**: Yêu cầu hỗ trợ/báo lỗi gửi tới ban quản trị.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `category` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin category |
| `message` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin message |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `priority` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin priority |
| `assigned_to` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `resolved_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `ANNOUNCEMENTS`
**Mô tả**: Các thông báo chung từ ban quản trị tới toàn hệ thống.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `title` | `string` | - | Required | Tiêu đề |
| `body` | `text` | - | Required | Nội dung chi tiết |
| `type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `target` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin target |
| `created_by` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin created_by |
| `starts_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `expires_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `PERMISSION_GRANTS`
**Mô tả**: Danh sách cấp quyền chi tiết cho người dùng theo phạm vi.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `permission_key` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin permission_key |
| `scope_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `scope_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin scope_id |
| `revoked_by` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin revoked_by |
| `reason` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin reason |
| `starts_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `expires_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `revoked_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="communities"></a>Communities, Suggestions & Events (Cộng đồng & Sự kiện nhóm)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/communities.mmd) | [Bản gốc (Đầy đủ)](original/communities.mmd)

### Bảng `COMMUNITIES`
**Mô tả**: Lưu trữ thông tin các cộng đồng/nhóm học tập.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_by` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin created_by |
| `settings` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin settings |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `members_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `join_policy` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin join_policy |
| `related_program_id` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `ACADEMIC_PROGRAMS` |
| `resource_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `suspended_safe_reason` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin suspended_safe_reason |
| `suspended_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `archived_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |

### Bảng `COMMUNITY_MEMBERS`
**Mô tả**: Thành viên trực thuộc các cộng đồng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `community_id` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `COMMUNITIES` |
| `user_id` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `role` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin role |
| `joined_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `removed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `remove_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin remove_reason |

### Bảng `COMMUNITY_JOIN_REQUESTS`
**Mô tả**: Yêu cầu xin gia nhập các cộng đồng riêng tư.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `community_id` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin community_id |
| `user_id` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `join_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin join_reason |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `review_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_reason |
| `reviewed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `COMMUNITY_RESOURCES`
**Mô tả**: Tài nguyên/tài liệu chia sẻ trong cộng đồng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `community_id` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin community_id |
| `title` | `string` | - | Required | Tiêu đề |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `category` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin category |
| `approved_by` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `approved_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `rejection_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin rejection_reason |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `COMMUNITY_SUGGESTIONS`
**Mô tả**: Đề xuất tạo cộng đồng mới từ thành viên gửi lên Admin.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `submitted_by` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin submitted_by |
| `suggested_name` | `string` | - | Required | Tên hiển thị |
| `community_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `purpose` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin purpose |
| `target_members` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin target_members |
| `related_program_id` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `ACADEMIC_PROGRAMS` |
| `proposed_owner_id` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `admin_instruction` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin admin_instruction |
| `admin_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin admin_reason |
| `reviewed_by` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `join_policy` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin join_policy |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `rules` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin rules |

### Bảng `COMMUNITY_EVENTS`
**Mô tả**: Sự kiện do cộng đồng tổ chức.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `community_id` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin community_id |
| `created_by` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `title` | `string` | - | Required | Tiêu đề |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `ends_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `online_link` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin online_link |
| `rsvp_deadline` | `timestamp` | - | Nullable | Trường dữ liệu lưu trữ thông tin rsvp_deadline |
| `capacity` | `unsignedInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin capacity |
| `waitlist_enabled` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin waitlist_enabled |
| `interested_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `waitlist_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `cancelled_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `COMMUNITY_EVENT_RSVPS`
**Mô tả**: Danh sách đăng ký tham gia sự kiện cộng đồng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `event_id` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `COMMUNITY_EVENTS` |
| `user_id` | `unsignedBigInteger` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin note |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="academic"></a>Academic Programs & Faculty Structure (Khung đào tạo & Khoa chuyên môn)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/academic.mmd) | [Bản gốc (Đầy đủ)](original/academic.mmd)

### Bảng `FACULTIES`
**Mô tả**: Lưu trữ thông tin các Khoa đào tạo của trường đại học.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `code` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin code |
| `normalized_name` | `string` | - | Nullable | Tên hiển thị |
| `source_url` | `text` | - | Nullable | Đường dẫn lưu trữ hoặc URL |

### Bảng `ACADEMIC_PROGRAMS`
**Mô tả**: Thông tin các chương trình đào tạo hoặc chuyên ngành thuộc Khoa.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `degree_level` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin degree_level |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MAJORS`
**Mô tả**: Lưu trữ danh mục ngành đào tạo.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `code` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin code |
| `name` | `string` | - | Required | Tên hiển thị |
| `normalized_name` | `string` | - | Nullable | Tên hiển thị |
| `degree_level` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin degree_level |
| `source_url` | `text` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `ADMISSION_COHORTS`
**Mô tả**: Quản lý khóa tuyển sinh/niên khóa.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `year` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin year |
| `cohort_name` | `string` | - | Required | Tên hiển thị |
| `normalized_name` | `string` | - | Nullable | Tên hiển thị |
| `note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin note |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `TRAINING_PROGRAMS`
**Mô tả**: Chương trình đào tạo tổng quát cho từng khóa-ngành.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `cohort_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `ADMISSION_COHORTS` |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `FACULTIES` |
| `major_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MAJORS` |
| `title` | `string` | - | Required | Tiêu đề |
| `total_credits` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_credits |
| `effective_from` | `year` | - | Nullable | Trường dữ liệu lưu trữ thông tin effective_from |
| `effective_to` | `year` | - | Nullable | Trường dữ liệu lưu trữ thông tin effective_to |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `source_url` | `text` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `source_file_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_file_id |
| `published_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CURRICULUM_COURSE_GROUPS`
**Mô tả**: Nhóm học phần (bắt buộc, tự chọn, giáo dục đại cương, v.v.).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `TRAINING_PROGRAMS` |
| `name` | `string` | - | Required | Tên hiển thị |
| `group_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin note |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CURRICULUM_COURSES`
**Mô tả**: Học phần chi tiết trong chương trình đào tạo của trường.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `TRAINING_PROGRAMS` |
| `group_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CURRICULUM_COURSE_GROUPS` |
| `semester` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin semester |
| `course_code` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin course_code |
| `course_name` | `string` | - | Required | Tên hiển thị |
| `normalized_course_name` | `string` | - | Nullable | Tên hiển thị |
| `credits` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin credits |
| `theory_hours` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin theory_hours |
| `practice_hours` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin practice_hours |
| `self_study_hours` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin self_study_hours |
| `course_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `prerequisite` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin prerequisite |
| `source_location` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_location |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `PROGRAM_LEARNING_OUTCOMES`
**Mô tả**: Chuẩn đầu ra của chương trình đào tạo.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `TRAINING_PROGRAMS` |
| `code` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin code |
| `description` | `text` | - | Required | Mô tả chi tiết |
| `category` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin category |
| `source_location` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_location |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="mentorship"></a>Mentorship & Advising (Hệ thống Cố vấn & Mentor)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/mentorship.mmd) | [Bản gốc (Đầy đủ)](original/mentorship.mmd)

### Bảng `MENTOR_ACCESSES`
**Mô tả**: Quản lý quyền truy cập và kiểm duyệt vai trò Mentor.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin note |
| `reviewed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MENTOR_ACCESS_REQUESTS`
**Mô tả**: Yêu cầu đăng ký trở thành Mentor của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `requested_role_context` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin requested_role_context |
| `experience_summary` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin experience_summary |
| `expertise_topics` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin expertise_topics |
| `career_paths` | `json` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `availability_note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin availability_note |
| `policy_agreed` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin policy_agreed |
| `headline` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin headline |
| `bio` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin bio |
| `help_topics` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin help_topics |
| `preferred_request_types` | `json` | - | Nullable | Phân loại/Kiểu bản ghi |
| `skills` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin skills |
| `response_expectation_text` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin response_expectation_text |
| `office_hours_text` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin office_hours_text |
| `evidence_media_id` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `MEDIA` |
| `reviewed_by` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `reviewed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `review_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin review_reason |
| `admin_notes` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin admin_notes |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MENTOR_PROFILES`
**Mô tả**: Hồ sơ chi tiết của Mentor (lĩnh vực hỗ trợ, kỹ năng, giới hạn nhận hỗ trợ).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `headline` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin headline |
| `bio` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin bio |
| `expertise_topics` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin expertise_topics |
| `career_paths` | `json` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `skills` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin skills |
| `help_topics` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin help_topics |
| `preferred_request_types` | `json` | - | Nullable | Phân loại/Kiểu bản ghi |
| `availability_status` | `string` | - | Required | Trạng thái hoạt động |
| `is_public_ready` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_public_ready |
| `max_monthly_accepts` | `unsignedSmallInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin max_monthly_accepts |
| `response_expectation_text` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin response_expectation_text |
| `office_hours_text` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin office_hours_text |
| `is_active` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_active |
| `approved_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `approved_by` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MENTOR_REQUESTS`
**Mô tả**: Yêu cầu kết nối hỗ trợ từ Sinh viên tới Mentor.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `student_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `mentor_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `mentor_profile_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MENTOR_PROFILES` |
| `topic` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin topic |
| `goal` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin goal |
| `question` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin question |
| `urgency` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin urgency |
| `expected_outcome` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin expected_outcome |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `decline_reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin decline_reason |
| `more_info_question` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin more_info_question |
| `conversation_id` | `unsignedBigInteger` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CONVERSATIONS` |
| `accepted_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `declined_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `completed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `MENTOR_FEEDBACK`
**Mô tả**: Đánh giá phản hồi từ sinh viên sau khi hoàn thành buổi cố vấn.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `mentor_request_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `MENTOR_REQUESTS` |
| `student_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `mentor_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `helpfulness_level` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin helpfulness_level |
| `is_private` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_private |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

## <a id="ai_chatbot"></a>AI Chatbot & Document Search (AI Chatbot & Tra cứu thông tin)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/ai_chatbot.mmd) | [Bản gốc (Đầy đủ)](original/ai_chatbot.mmd)

### Bảng `SOURCE_DOCUMENTS`
**Mô tả**: Tài liệu nguồn chứa thông tin đào tạo để đồng bộ vào Chatbot.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `document_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `cohort` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin cohort |
| `effective_year` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin effective_year |
| `source_url` | `text` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `file_path` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `mime_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `source_hash` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_hash |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `uploaded_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `published_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `knowledge_batch_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `KNOWLEDGE_BATCHES` |
| `knowledge_batch_key` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin knowledge_batch_key |

### Bảng `DOCUMENT_CHUNKS`
**Mô tả**: Đoạn văn bản được cắt nhỏ từ tài liệu để tìm kiếm vector.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `source_document_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `SOURCE_DOCUMENTS` |
| `chunk_index` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin chunk_index |
| `chunk_text` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin chunk_text |
| `token_count` | `integer` | - | Required | Số lượng / Bộ đếm tích lũy |
| `page_start` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin page_start |
| `page_end` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin page_end |
| `part` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin part |
| `chapter` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin chapter |
| `section` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin section |
| `article` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin article |
| `clause` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin clause |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `embedding_status` | `string` | - | Required | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `knowledge_batch_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `KNOWLEDGE_BATCHES` |
| `knowledge_batch_key` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin knowledge_batch_key |

### Bảng `CHAT_SESSIONS`
**Mô tả**: Phiên chat tra cứu của người dùng với Chatbot AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `title` | `string` | - | Nullable | Tiêu đề |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CHAT_MESSAGES`
**Mô tả**: Lịch sử tin nhắn trao đổi với Chatbot AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `session_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CHAT_SESSIONS` |
| `role` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin role |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `AI_QUESTIONS`
**Mô tả**: Câu hỏi của người dùng gửi tới hệ thống AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `session_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CHAT_SESSIONS` |
| `user_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `original_question` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin original_question |
| `normalized_question` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin normalized_question |
| `intent` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin intent |
| `source_route` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin source_route |
| `confidence` | `double` | - | Required | Trường dữ liệu lưu trữ thông tin confidence |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `AI_ANSWERS`
**Mô tả**: Câu trả lời của AI phản hồi cho người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `question_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `AI_QUESTIONS` |
| `answer_text` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin answer_text |
| `model_provider` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin model_provider |
| `model_name` | `string` | - | Nullable | Tên hiển thị |
| `prompt_version` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin prompt_version |
| `latency_ms` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin latency_ms |
| `input_tokens` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin input_tokens |
| `output_tokens` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin output_tokens |
| `total_tokens` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_tokens |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `AI_RETRIEVED_CHUNKS`
**Mô tả**: Các đoạn văn bản tài liệu được AI truy xuất để làm ngữ cảnh trả lời.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `question_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `AI_QUESTIONS` |
| `document_chunk_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `DOCUMENT_CHUNKS` |
| `score` | `double` | - | Required | Trường dữ liệu lưu trữ thông tin score |
| `rerank_score` | `double` | - | Nullable | Trường dữ liệu lưu trữ thông tin rerank_score |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `AI_STRUCTURED_QUERIES`
**Mô tả**: Câu truy vấn cấu trúc hóa được sinh ra từ câu hỏi tự nhiên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `question_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `AI_QUESTIONS` |
| `query_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `filters_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin filters_json |
| `result_count` | `integer` | - | Required | Số lượng / Bộ đếm tích lũy |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `AI_FEEDBACK`
**Mô tả**: Phản hồi đánh giá (rating) của người dùng cho câu trả lời của AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `answer_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `AI_ANSWERS` |
| `user_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `rating` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin rating |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

### Bảng `AI_PROMPT_VERSIONS`
**Mô tả**: Lưu trữ các phiên bản prompt cấu hình cho AI.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `version` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin version |
| `content` | `text` | - | Required | Nội dung chi tiết |
| `is_active` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_active |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `SOURCE_SYNC_LOGS`
**Mô tả**: Nhật ký đồng bộ hóa dữ liệu từ nguồn bên ngoài.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `source_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `source_url` | `text` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `started_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |
| `finished_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `records_found` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin records_found |
| `records_created` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin records_created |
| `records_updated` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin records_updated |
| `error_message` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin error_message |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `KNOWLEDGE_BATCHES`
**Mô tả**: Lô nhập liệu/đồng bộ tri thức học thuật.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `batch_key` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin batch_key |
| `name` | `string` | - | Required | Tên hiển thị |
| `root_path` | `string` | - | Nullable | Đường dẫn lưu trữ hoặc URL |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `total_imported` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_imported |
| `total_failed` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_failed |
| `total_needs_ocr` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_needs_ocr |
| `total_chunks` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_chunks |
| `total_vectors` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_vectors |
| `started_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `finished_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `metadata_json` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `TRAINING_PROGRAM_EXTRACTION_CANDIDATES`
**Mô tả**: Các ứng viên thông tin đào tạo được trích xuất tự động.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `source_document_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `SOURCE_DOCUMENTS` |
| `field_name` | `string` | - | Required | Tên hiển thị |
| `candidate_value` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin candidate_value |
| `confidence` | `double` | - | Required | Trường dữ liệu lưu trữ thông tin confidence |
| `evidence_text` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin evidence_text |
| `page` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin page |
| `metadata_json` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Required | Thời điểm tạo bản ghi |

---

## <a id="career"></a>Career Development & Pathways (Lộ trình Hướng nghiệp & Kỹ năng)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/career.mmd) | [Bản gốc (Đầy đủ)](original/career.mmd)

### Bảng `CAREER_IMPORT_RUNS`
**Mô tả**: Lịch sử các phiên nhập dữ liệu hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `started_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `completed_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `log` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin log |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_SOURCE_DOCUMENTS`
**Mô tả**: Tài liệu nguồn phục vụ phân tích dữ liệu hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `import_run_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_IMPORT_RUNS` |
| `file_path` | `string` | - | Required | Đường dẫn lưu trữ hoặc URL |
| `document_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `extraction_status` | `string` | - | Required | Trạng thái hoạt động |
| `error_message` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin error_message |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_COHORTS`
**Mô tả**: Các khóa học/niên khóa áp dụng trong hệ thống hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `start_year` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin start_year |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_FACULTIES`
**Mô tả**: Thông tin Khoa áp dụng trong hệ thống hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_MAJORS`
**Mô tả**: Thông tin ngành đào tạo áp dụng trong hệ thống hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_FACULTIES` |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_PROGRAMS`
**Mô tả**: Chương trình đào tạo hướng nghiệp cụ thể.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `cohort_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_COHORTS` |
| `faculty_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_FACULTIES` |
| `major_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_MAJORS` |
| `source_document_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_SOURCE_DOCUMENTS` |
| `name` | `string` | - | Required | Tên hiển thị |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `total_credits` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin total_credits |
| `total_semesters` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin total_semesters |
| `original_dir` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin original_dir |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_SEMESTERS`
**Mô tả**: Học kỳ đào tạo trong khung chương trình hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_PROGRAMS` |
| `semester_number` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin semester_number |
| `title` | `string` | - | Nullable | Tiêu đề |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_COURSES`
**Mô tả**: Môn học/học phần dùng trong bản đồ hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `code` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin code |
| `name` | `string` | - | Required | Tên hiển thị |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_PROGRAM_COURSES`
**Mô tả**: Liên kết môn học vào chương trình đào tạo hướng nghiệp cụ thể.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `program_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_PROGRAMS` |
| `semester_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_SEMESTERS` |
| `course_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_COURSES` |
| `source_document_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_SOURCE_DOCUMENTS` |
| `credits` | `integer` | - | Nullable | Trường dữ liệu lưu trữ thông tin credits |
| `is_mandatory` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_mandatory |
| `knowledge_block` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin knowledge_block |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_COURSE_DESCRIPTIONS`
**Mô tả**: Mô tả chi tiết nội dung môn học hướng nghiệp.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `course_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_COURSES` |
| `description_text` | `text` | - | Required | Mô tả chi tiết |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_DATA_QUALITY_ISSUES`
**Mô tả**: Ghi nhận lỗi chất lượng dữ liệu trong quá trình import.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `import_run_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_IMPORT_RUNS` |
| `source_document_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_SOURCE_DOCUMENTS` |
| `program_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_PROGRAMS` |
| `issue_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `severity` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin severity |
| `message` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin message |
| `context` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin context |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_CONTRIBUTIONS`
**Mô tả**: Các đóng góp nội dung hướng nghiệp từ cộng đồng sinh viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `target_id` | `unsignedBigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin target_id |
| `contribution_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `title` | `string` | - | Nullable | Tiêu đề |
| `content` | `text` | - | Required | Nội dung chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `source_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `upvotes_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `downvotes_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `reports_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `verified_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `verified_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `CAREER_CONTRIBUTION_MODERATION_LOGS`
**Mô tả**: Lịch sử kiểm duyệt các đóng góp nội dung của sinh viên.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `career_contribution_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_CONTRIBUTIONS` |
| `admin_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `action` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin action |
| `reason` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin reason |
| `previous_status` | `string` | - | Nullable | Trạng thái hoạt động |
| `new_status` | `string` | - | Nullable | Trạng thái hoạt động |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_CONTRIBUTION_VOTES`
**Mô tả**: Lượt bình chọn (upvote/downvote) đóng góp từ cộng đồng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `career_contribution_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_CONTRIBUTIONS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `value` | `tinyInteger` | - | Required | Trường dữ liệu lưu trữ thông tin value |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_SKILLS`
**Mô tả**: Danh mục kỹ năng chuyên môn/kỹ năng mềm.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `normalized_name` | `string` | - | Required | Tên hiển thị |
| `category` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin category |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `created_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `is_active` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_active |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_COURSE_SKILL_EDGES`
**Mô tả**: Liên kết kỹ năng đầu ra được cung cấp bởi môn học.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `career_course_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_COURSES` |
| `career_skill_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_SKILLS` |
| `source_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `relevance_level` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin relevance_level |
| `is_active` | `boolean` | - | Required | Trường dữ liệu lưu trữ thông tin is_active |
| `verified_by` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `USERS` |
| `verified_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_POSITIONS`
**Mô tả**: Các vị trí công việc/nghề nghiệp mục tiêu.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `created_by` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `title` | `string` | - | Required | Tiêu đề |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `industry` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin industry |
| `target_audience` | `string` | - | Nullable | Trường dữ liệu lưu trữ thông tin target_audience |
| `related_faculty_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_FACULTIES` |
| `related_major_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_MAJORS` |
| `related_program_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_PROGRAMS` |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `published_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `upvotes_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `saves_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `reports_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `CAREER_POSITION_SECTIONS`
**Mô tả**: Các nhóm nội dung/yêu cầu của một vị trí công việc.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `position_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_POSITIONS` |
| `title` | `string` | - | Required | Tiêu đề |
| `section_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `order_index` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin order_index |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_POSITION_ITEMS`
**Mô tả**: Chi tiết kỹ năng/môn học yêu cầu cho từng vị trí công việc.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `position_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_POSITIONS` |
| `section_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_POSITION_SECTIONS` |
| `item_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `target` | `nullableMorphs` | - | Required | Trường dữ liệu lưu trữ thông tin target |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `importance_level` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin importance_level |
| `source_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `order_index` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin order_index |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_POSITION_SAVES`
**Mô tả**: Danh sách vị trí công việc người dùng lưu trữ/quan tâm.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `position_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_POSITIONS` |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_USER_PATHWAYS`
**Mô tả**: Lộ trình nghề nghiệp cá nhân do người dùng thiết kế.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `title` | `string` | - | Required | Tiêu đề |
| `slug` | `string` | - | Required | Đường dẫn tĩnh thân thiện (SEO Slug) |
| `program_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_PROGRAMS` |
| `career_position_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_POSITIONS` |
| `story` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin story |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `visibility` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin visibility |
| `published_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `saves_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `comments_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `reports_count` | `unsignedInteger` | - | Required | Số lượng / Bộ đếm tích lũy |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `CAREER_USER_PATHWAY_ITEMS`
**Mô tả**: Các bước/môn học trong lộ trình cá nhân của người dùng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `pathway_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_USER_PATHWAYS` |
| `item_type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `target_type` | `string` | - | Nullable | Phân loại/Kiểu bản ghi |
| `target_id` | `unsignedBigInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin target_id |
| `semester_number` | `unsignedInteger` | - | Nullable | Trường dữ liệu lưu trữ thông tin semester_number |
| `title` | `string` | - | Nullable | Tiêu đề |
| `note` | `text` | - | Nullable | Trường dữ liệu lưu trữ thông tin note |
| `order_index` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin order_index |
| `metadata_json` | `json` | - | Nullable | Trường dữ liệu lưu trữ thông tin metadata_json |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_USER_PATHWAY_SAVES`
**Mô tả**: Lộ trình cá nhân được người dùng khác lưu trữ.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `pathway_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_USER_PATHWAYS` |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

### Bảng `CAREER_USER_PATHWAY_COMMENTS`
**Mô tả**: Bình luận trao đổi trên lộ trình cá nhân.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `pathway_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_USER_PATHWAYS` |
| `user_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `parent_id` | `foreignId` | `FK` | Nullable | Khóa ngoại liên kết đến bảng `CAREER_USER_PATHWAY_COMMENTS` |
| `body` | `text` | - | Required | Nội dung chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |
| `deleted_at` | `timestamp` | - | Required | Thời điểm xóa mềm bản ghi (Soft Delete) |

### Bảng `CAREER_USER_PATHWAY_REPORTS`
**Mô tả**: Báo cáo vi phạm lộ trình cá nhân.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `reporter_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `USERS` |
| `pathway_id` | `foreignId` | `FK` | Required | Khóa ngoại liên kết đến bảng `CAREER_USER_PATHWAYS` |
| `reason` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin reason |
| `description` | `text` | - | Nullable | Mô tả chi tiết |
| `status` | `string` | - | Required | Trạng thái hoạt động |

---

## <a id="system"></a>System & Job Queues (Hệ thống & Hàng đợi công việc)

Xem sơ đồ trực quan: [Bản thu gọn (Dễ đọc)](simplified/system.mmd) | [Bản gốc (Đầy đủ)](original/system.mmd)

### Bảng `CACHE`
**Mô tả**: Lưu trữ dữ liệu cache tạm thời của ứng dụng.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `key` | `string` | `PK` | Required | Trường dữ liệu lưu trữ thông tin key |
| `value` | `mediumText` | - | Required | Trường dữ liệu lưu trữ thông tin value |
| `expiration` | `bigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin expiration |

### Bảng `CACHE_LOCKS`
**Mô tả**: Lưu các khóa lock cache để tránh race condition.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `key` | `string` | `PK` | Required | Trường dữ liệu lưu trữ thông tin key |
| `owner` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin owner |
| `expiration` | `bigInteger` | - | Required | Trường dữ liệu lưu trữ thông tin expiration |

### Bảng `JOBS`
**Mô tả**: Quản lý các công việc chạy ngầm (Queue jobs).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `queue` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin queue |
| `payload` | `longText` | - | Required | Trường dữ liệu lưu trữ thông tin payload |
| `attempts` | `unsignedSmallInteger` | - | Required | Trường dữ liệu lưu trữ thông tin attempts |
| `reserved_at` | `unsignedInteger` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `available_at` | `unsignedInteger` | - | Required | Thời điểm ghi nhận sự kiện |
| `created_at` | `unsignedInteger` | - | Required | Thời điểm tạo bản ghi |

### Bảng `JOB_BATCHES`
**Mô tả**: Quản lý các nhóm công việc chạy ngầm theo lô (Batch jobs).

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `string` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `name` | `string` | - | Required | Tên hiển thị |
| `total_jobs` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin total_jobs |
| `pending_jobs` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin pending_jobs |
| `failed_jobs` | `integer` | - | Required | Trường dữ liệu lưu trữ thông tin failed_jobs |
| `failed_job_ids` | `longText` | - | Required | Trường dữ liệu lưu trữ thông tin failed_job_ids |
| `options` | `mediumText` | - | Nullable | Trường dữ liệu lưu trữ thông tin options |
| `cancelled_at` | `integer` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `integer` | - | Required | Thời điểm tạo bản ghi |
| `finished_at` | `integer` | - | Nullable | Thời điểm ghi nhận sự kiện |

### Bảng `FAILED_JOBS`
**Mô tả**: Lưu trữ chi tiết các công việc chạy ngầm bị thất bại để xử lý lại.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `uuid` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin uuid |
| `connection` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin connection |
| `queue` | `string` | - | Required | Trường dữ liệu lưu trữ thông tin queue |
| `payload` | `longText` | - | Required | Trường dữ liệu lưu trữ thông tin payload |
| `exception` | `longText` | - | Required | Trường dữ liệu lưu trữ thông tin exception |
| `failed_at` | `timestamp` | - | Required | Thời điểm ghi nhận sự kiện |

### Bảng `NOTIFICATIONS`
**Mô tả**: Lưu trữ thông báo gửi đến người dùng trong hệ thống.

| Tên trường | Kiểu dữ liệu | Ràng buộc | Trạng thái | Giải thích vai trò |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `uuid` | `PK` | Required | Khóa chính tự tăng (Primary Key) |
| `type` | `string` | - | Required | Phân loại/Kiểu bản ghi |
| `notifiable` | `morphs` | - | Required | Trường dữ liệu lưu trữ thông tin notifiable |
| `data` | `text` | - | Required | Trường dữ liệu lưu trữ thông tin data |
| `read_at` | `timestamp` | - | Nullable | Thời điểm ghi nhận sự kiện |
| `created_at` | `timestamp` | - | Nullable | Thời điểm tạo bản ghi |
| `updated_at` | `timestamp` | - | Nullable | Thời điểm cập nhật bản ghi gần nhất |

---

