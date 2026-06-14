@props(['count' => 5])

<div class="ue-feed-list select-none">
    @for ($i = 0; $i < $count; $i++)
        <div class="ue-feed-item">
            <div class="ue-post-card">
                <div class="ue-post-card__body">
                    {{-- Left Avatar Column --}}
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full ue-skeleton"></div>
                        </div>
                    </div>

                    {{-- Right Content Column --}}
                    <div class="flex-1 min-w-0">
                        {{-- Post Author Header --}}
                        <div class="ue-post-card__header">
                            <div>
                                <div class="flex items-center gap-1.5 flex-nowrap min-w-0">
                                    <div class="ue-skeleton h-4 w-24"></div>
                                    <div class="ue-skeleton h-3.5 w-16"></div>
                                </div>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <div class="ue-skeleton h-3 w-16"></div>
                                </div>
                            </div>
                            
                            {{-- Header Actions Side-by-Side --}}
                            <div class="flex items-center gap-1.5">
                                <div class="ue-skeleton h-6 w-6 rounded-md"></div>
                            </div>
                        </div>

                        {{-- Body Content --}}
                        <div class="ue-post-card__content mt-2.5">
                            <div class="space-y-2">
                                <div class="ue-skeleton h-3.5 w-11/12"></div>
                                <div class="ue-skeleton h-3.5 w-full"></div>
                                <div class="ue-skeleton h-3.5 w-4/5"></div>
                            </div>
                        </div>

                        {{-- Image block skeleton (optional, alternate items) --}}
                        @if ($i % 2 === 1)
                            <div class="mt-2.5 w-full max-w-lg select-none mr-auto">
                                <div class="ue-media-frame">
                                    <div class="ue-skeleton w-full aspect-video"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Standard Action Buttons Bar --}}
                        <div class="ue-post-card__actions gap-x-4 sm:gap-x-6">
                            <div class="ue-skeleton h-[38px] w-[52px] rounded-lg"></div>
                            <div class="ue-skeleton h-[38px] w-[52px] rounded-lg"></div>
                            <div class="ue-skeleton h-[38px] w-[52px] rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endfor
</div>

