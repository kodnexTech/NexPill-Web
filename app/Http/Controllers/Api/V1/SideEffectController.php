<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\SideEffectLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SideEffectController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->ok(SideEffectLog::where('user_id', $request->user()->id)->with('medicine')->latest('experienced_at')->paginate(min(100, max(1, $request->integer('per_page', 30)))));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'medicine_id' => ['nullable', 'uuid'], 'dose_log_id' => ['nullable', 'uuid'],
            'symptoms' => ['required', 'array', 'min:1'], 'symptoms.*' => ['string', 'max:120'],
            'severity' => ['required', 'in:mild,moderate,severe'], 'experienced_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        if (! empty($data['medicine_id']) && ! Medicine::where('user_id', $request->user()->id)->whereKey($data['medicine_id'])->exists()) {
            abort(422, 'Invalid medicine.');
        }
        if (! empty($data['dose_log_id']) && ! DoseLog::where('user_id', $request->user()->id)->whereKey($data['dose_log_id'])->exists()) {
            abort(422, 'Invalid dose log.');
        }
        $data['user_id'] = $request->user()->id;

        return $this->ok(SideEffectLog::create($data), 'Side effect logged', 201);
    }

    public function destroy(Request $request, string $sideEffect): JsonResponse
    {
        SideEffectLog::where('user_id', $request->user()->id)->findOrFail($sideEffect)->delete();

        return $this->ok(null, 'Side effect log deleted');
    }
}
