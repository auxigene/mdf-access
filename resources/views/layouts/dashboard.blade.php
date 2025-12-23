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
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @if(!isset($hideSidebar) || !$hideSidebar)
        <x-layout.sidebar :items="$sidebarItems ?? []" />
        @endif

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation -->
            <x-layout.nav variant="dashboard" />

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Page Header -->
                @if(isset($header))
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        {{ $header }}
                    </div>
                </header>
                @endif

                <!-- Main Content -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
