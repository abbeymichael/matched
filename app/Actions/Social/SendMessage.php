<?php

namespace App\Actions\Social;

use App\Models\Message;
use App\Models\MutualMatch;
use App\Models\User;
use App\Services\ModerationService;
use Illuminate\Validation\ValidationException;

/**
 * Creates a Message, runs the outgoing moderation check (§12.4), and
 * escalates to under_review after repeated flags (§12.4 escalation rule).
 * Gated on an existing, active MutualMatch — chat is never open to non-matches.
 */
final class SendMessage
{
    public function __construct(private readonly ModerationService $moderation) {}

    public function handle(MutualMatch $match, User $sender, string $body): Message
    {
        if (! $match->includesUser($sender->id) || ! $match->is_active) {
            throw ValidationException::withMessages(['match' => 'You can only message mutual matches.']);
        }

        if ($sender->isBannedOrSuspended()) {
            throw ValidationException::withMessages(['sender' => 'Your account cannot send messages right now.']);
        }

        $result = $this->moderation->check($body);

        $message = Message::create([
            'match_id' => $match->id,
            'sender_id' => $sender->id,
            'body' => $body,
            'flagged' => $result->flagged,
            'flag_reason' => $result->reason,
            'delivered' => $result->deliver,
            'sent_at' => now(),
        ]);

        if ($result->flagged) {
            $flaggedCount = Message::where('sender_id', $sender->id)->where('flagged', true)->count();
            $threshold = config('moderation.auto_suspend_after_flags', 3);

            if ($flaggedCount >= $threshold && ! $sender->isBannedOrSuspended()) {
                $sender->forceFill(['status' => \App\Enums\UserStatus::UnderReview->value])->save();
            }
        }

        event(new \App\Events\MessageSent($message));

        return $message;
    }
}
