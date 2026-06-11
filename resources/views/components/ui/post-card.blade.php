@props([
    'post',
    'currentUser',
    'isSaved' => false,
    'isLiked' => false,
    'likeCount' => 0,
    'commentCount' => 0,
    'editingPostId' => null,
    'editingBody' => '',
    'showQuickFollow' => false,
    'showFollowCheck' => false,
    'repostCount' => 0,
    'isReposted' => false,
    'repostedBy' => null,
    'repostedAt' => null,
    'feedItemKey' => null,
    'showRepostAction' => false,
])

@php
    $author = $post->user;
    $profile = $author->profile;
    $authorProfileUrl = route('profile.show', $author);
    $isOwner = $post->user_id === $currentUser->id;
    $isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));
    $mediaUrlAction = app(\App\Actions\Media\GenerateMediaUrlAction::class);
    $reposterName = $repostedBy?->profile?->display_name ?? $repostedBy?->name;
    $mediaItems = $post->relationLoaded('media')
        ? $post->media->where('status', 'ready')->values()
        : $post->media()->where('status', 'ready')->with('variants')->get();
    $mediaCount = $mediaItems->count();
    $mediaDimensions = function ($mediaItem): array {
        $variant = $mediaItem->relationLoaded('variants')
            ? $mediaItem->variants->firstWhere('variant_name', 'feed')
            : null;

        return [
            'width' => $variant?->width ?: $mediaItem->width,
            'height' => $variant?->height ?: $mediaItem->height,
        ];
    };
@endphp

<div
    class="ue-post-card ue-post-card--interactive"
    wire:key="post-card-{{ $feedItemKey ?? $post->id }}"
