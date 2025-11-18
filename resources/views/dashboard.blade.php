<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-500">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold mb-4">Welcome to your Dashboard!</h2>
                        <p class="text-gray-600 mb-6">You are logged in as {{ Auth::user()->email }}</p>

                        <!-- User Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Account Information</h3>
                            <ul class="list-disc list-inside text-gray-700">
                                <li>Name: {{ Auth::user()->name }}</li>
                                <li>Email: {{ Auth::user()->email }}</li>
                                <li>Email Verified: {{ Auth::user()->email_verified_at ? 'Yes' : 'No' }}</li>
                                @if(Auth::user()->organization)
                                    <li>Organization: {{ Auth::user()->organization->name }}</li>
                                @endif
                                <li>System Admin: {{ Auth::user()->is_system_admin ? 'Yes' : 'No' }}</li>
                                <li>Two-Factor Authentication: {{ Auth::user()->two_factor_enabled ? 'Enabled' : 'Disabled' }}</li>
                            </ul>
                        </div>

                        <!-- 2FA Settings -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">Security Settings</h3>

                            @if(!Auth::user()->two_factor_enabled)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-yellow-800 mb-2">
                                        Two-factor authentication is not enabled. Enable it now to secure your account.
                                    </p>
                                    <a href="{{ route('2fa.setup') }}"
                                       class="inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Enable 2FA
                                    </a>
                                </div>
                            @else
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-green-800 mb-2">
                                        Two-factor authentication is enabled on your account.
                                    </p>
                                    <form method="POST" action="{{ route('2fa.disable') }}" class="inline">
                                        @csrf
                                        <input type="password" name="password" placeholder="Enter your password" required
                                               class="mr-2 px-3 py-2 border border-gray-300 rounded-md text-sm">
                                        <button type="submit"
                                                class="inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                                onclick="return confirm('Are you sure you want to disable 2FA?')">
                                            Disable 2FA
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
