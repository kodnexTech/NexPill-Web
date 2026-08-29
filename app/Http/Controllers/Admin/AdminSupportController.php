<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::withCount('messages')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        $counts = [
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'closed'      => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'counts'));
    }

    public function show(string $id): View
    {
        $ticket   = SupportTicket::with('messages')->findOrFail($id);

        return view('admin.support.show', compact('ticket'));
    }

    public function reply(Request $request, string $id): RedirectResponse
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate(['message' => ['required', 'string', 'max:10000']]);

        $ticket->messages()->create([
            'message'        => $request->message,
            'sender_id'      => Auth::id(),
            'is_staff_reply' => true,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'status'   => ['sometimes', 'in:open,in_progress,resolved,closed'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
        ]);

        $ticket->update($request->only(['status', 'priority']));

        return back()->with('success', 'Ticket updated.');
    }
}
