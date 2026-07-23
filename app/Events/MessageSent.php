<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast a new message to both participants in a mutual match.
 *
 * Only broadcasts when the message was actually delivered (not held for
 * severe moderation). For the held case, the message stays in the database
 * but is never broadcast.
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        $match = $this->message->match;

        return [
            new PrivateChannel('match.' . $match->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'match_id' => $this->message->match_id,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->body,
            'delivered' => $this->message->delivered,
            'flagged' => $this->message->flagged,
            'sent_at' => $this->message->sent_at?->toISOString(),
        ];
    }
}
