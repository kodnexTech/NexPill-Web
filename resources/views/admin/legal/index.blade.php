@extends('admin.layouts.admin')
@section('title', 'Legal Documents') @section('page-title', 'Legal Documents') @section('breadcrumb', 'Privacy Policy & Terms of Service')
@section('content')

<div class="mb-5 flex justify-end">
    <a href="{{ route('admin.legal.create') }}" class="flex items-center gap-2 rounded-xl bg-[#063f3a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b514a] transition-colors shadow-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Document
    </a>
</div>

<div class="rounded-2xl border border-[#dde8e5] bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#dde8e5] bg-[#f6faf8]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Document</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Version</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#60716e]">Published</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#60716e]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f6f4]">
                @forelse($documents as $doc)
                    <tr class="hover:bg-[#f9fcfb] transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#102a2a]">{{ $doc->title }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $doc->type === 'privacy' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ ucfirst($doc->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-mono text-xs text-[#60716e]">{{ $doc->version }}</td>
                        <td class="px-4 py-4">
                            @if($doc->published_at)
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#e4f5f1] px-2.5 py-1 text-[11px] font-bold text-[#0f806f]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#0f806f]"></span> Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs text-[#60716e]">{{ $doc->published_at ? $doc->published_at->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.legal.publish', $doc->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ $doc->published_at ? 'border border-amber-200 text-amber-700 hover:bg-amber-50' : 'border border-[#0f806f] text-[#0f806f] hover:bg-[#e4f5f1]' }}">
                                        {{ $doc->published_at ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.legal.edit', $doc->id) }}" class="rounded-lg border border-[#dde8e5] px-3 py-1.5 text-xs font-semibold text-[#102a2a] hover:bg-[#f6faf8] transition-colors">Edit</a>
                                <form method="POST" action="{{ route('admin.legal.destroy', $doc->id) }}" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-[#60716e]">No legal documents yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
        <div class="border-t border-[#dde8e5] px-6 py-4">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
