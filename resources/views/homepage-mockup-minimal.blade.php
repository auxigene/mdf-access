<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDF Access - Gestion de Projets PMBOK</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

    <!-- ============================================ -->
    <!-- HEADER MINIMAL -->
    <!-- ============================================ -->
    <header class="absolute top-0 left-0 right-0 z-50">
        <nav class="max-w-7xl mx-auto px-6 py-6">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="/" class="text-2xl font-bold text-gray-900">MDF Access</a>

                <!-- Auth Links -->
                <div class="flex items-center space-x-6">
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 font-medium transition-colors">
                            Connexion
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 font-medium transition-colors">
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <!-- ============================================ -->
    <!-- HERO SECTION MINIMAL & CENTRÉ -->
    <!-- ============================================ -->
    <section class="min-h-screen flex items-center justify-center px-6">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Logo Icon -->
            <div class="mb-8">
                <div class="inline-flex w-24 h-24 bg-gradient-to-br from-gray-900 to-gray-700 rounded-3xl items-center justify-center">
                    <span class="text-white font-bold text-4xl">M</span>
                </div>
            </div>

            <!-- Main Title -->
            <h1 class="text-6xl sm:text-7xl lg:text-8xl font-bold text-gray-900 mb-8 leading-none tracking-tight">
                MDF Access
            </h1>

            <!-- Subtitle -->
            <p class="text-xl sm:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto">
                Gestion de Projets PMBOK Multi-Tenant Professionnelle
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-all">
                    Commencer
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="#features" class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg border-2 border-gray-900 hover:bg-gray-50 transition-all">
                    En savoir plus
                </a>
            </div>

            <!-- Mini Stats -->
            <div class="flex flex-wrap justify-center gap-8 text-sm text-gray-500">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>174 Permissions</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>29 Rôles</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Multi-Tenant</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- MÉTHODOLOGIES - 3 CARDS -->
    <!-- ============================================ -->
    <section id="features" class="py-24 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <!-- Title -->
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4">
                    Méthodologies Supportées
                </h2>
                <p class="text-lg text-gray-600">
                    Choisissez l'approche qui convient à votre projet
                </p>
            </div>

            <!-- Grid -->
            <div class="grid md:grid-cols-3 gap-8">
                <!-- PMBOK -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-gray-900 transition-all group cursor-pointer">
                    <div class="text-5xl mb-4">📋</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">PMBOK Waterfall</h3>
                    <p class="text-gray-600 mb-4">
                        5 phases structurées pour une gestion méthodique et prévisible de vos projets.
                    </p>
                    <div class="text-sm text-gray-500">
                        Initiation → Planning → Execution → Monitoring → Closure
                    </div>
                </div>

                <!-- Scrum -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-gray-900 transition-all group cursor-pointer">
                    <div class="text-5xl mb-4">⚡</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Agile Scrum</h3>
                    <p class="text-gray-600 mb-4">
                        Approche itérative avec des sprints pour une adaptabilité maximale.
                    </p>
                    <div class="text-sm text-gray-500">
                        Sprint 0 → Development Sprints → Release
                    </div>
                </div>

                <!-- Hybrid -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:border-gray-900 transition-all group cursor-pointer">
                    <div class="text-5xl mb-4">🔄</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Hybrid</h3>
                    <p class="text-gray-600 mb-4">
                        Combine la structure PMBOK avec la flexibilité Agile pour le meilleur des deux mondes.
                    </p>
                    <div class="text-sm text-gray-500">
                        Structure + Agilité = Projet complexe
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FONCTIONNALITÉS CLÉS - LISTE SIMPLE -->
    <!-- ============================================ -->
    <section class="py-24 px-6 bg-white">
        <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Colonne 1 -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Multi-Tenant & Multi-Organisations</h3>
                        <p class="text-gray-600">
                            Isolation complète des données avec support de multiples organisations par projet.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Templates de Phases Hiérarchiques</h3>
                        <p class="text-gray-600">
                            12 templates avec métadonnées complètes et hiérarchie illimitée.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Système RBAC Avancé</h3>
                        <p class="text-gray-600">
                            174 permissions et 29 rôles pour un contrôle d'accès granulaire.
                        </p>
                    </div>
                </div>

                <!-- Colonne 2 -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Sécurité Renforcée</h3>
                        <p class="text-gray-600">
                            Authentification 2FA et protection contre les vulnérabilités OWASP Top 10.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Import/Export Excel</h3>
                        <p class="text-gray-600">
                            Importez et exportez vos données avec validation automatique.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">API REST Complète</h3>
                        <p class="text-gray-600">
                            Intégration facile avec vos outils existants via API documentée.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CHIFFRES -->
    <!-- ============================================ -->
    <section class="py-24 px-6 bg-gray-900 text-white">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
                <div>
                    <div class="text-5xl font-bold mb-2">66</div>
                    <div class="text-gray-400">Projets</div>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">9,626</div>
                    <div class="text-gray-400">Tâches</div>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">27</div>
                    <div class="text-gray-400">Organisations</div>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">58</div>
                    <div class="text-gray-400">Utilisateurs</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CTA FINAL MINIMAL -->
    <!-- ============================================ -->
    <section class="py-24 px-6 bg-white">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                Prêt à commencer ?
            </h2>
            <p class="text-xl text-gray-600 mb-10">
                Rejoignez les organisations qui utilisent MDF Access
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-all">
                Commencer maintenant
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER MINIMAL -->
    <!-- ============================================ -->
    <footer class="py-12 px-6 border-t border-gray-200">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <!-- Logo -->
                <div class="text-xl font-bold text-gray-900 mb-4 md:mb-0">
                    MDF Access
                </div>

                <!-- Links -->
                <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-600">
                    <a href="/docs" class="hover:text-gray-900 transition-colors">Documentation</a>
                    <a href="/docs/api" class="hover:text-gray-900 transition-colors">API</a>
                    <a href="/support" class="hover:text-gray-900 transition-colors">Support</a>
                    <a href="/changelog" class="hover:text-gray-900 transition-colors">Changelog</a>
                </div>

                <!-- Copyright -->
                <div class="text-sm text-gray-500 mt-4 md:mt-0">
                    &copy; {{ date('Y') }} MDF Access
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================ -->
    <!-- SCRIPTS -->
    <!-- ============================================ -->
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
