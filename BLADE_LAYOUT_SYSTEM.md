# Blade Layout System Documentation

## Overview

This document describes the global blade layout system for MDF Access. This flexible, component-based system allows for maximum reusability across all application UIs.

## Architecture

The layout system is organized into three main categories:

```
resources/views/
├── layouts/           # Main page layouts
├── components/
│   ├── ui/           # Reusable UI components
│   ├── layout/       # Layout-specific components
│   └── sections/     # Pre-built sections
```

---

## 1. Layouts

Layouts provide the overall page structure and are extended using `<x-layouts.{name}>` syntax.

### 1.1 App Layout (`layouts/app.blade.php`)

Main application layout with navigation and footer.

**Usage:**
```blade
<x-layouts.app title="Page Title">
    <h1>Your content here</h1>
</x-layouts.app>
```

**Props:**
- `title` - Page title (default: app name)
- `hideNav` - Hide navigation (default: false)
- `hideFooter` - Hide footer (default: false)

**Stacks:**
- `@stack('head')` - Additional head content
- `@stack('scripts')` - Additional scripts

### 1.2 Auth Layout (`layouts/auth.blade.php`)

Centered layout for authentication pages.

**Usage:**
```blade
<x-layouts.auth title="Login">
    <h2 class="text-2xl font-bold mb-6">Sign In</h2>
    <!-- Auth form content -->

    <x-slot:footerLinks>
        <a href="/register" class="text-sm text-blue-600">Don't have an account?</a>
    </x-slot:footerLinks>
</x-layouts.auth>
```

**Props:**
- `title` - Page title
- `hideLogo` - Hide logo (default: false)

**Slots:**
- `footerLinks` - Links below the auth card

### 1.3 Dashboard Layout (`layouts/dashboard.blade.php`)

Layout with sidebar for dashboard pages.

**Usage:**
```blade
<x-layouts.dashboard
    title="Dashboard"
    :sidebarItems="$sidebarItems"
>
    <x-slot:header>
        <h1 class="text-2xl font-bold">Dashboard</h1>
    </x-slot:header>

    <!-- Dashboard content -->
</x-layouts.dashboard>
```

**Props:**
- `title` - Page title
- `hideSidebar` - Hide sidebar (default: false)
- `sidebarItems` - Array of sidebar navigation items

**Slots:**
- `header` - Page header section

**Sidebar Items Format:**
```php
$sidebarItems = [
    [
        'label' => 'Dashboard',
        'url' => route('dashboard'),
        'icon' => '<path d="..."/>',
        'active' => true,
        'badge' => '5' // Optional
    ],
    ['separator' => true], // Separator line
    [
        'label' => 'Projects',
        'url' => route('projects.index'),
        'icon' => '<path d="..."/>',
    ],
];
```

### 1.4 Blank Layout (`layouts/blank.blade.php`)

Minimal layout with no navigation or footer.

**Usage:**
```blade
<x-layouts.blank title="Landing Page" bodyClass="bg-gradient-to-br from-blue-500 to-purple-600">
    <!-- Full control over page content -->
</x-layouts.blank>
```

**Props:**
- `title` - Page title
- `bodyClass` - Custom body classes

---

## 2. UI Components

Reusable UI components for common elements.

### 2.1 Button (`components/ui/button.blade.php`)

**Usage:**
```blade
<!-- Primary Button -->
<x-ui.button variant="primary">
    Click Me
</x-ui.button>

<!-- Button with Icon -->
<x-ui.button
    variant="primary"
    size="lg"
    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>'
    iconPosition="right"
>
    Next Step
</x-ui.button>

<!-- Link as Button -->
<x-ui.button href="/dashboard" variant="outline">
    Go to Dashboard
</x-ui.button>
```

**Props:**
- `variant` - primary | secondary | outline | ghost | danger | success | dark (default: primary)
- `size` - xs | sm | md | lg | xl (default: md)
- `type` - button | submit | reset (default: button)
- `href` - If set, renders as `<a>` tag
- `icon` - SVG path content
- `iconPosition` - left | right (default: left)

