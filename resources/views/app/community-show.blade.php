<x-app-layout>
    <x-slot name="title">{{ $community->name }}</x-slot>
    <livewire:pages.app.community-show :community="$community" />
</x-app-layout>
