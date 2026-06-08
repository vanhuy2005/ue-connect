<?php

use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Post;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Enums\MessageType;
use App\Enums\MessageStatus;
use App\Enums\ConnectionStatus;
use App\Enums\ConversationStatus;
use App\Actions\Messaging\SendMessage;
use App\Actions\Messaging\ReplyToMessage;
use App\Actions\Messaging\RecallMessage;
use App\Actions\Messaging\PinMessage;
use App\Actions\Messaging\UnpinMessage;
use App\Actions\Messaging\ForwardMessage;
use App\Actions\Messaging\ReportMessage;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\UnblockUser;
use App\Support\Navigation\UserNavigationMetrics;
use App\Models\ConversationUserSetting;
use App\Models\ConversationPinnedMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Support\Media\MediaUrlResolver;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    use \Livewire\WithFileUploads;

    public ?int $selectedConversationId = null;
    public string $newMessageBody = '';
    public string $conversationSearch = '';
    public ?string $feedbackMessage = null;

    // Attachment properties
    public $attachmentFile = null;
    public ?array $messageAttachment = null;

    // Advanced controls properties
    public string $activeInboxTab = 'all'; // all, restricted
    public ?int $replyingToMessageId = null;
    public ?int $forwardingMessageId = null;
    public bool $showForwardModal = false;
    public string $forwardSearch = '';
    public bool $showNicknameModal = false;
    public string $nicknameInput = '';
    public bool $showReportModal = false;
    public ?int $reportingMessageId = null;
    public string $reportReason = 'harassment';
    public string $reportDescription = '';

    protected $listeners = ['refreshMessages' => '$refresh'];

    public function mount(?Conversation $activeConversation = null): void
    {
        if ($activeConversation && $activeConversation->id) {
            Gate::authorize('view', $activeConversation);
            $this->selectedConversationId = $activeConversation->id;
            $this->markAsRead($activeConversation->id);
        }
    }

    /**
     * Handle temporary message attachment upload.
     */
    public function updatedAttachmentFile(): void
    {
        try {
            $this->validate([
                'attachmentFile' => 'image|max:5120', // 5MB limit
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('attachmentFile', $e->validator->errors()->first());
            $this->attachmentFile = null;
            return;
        }

        $storeAction = app(\App\Actions\Media\StoreTemporaryMediaAction::class);

        try {
            $media = $storeAction->execute(Auth::user(), $this->attachmentFile, 'message_attachment', ['visibility' => 'private']);
            
            $this->messageAttachment = [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'url' => MediaUrlResolver::thumbnailUrl($media),
            ];
        } catch (\Exception $e) {
            $this->addError('attachmentFile', 'Lỗi tải ảnh đính kèm: ' . $e->getMessage());
        }

        $this->attachmentFile = null;
    }

    /**
     * Remove the current attachment draft.
     */
    public function removeAttachment(): void
    {
        if ($this->messageAttachment) {
            $media = \App\Models\Media::find($this->messageAttachment['id']);
            if ($media) {
                app(\App\Actions\Media\DeleteMediaAction::class)->execute($media);
            }
            $this->messageAttachment = null;
        }
    }

    /**
     * Select a conversation and load its thread.
     */
    public function selectConversation(int $convoId): void
    {
        $conversation = Conversation::findOrFail($convoId);
        Gate::authorize('view', $conversation);

        $this->selectedConversationId = $convoId;
        $this->newMessageBody = '';
        $this->replyingToMessageId = null;
        $this->removeAttachment();
        $this->markAsRead($convoId);
    }

    /**
     * Deselect conversation (Back to list on mobile).
     */
    public function deselectConversation(): void
    {
        $this->selectedConversationId = null;
        $this->newMessageBody = '';
        $this->replyingToMessageId = null;
        $this->removeAttachment();
    }

    /**
     * Refresh component messages.
     */
    public function refreshMessages(): void
    {
        // This triggers a Livewire component re-render
    }

    /**
     * Send a text or image message inside the selected conversation.
     */
    public function submitMessage(
        SendMessage $sendMessage, 
        ReplyToMessage $replyToMessageAction, 
        ?string $body = null, 
        ?int $mediaId = null, 
        ?string $clientMessageId = null
    ): array {
        $body = $body ?? $this->newMessageBody;
        $mediaId = $mediaId ?? ($this->messageAttachment ? $this->messageAttachment['id'] : null);
        
        $trimmedBody = trim((string) $body);
        if (empty($trimmedBody) && !$mediaId) {
            return ['status' => 'error', 'message' => 'Nội dung tin nhắn không được để trống.'];
        }

        if (! $this->selectedConversationId) {
            return ['status' => 'error', 'message' => 'Không có cuộc trò chuyện nào được chọn.'];
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            Gate::authorize('view', $conversation);
            
            // Check if restricted
            if ($this->isRestricted($conversation)) {
                return ['status' => 'error', 'message' => 'Không thể gửi tin nhắn trong cuộc trò chuyện bị giới hạn.'];
            }

            // Deduplication: If client_message_id is provided, check if it already exists
            if ($clientMessageId) {
                $existingMessage = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', Auth::id())
                    ->where('client_message_id', $clientMessageId)
                    ->first();
                if ($existingMessage) {
                    return [
                        'id' => $existingMessage->id,
                        'client_message_id' => $existingMessage->client_message_id,
                        'conversation_id' => $existingMessage->conversation_id,
                        'sender_id' => $existingMessage->sender_id,
                        'body' => $existingMessage->body,
                        'message_type' => $existingMessage->message_type instanceof MessageType ? $existingMessage->message_type->value : $existingMessage->message_type,
                        'created_at' => $existingMessage->created_at->toISOString(),
                        'status' => 'sent',
                    ];
                }
            }

            $message = null;
            if ($this->replyingToMessageId) {
                $replyToMsg = Message::findOrFail($this->replyingToMessageId);
                $message = $replyToMessageAction->execute(Auth::user(), $conversation, $replyToMsg, [
                    'body' => $trimmedBody,
                    'media_id' => $mediaId,
                    'client_message_id' => $clientMessageId,
                ]);
                $this->replyingToMessageId = null;
            } else {
                $message = $sendMessage->execute(Auth::user(), $conversation, [
                    'body' => $trimmedBody,
                    'media_id' => $mediaId,
                    'client_message_id' => $clientMessageId,
                ]);
            }

            $this->newMessageBody = '';
            $this->messageAttachment = null;
            $this->markAsRead($conversation->id);
            $this->dispatch('scroll-bottom');

            return [
                'id' => $message->id,
                'client_message_id' => $message->client_message_id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'message_type' => $message->message_type instanceof MessageType ? $message->message_type->value : $message->message_type,
                'created_at' => $message->created_at->toISOString(),
                'status' => 'sent',
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Start active reply mode for a message.
     */
    public function startReply(int $messageId): void
    {
        $this->replyingToMessageId = $messageId;
    }

    /**
     * Cancel active reply mode.
     */
    public function cancelReply(): void
    {
        $this->replyingToMessageId = null;
    }

    /**
     * Recall own message (acts as upgraded deleteMessage).
     */
    public function deleteMessage(int $messageId, RecallMessage $recallMessage): void
    {
        $this->recallMessage($messageId, $recallMessage);
    }

    /**
     * Recall own message action.
     */
    public function recallMessage(int $messageId, RecallMessage $recallMessage): void
    {
        try {
            $message = Message::findOrFail($messageId);
            $recallMessage->execute(Auth::user(), $message);
            $this->feedbackMessage = 'Đã thu hồi tin nhắn.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Pin message action.
     */
    public function pinMessage(int $messageId, PinMessage $pinMessage): void
    {
        try {
            $message = Message::findOrFail($messageId);
            $pinMessage->execute(Auth::user(), $message);
            $this->feedbackMessage = 'Đã ghim tin nhắn.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unpin message action.
     */
    public function unpinMessage(int $messageId, UnpinMessage $unpinMessage): void
    {
        try {
            $message = Message::findOrFail($messageId);
            $unpinMessage->execute(Auth::user(), $message);
            $this->feedbackMessage = 'Đã bỏ ghim tin nhắn.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Open forward modal dialog.
     */
    public function openForwardModal(int $messageId): void
    {
        $this->forwardingMessageId = $messageId;
        $this->showForwardModal = true;
        $this->forwardSearch = '';
    }

    /**
     * Execute forward action.
     */
    public function forwardMessage(int $targetConversationId, ForwardMessage $forwardMessage): void
    {
        if (!$this->forwardingMessageId) {
            return;
        }

        try {
            $sourceMessage = Message::findOrFail($this->forwardingMessageId);
            $targetConversation = Conversation::findOrFail($targetConversationId);
            $forwardMessage->execute(Auth::user(), $sourceMessage, $targetConversation);
            
            $this->showForwardModal = false;
            $this->forwardingMessageId = null;
            $this->feedbackMessage = 'Đã chuyển tiếp tin nhắn thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Open report message modal.
     */
    public function openReportModal(int $messageId): void
    {
        $this->reportingMessageId = $messageId;
        $this->showReportModal = true;
        $this->reportReason = 'harassment';
        $this->reportDescription = '';
    }

    /**
     * Submit message report action.
     */
    public function submitReport(ReportMessage $reportMessageAction): void
    {
        if (!$this->reportingMessageId) {
            return;
        }

        try {
            $message = Message::findOrFail($this->reportingMessageId);
            $reportMessageAction->execute(Auth::user(), $message, [
                'reason' => $this->reportReason,
                'description' => $this->reportDescription,
            ]);

            $this->showReportModal = false;
            $this->reportingMessageId = null;
            $this->feedbackMessage = 'Báo cáo tin nhắn thành công. Nội dung vi phạm đã được gửi tới Ban kiểm duyệt.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Test compatibility: report a specific message directly.
     */
    public function reportMessage(int $messageId, ReportMessage $reportMessageAction): void
    {
        try {
            $message = Message::findOrFail($messageId);
            $reportMessageAction->execute(Auth::user(), $message, [
                'reason' => 'harassment',
                'description' => 'Reported directly via test call.',
            ]);
            $this->feedbackMessage = 'Báo cáo tin nhắn thành công. Nội dung vi phạm đã được gửi tới Ban kiểm duyệt.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Check if the conversation is restricted.
     */
    public function isRestricted(Conversation $conversation): bool
    {
        $sender = Auth::user();
        $recipient = $conversation->getRecipientFor($sender);
        if (! $recipient) {
            return true;
        }

        // 1. Check if blocked
        $isBlocked = BlockedUser::where(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $sender->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $sender->id);
        })->exists();

        if ($isBlocked) {
            return true;
        }

        // Mentor conversations bypass connection check
        if ($conversation->mentor_request_id) {
            // But respect archived status (completed requests)
            return $conversation->status !== ConversationStatus::ACTIVE;
        }

        // 2. Check if connected
        $userOneId = min($sender->id, $recipient->id);
        $userTwoId = max($sender->id, $recipient->id);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        return ! $isConnected;
    }

    /**
     * Check if the current user blocked the other participant.
     */
    public function isBlockedByMe(Conversation $conversation): bool
    {
        $recipient = $conversation->getRecipientFor(Auth::user());
        if (!$recipient) {
            return false;
        }

        return BlockedUser::where('blocker_id', Auth::id())
            ->where('blocked_id', $recipient->id)
            ->exists();
    }

    /**
     * Block or unblock the recipient user directly from the chat.
     */
    public function toggleBlock(BlockUser $blockUser, UnblockUser $unblockUser): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            $recipient = $conversation->getRecipientFor(Auth::user());
            if ($recipient) {
                if ($this->isBlockedByMe($conversation)) {
                    $unblockUser->execute(Auth::user(), $recipient);
                    $this->feedbackMessage = 'Đã bỏ chặn người dùng này thành công.';
                } else {
                    $blockUser->execute(Auth::user(), $recipient, [
                        'reason' => 'Blocked via Chat interface.',
                    ]);
                    $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
                }
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Test compatibility: block current recipient.
     */
    public function blockRecipient(BlockUser $blockUser): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            $recipient = $conversation->getRecipientFor(Auth::user());
            if ($recipient) {
                $blockUser->execute(Auth::user(), $recipient, [
                    'reason' => 'Blocked via Chat interface.',
                ]);
                $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Open local nickname customization dialog.
     */
    public function openNicknameModal(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }
        $conversation = Conversation::findOrFail($this->selectedConversationId);
        $recipient = $conversation->getRecipientFor(Auth::user());
        if (!$recipient) {
            return;
        }
        
        $settings = $conversation->getUserSettingsFor(Auth::user());
        $this->nicknameInput = $settings->nickname ?? '';
        $this->showNicknameModal = true;
    }

    /**
     * Save local nickname overrides.
     */
    public function saveNickname(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }
        $conversation = Conversation::findOrFail($this->selectedConversationId);
        $recipient = $conversation->getRecipientFor(Auth::user());
        if (!$recipient) {
            return;
        }

        ConversationUserSetting::updateOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
        ], [
            'target_user_id' => $recipient->id,
            'nickname' => empty(trim($this->nicknameInput)) ? null : trim($this->nicknameInput),
        ]);

        $this->showNicknameModal = false;
        $this->feedbackMessage = 'Đã cập nhật biệt danh thành công.';
    }

    /**
     * Toggle mute notifications.
     */
    public function toggleMute(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }
        $conversation = Conversation::findOrFail($this->selectedConversationId);
        $settings = $conversation->getUserSettingsFor(Auth::user());

        if ($settings->muted_until && $settings->muted_until->isFuture()) {
            $settings->update(['muted_until' => null]);
            $this->feedbackMessage = 'Đã bật lại thông báo cho cuộc trò chuyện này.';
        } else {
            $settings->update(['muted_until' => now()->addYears(10)]);
            $this->feedbackMessage = 'Đã tắt thông báo cho cuộc trò chuyện này.';
        }
    }

    /**
     * Toggle restrict status local.
     */
    public function toggleRestrict(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }
        $conversation = Conversation::findOrFail($this->selectedConversationId);
        $settings = $conversation->getUserSettingsFor(Auth::user());

        $newStatus = !$settings->is_restricted;
        $settings->update(['is_restricted' => $newStatus]);

        if ($newStatus) {
            $this->feedbackMessage = 'Đã chuyển cuộc trò chuyện vào mục Hạn chế.';
            $this->selectedConversationId = null;
        } else {
            $this->feedbackMessage = 'Đã bỏ hạn chế cuộc trò chuyện này.';
        }
    }

    /**
     * Locally soft-delete conversation thread.
     */
    public function deleteConversationLocally(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            ConversationUserSetting::updateOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
            ], [
                'deleted_at' => now(),
            ]);

            $this->selectedConversationId = null;
            $this->feedbackMessage = 'Đã xóa đoạn chat thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Check block state specifically.
     */
    public function isBlockedState(Conversation $conversation): bool
    {
        $sender = Auth::user();
        $recipient = $conversation->getRecipientFor($sender);
        if (! $recipient) {
            return true;
        }

        return BlockedUser::where(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $sender->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $sender->id);
        })->exists();
    }

    /**
     * Mark selected conversation as read.
     */
    protected function markAsRead(int $convoId): void
    {
        $conversation = Conversation::findOrFail($convoId);
        Gate::authorize('view', $conversation);

        ConversationParticipant::where('conversation_id', $convoId)
            ->where('user_id', Auth::id())
            ->update([
                'last_read_at' => now(),
            ]);

        app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
    }

    /**
     * Check if post is visible to current user (privacy gate).
     */
    public function canViewPost(?Post $post): bool
    {
        if (! $post) {
            return false;
        }

        return Gate::allows('view', $post);
    }

    public function with(): array
    {
        $userId = Auth::id();

        // 1. Fetch a bounded conversation listing ordered directly in DB.
        $conversationQuery = Conversation::whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->when($this->activeInboxTab === 'restricted', function ($query) use ($userId): void {
                $query->whereHas('conversationUserSettings', function ($settingsQuery) use ($userId): void {
                    $settingsQuery->where('user_id', $userId)
                        ->where('is_restricted', true);
                });
            })
            ->when($this->activeInboxTab !== 'restricted', function ($query) use ($userId): void {
                $query->where(function ($visibilityQuery) use ($userId): void {
                    $visibilityQuery->whereDoesntHave('conversationUserSettings', function ($settingsQuery) use ($userId): void {
                        $settingsQuery->where('user_id', $userId);
                    })
                    ->orWhereHas('conversationUserSettings', function ($settingsQuery) use ($userId): void {
                        $settingsQuery->where('user_id', $userId)
                            ->where('is_restricted', false);
                    });
                });
            })
            ->with([
                'participants.user.profile',
                'lastMessage',
                'latestMessage',
                'conversationUserSettings' => function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC');

        if (empty($this->conversationSearch)) {
            $conversationQuery->limit(30);
        }

        $conversations = $conversationQuery
            ->get()
            ->map(function ($convo) use ($userId) {
                $recipient = $convo->getRecipientFor(Auth::user());
                $myParticipant = $convo->participants->firstWhere('user_id', $userId);
                $settings = $convo->conversationUserSettings->first();

                $isRestricted = $settings ? (bool) $settings->is_restricted : false;
                $deletedAt = $settings ? $settings->deleted_at : null;
                $nickname = ($settings && $settings->nickname) ? $settings->nickname : null;

                $lastMsg = $convo->lastMessage ?: $convo->latestMessage;
                if ($deletedAt && $lastMsg && ! $lastMsg->created_at->gt($deletedAt)) {
                    $lastMsg = null;
                }

                // Hide locally soft-deleted conversation if no new messages received since deletion, unless it is currently selected
                if ($deletedAt && !$lastMsg && $convo->id !== $this->selectedConversationId) {
                    return null;
                }

                $isUnread = $lastMsg && $myParticipant &&
                            ($myParticipant->last_read_at === null || $myParticipant->last_read_at->lt($lastMsg->created_at));

                return [
                    'id' => $convo->id,
                    'recipient' => $recipient,
                    'last_message' => $lastMsg,
                    'is_unread' => $isUnread,
                    'is_restricted' => $isRestricted,
                    'is_mentor' => (bool) $convo->mentor_request_id,
                    'nickname' => $nickname,
                    'updated_at' => $lastMsg ? $lastMsg->created_at : ($convo->last_message_at ?: $convo->created_at),
                ];
            })
            ->filter()
            ->values();

        if (!empty($this->conversationSearch)) {
            $search = mb_strtolower($this->conversationSearch);
            $conversations = $conversations->filter(function ($convo) use ($search) {
                $name = $convo['nickname'] ?: ($convo['recipient'] ? $convo['recipient']->name : '');
                return str_contains(mb_strtolower($name), $search);
            })->values();
        }

        // 2. Fetch active conversation details if selected (limited to latest 50 messages)
        $activeConvo = null;
        $messages = collect();
        $isRestricted = false;
        $isBlocked = false;
        $isBlockedByMe = false;
        $isMuted = false;
        $pinnedMessages = collect();
        $recipientNickname = null;

        if ($this->selectedConversationId) {
            $activeConvo = Conversation::with(['participants.user.profile', 'conversationUserSettings' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])->findOrFail($this->selectedConversationId);
            Gate::authorize('view', $activeConvo);

            $userSetting = $activeConvo->conversationUserSettings->first();
            $deletedAt = $userSetting ? $userSetting->deleted_at : null;
            $isMuted = $userSetting && $userSetting->muted_until && $userSetting->muted_until->isFuture();
            $recipientNickname = $userSetting ? $userSetting->nickname : null;

            $messagesQuery = Message::where('conversation_id', $this->selectedConversationId)
                ->withTrashed()
                ->with(['sender.profile', 'sharedPost.user.profile', 'replyTo.sender', 'media.variants']);

            if ($deletedAt) {
                $messagesQuery->where('created_at', '>', $deletedAt);
            }

            $messages = $messagesQuery->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $isRestricted = $userSetting ? (bool) $userSetting->is_restricted : false;
            $isBlocked = $this->isBlockedState($activeConvo);
            $isBlockedByMe = $this->isBlockedByMe($activeConvo);

            $pinnedMessages = ConversationPinnedMessage::where('conversation_id', $this->selectedConversationId)
                ->with(['message.sender', 'pinnedBy'])
                ->latest()
                ->get();
        }

        // Load active connections for forward modal
        $forwardConversations = collect();
        if ($this->showForwardModal) {
            $forwardConversations = Conversation::whereHas('participants', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with(['participants.user.profile'])
                ->get()
                ->map(function ($convo) {
                    return [
                        'id' => $convo->id,
                        'recipient' => $convo->getRecipientFor(Auth::user()),
                    ];
                })
                ->filter(function ($convo) {
                    return $convo['recipient'] !== null;
                });

            if (!empty($this->forwardSearch)) {
                $search = mb_strtolower($this->forwardSearch);
                $forwardConversations = $forwardConversations->filter(function ($convo) use ($search) {
                    return str_contains(mb_strtolower($convo['recipient']->name), $search);
                });
            }
            $forwardConversations = $forwardConversations->values();
        }

        return [
            'conversations' => $conversations,
            'activeConvo' => $activeConvo,
            'messages' => $messages,
            'isRestricted' => $isRestricted,
            'isBlocked' => $isBlocked,
            'isBlockedByMe' => $isBlockedByMe,
            'isMuted' => $isMuted,
            'recipientNickname' => $recipientNickname,
            'pinnedMessages' => $pinnedMessages,
            'forwardConversations' => $forwardConversations,
        ];
    }
}; ?>

@push('scripts')
    @vite('resources/js/realtime.js')
@endpush

<div 
    class="h-[calc(100dvh-64px)] lg:h-dvh flex overflow-hidden bg-slate-50 relative"
    x-data="{
        initUserChannel() {
            if (window.Echo) {
                window.Echo.private('user.{{ Auth::id() }}')
                    .listen('.ConversationUpdated', (e) => {
                        @this.call('refreshMessages');
                    });
            }
        }
    }"
    x-init="initUserChannel()"
>
    {{-- Feedback Message Toast --}}
    @if ($feedbackMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-slate-900 text-white rounded-xl shadow-xl px-4 py-3 border border-slate-800 flex items-center gap-3"
        >
            <x-ui.icon name="info" size="sm" class="text-ue-brand flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- Left Pane: Conversation List --}}
    <div class="w-full lg:w-80 border-r border-slate-150 bg-white flex flex-col flex-shrink-0 {{ $selectedConversationId ? 'hidden lg:flex' : 'flex' }}" wire:poll.visible.60s>
        {{-- Header --}}
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <h1 class="text-sm font-bold text-slate-800 tracking-tight">Hộp thư</h1>
            <a 
                href="{{ route('connections.index') }}" 
                class="text-xxs font-bold text-ue-brand hover:text-ue-brand-dark transition-colors flex items-center gap-1"
                aria-label="Xem danh sách bạn bè"
            >
                <x-ui.icon name="users" size="xs" />
                Bạn bè
            </a>
        </div>

        {{-- Inbox Tabs --}}
        <div class="flex border-b border-slate-100 flex-shrink-0 bg-slate-50/30">
            <button 
                type="button" 
                wire:click="$set('activeInboxTab', 'all')"
                class="flex-1 py-2 text-center text-xxs font-bold border-b-2 transition-all {{ $activeInboxTab === 'all' ? 'border-ue-brand text-ue-brand font-extrabold' : 'border-transparent text-slate-450 hover:text-slate-650' }}"
            >
                Hộp thư
            </button>
            <button 
                type="button" 
                wire:click="$set('activeInboxTab', 'restricted')"
                class="flex-1 py-2 text-center text-xxs font-bold border-b-2 transition-all {{ $activeInboxTab === 'restricted' ? 'border-ue-brand text-ue-brand font-extrabold' : 'border-transparent text-slate-450 hover:text-slate-650' }}"
            >
                Hạn chế
            </button>
        </div>

        {{-- Search Conversations --}}
        <div class="p-3 border-b border-slate-100 bg-slate-50/50 flex-shrink-0">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-ui.icon name="search" size="xs" class="text-slate-400" />
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="conversationSearch"
                    placeholder="Tìm cuộc trò chuyện..."
                    class="w-full pl-9 pr-4 py-1.5 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-white placeholder-slate-400 text-slate-700"
                />
            </div>
        </div>

        {{-- List --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <div wire:loading.delay wire:target="activeInboxTab,conversationSearch" class="p-4 space-y-3">
                @for ($i = 0; $i < 6; $i++)
                    <div class="flex items-center gap-3">
                        <div class="ue-skeleton-avatar ue-skeleton"></div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="ue-skeleton-line ue-skeleton w-1/2"></div>
                            <div class="ue-skeleton-line ue-skeleton w-4/5"></div>
                        </div>
                    </div>
                @endfor
            </div>

            @forelse ($conversations as $convo)
                <button
                    type="button"
                    wire:click="selectConversation({{ $convo['id'] }})"
                    wire:loading.attr="disabled"
                    wire:target="selectConversation({{ $convo['id'] }})"
                    class="w-full p-4 flex items-center justify-between hover:bg-slate-50/80 transition-colors text-left {{ $selectedConversationId === $convo['id'] ? 'bg-slate-50 font-bold' : '' }}"
                >
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        @if ($convo['recipient'])
                            <x-ui.avatar :user="$convo['recipient']" size="md" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h2 class="text-xs font-bold text-slate-800 flex items-center gap-1 truncate">
                                        {{ $convo['nickname'] ?: $convo['recipient']->name }}
                                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                    </h2>
                                    <span class="text-[9px] text-slate-400 font-semibold flex-shrink-0">
                                        {{ $convo['updated_at']->diffForHumans(null, true) }}
                                    </span>
                                </div>
                                @if ($convo['last_message'])
                                    <p class="text-xxs text-slate-500 font-medium truncate mt-0.5 {{ $convo['is_unread'] ? 'text-slate-800 font-bold' : '' }}">
                                        @if ($convo['last_message']->message_type === MessageType::SHARED_POST)
                                            <span class="text-ue-brand font-semibold">[Bài viết chia sẻ]</span>
                                        @else
                                            {{ $convo['last_message']->body }}
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xxs text-slate-350 italic font-semibold mt-0.5">Bắt đầu cuộc trò chuyện.</p>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <x-ui.icon name="user" size="sm" class="text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-700">UEer không xác định</p>
                                    <p class="text-xxs text-slate-400">Thành viên đã rời cuộc trò chuyện.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Unread Badge --}}
                    @if ($convo['is_unread'])
                        <span class="w-2.5 h-2.5 bg-ue-brand rounded-full ml-3 flex-shrink-0 shadow-3xs" aria-label="Tin nhắn mới"></span>
                    @endif
                </button>
            @empty
                <div class="py-12 px-4 flex flex-col items-center justify-center text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center border border-dashed border-slate-200">
                        <x-ui.icon name="message-square" size="md" class="text-slate-300" />
                    </div>
                    <h3 class="text-xs font-bold text-slate-700">Hộp thư chưa có tin nhắn</h3>
                    <p class="text-xxs text-slate-400 max-w-[200px]">Sau khi kết nối thành công, bạn có thể gửi tin nhắn riêng tư tại đây.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Pane: Conversation Details --}}
    <div 
        class="flex-1 bg-slate-50 flex flex-col min-w-0 {{ ! $selectedConversationId ? 'hidden lg:flex' : 'flex' }}"
        x-data="{
            pendingMessages: [],
            recipientTyping: false,
            typingTimeout: null,
            isCurrentlyTyping: false,
            submitForm() {
                let body = @this.newMessageBody;
                let attachment = @this.messageAttachment;
                if (!body.trim() && !attachment) return;

                let clientMsgId = 'client-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                
                // Clear inputs immediately on frontend
                @this.newMessageBody = '';
                @this.messageAttachment = null;

                // Push optimistic message
                let tempMessage = {
                    client_message_id: clientMsgId,
                    body: body,
                    imageUrl: attachment ? attachment.url : null,
                    mediaId: attachment ? attachment.id : null,
                    status: 'sending',
                    error: null
                };
                this.pendingMessages.push(tempMessage);
                this.scrollToBottom();

                let mediaId = attachment ? attachment.id : null;
                @this.call('submitMessage', body, mediaId, clientMsgId)
                    .then(response => {
                        if (response && response.status === 'sent') {
                            this.pendingMessages = this.pendingMessages.filter(pm => pm.client_message_id !== clientMsgId);
                        } else if (response && response.status === 'error') {
                            let msg = this.pendingMessages.find(pm => pm.client_message_id === clientMsgId);
                            if (msg) {
                                msg.status = 'failed';
                                msg.error = response.message;
                            }
                        }
                    })
                    .catch(err => {
                        let msg = this.pendingMessages.find(pm => pm.client_message_id === clientMsgId);
                        if (msg) {
                            msg.status = 'failed';
                            msg.error = 'Không thể kết nối máy chủ.';
                        }
                    });
            },
            retryMessage(clientMsgId) {
                let msg = this.pendingMessages.find(pm => pm.client_message_id === clientMsgId);
                if (!msg) return;

                msg.status = 'sending';
                msg.error = null;

                let body = msg.body;
                let mediaId = msg.mediaId || null;

                @this.call('submitMessage', body, mediaId, clientMsgId)
                    .then(response => {
                        if (response && response.status === 'sent') {
                            this.pendingMessages = this.pendingMessages.filter(pm => pm.client_message_id !== clientMsgId);
                        } else if (response && response.status === 'error') {
                            msg.status = 'failed';
                            msg.error = response.message;
                        }
                    })
                    .catch(err => {
                        msg.status = 'failed';
                        msg.error = 'Không thể kết nối máy chủ.';
                    });
            },
            reconcile() {
                let serverIdsEl = document.getElementById('server-message-client-ids');
                if (serverIdsEl) {
                    let serverIds = JSON.parse(serverIdsEl.getAttribute('data-ids') || '[]');
                    this.pendingMessages = this.pendingMessages.filter(pm => !serverIds.includes(pm.client_message_id));
                }
            },
            scrollToBottom() {
                setTimeout(() => {
                    let container = document.getElementById('message-container');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 50);
            },
            handleTyping() {
                if (!window.Echo || !@this.selectedConversationId) return;
                
                if (!this.isCurrentlyTyping) {
                    this.isCurrentlyTyping = true;
                    window.Echo.private('conversation.' + @this.selectedConversationId)
                        .whisper('typing', {
                            typing: true,
                            userId: {{ Auth::id() }}
                        });
                }
                
                clearTimeout(this.typingTimeout);
                this.typingTimeout = setTimeout(() => {
                    this.isCurrentlyTyping = false;
                    if (window.Echo && @this.selectedConversationId) {
                        window.Echo.private('conversation.' + @this.selectedConversationId)
                            .whisper('typing', {
                                typing: false,
                                userId: {{ Auth::id() }}
                            });
                    }
                }, 2000);
            },
            setupChannel(convoId) {
                if (!window.Echo || !convoId) return;
                
                let channel = window.Echo.private('conversation.' + convoId);
                
                channel.listen('.MessageSent', (e) => {
                    if (e.sender_id != {{ Auth::id() }}) {
                        @this.call('refreshMessages');
                    } else {
                        // Sync multiple tabs of same user
                        let serverIdsEl = document.getElementById('server-message-client-ids');
                        let serverIds = JSON.parse(serverIdsEl ? serverIdsEl.getAttribute('data-ids') || '[]' : '[]');
                        if (e.client_message_id && !serverIds.includes(e.client_message_id)) {
                            @this.call('refreshMessages');
                        }
                    }
                });
                
                channel.listenForWhisper('typing', (e) => {
                    if (e.userId != {{ Auth::id() }}) {
                        this.recipientTyping = e.typing;
                    }
                });
            }
        }"
        x-init="
            if ($wire.selectedConversationId) {
                setupChannel($wire.selectedConversationId);
            }
            
            $watch('$wire.selectedConversationId', (newId, oldId) => {
                pendingMessages = [];
                recipientTyping = false;
                scrollToBottom();
                
                if (oldId && window.Echo) {
                    window.Echo.leave('conversation.' + oldId);
                }
                
                if (newId) {
                    setupChannel(newId);
                }
            });
            $watch('pendingMessages', () => scrollToBottom());
            
            // Listen to livewire load/update
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => {
                    $nextTick(() => reconcile());
                });
            });
        "
    >
        @if ($selectedConversationId && $activeConvo)
            @php
                $recipient = $activeConvo->getRecipientFor(Auth::user());
            @endphp
            {{-- Header --}}
            <div class="h-14 px-4 bg-white border-b border-slate-150 flex items-center justify-between flex-shrink-0 z-10">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    {{-- Back button for mobile --}}
                    <button
                        type="button"
                        wire:click="deselectConversation"
                        class="lg:hidden text-slate-500 hover:text-slate-700 transition-colors p-1 -ml-1"
                        aria-label="Quay lại danh sách tin nhắn"
                    >
                        <x-ui.icon name="arrow-left" size="sm" />
                    </button>

                    @if ($recipient)
                        <a href="{{ route('profile.show', $recipient) }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $recipient->name }}">
                            <x-ui.avatar :user="$recipient" size="sm" />
                        </a>
                        <div class="min-w-0">
                            @if ($recipientNickname)
                                <a href="{{ route('profile.show', $recipient) }}" class="text-xs font-bold text-slate-800 truncate flex items-center gap-1 leading-tight hover:text-ue-brand hover:underline">
                                    {{ $recipientNickname }} <span class="text-[9px] text-slate-400 font-normal">({{ $recipient->name }})</span>
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                </a>
                            @else
                                <a href="{{ route('profile.show', $recipient) }}" class="text-xs font-bold text-slate-800 truncate flex items-center gap-1 leading-tight hover:text-ue-brand hover:underline">
                                    {{ $recipient->name }}
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                </a>
                            @endif
                            @if ($recipient->profile && $recipient->profile->faculty)
                                <p class="text-[9px] text-slate-400 font-semibold truncate leading-none mt-0.5">{{ $recipient->profile->faculty }}</p>
                            @endif
                        </div>
                    @else
                        <h2 class="text-xs font-bold text-slate-800">Thành viên UEConnect</h2>
                    @endif
                </div>

                @if ($recipient)
                    <div class="flex items-center gap-1.5" x-data="{ openMenu: false }" @click.away="openMenu = false">
                        <div class="relative">
                            <x-ui.icon-button
                                icon="more-vertical"
                                label="Tùy chọn cuộc trò chuyện"
                                variant="ghost"
                                size="sm"
                                @click="openMenu = !openMenu"
                                class="text-slate-400 hover:text-slate-600 focus:ring-1 focus:ring-slate-100"
                            />

                            <div
                                x-show="openMenu"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-30 w-44"
                                style="display: none;"
                            >
                                <a
                                    href="{{ route('profile.show', $recipient) }}"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors"
                                >
                                    <x-ui.icon name="user" size="xs" class="text-slate-400" />
                                    Xem hồ sơ
                                </a>
                                <button
                                    type="button"
                                    wire:click="openNicknameModal"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors"
                                >
                                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                    Biệt danh
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleMute"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleMute"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors disabled:opacity-60"
                                >
                                    <x-ui.icon name="bell" size="xs" class="text-slate-400" />
                                    {{ $isMuted ? 'Bật thông báo' : 'Tắt thông báo' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleRestrict"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleRestrict"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors disabled:opacity-60"
                                >
                                    <x-ui.icon name="shield" size="xs" class="text-slate-400" />
                                    {{ $isRestricted ? 'Bỏ hạn chế' : 'Hạn chế' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleBlock"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleBlock"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors disabled:opacity-60"
                                >
                                    <x-ui.icon name="shield-x" size="xs" class="text-slate-400" />
                                    {{ $isBlockedByMe ? 'Bỏ chặn' : 'Chặn thành viên' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteConversationLocally"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteConversationLocally"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-red-650 hover:bg-red-50 flex items-center gap-1.5 transition-colors border-t border-slate-100 disabled:opacity-60"
                                >
                                    <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                    Xóa đoạn chat
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pinned Messages Bar --}}
            @if ($pinnedMessages->isNotEmpty())
                <div x-data="{ expanded: false }" class="bg-slate-50 border-b border-slate-150 px-4 py-2 z-10">
                    <div class="flex items-center justify-between">
                        <button @click="expanded = !expanded" class="flex items-center gap-1.5 text-[10px] font-bold text-slate-700 hover:text-slate-900 transition-colors">
                            <x-ui.icon name="pin" size="xs" class="text-ue-brand fill-ue-brand" />
                            <span>Tin nhắn đã ghim ({{ $pinnedMessages->count() }})</span>
                            <x-ui.icon name="chevron-down" size="xs" class="transition-transform duration-200" ::class="expanded ? 'rotate-180' : ''" />
                        </button>
                        @if ($pinnedMessages->count() === 1)
                            <span class="text-[10px] text-slate-400 font-semibold truncate max-w-[60%]">
                                {{ $pinnedMessages->first()->message->isRecalled() ? 'Tin nhắn đã bị thu hồi.' : $pinnedMessages->first()->message->body }}
                            </span>
                        @endif
                    </div>
                    
                    <div x-show="expanded" x-collapse class="mt-2 space-y-2 border-t border-slate-200/60 pt-2" style="display: none;">
                        @foreach ($pinnedMessages as $pinned)
                            <div class="flex items-center justify-between gap-4 text-xxs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="font-bold text-slate-700 flex-shrink-0">{{ $pinned->pinnedBy->id === Auth::id() ? 'Bạn' : $pinned->pinnedBy->name }} ghim:</span>
                                    <span class="text-slate-600 truncate flex-1">
                                        {{ $pinned->message->isRecalled() ? 'Tin nhắn đã bị thu hồi.' : $pinned->message->body }}
                                    </span>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="unpinMessage({{ $pinned->message_id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="unpinMessage({{ $pinned->message_id }})"
                                    class="text-[10px] font-bold text-red-500 hover:text-red-700 transition-colors flex-shrink-0"
                                >
                                    Bỏ ghim
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Message Thread Bubble Container --}}
            <div id="message-container" class="flex-1 overflow-y-auto p-4 space-y-4 flex flex-col" wire:poll.visible.20s>
                <div wire:loading.delay wire:target="selectConversation" class="space-y-4">
                    <div class="ue-skeleton-line ue-skeleton w-32 mx-auto"></div>
                    <div class="ue-skeleton-card max-w-[70%]">
                        <div class="ue-skeleton-line ue-skeleton w-full"></div>
                        <div class="ue-skeleton-line ue-skeleton w-2/3"></div>
                    </div>
                    <div class="ue-skeleton-card max-w-[70%] ml-auto">
                        <div class="ue-skeleton-line ue-skeleton w-full"></div>
                        <div class="ue-skeleton-line ue-skeleton w-3/4"></div>
                    </div>
                    <div class="ue-skeleton-card max-w-[62%]">
                        <div class="ue-skeleton-line ue-skeleton w-full"></div>
                    </div>
                </div>

                <div class="text-center py-6">
                    <x-ui.icon name="shield-alert" size="md" class="text-slate-300 mx-auto" />
                    <p class="text-[10px] text-slate-400 font-medium max-w-xs mx-auto mt-2 leading-relaxed">
                        Cuộc trò chuyện này được mã hóa bảo mật và chỉ giới hạn hiển thị giữa hai thành viên xác thực học đường. Hãy trao đổi văn minh lịch sự.
                    </p>
                </div>

                @php
                    $lastDate = null;
                @endphp
                @foreach ($messages as $message)
                    @php
                        $msgDate = $message->created_at->format('d/m/Y');
                        $isMine = (int) $message->sender_id === (int) Auth::id();
                    @endphp
                    {{-- Date Separator --}}
                    @if ($msgDate !== $lastDate)
                        <div class="text-center my-3 flex items-center justify-center gap-3">
                            <span class="h-px bg-slate-200/60 flex-1"></span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ $msgDate }}</span>
                            <span class="h-px bg-slate-200/60 flex-1"></span>
                        </div>
                        @php
                            $lastDate = $msgDate;
                        @endphp
                    @endif

                    {{-- Message Bubble Wrapper --}}
                    <div
                        data-testid="message-row-{{ $message->id }}"
                        data-message-id="{{ $message->id }}"
                        data-sender-id="{{ $message->sender_id }}"
                        data-own-message="{{ $isMine ? 'true' : 'false' }}"
                        class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-center gap-2 group w-full"
                    >
                        @if (! $isMine && $message->sender)
                            <a href="{{ route('profile.show', $message->sender) }}" class="self-end flex-shrink-0 rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $message->sender->name }}">
                                <x-ui.avatar :user="$message->sender" size="xs" />
                            </a>
                        @endif

                        {{-- Hover Actions - Left for own message --}}
                        @if ($isMine && !$message->isRecalled())
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                <button type="button" wire:click="startReply({{ $message->id }})" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Trả lời"><x-ui.icon name="reply" size="xs" /></button>
                                <button type="button" wire:click="openForwardModal({{ $message->id }})" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Chuyển tiếp"><x-ui.icon name="arrow-right" size="xs" /></button>
                                @if ($pinnedMessages->contains('message_id', $message->id))
                                    <button type="button" wire:click="unpinMessage({{ $message->id }})" wire:loading.attr="disabled" wire:target="unpinMessage({{ $message->id }})" class="p-1 text-ue-brand rounded-lg transition-colors disabled:opacity-50" title="Bỏ ghim"><x-ui.icon name="pin" size="xs" class="fill-ue-brand" /></button>
                                @else
                                    <button type="button" wire:click="pinMessage({{ $message->id }})" wire:loading.attr="disabled" wire:target="pinMessage({{ $message->id }})" class="p-1 text-slate-400 hover:text-ue-brand rounded-lg transition-colors disabled:opacity-50" title="Ghim"><x-ui.icon name="pin" size="xs" /></button>
                                @endif
                                <button type="button" wire:click="recallMessage({{ $message->id }})" wire:loading.attr="disabled" wire:target="recallMessage({{ $message->id }})" class="p-1 text-slate-400 hover:text-red-500 rounded-lg transition-colors disabled:opacity-50" title="Thu hồi"><x-ui.icon name="trash" size="xs" /></button>
                            </div>
                        @endif

                        <div class="flex flex-col max-w-[70%] gap-1">
                            {{-- Reply quoted preview inside bubble --}}
                            @if (!$message->isRecalled() && $message->replyTo)
                                <div class="mb-1.5 p-2 rounded-xl text-[10px] font-semibold max-w-full truncate
                                            {{ $isMine ? 'bg-ue-brand-dark/20 border-l-2 border-white/60 text-white/90' : 'bg-slate-50 border border-slate-150 border-l-2 border-l-ue-brand text-slate-550' }}">
                                    <span class="block font-bold text-[9px] uppercase tracking-wider mb-0.5 {{ $isMine ? 'text-white/80' : 'text-slate-400' }}">
                                        {{ (int)$message->replyTo->sender_id === (int)Auth::id() ? 'Bạn' : ($message->replyTo->sender ? $message->replyTo->sender->name : 'Thành viên') }}
                                    </span>
                                    <span class="block italic truncate">
                                        {{ $message->replyTo->isRecalled() ? 'Tin nhắn đã bị thu hồi.' : $message->replyTo->body }}
                                    </span>
                                </div>
                            @endif

                            {{-- Forward tag --}}
                            @if (!$message->isRecalled() && $message->forwarded_from_message_id)
                                <div class="flex items-center gap-1 text-[9px] font-bold text-slate-400 italic mb-1 select-none">
                                    <x-ui.icon name="forward" size="xxs" class="text-slate-350" />
                                    <span>Đã chuyển tiếp</span>
                                </div>
                            @endif

                            @if ($message->isRecalled())
                                <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed italic bg-slate-100 border border-slate-200 text-slate-400 {{ $isMine ? 'rounded-br-xs' : 'rounded-bl-xs' }}">
                                    Tin nhắn đã bị thu hồi.
                                </div>
                            @else
                                {{-- Standard text bubble --}}
                                @if ($message->message_type === MessageType::TEXT)
                                    <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed
                                                {{ $isMine ? 'bg-ue-brand text-white rounded-br-xs shadow-2xs' : 'bg-white border border-slate-150 text-slate-700 rounded-bl-xs' }}">
                                        {{ $message->body }}
                                    </div>
                                {{-- Image attachment bubble --}}
                                @elseif ($message->message_type === MessageType::IMAGE)
                                    @php
                                        $mediaItem = $message->media->first();
                                        $imageUrl = $mediaItem ? MediaUrlResolver::publicUrl($mediaItem, 'detail') : null;
                                        $originalUrl = $mediaItem ? MediaUrlResolver::publicUrl($mediaItem, 'original') : null;
                                        $messageImageVariant = $mediaItem?->relationLoaded('variants') ? $mediaItem->variants->firstWhere('variant_name', 'detail') : null;
                                    @endphp
                                    @if ($imageUrl)
                                        <div class="flex flex-col gap-2">
                                            <div class="rounded-2xl overflow-hidden border border-slate-150 max-w-[280px] bg-slate-100 shadow-2xs flex items-center justify-center">
                                                <a href="{{ $originalUrl ?? $imageUrl }}" target="_blank" class="block cursor-zoom-in w-full h-full">
                                                    <img
                                                        src="{{ $imageUrl }}"
                                                        alt="Attachment"
                                                        class="ue-message-attachment-media hover:opacity-95 transition-opacity"
                                                        loading="lazy"
                                                        @if($messageImageVariant?->width) width="{{ $messageImageVariant->width }}" @endif
                                                        @if($messageImageVariant?->height) height="{{ $messageImageVariant->height }}" @endif
                                                    />
                                                </a>
                                            </div>
                                            @if ($message->body)
                                                <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed
                                                            {{ $isMine ? 'bg-ue-brand text-white rounded-br-xs shadow-2xs' : 'bg-white border border-slate-150 text-slate-700 rounded-bl-xs' }}">
                                                    {{ $message->body }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed italic bg-slate-100 border border-slate-200 text-slate-400 {{ $isMine ? 'rounded-br-xs' : 'rounded-bl-xs' }}">
                                            Lỗi tải ảnh đính kèm.
                                        </div>
                                    @endif
                                {{-- Shared Post preview card bubble --}}
                                @elseif ($message->message_type === MessageType::SHARED_POST)
                                    <div class="p-3.5 rounded-2xl border border-slate-150 bg-white shadow-2xs {{ $isMine ? 'rounded-br-xs' : 'rounded-bl-xs' }} text-slate-700 flex flex-col gap-3">
                                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-semibold uppercase tracking-wider leading-none">
                                            <x-ui.icon name="link-2" size="xs" class="text-ue-brand" />
                                            <span>Chia sẻ bài viết</span>
                                        </div>

                                        @if ($this->canViewPost($message->sharedPost))
                                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl flex flex-col gap-1.5">
                                                <div class="flex items-center gap-1.5">
                                                    <a href="{{ route('profile.show', $message->sharedPost->user) }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $message->sharedPost->user->name }}">
                                                        <x-ui.avatar :user="$message->sharedPost->user" size="xs" />
                                                    </a>
                                                    <a href="{{ route('profile.show', $message->sharedPost->user) }}" class="text-xxs font-bold text-slate-800 hover:text-ue-brand hover:underline">{{ $message->sharedPost->user->name }}</a>
                                                </div>
                                                <p class="text-xxs font-medium text-slate-600 line-clamp-2 leading-relaxed">
                                                    {{ $message->sharedPost->body }}
                                                </p>
                                            </div>
                                            <a
                                                href="{{ route('posts.show', $message->sharedPost) }}"
                                                class="w-full text-center bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xxs font-bold py-1.5 rounded-lg transition-colors flex items-center justify-center gap-1.5"
                                            >
                                                <x-ui.icon name="external-link" size="xs" />
                                                Xem bài viết
                                            </a>
                                        @else
                                            <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-2 text-xxs font-semibold text-slate-400 italic">
                                                <x-ui.icon name="alert-triangle" size="xs" />
                                                <span>Bài viết này không còn khả dụng.</span>
                                            </div>
                                        @endif

                                        @if ($message->body)
                                            <div class="text-xxs font-semibold border-t border-slate-100 pt-2 text-slate-650 leading-normal">
                                                {{ $message->body }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- Time tag --}}
                            <span class="text-[8px] text-slate-400 font-semibold px-1 {{ $isMine ? 'self-end' : 'self-start' }}">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>

                        {{-- Hover Actions - Right for incoming message --}}
                        @if (!$isMine && !$message->isRecalled())
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                <button type="button" wire:click="startReply({{ $message->id }})" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Trả lời"><x-ui.icon name="reply" size="xs" /></button>
                                <button type="button" wire:click="openForwardModal({{ $message->id }})" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Chuyển tiếp"><x-ui.icon name="arrow-right" size="xs" /></button>
                                @if ($pinnedMessages->contains('message_id', $message->id))
                                    <button type="button" wire:click="unpinMessage({{ $message->id }})" wire:loading.attr="disabled" wire:target="unpinMessage({{ $message->id }})" class="p-1 text-ue-brand rounded-lg transition-colors disabled:opacity-50" title="Bỏ ghim"><x-ui.icon name="pin" size="xs" class="fill-ue-brand" /></button>
                                @else
                                    <button type="button" wire:click="pinMessage({{ $message->id }})" wire:loading.attr="disabled" wire:target="pinMessage({{ $message->id }})" class="p-1 text-slate-400 hover:text-ue-brand rounded-lg transition-colors disabled:opacity-50" title="Ghim"><x-ui.icon name="pin" size="xs" /></button>
                                @endif
                                <button type="button" wire:click="openReportModal({{ $message->id }})" class="p-1 text-slate-400 hover:text-red-500 rounded-lg transition-colors" title="Báo cáo"><x-ui.icon name="flag" size="xs" /></button>
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Alpine Pending Messages (Optimistic UI) --}}
                <template x-for="msg in pendingMessages" :key="msg.client_message_id">
                    <div class="flex justify-end items-center gap-2 group w-full opacity-80 animate-pulse">
                        <div class="flex flex-col max-w-[70%] gap-1">
                            <template x-if="msg.imageUrl">
                                <div class="flex flex-col gap-2">
                                    <div class="rounded-2xl overflow-hidden border border-slate-150 max-w-[280px] bg-slate-100 shadow-2xs flex items-center justify-center">
                                        <div class="block w-full h-full">
                                            <img
                                                :src="msg.imageUrl"
                                                alt="Uploading Attachment"
                                                class="ue-message-attachment-media hover:opacity-95 transition-opacity"
                                                loading="lazy"
                                            />
                                        </div>
                                    </div>
                                    <template x-if="msg.body">
                                        <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed bg-ue-brand text-white rounded-br-xs shadow-2xs">
                                            <span x-text="msg.body"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!msg.imageUrl">
                                <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed bg-ue-brand text-white rounded-br-xs shadow-2xs">
                                    <span x-text="msg.body"></span>
                                </div>
                            </template>
                            <div class="text-[8px] font-semibold px-1 self-end flex items-center gap-1 select-none">
                                <template x-if="msg.status === 'sending'">
                                    <span class="text-slate-400 flex items-center gap-1">
                                        <span>Đang gửi...</span>
                                        <span class="block w-1.5 h-1.5 border border-slate-400 border-t-transparent rounded-full animate-spin"></span>
                                    </span>
                                </template>
                                <template x-if="msg.status === 'failed'">
                                    <span class="text-red-550 flex items-center gap-1.5">
                                        <span x-text="msg.error || 'Lỗi gửi tin'"></span>
                                        <button 
                                            type="button" 
                                            @click="retryMessage(msg.client_message_id)"
                                            class="font-bold text-ue-brand hover:underline focus:outline-none"
                                        >
                                            Thử lại
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Typing Indicator --}}
                <div x-show="recipientTyping" class="flex justify-start items-center gap-2 w-full animate-pulse" style="display: none;">
                    @if ($recipient)
                        <x-ui.avatar :user="$recipient" size="xs" />
                    @endif
                    <div class="px-3.5 py-2 rounded-2xl bg-white border border-slate-150 text-slate-500 rounded-bl-xs flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>

                <div id="server-message-client-ids" data-ids="{{ json_encode($messages->pluck('client_message_id')->filter()->values()->toArray()) }}" class="hidden"></div>
            </div>

            {{-- Message Composer or Restricted Banner --}}
            <div class="p-3 border-t border-slate-150 bg-white flex-shrink-0">
                <div wire:loading.delay wire:target="selectConversation" class="flex items-center gap-2">
                    <div class="ue-skeleton h-10 w-10 rounded-xl"></div>
                    <div class="ue-skeleton h-10 flex-1 rounded-xl"></div>
                    <div class="ue-skeleton h-10 w-10 rounded-xl"></div>
                </div>

                @if ($isRestricted)
                    <div class="bg-slate-50 border border-slate-150 rounded-xl p-3.5 flex items-center gap-3 text-xxs font-semibold text-slate-500">
                        <x-ui.icon name="shield-alert" size="sm" class="text-slate-400 flex-shrink-0" />
                        <span class="leading-normal">
                            @if ($isBlocked)
                                Bạn không thể gửi tin nhắn cho người dùng này.
                            @else
                                Bạn chỉ có thể gửi tin nhắn cho các liên kết bạn bè hiện tại. <a href="{{ route('discovery.index') }}" class="text-ue-brand font-bold hover:underline">Khám phá</a>
                            @endif
                        </span>
                    </div>
                @else
                    {{-- Active Reply Preview Bar --}}
                    @if ($replyingToMessageId)
                        @php
                            $replyMsg = \App\Models\Message::with('sender')->find($replyingToMessageId);
                        @endphp
                        @if ($replyMsg)
                            <div class="mb-2 p-2 bg-slate-50 border border-slate-150 border-l-2 border-l-ue-brand rounded-xl flex items-center justify-between gap-3 text-xxs font-medium animate-in fade-in duration-100">
                                <div class="min-w-0 flex-1">
                                    <span class="block font-bold text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">
                                        Đang trả lời {{ (int)$replyMsg->sender_id === (int)Auth::id() ? 'chính mình' : ($replyMsg->sender ? $replyMsg->sender->name : 'Thành viên') }}
                                    </span>
                                    <p class="text-slate-650 italic truncate">{{ $replyMsg->isRecalled() ? 'Tin nhắn đã bị thu hồi.' : $replyMsg->body }}</p>
                                </div>
                                <button type="button" wire:click="cancelReply" class="text-slate-400 hover:text-slate-600 flex-shrink-0 transition-colors p-1" title="Hủy trả lời">
                                    <x-ui.icon name="x" size="xs" />
                                </button>
                            </div>
                        @endif
                    @endif

                    {{-- Attachment Preview Bar --}}
                    @if ($messageAttachment)
                        <div class="mb-2 p-2 bg-slate-50 border border-slate-150 rounded-xl flex items-center justify-between gap-3 text-xxs font-medium animate-in fade-in duration-100">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                                    <img src="{{ $messageAttachment['url'] }}" alt="Attachment Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="min-w-0">
                                    <span class="block font-bold text-[9px] text-slate-400 uppercase tracking-wider">Hình ảnh đính kèm</span>
                                    <span class="block text-slate-600 truncate text-[10px]">{{ $messageAttachment['uuid'] }}.png</span>
                                </div>
                            </div>
                            <button type="button" wire:click="removeAttachment" class="text-slate-400 hover:text-slate-600 flex-shrink-0 transition-colors p-1" title="Xóa đính kèm">
                                <x-ui.icon name="x" size="xs" />
                            </button>
                        </div>
                    @endif

                    @error('attachmentFile')
                        <div class="mb-2 px-2 text-[10px] text-red-500 font-semibold leading-normal">
                            {{ $message }}
                        </div>
                    @enderror

                    <form @submit.prevent="submitForm()" class="flex gap-2 items-center" x-data="{ uploading: false }" x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false">
                        {{-- Hidden File Input --}}
                        <input
                            type="file"
                            wire:model="attachmentFile"
                            id="attachment-input"
                            class="hidden"
                            accept="image/jpeg,image/png,image/webp"
                        />

                        {{-- Attachment Button --}}
                        <button
                            type="button"
                            onclick="document.getElementById('attachment-input').click()"
                            class="text-slate-400 hover:text-slate-650 p-2.5 rounded-xl hover:bg-slate-50 transition-colors flex-shrink-0 flex items-center justify-center"
                            aria-label="Đính kèm ảnh"
                            :disabled="uploading"
                        >
                            <template x-if="!uploading">
                                <x-ui.icon name="paperclip" size="sm" />
                            </template>
                            <template x-if="uploading">
                                <span class="block w-4 h-4 border-2 border-ue-brand border-t-transparent rounded-full animate-spin"></span>
                            </template>
                        </button>

                        <input
                            type="text"
                            wire:model="newMessageBody"
                            placeholder="Nhập tin nhắn..."
                            class="flex-1 px-4 py-2.5 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 placeholder-slate-400 text-slate-700 bg-slate-50/60"
                            @input="handleTyping()"
                        />
                        <button
                            type="submit"
                            wire:target="submitMessage"
                            class="bg-ue-brand hover:bg-ue-brand-dark text-white rounded-xl p-2.5 shadow-2xs hover:shadow-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                            aria-label="Gửi tin nhắn"
                            :disabled="uploading"
                        >
                            <span>
                                <x-ui.icon name="send" size="sm" />
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <x-ui.icon name="message-square" size="lg" class="text-slate-200" />
                <h3 class="text-sm font-bold text-slate-700 mt-3">Chào mừng bạn đến với Hộp thư</h3>
                <p class="text-xxs text-slate-400 max-w-sm mt-1">Chọn một cuộc trò chuyện từ danh sách bên trái hoặc truy cập danh sách bạn bè để bắt đầu trò chuyện riêng tư, bảo mật.</p>
            </div>
        @endif
    </div>

    {{-- Nickname Customization Modal --}}
    @if ($showNicknameModal && $recipient)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-data x-cloak>
            <div class="bg-white rounded-2xl border border-slate-150 shadow-2xl w-full max-w-sm overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-800">Đặt biệt danh</h3>
                    <button type="button" wire:click="$set('showNicknameModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <p class="text-[10px] text-slate-500 font-semibold leading-relaxed">
                        Biệt danh này chỉ hiển thị với bạn trong UEConnect. Đặt biệt danh giúp bạn dễ dàng quản lý bạn bè.
                    </p>
                    <div>
                        <label for="nickname-input" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Biệt danh cho {{ $recipient->name }}</label>
                        <input
                            id="nickname-input"
                            type="text"
                            wire:model="nicknameInput"
                            placeholder="Nhập biệt danh..."
                            class="w-full px-3.5 py-2 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 text-slate-700 bg-slate-50/50"
                        />
                    </div>
                </div>
                <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" wire:click="$set('showNicknameModal', false)" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xxs font-bold transition-colors">
                        Hủy
                    </button>
                    <button type="button" wire:click="saveNickname" wire:loading.attr="disabled" wire:target="saveNickname" class="px-4 py-2 rounded-xl bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold shadow-2xs hover:shadow-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveNickname">Lưu thay đổi</span>
                        <span wire:loading wire:target="saveNickname">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Forward Message Modal --}}
    @if ($showForwardModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-data x-cloak>
            <div class="bg-white rounded-2xl border border-slate-150 shadow-2xl w-full max-w-sm overflow-hidden flex flex-col h-[400px] animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
                    <h3 class="text-xs font-bold text-slate-800">Chuyển tiếp tin nhắn</h3>
                    <button type="button" wire:click="$set('showForwardModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>
                <div class="p-3 border-b border-slate-100 bg-slate-50/30 flex-shrink-0">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="xs" class="text-slate-400" />
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="forwardSearch"
                            placeholder="Tìm kiếm cuộc trò chuyện..."
                            class="w-full pl-9 pr-4 py-1.5 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-white text-slate-700 placeholder-slate-400"
                        />
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                    @forelse ($forwardConversations as $fConvo)
                        <div class="p-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                @if ($fConvo['recipient'])
                                    <x-ui.avatar :user="$fConvo['recipient']" size="sm" />
                                    <span class="text-xxs font-bold text-slate-800 truncate">{{ $fConvo['recipient']->name }}</span>
                                @else
                                    <span class="text-xxs font-bold text-slate-800 truncate">Thành viên UEConnect</span>
                                @endif
                            </div>
                            <button
                                type="button"
                                wire:click="forwardMessage({{ $fConvo['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="forwardMessage({{ $fConvo['id'] }})"
                                class="bg-ue-brand hover:bg-ue-brand-dark text-white px-3 py-1.5 rounded-lg text-xxs font-bold shadow-3xs transition-all flex-shrink-0"
                            >
                                <span wire:loading.remove wire:target="forwardMessage({{ $fConvo['id'] }})">Gửi</span>
                                <span wire:loading wire:target="forwardMessage({{ $fConvo['id'] }})">Đang gửi...</span>
                            </button>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 flex flex-col items-center justify-center gap-2">
                            <x-ui.icon name="users" size="lg" class="text-slate-200" />
                            <p class="text-xxs font-semibold">Không tìm thấy cuộc trò chuyện nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Report Message Modal --}}
    @if ($showReportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-data x-cloak>
            <div class="bg-white rounded-2xl border border-slate-150 shadow-2xl w-full max-w-sm overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-800">Báo cáo tin nhắn</h3>
                    <button type="button" wire:click="$set('showReportModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>
                <div class="p-4 space-y-4">
                    <p class="text-[10px] text-slate-500 font-semibold leading-relaxed">
                        Hãy chọn lý do báo cáo tin nhắn này để giúp UEConnect xây dựng môi trường trao đổi văn minh, an toàn.
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label for="report-reason" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Lý do vi phạm</label>
                            <select
                                id="report-reason"
                                wire:model="reportReason"
                                class="w-full px-3 py-2 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 text-slate-700 bg-white"
                            >
                                <option value="spam">Spam / Quảng cáo rác</option>
                                <option value="harassment">Quấy rối / Công kích cá nhân</option>
                                <option value="inappropriate_content">Nội dung không phù hợp / Nhạy cảm</option>
                                <option value="misinformation">Tin giả / Sai lệch thông tin</option>
                                <option value="privacy_violation">Xâm phạm quyền riêng tư</option>
                                <option value="other">Lý do khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="report-desc" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Chi tiết bổ sung (không bắt buộc)</label>
                            <textarea
                                id="report-desc"
                                wire:model="reportDescription"
                                placeholder="Cung cấp thêm chi tiết..."
                                rows="3"
                                class="w-full px-3.5 py-2 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 text-slate-700 bg-slate-50/50 placeholder-slate-400 resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" wire:click="$set('showReportModal', false)" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xxs font-bold transition-colors">
                        Hủy
                    </button>
                    <button type="button" wire:click="submitReport" wire:loading.attr="disabled" wire:target="submitReport" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xxs font-bold shadow-2xs hover:shadow-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitReport">Gửi báo cáo</span>
                        <span wire:loading wire:target="submitReport">Đang gửi...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
