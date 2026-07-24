<?php

namespace App\Actions\Admin;

use App\Models\ProfileFieldOption;

/**
 * Retire/rename a single option without touching the whole field (§2.4).
 */
final class UpdateFieldOption
{
    public function handle(ProfileFieldOption $option, array $attributes): ProfileFieldOption
    {
        $option->fill([
            'label' => $attributes['label'] ?? $option->label,
            'sort_order' => array_key_exists('sort_order', $attributes) ? (int) $attributes['sort_order'] : $option->sort_order,
            'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : $option->is_active,
        ])->save();

        return $option;
    }
}
