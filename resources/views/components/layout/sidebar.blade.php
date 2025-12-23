@props([
    'items' => [],
    'collapsed' => false,
])

<aside
    class="bg-gray-900 text-white w-64 min-h-screen flex-shrink-0 hidden lg:block"
    x-data="{ collapsed: {{ $collapsed ? 'true' : 'false' }} }"
    :class="{ 'w-20': collapsed, 'w-64': !collapsed }"
>
    <div class="flex flex-col h-full">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-800">
            <span x-show="!collapsed" class="text-xl font-bold">Menu</span>
            <button @click="collapsed = !collapsed" class="p-2 rounded-lg hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Items -->
        <nav class="flex-1 overflow-y-auto py-6">
            <ul class="space-y-2 px-3">
                @forelse($items as $item)
                    @if(isset($item['separator']))
                        <li class="my-4 border-t border-gray-800"></li>
                    @else
                        <li>
                            <a
                                href="{{ $item['url'] ?? '#' }}"
                                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ ($item['active'] ?? false) ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                title="{{ $item['label'] ?? '' }}"
                            >
                                @if(isset($item['icon']))
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $item['icon'] !!}
                                    </svg>
                                @endif
                                <span x-show="!collapsed" class="ml-3">{{ $item['label'] ?? '' }}</span>

                                @if(isset($item['badge']))
                                    <span x-show="!collapsed" class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-500 text-white">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                @empty
                    <!-- Default Menu Items -->
                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span x-show="!collapsed" class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span x-show="!collapsed" class="ml-3">Projets</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span x-show="!collapsed" class="ml-3">Tâches</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span x-show="!collapsed" class="ml-3">Équipe</span>
                        </a>
                    </li>
                @endforelse
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="border-t border-gray-800 p-4">
            <div class="flex items-center" x-show="!collapsed">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm font-semibold">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'U' }}</span>
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-medium truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
