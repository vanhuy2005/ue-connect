<?php

namespace App\Actions\Community;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityStatus;
use App\Enums\CommunitySuggestionStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunitySuggestion;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReviewCommunitySuggestionAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array{action: string, reason?: string, instruction?: string, community_name?: string}  $data
     */
    public function execute(User $actor, CommunitySuggestion $suggestion, array $data): void
    {
        $action = $data['action'] ?? '';

        $validActions = ['approve', 'reject', 'need_more_information', 'mark_duplicate', 'create_community'];
        if (! in_array($action, $validActions)) {
            throw ValidationException::withMessages([
                'action' => 'Hành động không hợp lệ.',
            ]);
        }

        if (in_array($action, ['reject', 'mark_duplicate']) && empty(trim($data['reason'] ?? ''))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do.',
            ]);
        }

        DB::transaction(function () use ($actor, $suggestion, $action, $data) {
            $before = $suggestion->toArray();

            match ($action) {
                'approve' => $suggestion->update([
                    'status' => CommunitySuggestionStatus::Approved->value,
                    'reviewed_by' => $actor->id,
                    'admin_reason' => $data['reason'] ?? null,
                    'admin_instruction' => $data['instruction'] ?? null,
                ]),
                'reject' => $suggestion->update([
                    'status' => CommunitySuggestionStatus::Rejected->value,
                    'reviewed_by' => $actor->id,
                    'admin_reason' => $data['reason'],
                ]),
                'need_more_information' => $suggestion->update([
                    'status' => CommunitySuggestionStatus::NeedMoreInformation->value,
                    'reviewed_by' => $actor->id,
                    'admin_instruction' => $data['instruction'] ?? null,
                ]),
                'mark_duplicate' => $suggestion->update([
                    'status' => CommunitySuggestionStatus::Duplicate->value,
                    'reviewed_by' => $actor->id,
                    'admin_reason' => $data['reason'],
                ]),
                'create_community' => $this->convertToCommunity($actor, $suggestion, $data),
            };

            $this->audit->log([
                'action' => "community_suggestion_{$action}",
                'target_type' => 'community_suggestion',
                'target_id' => $suggestion->id,
                'before_values' => $before,
                'after_values' => $suggestion->fresh()?->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function convertToCommunity(User $actor, CommunitySuggestion $suggestion, array $data): void
    {
        $community = Community::create([
            'name' => $data['community_name'] ?? $suggestion->suggested_name,
            'slug' => Str::slug($data['community_name'] ?? $suggestion->suggested_name),
            'type' => $suggestion->community_type,
            'join_policy' => $suggestion->join_policy ?? 'approval_required',
            'visibility' => $suggestion->visibility ?? 'public',
            'description' => $suggestion->purpose,
            'rules' => $suggestion->rules,
            'settings' => ['target_members' => $suggestion->target_members],
            'status' => CommunityStatus::Draft->value,
            'created_by' => $actor->id,
            'owner_id' => $suggestion->proposed_owner_id,
            'related_faculty' => $suggestion->related_faculty,
            'related_program_id' => $suggestion->related_program_id,
        ]);

        if ($community->owner_id) {
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => $community->owner_id,
                'role' => CommunityMemberRole::Owner->value,
                'status' => CommunityMemberStatus::Active->value,
                'joined_at' => now(),
            ]);
            $community->increment('members_count');
        }

        $suggestion->update([
            'status' => CommunitySuggestionStatus::ConvertedToCommunity->value,
            'reviewed_by' => $actor->id,
            'converted_community_id' => $community->id,
        ]);
    }
}
