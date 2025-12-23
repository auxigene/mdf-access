<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MDF Access') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Head Content -->
    @stack('head')
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        @if(!isset($hideLogo) || !$hideLogo)
        <div class="mb-8">
            <a href="/">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-2xl">M</span>
                </div>
            </a>
        </div>
        @endif

        <!-- Auth Card -->
        <div class="w-full max-w-md">
            <x-ui.card class="shadow-2xl border-0">
                {{ $slot }}
            </x-ui.card>
        </div>

        <!-- Footer Links -->
        @if(isset($footerLinks))
        <div class="mt-6 text-center">
            {{ $footerLinks }}
        </div>
        @endif
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
