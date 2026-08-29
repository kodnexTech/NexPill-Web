@extends('admin.layouts.admin')
@section('title', 'Edit Document') @section('page-title', 'Edit Legal Document') @section('breadcrumb', 'Legal / ' . $legal->title)
@section('content')

<div class="mx-auto max-w-3xl">
    <div class="rounded-2xl border border-[#dde8e5] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.legal.update', $legal->id) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="flex items-center gap-3 rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm text-[#60716e]">
                <svg class="h-4 w-4 text-[#0f806f]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><span class="font-bold text-[#102a2a]">{{ ucfirst($legal->type) }}</span> · Version <code class="font-mono font-bold">{{ $legal->version }}</code></span>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Title <span class="text-red-500">*</span></label>
                <input name="title" value="{{ old('title', $legal->title) }}" required
                    class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#102a2a]">Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="20" required
                    class="w-full rounded-xl border border-[#dde8e5] bg-[#f6faf8] px-4 py-3 text-sm font-mono focus:border-[#0f806f] focus:outline-none focus:ring-2 focus:ring-[#0f806f]/15 resize-y">{{ old('content', $legal->content) }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#063f3a] px-6 py-3 text-sm font-bold text-white hover:bg-[#0b514a] transition-colors shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.legal.index') }}" class="flex items-center rounded-xl border border-[#dde8e5] px-5 py-3 text-sm font-semibold text-[#60716e] hover:bg-[#f6faf8] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
