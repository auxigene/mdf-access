@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => true,
])

@php
$types = [
    'success' => [
        'container' => 'bg-green-50 border-green-200 text-green-800',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'iconColor' => 'text-green-500',
    ],
    'error' => [
        'container' => 'bg-red-50 border-red-200 text-red-800',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'iconColor' => 'text-red-500',
    ],
    'warning' => [
        'container' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        'iconColor' => 'text-yellow-500',
    ],
    'info' => [
        'container' => 'bg-blue-50 border-blue-200 text-blue-800',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'iconColor' => 'text-blue-500',
    ],
];

$config = $types[$type];
$containerClass = 'rounded-lg border p-4 ' . $config['container'];
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }} role="alert" x-data="{ show: true }" x-show="show" x-transition>
    <div class="flex {{ $dismissible ? 'justify-between' : '' }}">
        <div class="flex items-start">
            @if($icon)
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 {{ $config['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $config['icon'] !!}
                    </svg>
                </div>
            @endif

            <div class="{{ $icon ? 'ml-3' : '' }}">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button type="button" @click="show = false" class="ml-3 inline-flex flex-shrink-0 rounded-md p-1.5 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                <span class="sr-only">Dismiss</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>
