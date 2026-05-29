{{--
    UEConnect Feed Shimmer Skeleton Card
    Source: docs/04-design/12-component-primitives.md §Skeleton
--}}
<div class="ue-skeleton-card ue-animate-fade-in" {{ $attributes }}>
    <div class="flex items-center gap-3">
        <div class="ue-skeleton-avatar ue-skeleton"></div>
        <div class="flex-grow space-y-1.5">
            <div class="ue-skeleton-line ue-skeleton w-1/4"></div>
            <div class="ue-skeleton-line ue-skeleton w-1/3"></div>
        </div>
    </div>
    <div class="space-y-2 mt-3">
        <div class="ue-skeleton-line ue-skeleton w-full"></div>
        <div class="ue-skeleton-line ue-skeleton w-11/12"></div>
        <div class="ue-skeleton-line ue-skeleton w-4/5"></div>
    </div>
</div>
