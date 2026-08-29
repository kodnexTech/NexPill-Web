<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'NexPill helps people and families manage medicines, reminders, refills, appointments, and adherence in one calm place.')">
    <meta name="theme-color" content="#063f3a">
    <title>@yield('title', 'NexPill — Medication care, kept human')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-mist text-ink antialiased">
<header class="site-header">
    <nav class="shell flex h-20 items-center justify-between" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="brand" aria-label="NexPill home">
            <span class="brand-mark"><img src="/images/nexpill-mark.png" alt="" width="42" height="42"></span>
            <span>Nex<span>Pill</span></span>
        </a>
        <div class="hidden items-center gap-8 md:flex">
            <a class="nav-link" href="{{ route('home') }}#features">Features</a>
            <a class="nav-link" href="{{ route('home') }}#families">For families</a>
            <a class="nav-link" href="{{ route('privacy') }}">Privacy</a>
            <a class="nav-link" href="{{ route('support') }}">Support</a>
            <a class="button button-dark" href="{{ route('support') }}">Get early access</a>
        </div>
        <details class="relative md:hidden">
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
    <div class="shell grid gap-10 py-14 md:grid-cols-[1.5fr_1fr_1fr]">
        <div><a href="{{ route('home') }}" class="brand brand-light"><span>Nex<span>Pill</span></span></a><p class="mt-4 max-w-sm text-sm leading-6 text-white/60">Medication organization and reminders for everyday care. NexPill does not replace professional medical advice.</p></div>
        <div><p class="footer-title">Product</p><a href="{{ route('home') }}#features">Features</a><a href="{{ route('support') }}">Support</a><a href="/admin">Admin</a></div>
        <div><p class="footer-title">Legal</p><a href="{{ route('privacy') }}">Privacy policy</a><a href="{{ route('terms') }}">Terms of service</a><a href="{{ route('data-deletion') }}">Delete my data</a></div>
    </div>
    <div class="shell border-t border-white/10 py-6 text-xs text-white/45">© {{ date('Y') }} NexPill. Built for safer medication routines.</div>
</footer>
</body>
</html>
