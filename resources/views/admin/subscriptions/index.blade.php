@extends('admin.layouts.admin')
@section('title', 'Subscriptions') @section('page-title', 'Subscriptions') @section('breadcrumb', 'All user subscriptions')
@section('content')

{{-- Revenue Banner --}}
<div class="mb-5 flex items-center gap-4 rounded-2xl bg-gradient-to-r from-[#052A70] to-[#075DE7] p-5 text-white shadow-sm">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <div>
        <p class="text-xs font-semibold text-white/60 uppercase tracking-wider">Estimated Active Revenue</p>
        <p class="text-2xl font-bold mt-0.5">{{ number_format($revenue / 100, 2) }} INR</p>
        <p class="text-xs text-white/50 mt-0.5">Based on active subscription plan prices</p>
    </div>
</div>

{{-- Filters --}}
<div class="mb-5 rounded-2xl border border-[#DCE6F2] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#5B6D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search user..."
                class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] py-2.5 pl-9 pr-4 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
        </div>
        <select name="status" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Status</option>
            @foreach(['active','cancelled','expired','trial'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="plan_id" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Plans</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}" {{ request('plan_id') === $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Filter</button>
        @if(request()->hasAny(['search','status','plan_id']))
            <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-4 py-2.5 text-sm text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#DCE6F2] px-6 py-4">
        <p class="text-sm font-semibold text-[#10233F]">{{ $subscriptions->total() }} subscriptions</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Plan</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Started</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Ends</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Provider</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EDF5FD]">
                @forelse($subscriptions as $sub)
                    <tr class="hover:bg-[#FAFCFF] transition-colors">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.users.show', $sub->user_id) }}" class="font-medium text-[#075DE7] hover:underline">{{ $sub->user->name ?? '—' }}</a>
                            <p class="text-xs text-[#5B6D86]">{{ $sub->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-[#10233F]">{{ $sub->plan->name ?? '—' }}</p>
                            @if($sub->plan) <p class="text-xs text-[#5B6D86]">{{ number_format($sub->plan->price_minor / 100, 2) }} {{ $sub->plan->currency }}/{{ $sub->plan->billing_period }}</p> @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $sc = ['active'=>'bg-[#E5F2FF] text-[#075DE7]','cancelled'=>'bg-red-100 text-red-700','expired'=>'bg-gray-100 text-gray-600','trial'=>'bg-blue-100 text-blue-700']; @endphp
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $sc[$sub->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($sub->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $sub->starts_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $sub->ends_at ? $sub->ends_at->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $sub->provider ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-[#5B6D86]">No subscriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
