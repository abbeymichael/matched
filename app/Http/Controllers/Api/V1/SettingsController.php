<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Onboarding\ResetUserProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function reset(Request $request, ResetUserProfile $action): JsonResponse
    {
        $action->handle($request->user());

        return response()->json(['reset' => true]);
    }

    public function threshold(Request $request): JsonResponse
    {
        $request->validate(['threshold' => ['required', 'integer', 'min:0', 'max:100']]);

        $request->user()->forceFill(['match_threshold' => $request->integer('threshold')])->save();

        return response()->json(['match_threshold' => $request->user()->match_threshold]);
    }
}
