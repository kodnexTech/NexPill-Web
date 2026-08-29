@extends('admin.layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb', 'Manage all registered users')

@section('content')

{{-- Filters --}}
<div class="mb-5 rounded-2xl border border-[#dde8e5] bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#60716e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                    class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] py-2.5 pl-9 pr-4 text-sm text-[#102a2a] placeholder-[#a0b5b1] focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
            </div>
        </div>
        <select name="role" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm text-[#102a2a] focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
            <option value="">All Roles</option>
            <option value="user"    {{ request('role') === 'user'    ? 'selected' : '' }}>User</option>
            <option value="support" {{ request('role') === 'support' ? 'selected' : '' }}>Support</option>
            <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
        </select>
        <select name="status" class="rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm text-[#102a2a] focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="deleted"  {{ request('status') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
        </select>
        <button type="submit" class="rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">Filter</button>
        @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-4 py-2.5 text-sm text-[#60716e] hover:bg-[#f6faf8] transition-colors">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between border-b border-[#dde8e5] px-6 py-4">
        <p class="text-sm font-semibold text-[#102a2a]">{{ $users->total() }} users found</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Medicines</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Joined</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#60716e]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($users as $user)
                    <tr class="hover:bg-[#f9fcfb] transition-colors {{ $user->trashed() ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e4f5f1] text-xs font-bold text-[#0f806f]">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-[#102a2a] truncate max-w-48">{{ $user->name }}</p>
                                    <p class="text-xs text-[#60716e] truncate max-w-48">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold
                                {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role->value === 'support' ? 'bg-blue-100 text-blue-700' : 'bg-[#e4f5f1] text-[#0f806f]') }}">
                                {{ ucfirst($user->role->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($user->trashed())
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-bold text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Deleted
                                </span>
                            @elseif(!$user->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Inactive
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#e4f5f1] px-2.5 py-1 text-[11px] font-bold text-[#0f806f]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#0f806f]"></span> Active
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-[#102a2a]">{{ $user->medicines_count ?? 0 }}</td>
                        <td class="px-4 py-4 text-xs text-[#60716e]">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.users.show', $user->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-[#dde8e5] px-3 py-1.5 text-xs font-semibold text-[#102a2a] hover:bg-[#f6faf8] transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <svg class="mx-auto h-10 w-10 text-[#a8ded1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="mt-3 text-sm text-[#60716e]">No users found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
