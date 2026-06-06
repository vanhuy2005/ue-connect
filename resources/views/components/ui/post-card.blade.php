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
    $authorProfileUrl = route('profile.show', $author);
    $isOwner = $post->user_id === $currentUser->id;
    $isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));
    $postType = $post->post_type?->value ?? 'standard';
    $opportunity = $post->opportunityDetail;
    $mediaUrlAction = app(\App\Actions\Media\GenerateMediaUrlAction::class);
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
    wire:key="post-card-{{ $post->id }}"
>
    <div class="ue-post-card__body">
        {{-- Left Avatar Column --}}
        <div class="flex-shrink-0">
            <a href="{{ $authorProfileUrl }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $author->name }}">
                <x-ui.avatar :user="$author" size="md" />
            </a>
        </div>

        {{-- Right Content Column --}}
        <div class="flex-1 min-w-0">
            {{-- Post Author Header --}}
            <div class="ue-post-card__header">
                <div>
                    <div class="flex items-center gap-1.5 flex-nowrap min-w-0">
                        <a href="{{ $authorProfileUrl }}" class="text-sm font-bold text-slate-800 leading-tight truncate min-w-0 hover:text-ue-brand hover:underline">
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
                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 leading-none flex items-center flex-wrap gap-x-1.5">
                            <span>{{ Str::ucfirst($profile->role_type) }}</span>
                            @if ($profile->faculty)
                                <span>· {{ $profile->faculty }}</span>
                            @endif
                            {{-- Post Type Badge --}}
                            @if ($postType === 'experience_share')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-white text-slate-500 border border-slate-200 leading-none">
                                    Kinh nghiệm
                                </span>
                            @elseif ($postType === 'mentor_insight')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-white text-slate-500 border border-slate-200 leading-none">
                                    Career Insight
                                </span>
                            @elseif ($postType === 'opportunity')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-white text-slate-500 border border-slate-200 leading-none">
                                    Cơ hội
                                </span>
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
                <div class="ue-post-card__content mt-1">{{ $post->body }}</div>

                
                {{-- Opportunity Detail Card --}}
                @if ($postType === 'opportunity' && $opportunity)
                    <div class="mt-2.5 p-3 bg-white border border-slate-200 rounded-xl space-y-1.5">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="building" size="xs" class="text-ue-brand flex-shrink-0" />
                            <span class="text-xs font-bold text-slate-800">{{ $opportunity->company }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="briefcase" size="xs" class="text-ue-brand flex-shrink-0" />
                            <span class="text-xs font-semibold text-slate-700">{{ $opportunity->position }}</span>
                        </div>
                        @if ($opportunity->location)
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="map-pin" size="xs" class="text-slate-400 flex-shrink-0" />
                                <span class="text-xs text-slate-600">{{ $opportunity->location }}</span>
                            </div>
                        @endif
                        @if ($opportunity->application_deadline)
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="calendar" size="xs" class="text-slate-400 flex-shrink-0" />
                                <span class="text-xs text-slate-600">
                                    Hạn nộp: {{ $opportunity->application_deadline->format('d/m/Y') }}
                                </span>
                            </div>
                        @endif
                        @if ($opportunity->application_url)
                            <div class="pt-1">
                                <a href="{{ $opportunity->application_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold rounded-lg transition-colors">
                                    <x-ui.icon name="external-link" size="3xs" />
                                    Ứng tuyển ngay
                                </a>
                            </div>
                        @endif
                        @if ($opportunity->field_tags)
                            <div class="flex flex-wrap gap-1 pt-1">
                                @foreach ($opportunity->field_tags as $tag)
                                    <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-bold rounded-full leading-none">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Polymorphic Media Grid --}}
                @if ($mediaCount > 0)
                    <div class="mt-2.5 max-w-lg select-none">
                        @if ($mediaCount === 1)
                            {{-- 1 image: full width, smart ratio --}}
                            @php($dimensions = $mediaDimensions($mediaItems[0]))
                            <div class="overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                <a href="{{ $mediaUrlAction->execute($mediaItems[0], 'detail', $currentUser) ?? $mediaUrlAction->execute($mediaItems[0], 'original', $currentUser) }}" target="_blank" rel="noopener noreferrer" class="block">
                                    <img
                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}"
                                        alt="Hình ảnh đính kèm"
                                        class="w-full h-auto object-cover max-h-[360px] hover:scale-[1.01] transition-transform duration-300 cursor-zoom-in"
                                        loading="lazy"
                                        @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                        @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                    />
                                </a>
                            </div>
                        @elseif ($mediaCount === 2)
                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                @foreach ($mediaItems as $mediaItem)
                                    @php($dimensions = $mediaDimensions($mediaItem))
                                    <a href="{{ $mediaUrlAction->execute($mediaItem, 'detail', $currentUser) ?? $mediaUrlAction->execute($mediaItem, 'original', $currentUser) }}" target="_blank" rel="noopener noreferrer" class="aspect-[4/3] overflow-hidden block">
                                        <img 
                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                            alt="Hình ảnh đính kèm" 
                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                            loading="lazy"
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
                                <a href="{{ $mediaUrlAction->execute($mediaItems[0], 'detail', $currentUser) ?? $mediaUrlAction->execute($mediaItems[0], 'original', $currentUser) }}" target="_blank" rel="noopener noreferrer" class="col-span-2 aspect-[4/3] overflow-hidden block">
                                    <img 
                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}" 
                                        alt="Hình ảnh" 
                                        class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                        loading="lazy"
                                        @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                        @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                    />
                                </a>
                                <div class="grid grid-rows-2 gap-2">
                                    @foreach ($mediaItems->slice(1, 2) as $mediaItem)
                                        @php($dimensions = $mediaDimensions($mediaItem))
                                        <a href="{{ $mediaUrlAction->execute($mediaItem, 'detail', $currentUser) ?? $mediaUrlAction->execute($mediaItem, 'original', $currentUser) }}" target="_blank" rel="noopener noreferrer" class="aspect-square overflow-hidden block">
                                            <img 
                                                src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                                alt="Hình ảnh" 
                                                class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                loading="lazy"
                                                @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                                @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                            />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif ($mediaCount >= 4)
                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                @foreach ($mediaItems->take(4) as $mediaItem)
                                    @php($dimensions = $mediaDimensions($mediaItem))
                                    <a href="{{ $mediaUrlAction->execute($mediaItem, 'detail', $currentUser) ?? $mediaUrlAction->execute($mediaItem, 'original', $currentUser) }}" target="_blank" rel="noopener noreferrer" class="aspect-[4/3] overflow-hidden block">
                                        <img 
                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                            alt="Hình ảnh" 
                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                            loading="lazy"
                                            @if($dimensions['width']) width="{{ $dimensions['width'] }}" @endif
                                            @if($dimensions['height']) height="{{ $dimensions['height'] }}" @endif
                                        />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif (!empty($post->media_url))
                    <div class="ue-post-card__media mt-2.5 overflow-hidden rounded-xl border border-slate-150 max-w-lg select-none bg-slate-50">
                        <a href="{{ $post->media_url }}" target="_blank" rel="noopener noreferrer" class="block">
                            <img
                                src="{{ $post->media_url }}"
                                alt="Hình ảnh đính kèm"
                                class="w-full h-auto object-cover max-h-[360px] hover:scale-[1.01] transition-transform duration-300 cursor-zoom-in"
                                loading="lazy"
                            />
                        </a>
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
