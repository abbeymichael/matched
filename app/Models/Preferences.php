<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preferences extends Model
{
    use HasUuids;

    protected $table = 'preferences';

    protected $fillable = ['user_id', 'age_min', 'age_max', 'accepted_genders', 'max_distance_km'];

    protected function casts(): array
    {
        return [
            'accepted_genders' => 'array',
            'age_min' => 'integer',
            'age_max' => 'integer',
            'max_distance_km' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
