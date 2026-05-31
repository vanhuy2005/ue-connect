<x-app-layout>
    <x-slot name="title">Chi tiết tài khoản - {{ $user->name }}</x-slot>
    <livewire:pages.admin.users-detail :user="$user" />
</x-app-layout>
