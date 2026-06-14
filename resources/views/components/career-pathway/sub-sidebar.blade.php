@php
    $user = auth()->user();
    $canManageCareerPathway = $user?->hasRole('admin') || ($user?->canAny([
        'view_admin_dashboard',
        'manage_permissions',
        'review_verification',
        'manage_reports',
    ]) ?? false);

    $groups = [
        'Học tập' => [
            [
                'icon' => 'home',
                'label' => 'Tổng quan',
                'href' => route('app.career-pathway.index'),
                'active' => request()->routeIs('app.career-pathway.index'),
            ],
            [
                'icon' => 'map',
                'label' => 'Chương trình đào tạo',
                'href' => route('app.career-pathway.programs'),
                'active' => request()->routeIs('app.career-pathway.programs'),
            ],
            [
                'icon' => 'book-open',
                'label' => 'Môn học & tri thức',
                'href' => route('app.career-pathway.courses'),
                'active' => request()->routeIs('app.career-pathway.courses*'),
            ],
        ],
        'Cộng đồng' => [
            [
                'icon' => 'briefcase',
                'label' => 'Vị trí nghề nghiệp',
                'href' => route('app.career-pathway.positions.index'),
                'active' => request()->routeIs('app.career-pathway.positions.*'),
            ],
            [
                'icon' => 'users',
                'label' => 'Hành trình anh/chị khóa trước',
                'href' => route('app.career-pathway.senior-pathways.index'),
                'active' => request()->routeIs('app.career-pathway.senior-pathways.*'),
            ],
            [
                'icon' => 'bookmark',
                'label' => 'Đã lưu',
                'href' => route('app.career-pathway.saved'),
                'active' => request()->routeIs('app.career-pathway.saved'),
            ],
        ],
    ];

    $adminItems = [
        [
            'icon' => 'database',
            'label' => 'Quản trị dữ liệu',
            'href' => route('app.career-pathway.admin.data-quality'),
            'active' => request()->routeIs('app.career-pathway.admin.data-quality'),
        ],
        [
            'icon' => 'refresh-cw',
            'label' => 'Import runs',
            'href' => route('app.career-pathway.admin.import-runs'),
            'active' => request()->routeIs('app.career-pathway.admin.import-runs'),
        ],
        [
            'icon' => 'alert-triangle',
            'label' => 'Vấn đề dữ liệu',
            'href' => route('app.career-pathway.admin.issues'),
            'active' => request()->routeIs('app.career-pathway.admin.issues'),
        ],
    ];
@endphp

<aside data-career-pathway-sub-sidebar class="border-b border-ue-border/80 bg-white lg:sticky lg:top-0 lg:h-screen lg:w-80 lg:shrink-0 lg:self-start lg:overflow-y-auto lg:border-b-0 lg:border-r">
    <div class="flex gap-2 overflow-x-auto bg-white px-4 py-3 lg:min-h-screen lg:flex-col lg:overflow-visible lg:px-5 lg:py-7">
        <div class="hidden px-2 pb-4 lg:block">
            <p class="text-xs font-extrabold uppercase tracking-wider text-ue-text-muted">Bản đồ học tập</p>
            <p class="mt-1.5 max-w-[230px] text-sm font-medium leading-6 text-slate-500">Chương trình chính thức, tri thức cộng đồng và lộ trình nghề trong một nơi.</p>
        </div>

        @foreach($groups as $groupLabel => $items)
            <nav class="flex shrink-0 gap-2 lg:flex-col lg:gap-1" aria-label="{{ $groupLabel }}">
                <p class="hidden px-2 pt-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 lg:block">{{ $groupLabel }}</p>
                @foreach($items as $item)
                    <a
                        href="{{ $item['href'] }}"
                        wire:navigate.hover
                        @class([
                            'inline-flex min-h-11 items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition active:translate-y-px lg:w-full',
                            'bg-ue-brand-soft text-ue-brand-active ring-1 ring-ue-brand/20' => $item['active'],
                            'text-slate-600 hover:bg-ue-brand-soft/60 hover:text-ue-brand-active' => ! $item['active'],
                        ])
                        @if($item['active']) aria-current="page" @endif
                    >
                        <x-ui.icon :name="$item['icon']" size="sm" class="shrink-0" />
                        <span class="whitespace-nowrap lg:whitespace-normal">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endforeach

        @if($canManageCareerPathway)
            <nav class="flex shrink-0 gap-2 border-l border-ue-border pl-2 lg:mt-4 lg:flex-col lg:gap-1 lg:border-l-0 lg:border-t lg:pl-0 lg:pt-4" aria-label="Quản trị Career Pathway">
                <p class="hidden px-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 lg:block">Quản trị dữ liệu</p>
                @foreach($adminItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        wire:navigate.hover
                        @class([
                            'inline-flex min-h-11 items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-extrabold transition active:translate-y-px lg:w-full',
                            'bg-amber-50 text-amber-800 ring-1 ring-amber-200' => $item['active'],
                            'text-slate-600 hover:bg-amber-50 hover:text-amber-800' => ! $item['active'],
                        ])
                        @if($item['active']) aria-current="page" @endif
                    >
                        <x-ui.icon :name="$item['icon']" size="sm" class="shrink-0" />
                        <span class="whitespace-nowrap lg:whitespace-normal">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
</aside>
