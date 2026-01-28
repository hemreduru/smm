@props([
    'type' => 'light-primary',
    'size' => 'sm',
    'text' => '',
    'pill' => false
])

<span {{ $attributes->merge(['class' => 'badge badge-' . $type . ($pill ? ' rounded-pill' : '') . ($size === 'sm' ? ' fs-8' : ($size === 'lg' ? ' fs-6' : ' fs-7'))]) }}>
    {{ $text ?: $slot }}
</span>
