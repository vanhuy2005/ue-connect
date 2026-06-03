<?php // This view now delegates creation to the controller route: admin.announcements.store ?>

<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold">Tạo thông báo</h1>

    <x-ui.card class="mt-6">
        <form action="{{ route('admin.announcements.store') }}" method="POST" class="grid grid-cols-1 gap-4">
            @csrf
            <div>
                <x-ui.label for="title">Tiêu đề</x-ui.label>
                <x-ui.input id="title" name="title" value="{{ old('title') }}" />
            </div>

            <div>
                <x-ui.label for="body">Nội dung</x-ui.label>
                <textarea id="body" name="body" rows="6" class="w-full border rounded-lg px-3 py-2">{{ old('body') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-ui.label for="type">Loại</x-ui.label>
                    <x-ui.select id="type" name="type">
                        <option value="system_announcement" {{ old('type') === 'system_announcement' ? 'selected' : '' }}>System</option>
                        <option value="feature_update" {{ old('type') === 'feature_update' ? 'selected' : '' }}>Feature</option>
                        <option value="safety_notice" {{ old('type') === 'safety_notice' ? 'selected' : '' }}>Safety</option>
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.label for="status">Trạng thái</x-ui.label>
                    <x-ui.select id="status" name="status">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </x-ui.select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-ue-brand text-white rounded-lg">Tạo</button>
            </div>
        </form>
    </x-ui.card>
</div>
