@props([
    'platform',
    'showLabel' => true,
    'size' => 'sm'
])

@php
    $sizeClass = match($size) {
        'xs' => 'fs-9',
        'sm' => 'fs-8',
        'md' => 'fs-7',
        'lg' => 'fs-6',
        default => 'fs-8'
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$platform->badgeClass()} $sizeClass"]) }}>
    <i class="bi {{ $platform->icon() }} me-1"></i>
    @if($showLabel)
        {{ $platform->label() }}
    @endif
</span>
