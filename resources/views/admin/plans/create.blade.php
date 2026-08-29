@extends('admin.layouts.admin')
@section('title', 'Create Plan') @section('page-title', 'Create Plan') @section('breadcrumb', 'Plans / New')
@section('content')

<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-[#dde8e5] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Plan Name <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="e.g. Pro Plan">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Slug <span class="text-red-500">*</span></label>
                    <input name="slug" value="{{ old('slug') }}" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm font-mono focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="pro-plan">
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Billing Period <span class="text-red-500">*</span></label>
                    <select name="billing_period" class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none">
                        @foreach(['month','year','lifetime'] as $bp)
                            <option value="{{ $bp }}" {{ old('billing_period') === $bp ? 'selected' : '' }}>{{ ucfirst($bp) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Price (in paise/cents) <span class="text-red-500">*</span></label>
                    <input name="price_minor" type="number" value="{{ old('price_minor', 0) }}" min="0" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="e.g. 49900 = ₹499">
                    <p class="mt-1 text-xs text-[#60716e]">In minor units (e.g. 49900 = ₹499.00)</p>
                    @error('price_minor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Currency <span class="text-red-500">*</span></label>
                    <input name="currency" value="{{ old('currency', 'INR') }}" maxlength="3" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="INR">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Features (one per line)</label>
                    <textarea name="features" rows="5"
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15 resize-none"
                        placeholder="Unlimited medicines&#10;Family support&#10;PDF export">{{ old('features') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-[#dde8e5] text-[#0f806f] focus:ring-[#0f806f]/20">
                    <label for="is_active" class="text-sm font-medium text-[#102a2a]">Active (visible to users)</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#063f3a] px-6 py-3 text-sm font-bold text-white hover:bg-[#0b514a] transition-colors shadow-sm">
                    Create Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-5 py-3 text-sm font-semibold text-[#60716e] hover:bg-[#f6faf8] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
