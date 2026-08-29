@extends('admin.layouts.admin')
@section('title', 'Audit Logs') @section('page-title', 'Audit Logs') @section('breadcrumb', 'System activity logs (read-only)')
@section('content')

<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <input name="action" value="{{ request('action') }}" placeholder="Filter by action..."
                class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
        </div>
        <select name="actor_id" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Actors</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" {{ request('actor_id') === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
        @if(request()->hasAny(['action','actor_id','date_from','date_to']))
            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-4 py-2.5 text-sm text-[#60716e] hover:bg-[#f6faf8] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#dde8e5] px-6 py-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm font-semibold text-[#102a2a]">{{ $logs->total() }} audit entries (read-only)</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Actor</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">IP Address</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($logs as $log)
                    <tr class="hover:bg-[#f9fcfb] transition-colors">
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-lg bg-[#f0f7f4] border border-[#dde8e5] px-2.5 py-1 text-xs font-mono font-semibold text-[#102a2a]">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-[#102a2a]">{{ $log->actor->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-[#60716e]">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-[#60716e]">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-[#60716e]">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
