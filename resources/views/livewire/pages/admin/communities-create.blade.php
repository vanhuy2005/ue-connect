<?php

use App\Models\Community;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $description = '';

    public function create()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:communities,name'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function (): void {
            $community = Community::create([
                'name' => $this->name,
                'slug' => $this->generateUniqueSlug($this->name),
                'description' => $this->description ?: null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

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
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Tên cộng đồng</label>
                    <input type="text" wire:model.live="name" class="w-full px-3 py-2 border rounded-lg" placeholder="Tên cộng đồng">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-ue-text mb-1">Mô tả</label>
                    <textarea wire:model.live="description" class="w-full px-3 py-2 border rounded-lg" rows="4"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-ue-brand text-white rounded-lg">Tạo</button>
                </div>
            </div>
        </form>
    </x-ui.card>
</div>