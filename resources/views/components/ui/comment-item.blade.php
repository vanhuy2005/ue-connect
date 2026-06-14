@props([
    'comment',
    'currentUser',
    'replyingToCommentId' => null,
    'editingCommentId' => null,
    'editingCommentBody' => '',
    'commentBody' => '',
    'isReply' => false,
])

@php
    $commentAuthor = $comment->user;
    $commentProfile = $commentAuthor->profile;
    $commentAuthorProfileUrl = route('profile.show', $commentAuthor);
    $commentLikes = $comment->likes->count();
    $isCommentLiked = $comment->likes->where('user_id', $currentUser->id)->isNotEmpty();
    $isCommentOwner = $comment->user_id === $currentUser->id;
    $isDeleted = in_array($comment->status, [\App\Enums\CommentStatus::DELETED_BY_OWNER, \App\Enums\CommentStatus::DELETED_BY_MODERATION, \App\Enums\CommentStatus::HIDDEN_BY_MODERATION]);
    $menuId = "comment-menu-" . $comment->id;
@endphp

<div
    class="ue-comment {{ $isReply ? 'pt-2' : '' }}"
    wire:key="comment-item-{{ $comment->id }}"
>
    @if(!$isReply && !$isDeleted && $comment->replies->count() > 0)
        {{-- Visual rail line indicating reply nesting --}}
        <div class="ue-comment__rail"></div>
    @endif

    {{-- Avatar Column --}}
    <div class="flex-shrink-0">
        @if ($isDeleted)
            <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-150 flex items-center justify-center text-slate-400">
                <x-ui.icon name="eye-off" size="xs" />
            </div>
        @else
            <a href="{{ $commentAuthorProfileUrl }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $commentAuthor->name }}">
                <x-ui.avatar :user="$commentAuthor" size="sm" />
            </a>
        @endif
    </div>

    {{-- Content Column --}}
    <div class="ue-comment__body min-w-0">
        @if ($isDeleted)
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-slate-400 text-xs italic font-medium">
                Bình luận này không còn khả dụng.
            </div>
        @else
            @if ($editingCommentId === $comment->id)
                <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-105 ue-animate-fade-in">
                    <label for="edit-comment-{{ $comment->id }}" class="sr-only">Nội dung bình luận chỉnh sửa</label>
                    <textarea
                        id="edit-comment-{{ $comment->id }}"
                        wire:model.live.debounce.150ms="editingCommentBody"
                        rows="2"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                        maxlength="1000"
                    ></textarea>
                    @error('editingCommentBody')
                        <p class="text-xs text-red-655 font-semibold">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <span class="text-[10px] text-slate-400 font-semibold">
                            {{ mb_strlen($editingCommentBody) }}/1000
                        </span>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                wire:click="cancelCommentEdit" 
                                class="px-2.5 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
                            >
                                Hủy
                            </button>
                            <x-ui.button
                                type="button"
                                wire:click="saveCommentEdit"
                                variant="primary"
                                size="xs"
                                icon="check"
                                :disabled="trim($editingCommentBody) === ''"
                            >
                                Lưu
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @else
                {{-- Comment Header --}}
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ $commentAuthorProfileUrl }}" class="text-sm font-bold text-slate-800 leading-tight hover:text-ue-brand hover:underline">
                                {{ $commentAuthor->name }}
                            </a>
                            <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" />
                            <span class="text-xs text-slate-400 font-semibold">
                                · {{ $comment->created_at->diffForHumans() }}
                            </span>
                        </div>
                        @if ($commentProfile)
                            <div class="text-xs text-slate-400 font-medium mt-0.5">
                                {{ Str::ucfirst($commentProfile->role_type) }}
                                @if ($commentProfile->faculty)
                                    · {{ $commentProfile->faculty }}
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Actions options trigger via popover --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-100 rounded-full p-0.5"
                            aria-haspopup="true"
                            :aria-expanded="open"
                            aria-label="Tùy chọn bình luận"
                        >
                            <x-ui.icon name="more-horizontal" size="xs" />
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-1 rounded-xl bg-white border border-ue-border shadow-lg py-1 z-30"
                            style="display: none; width: 180px;"
                        >
                            <a
                                href="{{ $commentAuthorProfileUrl }}"
                                class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2"
                            >
                                <x-ui.icon name="user" size="xs" class="text-slate-400" />
                                <span>Xem trang cá nhân</span>
                            </a>
                            @if ($isCommentOwner)
                                <button
                                    type="button"
                                    wire:click="startCommentEdit({{ $comment->id }})"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2"
                                >
                                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                    <span>Chỉnh sửa</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="openCommentDeleteModal({{ $comment->id }})"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2"
                                >
                                    <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                    <span>Xóa bình luận</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="openCommentReport({{ $comment->id }})"
                                    @click="open = false"
                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-750 flex items-center gap-2"
                                >
                                    <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                    <span>Báo cáo vi phạm</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Content Body --}}
                <div class="text-slate-700 text-sm mt-1.5 leading-relaxed">
                    {!! \App\Models\Comment::parseMentions($comment->body) !!}
                </div>

                {{-- Action triggers --}}
                <div class="flex items-center gap-4 mt-2.5 text-slate-400 text-xs font-bold">
                    <button
                        type="button"
                        wire:click="toggleCommentLike({{ $comment->id }})"
                        class="hover:text-rose-600 transition-colors flex items-center gap-1 {{ $isCommentLiked ? 'text-rose-600' : '' }}"
                    >
                        <x-ui.icon name="heart" size="xs" class="{{ $isCommentLiked ? 'fill-rose-600 text-rose-600' : '' }}" />
                        <span>{{ $commentLikes }}</span>
                    </button>

                    @if ($currentUser->isActive())
                        <button
                            type="button"
                            wire:click="setReplyingTo({{ $comment->id }})"
                            class="hover:text-ue-brand transition-colors flex items-center gap-1"
                        >
                            <x-ui.icon name="message-circle" size="xs" />
                            <span>Phản hồi</span>
                        </button>
                    @endif
                </div>

                {{-- Inline Reply form --}}
                @if ($replyingToCommentId === $comment->id && $currentUser->isActive())
                    <div class="mt-3 ue-animate-fade-in {{ $isReply ? '' : 'ue-comment--reply' }}">
                        <div class="ue-feed-composer border border-ue-border/60 rounded-2xl bg-white shadow-xs">
                            <div class="ue-composer">
                                <div class="flex justify-start">
                                    <x-ui.avatar :user="$currentUser" size="sm" />
                                </div>
                                <div class="min-w-0 relative flex-1" x-data="mentionComposer({ textareaId: 'reply-text-{{ $comment->id }}', wireModel: 'commentBody', initialMention: '{{ $commentAuthor->profile?->display_name ?? $commentAuthor->name }}' })" @focusout="setTimeout(() => { if (! $el.contains(document.activeElement)) closeDropdown() }, 150)" wire:key="reply-composer-{{ $comment->id }}">
                                    <div class="mb-3 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-xxs text-ue-brand font-bold flex items-center justify-between">
                                        <span>Đang phản hồi bình luận của {{ $commentAuthor->name }}</span>
                                        <button type="button" wire:click="setReplyingTo(null)" class="text-slate-400 hover:text-slate-655 transition-colors">
                                            Hủy bỏ
                                        </button>
                                    </div>

                                    <form wire:submit.prevent="submitComment">
                                        <div>
                                            <div class="relative w-full">
                                                <label for="reply-text-{{ $comment->id }}" class="sr-only">Nội dung phản hồi</label>
                                                <textarea
                                                    id="reply-text-{{ $comment->id }}"
                                                    x-ref="textarea"
                                                    wire:model.live.debounce.150ms="commentBody"
                                                    @input="handleInput($event)"
                                                    @keydown.arrow-down.prevent="selectNext()"
                                                    @keydown.arrow-up.prevent="selectPrev()"
                                                    @keydown.enter="showDropdown ? ($event.preventDefault() || confirmSelection()) : true"
                                                    @keydown.escape="showDropdown ? ($event.preventDefault() || closeDropdown()) : true"
                                                    placeholder="Nhập phản hồi của bạn..."
                                                    rows="2"
                                                    class="ue-composer__textarea focus:outline-none"
                                                    maxlength="1000"
                                                ></textarea>

                                                {{-- Suggestion Dropdown --}}
                                                <div 
                                                    x-show="showDropdown" 
                                                    x-transition
                                                    @click.outside="closeDropdown()"
                                                    class="absolute left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto divide-y divide-slate-50"
                                                    :style="openUpward ? 'top: auto; bottom: 100%; margin-bottom: 6px;' : 'bottom: auto; top: ' + dropdownTop"
                                                    style="display: none;"
                                                >
                                                    <template x-for="(user, index) in suggestions" :key="user.id">
                                                        <button
                                                            type="button"
                                                            @click="insertMention(user)"
                                                            @mouseenter="selectedIndex = index"
                                                            class="w-full text-left px-4 py-2 flex items-center gap-3 transition-colors"
                                                            x-bind:class="selectedIndex === index ? 'bg-slate-50 text-ue-brand' : 'text-slate-700'"
                                                        >
                                                            <img :src="user.avatar_url || 'https://www.gravatar.com/avatar/' + user.id + '?d=mp&s=100'" class="w-6 h-6 rounded-full object-cover border border-slate-100" />
                                                            <div class="flex-1 min-w-0">
                                                                <span class="text-xxs font-bold block truncate" x-text="user.display_name"></span>
                                                                <span class="text-[9px] text-slate-400 block truncate" x-text="'@' + user.name + (user.role ? ' · ' + user.role : '')"></span>
                                                            </div>
                                                        </button>
                                                    </template>
                                                </template>
                                            </div>
                                        </div>
                                        @error('commentBody')
                                            <p class="text-xs text-red-655 font-semibold mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                        <div class="ue-composer__toolbar">
                                            <div class="ue-composer__actions">
                                                <span class="ue-composer__counter text-slate-400 text-xxs font-semibold whitespace-nowrap">
                                                    {{ mb_strlen($commentBody) }}/1000
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click="setReplyingTo(null)" class="px-2.5 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                                                    Hủy
                                                </button>
                                                <x-ui.button
                                                    type="submit"
                                                    variant="primary"
                                                    size="sm"
                                                    icon="send"
                                                    :disabled="trim($commentBody) === ''"
                                                >
                                                    Gửi phản hồi
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endif

        {{-- Nested Replies Recursion --}}
        @if (!$isReply && $comment->replies->count() > 0)
            <div class="mt-3 space-y-4 ml-3 md:ml-4">
                @foreach ($comment->replies as $reply)
                    <x-ui.comment-item
                        :comment="$reply"
                        :currentUser="$currentUser"
                        :replyingToCommentId="$replyingToCommentId"
                        :editingCommentId="$editingCommentId"
                        :editingCommentBody="$editingCommentBody"
                        :commentBody="$commentBody"
                        :isReply="true"
                    />
                @endforeach
            </div>
        @endif
    </div>
</div>
