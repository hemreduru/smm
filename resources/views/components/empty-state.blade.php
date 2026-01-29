@props([
    'icon' => 'bi-inbox',
    'title',
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => 'bi-plus-lg'
])

<div {{ $attributes->merge(['class' => 'text-center py-15']) }}>
    <i class="bi {{ $icon }} text-gray-400 fs-3x mb-5 d-block"></i>
    <div class="text-gray-600 fs-5 fw-semibold mb-3">{{ $title }}</div>
    @if($description)
        <p class="text-gray-500 fs-6 mb-5">{{ $description }}</p>
    @endif
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary">
            <i class="bi {{ $actionIcon }} me-1"></i>
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
