@props([
    'variant' => 'default',
    'sticky' => true,
    'transparent' => false,
])

@php
$baseClasses = 'bg-white shadow-sm border-b border-gray-200';

if ($sticky) {
    $baseClasses .= ' sticky top-0 z-50';
}

if ($transparent) {
    $baseClasses = 'absolute top-0 left-0 right-0 z-50 bg-transparent border-none shadow-none';
}

$containerClass = $variant === 'dashboard' ? 'w-full px-4 sm:px-6 lg:px-8' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
@endphp

<header {{ $attributes->merge(['class' => $baseClasses]) }}>
    <nav class="{{ $containerClass }}">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">M</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">MDF Access</span>
                </a>
            </div>

            <!-- Navigation Links (Desktop) -->
            @if($variant !== 'dashboard')
            <div class="hidden md:flex items-center space-x-8">
                @if(isset($navLinks))
                    {{ $navLinks }}
                @else
                    <a href="#fonctionnalites" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                        Fonctionnalités
                    </a>
                    <a href="#methodologies" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                        Méthodologies
                    </a>
                    <a href="#securite" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                        Sécurité
                    </a>
                    <a href="/docs" class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                        Documentation
                    </a>
                @endif
            </div>
            @endif

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                @if(isset($actions))
                    {{ $actions }}
                @else
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg">
                            Se connecter
                        </a>
                    @else
                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 focus:outline-none">
                                <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Paramètres</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                @endif

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        @if($variant !== 'dashboard')
        <div id="mobile-menu" class="hidden md:hidden py-4 border-t border-gray-200">
            <div class="flex flex-col space-y-3">
                @if(isset($navLinks))
                    {{ $navLinks }}
                @else
                    <a href="#fonctionnalites" class="text-gray-600 hover:text-blue-600 font-medium">Fonctionnalités</a>
                    <a href="#methodologies" class="text-gray-600 hover:text-blue-600 font-medium">Méthodologies</a>
                    <a href="#securite" class="text-gray-600 hover:text-blue-600 font-medium">Sécurité</a>
                    <a href="/docs" class="text-gray-600 hover:text-blue-600 font-medium">Documentation</a>
                @endif
            </div>
        </div>
        @endif
    </nav>
</header>

@push('scripts')
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
</script>
@endpush
