<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: linear-gradient(135deg, #2C3E50 0%, #34495E 50%, #2C3E50 100%);
        }
        .logo-container {
            animation: fadeInDown 0.8s ease-out;
        }
        .login-card {
            animation: fadeInUp 0.8s ease-out;
            backdrop-filter: blur(10px);
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(0, 173, 239, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #00ADEF 0%, #0097D6 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0097D6 0%, #007DB8 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 173, 239, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="logo-container text-center">
            <img src="{{ asset('images/logo-samsic.jpg') }}" alt="Samsic Maintenance Maroc" class="mx-auto h-32 w-auto rounded-lg shadow-2xl">
        </div>

        <!-- Login Card -->
        <div class="login-card bg-white/95 rounded-2xl shadow-2xl p-8 space-y-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">
                    Bienvenue
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Connectez-vous à votre compte
                </p>
            </div>

            @if (session('status'))
                <div class="bg-cyan-50 border-l-4 border-cyan-500 text-cyan-700 px-4 py-3 rounded" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse email
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               class="input-field appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200 @error('email') border-red-500 ring-2 ring-red-200 @enderror"
                               placeholder="votre@email.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mot de passe
                        </label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="input-field appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200 @error('password') border-red-500 ring-2 ring-red-200 @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-cyan-600 focus:ring-cyan-500 border-gray-300 rounded transition duration-200">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Se souvenir de moi
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-cyan-600 hover:text-cyan-500 transition duration-200">
                            Mot de passe oublié?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                        Se connecter
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-white/80 text-sm">
            <p>&copy; {{ date('Y') }} Samsic Maintenance Maroc. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
