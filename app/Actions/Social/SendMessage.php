<?php

namespace App\Actions\Social;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\MutualMatch;
use App\Models\User;
use App\Services\ModerationService;
use Illuminate\Validation\ValidationException;

/**
 * Send a message inside a mutual match.
 *
 * Moderation is applied synchronously before storage:
 *   - Severe triggers: message is stored but not delivered (delivered=false) and not broadcast.
 *   - Mild / contact triggers: message is delivered but flagged for review.
 *
 * After delivery, the message is broadcast to the private match channel.
 */
final class SendMessage
{
    public function __construct(private readonly ModerationService $moderation) {}

    public function handle(User $sender, MutualMatch $match, string $body): Message
    {
        if (! $match->includesUser($sender->id)) {
            throw ValidationException::withMessages(['match' => 'You are not a participant in this match.']);
        }

        if (! $match->is_active) {
            throw ValidationException::withMessages(['match' => 'This match is no longer active.']);
        }

        if ($sender->isBannedOrSuspended()) {
            throw ValidationException::withMessages(['sender' => 'Your account is restricted.']);
        }

        $body = trim($body);

        if (mb_strlen($body) === 0) {
            throw ValidationException::withMessages(['body' => 'Message cannot be empty.']);
        }

        if (mb_strlen($body) > 2000) {
            throw ValidationException::withMessages(['body' => 'Message is too long.']);
        }

        $check = $this->moderation->checkMessage($body);

        $message = Message::create([
            'match_id' => $match->id,
            'sender_id' => $sender->id,
            'body' => $body,
            'flagged' => $check['flagged'],
            'flag_reason' => $check['flagged'] ? implode(', ', $check['reasons']) : null,
            'delivered' => ! $check['severe'],
            'sent_at' => now(),
        ]);

        if ($message->delivered) {
            broadcast(new MessageSent($message))->toOthers();
        }

        return $message;
    }
}
