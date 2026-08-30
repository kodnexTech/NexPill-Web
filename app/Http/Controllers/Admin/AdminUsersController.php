<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
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
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
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
        $medicines = $user->medicines()->withCount('doseLogs')->latest()->limit(10)->get();
        $doseLogs = $user->doseLogs()->with('medicine')->latest()->limit(15)->get();
        $tickets = SupportTicket::where('email', $user->email)->latest()->limit(5)->get();
        $subscriptions = $user->subscriptions()->with('plan')->latest()->get();

        return view('admin.users.show', compact('user', 'medicines', 'doseLogs', 'tickets', 'subscriptions'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'role' => ['sometimes', 'in:user,support,admin'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $selfLockout = $request->user()?->is($user) && (
            ($request->has('role') && $request->string('role')->toString() !== UserRole::Admin->value)
            || ($request->has('is_active') && ! $request->boolean('is_active'))
            || $request->has('soft_delete')
        );
        if ($selfLockout) {
            return back()->with('error', 'You cannot remove your own admin access or deactivate your current account.');
        }

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
        if (! $user->is_active || $user->trashed()) {
            $user->tokens()->delete();
            $user->deviceTokens()->delete();
        }

        return back()->with('success', 'User updated successfully.');
    }
}
