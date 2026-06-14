<?php

namespace App\Http\Controllers;

use App\Models\CareerUserPathway;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerUserPathwayCommentController extends Controller
{
    public function index(Request $request, CareerUserPathway $pathway)
    {
        $comments = $pathway->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('created_at', 'asc')
            ->get();

        return JsonResource::collection($comments);
    }

    public function store(Request $request, CareerUserPathway $pathway)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:career_user_pathway_comments,id',
        ]);

        if (! empty($validated['parent_id'])) {
            $parent = $pathway->comments()->find($validated['parent_id']);
            if (! $parent || $parent->parent_id !== null) {
                return response()->json(['message' => 'Cannot reply to a reply or invalid parent.'], 400);
            }
        }

        $comment = $pathway->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => 'active',
        ]);

        $pathway->increment('comments_count');

        $comment->load('user');

        return new JsonResource($comment);
    }
}
