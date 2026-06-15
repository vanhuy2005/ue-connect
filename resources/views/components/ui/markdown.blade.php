@props(['content'])

<div class="prose prose-sm dark:prose-invert max-w-none markdown-content">
    {!! \Illuminate\Support\Str::markdown($content ?? '', [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]) !!}
</div>

@once
<style>
    .markdown-content ul {
        list-style-type: disc !important;
        margin-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        padding-left: 0.5rem !important;
    }
    .markdown-content ol {
        list-style-type: decimal !important;
        margin-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        padding-left: 0.5rem !important;
    }
    .markdown-content li {
        margin-bottom: 0.25rem !important;
        display: list-item !important;
    }
    .markdown-content strong {
        font-weight: 700 !important;
    }
    .markdown-content p {
        margin-bottom: 0.5rem !important;
    }
    .markdown-content p:last-child {
        margin-bottom: 0 !important;
    }
</style>
@endonce
