<?php

namespace App\Models;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldDefinition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'key', 'label', 'description', 'category', 'field_type',
        'is_active', 'is_hard_filter', 'is_required', 'is_core', 'weight', 'sort_order', 'config',
        'scores_stale_since',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_hard_filter' => 'boolean',
            'is_required' => 'boolean',
            'is_core' => 'boolean',
            'weight' => 'float',
            'sort_order' => 'integer',
            'config' => 'array',
            'scores_stale_since' => 'datetime',
            'field_type' => FieldType::class,
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProfileFieldOption::class, 'field_key', 'key')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public static function activeOrdered()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }

    public static function activeWeighted()
    {
        return static::where('is_active', true)->where('is_hard_filter', false)->get();
    }

    public static function activeHardFilters()
    {
        return static::where('is_active', true)->where('is_hard_filter', true)->get();
    }
}
