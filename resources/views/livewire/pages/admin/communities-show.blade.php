<?php

use App\Models\Community;
use Livewire\Volt\Component;

new class extends Component {
    public int $communityId;
    public ?Community $community = null;

    public function mount(int $id): void
    {
        $this->communityId = $id;
        $this->community = Community::findOrFail($id);
    }
};
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-ue-text">{{ $this->community->name }}</h1>
    <p class="text-sm text-ue-text-muted">{{ $this->community->description }}</p>

    <x-ui.card class="mt-6">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <strong>Người tạo:</strong> {{ $this->community->creator?->name ?? 'N/A' }}
            </div>
            <div>
                <strong>Trạng thái:</strong> {{ ucfirst($this->community->status) }}
            </div>
        </div>
    </x-ui.card>
</div>