@props([
    'variant' => 'default',
    'padding' => 'default',
    'shadow' => 'md',
])

@php
$baseClasses = 'bg-white rounded-xl border border-gray-200';

$variants = [
    'default' => '',
    'hover' => 'hover:shadow-xl hover:border-gray-300 transition-all cursor-pointer',
    'gradient' => 'bg-gradient-to-br from-blue-50 to-white',
    'bordered' => 'border-2',
];

$paddings = [
    'none' => '',
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8',
    'xl' => 'p-10',
];

$shadows = [
    'none' => 'shadow-none',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl',
    '2xl' => 'shadow-2xl',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $paddings[$padding] . ' ' . $shadows[$shadow];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="border-b border-gray-200 {{ $padding !== 'none' ? 'pb-4 mb-4' : '' }}">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if(isset($footer))
        <div class="border-t border-gray-200 {{ $padding !== 'none' ? 'pt-4 mt-4' : '' }}">
            {{ $footer }}
        </div>
    @endif
</div>
