---
title: "Migration Strategy"
module: "06-database"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Database Architecture / Backend"
depends_on:
  - "database-overview.md"
  - "schema.md"
  - "table-specifications.md"
  - "erd.mmd"
  - "../05-system-architecture/techstack.md"
related:
  - "seed-data.md"
---

# Migration Strategy

## 1. Purpose

File này định nghĩa chiến lược viết Laravel migration cho database UEConnect.

Mục tiêu:

- Migration có thứ tự rõ ràng.
- Không tạo bảng thiếu foreign key.
- Không tạo column nullable bừa bãi.
- Không phá dữ liệu production khi thay đổi schema.
- Dễ rollback trong giai đoạn dev.
- Dễ review khi pull request.
- Phù hợp SQL Server + Laravel Eloquent.

Migration không chỉ là file để “chạy cho có bảng”. Migration là lịch sử tiến hóa database. Và giống mọi lịch sử tiến hóa khác, nếu làm bừa thì sẽ sinh ra sinh vật rất lạ.

---

# 2. Migration Principles

## 2.1. One Migration, One Clear Purpose

Mỗi migration nên làm một việc rõ ràng.

Good:

```txt
create_users_table
create_profiles_table
add_account_status_to_users_table
create_verification_requests_table

Bad:

update_database
fix_tables
add_some_fields
final_final_db

Tên migration kiểu fix_tables là tiếng hét tuyệt vọng được lưu vào Git.

2.2. Create Reference Tables First

Các bảng reference phải tạo trước domain tables.

Thứ tự:

faculties
academic_programs
mentor_topics
report_reasons
moderation_action_types

Sau đó mới đến:

profiles
verification_requests
mentor_requests
career_pathways
2.3. Parent Before Child

Bảng cha phải tạo trước bảng con.

Example:

users
→ profiles
→ student_profiles
verification_requests
→ verification_evidences
→ verification_review_actions
communities
→ community_members
→ community_channels
→ community_resources
2.4. Avoid Nullable Without Reason

Chỉ dùng nullable khi business cho phép thiếu dữ liệu.

Good nullable:

reviewed_at nullable because request may not be reviewed yet
avatar_media_file_id nullable before profile setup complete
conversation_id nullable before mentor request accepted

Bad nullable:

email nullable
status nullable
user_id nullable
created_at nullable

Nếu cột quan trọng nullable chỉ để đỡ lỗi seed, xin chúc mừng, bạn vừa chuyển bug từ hôm nay sang production.

2.5. String Enum Fields

Business state dùng string enum.

Example:

$table->string('status', 50)->index();
$table->string('account_status', 50)->default('active')->index();

Không dùng integer magic enum kiểu:

0 = pending
1 = approved
2 = rejected

Ba tháng sau không ai nhớ 2 là rejected hay “đã bị thần linh bỏ rơi”.

2.6. Index What You Query

Mỗi query phổ biến phải có index hỗ trợ.

Index bắt buộc cho:

foreign keys
status
created_at timeline
deleted_at soft delete
target_type + target_id polymorphic
user_id ownership
community_id scope
conversation_id messaging
2.7. Audit Tables Are Append-only

Các bảng sau không soft delete:

audit_logs
analytics_events
verification_review_actions
moderation_actions
account_status_histories
report_status_histories

Không sửa/xóa audit log bằng app code.

2.8. No Raw Private File Paths in UI

Migration có thể lưu path, nhưng app không được expose trực tiếp.

Private files:

verification_evidence
message_attachment
admin_export
private community_resource
3. Recommended Migration Order
3.1. Foundation
001_create_users_table
002_create_password_reset_tokens_table
003_create_sessions_table
004_create_account_status_histories_table
3.2. Reference Data Tables
010_create_faculties_table
011_create_academic_programs_table
012_create_report_reasons_table
013_create_moderation_action_types_table
014_create_system_settings_table
3.3. Media
020_create_media_files_table
3.4. Profile
030_create_profiles_table
031_create_student_profiles_table
032_create_alumni_profiles_table
033_create_advisor_profiles_table
034_create_profile_privacy_settings_table
035_create_profile_interests_table
036_create_profile_skills_table
037_create_profile_social_links_table
038_create_saved_profiles_table
039_create_blocked_users_table
3.5. Verification
040_create_verification_requests_table
041_create_verification_evidences_table
042_create_verification_review_actions_table
043_create_verification_conflicts_table
3.6. Feed
050_create_posts_table
051_create_post_media_table
052_create_comments_table
053_create_comment_media_table
054_create_system_announcements_table
3.7. Discovery / Connection
060_create_greetings_table
061_create_connections_table
062_create_connection_events_table
063_create_discovery_passes_table
3.8. Messaging
070_create_conversations_table
071_create_conversation_participants_table
072_create_messages_table
073_create_message_attachments_table
074_create_message_read_receipts_table
075_create_message_edits_table
076_create_message_requests_table
3.9. Notification
080_create_notifications_table
081_create_notification_preferences_table
082_create_browser_push_subscriptions_table
3.10. Mentor
090_create_mentor_profiles_table
091_create_mentor_topics_table
092_create_mentor_profile_topics_table
093_create_mentor_access_requests_table
094_create_mentor_requests_table
095_create_mentor_availability_slots_table
096_create_mentor_feedback_table
3.11. Career Pathway
100_create_career_pathways_table
101_create_career_pathway_steps_table
102_create_career_pathway_resources_table
103_create_career_pathway_topics_table
104_create_saved_career_pathways_table
3.12. Community / Club
110_create_communities_table
111_create_community_members_table
112_create_community_join_requests_table
113_create_community_roles_table
114_create_community_channels_table
115_create_community_posts_table
116_create_community_resources_table
117_create_community_resource_reviews_table
118_create_community_events_table

Community chat nên ưu tiên dùng conversations/messages với conversation_type = community_chat. Chỉ tạo community_messages riêng nếu sau này cần behavior khác thật sự.

3.13. Safety / Moderation
120_create_reports_table
121_create_report_status_histories_table
122_create_report_attachments_table
123_create_moderation_cases_table
124_create_moderation_actions_table
125_create_moderation_case_assignments_table
126_create_moderation_appeals_table
127_create_content_visibility_states_table
3.14. Permission / Audit / Analytics
130_create_permission_grants_table
131_create_audit_logs_table
132_create_admin_actions_table
133_create_analytics_events_table
3.15. AI Evidence Intelligence Future
140_create_evidence_analysis_jobs_table
141_create_evidence_analysis_results_table
4. Laravel Migration Style
4.1. Migration Skeleton
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('example_tables', function (Blueprint $table) {
            $table->id();

            $table->string('status', 50)->default('active')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('example_tables');
    }
};
4.2. Foreign Key Style

Prefer explicit foreign keys:

$table->foreignId('user_id')
    ->constrained('users')
    ->restrictOnDelete();

Use nullOnDelete() only when losing parent should not delete child.

Example:

$table->foreignId('reviewed_by')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();
4.3. Delete Strategy

Use intentionally:

Strategy	Use Case
restrictOnDelete()	Audit-sensitive parent
cascadeOnDelete()	Pure child records with no independent meaning
nullOnDelete()	Optional reviewer/admin reference
Soft delete	User-generated content

Avoid cascade delete on:

audit_logs
moderation_actions
verification_review_actions
reports
messages
permission_grants

Vâng, cascade delete audit log là cách hệ thống tự xóa ký ức, rất tiện cho tội phạm và rất tệ cho sản phẩm.

4.4. Status Column Pattern
$table->string('status', 50)
    ->default('pending')
    ->index();

For domain-specific status:

$table->string('account_status', 50)
    ->default('active')
    ->index();
4.5. Polymorphic Target Pattern
$table->string('target_type', 100);
$table->unsignedBigInteger('target_id');

$table->index(['target_type', 'target_id']);

Do not use Laravel morphs blindly if you need strict enum control.

4.6. JSON Column Pattern

SQL Server support varies by environment. To stay portable:

$table->json('metadata_json')->nullable();

If SQL Server driver/environment causes issues, use:

$table->longText('metadata_json')->nullable();

And validate JSON in application layer. Không đẹp bằng, nhưng ít drama hơn.

4.7. Soft Delete Pattern
$table->softDeletes();

Add index if queried frequently:

$table->index('deleted_at');
4.8. Unique Constraint Pattern

Example:

$table->unique(['conversation_id', 'user_id']);

For conditional uniqueness like “only one active pending greeting”, SQL Server filtered unique index may be needed.

Laravel schema builder may not support every filtered index cleanly, so use raw SQL when necessary:

DB::statement("
    CREATE UNIQUE INDEX greetings_pending_unique
    ON greetings(sender_id, receiver_id)
    WHERE status = 'pending' AND deleted_at IS NULL
");

Use raw SQL carefully and document it in migration comments, because future devs are not archaeologists.

5. Critical Migration Examples
5.1. users
Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('email')->unique();
    $table->string('password');
    $table->timestamp('email_verified_at')->nullable();

    $table->string('account_status', 50)
        ->default('active')
        ->index();

    $table->text('account_status_reason')->nullable();
    $table->timestamp('account_restricted_until')->nullable();
    $table->timestamp('last_login_at')->nullable();

    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
5.2. profiles
Schema::create('profiles', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->unique()
        ->constrained('users')
        ->restrictOnDelete();

    $table->string('display_name');
    $table->foreignId('avatar_media_file_id')
        ->nullable()
        ->constrained('media_files')
        ->nullOnDelete();

    $table->text('bio')->nullable();

    $table->string('role_type', 50)->index();

    $table->string('profile_status', 50)
        ->default('incomplete')
        ->index();

    $table->string('visibility', 50)
        ->default('hcmue_only')
        ->index();

    $table->boolean('discoverable')->default(true)->index();
    $table->timestamp('profile_completed_at')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
5.3. verification_requests
Schema::create('verification_requests', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained('users')
        ->restrictOnDelete();

    $table->string('role_requested', 50);

    $table->string('status', 50)
        ->default('draft')
        ->index();

    $table->string('submitted_name');
    $table->string('submitted_student_code', 50)->nullable()->index();

    $table->foreignId('submitted_faculty_id')
        ->nullable()
        ->constrained('faculties')
        ->nullOnDelete();

    $table->foreignId('submitted_academic_program_id')
        ->nullable()
        ->constrained('academic_programs')
        ->nullOnDelete();

    $table->string('submitted_cohort', 50)->nullable();
    $table->string('submitted_email');
    $table->text('submitted_note')->nullable();

    $table->foreignId('assigned_admin_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamp('expires_at')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'submitted_at']);
});
5.4. permission_grants
Schema::create('permission_grants', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained('users')
        ->restrictOnDelete();

    $table->string('permission_key', 100);

    $table->string('scope_type', 100)->nullable();
    $table->unsignedBigInteger('scope_id')->nullable();

    $table->string('status', 50)
        ->default('active')
        ->index();

    $table->foreignId('granted_by')
        ->constrained('users')
        ->restrictOnDelete();

    $table->text('reason')->nullable();

    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('revoked_at')->nullable();

    $table->foreignId('revoked_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->text('revoke_reason')->nullable();

    $table->timestamps();

    $table->index(['user_id', 'permission_key']);
    $table->index(['scope_type', 'scope_id']);
    $table->index(['status', 'expires_at']);
});
5.5. audit_logs
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();

    $table->foreignId('actor_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->string('actor_type', 100)->default('user');

    $table->string('action_key', 100)->index();

    $table->string('target_type', 100);
    $table->unsignedBigInteger('target_id')->nullable();

    $table->string('context_type', 100)->nullable();
    $table->unsignedBigInteger('context_id')->nullable();

    $table->longText('before_snapshot_json')->nullable();
    $table->longText('after_snapshot_json')->nullable();

    $table->text('reason')->nullable();
    $table->longText('metadata_json')->nullable();

    $table->string('ip_address', 100)->nullable();
    $table->text('user_agent')->nullable();

    $table->timestamp('created_at')->useCurrent();

    $table->index(['actor_id', 'created_at']);
    $table->index(['target_type', 'target_id']);
    $table->index(['context_type', 'context_id']);
});
6. Migration Review Checklist

Before merging a migration:

[ ] Migration name is clear.
[ ] Parent tables already exist.
[ ] Foreign keys are intentional.
[ ] Delete behavior is intentional.
[ ] Required fields are not nullable.
[ ] Nullable fields have business reason.
[ ] Status fields have default values where appropriate.
[ ] Indexes support expected queries.
[ ] Unique constraints are present.
[ ] Audit-sensitive data is not cascade deleted.
[ ] Private data is not exposed through public fields.
[ ] Migration can run from empty database.
[ ] Migration can rollback in development.
[ ] Raw SQL is documented if used.
7. Migration Anti-patterns

Do not:

- Create all tables in one giant migration.
- Add nullable everywhere.
- Use integer magic status.
- Use cascade delete on audit/history tables.
- Store arrays in JSON when relation table is needed.
- Skip indexes for timeline/feed/message queries.
- Expose private file path directly.
- Rename columns in production without migration plan.
- Drop columns with data without backup.
- Create table names like data, records, items.
- Put business logic into migration comments only.
8. Production Migration Rules

For production:

Never run destructive migration casually.
Never drop columns before deploy code no longer uses them.
Use expand-migrate-contract strategy for risky changes.
Backup before major schema changes.
Test migrations on staging first.
8.1. Expand-Migrate-Contract

Use this pattern:

1. Expand: add new nullable column/table.
2. Deploy code that writes both old and new fields if needed.
3. Migrate/backfill data.
4. Deploy code that reads new field.
5. Contract: remove old field after safe period.

Không “drop column rồi cầu nguyện”. Cầu nguyện không có rollback.

9. Final Rule

Migration phải làm database tiến hóa có kiểm soát.

Nếu migration khiến dev khác phải hỏi:

Cột này để làm gì?
Tại sao nullable?
Sao không có index?
Sao xóa user lại bay luôn audit?
Sao status là int?

Thì migration đó chưa đạt chuẩn.