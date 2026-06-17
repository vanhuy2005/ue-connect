@php
    $groups = \App\Support\Navigation\AdminNavigation::getVisibleGroups();
    $activeGroupKey = null;
    $activeGroup = null;

    // Determine active group
    foreach ($groups as $groupKey => $g) {
        if (request()->routeIs('admin.console') && (request()->route('group') ?? 'overview') === $groupKey) {
            $activeGroupKey = $groupKey;
            $activeGroup = $g;
            break;
        }

        foreach ($g['items'] as $i) {
            $routeParts = explode('.', $i['route']);
            $baseRouteName = count($routeParts) >= 2 ? $routeParts[0] . '.' . $routeParts[1] : $i['route'];

            if (request()->routeIs($i['route']) || request()->routeIs($baseRouteName . '.*')) {
                $activeGroupKey = $groupKey;
                $activeGroup = $g;
                break 2;
            }
        }
    }

    if (!$activeGroup && !empty($groups)) {
        $activeGroupKey = array_key_first($groups);
        $activeGroup = $groups[$activeGroupKey];
    }
@endphp

@if($activeGroup)
<aside class="hidden lg:flex flex-col w-80 bg-white border-r border-slate-200 flex-shrink-0 p-4 sticky top-0 h-full overflow-y-auto">
    <div class="mb-4">
        <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $activeGroup['vn_label'] }}</h2>
        <p class="text-[11px] text-slate-500 font-bold mt-1 leading-relaxed">{{ $activeGroup['description'] }}</p>
    </div>

    <nav class="flex-1 space-y-1">
        @foreach($activeGroup['items'] as $item)
            @php
                $routeParts = explode('.', $item['route']);
                $baseRouteName = count($routeParts) >= 2 ? $routeParts[0] . '.' . $routeParts[1] : $item['route'];
                $isActive = request()->routeIs($item['route']) || request()->routeIs($baseRouteName . '.*');
            @endphp
            <a href="{{ route($item['route']) }}" wire:navigate.hover class="ue-sidebar-subnav-link {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                <span class="flex-1 text-left truncate">{{ $item['label'] }}</span>
                @if(isset($item['badge']) && $item['badge'] > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 ml-2 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-ue-brand text-white' : 'bg-red-500 text-white shadow-xs' }}">
                        {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>
</aside>
@endif
