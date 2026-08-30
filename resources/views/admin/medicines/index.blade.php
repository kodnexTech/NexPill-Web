@extends('admin.layouts.admin')
@section('title', 'Medicines') @section('page-title', 'Medicines') @section('breadcrumb', 'All medicines across users')
@section('content')

<div class="mb-5 rounded-2xl border border-[#DCE6F2] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#5B6D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search medicine name..."
                class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] py-2.5 pl-9 pr-4 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
        </div>
        <select name="form" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Forms</option>
            @foreach(['tablet','capsule','liquid','injection','patch','inhaler','drops','other'] as $f)
                <option value="{{ $f }}" {{ request('form') === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Status</option>
            <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>Active</option>
            <option value="paused"  {{ request('status') === 'paused'  ? 'selected' : '' }}>Paused</option>
            <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
        </select>
        <button type="submit" class="rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Filter</button>
        @if(request()->hasAny(['search','form','status']))
            <a href="{{ route('admin.medicines.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-4 py-2.5 text-sm text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#DCE6F2] px-6 py-4">
        <p class="text-sm font-semibold text-[#10233F]">{{ $medicines->total() }} medicines found</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Medicine</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Form</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Inventory</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EDF5FD]">
                @forelse($medicines as $med)
                    <tr class="hover:bg-[#FAFCFF] transition-colors {{ $med->trashed() ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#10233F]">{{ $med->name }}</p>
                            @if($med->strength) <p class="text-xs text-[#5B6D86]">{{ $med->strength }} {{ $med->unit }}</p> @endif
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('admin.users.show', $med->user_id) }}" class="text-[#075DE7] hover:underline font-medium">{{ $med->user->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-4"><span class="rounded-full bg-[#F4FAFF] border border-[#DCE6F2] px-2.5 py-1 text-[11px] font-semibold text-[#5B6D86]">{{ ucfirst($med->form) }}</span></td>
                        <td class="px-4 py-4 text-sm text-[#10233F]">
                            @if($med->inventory_remaining !== null)
                                {{ $med->inventory_remaining }} / {{ $med->inventory_total ?? '?' }}
                            @else
                                <span class="text-[#8DA0B8]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($med->trashed())
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-bold text-red-700">Deleted</span>
                            @elseif($med->is_paused)
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">Paused</span>
                            @else
                                <span class="rounded-full bg-[#E5F2FF] px-2.5 py-1 text-[11px] font-bold text-[#075DE7]">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.medicines.show', $med->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#DCE6F2] px-3 py-1.5 text-xs font-semibold text-[#10233F] hover:bg-[#F4FAFF] transition-colors">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-[#5B6D86]">No medicines found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($medicines->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $medicines->links() }}</div>
    @endif
</div>
@endsection
