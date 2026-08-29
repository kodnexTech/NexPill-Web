<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->ok(Plan::where('is_active', true)->orderBy('price_minor')->get());
    }

    public function current(Request $request): JsonResponse
    {
        return $this->ok($request->user()->subscriptions()->with('plan')->latest('starts_at')->first());
    }
}
