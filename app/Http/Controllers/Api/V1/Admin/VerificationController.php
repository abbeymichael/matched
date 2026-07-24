<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::where('verification_status', 'pending')
            ->with('photos')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json(UserResource::collection($users->items()));
    }

    public function review(Request $request, string $user): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
        ]);

        $target = User::findOrFail($user);
        $target->forceFill(['verification_status' => $data['status']])->save();

        return response()->json(new UserResource($target));
    }
}
