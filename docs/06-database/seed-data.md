---
title: "Seed Data Strategy"
module: "06-database"
product: "UEConnect"
version: "1.0"
status: "approved-draft"
priority: "P0"
last_updated: "2026-05-26"
owner: "Database Architecture / Backend / QA"
depends_on:
  - "database-overview.md"
  - "schema.md"
  - "table-specifications.md"
  - "migration-strategy.md"
related:
  - "../03-product/feature-priority.md"
  - "../03-product/state-machines/STATE-MACHINE-SOURCE-OF-TRUTH.md"
---

# Seed Data Strategy

## 1. Purpose

File này định nghĩa cách seed data chuẩn Laravel cho UEConnect.

Seed data dùng cho:

- Local development.
- Testing.
- Demo product.
- QA flows.
- Admin review flows.
- UI state preview.
- Permission testing.
- Realtime/message testing.
- Moderation testing.

Seed data không phải nơi nhét dữ liệu thật của sinh viên. Database demo mà chứa dữ liệu thật là kiểu “tiện tay phạm privacy”, một thói quen nên được chôn cùng Windows XP.

---

# 2. Seed Data Principles

## 2.1. Safe by Default

Seed data phải là dữ liệu giả.

Không dùng:

```txt
real student data
real MSSV
real teacher data
real email cá nhân
real evidence image
real report content nhạy cảm
real private message
### 2.2. Deterministic Core Seeds

Core seed phải ổn định giữa các máy.

Core seeds gồm:

roles
permissions
faculties
academic_programs
mentor_topics
report_reasons
moderation_action_types
system_settings

Các seed này nên idempotent.

Nghĩa là chạy nhiều lần không nhân bản dữ liệu như gremlin gặp nước.

### 2.3. Demo Seeds Are Optional

Demo seeds có thể bật/tắt theo environment.

local: yes
testing: minimal
staging: controlled
production: only reference seeds
### 2.4. Production Seeds Are Strict

Production chỉ seed:

roles
permissions
reference data
system settings
report reasons
moderation action types

Không seed:

demo users
demo posts
demo messages
demo reports
demo evidence
### 2.5. Use Factories for Fake Data

Dùng Laravel factories cho:

users
profiles
posts
comments
messages
communities
mentor_requests
reports

Không hardcode 200 user bằng tay. Con người đã đau đủ rồi.

## 3. Seeder Structure

Recommended structure:

database/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── Reference/
│   │   ├── RoleSeeder.php
│   │   ├── PermissionSeeder.php
│   │   ├── FacultySeeder.php
│   │   ├── AcademicProgramSeeder.php
│   │   ├── MentorTopicSeeder.php
│   │   ├── ReportReasonSeeder.php
│   │   ├── ModerationActionTypeSeeder.php
│   │   └── SystemSettingSeeder.php
│   ├── Demo/
│   │   ├── DemoUserSeeder.php
│   │   ├── DemoVerificationSeeder.php
│   │   ├── DemoProfileSeeder.php
│   │   ├── DemoFeedSeeder.php
│   │   ├── DemoConnectionSeeder.php
│   │   ├── DemoMessagingSeeder.php
│   │   ├── DemoMentorSeeder.php
│   │   ├── DemoCommunitySeeder.php
│   │   ├── DemoModerationSeeder.php
│   │   └── DemoNotificationSeeder.php
│   └── Testing/
│       └── MinimalTestingSeeder.php
## 4. DatabaseSeeder Strategy
### 4.1. Recommended DatabaseSeeder
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            Reference\RoleSeeder::class,
            Reference\PermissionSeeder::class,
            Reference\FacultySeeder::class,
            Reference\AcademicProgramSeeder::class,
            Reference\MentorTopicSeeder::class,
            Reference\ReportReasonSeeder::class,
            Reference\ModerationActionTypeSeeder::class,
            Reference\SystemSettingSeeder::class,
        ]);

        if (app()->environment(['local'])) {
            $this->call([
                Demo\DemoUserSeeder::class,
                Demo\DemoProfileSeeder::class,
                Demo\DemoVerificationSeeder::class,
                Demo\DemoFeedSeeder::class,
                Demo\DemoConnectionSeeder::class,
                Demo\DemoMessagingSeeder::class,
                Demo\DemoMentorSeeder::class,
                Demo\DemoCommunitySeeder::class,
                Demo\DemoModerationSeeder::class,
                Demo\DemoNotificationSeeder::class,
            ]);
        }
    }
}
### 4.2. Production Rule

Production deployment should run only reference seeders.

Use command intentionally:

php artisan db:seed --class=Database\\Seeders\\Reference\\RoleSeeder
php artisan db:seed --class=Database\\Seeders\\Reference\\PermissionSeeder

Or use a production-safe seeder group.

Do not run demo seeders in production unless you enjoy explaining why “Nguyễn Văn Demo” is posting memes in the official platform.

## 5. Reference Seeds
### 5.1. Roles

Seed roles:

student
alumni
advisor
mentor
club_owner
club_manager
moderator
admin
super_admin

Example:

$roles = [
    'student',
    'alumni',
    'advisor',
    'mentor',
    'club_owner',
    'club_manager',
    'moderator',
    'admin',
    'super_admin',
];

foreach ($roles as $role) {
    Role::updateOrCreate(
        ['name' => $role, 'guard_name' => 'web'],
        ['guard_name' => 'web']
    );
}
### 5.2. Permissions

Global permissions:

review_verification
manage_users
manage_reports
moderate_content
manage_communities
manage_mentor_access
manage_system_announcements
manage_roles
manage_permissions
view_audit_logs
view_admin_dashboard

Scoped permissions:

manage_community_members
manage_community_posts
manage_community_resources
manage_community_settings
moderate_community_chat
### 5.3. Permission Mapping

Suggested mapping:

| Role | Permissions |
| --- | --- |
| student | basic app usage |
| alumni | basic app usage, mentor request eligibility |
| advisor | basic app usage, mentor request eligibility |
| mentor | mentor profile/request handling |
| club_manager | scoped community permissions only |
| moderator | moderate content, manage reports |
| admin | verification, users, reports, communities, mentors |
| super_admin | all permissions |

Important:

club_manager should not be global by default. Use permission_grants scoped by community_id.

### 5.4. Faculties

Seed HCMUE faculties/departments as reference data.

Minimum seed format:

[
    'name' => 'Khoa Công nghệ Thông tin',
    'slug' => 'cong-nghe-thong-tin',
    'status' => 'active',
]

Because faculty/program data may change, keep this seed easy to update.

### 5.5. Academic Programs

Each program belongs to a faculty.

Example shape:

[
    'faculty_slug' => 'cong-nghe-thong-tin',
    'name' => 'Công nghệ thông tin',
    'slug' => 'cong-nghe-thong-tin',
    'degree_level' => 'undergraduate',
    'status' => 'active',
]

Do not pretend HCMUE is only IT. Bạn đã bắt lỗi này rồi, và đúng. Trường có nhiều khoa, database cũng phải cư xử tử tế với tất cả.

### 5.6. Mentor Topics

Seed topics:

career_orientation
internship_preparation
academic_learning
research_method
teaching_method
classroom_management
frontend_development
backend_development
database_design
english_learning
chinese_learning
soft_skills
scholarship
graduation_project
### 5.7. Report Reasons

Seed report reasons:

spam
harassment
impersonation
sexual_or_dating_content
copyright_violation
personal_information_leak
scam_fraud
hate_or_offensive_language
politically_sensitive_content
other

Each reason should include Vietnamese label.

Example:

[
    'key' => 'spam',
    'label' => 'Spam',
    'description' => 'Nội dung lặp lại, quảng cáo rác hoặc gây phiền.',
    'status' => 'active',
    'sort_order' => 10,
]
### 5.8. Moderation Action Types

Seed actions:

dismiss
hide
delete
restore
warn
suspend
ban
escalate
resolve
### 5.9. System Settings

Seed default settings:

notification_retention_days = 7
max_evidence_files = 3
max_evidence_file_size_mb = 5
verification_resubmit_cooldown_minutes = 30
mentor_max_pending_requests_default = 10
post_image_max_files = 4
message_attachment_max_size_mb = 10

Example:

SystemSetting::updateOrCreate(
    ['key' => 'notification_retention_days'],
    [
        'value_json' => json_encode(['value' => 7]),
        'description' => 'Normal notification retention in days.',
    ]
);
## 6. Demo Users
### 6.1. Demo User Types

Create demo users for each important state:

unverified student
pending verification student
verified student with incomplete profile
verified ready student
verified alumni
academic advisor
approved mentor
club owner
club manager
moderator
admin
suspended user
banned user
### 6.2. Demo Email Pattern

Use fake HCMUE-like domain only for local:

student.demo01@hcmue.edu.vn
alumni.demo01@hcmue.edu.vn
advisor.demo01@hcmue.edu.vn
mentor.demo01@hcmue.edu.vn
admin.demo01@hcmue.edu.vn

Do not use real accounts.

### 6.3. Demo Password

For local only:

password

Never production.

### 6.4. Demo User Seeder Pattern
User::factory()->create([
    'email' => 'student.demo01@hcmue.edu.vn',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    'account_status' => 'active',
]);
## 7. Demo Verification Seeds

Create verification examples:

draft
pending_review
under_review
needs_more_information
approved
rejected
conflict
suspicious
expired

For each:

User.
Verification request.
Evidence metadata.
Review actions if reviewed.
Audit log if admin action happened.

Do not store real evidence files. Use placeholder media path:

demo/evidence/student-card-placeholder.pdf

Better: fake media metadata without actual private document.

### 7.1. Required Demo Cases
student pending with 2 evidence files
student need more information with admin instruction
student rejected with reason
student approved
student conflict with duplicate MSSV scenario
suspicious evidence case
## 8. Demo Profile Seeds

Create profiles for:

student
alumni
advisor
mentor

Each should include:

display_name
avatar placeholder
bio
faculty/program
privacy settings
skills/interests
### 8.1. Profile States

Seed:

incomplete
ready
restricted
hidden

This helps test account gate and UI state.

## 9. Demo Feed Seeds

Create:

normal student post
post with image
community post
admin/system announcement
hidden by moderation post
removed post placeholder
post with comments
post with nested replies
### 9.1. Feed Rules

Demo posts should include:

Vietnamese content
HCMUE campus context
non-sensitive content
no real student data

Do not create dating-flavored posts. UEConnect không cần seed “ai đó tìm một nửa”. Không, cảm ơn.

## 10. Demo Connection Seeds

Create:

pending greeting
accepted greeting
declined greeting
expired greeting
active connection
blocked relationship
discovery pass

Each accepted greeting should have:

connection
conversation
notification
## 11. Demo Messaging Seeds

Create:

direct conversation
mentor request conversation
community chat conversation
message with image attachment placeholder
message edited
message deleted placeholder
read receipt
typing indicator not stored
blocked conversation state

Use safe message content.

Do not seed sensitive private messages. Demo privacy violations are still privacy violations, chỉ là mặc áo “demo”.

## 12. Demo Notification Seeds

Create notification examples:

verification_approved
verification_rejected
verification_need_more_info
greeting_received
greeting_accepted
message_received
mentor_request_update
moderation_action
community_update
system_announcement

Create read/unread/expired states.

Notification previews must be privacy-safe.

## 13. Demo Mentor Seeds

Create:

approved mentor profile
pending mentor access request
rejected mentor access request
student mentor request pending
mentor request needs more information
mentor request accepted
mentor request declined
mentor paused availability
mentor full pending requests

Mentor topics must be seeded first.

## 14. Demo Community Seeds

Create:

public active club
private approval-required community
official faculty community
career community
suspended community
archived community

Each community can have:

owner
manager
members
join requests
channels
resources
posts
### 14.1. Community Roles

Seed scoped permission grants for:

club manager in one community
community moderator in one community
resource manager in one community

Do not make every club manager global admin. That would be “phân quyền” theo kiểu phát chìa khóa tổng cho người trông xe.

## 15. Demo Safety / Moderation Seeds

Create reports for targets:

profile
post
comment
message
community
mentor_request
verification_evidence

Report statuses:

submitted
queued
in_review
resolved
dismissed
duplicate

Moderation cases:

low priority
normal priority
high priority
urgent priority

Moderation actions:

dismiss
hide
delete
restore
warn
suspend
ban

Every moderation action must have reason.

## 16. Demo Admin / Audit Seeds

Create audit logs for:

verification approved
verification rejected
community suspended
permission granted
moderation hide action
mentor access approved
account suspended
system announcement published

Do not make audit logs editable in seed logic.

## 17. Factory Strategy
### 17.1. Required Factories

Create factories for:

UserFactory
ProfileFactory
StudentProfileFactory
AlumniProfileFactory
AdvisorProfileFactory
MediaFileFactory
VerificationRequestFactory
VerificationEvidenceFactory
PostFactory
CommentFactory
GreetingFactory
ConnectionFactory
ConversationFactory
MessageFactory
NotificationFactory
MentorProfileFactory
MentorRequestFactory
CommunityFactory
CommunityMemberFactory
ReportFactory
ModerationCaseFactory
AuditLogFactory
AnalyticsEventFactory
### 17.2. Factory States

Example user factory states:

public function verified(): static
{
    return $this->state(fn () => [
        'email_verified_at' => now(),
        'account_status' => 'active',
    ]);
}

public function suspended(): static
{
    return $this->state(fn () => [
        'account_status' => 'suspended',
        'account_status_reason' => 'Demo suspended account.',
    ]);
}

Example verification states:

public function pendingReview(): static
{
    return $this->state(fn () => [
        'status' => 'pending_review',
        'submitted_at' => now(),
    ]);
}

public function approved(): static
{
    return $this->state(fn () => [
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);
}
## 18. Idempotent Seeder Pattern

Use updateOrCreate for reference data.

Faculty::updateOrCreate(
    ['slug' => 'cong-nghe-thong-tin'],
    [
        'name' => 'Khoa Công nghệ Thông tin',
        'description' => 'Demo/reference faculty.',
        'status' => 'active',
    ]
);

Use factories for demo data, but avoid duplicate on repeated seeding.

Options:

truncate demo tables in local before seeding
use fixed demo emails/slugs
use updateOrCreate for named demo records

For local reset:

php artisan migrate:fresh --seed

Production should never rely on migrate:fresh. Nếu production cần fresh database, vấn đề không còn là kỹ thuật nữa, đó là nghi lễ chia tay.

## 19. Environment Rules
### 19.1. Local

Allowed:

reference seeds
demo users
demo posts
demo messages
demo communities
demo reports
demo admin
### 19.2. Testing

Allowed:

minimal reference seeds
small deterministic test users
small deterministic test permissions

Avoid heavy demo seeds in automated tests.

### 19.3. Staging

Allowed:

reference seeds
controlled demo data
fake accounts only
### 19.4. Production

Allowed:

roles
permissions
faculties
academic_programs
mentor_topics
report_reasons
moderation_action_types
system_settings
initial super admin if explicitly controlled

Forbidden:

demo posts
demo messages
demo reports
demo evidence
fake moderation cases
## 20. Initial Super Admin

Production may need initial super admin.

Recommended options:

Option A: Artisan Command

Preferred:

php artisan ueconnect:create-super-admin

Prompt:

email
password
display_name
reason

This avoids hardcoding admin credentials in seeder.

Option B: Environment-based Seeder

Allowed only if carefully controlled:

INITIAL_ADMIN_EMAIL=
INITIAL_ADMIN_PASSWORD=

Seeder reads env and creates admin once.

Do not commit real admin credentials. Một câu rất hiển nhiên, nhưng GitHub vẫn là nghĩa trang API key vì con người thích thử vận may.

## 21. Seed QA Checklist

Before approving seed data:

[ ] Reference seeds are idempotent.
[ ] Demo seeds run only in local/staging.
[ ] Production seeds do not create demo content.
[ ] No real student data.
[ ] No real private evidence.
[ ] No real messages.
[ ] Roles are seeded.
[ ] Permissions are seeded.
[ ] Scoped permission examples exist.
[ ] Faculties/programs are seeded.
[ ] Mentor topics are seeded.
[ ] Report reasons are seeded.
[ ] Moderation actions are seeded.
[ ] Demo users cover account states.
[ ] Demo verification covers review states.
[ ] Demo feed covers post states.
[ ] Demo messaging covers realtime states.
[ ] Demo community covers membership states.
[ ] Demo moderation covers queue/action states.
[ ] Demo notifications cover read/unread/expired states.
[ ] Seeders can run after migrate:fresh.
[ ] Seeders do not leak secrets.
## 22. Recommended Artisan Commands
Local reset
php artisan migrate:fresh --seed
Seed reference only
php artisan db:seed --class=Database\\Seeders\\Reference\\RoleSeeder
php artisan db:seed --class=Database\\Seeders\\Reference\\PermissionSeeder
php artisan db:seed --class=Database\\Seeders\\Reference\\FacultySeeder
Seed demo only
php artisan db:seed --class=Database\\Seeders\\Demo\\DemoUserSeeder
Testing
php artisan migrate:fresh --env=testing
php artisan db:seed --class=Database\\Seeders\\Testing\\MinimalTestingSeeder --env=testing
## 23. Final Rule

Seed data phải giúp dev kiểm thử nghiệp vụ thật:

verification pending
profile incomplete
feed empty
message unread
community suspended
mentor paused
report queued
moderation hidden
permission scoped
notification expired
account banned
