@extends('admin.layouts.admin')
@section('title', 'Audit Logs') @section('page-title', 'Audit Logs') @section('breadcrumb', 'System activity logs (read-only)')
@section('content')

<div class="mb-5 rounded-2xl border border-[#DCE6F2] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <input name="action" value="{{ request('action') }}" placeholder="Filter by action..."
                class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
        </div>
        <select name="actor_id" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Actors</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" {{ request('actor_id') === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
        <button type="submit" class="rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Filter</button>
        @if(request()->hasAny(['action','actor_id','date_from','date_to']))
            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-4 py-2.5 text-sm text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#DCE6F2] px-6 py-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-[#5B6D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm font-semibold text-[#10233F]">{{ $logs->total() }} audit entries (read-only)</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Actor</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">IP Address</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EDF5FD]">
                @forelse($logs as $log)
                    <tr class="hover:bg-[#FAFCFF] transition-colors">
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-lg bg-[#EDF5FD] border border-[#DCE6F2] px-2.5 py-1 text-xs font-mono font-semibold text-[#10233F]">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-[#10233F]">{{ $log->actor->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-[#5B6D86]">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-[#5B6D86]">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
