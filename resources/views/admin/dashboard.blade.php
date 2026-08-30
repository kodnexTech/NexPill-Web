@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview of NexPill platform')

@section('content')

{{-- KPI Cards --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

    {{-- Total Users --}}
    <div class="relative overflow-hidden rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-[#5B6D86]">Total Users</p>
                <p class="mt-2 text-3xl font-bold text-[#10233F]">{{ number_format($stats['total_users']) }}</p>
                <p class="mt-1 text-xs text-[#5B6D86]">
                    <span class="font-semibold text-[#075DE7]">+{{ $stats['new_users_week'] }}</span> this week
                </p>
            </div>
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E5F2FF]">
                <svg class="h-5 w-5 text-[#075DE7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#075DE7] to-[#77E6D1]"></div>
    </div>

    {{-- Dose Adherence --}}
    <div class="relative overflow-hidden rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-[#5B6D86]">Today's Doses</p>
                <p class="mt-2 text-3xl font-bold text-[#10233F]">{{ $stats['taken_doses_today'] }}<span class="text-lg text-[#5B6D86]">/{{ $stats['total_doses_today'] }}</span></p>
                @php $pct = $stats['total_doses_today'] > 0 ? round(($stats['taken_doses_today']/$stats['total_doses_today'])*100) : 0; @endphp
                <p class="mt-1 text-xs text-[#5B6D86]"><span class="font-semibold text-[#075DE7]">{{ $pct }}%</span> adherence rate</p>
            </div>
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E5F2FF]">
                <svg class="h-5 w-5 text-[#075DE7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#E5F2FF]">
            <div class="h-full rounded-full bg-gradient-to-r from-[#075DE7] to-[#77E6D1] transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#075DE7] to-[#77E6D1]"></div>
    </div>

    {{-- Open Tickets --}}
    <div class="relative overflow-hidden rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-[#5B6D86]">Open Tickets</p>
                <p class="mt-2 text-3xl font-bold text-[#10233F]">{{ number_format($stats['open_tickets']) }}</p>
                <a href="{{ route('admin.support.index') }}?status=open" class="mt-1 text-xs font-semibold text-[#00BFA6] hover:underline">View all open →</a>
            </div>
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#DDF8F2]">
                <svg class="h-5 w-5 text-[#00BFA6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#00BFA6] to-[#ffa891]"></div>
    </div>

    {{-- Active Subscriptions --}}
    <div class="relative overflow-hidden rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-[#5B6D86]">Active Subs</p>
                <p class="mt-2 text-3xl font-bold text-[#10233F]">{{ number_format($stats['active_subs']) }}</p>
                <p class="mt-1 text-xs text-[#5B6D86]"><span class="font-semibold text-[#075DE7]">{{ $stats['active_users'] }}</span> active users</p>
            </div>
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#E5F2FF]">
                <svg class="h-5 w-5 text-[#075DE7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#075DE7] to-[#77E6D1]"></div>
    </div>
</div>

{{-- Charts + Recent --}}
<div class="mt-6 grid gap-6 lg:grid-cols-3">

    {{-- Adherence Chart --}}
    <div class="lg:col-span-2 rounded-2xl border border-[#DCE6F2] bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-sm font-bold text-[#10233F]">7-Day Adherence Trend</h2>
                <p class="text-xs text-[#5B6D86] mt-0.5">Dose completion rate by day</p>
            </div>
            <span class="rounded-full bg-[#E5F2FF] px-3 py-1 text-xs font-semibold text-[#075DE7]">Last 7 days</span>
        </div>
        <div class="flex h-40 items-end gap-2">
            @foreach($adherenceChart as $day)
                <div class="flex flex-1 flex-col items-center gap-1.5">
                    <span class="text-[10px] font-bold text-[#5B6D86]">{{ $day['pct'] }}%</span>
                    <div class="relative w-full rounded-t-lg overflow-hidden" style="height: 100px; background: #EDF5FD;">
                        <div class="absolute bottom-0 left-0 right-0 rounded-t-lg transition-all duration-500"
                             style="height: {{ $day['pct'] }}%; background: linear-gradient(to top, #073B9A, #075DE7);">
                        </div>
                    </div>
                    <span class="text-[10px] font-semibold text-[#5B6D86]">{{ $day['date'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="rounded-2xl border border-[#DCE6F2] bg-white p-6 shadow-sm">
        <h2 class="text-sm font-bold text-[#10233F] mb-4">Platform Stats</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between rounded-xl bg-[#F4FAFF] px-4 py-3">
                <span class="text-xs font-medium text-[#5B6D86]">Total Medicines</span>
                <span class="text-sm font-bold text-[#10233F]">{{ number_format($stats['total_medicines']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-[#F4FAFF] px-4 py-3">
                <span class="text-xs font-medium text-[#5B6D86]">Active Users</span>
                <span class="text-sm font-bold text-[#10233F]">{{ number_format($stats['active_users']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-[#F4FAFF] px-4 py-3">
                <span class="text-xs font-medium text-[#5B6D86]">Unread Notifications</span>
                <span class="text-sm font-bold text-[#10233F]">{{ number_format($stats['unread_notifications']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-[#F4FAFF] px-4 py-3">
                <span class="text-xs font-medium text-[#5B6D86]">Today's Total Doses</span>
                <span class="text-sm font-bold text-[#10233F]">{{ number_format($stats['total_doses_today']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl bg-[#F4FAFF] px-4 py-3">
                <span class="text-xs font-medium text-[#5B6D86]">New Users (7d)</span>
                <span class="text-sm font-bold text-[#075DE7]">+{{ $stats['new_users_week'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Recent Users + Tickets --}}
<div class="mt-6 grid gap-6 lg:grid-cols-2">

    {{-- Recent Users --}}
    <div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#DCE6F2] px-6 py-4">
            <h2 class="text-sm font-bold text-[#10233F]">Recent Users</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-[#075DE7] hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-[#EDF5FD]">
            @forelse($recentUsers as $user)
                <div class="flex items-center gap-3 px-6 py-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E5F2FF] text-xs font-bold text-[#075DE7]">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#10233F]">{{ $user->name }}</p>
                        <p class="truncate text-xs text-[#5B6D86]">{{ $user->email }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold
                            {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role->value === 'support' ? 'bg-blue-100 text-blue-700' : 'bg-[#E5F2FF] text-[#075DE7]') }}">
                            {{ ucfirst($user->role->value) }}
                        </span>
                        <span class="text-[10px] text-[#8DA0B8]">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-sm text-[#5B6D86]">No users yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Open Tickets --}}
    <div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#DCE6F2] px-6 py-4">
            <h2 class="text-sm font-bold text-[#10233F]">Open Support Tickets</h2>
            <a href="{{ route('admin.support.index') }}" class="text-xs font-semibold text-[#075DE7] hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-[#EDF5FD]">
            @forelse($recentTickets as $ticket)
                <a href="{{ route('admin.support.show', $ticket->id) }}"
                   class="flex items-center gap-3 px-6 py-3 hover:bg-[#F4FAFF] transition-colors">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#DDF8F2]">
                        <svg class="h-4 w-4 text-[#00BFA6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#10233F]">{{ $ticket->subject }}</p>
                        <p class="truncate text-xs text-[#5B6D86]">{{ $ticket->email }}</p>
                    </div>
                    <span class="text-[10px] text-[#8DA0B8] whitespace-nowrap">{{ $ticket->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-[#77E6D1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-[#5B6D86]">No open tickets. All good!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
