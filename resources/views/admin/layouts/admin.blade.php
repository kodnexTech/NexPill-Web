<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — NexPill Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css'])
    @stack('head')
</head>
<body class="h-full bg-[#f6faf8] font-[Inter,ui-sans-serif] antialiased">

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="closeSidebar()"></div>

<div class="flex h-full min-h-screen">

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-[#062f2c] transition-transform duration-300
               -translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:z-auto">

        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#f57863] shadow-lg">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
            </div>
            <div>
                <span class="text-base font-bold text-white tracking-tight">Nex<span class="text-[#f57863]">Pill</span></span>
                <p class="text-[10px] font-medium text-white/40 leading-none mt-0.5">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            @php
                $currentRoute = request()->route()->getName();
                function isActive(string $pattern): string {
                    return str_starts_with(request()->route()->getName() ?? '', $pattern)
                        ? 'bg-white/10 text-white'
                        : 'text-white/60 hover:bg-white/7 hover:text-white';
                }
            @endphp

            <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-white/30">Overview</p>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.dashboard') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <p class="px-3 pb-1 pt-4 text-[10px] font-bold uppercase tracking-widest text-white/30">Users & Data</p>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.users') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Users
            </a>

            <a href="{{ route('admin.medicines.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.medicines') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Medicines
            </a>

            <a href="{{ route('admin.dose-logs.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.dose-logs') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Dose Logs
            </a>

            <a href="{{ route('admin.subscriptions.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.subscriptions') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Subscriptions
            </a>

            <p class="px-3 pb-1 pt-4 text-[10px] font-bold uppercase tracking-widest text-white/30">Support & Content</p>

            <a href="{{ route('admin.support.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.support') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Support Tickets
                @php $openTickets = \App\Models\SupportTicket::where('status','open')->count(); @endphp
                @if($openTickets > 0)
                    <span class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-[#f57863] px-1.5 text-[10px] font-bold text-white">{{ $openTickets }}</span>
                @endif
            </a>

            <a href="{{ route('admin.plans.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.plans') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Plans
            </a>

            <a href="{{ route('admin.notifications.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.notifications') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Notifications
            </a>

            <a href="{{ route('admin.legal.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.legal') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Legal Docs
            </a>

            <p class="px-3 pb-1 pt-4 text-[10px] font-bold uppercase tracking-widest text-white/30">System</p>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ isActive('admin.audit-logs') }}">
                <svg class="h-4.5 w-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Audit Logs
            </a>
        </nav>

        {{-- User footer --}}
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#0f806f] text-sm font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-white/45">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-1.5 text-white/40 hover:bg-white/10 hover:text-white transition-colors" title="Logout">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ─────────────────────────────────────────────────── --}}
    <div class="flex flex-1 flex-col min-w-0">

        {{-- Topbar --}}
        <header class="sticky top-0 z-10 flex h-16 items-center gap-4 border-b border-[#dde8e5] bg-white/90 px-4 shadow-sm backdrop-blur-sm lg:px-6">
            {{-- Mobile menu button --}}
            <button onclick="openSidebar()"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#dde8e5] text-[#60716e] hover:bg-[#f4f8f5] transition-colors lg:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb --}}
            <div class="flex-1 min-w-0">
                <h1 class="truncate text-base font-semibold text-[#102a2a]">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <p class="text-xs text-[#60716e] mt-0.5">@yield('breadcrumb')</p>
                @endif
            </div>

            {{-- Right side actions --}}
            <div class="flex items-center gap-2">
                <a href="{{ url('/') }}" target="_blank"
                   class="hidden items-center gap-2 rounded-lg border border-[#dde8e5] px-3 py-2 text-xs font-medium text-[#60716e] hover:bg-[#f4f8f5] transition-colors sm:flex">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Site
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mx-4 mt-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800 lg:mx-6" id="flash-success">
                <svg class="h-4 w-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
                <button onclick="this.closest('#flash-success').remove()" class="ml-auto text-green-500 hover:text-green-700">✕</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-4 mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 lg:mx-6" id="flash-error">
                <svg class="h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
                <button onclick="this.closest('#flash-error').remove()" class="ml-auto text-red-500 hover:text-red-700">✕</button>
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-[#dde8e5] px-6 py-3 text-xs text-[#60716e]">
            © {{ date('Y') }} NexPill Admin Panel
        </footer>
    </div>
</div>

<script>
    function openSidebar()  { document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('sidebar-overlay').classList.remove('hidden'); }
    function closeSidebar() { document.getElementById('sidebar').classList.add('-translate-x-full');    document.getElementById('sidebar-overlay').classList.add('hidden'); }
</script>
@stack('scripts')
</body>
</html>
