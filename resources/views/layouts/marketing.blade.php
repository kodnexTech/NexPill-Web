<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'NexPill helps people and families manage medicines, reminders, refills, appointments, and adherence in one calm place.')">
    <meta name="theme-color" content="#075DE7">
    <title>@yield('title', 'NexPill — Medication care, kept human')</title>
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-mist text-ink antialiased font-[Poppins,sans-serif]">
<header class="site-header">
    <nav class="shell flex h-20 items-center justify-between" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="brand" aria-label="NexPill home">
            <img class="brand-logo" src="/images/nexpill-logo-horizontal.png" alt="NexPill" width="172" height="54">
        </a>
        <div class="hidden items-center gap-8 lg:flex">
            <a class="nav-link" href="{{ route('home') }}#features">Features</a>
            <a class="nav-link" href="{{ route('home') }}#families">For families</a>
            <a class="nav-link" href="{{ route('privacy') }}">Privacy</a>
            <a class="nav-link" href="{{ route('support') }}">Support</a>
            <a class="button button-dark" href="{{ route('support') }}">Get early access</a>
        </div>
        <details class="relative lg:hidden">
            <summary class="menu-button" aria-label="Open navigation">Menu</summary>
            <div class="mobile-menu">
                <a href="{{ route('home') }}#features">Features</a><a href="{{ route('home') }}#families">For families</a>
                <a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('support') }}">Support</a>
            </div>
        </details>
    </nav>
</header>

<main>@yield('content')</main>

<footer class="footer">
    <div class="shell grid gap-12 py-16 md:grid-cols-[1.6fr_1fr_1fr]">
        <div>
            <a href="{{ route('home') }}" class="footer-brand" aria-label="NexPill home">
                <img src="/images/nexpill-logo-horizontal.png" alt="NexPill" width="160" height="48" class="h-8 w-auto object-contain">
            </a>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-blue-100/75">
                Medication organization and reminders for everyday care. NexPill does not replace professional medical advice.
            </p>
            <div class="mt-5 flex items-center gap-2.5 text-xs font-semibold text-[#77E6D1]">
                <span class="inline-flex h-2 w-2 rounded-full bg-[#00BFA6] animate-pulse"></span>
                <span>Privacy-first & consent-based care</span>
            </div>
        </div>
        <div>
            <p class="footer-title">Product</p>
            <a href="{{ route('home') }}#features">Features</a>
            <a href="{{ route('home') }}#families">For families</a>
            <a href="{{ route('support') }}">Support</a>
            <a href="{{ route('admin.dashboard') }}">Admin Portal</a>
        </div>
        <div>
            <p class="footer-title">Legal & Policy</p>
            <a href="{{ route('privacy') }}">Privacy policy</a>
            <a href="{{ route('terms') }}">Terms of service</a>
            <a href="{{ route('data-deletion') }}">Delete my data</a>
        </div>
    </div>
    <div class="shell border-t border-white/10 py-6 text-xs text-blue-200/60 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div>© {{ date('Y') }} NexPill. Built for safer, calmer medication routines.</div>
        <div class="flex items-center gap-4 text-blue-200/50">
            <span>All rights reserved</span>
        </div>
    </div>
</footer>
</body>
</html>
