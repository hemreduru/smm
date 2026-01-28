@props([
    'name' => '',
    'size' => '40px',
    'variant' => 'light-primary',
    'image' => null,
    'fontSize' => null
])

@php
    $initials = collect(explode(' ', trim($name)))
        ->take(2)
        ->map(fn($word) => strtoupper(mb_substr($word, 0, 1)))
        ->join('');
    
    $fontSizeMap = [
        '25px' => 'fs-8',
        '30px' => 'fs-7',
        '35px' => 'fs-6',
        '40px' => 'fs-6',
        '45px' => 'fs-5',
        '50px' => 'fs-4',
        '60px' => 'fs-3',
        '75px' => 'fs-2',
    ];
    
    $fontClass = $fontSize ?? ($fontSizeMap[$size] ?? 'fs-6');
@endphp

<div class="symbol symbol-{{ str_replace('px', '', $size) }}px {{ $attributes->get('class', '') }}">
    @if($image)
        <img src="{{ $image }}" alt="{{ $name }}" />
    @else
        <span class="symbol-label bg-{{ $variant }} text-{{ str_replace('light-', '', $variant) }} {{ $fontClass }} fw-bold">
            {{ $initials ?: '?' }}
        </span>
    @endif
</div>
