@extends('admin.layouts.admin')
@section('title', 'App Notifications') @section('page-title', 'App Notifications') @section('breadcrumb', 'All push/in-app notifications')
@section('content')

<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="search" value="{{ request('search') }}" placeholder="Search user..."
                class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] py-2.5 pl-9 pr-4 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
        </div>
        <select name="type" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All Types</option>
            @foreach($types as $type)
                <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
        </select>
        <select name="read" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none">
            <option value="">All</option>
            <option value="read"   {{ request('read') === 'read'   ? 'selected' : '' }}>Read</option>
            <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>Unread</option>
        </select>
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
        @if(request()->hasAny(['search','type','read']))
            <a href="{{ route('admin.notifications.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-4 py-2.5 text-sm text-[#60716e] hover:bg-[#f6faf8] transition-colors">Clear</a>
        @endif
    </form>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="border-b border-[#dde8e5] px-6 py-4">
        <p class="text-sm font-semibold text-[#102a2a]">{{ $notifications->total() }} notifications</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Read</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Sent</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($notifications as $notif)
                    <tr class="hover:bg-[#f9fcfb] transition-colors {{ !$notif->read_at ? 'bg-[#fffbf4]' : '' }}">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.users.show', $notif->user_id) }}" class="font-medium text-[#0f806f] hover:underline">{{ $notif->user->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-[#102a2a]">{{ $notif->title }}</p>
                            <p class="text-xs text-[#60716e] truncate max-w-60">{{ $notif->message }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-lg bg-[#f0f7f4] border border-[#dde8e5] px-2 py-0.5 text-[10px] font-mono font-semibold text-[#60716e]">{{ $notif->type->value ?? $notif->type }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($notif->read_at)
                                <span class="rounded-full bg-[#e4f5f1] px-2.5 py-1 text-[10px] font-bold text-[#0f806f]">Read</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700">Unread</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-[#60716e]">{{ $notif->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#60716e]">No notifications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
