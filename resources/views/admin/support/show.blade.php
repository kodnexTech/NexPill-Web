@extends('admin.layouts.admin')
@section('title', $ticket->subject) @section('page-title', 'Support Ticket') @section('breadcrumb', 'Support / ' . $ticket->subject)
@section('content')

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Ticket Info --}}
    <div class="space-y-4">
        <div class="rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
            <h2 class="text-base font-bold text-[#10233F]">{{ $ticket->subject }}</h2>
            <div class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-[#F4FAFF] px-3 py-2">
                    <span class="text-[#5B6D86]">Email</span>
                    <span class="font-medium text-[#10233F]">{{ $ticket->email }}</span>
                </div>
                <div class="flex justify-between rounded-xl bg-[#F4FAFF] px-3 py-2">
                    <span class="text-[#5B6D86]">Category</span>
                    <span class="font-medium text-[#10233F] capitalize">{{ $ticket->category }}</span>
                </div>
                <div class="flex justify-between rounded-xl bg-[#F4FAFF] px-3 py-2">
                    <span class="text-[#5B6D86]">Created</span>
                    <span class="font-medium text-[#10233F]">{{ $ticket->created_at->format('d M Y') }}</span>
                </div>
            </div>

            {{-- Status + Priority Update --}}
            <form method="POST" action="{{ route('admin.support.status', $ticket->id) }}" class="mt-4 space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="mb-1 block text-xs font-bold text-[#5B6D86]">Status</label>
                    <select name="status" class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2 text-sm focus:border-[#075DE7] focus:outline-none">
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-[#5B6D86]">Priority</label>
                    <select name="priority" class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-3 py-2 text-sm focus:border-[#075DE7] focus:outline-none">
                        @foreach(['low','normal','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full rounded-xl bg-[#073B9A] py-2 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">Update</button>
            </form>
        </div>

        <a href="{{ route('admin.support.index') }}" class="flex items-center gap-2 text-sm text-[#5B6D86] hover:text-[#075DE7] transition-colors">
            ← Back to tickets
        </a>
    </div>

    {{-- Thread --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="space-y-3">
            @forelse($ticket->messages as $msg)
                <div class="rounded-2xl border p-4 shadow-sm {{ $msg->is_staff_reply ? 'border-[#075DE7]/20 bg-[#f0faf7]' : 'border-[#DCE6F2] bg-white' }}">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold
                            {{ $msg->is_staff_reply ? 'bg-[#075DE7] text-white' : 'bg-[#E5F2FF] text-[#075DE7]' }}">
                            {{ $msg->is_staff_reply ? 'S' : 'U' }}
                        </div>
                        <span class="text-xs font-semibold {{ $msg->is_staff_reply ? 'text-[#075DE7]' : 'text-[#10233F]' }}">
                            {{ $msg->is_staff_reply ? ($msg->sender->name ?? 'Staff') : 'User' }}
                        </span>
                        <span class="text-xs text-[#8DA0B8] ml-auto">{{ $msg->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <p class="text-sm leading-relaxed text-[#10233F] whitespace-pre-wrap">{{ $msg->message }}</p>
                </div>
            @empty
                <p class="text-center text-sm text-[#5B6D86] py-8">No messages yet.</p>
            @endforelse
        </div>

        {{-- Reply --}}
        @if(!in_array($ticket->status, ['resolved','closed']))
            <div class="rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-[#10233F]">Send Reply</h3>
                <form method="POST" action="{{ route('admin.support.reply', $ticket->id) }}">
                    @csrf
                    <textarea name="message" rows="5" required
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] p-3 text-sm text-[#10233F] placeholder-[#8DA0B8] focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15 resize-none"
                        placeholder="Type your reply..."></textarea>
                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="rounded-2xl border border-[#DCE6F2] bg-[#F4FAFF] p-4 text-center text-sm text-[#5B6D86]">
                This ticket is {{ $ticket->status }}. No further replies can be added.
            </div>
        @endif
    </div>
</div>
@endsection
