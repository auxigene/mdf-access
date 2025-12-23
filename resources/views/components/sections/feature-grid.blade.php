@props([
    'columns' => 3,
    'variant' => 'default',
    'title' => '',
    'subtitle' => '',
])

@php
$columnClasses = [
    2 => 'md:grid-cols-2',
    3 => 'md:grid-cols-2 lg:grid-cols-3',
    4 => 'md:grid-cols-2 lg:grid-cols-4',
];

$variants = [
    'default' => 'bg-white',
    'gray' => 'bg-gray-50',
    'dark' => 'bg-gray-900 text-white',
];

$containerClass = 'py-16 sm:py-24 ' . $variants[$variant];
@endphp

<section {{ $attributes->merge(['class' => $containerClass]) }}>
    <x-layout.container>
        @if($title || $subtitle || isset($header))
            <div class="text-center mb-12">
                @if(isset($header))
                    {{ $header }}
                @else
                    @if($title)
                        <h2 class="text-3xl sm:text-4xl font-bold {{ $variant === 'dark' ? 'text-white' : 'text-gray-900' }} mb-4">
                            {{ $title }}
                        </h2>
                    @endif

                    @if($subtitle)
                        <p class="text-lg {{ $variant === 'dark' ? 'text-gray-300' : 'text-gray-600' }} max-w-2xl mx-auto">
                            {{ $subtitle }}
                        </p>
                    @endif
                @endif
            </div>
        @endif

        <div class="grid {{ $columnClasses[$columns] }} gap-8">
            {{ $slot }}
        </div>
    </x-layout.container>
</section>
