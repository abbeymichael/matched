<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'phone', 'email', 'profile_locked', 'match_threshold', 'status',
        'verification_status', 'is_admin', 'reset_used', 'consented_at',
    ];

    protected $hidden = ['remember_token', 'password'];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'profile_locked' => 'boolean',
            'reset_used' => 'boolean',
            'is_admin' => 'boolean',
            'locked_at' => 'datetime',
            'banned_at' => 'datetime',
            'suspension_ends_at' => 'datetime',
            'consented_at' => 'datetime',
            'last_active_at' => 'datetime',
            'match_threshold' => 'integer',
            'strike_count' => 'integer',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(Preferences::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProfilePhoto::class)->orderBy('sort_order');
    }

    public function profileFieldValues(): HasMany
    {
        return $this->hasMany(ProfileFieldValue::class);
    }

    public function preferenceFieldValues(): HasMany
    {
        return $this->hasMany(PreferenceFieldValue::class);
    }

    public function sentInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'from_id');
    }

    public function receivedInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'to_id');
    }

    public function matchScoresAsViewer(): HasMany
    {
        return $this->hasMany(MatchScore::class, 'viewer_id');
    }

    public function matchScoresAsTarget(): HasMany
    {
        return $this->hasMany(MatchScore::class, 'target_id');
    }

    public function filedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsAgainst(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * MutualMatches where this user is either party. Custom accessor since a user
     * could be stored as user_a or user_b (§5.3 pair ordering convention).
     */
    public function mutualMatches()
    {
        return MutualMatch::query()
            ->where('user_a_id', $this->id)
            ->orWhere('user_b_id', $this->id);
    }

    public function isBannedOrSuspended(): bool
    {
        return in_array($this->status, ['banned', 'suspended', 'under_review'], true);
    }

    public function age(): ?int
    {
        return $this->profile?->date_of_birth?->age;
    }
}
