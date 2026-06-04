<?php

use App\Models\Community;
use App\Enums\CommunityType;
use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityVisibility;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $description = '';
    public string $type = 'club';
    public string $join_policy = 'approval_required';
    public string $visibility = 'public';
    public string $related_faculty = '';
    public string $status = 'active';
    public string $rules = '';
    public string $target_members = '';

    public function create()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:communities,name'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:' . implode(',', array_column(CommunityType::cases(), 'value'))],
            'join_policy' => ['required', 'string', 'in:' . implode(',', array_column(CommunityJoinPolicy::cases(), 'value'))],
            'visibility' => ['required', 'string', 'in:' . implode(',', array_column(CommunityVisibility::cases(), 'value'))],
            'related_faculty' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,active,inactive'],
            'rules' => ['nullable', 'string', 'max:5000'],
            'target_members' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function (): void {
            $community = Community::create([
                'name' => $this->name,
                'slug' => $this->generateUniqueSlug($this->name),
                'description' => $this->description ?: null,
                'type' => $this->type,
                'join_policy' => $this->join_policy,
                'visibility' => $this->visibility,
                'related_faculty' => $this->related_faculty ?: null,
                'rules' => $this->rules ?: null,
                'settings' => ['target_members' => $this->target_members ?: null],
                'status' => $this->status,
                'created_by' => auth()->id(),
                'owner_id' => auth()->id(),
            ]);

            \App\Models\CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => $community->owner_id,
                'role' => \App\Enums\CommunityMemberRole::Owner->value,
                'status' => \App\Enums\CommunityMemberStatus::Active->value,
                'joined_at' => now(),
            ]);
            $community->increment('members_count');

            AuditLogService::log(
                actorId: auth()->id(),
                actorType: 'admin',
                actionKey: 'community.create',
                targetType: 'community',
                targetId: $community->id,
                beforeSnapshot: null,
                afterSnapshot: $community->toArray(),
                reason: 'created from admin UI'
            );

            session()->flash('success', 'Tạo cộng đồng thành công.');
        });

        return redirect()->route('admin.communities.index');
    }

    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Community::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
};
?>

<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-ue-text mb-4">Tạo cộng đồng mới</h1>

    <x-ui.card>
        <form wire:submit.prevent="create" class="grid grid-cols-1 gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ue-text mb-1">Tên cộng đồng <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live="name"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}"
                        placeholder="Tên cộng đồng">
                    @error('name') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Loại cộng đồng <span class="text-red-500">*</span></label>
                    <select wire:model.live="type"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('type') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}">
                        @foreach (CommunityType::cases() as $cType)
                            <option value="{{ $cType->value }}">{{ $cType->label() }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Join Policy --}}
                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Chính sách tham gia <span class="text-red-500">*</span></label>
                    <select wire:model.live="join_policy"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('join_policy') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}">
                        @foreach (CommunityJoinPolicy::cases() as $jPolicy)
                            <option value="{{ $jPolicy->value }}">{{ $jPolicy->label() }}</option>
                        @endforeach
                    </select>
                    @error('join_policy') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Visibility --}}
                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Trạng thái hiển thị <span class="text-red-500">*</span></label>
                    <select wire:model.live="visibility"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('visibility') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}">
                        @foreach (CommunityVisibility::cases() as $vis)
                            <option value="{{ $vis->value }}">{{ $vis->label() }}</option>
                        @endforeach
                    </select>
                    @error('visibility') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Trạng thái hoạt động <span class="text-red-500">*</span></label>
                    <select wire:model.live="status"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('status') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}">
                        <option value="draft">Bản nháp</option>
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Ngưng hoạt động</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Faculty --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ue-text mb-1">Khoa liên quan</label>
                    <input type="text" wire:model.live="related_faculty"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('related_faculty') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}"
                        placeholder="VD: Khoa Công nghệ thông tin">
                    @error('related_faculty') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Target Members --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ue-text mb-1">Đối tượng hướng tới</label>
                    <input type="text" wire:model.live="target_members"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('target_members') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}"
                        placeholder="VD: Sinh viên ngành CNTT, K48-K51...">
                    @error('target_members') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ue-text mb-1">Mô tả</label>
                    <textarea wire:model.live="description"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('description') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}"
                        rows="4" placeholder="Nhập mô tả chi tiết về cộng đồng..."></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Rules --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-ue-text mb-1">Nội quy cộng đồng</label>
                    <textarea wire:model.live="rules"
                        class="w-full px-3 py-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-ue-brand {{ $errors->has('rules') ? 'border-red-500 focus:ring-red-500' : 'border-ue-border' }}"
                        rows="3" placeholder="Nhập nội quy, quy định khi tham gia cộng đồng..."></textarea>
                    @error('rules') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-ue-brand text-white rounded-lg font-semibold text-sm hover:bg-opacity-90 transition">Tạo cộng đồng</button>
            </div>
        </form>
    </x-ui.card>
</div>