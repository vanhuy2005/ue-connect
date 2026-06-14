<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $query = '';

    public function search(): void
    {
        if (! empty($this->query)) {
            $this->redirectRoute('app.career-pathway.search', ['q' => $this->query]);
        }
    }
}; ?>

<div class="relative w-full max-w-lg">
    <form wire:submit="search">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input wire:model="query" type="search" class="block w-full rounded-xl border border-ue-border bg-slate-50 p-3 pl-10 text-sm text-slate-900 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15" placeholder="Tìm môn học, kỹ năng, vị trí nghề nghiệp...">
            <button type="submit" class="absolute bottom-2 right-2.5 rounded-lg bg-ue-brand px-4 py-1.5 text-sm font-bold text-white hover:bg-ue-brand-active focus:outline-none focus:ring-4 focus:ring-ue-brand/20">Tìm</button>
        </div>
    </form>
</div>
