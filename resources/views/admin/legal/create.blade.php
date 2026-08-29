@extends('admin.layouts.admin')
@section('title', 'Create Legal Document') @section('page-title', 'New Legal Document') @section('breadcrumb', 'Legal / Create')
@section('content')

<div class="mx-auto max-w-3xl">
    <div class="rounded-2xl border border-[#dde8e5] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.legal.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Type <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none">
                        <option value="privacy" {{ old('type') === 'privacy' ? 'selected' : '' }}>Privacy Policy</option>
                        <option value="terms"   {{ old('type') === 'terms'   ? 'selected' : '' }}>Terms of Service</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Version <span class="text-red-500">*</span></label>
                    <input name="version" value="{{ old('version') }}" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm font-mono focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="v1.0">
                    @error('version') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Title <span class="text-red-500">*</span></label>
                    <input name="title" value="{{ old('title') }}" required
                        class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15"
                        placeholder="Privacy Policy">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Content (HTML or plain text) <span class="text-red-500">*</span></label>
                <textarea name="content" rows="20" required
                    class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm font-mono focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15 resize-y">{{ old('content') }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#063f3a] px-6 py-3 text-sm font-bold text-white hover:bg-[#0b514a] transition-colors shadow-sm">
                    Create Document
                </button>
                <a href="{{ route('admin.legal.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-5 py-3 text-sm font-semibold text-[#60716e] hover:bg-[#f6faf8] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
