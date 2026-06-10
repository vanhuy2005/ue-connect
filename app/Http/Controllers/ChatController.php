<?php

namespace App\Http\Controllers;

use App\AI\HcmueChatbot\Chat\HcmueChatService;
use App\Models\AiAnswer;
use App\Models\AiFeedback;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected HcmueChatService $chatService) {}

    /**
     * Create a new chat session.
     */
    public function createSession(Request $request): JsonResponse
    {
        $user = $request->user();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => $request->input('title', 'Cuộc hội thoại mới'),
        ]);

        return response()->json([
            'session_id' => $session->id,
            'title' => $session->title,
            'created_at' => $session->created_at,
        ], 201);
    }

    /**
     * List chat sessions for the authenticated user.
     */
    public function listSessions(Request $request): JsonResponse
    {
        $sessions = ChatSession::where('user_id', $request->user()->id)
            ->with(['questions' => fn ($q) => $q->orderBy('created_at', 'desc')->limit(1)])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'created_at' => $s->created_at,
                'last_question' => $s->questions->first()?->original_question,
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Send a message in a chat session.
     */
    public function sendMessage(Request $request, ChatSession $session): JsonResponse
    {
        // Ensure user owns this session
        if ($session->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'message' => 'required|string|min:2|max:2000',
        ]);

        $result = $this->chatService->chat(
            $data['message'],
            $session,
            $request->user()
        );

        return response()->json([
            'answer' => $result['answer'],
            'sources' => $result['sources'],
            'route' => $result['route'],
            'intent' => $result['intent'],
            'requires_clarification' => $result['requires_clarification'],
            'question_id' => $result['question_id'],
            'answer_id' => $result['answer_id'],
        ]);
    }

    /**
     * Submit feedback on an answer.
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'answer_id' => 'required|integer|exists:ai_answers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $answer = AiAnswer::findOrFail($data['answer_id']);

        // Ensure answer belongs to user's session
        $sessionUserId = $answer->question?->session?->user_id;
        if ($sessionUserId !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        AiFeedback::updateOrCreate(
            [
                'answer_id' => $data['answer_id'],
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Cảm ơn phản hồi của bạn!']);
    }
}
