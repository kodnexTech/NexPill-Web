@extends('admin.layouts.admin')
@section('title', 'Support Tickets') @section('page-title', 'Support Tickets') @section('breadcrumb', 'Manage user support requests')
@section('content')

{{-- Status Tabs --}}
<div class="mb-5 flex gap-2 flex-wrap">
    @php $tabColors = ['open'=>'#00BFA6','in_progress'=>'#2563eb','resolved'=>'#075DE7','closed'=>'#5B6D86']; @endphp
    @foreach($counts as $status => $count)
        <a href="{{ route('admin.support.index') }}?status={{ $status }}"
           class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors
                  {{ request('status') === $status ? 'border-[#073B9A] bg-[#073B9A] text-white' : 'border-[#DCE6F2] bg-white text-[#5B6D86] hover:bg-[#F4FAFF]' }}">
            {{ ucfirst(str_replace('_', ' ', $status)) }}
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ request('status') === $status ? 'bg-white/20' : 'bg-[#F4FAFF]' }}">{{ $count }}</span>
        </a>
    @endforeach
    <a href="{{ route('admin.support.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] bg-white px-4 py-2 text-sm font-semibold text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors {{ !request('status') ? 'border-[#073B9A] bg-[#073B9A] text-white' : '' }}">
        All
    </a>
</div>

{{-- Filters --}}
<div class="mb-5 rounded-2xl border border-[#DCE6F2] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#5B6D86]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search email or subject..."
                class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] py-2.5 pl-9 pr-4 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
        </div>
        <select name="priority" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Priority</option>
            @foreach(['low','normal','high','urgent'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="category" class="rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2.5 text-sm focus:border-[#075DE7] focus:outline-none">
            <option value="">All Categories</option>
            @foreach(['general','account','billing','technical','safety'] as $c)
                <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Filter</button>
    </form>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#DCE6F2] px-6 py-4">
        <p class="text-sm font-semibold text-[#10233F]">{{ $tickets->total() }} tickets</p>
    </div>
    <div class="divide-y divide-[#EDF5FD]">
        @forelse($tickets as $ticket)
            <a href="{{ route('admin.support.show', $ticket->id) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-[#FAFCFF] transition-colors">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                    {{ $ticket->status === 'open' ? 'bg-[#DDF8F2]' : ($ticket->status === 'resolved' ? 'bg-[#E5F2FF]' : 'bg-[#F4FAFF]') }}">
                    <svg class="h-5 w-5 {{ $ticket->status === 'open' ? 'text-[#00BFA6]' : ($ticket->status === 'resolved' ? 'text-[#075DE7]' : 'text-[#5B6D86]') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#10233F]">{{ $ticket->subject }}</p>
                    <p class="truncate text-xs text-[#5B6D86]">{{ $ticket->email }} · {{ $ticket->category }} · {{ $ticket->messages_count }} messages</p>
                </div>
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                    @php $sc = ['open'=>'bg-[#DDF8F2] text-[#00BFA6]','in_progress'=>'bg-blue-100 text-blue-700','resolved'=>'bg-[#E5F2FF] text-[#075DE7]','closed'=>'bg-gray-100 text-gray-600']; @endphp
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    @php $pc = ['urgent'=>'bg-red-100 text-red-700','high'=>'bg-amber-100 text-amber-700','normal'=>'bg-gray-100 text-gray-600','low'=>'bg-gray-50 text-gray-400']; @endphp
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $pc[$ticket->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($ticket->priority) }}</span>
                    <span class="text-[10px] text-[#8DA0B8]">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-[#77E6D1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-2 text-sm text-[#5B6D86]">No tickets found.</p>
            </div>
        @endforelse
    </div>
    @if($tickets->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
