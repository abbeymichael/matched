<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Onboarding\LockUserProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LockController extends Controller
{
    public function lock(Request $request, LockUserProfile $action): JsonResponse
    {
        $action->handle($request->user());

        return response()->json(['locked' => true]);
    }
}
