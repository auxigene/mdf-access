<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDF Access - Gestion de Projets PMBOK Multi-Tenant</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <!-- ============================================ -->
    <!-- HEADER / NAVIGATION -->
    <!-- ============================================ -->
    <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

                <!-- Navigation Desktop -->
                <div class="hidden md:flex items-center space-x-8">
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
                </div>

                <!-- CTAs -->
                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg">
                            Se connecter
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="inline-flex px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all shadow-md hover:shadow-lg">
                            Dashboard
                        </a>
                    @endguest

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden py-4 border-t border-gray-200">
                <div class="flex flex-col space-y-3">
                    <a href="#fonctionnalites" class="text-gray-600 hover:text-blue-600 font-medium">Fonctionnalités</a>
                    <a href="#methodologies" class="text-gray-600 hover:text-blue-600 font-medium">Méthodologies</a>
                    <a href="#securite" class="text-gray-600 hover:text-blue-600 font-medium">Sécurité</a>
                    <a href="/docs" class="text-gray-600 hover:text-blue-600 font-medium">Documentation</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- ============================================ -->
    <!-- HERO SECTION -->
    <!-- ============================================ -->
    <section class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-white py-4">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgb(30, 64, 175) 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Texte -->
                <div class="text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold mb-6">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Plateforme PMBOK Multi-Tenant
                    </div>

                    <!-- Titre -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Gérez vos projets avec
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-blue-800">
                            excellence
                        </span>
                    </h1>

                    <!-- Sous-titre -->
                    <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed">
                        MDF Access offre une solution multi-tenant complète avec des templates de méthodologies PMBOK, Scrum et Hybrid, un système de permissions avancé et un suivi en temps réel de vos projets.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Se connecter
                        </a>
                        <a href="#fonctionnalites" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg border-2 border-blue-600 hover:bg-blue-50 transition-all shadow-md hover:shadow-lg">
                            Découvrir les fonctionnalités
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Stats mini -->
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-blue-600">174</div>
                            <div class="text-sm text-gray-600">Permissions</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-blue-600">29</div>
                            <div class="text-sm text-gray-600">Rôles</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-blue-600">3</div>
                            <div class="text-sm text-gray-600">Méthodologies</div>
                        </div>
                    </div>
                </div>

                <!-- Visual / Screenshot -->
                <div class="relative">
                    <div class="relative bg-white rounded-2xl shadow-2xl p-4 transform lg:rotate-2 hover:rotate-0 transition-transform duration-300">
                        <!-- Mockup Dashboard -->
                        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg p-8 text-white">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold">Dashboard</h3>
                                <div class="flex space-x-2">
                                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm opacity-80">Projets actifs</span>
                                        <span class="text-2xl font-bold">66</span>
                                    </div>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm opacity-80">Tâches en cours</span>
                                        <span class="text-2xl font-bold">9,626</span>
                                    </div>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm opacity-80">Organisations</span>
                                        <span class="text-2xl font-bold">27</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Floating Elements -->
                    <div class="absolute -top-4 -right-4 bg-green-500 text-white px-4 py-2 rounded-full font-semibold shadow-lg transform rotate-12">
                        ✓ Sécurisé
                    </div>
                    <div class="absolute -bottom-4 -left-4 bg-purple-500 text-white px-4 py-2 rounded-full font-semibold shadow-lg transform -rotate-12">
                        ⚡ Rapide
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- MÉTHODOLOGIES SUPPORTÉES -->
    <!-- ============================================ -->
    <section id="methodologies" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Titre Section -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Méthodologies Supportées
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Choisissez la méthodologie qui correspond le mieux à vos besoins. MDF Access supporte les principales approches de gestion de projets.
                </p>
            </div>

            <!-- Grid Méthodologies -->
            <div class="grid md:grid-cols-3 gap-8">
                <!-- PMBOK Waterfall -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 border-2 border-blue-200 hover:border-blue-400 transition-all hover:shadow-xl group">
                    <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">PMBOK Waterfall</h3>
                    <p class="text-gray-700 mb-4">
                        Méthodologie classique en 5 phases : Initiation, Planning, Execution, Monitoring & Controlling, Closure.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            5 phases structurées
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Templates de livrables
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Critères d'entrée/sortie
                        </li>
                    </ul>
                </div>

                <!-- Agile Scrum -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-8 border-2 border-green-200 hover:border-green-400 transition-all hover:shadow-xl group">
                    <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Agile Scrum</h3>
                    <p class="text-gray-700 mb-4">
                        Approche itérative et incrémentale en sprints : Sprint 0, Development Sprints, Release.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Sprints itératifs
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Livraisons continues
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Adaptabilité maximale
                        </li>
                    </ul>
                </div>

                <!-- Hybrid -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-8 border-2 border-purple-200 hover:border-purple-400 transition-all hover:shadow-xl group">
                    <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Hybrid PMBOK + Agile</h3>
                    <p class="text-gray-700 mb-4">
                        Combine le meilleur des deux mondes : structure PMBOK avec flexibilité Agile.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Structure + Agilité
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Personnalisable
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Idéal projets complexes
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FONCTIONNALITÉS CLÉS -->
    <!-- ============================================ -->
    <section id="fonctionnalites" class="py-16 sm:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Titre Section -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Fonctionnalités Puissantes
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Une plateforme complète pour gérer tous les aspects de vos projets, de l'initiation à la clôture.
                </p>
            </div>

            <!-- Grid Fonctionnalités -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Multi-Tenant</h3>
                    <p class="text-gray-600">
                        Isolation complète des données par organisation avec support de multiples organisations par projet.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Templates de Phases</h3>
                    <p class="text-gray-600">
                        12 templates de phases avec hiérarchie illimitée et métadonnées complètes (activités, livrables).
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sécurité Avancée</h3>
                    <p class="text-gray-600">
                        Authentification 2FA, 174 permissions, 29 rôles prédéfinis et gestion granulaire des accès.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Import/Export Excel</h3>
                    <p class="text-gray-600">
                        Importez et exportez vos projets, phases et tâches via Excel avec validation automatique.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">API REST Complète</h3>
                    <p class="text-gray-600">
                        Intégrez MDF Access avec vos outils existants via notre API REST documentée et sécurisée.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Suivi en Temps Réel</h3>
                    <p class="text-gray-600">
                        Tableaux de bord dynamiques avec métriques en temps réel et rapports personnalisables.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CHIFFRES CLÉS / STATS -->
    <!-- ============================================ -->
    <section class="py-16 sm:py-24 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    MDF Access en Chiffres
                </h2>
                <p class="text-lg text-blue-100">
                    Des chiffres qui témoignent de notre robustesse
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">66</div>
                    <div class="text-blue-100">Projets Gérés</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">9,626</div>
                    <div class="text-blue-100">Tâches Suivies</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">27</div>
                    <div class="text-blue-100">Organisations</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">58</div>
                    <div class="text-blue-100">Utilisateurs</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- SÉCURITÉ & CONFORMITÉ -->
    <!-- ============================================ -->
    <section id="securite" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Texte -->
                <div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">
                        Sécurité & Conformité au Cœur
                    </h2>
                    <p class="text-lg text-gray-600 mb-8">
                        Vos données sont précieuses. C'est pourquoi nous avons implémenté les meilleures pratiques de sécurité pour protéger vos informations et garantir la conformité.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Authentification à Deux Facteurs (2FA)</h4>
                                <p class="text-gray-600">Compatible Google Authenticator et Authy pour une sécurité renforcée.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Isolation Multi-Tenant</h4>
                                <p class="text-gray-600">Chaque organisation est totalement isolée avec ses propres données.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Système RBAC Avancé</h4>
                                <p class="text-gray-600">174 permissions et 29 rôles pour un contrôle d'accès granulaire.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Protection OWASP Top 10</h4>
                                <p class="text-gray-600">Protection contre CSRF, XSS, injection SQL et autres vulnérabilités.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual / Badges -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 border-2 border-gray-200">
                        <div class="space-y-6">
                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-2xl font-bold text-gray-900">174</span>
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-600 font-medium">Permissions Définies</p>
                            </div>

                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-2xl font-bold text-gray-900">29</span>
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-600 font-medium">Rôles Préconfigurés</p>
                            </div>

                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-2xl font-bold text-gray-900">100%</span>
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-600 font-medium">Isolation Multi-Tenant</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CTA FINAL -->
    <!-- ============================================ -->
    <section class="py-16 sm:py-24 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6">
                Prêt à transformer votre gestion de projets ?
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Rejoignez les organisations qui font confiance à MDF Access pour gérer leurs projets avec excellence.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Se connecter
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="/docs" class="inline-flex items-center justify-center px-8 py-4 bg-blue-800 text-white font-semibold rounded-lg border-2 border-blue-400 hover:bg-blue-700 transition-all shadow-lg hover:shadow-xl">
                    Consulter la documentation
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h4 class="text-white font-semibold mb-4">À Propos</h4>
                    <p class="text-sm text-gray-400">
                        MDF Access est la plateforme de gestion de projets PMBOK multi-tenant conçue pour les organisations professionnelles.
                    </p>
                </div>

                <!-- Liens -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Liens Rapides</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#fonctionnalites" class="hover:text-white transition-colors">Fonctionnalités</a></li>
                        <li><a href="#methodologies" class="hover:text-white transition-colors">Méthodologies</a></li>
                        <li><a href="#securite" class="hover:text-white transition-colors">Sécurité</a></li>
                        <li><a href="/docs" class="hover:text-white transition-colors">Documentation</a></li>
                    </ul>
                </div>

                <!-- Ressources -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Ressources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/docs/api" class="hover:text-white transition-colors">API Documentation</a></li>
                        <li><a href="/docs/guides" class="hover:text-white transition-colors">Guides</a></li>
                        <li><a href="/support" class="hover:text-white transition-colors">Support</a></li>
                        <li><a href="/changelog" class="hover:text-white transition-colors">Changelog</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            contact@mdfaccess.com
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Paris, France
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} MDF Access. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- ============================================ -->
    <!-- SCRIPTS -->
    <!-- ============================================ -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    const menu = document.getElementById('mobile-menu');
                    if (!menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                    }
                }
            });
        });

        // Add scroll effect to header
        let lastScroll = 0;
        const header = document.querySelector('header');
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > lastScroll && currentScroll > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            lastScroll = currentScroll;
        });
    </script>
</body>
</html>