### 2.2 Card (`components/ui/card.blade.php`)

**Usage:**
```blade
<!-- Basic Card -->
<x-ui.card>
    <h3 class="text-xl font-bold mb-2">Card Title</h3>
    <p>Card content goes here</p>
</x-ui.card>

<!-- Card with Header and Footer -->
<x-ui.card padding="lg" shadow="xl">
    <x-slot:header>
        <h3 class="text-xl font-bold">Card Header</h3>
    </x-slot:header>

    <p>Card body content</p>

    <x-slot:footer>
        <x-ui.button variant="primary" size="sm">Action</x-ui.button>
    </x-slot:footer>
</x-ui.card>

<!-- Hover Card -->
<x-ui.card variant="hover">
    Interactive card content
</x-ui.card>
```

**Props:**
- `variant` - default | hover | gradient | bordered (default: default)
- `padding` - none | sm | default | lg | xl (default: default)
- `shadow` - none | sm | md | lg | xl | 2xl (default: md)

**Slots:**
- `header` - Card header section
- `footer` - Card footer section

### 2.3 Input (`components/ui/input.blade.php`)

**Usage:**
```blade
<!-- Basic Input -->
<x-ui.input
    name="email"
    type="email"
    label="Email Address"
    placeholder="you@example.com"
    required
/>

<!-- Input with Icon -->
<x-ui.input
    name="search"
    label="Search"
    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
    iconPosition="left"
/>

<!-- Input with Error -->
<x-ui.input
    name="username"
    label="Username"
    error="This username is already taken"
/>

<!-- Textarea -->
<x-ui.input
    name="description"
    type="textarea"
    label="Description"
    hint="Maximum 500 characters"
/>
```

**Props:**
- `type` - text | email | password | number | textarea | etc. (default: text)
- `name` - Input name attribute (required)
- `label` - Input label
- `error` - Error message
- `hint` - Hint text below input
- `required` - Mark as required (default: false)
- `disabled` - Disable input (default: false)
- `icon` - SVG path content
- `iconPosition` - left | right (default: left)

### 2.4 Alert (`components/ui/alert.blade.php`)

**Usage:**
```blade
<!-- Success Alert -->
<x-ui.alert type="success">
    Your changes have been saved successfully!
</x-ui.alert>

<!-- Error Alert with Dismissible -->
<x-ui.alert type="error" dismissible>
    There was an error processing your request.
</x-ui.alert>

<!-- Info Alert without Icon -->
<x-ui.alert type="info" :icon="false">
    This is an informational message.
</x-ui.alert>
```

**Props:**
- `type` - success | error | warning | info (default: info)
- `dismissible` - Show dismiss button (default: false)
- `icon` - Show icon (default: true)

**Note:** Requires Alpine.js for dismissible functionality.

### 2.5 Badge (`components/ui/badge.blade.php`)

**Usage:**
```blade
<!-- Basic Badge -->
<x-ui.badge variant="primary">
    New
</x-ui.badge>

<!-- Badge with Icon -->
<x-ui.badge
    variant="success"
    size="lg"
    icon='<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>'
>
    Verified
</x-ui.badge>
```

**Props:**
- `variant` - default | primary | success | warning | danger | info | dark (default: default)
- `size` - sm | md | lg (default: md)
- `icon` - SVG path content

---

## 3. Layout Components

Components for page structure elements.

### 3.1 Navigation (`components/layout/nav.blade.php`)

**Usage:**
```blade
<!-- Default Navigation -->
<x-layout.nav />

<!-- Custom Navigation Links -->
<x-layout.nav>
    <x-slot:navLinks>
        <a href="/features" class="text-gray-600 hover:text-blue-600">Features</a>
        <a href="/pricing" class="text-gray-600 hover:text-blue-600">Pricing</a>
    </x-slot:navLinks>
</x-layout.nav>

<!-- Custom Actions -->
<x-layout.nav>
    <x-slot:actions>
        <x-ui.button href="/demo" variant="primary">
            Get Demo
        </x-ui.button>
    </x-slot:actions>
</x-layout.nav>

<!-- Transparent Navigation -->
<x-layout.nav :transparent="true" />
```

