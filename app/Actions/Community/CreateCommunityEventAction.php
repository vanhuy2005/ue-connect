<?php

namespace App\Actions\Community;

use App\Enums\CommunityEventStatus;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCommunityEventAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, Community $community, array $data): CommunityEvent
    {
        return DB::transaction(function () use ($actor, $community, $data) {
            $slug = Str::slug($data['title']).'-'.Str::random(6);

            return CommunityEvent::create([
                'community_id' => $community->id,
                'created_by' => $actor->id,
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'event_type' => $data['event_type'] ?? 'in_person',
                'status' => $data['status'] ?? CommunityEventStatus::Draft->value,
                'visibility' => $data['visibility'] ?? 'community_members',
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'location' => $data['location'] ?? null,
                'online_link' => $data['online_link'] ?? null,
                'rsvp_required' => $data['rsvp_required'] ?? true,
                'rsvp_deadline' => $data['rsvp_deadline'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'waitlist_enabled' => $data['waitlist_enabled'] ?? false,
            ]);
        });
    }
}
