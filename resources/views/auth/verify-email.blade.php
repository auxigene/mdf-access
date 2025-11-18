<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verify your email address
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    We've sent you a verification link to your email address. Please check your inbox and click the link to verify your account.
                </p>
            </div>

            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">Didn't receive the email?</p>

                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Resend Verification Email
                    </button>
                </form>
            </div>

            <div class="text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-500">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
