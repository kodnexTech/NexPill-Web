@extends('admin.layouts.admin')
@section('title', 'Medicines') @section('page-title', 'Medicines') @section('breadcrumb', 'All medicines across users')
@section('content')

<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search medicine name..."
                class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] py-2.5 pl-9 pr-4 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
        </div>
        <select name="form" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Forms</option>
            @foreach(['tablet','capsule','liquid','injection','patch','inhaler','drops','other'] as $f)
                <option value="{{ $f }}" {{ request('form') === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Status</option>
            <option value="active"  {{ request('status') === 'active'  ? 'selected' : '' }}>Active</option>
            <option value="paused"  {{ request('status') === 'paused'  ? 'selected' : '' }}>Paused</option>
            <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
        </select>
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
        @if(request()->hasAny(['search','form','status']))
            <a href="{{ route('admin.medicines.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-4 py-2.5 text-sm text-[#60716e] hover:bg-[#f6faf8] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#dde8e5] px-6 py-4">
        <p class="text-sm font-semibold text-[#102a2a]">{{ $medicines->total() }} medicines found</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Medicine</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Form</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Inventory</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#60716e]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($medicines as $med)
                    <tr class="hover:bg-[#f9fcfb] transition-colors {{ $med->trashed() ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#102a2a]">{{ $med->name }}</p>
                            @if($med->strength) <p class="text-xs text-[#60716e]">{{ $med->strength }} {{ $med->unit }}</p> @endif
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('admin.users.show', $med->user_id) }}" class="text-[#0f806f] hover:underline font-medium">{{ $med->user->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-4"><span class="rounded-full bg-[#f6faf8] border border-[#dde8e5] px-2.5 py-1 text-[11px] font-semibold text-[#60716e]">{{ ucfirst($med->form) }}</span></td>
                        <td class="px-4 py-4 text-sm text-[#102a2a]">
                            @if($med->inventory_remaining !== null)
                                {{ $med->inventory_remaining }} / {{ $med->inventory_total ?? '?' }}
                            @else
                                <span class="text-[#a0b5b1]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($med->trashed())
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-bold text-red-700">Deleted</span>
                            @elseif($med->is_paused)
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">Paused</span>
                            @else
                                <span class="rounded-full bg-[#e4f5f1] px-2.5 py-1 text-[11px] font-bold text-[#0f806f]">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.medicines.show', $med->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#dde8e5] px-3 py-1.5 text-xs font-semibold text-[#102a2a] hover:bg-[#f6faf8] transition-colors">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-[#60716e]">No medicines found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($medicines->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $medicines->links() }}</div>
    @endif
</div>
@endsection
