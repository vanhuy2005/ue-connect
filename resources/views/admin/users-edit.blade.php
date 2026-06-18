<x-app-layout shell="admin">
    <x-slot name="title">Chỉnh sửa tài khoản - {{ $user->name }}</x-slot>
    <livewire:pages.admin.users-form :user="$user" />
</x-app-layout>
