<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = ['phone', 'email', 'profile_locked', 'match_threshold', 'status', 'verification_status'];
    protected $hidden = ['remember_token'];

    protected function casts(): array
    {
        return ['phone_verified_at' => 'datetime', 'profile_locked' => 'boolean', 'is_admin' => 'boolean', 'locked_at' => 'datetime'];
    }
}