**Props:**
- `variant` - default | dashboard (default: default)
- `sticky` - Sticky header (default: true)
- `transparent` - Transparent background (default: false)

**Slots:**
- `navLinks` - Custom navigation links
- `actions` - Custom action buttons

### 3.2 Footer (`components/layout/footer.blade.php`)

**Usage:**
```blade
<!-- Full Footer -->
<x-layout.footer />

<!-- Minimal Footer -->
<x-layout.footer variant="minimal" />
```

**Props:**
- `variant` - default | minimal (default: default)

### 3.3 Sidebar (`components/layout/sidebar.blade.php`)

**Usage:**
```blade
<x-layout.sidebar :items="$sidebarItems" />
```

**Props:**
- `items` - Array of navigation items (see Dashboard Layout section)
- `collapsed` - Start collapsed (default: false)

**Note:** Requires Alpine.js for collapse functionality.

### 3.4 Container (`components/layout/container.blade.php`)

**Usage:**
```blade
<!-- Standard Container -->
<x-layout.container>
    <h1>Centered Content</h1>
</x-layout.container>

<!-- Different Sizes -->
<x-layout.container size="sm">
    Small container
</x-layout.container>

<x-layout.container size="xl">
    Extra large container
</x-layout.container>

<!-- No Padding -->
<x-layout.container :padding="false">
    Full width content
</x-layout.container>
```

**Props:**
- `size` - sm | default | lg | xl | full (default: default)
- `padding` - Add horizontal padding (default: true)

---

## 4. Section Components

Pre-built sections for common page patterns.

### 4.1 Hero Section (`components/sections/hero.blade.php`)

**Usage:**
```blade
<!-- Simple Hero -->
<x-sections.hero
    variant="default"
    title="Welcome to MDF Access"
    subtitle="Professional project management platform"
    :pattern="true"
>
    <x-slot:actions>
        <x-ui.button variant="primary" size="lg" href="/register">
            Get Started
        </x-ui.button>
        <x-ui.button variant="outline" size="lg" href="/demo">
            View Demo
        </x-ui.button>
    </x-slot:actions>

    <x-slot:stats>
        <div class="grid grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">174</div>
                <div class="text-sm text-gray-600">Permissions</div>
            </div>
            <!-- More stats... -->
        </div>
    </x-slot:stats>
</x-sections.hero>

<!-- Hero with Badge -->
<x-sections.hero variant="gradient">
    <x-slot:badge>
        <x-ui.badge variant="primary" size="md">
            New Release
        </x-ui.badge>
    </x-slot:badge>

    <x-slot:heading>
        <h1 class="text-5xl font-bold">Custom Heading</h1>
    </x-slot:heading>

    <x-slot:description>
        <p class="text-xl">Custom description content</p>
    </x-slot:description>
</x-sections.hero>
```

**Props:**
- `variant` - default | gradient | minimal | dark (default: default)
- `title` - Hero title (can also use heading slot)
- `subtitle` - Hero subtitle (can also use description slot)
- `pattern` - Show background pattern (default: false)

**Slots:**
- `badge` - Badge above title
- `heading` - Custom heading (alternative to title prop)
- `description` - Custom description (alternative to subtitle prop)
- `actions` - Call-to-action buttons
- `stats` - Statistics display
- `aside` - Side content (creates 2-column layout)

### 4.2 Feature Grid (`components/sections/feature-grid.blade.php`)

**Usage:**
```blade
<x-sections.feature-grid
    title="Features"
    subtitle="Everything you need to manage projects"
    :columns="3"
    variant="gray"
>
    <x-sections.feature-card
        title="Multi-Tenant"
        description="Complete data isolation per organization"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'
    />

    <x-sections.feature-card
        title="Security"
        description="Advanced 2FA and RBAC permissions"
        variant="gradient-blue"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
    />

    <!-- More feature cards... -->
</x-sections.feature-grid>
```

