@props([
    'variant' => 'default',
    'size' => 'md',
    'icon' => null,
])

@php
$baseClasses = 'inline-flex items-center font-semibold rounded-full';

$variants = [
    'default' => 'bg-gray-100 text-gray-800',
    'primary' => 'bg-blue-100 text-blue-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
    'dark' => 'bg-gray-800 text-white',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-1.5 text-base',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <svg class="w-4 h-4 {{ $slot->isNotEmpty() ? 'mr-1.5' : '' }}" fill="currentColor" viewBox="0 0 20 20">
            {!! $icon !!}
        </svg>
    @endif

    {{ $slot }}
</span>
