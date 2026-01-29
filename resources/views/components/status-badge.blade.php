@props([
    'status' => null,
    'active' => null,
    'size' => 'sm'
])

@php
    // Determine badge based on active/inactive or status
    if ($active !== null) {
        $badgeClass = $active ? 'badge-light-success' : 'badge-light-secondary';
        $text = $active ? __('messages.active') : __('messages.inactive');
    } elseif ($status !== null && method_exists($status, 'badgeClass')) {
        // For Enum status objects
        $badgeClass = $status->badgeClass();
        $text = $status->label();
    } else {
        $badgeClass = 'badge-light-secondary';
        $text = $status ?? '-';
    }
    
    $sizeClass = match($size) {
        'xs' => 'fs-9',
        'sm' => 'fs-8',
        'md' => 'fs-7',
        'lg' => 'fs-6',
        default => 'fs-8'
    };
@endphp

<span {{ $attributes->merge(['class' => "badge $badgeClass $sizeClass"]) }}>
    {{ $text }}
</span>
