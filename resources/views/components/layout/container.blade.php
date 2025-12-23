@props([
    'size' => 'default',
    'padding' => true,
])

@php
$sizes = [
    'sm' => 'max-w-3xl',
    'default' => 'max-w-7xl',
    'lg' => 'max-w-screen-xl',
    'xl' => 'max-w-screen-2xl',
    'full' => 'max-w-full',
];

$paddingClasses = $padding ? 'px-4 sm:px-6 lg:px-8' : '';

$classes = $sizes[$size] . ' mx-auto ' . $paddingClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
