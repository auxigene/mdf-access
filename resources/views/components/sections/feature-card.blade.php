@props([
    'icon' => null,
    'title' => '',
    'description' => '',
    'variant' => 'default',
])

@php
$variants = [
    'default' => 'bg-white',
    'gradient-blue' => 'bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200 hover:border-blue-400',
    'gradient-green' => 'bg-gradient-to-br from-green-50 to-green-100 border-green-200 hover:border-green-400',
    'gradient-purple' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200 hover:border-purple-400',
];

$iconColors = [
    'default' => 'bg-blue-100 text-blue-600',
    'gradient-blue' => 'bg-blue-600 text-white',
    'gradient-green' => 'bg-green-600 text-white',
    'gradient-purple' => 'bg-purple-600 text-white',
];

$cardClass = $variants[$variant] . ' rounded-2xl p-6 sm:p-8 border-2 border-gray-200 hover:shadow-xl transition-all group';
@endphp

<div {{ $attributes->merge(['class' => $cardClass]) }}>
    @if($icon || isset($iconSlot))
        <div class="w-12 h-12 sm:w-16 sm:h-16 {{ $iconColors[$variant] }} rounded-xl flex items-center justify-center mb-4 sm:mb-6 group-hover:scale-110 transition-transform">
            @if(isset($iconSlot))
                {{ $iconSlot }}
            @elseif($icon)
                <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            @endif
        </div>
    @endif

    @if($title)
        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">
            {{ $title }}
        </h3>
    @endif

    @if($description)
        <p class="text-gray-700 mb-4">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}
</div>
