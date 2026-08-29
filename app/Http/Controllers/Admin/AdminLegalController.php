<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLegalController extends Controller
{
    public function index(): View
    {
        $documents = LegalDocument::latest()->paginate(20);

        return view('admin.legal.index', compact('documents'));
    }

    public function create(): View
    {
        return view('admin.legal.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'    => ['required', 'in:privacy,terms'],
            'version' => ['required', 'string', 'max:32'],
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        LegalDocument::create($validated);

        return redirect()->route('admin.legal.index')->with('success', 'Legal document created.');
    }

    public function edit(LegalDocument $legal): View
    {
        return view('admin.legal.edit', compact('legal'));
    }

    public function update(Request $request, LegalDocument $legal): RedirectResponse
    {
        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $legal->update($validated);

        return redirect()->route('admin.legal.index')->with('success', 'Document updated.');
    }

    public function destroy(LegalDocument $legal): RedirectResponse
    {
        $legal->delete();

        return redirect()->route('admin.legal.index')->with('success', 'Document deleted.');
    }

    public function publish(Request $request, LegalDocument $legal): RedirectResponse
    {
        $legal->update([
            'published_at' => $legal->published_at ? null : now(),
        ]);

        $message = $legal->published_at ? 'Document published.' : 'Document unpublished.';

        return back()->with('success', $message);
    }
}
