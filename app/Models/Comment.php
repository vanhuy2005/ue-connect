<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'status',
        'edited_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Get the post that owns the comment.
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who made the comment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment of this comment.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child replies of this comment.
     *
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the likes of this comment.
     *
     * @return HasMany<CommentLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Parse and render mentions in the comment body as XSS-safe HTML tags.
     */
    public static function parseMentions(string $body): string
    {
        $escapedBody = e($body);

        // Match @ followed by up to 5 words, excluding common punctuation marks
        if (preg_match_all('/@([^\s@#?!.,:;"]+(?:\s+[^\s@#?!.,:;"]+){0,4})/u', $body, $matches)) {
            $candidates = [];
            foreach ($matches[1] as $matchText) {
                $words = preg_split('/\s+/', trim($matchText));
                $currentPrefix = '';
                foreach ($words as $word) {
                    $currentPrefix = $currentPrefix === '' ? $word : $currentPrefix.' '.$word;
                    $candidates[] = $currentPrefix;
                }
            }

            $candidates = array_unique(array_filter($candidates));

            if (! empty($candidates)) {
                $users = User::where(function ($query) use ($candidates) {
                    $query->whereIn('name', $candidates)
                        ->orWhereHas('profile', function ($q) use ($candidates) {
                            $q->whereIn('display_name', $candidates);
                        });
                })->with('profile')->get();

                // Sort by name length descending to avoid partial matches
                $users = $users->sortByDesc(function ($user) {
                    return max(
                        strlen($user->name),
                        $user->profile?->display_name ? strlen($user->profile->display_name) : 0
                    );
                });

                foreach ($users as $user) {
                    $displayName = $user->profile?->display_name ?? $user->name;
                    $username = $user->name;

                    $profileUrl = route('profile.show', $user);

                    $mentionDisplay = '@'.$displayName;
                    $escapedMentionDisplay = e($mentionDisplay);
                    $replaceHtml = '<a href="'.$profileUrl.'" class="text-ue-brand font-semibold hover:underline" wire:navigate>'.$escapedMentionDisplay.'</a>';

                    $escapedBody = str_replace($escapedMentionDisplay, $replaceHtml, $escapedBody);

                    if ($displayName !== $username) {
                        $mentionUsername = '@'.$username;
                        $escapedMentionUsername = e($mentionUsername);
                        $replaceHtmlUser = '<a href="'.$profileUrl.'" class="text-ue-brand font-semibold hover:underline" wire:navigate>'.$escapedMentionUsername.'</a>';

                        $escapedBody = str_replace($escapedMentionUsername, $replaceHtmlUser, $escapedBody);
                    }
                }
            }
        }

        return $escapedBody;
    }
}
