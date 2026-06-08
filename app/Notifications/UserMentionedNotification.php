<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserMentionedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public User $sender
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->comment->post;

        return [
            'type' => 'user_mentioned',
            'comment_id' => $this->comment->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'title' => 'Nhắc đến bạn trong bình luận',
            'body' => $this->sender->name.' đã nhắc đến bạn trong một bình luận: '.Str::limit($this->comment->body, 50),
            'action_url' => route('posts.show', $post),
        ];
    }
}
