@extends('admin.layouts.admin')
@section('title', $medicine->name) @section('page-title', $medicine->name) @section('breadcrumb', 'Medicines / ' . $medicine->name)
@section('content')

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4">
        <div class="rounded-2xl border border-[#DCE6F2] bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E5F2FF] text-2xl mb-4">💊</div>
            <h2 class="text-xl font-bold text-[#10233F]">{{ $medicine->name }}</h2>
            @if($medicine->strength) <p class="text-sm text-[#5B6D86] mt-1">{{ $medicine->strength }} {{ $medicine->unit }}</p> @endif

            <div class="mt-4 space-y-2 text-sm">
                @foreach(['Form' => ucfirst($medicine->form), 'Color' => ($medicine->color ?? '—'), 'Instructions' => ($medicine->instructions ?? '—'), 'Notes' => ($medicine->notes ?? '—')] as $label => $value)
                    <div class="flex justify-between rounded-xl bg-[#F4FAFF] px-4 py-2.5">
                        <span class="text-[#5B6D86]">{{ $label }}</span>
                        <span class="font-semibold text-[#10233F] text-right max-w-32 truncate">{{ $value }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between rounded-xl bg-[#F4FAFF] px-4 py-2.5">
                    <span class="text-[#5B6D86]">Owner</span>
                    <a href="{{ route('admin.users.show', $medicine->user_id) }}" class="font-semibold text-[#075DE7] hover:underline">{{ $medicine->user->name ?? '—' }}</a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
            <h3 class="text-sm font-bold text-[#10233F] mb-3">Inventory</h3>
            @if($medicine->inventory_total !== null)
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#10233F]">{{ $medicine->inventory_remaining }}<span class="text-lg text-[#5B6D86]">/{{ $medicine->inventory_total }}</span></p>
                    <p class="text-xs text-[#5B6D86] mt-1">doses remaining</p>
                    @php $invPct = $medicine->inventory_total > 0 ? ($medicine->inventory_remaining / $medicine->inventory_total) * 100 : 0; @endphp
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#E5F2FF]">
                        <div class="h-full rounded-full {{ $invPct < 20 ? 'bg-[#00BFA6]' : 'bg-[#075DE7]' }}" style="width: {{ $invPct }}%"></div>
                    </div>
                    @if($medicine->refill_threshold) <p class="mt-2 text-xs text-[#5B6D86]">Refill alert at {{ $medicine->refill_threshold }} doses</p> @endif
                </div>
            @else
                <p class="text-sm text-[#5B6D86] text-center">Not tracked</p>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm">
            <div class="border-b border-[#DCE6F2] px-6 py-4">
                <h3 class="text-sm font-bold text-[#10233F]">Dose Logs (last 20)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Scheduled</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Taken At</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF5FD]">
                        @forelse($doseLogs as $log)
                            @php $sc = ['taken'=>'bg-[#E5F2FF] text-[#075DE7]','missed'=>'bg-red-100 text-red-700','skipped'=>'bg-amber-100 text-amber-700','scheduled'=>'bg-blue-100 text-blue-700','snoozed'=>'bg-purple-100 text-purple-700']; @endphp
                            <tr class="hover:bg-[#FAFCFF]">
                                <td class="px-6 py-3 text-[#10233F]">{{ $log->scheduled_for->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3 text-[#5B6D86] text-xs">{{ $log->taken_at ? $log->taken_at->format('h:i A') : '—' }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sc[$log->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($log->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-[#5B6D86]">No dose logs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($medicine->schedules->count())
            <div class="rounded-2xl border border-[#DCE6F2] bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-bold text-[#10233F]">Schedules</h3>
                <div class="space-y-2">
                    @foreach($medicine->schedules as $sched)
                        <div class="rounded-xl bg-[#F4FAFF] p-3 text-xs">
                            <span class="font-bold text-[#10233F]">{{ ucfirst($sched->type) }}</span> ·
                            <span class="text-[#5B6D86]">{{ implode(', ', json_decode($sched->times ?? '[]', true)) }}</span> ·
                            From {{ $sched->starts_on }} {{ $sched->ends_on ? 'to ' . $sched->ends_on : '' }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
