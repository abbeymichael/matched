<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired on every message creation, even though nothing listens to it yet for
 * MVP (chat uses wire:poll.5s, §8.4). This makes the future Reverb upgrade a
 * config change rather than a code rewrite: the moment a broadcaster is
 * configured, this event starts pushing to `matches.{id}` in real time.
 */
final class MessageSent implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(public readonly Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('matches.'.$this->message->match_id)];
    }
}
