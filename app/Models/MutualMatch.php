<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `matches` table. Named MutualMatch to avoid the PHP
 * reserved-word conflict a `Match` class name would create (§9).
 */
class MutualMatch extends Model
{
    use HasUuids;

    protected $table = 'matches';

    protected $fillable = ['user_a_id', 'user_b_id', 'matched_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function userA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_a_id');
    }

    public function userB(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_b_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'match_id')->orderBy('sent_at');
    }

    public function otherUser(string $userId): ?User
    {
        $otherId = $this->user_a_id === $userId ? $this->user_b_id : $this->user_a_id;

        return User::find($otherId);
    }

    public function includesUser(string $userId): bool
    {
        return $this->user_a_id === $userId || $this->user_b_id === $userId;
    }
}
