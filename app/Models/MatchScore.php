<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchScore extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['viewer_id', 'target_id', 'score', 'passed_hard_filters', 'updated_at'];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'passed_hard_filters' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
