<?php

namespace App\Actions\Community;

use App\Models\CommunitySuggestion;
use App\Models\User;

class CreateCommunitySuggestionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): CommunitySuggestion
    {
        return CommunitySuggestion::create([
            'submitted_by' => $user->id,
            'suggested_name' => $data['suggested_name'],
            'community_type' => $data['community_type'],
            'join_policy' => $data['join_policy'] ?? 'approval_required',
            'visibility' => $data['visibility'] ?? 'public',
            'purpose' => $data['purpose'],
            'target_members' => $data['target_members'],
            'rules' => $data['rules'] ?? null,
            'related_faculty' => $data['related_faculty'] ?? null,
            'related_program_id' => $data['related_program_id'] ?? null,
            'proposed_owner_id' => $data['proposed_owner_id'] ?? $user->id,
            'status' => 'submitted',
        ]);
    }
}
