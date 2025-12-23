{{--
    Blade Layout System Demo Page
    This page demonstrates all components of the blade layout system
--}}

<x-layouts.app title="Layout System Demo">
    <!-- Hero Section -->
    <x-sections.hero
        title="Blade Layout System"
        subtitle="A flexible, reusable component system built with Laravel Blade and Tailwind CSS"
        variant="default"
        :pattern="true"
    >
        <x-slot:badge>
            <x-ui.badge variant="primary" size="md">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Version 1.0
            </x-ui.badge>
        </x-slot:badge>

        <x-slot:actions>
            <x-ui.button
                variant="primary"
                size="lg"
                href="#components"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>'
                iconPosition="right"
            >
                Explore Components
            </x-ui.button>

            <x-ui.button
                variant="outline"
                size="lg"
                href="/BLADE_LAYOUT_SYSTEM.md"
            >
                View Documentation
            </x-ui.button>
        </x-slot:actions>

        <x-slot:stats>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">4</div>
                    <div class="text-sm text-gray-600">Layouts</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">9</div>
                    <div class="text-sm text-gray-600">Components</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">100%</div>
                    <div class="text-sm text-gray-600">Reusable</div>
                </div>
            </div>
        </x-slot:stats>
    </x-sections.hero>

    <!-- Buttons Demo -->
    <section id="components" class="py-16 bg-white">
        <x-layout.container>
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Button Components</h2>

            <div class="space-y-6">
                <!-- Button Variants -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-ui.button variant="primary">Primary</x-ui.button>
                        <x-ui.button variant="secondary">Secondary</x-ui.button>
                        <x-ui.button variant="outline">Outline</x-ui.button>
                        <x-ui.button variant="ghost">Ghost</x-ui.button>
                        <x-ui.button variant="danger">Danger</x-ui.button>
                        <x-ui.button variant="success">Success</x-ui.button>
                        <x-ui.button variant="dark">Dark</x-ui.button>
                    </div>
                </div>

                <!-- Button Sizes -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.button variant="primary" size="xs">Extra Small</x-ui.button>
                        <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                        <x-ui.button variant="primary" size="md">Medium</x-ui.button>
                        <x-ui.button variant="primary" size="lg">Large</x-ui.button>
                        <x-ui.button variant="primary" size="xl">Extra Large</x-ui.button>
                    </div>
                </div>

                <!-- Buttons with Icons -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">With Icons</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-ui.button
                            variant="primary"
                            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>'
                        >
                            Add New
                        </x-ui.button>

                        <x-ui.button
                            variant="outline"
                            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>'
                            iconPosition="right"
                        >
                            Next Step
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    <!-- Cards Demo -->
    <section class="py-16 bg-gray-50">
        <x-layout.container>
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Card Components</h2>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Basic Card -->
                <x-ui.card>
                    <h3 class="text-xl font-bold mb-2">Basic Card</h3>
                    <p class="text-gray-600">Simple card with default styling and padding.</p>
                </x-ui.card>

                <!-- Card with Header and Footer -->
                <x-ui.card shadow="xl">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold">Featured Card</h3>
                            <x-ui.badge variant="success">New</x-ui.badge>
                        </div>
                    </x-slot:header>

                    <p class="text-gray-600">Card with header and footer sections.</p>

                    <x-slot:footer>
                        <x-ui.button variant="primary" size="sm">Learn More</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>

                <!-- Hover Card -->
                <x-ui.card variant="hover">
                    <h3 class="text-xl font-bold mb-2">Hover Card</h3>
                    <p class="text-gray-600">Interactive card with hover effects.</p>
                </x-ui.card>
            </div>
        </x-layout.container>
    </section>

    <!-- Alerts Demo -->
    <section class="py-16 bg-white">
        <x-layout.container size="sm">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Alert Components</h2>

            <div class="space-y-4">
                <x-ui.alert type="success">
                    <strong class="font-semibold">Success!</strong> Your changes have been saved.
                </x-ui.alert>

                <x-ui.alert type="error" dismissible>
                    <strong class="font-semibold">Error!</strong> There was a problem with your request.
                </x-ui.alert>

                <x-ui.alert type="warning">
                    <strong class="font-semibold">Warning!</strong> Please review your input.
                </x-ui.alert>

                <x-ui.alert type="info" dismissible>
                    <strong class="font-semibold">Info:</strong> This is an informational message.
                </x-ui.alert>
            </div>
        </x-layout.container>
    </section>

    <!-- Input Demo -->
    <section class="py-16 bg-gray-50">
        <x-layout.container size="sm">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Input Components</h2>

            <div class="space-y-6">
                <x-ui.input
                    name="email"
                    type="email"
                    label="Email Address"
                    placeholder="you@example.com"
                    hint="We'll never share your email"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'
                />

                <x-ui.input
                    name="password"
                    type="password"
                    label="Password"
                    required
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
                />

                <x-ui.input
                    name="search"
                    type="text"
                    label="Search"
                    placeholder="Search..."
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
                />

                <x-ui.input
                    name="description"
                    type="textarea"
                    label="Description"
                    placeholder="Enter description..."
                    hint="Maximum 500 characters"
                />
            </div>
        </x-layout.container>
    </section>

    <!-- Badge Demo -->
    <section class="py-16 bg-white">
        <x-layout.container>
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Badge Components</h2>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Variants</h3>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.badge variant="default">Default</x-ui.badge>
                        <x-ui.badge variant="primary">Primary</x-ui.badge>
                        <x-ui.badge variant="success">Success</x-ui.badge>
                        <x-ui.badge variant="warning">Warning</x-ui.badge>
                        <x-ui.badge variant="danger">Danger</x-ui.badge>
                        <x-ui.badge variant="info">Info</x-ui.badge>
                        <x-ui.badge variant="dark">Dark</x-ui.badge>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.badge variant="primary" size="sm">Small</x-ui.badge>
                        <x-ui.badge variant="primary" size="md">Medium</x-ui.badge>
                        <x-ui.badge variant="primary" size="lg">Large</x-ui.badge>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">With Icons</h3>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.badge
                            variant="success"
                            icon='<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>'
                        >
                            Verified
                        </x-ui.badge>

                        <x-ui.badge
                            variant="danger"
                            icon='<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>'
                        >
                            Error
                        </x-ui.badge>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    <!-- Feature Grid Demo -->
    <x-sections.feature-grid
        title="Section Components"
        subtitle="Pre-built sections for common page patterns"
        :columns="3"
        variant="gray"
    >
        <x-sections.feature-card
            title="Hero Sections"
            description="Eye-catching hero sections with multiple variants and flexible content slots"
            variant="gradient-blue"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>'
        >
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Multiple variants
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Flexible slots
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Background patterns
                </li>
            </ul>
        </x-sections.feature-card>

        <x-sections.feature-card
            title="Feature Grids"
            description="Responsive grid layouts for showcasing features, services, or team members"
            variant="gradient-green"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'
        >
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    2, 3, or 4 columns
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Responsive design
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Feature cards included
                </li>
            </ul>
        </x-sections.feature-card>

        <x-sections.feature-card
            title="Custom Layouts"
            description="Four base layouts to choose from: app, auth, dashboard, and blank"
            variant="gradient-purple"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'
        >
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Marketing pages
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Authentication flows
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 text-purple-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Dashboard views
                </li>
            </ul>
        </x-sections.feature-card>
    </x-sections.feature-grid>

    <!-- CTA Section -->
    <section class="py-16 sm:py-24 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white">
        <x-layout.container class="text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6">
                Ready to Build Something Amazing?
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Start using the blade layout system to create beautiful, consistent UIs across your application.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-ui.button
                    href="/BLADE_LAYOUT_SYSTEM.md"
                    variant="dark"
                    size="lg"
                    class="bg-white text-blue-600 hover:bg-gray-100"
                >
                    Read Documentation
                </x-ui.button>
                <x-ui.button
                    href="{{ route('dashboard') }}"
                    variant="outline"
                    size="lg"
                    class="border-white text-white hover:bg-blue-800"
                >
                    Go to Dashboard
                </x-ui.button>
            </div>
        </x-layout.container>
    </section>
</x-layouts.app>

@push('scripts')
<script>
    // Add Alpine.js for interactive components (alerts, dropdowns)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Blade Layout System Demo Page Loaded');
    });
</script>
@endpush
