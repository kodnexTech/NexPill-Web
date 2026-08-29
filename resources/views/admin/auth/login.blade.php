<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — NexPill</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-[#f4f8f5] via-[#edf5f1] to-[#e4f0ec] p-4 font-[Poppins,sans-serif] antialiased">

<div class="w-full max-w-md">

    {{-- Card --}}
    <div class="overflow-hidden rounded-2xl border border-[#dde8e5] bg-white shadow-xl shadow-[#0f806f]/5">

        {{-- Header --}}
        <div class="border-b border-[#dde8e5] bg-gradient-to-r from-[#062f2c] to-[#0a4a44] px-8 py-8">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f57863] shadow-lg">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Nex<span class="text-[#f57863]">Pill</span></h1>
                    <p class="text-xs text-white/50 mt-0.5">Admin Panel</p>
                </div>
            </div>
            <p class="mt-5 text-lg font-semibold text-white">Welcome back</p>
            <p class="mt-1 text-sm text-white/55">Sign in to your admin account to continue</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-8">

            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-[#102a2a] mb-1.5">Email address</label>
                    <input
                        id="email" name="email" type="email"
                        value="{{ old('email') }}"
                        autocomplete="email" required autofocus
                        class="block w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm text-[#102a2a] placeholder-[#a0b5b1] transition-all
                               focus:border-[#0f806f] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="admin@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[#102a2a] mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            id="password" name="password" type="password"
                            autocomplete="current-password" required
                            class="block w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm text-[#102a2a] placeholder-[#a0b5b1] transition-all
                                   focus:border-[#0f806f] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#60716e] hover:text-[#0f806f] transition-colors">
                            <svg id="eye-icon" class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-[#dde8e5] text-[#0f806f] focus:ring-[#0f806f]/20">
                    <label for="remember" class="text-sm text-[#60716e]">Remember me</label>
                </div>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#063f3a] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#063f3a]/20
                           transition-all hover:bg-[#0b514a] hover:shadow-xl hover:shadow-[#063f3a]/30 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign in to Admin Panel
                </button>
            </form>

            <div class="mt-6 flex items-center justify-center gap-2">
                <svg class="h-3.5 w-3.5 text-[#a0b5b1]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs text-[#a0b5b1]">Restricted to admin accounts only</p>
            </div>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-[#60716e]">
        <a href="{{ route('home') }}" class="text-[#0f806f] hover:underline">← Back to NexPill</a>
    </p>
</div>

<script>
    function togglePwd() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>
