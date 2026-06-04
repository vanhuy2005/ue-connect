<?php

namespace App\Actions\Community;

use App\Enums\CommunityEventRsvpStatus;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RsvpCommunityEventAction
{
    public function execute(User $user, CommunityEvent $event, CommunityEventRsvpStatus $status): CommunityEventRsvp
    {
        if (! $event->isPublished()) {
            throw ValidationException::withMessages([
                'event' => 'Sự kiện này không đang được mở đăng ký.',
            ]);
        }

        return DB::transaction(function () use ($user, $event, $status) {
            $existingRsvp = CommunityEventRsvp::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            $oldStatus = $existingRsvp?->status;

            $rsvp = CommunityEventRsvp::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                ['status' => $status->value]
            );

            // Sync cached counters
            $this->syncCounters($event, $oldStatus, $status, $existingRsvp === null);

            return $rsvp;
        });
    }

    private function syncCounters(
        CommunityEvent $event,
        ?CommunityEventRsvpStatus $oldStatus,
        CommunityEventRsvpStatus $newStatus,
        bool $isNew
    ): void {
        // Decrement old status counter
        if (! $isNew && $oldStatus !== null && $oldStatus !== $newStatus) {
            match ($oldStatus) {
                CommunityEventRsvpStatus::Going => $event->decrement('going_count'),
                CommunityEventRsvpStatus::Interested => $event->decrement('interested_count'),
                CommunityEventRsvpStatus::Waitlisted => $event->decrement('waitlist_count'),
                default => null,
            };
        }

        // Increment new status counter
        if ($isNew || $oldStatus !== $newStatus) {
            match ($newStatus) {
                CommunityEventRsvpStatus::Going => $event->increment('going_count'),
                CommunityEventRsvpStatus::Interested => $event->increment('interested_count'),
                CommunityEventRsvpStatus::Waitlisted => $event->increment('waitlist_count'),
                default => null,
            };
        }
    }
}
