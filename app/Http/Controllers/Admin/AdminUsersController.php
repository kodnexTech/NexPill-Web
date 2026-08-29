<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount(['medicines', 'doseLogs', 'appointments'])
            ->withTrashed()
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'deleted') {
                $query->whereNotNull('deleted_at');
            }
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(string $id): View
    {
        $user = User::withTrashed()->findOrFail($id);
        $medicines  = $user->medicines()->withCount('doseLogs')->latest()->limit(10)->get();
        $doseLogs   = $user->doseLogs()->with('medicine')->latest()->limit(15)->get();
        $tickets    = \App\Models\SupportTicket::where('email', $user->email)->latest()->limit(5)->get();
        $subscriptions = $user->subscriptions()->with('plan')->latest()->get();

        return view('admin.users.show', compact('user', 'medicines', 'doseLogs', 'tickets', 'subscriptions'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'role'      => ['sometimes', 'in:user,support,admin'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('role')) {
            $user->role = UserRole::from($request->role);
        }

        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
        }

        if ($request->has('restore') && $user->trashed()) {
            $user->restore();
        }

        if ($request->has('soft_delete') && ! $user->trashed()) {
            $user->delete();
        }

        $user->save();

        return back()->with('success', 'User updated successfully.');
    }
}
