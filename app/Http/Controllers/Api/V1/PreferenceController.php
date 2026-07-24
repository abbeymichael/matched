<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Onboarding\SavePreferenceStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\SavePreferenceStepRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $prefs = $request->user()->preferences;

        return response()->json($prefs ? [
            'age_min' => $prefs->age_min,
            'age_max' => $prefs->age_max,
            'accepted_genders' => $prefs->accepted_genders,
            'max_distance_km' => $prefs->max_distance_km,
        ] : []);
    }

    public function store(SavePreferenceStepRequest $request, SavePreferenceStep $action): JsonResponse
    {
        $action->handle($request->user(), $request->validated());

        return response()->json(['saved' => true]);
    }
}