**Feature Grid Props:**
- `columns` - 2 | 3 | 4 (default: 3)
- `variant` - default | gray | dark (default: default)
- `title` - Section title
- `subtitle` - Section subtitle

**Slots:**
- `header` - Custom header (alternative to title/subtitle props)

### 4.3 Feature Card (`components/sections/feature-card.blade.php`)

**Usage:**
```blade
<!-- Basic Feature Card -->
<x-sections.feature-card
    title="Feature Title"
    description="Feature description text"
    icon='<path d="..."/>'
/>

<!-- Gradient Feature Card -->
<x-sections.feature-card
    title="Premium Feature"
    description="Advanced functionality"
    variant="gradient-green"
    icon='<path d="..."/>'
>
    <!-- Additional content -->
    <ul class="space-y-2 text-sm">
        <li>Feature detail 1</li>
        <li>Feature detail 2</li>
    </ul>
</x-sections.feature-card>

<!-- Custom Icon Slot -->
<x-sections.feature-card title="Custom Icon">
    <x-slot:iconSlot>
        <span class="text-4xl">🚀</span>
    </x-slot:iconSlot>

    Feature content here
</x-sections.feature-card>
```

**Props:**
- `icon` - SVG path content
- `title` - Card title
- `description` - Card description
- `variant` - default | gradient-blue | gradient-green | gradient-purple (default: default)

**Slots:**
- `iconSlot` - Custom icon content (alternative to icon prop)

---

## 5. Complete Examples

### 5.1 Landing Page Example

```blade
<x-layouts.app title="Welcome">
    <!-- Hero Section -->
    <x-sections.hero
        title="Manage Your Projects with Excellence"
        subtitle="MDF Access offers a complete multi-tenant solution with PMBOK, Scrum, and Hybrid methodology templates"
        variant="default"
        :pattern="true"
    >
        <x-slot:actions>
            <x-ui.button variant="primary" size="lg" href="{{ route('login') }}">
                Get Started
            </x-ui.button>
            <x-ui.button variant="outline" size="lg" href="#features">
                Learn More
            </x-ui.button>
        </x-slot:actions>
    </x-sections.hero>

    <!-- Features Section -->
    <x-sections.feature-grid
        title="Powerful Features"
        subtitle="Everything you need to manage your projects"
        :columns="3"
        variant="gray"
    >
        <x-sections.feature-card
            title="Multi-Tenant"
            description="Complete data isolation per organization"
            variant="gradient-blue"
        />

        <x-sections.feature-card
            title="Advanced Security"
            description="2FA authentication and RBAC permissions"
            variant="gradient-green"
        />

        <x-sections.feature-card
            title="Real-time Tracking"
            description="Dynamic dashboards with live metrics"
            variant="gradient-purple"
        />
    </x-sections.feature-grid>
</x-layouts.app>
```

### 5.2 Auth Page Example

```blade
<x-layouts.auth title="Sign In">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Welcome Back</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            label="Email Address"
            placeholder="you@example.com"
            required
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'
        />

        <x-ui.input
            name="password"
            type="password"
            label="Password"
            required
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
        />

        <x-ui.button type="submit" variant="primary" class="w-full">
            Sign In
        </x-ui.button>
    </form>

    <x-slot:footerLinks>
        <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:text-blue-800">
            Don't have an account? Sign up
        </a>
    </x-slot:footerLinks>
</x-layouts.auth>
```

### 5.3 Dashboard Page Example

```blade
@php
$sidebarItems = [
    [
        'label' => 'Dashboard',
        'url' => route('dashboard'),
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'active' => true,
    ],
    [
        'label' => 'Projects',
        'url' => route('projects.index'),
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
        'badge' => '12',
    ],
];
@endphp

<x-layouts.dashboard title="Dashboard" :sidebarItems="$sidebarItems">
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <x-ui.button variant="primary" href="{{ route('projects.create') }}">
                New Project
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Status Alerts -->
    @if(session('success'))
        <x-ui.alert type="success" dismissible class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <!-- Stats Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Active Projects</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeProjects }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </x-ui.card>

        <!-- More stat cards... -->
    </div>

    <!-- Recent Projects -->
    <x-ui.card>
        <x-slot:header>
            <h2 class="text-xl font-bold">Recent Projects</h2>
        </x-slot:header>

        <!-- Project list... -->
    </x-ui.card>
</x-layouts.dashboard>
```

