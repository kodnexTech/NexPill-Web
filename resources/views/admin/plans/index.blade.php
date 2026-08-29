@extends('admin.layouts.admin')
@section('title', 'Plans') @section('page-title', 'Subscription Plans') @section('breadcrumb', 'Manage pricing plans')
@section('content')

<div class="mb-5 flex justify-end">
    <a href="{{ route('admin.plans.create') }}" class="flex items-center gap-2 rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors shadow-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Plan
    </a>
</div>

<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($plans as $plan)
        <div class="relative rounded-2xl border border-[#dde8e5] bg-white p-6 shadow-sm {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#60716e]">{{ $plan->billing_period }}</p>
                    <h3 class="mt-1 text-lg font-bold text-[#102a2a]">{{ $plan->name }}</h3>
                    <p class="mt-1 text-2xl font-bold text-[#0f806f]">{{ number_format($plan->price_minor / 100, 2) }}
                        <span class="text-sm font-semibold text-[#60716e]">{{ $plan->currency }}</span>
                    </p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $plan->is_active ? 'bg-[#e4f5f1] text-[#0f806f]' : 'bg-gray-100 text-gray-500' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            @if($plan->features)
                <ul class="mt-4 space-y-1.5">
                    @foreach($plan->features as $feature)
                        <li class="flex items-center gap-2 text-xs text-[#60716e]">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-[#e4f5f1] text-[#0f806f]">✓</span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-4 pt-4 border-t border-[#dde8e5] flex items-center justify-between">
                <p class="text-xs text-[#60716e]"><span class="font-bold text-[#102a2a]">{{ $plan->subscriptions_count }}</span> subscriptions</p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="rounded-lg border border-[#dde8e5] px-3 py-1.5 text-xs font-semibold text-[#102a2a] hover:bg-[#f6faf8] transition-colors">Edit</a>
                    <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 rounded-2xl border border-[#dde8e5] bg-white px-6 py-16 text-center">
            <p class="text-sm text-[#60716e]">No plans yet. <a href="{{ route('admin.plans.create') }}" class="text-[#0f806f] font-semibold hover:underline">Create one →</a></p>
        </div>
    @endforelse
</div>
@endsection
