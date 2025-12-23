@props([
    'variant' => 'default',
])

@php
$containerClass = $variant === 'minimal'
    ? 'max-w-6xl mx-auto'
    : 'max-w-7xl mx-auto';
@endphp

@if($variant === 'minimal')
    <!-- Minimal Footer -->
    <footer class="py-12 px-6 border-t border-gray-200 bg-white">
        <div class="{{ $containerClass }}">
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
@else
    <!-- Full Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="{{ $containerClass }} px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h4 class="text-white font-semibold mb-4">À Propos</h4>
                    <p class="text-sm text-gray-400">
                        MDF Access est la plateforme de gestion de projets PMBOK multi-tenant conçue pour les organisations professionnelles.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Liens Rapides</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#fonctionnalites" class="hover:text-white transition-colors">Fonctionnalités</a></li>
                        <li><a href="#methodologies" class="hover:text-white transition-colors">Méthodologies</a></li>
                        <li><a href="#securite" class="hover:text-white transition-colors">Sécurité</a></li>
                        <li><a href="/docs" class="hover:text-white transition-colors">Documentation</a></li>
                    </ul>
                </div>

                <!-- Resources -->
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
@endif
