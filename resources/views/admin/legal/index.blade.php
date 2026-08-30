@extends('admin.layouts.admin')
@section('title', 'Legal Documents') @section('page-title', 'Legal Documents') @section('breadcrumb', 'Privacy Policy & Terms of Service')
@section('content')

<div class="mb-5 flex justify-end">
    <a href="{{ route('admin.legal.create') }}" class="flex items-center gap-2 rounded-xl bg-[#073B9A] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0A4FC2] transition-colors shadow-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Document
    </a>
</div>

<div class="rounded-2xl border border-[#DCE6F2] bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#DCE6F2] bg-[#F4FAFF]">
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Document</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Version</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Published</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-[#5B6D86]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EDF5FD]">
                @forelse($documents as $doc)
                    <tr class="hover:bg-[#FAFCFF] transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#10233F]">{{ $doc->title }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $doc->type === 'privacy' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ ucfirst($doc->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-mono text-xs text-[#5B6D86]">{{ $doc->version }}</td>
                        <td class="px-4 py-4">
                            @if($doc->published_at)
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#E5F2FF] px-2.5 py-1 text-[11px] font-bold text-[#075DE7]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#075DE7]"></span> Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs text-[#5B6D86]">{{ $doc->published_at ? $doc->published_at->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.legal.publish', $doc->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ $doc->published_at ? 'border border-amber-200 text-amber-700 hover:bg-amber-50' : 'border border-[#075DE7] text-[#075DE7] hover:bg-[#E5F2FF]' }}">
                                        {{ $doc->published_at ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.legal.edit', $doc->id) }}" class="rounded-lg border border-[#DCE6F2] px-3 py-1.5 text-xs font-semibold text-[#10233F] hover:bg-[#F4FAFF] transition-colors">Edit</a>
                                <form method="POST" action="{{ route('admin.legal.destroy', $doc->id) }}" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-[#5B6D86]">No legal documents yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
        <div class="border-t border-[#DCE6F2] px-6 py-4">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
