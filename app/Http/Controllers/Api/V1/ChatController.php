<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Social\SendMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\MutualMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function threads(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = MutualMatch::query()
            ->where(fn ($q) => $q->where('user_a_id', $userId)->orWhere('user_b_id', $userId))
            ->where('is_active', true)
            ->with(['userA.profile', 'userB.profile', 'messages' => fn ($q) => $q->latest('sent_at')->limit(1)])
            ->orderBy('matched_at', 'desc')
            ->get();

        return response()->json($matches->map(fn ($match) => [
            'match_id' => $match->id,
            'other_user' => new \App\Http\Resources\UserResource($match->otherUser($userId)),
            'last_message' => $match->messages->first() ? new MessageResource($match->messages->first()) : null,
        ]));
    }

    public function messages(Request $request, string $matchId): JsonResponse
    {
        $match = MutualMatch::findOrFail($matchId);

        if (! $match->includesUser($request->user()->id)) {
            abort(403);
        }

        $query = $match->messages()->orderBy('sent_at');

        if ($since = $request->query('since')) {
            $sinceMessage = \App\Models\Message::find($since);
            if ($sinceMessage) {
                $query->where('sent_at', '>', $sinceMessage->sent_at);
            }
        }

        return response()->json(MessageResource::collection($query->get()));
    }

    public function send(SendMessageRequest $request, string $matchId, SendMessage $action): JsonResponse
    {
        $match = MutualMatch::findOrFail($matchId);
        $message = $action->handle($match, $request->user(), $request->validated()['body']);

        return response()->json(new MessageResource($message));
    }
}
