<?php

namespace App\Actions\Mentor;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MentorRequestStatus;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\BlockedUser;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MentorRequest;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class CreateMentorConversationAction
{
    /**
     * Create a direct conversation for an accepted mentor request.
     *
     * Safety rules enforced:
     * - mentor_request.status must be accepted
     * - conversation can only be created once per mentor_request_id
     * - student_id and mentor_id must match the accepted MentorRequest
     * - both users must be active and not suspended/banned
     * - blocked users cannot create mentor conversations
     * - conversation stores mentor_request_id
     * - sends a system opening message with topic/context
     *
     * @throws \Exception
     */
    public function execute(MentorRequest $mentorRequest): Conversation
    {
        // Guard: must be accepted
        if ($mentorRequest->status !== MentorRequestStatus::Accepted) {
            throw new \Exception('Chỉ có thể tạo cuộc trò chuyện sau khi yêu cầu cố vấn được chấp nhận.');
        }

        $student = $mentorRequest->student;
        $mentor = $mentorRequest->mentor;

        // Guard: conversation already exists for this request
        if ($mentorRequest->conversation_id) {
            $existing = Conversation::find($mentorRequest->conversation_id);
            if ($existing) {
                return $existing;
            }
        }

        // Guard: both users must be active
        if (! $student->isActive()) {
            throw new \Exception('Tài khoản sinh viên không ở trạng thái hoạt động.');
        }

        if (! $mentor->isActive()) {
            throw new \Exception('Tài khoản mentor không ở trạng thái hoạt động.');
        }

        // Guard: blocked state check
        $isBlocked = BlockedUser::where(function ($q) use ($student, $mentor) {
            $q->where('blocker_id', $student->id)->where('blocked_id', $mentor->id);
        })->orWhere(function ($q) use ($student, $mentor) {
            $q->where('blocker_id', $mentor->id)->where('blocked_id', $student->id);
        })->exists();

        if ($isBlocked) {
            throw new \Exception('Không thể tạo cuộc trò chuyện do trạng thái chặn giữa hai tài khoản.');
        }

        // Guard: idempotency — check by mentor_request_id in conversations table
        $existing = Conversation::where('mentor_request_id', $mentorRequest->id)->first();
        if ($existing) {
            return $existing;
        }

        $lowId = min($student->id, $mentor->id);
        $highId = max($student->id, $mentor->id);

        return DB::transaction(function () use ($mentorRequest, $student, $mentor, $lowId, $highId) {
            // Find if there is already a direct conversation between these two users
            $conversation = Conversation::where('conversation_type', ConversationType::DIRECT)
                ->where('direct_user_low_id', $lowId)
                ->where('direct_user_high_id', $highId)
                ->first();

            if ($conversation) {
                // Update the mentor_request_id on the existing conversation
                $conversation->update([
                    'mentor_request_id' => $mentorRequest->id,
                ]);
            } else {
                // Create a new conversation
                $conversation = Conversation::create([
                    'conversation_type' => ConversationType::DIRECT,
                    'status' => ConversationStatus::ACTIVE,
                    'direct_user_low_id' => $lowId,
                    'direct_user_high_id' => $highId,
                    'mentor_request_id' => $mentorRequest->id,
                    'created_by' => $mentor->id,
                ]);
            }

            // Ensure student participant exists
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $student->id,
            ], [
                'participant_role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Ensure mentor participant exists
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $mentor->id,
            ], [
                'participant_role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Send system opening message
            $openingBody = "Yêu cầu cố vấn đã được chấp nhận. Hai bên có thể bắt đầu trao đổi về: {$mentorRequest->topic}.";

            $systemMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $mentor->id,
                'body' => $openingBody,
                'message_type' => MessageType::SYSTEM,
                'status' => MessageStatus::SENT,
            ]);

            // Update last message
            $conversation->update([
                'last_message_id' => $systemMessage->id,
                'last_message_at' => now(),
            ]);

            return $conversation;
        });
    }
}
