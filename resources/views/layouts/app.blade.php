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
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Navigation -->
    @if(!isset($hideNav) || !$hideNav)
        <x-layout.nav />
    @endif

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    @if(!isset($hideFooter) || !$hideFooter)
        <x-layout.footer />
    @endif

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
