<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileFieldValue extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'field_key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
