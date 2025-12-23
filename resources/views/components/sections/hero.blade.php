@props([
    'variant' => 'default',
    'title' => '',
    'subtitle' => '',
    'pattern' => false,
])

@php
$variants = [
    'default' => 'bg-gradient-to-b from-blue-50 to-white',
    'gradient' => 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white',
    'minimal' => 'bg-white',
    'dark' => 'bg-gray-900 text-white',
];

$containerClass = 'relative overflow-hidden py-16 sm:py-24 lg:py-32 ' . $variants[$variant];
@endphp

<section {{ $attributes->merge(['class' => $containerClass]) }}>
    <!-- Background Pattern -->
    @if($pattern)
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgb(30, 64, 175) 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    @endif

    <div class="relative">
        <x-layout.container>
            <div class="text-center {{ isset($aside) ? 'lg:text-left' : '' }}">
                @if(isset($badge))
                    <div class="mb-6">
                        {{ $badge }}
                    </div>
                @endif

                @if($title)
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        {!! $title !!}
                    </h1>
                @else
                    {{ $heading ?? '' }}
                @endif

                @if($subtitle)
                    <p class="text-lg sm:text-xl {{ $variant === 'gradient' || $variant === 'dark' ? 'text-blue-100' : 'text-gray-600' }} mb-8 max-w-3xl mx-auto">
                        {{ $subtitle }}
                    </p>
                @else
                    {{ $description ?? '' }}
                @endif

                @if(isset($actions))
                    <div class="flex flex-col sm:flex-row gap-4 justify-center {{ isset($aside) ? 'lg:justify-start' : '' }}">
                        {{ $actions }}
                    </div>
                @endif

                @if(isset($stats))
                    <div class="mt-12">
                        {{ $stats }}
                    </div>
                @endif
            </div>

            @if(isset($aside))
                <div class="mt-12 lg:mt-0">
                    {{ $aside }}
                </div>
            @endif
        </x-layout.container>
    </div>
</section>
