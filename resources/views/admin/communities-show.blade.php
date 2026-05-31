<x-app-layout shell="admin">
    <x-slot name="title">Chi tiết cộng đồng</x-slot>
    <livewire:pages.admin.communities-show :id="$community->id" />
</x-app-layout>
