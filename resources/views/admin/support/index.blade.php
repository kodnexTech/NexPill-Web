@extends('admin.layouts.admin')
@section('title', 'Support Tickets') @section('page-title', 'Support Tickets') @section('breadcrumb', 'Manage user support requests')
@section('content')

{{-- Status Tabs --}}
<div class="mb-5 flex gap-2 flex-wrap">
    @php $tabColors = ['open'=>'#f57863','in_progress'=>'#2563eb','resolved'=>'#0f806f','closed'=>'#60716e']; @endphp
    @foreach($counts as $status => $count)
        <a href="{{ route('admin.support.index') }}?status={{ $status }}"
           class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors
                  {{ request('status') === $status ? 'border-[#063f3a] bg-[#063f3a] text-white' : 'border-[#dde8e5] bg-white text-[#60716e] hover:bg-[#f6faf8]' }}">
            {{ ucfirst(str_replace('_', ' ', $status)) }}
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ request('status') === $status ? 'bg-white/20' : 'bg-[#f6faf8]' }}">{{ $count }}</span>
        </a>
    @endforeach
    <a href="{{ route('admin.support.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] bg-white px-4 py-2 text-sm font-semibold text-[#60716e] hover:bg-[#f6faf8] transition-colors {{ !request('status') ? 'border-[#063f3a] bg-[#063f3a] text-white' : '' }}">
        All
    </a>
</div>

{{-- Filters --}}
<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search email or subject..."
                class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] py-2.5 pl-9 pr-4 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
        </div>
        <select name="priority" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Priority</option>
            @foreach(['low','normal','high','urgent'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="category" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Categories</option>
            @foreach(['general','account','billing','technical','safety'] as $c)
                <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
    </form>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#dde8e5] px-6 py-4">
        <p class="text-sm font-semibold text-[#102a2a]">{{ $tickets->total() }} tickets</p>
    </div>
    <div class="divide-y divide-[#f0f6f4]">
        @forelse($tickets as $ticket)
            <a href="{{ route('admin.support.show', $ticket->id) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-[#f9fcfb] transition-colors">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                    {{ $ticket->status === 'open' ? 'bg-[#ffeae5]' : ($ticket->status === 'resolved' ? 'bg-[#e4f5f1]' : 'bg-[#f6faf8]') }}">
                    <svg class="h-5 w-5 {{ $ticket->status === 'open' ? 'text-[#f57863]' : ($ticket->status === 'resolved' ? 'text-[#0f806f]' : 'text-[#60716e]') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#102a2a]">{{ $ticket->subject }}</p>
                    <p class="truncate text-xs text-[#60716e]">{{ $ticket->email }} · {{ $ticket->category }} · {{ $ticket->messages_count }} messages</p>
                </div>
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                    @php $sc = ['open'=>'bg-[#ffeae5] text-[#f57863]','in_progress'=>'bg-blue-100 text-blue-700','resolved'=>'bg-[#e4f5f1] text-[#0f806f]','closed'=>'bg-gray-100 text-gray-600']; @endphp
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    @php $pc = ['urgent'=>'bg-red-100 text-red-700','high'=>'bg-amber-100 text-amber-700','normal'=>'bg-gray-100 text-gray-600','low'=>'bg-gray-50 text-gray-400']; @endphp
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $pc[$ticket->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($ticket->priority) }}</span>
                    <span class="text-[10px] text-[#a0b5b1]">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-[#a8ded1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-2 text-sm text-[#60716e]">No tickets found.</p>
            </div>
        @endforelse
    </div>
    @if($tickets->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
