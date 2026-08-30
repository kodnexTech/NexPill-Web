@extends('admin.layouts.admin')
@section('title', 'Edit Plan') @section('page-title', 'Edit Plan') @section('breadcrumb', 'Plans / ' . $plan->name)
@section('content')

<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-[#DCE6F2] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Plan Name <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name', $plan->name) }}" required
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Slug <span class="text-red-500">*</span></label>
                    <input name="slug" value="{{ old('slug', $plan->slug) }}" required
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm font-mono focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Billing Period <span class="text-red-500">*</span></label>
                    <select name="billing_period" class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none">
                        @foreach(['month','year','lifetime'] as $bp)
                            <option value="{{ $bp }}" {{ old('billing_period', $plan->billing_period) === $bp ? 'selected' : '' }}>{{ ucfirst($bp) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Price (minor units) <span class="text-red-500">*</span></label>
                    <input name="price_minor" type="number" value="{{ old('price_minor', $plan->price_minor) }}" min="0" required
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
                    <p class="mt-1 text-xs text-[#5B6D86]">Current: {{ number_format($plan->price_minor / 100, 2) }} {{ $plan->currency }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Currency <span class="text-red-500">*</span></label>
                    <input name="currency" value="{{ old('currency', $plan->currency) }}" maxlength="3" required
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Features (one per line)</label>
                    <textarea name="features" rows="5"
                        class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15 resize-none">{{ old('features', $plan->features ? implode("\n", $plan->features) : '') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-[#DCE6F2] text-[#075DE7] focus:ring-[#075DE7]/20">
                    <label for="is_active" class="text-sm font-medium text-[#10233F]">Active</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#073B9A] px-6 py-3 text-sm font-bold text-white hover:bg-[#0A4FC2] transition-colors shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.plans.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-5 py-3 text-sm font-semibold text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