---

## 6. Dependencies

### Required

- **Laravel 12** - Framework
- **Tailwind CSS 4.0** - Styling
- **Vite 7.0** - Build tool

### Optional

- **Alpine.js** - Required for interactive components (alerts, dropdowns, sidebar collapse)

To add Alpine.js:

```bash
npm install alpinejs
```

```javascript
// resources/js/app.js
import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start()
```

---

## 7. Best Practices

### 7.1 Layout Selection

- Use `app` layout for public marketing pages
- Use `auth` layout for login, register, password reset
- Use `dashboard` layout for authenticated user pages
- Use `blank` layout for special full-screen experiences

### 7.2 Component Composition

Build complex UIs by composing simple components:

```blade
<x-ui.card variant="hover">
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h3 class="font-bold">Project Name</h3>
            <x-ui.badge variant="success">Active</x-ui.badge>
        </div>
    </x-slot:header>

    <p class="text-gray-600 mb-4">Project description...</p>

    <x-slot:footer>
        <div class="flex gap-2">
            <x-ui.button variant="primary" size="sm">View</x-ui.button>
            <x-ui.button variant="ghost" size="sm">Edit</x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
```

### 7.3 Consistency

- Use consistent spacing (Tailwind spacing scale)
- Use consistent colors (blue for primary, gray for neutral)
- Use consistent sizing (button sizes, text sizes)
- Reuse components instead of duplicating markup

### 7.4 Accessibility

- Always provide `label` for inputs
- Use semantic HTML
- Ensure proper color contrast
- Add ARIA labels where needed

### 7.5 Performance

- Use `@stack` for scripts that are only needed on specific pages
- Lazy load images where appropriate
- Keep component props simple and avoid complex logic

---

## 8. Extending the System

### Adding New Components

1. Create component file in appropriate directory:
   - UI components: `resources/views/components/ui/`
   - Layout components: `resources/views/components/layout/`
   - Section components: `resources/views/components/sections/`

2. Follow existing naming conventions and prop patterns

3. Document the component with usage examples

### Customizing Existing Components

Components are designed to be extended via:
- Props for common variations
- Slots for custom content
- CSS classes can be merged using `$attributes->merge(['class' => ...])`

---

## 9. Troubleshooting

### Component Not Found

Ensure the component file exists and follows Laravel's component naming:
- File: `components/ui/button.blade.php`
- Usage: `<x-ui.button>`

### Styles Not Applied

1. Check Tailwind content sources in `resources/css/app.css`
2. Run `npm run dev` to rebuild assets
3. Clear Laravel view cache: `php artisan view:clear`

### Alpine.js Not Working

1. Ensure Alpine.js is installed: `npm install alpinejs`
2. Import in `resources/js/app.js`
3. Check browser console for errors

---

## 10. Migration Guide

### Converting Existing Pages

**Before:**
```blade
<!DOCTYPE html>
<html>
<head>
    <title>My Page</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <!-- Full HTML structure -->
</body>
</html>
```

**After:**
```blade
<x-layouts.app title="My Page">
    <!-- Only page content -->
</x-layouts.app>
```

### Converting Custom Components

Extract repeated patterns into reusable components:

**Before:**
```blade
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
    Click Me
</button>
```

**After:**
```blade
<x-ui.button variant="primary">
    Click Me
</x-ui.button>
```

---

## Support

For questions or issues with the blade layout system:
1. Check this documentation
2. Review example implementations in `resources/views/homepage-mockup.blade.php`
3. Contact the development team

---

**Last Updated:** December 2025
**Version:** 1.0.0