>
    @if ($repostedBy)
        <div class="grid grid-cols-[36px_1fr] md:grid-cols-[var(--feed-avatar-size)_1fr] gap-x-[10px] md:gap-x-[var(--feed-avatar-gap)] mb-2 ue-text-meta font-bold text-slate-500">
            <div class="flex justify-end items-center pr-2">
                <x-ui.icon name="repost" size="xs" class="text-slate-400" />
            </div>
            <div class="flex items-center gap-1">
                <span>{{ $reposterName }} đã đăng lại</span>
                @if ($repostedAt)
                    <span class="font-semibold">· {{ $repostedAt->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    @endif

    <div class="ue-post-card__body">
        {{-- Left Avatar Column --}}
        <div class="flex-shrink-0">
            <div class="relative">
                <a href="{{ $authorProfileUrl }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $author->name }}">
                    <x-ui.avatar :user="$author" size="md" />
                </a>
                @if ($showQuickFollow)
                    <button
                        type="button"
                        wire:click="openQuickFollowModal({{ $author->id }})"
                        wire:loading.attr="disabled"
                        wire:target="openQuickFollowModal({{ $author->id }})"
                        class="absolute -bottom-1 -right-1 bg-white text-slate-700 w-5 h-5 rounded-full flex items-center justify-center border border-slate-200 hover:scale-110 active:scale-95 transition-all shadow-xs z-10"
                        title="Theo dõi nhanh {{ $author->name }}"
                        aria-label="Theo dõi nhanh {{ $author->name }}"
                    >
                        <x-ui.icon name="plus" size="xxs" class="text-slate-750 font-bold" />
                    </button>
                @elseif ($showFollowCheck)
                    <button
                        type="button"
                        wire:click="openQuickFollowModal({{ $author->id }})"
                        wire:loading.attr="disabled"
                        wire:target="openQuickFollowModal({{ $author->id }})"
                        class="absolute -bottom-1 -right-1 bg-white text-slate-700 w-5 h-5 rounded-full flex items-center justify-center border border-slate-200 hover:scale-110 active:scale-95 transition-all shadow-xs z-10"
                        title="Đang theo dõi {{ $author->name }}"
                        aria-label="Đang theo dõi {{ $author->name }}"
                    >
                        <x-ui.icon name="check" size="xxs" class="text-slate-750 font-bold" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Right Content Column --}}
        <div class="flex-1 min-w-0">
            {{-- Post Author Header --}}
            <div class="ue-post-card__header">
                <div>
                    <div class="flex items-center gap-1.5 flex-nowrap min-w-0">
                        <a href="{{ $authorProfileUrl }}" class="ue-text-body-strong text-slate-850 truncate min-w-0 hover:text-ue-brand hover:underline">
                            {{ $author->name }}
                        </a>
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" aria-label="Đã xác thực" />
                        
                        {{-- Relative timestamp --}}
                        <span class="ue-post-card__meta flex-shrink-0 whitespace-nowrap">
                            · {{ ($post->published_at ?? $post->created_at)->diffForHumans() }}
                        </span>
                    </div>
                    
                    {{-- Faculty & Major --}}
                    @if ($profile)
                        <div class="ue-text-caption mt-1 leading-none text-slate-500 font-semibold">
                            {{ Str::ucfirst($profile->role_type) }}
                            @if ($profile->faculty)
                                · {{ $profile->faculty }}
                            @endif
                        </div>
                    @endif

                    @php
                        $tags = $post->tags ?? [];
                        if (empty($tags)) {
                            if ($post->post_type && $post->post_type !== \App\Enums\PostType::STANDARD) {
                                if ($post->post_type === \App\Enums\PostType::EXPERIENCE || $post->post_type === \App\Enums\PostType::CAREER_INSIGHT) {
                                    $tags[] = 'experience';
                                } elseif ($post->post_type === \App\Enums\PostType::OPPORTUNITY) {
                                    $tags[] = 'opportunity';
                                    if ($post->opportunity?->category === 'pedagogy') {
                                        $tags[] = 'pedagogy';
                                    }
                                }
                            }
                        }
                    @endphp

                    @if (! empty($tags) || ($post->post_type === \App\Enums\PostType::OPPORTUNITY && ($post->opportunity?->is_expired || $post->moderation_status !== \App\Enums\ModerationStatus::NONE)))
                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                            @foreach ($tags as $tag)
                                @if ($tag === 'experience')
                                    <x-ui.badge variant="experience" size="sm" no-icon>Kinh nghiệm</x-ui.badge>
                                @elseif ($tag === 'opportunity')
                                    <x-ui.badge variant="opportunity" size="sm" no-icon>Cơ hội</x-ui.badge>
                                @elseif ($tag === 'pedagogy')
                                    <x-ui.badge variant="pedagogy" size="sm" no-icon>Sư phạm</x-ui.badge>
                                @endif
                            @endforeach

                            @if ($post->post_type === \App\Enums\PostType::OPPORTUNITY)
                                @if ($post->opportunity?->is_expired)
                                    <x-ui.badge variant="danger" size="sm" no-icon>Đã hết hạn</x-ui.badge>
                                @endif
                                @if ($post->moderation_status === \App\Enums\ModerationStatus::PENDING)
                                    <x-ui.badge variant="pending" size="sm" no-icon>Chờ duyệt</x-ui.badge>
                                @elseif ($post->moderation_status === \App\Enums\ModerationStatus::REJECTED)
                                    <x-ui.badge variant="rejected" size="sm" no-icon>Đã từ chối</x-ui.badge>
                                @endif
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
                        class="ue-post-card__quick-hide text-slate-400 hover:text-slate-700 focus:ring-1 focus:ring-slate-200 focus:outline-none"
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
                <div class="ue-post-card__content mt-2.5">{{ $post->body }}</div>
                
                {{-- Polymorphic Media Grid --}}
                @if ($mediaCount > 0)
                    @php
                        $lightboxImages = $mediaItems->map(function ($item) use ($mediaUrlAction, $currentUser) {
                            return $mediaUrlAction->execute($item, 'detail', $currentUser) ?? $mediaUrlAction->execute($item, 'original', $currentUser);
                        })->values()->toJson();
                    @endphp
                    <div class="mt-2.5 w-full max-w-lg select-none mr-auto">
                        @if ($mediaCount === 1)
                            {{-- 1 image: full width, smart ratio --}}
                            @php($dimensions = $mediaDimensions($mediaItems[0]))
                            <div class="ue-media-frame">
                                <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: 0 } }))" class="block">
                                    <img
                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}"
                                        alt="Hình ảnh đính kèm"
                                        class="ue-media-image"
                                        loading="lazy"
                                        decoding="async"
                                        @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                        @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                    />
                                </a>
                            </div>
                        @elseif ($mediaCount === 2)
                            {{-- 2 images: two columns --}}
                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                @foreach ($mediaItems as $mediaItem)
                                    @php($dimensions = $mediaDimensions($mediaItem))
                                    <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-[4/3] overflow-hidden block">
                                        <img 
                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                            alt="Hình ảnh đính kèm" 
                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                            loading="lazy"
                                            decoding="async"
                                            @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                            @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                        />
                                    </a>
                                @endforeach
                            </div>
                        @elseif ($mediaCount === 3)
                            {{-- 3 images: one large + two stacked --}}
                            @php($dimensions = $mediaDimensions($mediaItems[0]))
                            <div class="grid grid-cols-3 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: 0 } }))" class="col-span-2 aspect-[4/3] overflow-hidden block">
                                    <img 
                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}" 
                                        alt="Hình ảnh" 
                                        class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                        loading="lazy"
                                        decoding="async"
                                        @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                        @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                    />
                                </a>
                                <div class="grid grid-rows-2 gap-2">
                                    @foreach ($mediaItems->slice(1, 2) as $mediaItem)
                                        @php($dimensions = $mediaDimensions($mediaItem))
                                        <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-square overflow-hidden block">
                                            <img 
                                                src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                                alt="Hình ảnh" 
                                                class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                loading="lazy"
                                                decoding="async"
                                                @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                                @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                            />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif ($mediaCount >= 4)
                            {{-- 4 images: 2x2 grid --}}
                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                @foreach ($mediaItems->take(4) as $mediaItem)
                                    @php($dimensions = $mediaDimensions($mediaItem))
                                    <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-[4/3] overflow-hidden block">
                                        <img 
                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                            alt="Hình ảnh" 
                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif (!empty($post->media_url))
                    <div class="mt-2.5 w-full max-w-lg select-none mr-auto">
                        <div class="ue-media-frame">
                            <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: ['{{ $post->media_url }}'], index: 0 } }))" class="block">
                                <img
                                    src="{{ $post->media_url }}"
                                    alt="Hình ảnh đính kèm"
                                    class="ue-media-image"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </a>
                        </div>
                    </div>
                @endif
                
                {{-- Edited Badge --}}
                @if ($post->status === \App\Enums\PostStatus::EDITED)
                    <span class="inline-block mt-2 ue-text-meta font-bold text-slate-500 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5">
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
                    wire:loading.attr="disabled"
                    wire:target="toggleLike({{ $post->id }})"
                />

                {{-- Comments Link --}}
                <a
                    href="{{ route('posts.show', $post) }}"
                    class="ue-action-button flex items-center gap-1.5 ue-text-caption font-bold text-slate-550 hover:text-ue-brand transition-colors"
                >
                    <x-ui.icon name="message-circle" size="md" class="ue-action-button__icon text-current" />
                    <span class="ue-action-button__count">{{ $commentCount }}</span>
                </a>

                {{-- Share --}}
                <x-ui.post-action-button
                    icon="send"
                    label="Chia sẻ"
                    wireClick="startShare({{ $post->id }})"
                    wire:loading.attr="disabled"
                    wire:target="startShare({{ $post->id }})"
                />

                {{-- Repost --}}
                @if ($showRepostAction && ! $isOwner)
                    <x-ui.post-action-button
                        icon="repost"
                        activeIcon="repost"
                        label="Đăng lại"
                        :count="$repostCount"
                        :selected="$isReposted"
                        wireClick="toggleRepost({{ $post->id }})"
                        wire:loading.attr="disabled"
                        wire:target="toggleRepost({{ $post->id }})"
                    />
                @endif

                {{-- Save Toggle --}}
                <div class="ml-auto">
                    <x-ui.post-action-button
                        icon="bookmark"
                        activeIcon="bookmark"
                        label="Lưu"
                        :selected="$isSaved"
                        wireClick="toggleSave({{ $post->id }})"
                        wire:loading.attr="disabled"
                        wire:target="toggleSave({{ $post->id }})"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
