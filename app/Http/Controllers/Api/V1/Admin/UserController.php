<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Moderation\BanUser;
use App\Actions\Moderation\RestoreUser;
use App\Actions\Moderation\SuspendUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('profile')->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $query->where('phone', 'like', "%{$search}%")
                ->orWhereHas('profile', fn ($q) => $q->where('display_name', 'like', "%{$search}%"));
        }

        return response()->json(UserResource::collection($query->paginate(50)->items()));
    }

    public function update(Request $request, string $user, SuspendUser $suspend, BanUser $ban, RestoreUser $restore): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:suspend,ban,restore,verify'],
            'verification_status' => ['nullable', 'string', 'in:pending,approved,rejected'],
            'suspension_days' => ['nullable', 'integer'],
            'ban_reason' => ['nullable', 'string'],
        ]);

        $target = User::findOrFail($user);

        match ($data['action']) {
            'suspend' => $suspend->handle($target, $data['suspension_days'] ?? 7),
            'ban' => $ban->handle($target, $data['ban_reason'] ?? 'Manual admin ban'),
            'restore' => $restore->handle($target),
            'verify' => $target->forceFill(['verification_status' => $data['verification_status'] ?? 'approved'])->save(),
        };

        return response()->json(new UserResource($target->refresh()));
    }
}
