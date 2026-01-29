@if($content->scheduled_at)
    <div class="d-flex flex-column">
        <span class="text-gray-800 fw-semibold fs-7">
            {{ $content->scheduled_at->format('d M Y') }}
        </span>
        <span class="text-gray-500 fs-8">
            {{ $content->scheduled_at->format('H:i') }}
        </span>
    </div>
@elseif($content->published_at)
    <div class="d-flex flex-column">
        <span class="text-success fw-semibold fs-7">
            {{ $content->published_at->format('d M Y') }}
        </span>
        <span class="text-gray-500 fs-8">
            {{ $content->published_at->format('H:i') }}
        </span>
    </div>
@else
    <span class="text-gray-400">-</span>
@endif
