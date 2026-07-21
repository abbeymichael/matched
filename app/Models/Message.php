<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['match_id', 'sender_id', 'body', 'flagged', 'flag_reason', 'delivered', 'read_at', 'sent_at'];

    protected function casts(): array
    {
        return [
            'flagged' => 'boolean',
            'delivered' => 'boolean',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MutualMatch::class, 'match_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
