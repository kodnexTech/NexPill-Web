@extends('admin.layouts.admin')
@section('title', $medicine->name) @section('page-title', $medicine->name) @section('breadcrumb', 'Medicines / ' . $medicine->name)
@section('content')

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4">
        <div class="rounded-2xl border border-[#dde8e5] bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e4f5f1] text-2xl mb-4">💊</div>
            <h2 class="text-xl font-bold text-[#102a2a]">{{ $medicine->name }}</h2>
            @if($medicine->strength) <p class="text-sm text-[#60716e] mt-1">{{ $medicine->strength }} {{ $medicine->unit }}</p> @endif

            <div class="mt-4 space-y-2 text-sm">
                @foreach(['Form' => ucfirst($medicine->form), 'Color' => ($medicine->color ?? '—'), 'Instructions' => ($medicine->instructions ?? '—'), 'Notes' => ($medicine->notes ?? '—')] as $label => $value)
                    <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                        <span class="text-[#60716e]">{{ $label }}</span>
                        <span class="font-semibold text-[#102a2a] text-right max-w-32 truncate">{{ $value }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between rounded-xl bg-[#f6faf8] px-4 py-2.5">
                    <span class="text-[#60716e]">Owner</span>
                    <a href="{{ route('admin.users.show', $medicine->user_id) }}" class="font-semibold text-[#0f806f] hover:underline">{{ $medicine->user->name ?? '—' }}</a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-[#dde8e5] bg-white p-5 shadow-sm">
            <h3 class="text-sm font-bold text-[#102a2a] mb-3">Inventory</h3>
            @if($medicine->inventory_total !== null)
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#102a2a]">{{ $medicine->inventory_remaining }}<span class="text-lg text-[#60716e]">/{{ $medicine->inventory_total }}</span></p>
                    <p class="text-xs text-[#60716e] mt-1">doses remaining</p>
                    @php $invPct = $medicine->inventory_total > 0 ? ($medicine->inventory_remaining / $medicine->inventory_total) * 100 : 0; @endphp
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#e4f5f1]">
                        <div class="h-full rounded-full {{ $invPct < 20 ? 'bg-[#f57863]' : 'bg-[#0f806f]' }}" style="width: {{ $invPct }}%"></div>
                    </div>
                    @if($medicine->refill_threshold) <p class="mt-2 text-xs text-[#60716e]">Refill alert at {{ $medicine->refill_threshold }} doses</p> @endif
                </div>
            @else
                <p class="text-sm text-[#60716e] text-center">Not tracked</p>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm">
            <div class="border-b border-[#dde8e5] px-6 py-4">
                <h3 class="text-sm font-bold text-[#102a2a]">Dose Logs (last 20)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Scheduled</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Taken At</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0f6f4]">
                        @forelse($doseLogs as $log)
                            @php $sc = ['taken'=>'bg-[#e4f5f1] text-[#0f806f]','missed'=>'bg-red-100 text-red-700','skipped'=>'bg-amber-100 text-amber-700','scheduled'=>'bg-blue-100 text-blue-700','snoozed'=>'bg-purple-100 text-purple-700']; @endphp
                            <tr class="hover:bg-[#f9fcfb]">
                                <td class="px-6 py-3 text-[#102a2a]">{{ $log->scheduled_for->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3 text-[#60716e] text-xs">{{ $log->taken_at ? $log->taken_at->format('h:i A') : '—' }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$log->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-[#60716e]">No dose logs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($medicine->schedules->count())
            <div class="rounded-2xl border border-[#dde8e5] bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-[#102a2a]">Schedules</h3>
                <div class="space-y-2">
                    @foreach($medicine->schedules as $sched)
                        <div class="rounded-xl bg-[#f6faf8] p-3 text-xs">
                            <span class="font-bold text-[#102a2a]">{{ ucfirst($sched->type) }}</span> ·
                            <span class="text-[#60716e]">{{ implode(', ', json_decode($sched->times ?? '[]', true)) }}</span> ·
                            From {{ $sched->starts_on }} {{ $sched->ends_on ? 'to ' . $sched->ends_on : '' }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
