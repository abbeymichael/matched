<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePhoto extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'path', 'is_primary', 'is_selfie', 'sort_order', 'original_size_kb'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_selfie' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return \Storage::disk('public')->url($this->path);
    }
}
