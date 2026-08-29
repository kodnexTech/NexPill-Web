@extends('admin.layouts.admin')

@section('title', $user->name . ' — User')
@section('page-title', $user->name)
@section('breadcrumb', 'Users / ' . $user->name)

@section('content')

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Profile Card --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="rounded-2xl border border-[#dde8e5] bg-white p-6 shadow-sm">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#e4f5f1] text-2xl font-bold text-[#0f806f]">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <h2 class="mt-3 text-lg font-bold text-[#102a2a]">{{ $user->name }}</h2>
                <p class="text-sm text-[#60716e]">{{ $user->email }}</p>
                <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold
                        {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role->value === 'support' ? 'bg-blue-100 text-blue-700' : 'bg-[#e4f5f1] text-[#0f806f]') }}">
                        {{ ucfirst($user->role->value) }}
                    </span>
                    @if($user->trashed())
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-bold text-red-700">Deleted</span>
                    @elseif(!$user->is_active)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">Inactive</span>
                    @else
                        <span class="rounded-full bg-[#e4f5f1] px-2.5 py-1 text-[11px] font-bold text-[#0f806f]">Active</span>
                    @endif
                </div>
            </div>

            <div class="mt-5 space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                    <span class="text-[#60716e]">Joined</span>
                    <span class="font-semibold text-[#102a2a]">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                    <span class="text-[#60716e]">Last Seen</span>
                    <span class="font-semibold text-[#102a2a]">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                    <span class="text-[#60716e]">Medicines</span>
                    <span class="font-semibold text-[#102a2a]">{{ $medicines->count() }}</span>
                </div>
                <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                    <span class="text-[#60716e]">Dose Logs</span>
                    <span class="font-semibold text-[#102a2a]">{{ $doseLogs->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="rounded-2xl border border-[#dde8e5] bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-bold text-[#102a2a]">Quick Actions</h3>
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-3">
                @csrf @method('PATCH')

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[#60716e]">Change Role</label>
                    <select name="role" class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-3 py-2.5 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
                        <option value="user"    {{ $user->role->value === 'user'    ? 'selected' : '' }}>User</option>
                        <option value="support" {{ $user->role->value === 'support' ? 'selected' : '' }}>Support</option>
                        <option value="admin"   {{ $user->role->value === 'admin'   ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-[#dde8e5] text-[#0f806f] focus:ring-[#0f806f]/20">
                    <label for="is_active" class="text-sm font-medium text-[#102a2a]">Account Active</label>
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#063f3a] py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors">
                    Save Changes
                </button>
            </form>

            @if($user->trashed())
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="mt-2">
                    @csrf @method('PATCH')
                    <input type="hidden" name="restore" value="1">
                    <button type="submit" class="w-full rounded-xl border border-[#0f806f] py-2.5 text-sm font-semibold text-[#0f806f] hover:bg-[#e4f5f1] transition-colors">
                        Restore User
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="mt-2">
                    @csrf @method('PATCH')
                    <input type="hidden" name="soft_delete" value="1">
                    <button type="submit" class="w-full rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors"
                            onclick="return confirm('Soft delete this user?')">
                        Delete User
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Right Content --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Medicines --}}
        <div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm">
            <div class="border-b border-[#dde8e5] px-6 py-4">
                <h3 class="text-sm font-bold text-[#102a2a]">Medicines ({{ $medicines->count() }})</h3>
            </div>
            <div class="divide-y divide-[#f0f6f4]">
                @forelse($medicines as $med)
                    <div class="flex items-center gap-3 px-6 py-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#e4f5f1] text-sm">💊</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-[#102a2a]">{{ $med->name }}</p>
                            <p class="text-xs text-[#60716e]">{{ $med->form }} · {{ $med->dose_logs_count }} dose logs</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $med->is_paused ? 'bg-amber-100 text-amber-700' : 'bg-[#e4f5f1] text-[#0f806f]' }}">
                            {{ $med->is_paused ? 'Paused' : 'Active' }}
                        </span>
                    </div>
                @empty
                    <p class="px-6 py-6 text-center text-sm text-[#60716e]">No medicines.</p>
                @endforelse
            </div>
        </div>

        {{-- Dose Logs --}}
        <div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm">
            <div class="border-b border-[#dde8e5] px-6 py-4">
                <h3 class="text-sm font-bold text-[#102a2a]">Recent Dose Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Medicine</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Scheduled</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0f6f4]">
                        @forelse($doseLogs as $log)
                            <tr class="hover:bg-[#f9fcfb]">
                                <td class="px-6 py-3 font-medium text-[#102a2a]">{{ $log->medicine->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-[#60716e]">{{ $log->scheduled_for->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = ['taken' => 'bg-[#e4f5f1] text-[#0f806f]', 'missed' => 'bg-red-100 text-red-700', 'skipped' => 'bg-amber-100 text-amber-700', 'scheduled' => 'bg-blue-100 text-blue-700', 'snoozed' => 'bg-purple-100 text-purple-700'];
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-6 text-center text-sm text-[#60716e]">No dose logs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Support Tickets --}}
        @if($tickets->count())
            <div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm">
                <div class="border-b border-[#dde8e5] px-6 py-4">
                    <h3 class="text-sm font-bold text-[#102a2a]">Support Tickets</h3>
                </div>
                <div class="divide-y divide-[#f0f6f4]">
                    @foreach($tickets as $ticket)
                        <a href="{{ route('admin.support.show', $ticket->id) }}" class="flex items-center gap-3 px-6 py-3 hover:bg-[#f6faf8]">
                            <span class="text-sm font-medium text-[#102a2a] flex-1">{{ $ticket->subject }}</span>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $ticket->status === 'open' ? 'bg-[#ffeae5] text-[#f57863]' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

@endsection
