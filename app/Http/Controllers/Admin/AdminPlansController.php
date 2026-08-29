<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPlansController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->latest()->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'slug'           => ['required', 'string', 'max:60', 'unique:plans,slug', 'alpha_dash'],
            'price_minor'    => ['required', 'integer', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'billing_period' => ['required', 'in:month,year,lifetime'],
            'features'       => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $validated['features'] = $request->features
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : null;
        $validated['is_active'] = $request->boolean('is_active');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'slug'           => ['required', 'string', 'max:60', 'alpha_dash', 'unique:plans,slug,' . $plan->id],
            'price_minor'    => ['required', 'integer', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'billing_period' => ['required', 'in:month,year,lifetime'],
            'features'       => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $validated['features'] = $request->features
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : null;
        $validated['is_active'] = $request->boolean('is_active');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }
}
