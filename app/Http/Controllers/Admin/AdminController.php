<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_medicines' => Medicine::count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
            'total_doses_today' => DoseLog::whereDate('scheduled_for', today())->count(),
            'taken_doses_today' => DoseLog::whereDate('scheduled_for', today())->where('status', 'taken')->count(),
            'active_subs' => Subscription::where('status', 'active')->count(),
            'new_users_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'unread_notifications' => AppNotification::whereNull('read_at')->count(),
        ];

        // 7-day adherence data
        $adherenceChart = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);
            $total = DoseLog::whereDate('scheduled_for', $date)->count();
            $taken = DoseLog::whereDate('scheduled_for', $date)->where('status', 'taken')->count();

            return [
                'date' => $date->format('D'),
                'total' => $total,
                'taken' => $taken,
                'pct' => $total > 0 ? round(($taken / $total) * 100) : 0,
            ];
        })->values();

        $recentUsers = User::latest()->limit(5)->get();
        $recentTickets = SupportTicket::with('messages')->where('status', 'open')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'adherenceChart', 'recentUsers', 'recentTickets'));
    }
}
