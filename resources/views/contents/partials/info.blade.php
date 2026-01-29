<div class="d-flex flex-column">
    <a href="{{ route('contents.show', $content) }}" class="text-gray-800 fw-bold text-hover-primary fs-6 mb-1">
        {{ $content->title ?? Str::limit($content->caption, 50) ?? __('messages.content') . ' #' . $content->id }}
    </a>
    <div class="d-flex align-items-center text-gray-500 fs-7">
        @if($content->duration)
            <i class="bi bi-clock me-1"></i>
            <span class="me-3">{{ $content->formatted_duration }}</span>
        @endif
        @if($content->file_size)
            <i class="bi bi-hdd me-1"></i>
            <span>{{ $content->formatted_file_size }}</span>
        @endif
    </div>
    <div class="text-gray-500 fs-8 mt-1">
        {{ __('messages.created_at') }}: {{ $content->created_at->format('d M Y H:i') }}
    </div>
</div>
