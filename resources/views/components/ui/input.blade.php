@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
$inputClasses = 'block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors';

if ($icon) {
    $inputClasses .= $iconPosition === 'left' ? ' pl-10' : ' pr-10';
}

if ($error) {
    $inputClasses .= ' border-red-300 focus:border-red-500 focus:ring-red-500';
}
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 {{ $iconPosition === 'left' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif

        @if($type === 'textarea')
            <textarea
                name="{{ $name }}"
                id="{{ $name }}"
                rows="4"
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes->except(['class', 'label', 'error', 'hint'])->merge(['class' => $inputClasses]) }}
            >{{ $slot }}</textarea>
        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes->except(['class', 'label', 'error', 'hint'])->merge(['class' => $inputClasses]) }}
            />
        @endif
    </div>

    @if($hint)
        <p class="mt-2 text-sm text-gray-500">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
