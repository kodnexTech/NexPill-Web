@extends('admin.layouts.admin')
@section('title', 'Dose Logs') @section('page-title', 'Dose Logs') @section('breadcrumb', 'All dose logs across users')
@section('content')

{{-- Status Summary --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
    @php $statusConfig = ['taken'=>['#e4f5f1','#0f806f'],'missed'=>['#fee2e2','#dc2626'],'skipped'=>['#fef3c7','#d97706'],'scheduled'=>['#dbeafe','#2563eb'],'snoozed'=>['#f3e8ff','#7c3aed']]; @endphp
    @foreach($statusConfig as $s => $colors)
        <div class="rounded-xl border border-[#dde8e5] bg-white p-4 text-center shadow-sm">
            <p class="text-xl font-bold" style="color: {{ $colors[1] }}">{{ $statusCounts[$s] ?? 0 }}</p>
            <p class="mt-1 text-xs font-semibold text-[#60716e] capitalize">{{ $s }}</p>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search user..."
                class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] py-2.5 pl-9 pr-4 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
        </div>
        <select name="status" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Status</option>
            @foreach(['taken','missed','skipped','scheduled','snoozed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
        @if(request()->hasAny(['search','status','date_from','date_to']))
            <a href="{{ route('admin.dose-logs.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-4 py-2.5 text-sm text-[#60716e] hover:bg-[#f6faf8] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#dde8e5] px-6 py-4">
        <p class="text-sm font-semibold text-[#102a2a]">{{ $logs->total() }} logs found</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Medicine</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Scheduled</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Taken At</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($logs as $log)
                    @php $sc = ['taken'=>'bg-[#e4f5f1] text-[#0f806f]','missed'=>'bg-red-100 text-red-700','skipped'=>'bg-amber-100 text-amber-700','scheduled'=>'bg-blue-100 text-blue-700','snoozed'=>'bg-purple-100 text-purple-700']; @endphp
                    <tr class="hover:bg-[#f9fcfb] transition-colors">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.users.show', $log->user_id) }}" class="font-medium text-[#0f806f] hover:underline">{{ $log->user->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3 font-medium text-[#102a2a]">{{ $log->medicine->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-[#60716e]">{{ $log->scheduled_for->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 text-xs text-[#60716e]">{{ $log->taken_at ? $log->taken_at->format('h:i A') : '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$log->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#60716e]">No dose logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
