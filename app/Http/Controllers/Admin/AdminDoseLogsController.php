<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoseLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDoseLogsController extends Controller
{
    public function index(Request $request): View
    {
        $query = DoseLog::with(['user', 'medicine'])->latest('scheduled_for');

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_for', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_for', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $statusCounts = DoseLog::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dose-logs.index', compact('logs', 'statusCounts'));
    }
}
