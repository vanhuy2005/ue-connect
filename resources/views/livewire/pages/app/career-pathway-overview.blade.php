<?php

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionVisibility;
use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\CareerPosition;
use App\Models\CareerProgram;
use App\Models\CareerUserPathway;
use Livewire\Volt\Component;

new class extends Component
{
    public function with(): array
    {
        return [
            'stats' => [
                ['label' => 'chương trình đào tạo', 'value' => CareerProgram::publicReady()->count()],
                ['label' => 'môn học', 'value' => CareerCourse::query()->count()],
                ['label' => 'đóng góp cộng đồng', 'value' => CareerContribution::query()
                    ->where('status', CareerContributionStatus::APPROVED->value)
                    ->where('visibility', CareerContributionVisibility::PUBLIC->value)
                    ->count()],
                ['label' => 'vị trí nghề nghiệp', 'value' => CareerPosition::query()
                    ->where('status', CareerPositionStatus::PUBLISHED->value)
                    ->where('visibility', CareerPositionVisibility::PUBLIC->value)
                    ->count()],
                ['label' => 'hành trình đã chia sẻ', 'value' => CareerUserPathway::query()
                    ->where('status', CareerUserPathwayStatus::PUBLISHED->value)
                    ->where('visibility', CareerUserPathwayVisibility::PUBLIC->value)
                    ->count()],
            ],
        ];
    }
}; ?>

<x-career-pathway.shell
    title="Bản đồ học tập HCMUE"
    subtitle="Khám phá chương trình đào tạo, kinh nghiệm môn học và lộ trình nghề nghiệp từ cộng đồng UE-Connect."
>
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['icon' => 'map', 'title' => 'Xem chương trình đào tạo', 'body' => 'Chọn khóa, khoa, ngành để xem lộ trình học theo từng học kỳ.', 'href' => route('app.career-pathway.programs')],
                ['icon' => 'book-open', 'title' => 'Tìm môn học', 'body' => 'Tra cứu mã môn, mô tả chính thức, kỹ năng và chia sẻ từ cộng đồng.', 'href' => route('app.career-pathway.courses')],
                ['icon' => 'briefcase', 'title' => 'Khám phá vị trí nghề nghiệp', 'body' => 'Xem các hướng nghề được liên kết với môn học, kỹ năng và project.', 'href' => route('app.career-pathway.positions.index')],
                ['icon' => 'users', 'title' => 'Đọc hành trình anh/chị khóa trước', 'body' => 'Hiểu cách người đi trước chọn môn, làm project và chuẩn bị thực tập.', 'href' => route('app.career-pathway.senior-pathways.index')],
            ] as $action)
                <a href="{{ $action['href'] }}" wire:navigate.hover class="group rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-ue-brand-soft text-ue-brand-active">
                        <x-ui.icon :name="$action['icon']" size="md" />
                    </div>
                    <h2 class="mt-4 text-sm font-extrabold text-slate-900">{{ $action['title'] }}</h2>
                    <p class="mt-2 text-xs font-medium leading-5 text-slate-500">{{ $action['body'] }}</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-ue-brand-active">
                        Mở mục này
                        <x-ui.icon name="arrow-right" size="xs" class="transition group-hover:translate-x-0.5" />
                    </span>
                </a>
            @endforeach
        </section>

        @if(collect($stats)->sum('value') > 0)
            <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Dữ liệu hiện có</h2>
                        <p class="text-xs font-medium text-slate-500">Tổng quan nhanh về dữ liệu chính thức và tri thức cộng đồng đang được mở cho sinh viên.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-500">Chỉ hiển thị dữ liệu công khai</span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach($stats as $stat)
                        <div class="rounded-xl bg-slate-50 px-4 py-4">
                            <div class="text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($stat['value']) }}</div>
                            <div class="mt-1 text-xs font-bold text-slate-500">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                <div class="max-w-2xl">
                    <h2 class="text-base font-extrabold text-slate-900">Bản đồ học tập đang chờ dữ liệu đầu tiên</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                        Khi dữ liệu chương trình, môn học hoặc chia sẻ cộng đồng được import, khu vực này sẽ trở thành cửa vào để sinh viên khám phá toàn bộ Career Pathway.
                    </p>
                    <a href="{{ route('app.career-pathway.programs') }}" wire:navigate.hover class="mt-4 inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-bold text-ue-brand-active transition hover:bg-ue-brand-soft">
                        Kiểm tra chương trình đào tạo
                        <x-ui.icon name="arrow-right" size="sm" />
                    </a>
                </div>
            </section>
        @endif

        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                <p class="text-xs font-bold text-ue-brand-active">Dữ liệu chính thức</p>
                <h3 class="mt-2 text-sm font-extrabold text-slate-900">Chương trình, học kỳ và môn học</h3>
                <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Phần này dùng dữ liệu curriculum HCMUE, không trộn với ghi chú cá nhân.</p>
            </div>
            <div class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                <p class="text-xs font-bold text-ue-brand-active">Tri thức cộng đồng</p>
                <h3 class="mt-2 text-sm font-extrabold text-slate-900">Kỹ năng, project, tài liệu và kinh nghiệm</h3>
                <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Chỉ các chia sẻ phù hợp trạng thái công khai mới xuất hiện trong khu vực sinh viên.</p>
            </div>
            <div class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                <p class="text-xs font-bold text-ue-brand-active">Trải nghiệm cá nhân</p>
                <h3 class="mt-2 text-sm font-extrabold text-slate-900">Hành trình từ anh/chị khóa trước</h3>
                <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Câu chuyện được trình bày theo ngữ cảnh học tập, không lộ dữ liệu riêng tư không cần thiết.</p>
            </div>
        </section>
    </div>
</x-career-pathway.shell>
