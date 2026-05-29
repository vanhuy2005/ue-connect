<x-app-layout>
    <x-slot name="title">Tin nhắn</x-slot>
    <livewire:pages.app.messages :active-conversation="$activeConversation ?? null" />
</x-app-layout>
