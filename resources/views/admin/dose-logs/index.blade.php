@extends('admin.layouts.admin')
@section('title', 'Dose Logs') @section('page-title', 'Dose Logs') @section('breadcrumb', 'All dose logs across users')
@section('content')

{{-- Status Summary --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
    @php $statusConfig = ['taken'=>['#E5F2FF','#075DE7'],'missed'=>['#fee2e2','#dc2626'],'skipped'=>['#fef3c7','#d97706'],'scheduled'=>['#dbeafe','#2563eb'],'snoozed'=>['#f3e8ff','#7c3aed']]; @endphp
    @foreach($statusConfig as $s => $colors)
        <div class="rounded-xl border border-[#DCE6F2] bg-white p-4 text-center shadow-sm">
            <p class="text-xl font-bold" style="color: {{ $colors[1] }}">{{ $statusCounts[$s] ?? 0 }}</p>
            <p class="mt-1 text-xs font-semibold text-[#5B6D86] capitalize">{{ $s }}</p>
        </div>
    @endforeach
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
            @foreach(['taken','missed','skipped','scheduled','snoozed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
        <button type="submit" class="rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Filter</button>
        @if(request()->hasAny(['search','status','date_from','date_to']))
            <a href="{{ route('admin.dose-logs.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-4 py-2.5 text-sm text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#DCE6F2] px-6 py-4">
        <p class="text-sm font-semibold text-[#10233F]">{{ $logs->total() }} logs found</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Medicine</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Scheduled</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Taken At</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EDF5FD]">
                @forelse($logs as $log)
                    @php $sc = ['taken'=>'bg-[#E5F2FF] text-[#075DE7]','missed'=>'bg-red-100 text-red-700','skipped'=>'bg-amber-100 text-amber-700','scheduled'=>'bg-blue-100 text-blue-700','snoozed'=>'bg-purple-100 text-purple-700']; @endphp
                    <tr class="hover:bg-[#FAFCFF] transition-colors">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.users.show', $log->user_id) }}" class="font-medium text-[#075DE7] hover:underline">{{ $log->user->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3 font-medium text-[#10233F]">{{ $log->medicine->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $log->scheduled_for->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 text-xs text-[#5B6D86]">{{ $log->taken_at ? $log->taken_at->format('h:i A') : '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$log->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#5B6D86]">No dose logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
