<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatchResource;
use App\Http\Resources\UserResource;
use App\Models\FieldDefinition;
use App\Models\MatchScore;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    /**
     * §6 Trigger 2 query: precomputed data only, no live scoring.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $scores = MatchScore::query()
            ->where('viewer_id', $user->id)
            ->where('score', '>=', $user->match_threshold)
            ->where('passed_hard_filters', true)
            ->whereHas('target', function ($query) {
                $query->where('status', 'active')->where('verification_status', 'approved');
            })
            ->with(['target.profile', 'target.photos'])
            ->orderBy('score', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => MatchResource::collection($scores->items()),
            'meta' => ['current_page' => $scores->currentPage(), 'last_page' => $scores->lastPage(), 'total' => $scores->total()],
        ]);
    }

    public function show(Request $request, string $userId): JsonResponse
    {
        $target = User::with(['profile', 'photos', 'profileFieldValues'])->findOrFail($userId);
        $fields = FieldDefinition::where('is_active', true)->where('is_core', false)->orderBy('sort_order')->get();
        $fieldValues = $target->profileFieldValues->keyBy('field_key');

        return response()->json([
            'user' => new UserResource($target),
            'fields' => $fields->map(fn ($field) => [
                'key' => $field->key,
                'label' => $field->label,
                'value' => $fieldValues[$field->key]->value ?? null,
            ]),
        ]);
    }
}
