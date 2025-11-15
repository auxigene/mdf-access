<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Setup Two-Factor Authentication
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Scan the QR code below with your authenticator app
                </p>
            </div>

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-48 h-48">
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-2">Or enter this code manually:</p>
                    <code class="block bg-gray-100 p-3 rounded text-center font-mono text-sm break-all">{{ $secret }}</code>
                </div>

                <form method="POST" action="{{ route('2fa.enable') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Verification Code</label>
                        <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-center text-2xl tracking-widest @error('code') border-red-500 @enderror"
                               placeholder="000000">
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Enter the 6-digit code from your authenticator app</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Your Password</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Confirm with your password to enable 2FA</p>
                    </div>

                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Enable Two-Factor Authentication
                        </button>
                    </div>
                </form>
            </div>

            <div class="text-center text-sm">
                <a href="{{ route('dashboard') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</body>
</html>
