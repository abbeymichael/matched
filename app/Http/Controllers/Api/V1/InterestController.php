<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Social\RegisterInterest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function store(Request $request, string $userId, RegisterInterest $action): JsonResponse
    {
        $target = User::findOrFail($userId);
        $match = $action->handle($request->user(), $target);

        return response()->json(['mutual_match' => (bool) $match]);
    }
}
