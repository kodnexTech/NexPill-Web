<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('actor')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->action.'%');
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();
        $admins = User::whereIn('role', ['admin', 'support'])->get();

        return view('admin.audit-logs.index', compact('logs', 'admins'));
    }
}
