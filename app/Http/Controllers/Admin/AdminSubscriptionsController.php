<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscription::with(['user', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $plans = Plan::all();
        $revenue = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_minor');

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'revenue'));
    }
}
