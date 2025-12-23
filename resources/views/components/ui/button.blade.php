@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 shadow-md hover:shadow-lg',
    'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 shadow-md hover:shadow-lg',
    'outline' => 'bg-white text-blue-600 border-2 border-blue-600 hover:bg-blue-50 focus:ring-blue-500',
    'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-md hover:shadow-lg',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-md hover:shadow-lg',
    'dark' => 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-700 shadow-md hover:shadow-lg',
];

$sizes = [
    'xs' => 'px-3 py-1.5 text-xs',
    'sm' => 'px-4 py-2 text-sm',
    'md' => 'px-6 py-2.5 text-base',
    'lg' => 'px-8 py-3 text-lg',
    'xl' => 'px-10 py-4 text-xl',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <svg class="w-5 h-5 {{ $slot->isNotEmpty() ? 'mr-2' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <svg class="w-5 h-5 {{ $slot->isNotEmpty() ? 'ml-2' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <svg class="w-5 h-5 {{ $slot->isNotEmpty() ? 'mr-2' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <svg class="w-5 h-5 {{ $slot->isNotEmpty() ? 'ml-2' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
    </button>
@endif
