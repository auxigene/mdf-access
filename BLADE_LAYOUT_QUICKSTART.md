# Blade Layout System - Quick Start Guide

## 5-Minute Getting Started

### 1. Choose a Layout

Pick the layout that fits your page type:

```blade
<!-- Marketing/Public Pages -->
<x-layouts.app title="Home">
    <!-- Your content -->
</x-layouts.app>

<!-- Login/Register Pages -->
<x-layouts.auth title="Login">
    <!-- Your content -->
</x-layouts.auth>

<!-- Dashboard/Admin Pages -->
<x-layouts.dashboard title="Dashboard">
    <!-- Your content -->
</x-layouts.dashboard>

<!-- Full Custom Pages -->
<x-layouts.blank title="Special Page">
    <!-- Your content -->
</x-layouts.blank>
```

### 2. Build Your Page with Components

#### Add a Hero Section

```blade
<x-sections.hero
    title="Your Amazing Title"
    subtitle="A compelling subtitle"
    variant="default"
>
    <x-slot:actions>
        <x-ui.button variant="primary" href="/signup">
            Get Started
        </x-ui.button>
    </x-slot:actions>
</x-sections.hero>
```

#### Add Feature Cards

```blade
<x-sections.feature-grid
    title="Features"
    subtitle="What we offer"
    :columns="3"
>
    <x-sections.feature-card
        title="Fast"
        description="Lightning fast performance"
        variant="gradient-blue"
    />

    <x-sections.feature-card
        title="Secure"
        description="Bank-level security"
        variant="gradient-green"
    />

    <x-sections.feature-card
        title="Scalable"
        description="Grows with you"
        variant="gradient-purple"
    />
</x-sections.feature-grid>
```

#### Add Buttons

```blade
<x-ui.button variant="primary">
    Click Me
</x-ui.button>

<x-ui.button variant="outline" href="/learn-more">
    Learn More
</x-ui.button>
```

#### Add Cards

```blade
<x-ui.card>
    <x-slot:header>
        <h3 class="font-bold">Card Title</h3>
    </x-slot:header>

    Card content goes here

    <x-slot:footer>
        <x-ui.button size="sm">Action</x-ui.button>
    </x-slot:footer>
</x-ui.card>
```

#### Add Forms

```blade
<form method="POST" action="/submit">
    @csrf

    <x-ui.input
        name="email"
        type="email"
        label="Email"
        required
    />

    <x-ui.input
        name="password"
        type="password"
        label="Password"
        required
    />

    <x-ui.button type="submit" variant="primary" class="w-full">
        Submit
    </x-ui.button>
</form>
```

#### Add Alerts

```blade
@if(session('success'))
    <x-ui.alert type="success" dismissible>
        {{ session('success') }}
    </x-ui.alert>
@endif

@if($errors->any())
    <x-ui.alert type="error">
        Please fix the errors below
    </x-ui.alert>
@endif
```

### 3. Common Patterns

#### Landing Page

```blade
<x-layouts.app title="Welcome">
    <!-- Hero -->
    <x-sections.hero
        title="Your Product Name"
        subtitle="One-line pitch"
    >
        <x-slot:actions>
            <x-ui.button variant="primary" size="lg" href="/signup">
                Get Started Free
            </x-ui.button>
        </x-slot:actions>
    </x-sections.hero>

    <!-- Features -->
    <x-sections.feature-grid
        title="Why Choose Us"
        :columns="3"
        variant="gray"
    >
        <!-- Feature cards here -->
    </x-sections.feature-grid>
</x-layouts.app>
```

#### Login Page

```blade
<x-layouts.auth title="Sign In">
    <h2 class="text-2xl font-bold mb-6">Welcome Back</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            required
        />

        <x-ui.input
            name="password"
            type="password"
            label="Password"
            required
        />

        <x-ui.button type="submit" variant="primary" class="w-full">
            Sign In
        </x-ui.button>
    </form>

    <x-slot:footerLinks>
        <a href="/register" class="text-sm text-blue-600">
            Need an account?
        </a>
    </x-slot:footerLinks>
</x-layouts.auth>
```

#### Dashboard Page

```blade
<x-layouts.dashboard title="Dashboard" :sidebarItems="$items">
    <x-slot:header>
        <h1 class="text-2xl font-bold">Dashboard</h1>
    </x-slot:header>

    <!-- Stats -->
    <div class="grid md:grid-cols-3 gap-6">
        <x-ui.card>
            <div class="text-3xl font-bold">1,234</div>
            <div class="text-gray-600">Total Users</div>
        </x-ui.card>
        <!-- More stats -->
    </div>

    <!-- Content -->
    <x-ui.card class="mt-6">
        <x-slot:header>
            <h2 class="text-xl font-bold">Recent Activity</h2>
        </x-slot:header>

        <!-- Activity list -->
    </x-ui.card>
</x-layouts.dashboard>
```

### 4. Customization Tips

#### Override Classes

```blade
<x-ui.button variant="primary" class="!bg-purple-600 !hover:bg-purple-700">
    Custom Color
</x-ui.button>
```

#### Add Custom Sections

```blade
<section class="py-16 bg-gray-50">
    <x-layout.container>
        <h2 class="text-3xl font-bold mb-8">Custom Section</h2>
        <!-- Your content -->
    </x-layout.container>
</section>
```

#### Use Tailwind Utilities

All components support Tailwind utility classes:

```blade
<x-ui.card class="border-2 border-blue-500 hover:shadow-2xl">
    Enhanced card
</x-ui.card>
```

### 5. Next Steps

- **Read Full Documentation**: See `BLADE_LAYOUT_SYSTEM.md` for complete reference
- **View Demo Page**: Check `resources/views/layout-demo.blade.php` for examples
- **Customize Colors**: Modify Tailwind config for your brand colors
- **Add Alpine.js**: For interactive components (alerts, dropdowns)

### 6. Cheat Sheet

| Component | Usage | Common Props |
|-----------|-------|--------------|
| Button | `<x-ui.button>` | variant, size, href, icon |
| Card | `<x-ui.card>` | variant, padding, shadow |
| Input | `<x-ui.input>` | name, label, type, error |
| Alert | `<x-ui.alert>` | type, dismissible, icon |
| Badge | `<x-ui.badge>` | variant, size, icon |
| Hero | `<x-sections.hero>` | title, subtitle, variant |
| Feature Grid | `<x-sections.feature-grid>` | columns, title, variant |

### 7. Help

- Documentation: `BLADE_LAYOUT_SYSTEM.md`
- Demo: `resources/views/layout-demo.blade.php`
- Issues: Contact dev team

---

**Happy Building!** 🚀
