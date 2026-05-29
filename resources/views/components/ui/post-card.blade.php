@props([
    'post',
    'currentUser',
    'isSaved' => false,
    'isLiked' => false,
    'likeCount' => 0,
    'commentCount' => 0,
    'editingPostId' => null,
    'editingBody' => '',
])

@php
    $author = $post->user;
    $profile = $author->profile;
    $isOwner = $post->user_id === $currentUser->id;
    $isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));
@endphp

<div
    class="ue-post-card ue-post-card--interactive"
    wire:key="post-card-{{ $post->id }}"
>
    <div class="ue-post-card__body">
        {{-- Left Avatar Column --}}
        <div class="flex-shrink-0">
            <x-ui.avatar :user="$author" size="md" />
        </div>

        {{-- Right Content Column --}}
        <div class="flex-1 min-w-0">
            {{-- Post Author Header --}}
            <div class="ue-post-card__header">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-slate-800 leading-tight">
                            {{ $author->name }}
                        </span>
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" aria-label="Đã xác thực" />
                        
                        {{-- Relative timestamp --}}
                        <span class="ue-post-card__meta">
                            · {{ $post->published_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    {{-- Faculty & Major --}}
                    @if ($profile)
                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 leading-none">
                            {{ Str::ucfirst($profile->role_type) }}
                            @if ($profile->faculty)
                                · {{ $profile->faculty }}
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Header Actions Side-by-Side --}}
                <div class="flex items-center gap-1.5">
                    {{-- Unified Actions Menu --}}
                    <x-ui.post-menu
                        :post="$post"
                        :currentUser="$currentUser"
                        :isOwner="$isOwner"
                        :isAdmin="$isAdmin"
                        :isSaved="$isSaved"
                    />

                    {{-- X button: Quick hide --}}
                    <x-ui.icon-button
                        icon="x"
                        label="Ẩn bài viết khỏi bảng tin"
                        variant="ghost"
                        size="xs"
                        wire:click="hidePost({{ $post->id }})"
                        class="ue-post-card__quick-hide text-slate-400 hover:text-slate-650 focus:ring-1 focus:ring-slate-200 focus:outline-none"
                    />
                </div>
            </div>

            {{-- Body Content or Editing UI --}}
            @if ($editingPostId === $post->id)
                <div class="mt-2 space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100 ue-animate-fade-in">
                    <label for="edit-body-{{ $post->id }}" class="sr-only">Nội dung chỉnh sửa</label>
                    <textarea
                        id="edit-body-{{ $post->id }}"
                        wire:model="editingBody"
                        rows="3"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                        maxlength="3000"
                    ></textarea>
                    @error('editingBody')
                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                        <span class="text-[10px] text-slate-400 font-semibold">
                            {{ mb_strlen($editingBody) }}/3000
                        </span>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                wire:click="cancelEdit" 
                                class="px-2.5 py-1.5 text-xxs font-bold text-slate-550 hover:text-slate-750 transition-colors"
                            >
                                Hủy
                            </button>
                            <x-ui.button
                                type="button"
                                wire:click="saveEdit"
                                variant="primary"
                                size="xs"
                                icon="check"
                            >
                                Lưu thay đổi
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @else
                <div class="ue-post-card__content mt-1">{{ $post->body }}</div>
                
                {{-- Media Attachment (Threads-like premium rounded border container) --}}
                @if (!empty($post->media_url))
                    <div class="ue-post-card__media mt-2.5 overflow-hidden rounded-xl border border-slate-150 max-w-lg select-none bg-slate-50">
                        <img 
                            src="{{ $post->media_url }}" 
                            alt="Hình ảnh đính kèm" 
                            class="w-full h-auto object-cover max-h-[360px] hover:scale-[1.01] transition-transform duration-300 cursor-pointer"
                            loading="lazy"
                        />
                    </div>
                @endif
                
                {{-- Edited Badge --}}
                @if ($post->status === \App\Enums\PostStatus::EDITED)
                    <span class="inline-block mt-2 text-[9px] font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5">
                        Đã chỉnh sửa
                    </span>
                @endif
            @endif

            {{-- Standard Action Buttons Bar --}}
            <div class="ue-post-card__actions gap-x-4 sm:gap-x-6">
                {{-- Like --}}
                <x-ui.post-action-button
                    icon="heart"
                    activeIcon="heart"
                    label="Thích"
                    :count="$likeCount"
                    :selected="$isLiked"
                    danger="true"
                    wireClick="toggleLike({{ $post->id }})"
                />

                {{-- Comments Link --}}
                <a
                    href="{{ route('posts.show', $post) }}"
                    class="ue-action-button flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-ue-brand transition-colors"
                >
                    <x-ui.icon name="message-circle" size="md" class="ue-action-button__icon text-current" />
                    <span class="ue-action-button__count">{{ $commentCount }}</span>
                </a>

                {{-- Share --}}
                <x-ui.post-action-button
                    icon="send"
                    label="Chia sẻ"
                    wireClick="startShare({{ $post->id }})"
                />

                {{-- Save Toggle --}}
                <div class="ml-auto">
                    <x-ui.post-action-button
                        icon="bookmark"
                        activeIcon="bookmark"
                        label="Lưu"
                        :selected="$isSaved"
                        wireClick="toggleSave({{ $post->id }})"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
