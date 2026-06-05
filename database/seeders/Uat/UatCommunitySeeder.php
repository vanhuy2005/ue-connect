<?php

namespace Database\Seeders\Uat;

use App\Enums\CommunityEventRsvpStatus;
use App\Enums\CommunityEventStatus;
use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityJoinRequestStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityResourceStatus;
use App\Enums\CommunityResourceType;
use App\Enums\CommunityStatus;
use App\Enums\CommunitySuggestionStatus;
use App\Enums\CommunityType;
use App\Enums\CommunityVisibility;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\AcademicProgram;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\CommunitySuggestion;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MediaFile;
use App\Models\Message;
use App\Models\PermissionGrant;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UatCommunitySeeder extends Seeder
{
    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Community> */
    private array $communities = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->resolveUsers();
            $this->seedCommunities();
            $this->seedMemberships();
            $this->seedScopedPermissionGrants();
            $this->seedJoinRequests();
            $this->seedResources();
            $this->seedEvents();
            $this->seedCommunityPosts();
            $this->seedCommunityChats();
            $this->seedSuggestions();
            $this->seedReportsAndAudit();
        });
    }

    private function resolveUsers(): void
    {
        foreach ([
            'admin' => 'admin@teacher.hcmue.edu.vn',
            'manager' => 'student.cntt@student.hcmue.edu.vn',
            'moderator' => 'student.math@student.hcmue.edu.vn',
            'member' => 'student@student.hcmue.edu.vn',
            'member2' => 'student2@student.hcmue.edu.vn',
            'pending' => 'student.english@student.hcmue.edu.vn',
            'resource_manager' => 'student.literature@student.hcmue.edu.vn',
            'banned_member' => 'blocked.student@student.hcmue.edu.vn',
            'teacher' => 'teacher.mentor@teacher.hcmue.edu.vn',
        ] as $key => $email) {
            $this->users[$key] = User::where('email', $email)->firstOrFail();
        }
    }

    private function seedCommunities(): void
    {
        $program = AcademicProgram::where('slug', 'cong-nghe-thong-tin')->firstOrFail();

        foreach ([
            'it_club' => [
                'name' => 'CLB Công nghệ Giáo dục',
                'slug' => 'clb-cong-nghe-giao-duc',
                'type' => CommunityType::Club,
                'visibility' => CommunityVisibility::Public,
                'join_policy' => CommunityJoinPolicy::ApprovalRequired,
                'status' => CommunityStatus::Active,
                'short_description' => 'Không gian UAT cho workshop, project và mentoring công nghệ.',
                'description' => 'CLB demo dành cho sinh viên yêu thích xây dựng sản phẩm giáo dục bằng Laravel, AI và thiết kế trải nghiệm.',
            ],
            'math_private' => [
                'name' => 'Nhóm học Toán ứng dụng',
                'slug' => 'nhom-hoc-toan-ung-dung',
                'type' => CommunityType::AcademicGroup,
                'visibility' => CommunityVisibility::Private,
                'join_policy' => CommunityJoinPolicy::InviteOnly,
                'status' => CommunityStatus::Active,
                'short_description' => 'Nhóm private để UAT locked state và membership.',
                'description' => 'Nhóm học tập riêng cho các chủ đề toán ứng dụng và thống kê.',
            ],
            'official_faculty' => [
                'name' => 'Thông tin Khoa CNTT',
                'slug' => 'thong-tin-khoa-cntt',
                'type' => CommunityType::OfficialAnnouncementGroup,
                'visibility' => CommunityVisibility::OfficialOnly,
                'join_policy' => CommunityJoinPolicy::AdminOnly,
                'status' => CommunityStatus::Active,
                'short_description' => 'Kênh thông báo chính thức dùng cho UAT admin/community.',
                'description' => 'Community chính thức mô phỏng thông báo khoa và tài nguyên học vụ.',
            ],
            'career_group' => [
                'name' => 'Định hướng thực tập sư phạm và công nghệ',
                'slug' => 'dinh-huong-thuc-tap-su-pham-cong-nghe',
                'type' => CommunityType::CareerGroup,
                'visibility' => CommunityVisibility::Restricted,
                'join_policy' => CommunityJoinPolicy::Open,
                'status' => CommunityStatus::Active,
                'short_description' => 'Career community để UAT open join và mentor/resource flow.',
                'description' => 'Cộng đồng chia sẻ kinh nghiệm thực tập, portfolio và định hướng nghề nghiệp.',
            ],
            'suspended_club' => [
                'name' => 'CLB Tài nguyên cần rà soát',
                'slug' => 'clb-tai-nguyen-can-ra-soat',
                'type' => CommunityType::Club,
                'visibility' => CommunityVisibility::Public,
                'join_policy' => CommunityJoinPolicy::ApprovalRequired,
                'status' => CommunityStatus::Suspended,
                'short_description' => 'Community bị tạm khóa để UAT state suspended.',
                'description' => 'Community demo có hành động bị khóa để kiểm thử policy và UI locked state.',
            ],
            'archived_project' => [
                'name' => 'Dự án học kỳ cũ',
                'slug' => 'du-an-hoc-ky-cu',
                'type' => CommunityType::ProjectGroup,
                'visibility' => CommunityVisibility::Hidden,
                'join_policy' => CommunityJoinPolicy::Closed,
                'status' => CommunityStatus::Archived,
                'short_description' => 'Community archived để UAT read-only/history state.',
                'description' => 'Dự án học kỳ đã kết thúc, chỉ còn dùng để tham khảo lịch sử.',
            ],
        ] as $key => $data) {
            $this->communities[$key] = Community::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'visibility' => $data['visibility'],
                    'join_policy' => $data['join_policy'],
                    'status' => $data['status'],
                    'owner_id' => $this->users['manager']->id,
                    'created_by' => $this->users['admin']->id,
                    'related_faculty' => 'Khoa Công nghệ Thông tin',
                    'related_program_id' => $program->id,
                    'rules' => 'Tôn trọng thành viên, không spam, chỉ chia sẻ tài nguyên hợp lệ.',
                    'settings' => ['demo_seed' => true],
                    'suspended_reason' => $data['status'] === CommunityStatus::Suspended ? 'UAT moderation lock for unsafe resource review.' : null,
                    'suspended_safe_reason' => $data['status'] === CommunityStatus::Suspended ? 'Một số hoạt động của cộng đồng đã bị tạm khóa theo quy định UEConnect.' : null,
                    'suspended_at' => $data['status'] === CommunityStatus::Suspended ? now()->subDays(2) : null,
                    'archived_at' => $data['status'] === CommunityStatus::Archived ? now()->subDays(20) : null,
                ]
            );
        }
    }

    private function seedMemberships(): void
    {
        $this->member('it_club', 'manager', CommunityMemberRole::Owner, CommunityMemberStatus::Active, 'Chủ nhiệm CLB');
        $this->member('it_club', 'resource_manager', CommunityMemberRole::Manager, CommunityMemberStatus::Active, 'Quản lý tài nguyên');
        $this->member('it_club', 'moderator', CommunityMemberRole::Moderator, CommunityMemberStatus::Active, 'Điều phối nội dung');
        $this->member('it_club', 'member', CommunityMemberRole::Member, CommunityMemberStatus::Active);
        $this->member('it_club', 'member2', CommunityMemberRole::Member, CommunityMemberStatus::Active);
        $this->member('it_club', 'pending', CommunityMemberRole::Member, CommunityMemberStatus::Pending);
        $this->member('it_club', 'banned_member', CommunityMemberRole::Member, CommunityMemberStatus::BannedFromCommunity);

        $this->member('math_private', 'teacher', CommunityMemberRole::Owner, CommunityMemberStatus::Active, 'Cố vấn học thuật');
        $this->member('math_private', 'member2', CommunityMemberRole::Member, CommunityMemberStatus::Muted);

        $this->member('career_group', 'member', CommunityMemberRole::Member, CommunityMemberStatus::Active);
        $this->member('career_group', 'member2', CommunityMemberRole::Member, CommunityMemberStatus::Active);
        $this->member('suspended_club', 'member', CommunityMemberRole::Member, CommunityMemberStatus::Active);
    }

    private function seedScopedPermissionGrants(): void
    {
        foreach ([
            ['resource_manager', 'manage_community', 'it_club', 'Quản lý vận hành CLB Công nghệ Giáo dục.'],
            ['resource_manager', 'manage_community_members', 'it_club', 'Duyệt thành viên trong phạm vi CLB.'],
            ['resource_manager', 'manage_community_resources', 'it_club', 'Duyệt tài nguyên học tập hợp lệ.'],
            ['moderator', 'moderate_community_chat', 'it_club', 'Điều phối chat cộng đồng.'],
        ] as [$userKey, $permission, $communityKey, $reason]) {
            PermissionGrant::updateOrCreate(
                [
                    'user_id' => $this->users[$userKey]->id,
                    'permission_key' => $permission,
                    'scope_type' => 'community',
                    'scope_id' => $this->communities[$communityKey]->id,
                ],
                [
                    'granted_by' => $this->users['admin']->id,
                    'reason' => '[UAT] '.$reason,
                    'starts_at' => now()->subDays(4),
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedJoinRequests(): void
    {
        foreach ([
            ['it_club', 'pending', CommunityJoinRequestStatus::Pending, null],
            ['it_club', 'member2', CommunityJoinRequestStatus::Approved, 'Bạn đã được duyệt tham gia CLB.'],
            ['math_private', 'member', CommunityJoinRequestStatus::Rejected, 'Nhóm đang ưu tiên sinh viên trong lớp học phần.'],
        ] as [$communityKey, $userKey, $status, $reason]) {
            CommunityJoinRequest::updateOrCreate(
                ['community_id' => $this->communities[$communityKey]->id, 'user_id' => $this->users[$userKey]->id],
                [
                    'join_reason' => 'Mình muốn tham gia để học hỏi và đóng góp cho cộng đồng.',
                    'status' => $status,
                    'reviewed_by' => $status === CommunityJoinRequestStatus::Pending ? null : $this->users['resource_manager']->id,
                    'review_reason' => $reason,
                    'reviewed_at' => $status === CommunityJoinRequestStatus::Pending ? null : now()->subDays(2),
                ]
            );
        }
    }

    private function seedResources(): void
    {
        $file = MediaFile::updateOrCreate(
            ['checksum' => 'uat-community-resource-roadmap'],
            [
                'owner_id' => $this->users['resource_manager']->id,
                'disk' => 'local',
                'path' => 'demo/community/resources/laravel-roadmap.pdf',
                'original_name' => 'laravel-roadmap-uat.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size_bytes' => 256000,
                'visibility' => 'private',
                'file_category' => 'community_resource',
                'metadata_json' => ['demo_seed' => true],
            ]
        );

        foreach ([
            ['it_club', 'Lộ trình Laravel cho project học kỳ', CommunityResourceType::Guide, CommunityResourceStatus::Published, $file->id, null, null],
            ['it_club', 'Checklist bản quyền tài nguyên học tập', CommunityResourceType::Document, CommunityResourceStatus::PendingReview, null, null, null],
            ['it_club', 'Link tài liệu chưa đủ nguồn gốc', CommunityResourceType::Link, CommunityResourceStatus::Rejected, null, 'https://example.com/uat-resource-review', 'Nguồn chia sẻ chưa đủ rõ để công khai.'],
        ] as [$communityKey, $title, $type, $status, $fileId, $url, $rejectionReason]) {
            CommunityResource::updateOrCreate(
                ['community_id' => $this->communities[$communityKey]->id, 'title' => $title],
                [
                    'description' => 'Tài nguyên UAT dùng để kiểm thử review, publish và reject state.',
                    'resource_type' => $type,
                    'file_id' => $fileId,
                    'url' => $url,
                    'category' => 'uat',
                    'copyright_attestation' => true,
                    'status' => $status,
                    'submitted_by' => $this->users['member']->id,
                    'approved_by' => $status === CommunityResourceStatus::Published ? $this->users['resource_manager']->id : null,
                    'approved_at' => $status === CommunityResourceStatus::Published ? now()->subDays(1) : null,
                    'rejection_reason' => $rejectionReason,
                ]
            );
        }
    }

    private function seedEvents(): void
    {
        $event = CommunityEvent::updateOrCreate(
            ['slug' => 'workshop-laravel-uat'],
            [
                'community_id' => $this->communities['it_club']->id,
                'created_by' => $this->users['resource_manager']->id,
                'title' => 'Workshop Laravel UAT',
                'description' => 'Buổi demo quy trình tạo project Laravel an toàn và có kiểm thử.',
                'event_type' => 'hybrid',
                'status' => CommunityEventStatus::Published,
                'visibility' => 'community_members',
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(7)->addHours(2),
                'location' => 'Phòng tự học HCMUE',
                'online_link' => 'https://example.com/ueconnect-uat-workshop',
                'rsvp_required' => true,
                'rsvp_deadline' => now()->addDays(5),
                'capacity' => 40,
                'waitlist_enabled' => true,
                'going_count' => 1,
                'interested_count' => 1,
            ]
        );

        CommunityEventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $this->users['member']->id],
            ['status' => CommunityEventRsvpStatus::Going, 'note' => 'UAT RSVP going.']
        );

        CommunityEventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $this->users['member2']->id],
            ['status' => CommunityEventRsvpStatus::Interested, 'note' => 'UAT RSVP interested.']
        );
    }

    private function seedCommunityPosts(): void
    {
        foreach ([
            ['it_club', 'manager', 'announcement', 'Tuần này CLB có buổi review project Laravel. Thành viên chuẩn bị câu hỏi về migration, policy và test nhé.', true],
            ['it_club', 'member', 'question', 'Mọi người có checklist nào để kiểm tra quyền truy cập trước khi demo không?', false],
            ['suspended_club', 'member', 'system_update', '[UAT] Bài viết trong community đang bị tạm khóa để kiểm tra trạng thái read-only.', false],
        ] as [$communityKey, $userKey, $type, $body, $pinned]) {
            Post::updateOrCreate(
                ['body' => '[seed:community-'.$communityKey.'-'.$type.'] '.$body],
                [
                    'user_id' => $this->users[$userKey]->id,
                    'scope_type' => 'community',
                    'scope_id' => $this->communities[$communityKey]->id,
                    'community_post_type' => $type,
                    'pinned_at' => $pinned ? now()->subDay() : null,
                    'pinned_by' => $pinned ? $this->users['manager']->id : null,
                    'visibility' => PostVisibility::COMMUNITY,
                    'status' => PostStatus::PUBLISHED,
                    'published_at' => now()->subHours(8),
                ]
            );
        }
    }

    private function seedCommunityChats(): void
    {
        $community = $this->communities['it_club'];
        $conversation = Conversation::updateOrCreate(
            ['conversation_type' => ConversationType::COMMUNITY_CHAT->value, 'source_type' => 'community', 'source_id' => $community->id],
            [
                'title' => 'Chat '.$community->name,
                'status' => ConversationStatus::ACTIVE,
                'created_by' => $this->users['manager']->id,
                'last_message_at' => now()->subMinutes(20),
            ]
        );

        foreach (['manager', 'resource_manager', 'moderator', 'member', 'member2'] as $userKey) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $this->users[$userKey]->id],
                ['participant_role' => $userKey === 'manager' ? 'owner' : 'member', 'status' => 'active', 'joined_at' => now()->subDays(3)]
            );
        }

        $lastMessage = null;
        foreach ([
            ['manager', 'Chào cả nhà, kênh chat này dùng để trao đổi nhanh về workshop và tài nguyên CLB.'],
            ['member', 'Mình đã đọc checklist tài nguyên, phần bản quyền rất hữu ích.'],
            ['moderator', 'Mọi người nhớ không chia sẻ tài liệu không rõ nguồn trong chat nhé.'],
        ] as [$userKey, $body]) {
            $lastMessage = Message::updateOrCreate(
                ['conversation_id' => $conversation->id, 'sender_id' => $this->users[$userKey]->id, 'body' => $body],
                ['message_type' => MessageType::TEXT, 'status' => MessageStatus::SENT]
            );
        }

        if ($lastMessage) {
            $conversation->update(['last_message_id' => $lastMessage->id, 'last_message_at' => $lastMessage->created_at]);
        }
    }

    private function seedSuggestions(): void
    {
        CommunitySuggestion::updateOrCreate(
            ['suggested_name' => 'Nhóm đọc sách giáo dục mở', 'submitted_by' => $this->users['member']->id],
            [
                'community_type' => CommunityType::InterestGroup->value,
                'join_policy' => CommunityJoinPolicy::ApprovalRequired->value,
                'visibility' => CommunityVisibility::Public->value,
                'purpose' => 'Tạo không gian đọc và thảo luận sách giáo dục bằng nội dung hợp lệ.',
                'target_members' => 'Sinh viên, alumni và giảng viên quan tâm giáo dục.',
                'rules' => 'Không chia sẻ bản scan sách có bản quyền.',
                'related_faculty' => 'Khoa Khoa học Giáo dục',
                'proposed_owner_id' => $this->users['member']->id,
                'status' => CommunitySuggestionStatus::Submitted,
            ]
        );
    }

    private function seedReportsAndAudit(): void
    {
        Report::updateOrCreate(
            [
                'reporter_id' => $this->users['member2']->id,
                'target_type' => 'community',
                'target_id' => $this->communities['suspended_club']->id,
            ],
            [
                'reason' => ReportReason::INAPPROPRIATE_CONTENT,
                'description' => 'UAT report cho community bị tạm khóa.',
                'status' => ReportStatus::PENDING,
            ]
        );

        foreach ([
            ['community.created', 'community', $this->communities['it_club']->id, 'Admin tạo community UAT chính thức.'],
            ['community.suspended', 'community', $this->communities['suspended_club']->id, 'Admin tạm khóa community UAT để kiểm thử locked state.'],
            ['permission.granted', 'permission_grant', PermissionGrant::where('permission_key', 'manage_community')->where('scope_id', $this->communities['it_club']->id)->value('id'), 'Admin cấp quyền quản lý scoped community.'],
        ] as [$action, $targetType, $targetId, $reason]) {
            if (! $targetId) {
                continue;
            }

            AuditLog::updateOrCreate(
                ['action_key' => $action, 'target_type' => $targetType, 'target_id' => $targetId],
                [
                    'actor_id' => $this->users['admin']->id,
                    'actor_type' => 'user',
                    'before_values' => ['demo_seed' => true],
                    'after_values' => ['demo_seed' => true],
                    'reason' => '[UAT] '.$reason,
                    'metadata' => ['demo_seed' => true],
                    'created_at' => now(),
                ]
            );
        }

        $this->refreshCounters();
    }

    private function member(string $communityKey, string $userKey, CommunityMemberRole $role, CommunityMemberStatus $status, ?string $roleLabel = null): void
    {
        CommunityMember::updateOrCreate(
            ['community_id' => $this->communities[$communityKey]->id, 'user_id' => $this->users[$userKey]->id],
            [
                'role' => $role,
                'role_label' => $roleLabel,
                'status' => $status,
                'joined_at' => $status === CommunityMemberStatus::Active ? now()->subDays(5) : null,
                'removed_at' => $status === CommunityMemberStatus::BannedFromCommunity ? now()->subDay() : null,
                'removed_by' => $status === CommunityMemberStatus::BannedFromCommunity ? $this->users['manager']->id : null,
                'remove_reason' => $status === CommunityMemberStatus::BannedFromCommunity ? 'UAT community-specific ban.' : null,
            ]
        );
    }

    private function refreshCounters(): void
    {
        foreach ($this->communities as $community) {
            $community->update([
                'members_count' => $community->activeMembers()->count(),
                'post_count' => $community->posts()->count(),
                'resource_count' => $community->resources()->count(),
            ]);
        }
    }
}
