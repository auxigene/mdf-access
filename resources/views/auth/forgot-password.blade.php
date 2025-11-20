<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mot de passe oublié - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: linear-gradient(135deg, #2C3E50 0%, #34495E 50%, #2C3E50 100%);
            min-height: 100vh;
            min-height: -webkit-fill-available;
        }
        html {
            height: -webkit-fill-available;
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
        .btn-primary:active {
            transform: translateY(0);
        }
        /* Mobile optimization */
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }
            .login-container {
                max-height: 100vh;
                overflow-y: auto;
            }
        }
        /* Prevent zoom on input focus for iOS */
        @media screen and (max-width: 768px) {
            input[type="email"] {
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-0 sm:py-1 sm:px-4 md:py-2 md:px-6 lg:px-8">
    <div class="login-container w-full max-w-[90%] sm:max-w-md space-y-3 sm:space-y-2 md:space-y-6">
        <!-- Logo -->
        <div class="logo-container text-center">
            <img src="{{ asset('images/logo-samsic.jpg') }}"
                 alt="Samsic Maintenance Maroc"
                 class="mx-auto h-16 sm:h-24 md:h-16 lg:h-32 w-auto rounded-md sm:rounded-lg shadow-xl sm:shadow-2xl">
        </div>

        <!-- Card -->
        <div class="login-card bg-white/95 rounded-xl sm:rounded-2xl shadow-xl sm:shadow-2xl p-4 sm:p-6 md:px-8 md:py-3 space-y-3 sm:space-y-5 md:space-y-6">
            <div class="text-center">
                <h2 class="text-xl sm:text-3xl md:text-3xl font-bold text-gray-900">
                    Mot de passe oublié
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-gray-600">
                    Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe
                </p>
            </div>

            @if (session('status'))
                <div class="bg-cyan-50 border-l-4 border-cyan-500 text-cyan-700 px-3 sm:px-4 py-2 sm:py-3 rounded text-sm" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <form class="space-y-3 sm:space-y-5" action="{{ route('password.email') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        Adresse email
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           value="{{ old('email') }}"
                           class="input-field appearance-none block w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 text-base focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition duration-200 @error('email') border-red-500 ring-2 ring-red-200 @enderror"
                           placeholder="votre@email.com">
                    @error('email')
                        <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-red-600 flex items-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="btn-primary w-full flex justify-center py-2.5 sm:py-3 px-4 border border-transparent text-sm sm:text-base font-semibold rounded-lg text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                        Envoyer le lien de réinitialisation
                    </button>
                </div>

                <div class="text-center text-xs sm:text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-cyan-600 hover:text-cyan-500 transition duration-200">
                        Retour à la connexion
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-white/80 text-[10px] sm:text-sm px-2">
            <p>&copy; {{ date('Y') }} Samsic Maintenance Maroc. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
