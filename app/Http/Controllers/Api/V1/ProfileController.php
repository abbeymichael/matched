<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Onboarding\SaveProfileStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProfileStepRequest;
use App\Http\Resources\FieldDefinitionResource;
use App\Http\Resources\ProfileResource;
use App\Models\FieldDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/v1/fields — server-driven onboarding (§11.4). Active fields
     * ordered by sort_order, with their options, so a mobile client renders
     * the wizard without any hardcoded field list in the app binary.
     */
    public function fields(): JsonResponse
    {
        $fields = FieldDefinition::where('is_active', true)
            ->where('is_core', false)
            ->with('options')
            ->orderBy('sort_order')
            ->get();

        return response()->json(FieldDefinitionResource::collection($fields));
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(new ProfileResource($request->user()->profile));
    }

    public function store(SaveProfileStepRequest $request, SaveProfileStep $action): JsonResponse
    {
        $action->handle($request->user(), $request->validated());

        return response()->json(new ProfileResource($request->user()->profile()->first()));
    }
}
