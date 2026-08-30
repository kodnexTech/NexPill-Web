@extends('admin.layouts.admin')
@section('title', 'Edit Document') @section('page-title', 'Edit Legal Document') @section('breadcrumb', 'Legal / ' . $legal->title)
@section('content')

<div class="mx-auto max-w-3xl">
    <div class="rounded-2xl border border-[#DCE6F2] bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('admin.legal.update', $legal->id) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="flex items-center gap-3 rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm text-[#5B6D86]">
                <svg class="h-4 w-4 text-[#075DE7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><span class="font-bold text-[#10233F]">{{ ucfirst($legal->type) }}</span> · Version <code class="font-mono font-bold">{{ $legal->version }}</code></span>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Title <span class="text-red-500">*</span></label>
                <input name="title" value="{{ old('title', $legal->title) }}" required
                    class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#10233F]">Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="20" required
                    class="w-full rounded-xl border border-[#DCE6F2] bg-[#F4FAFF] px-4 py-3 text-sm font-mono focus:border-[#075DE7] focus:outline-none focus:ring-2 focus:ring-[#075DE7]/15 resize-y">{{ old('content', $legal->content) }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#073B9A] px-6 py-3 text-sm font-bold text-white hover:bg-[#0A4FC2] transition-colors shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.legal.index') }}" class="flex items-center rounded-xl border border-[#DCE6F2] px-5 py-3 text-sm font-semibold text-[#5B6D86] hover:bg-[#F4FAFF] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
