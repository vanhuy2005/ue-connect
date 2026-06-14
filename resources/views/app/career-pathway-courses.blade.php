<x-app-layout shell="social">
    <x-slot name="title">Môn học & tri thức cộng đồng</x-slot>
    <livewire:pages.app.career-pathway-courses :course="$course ?? null" />
</x-app-layout>
