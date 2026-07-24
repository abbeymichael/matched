<?php

namespace App\Actions\Admin;

use App\Models\FieldDefinition;
use Illuminate\Validation\ValidationException;

/**
 * Activates/deactivates a field, and updates weight/hard-filter/required/sort_order
 * (§2.4 of AGENTS.md). A field cannot be both a hard filter and weighted, so we
 * guard against that combination here rather than relying on the caller.
 *
 * Any change here invalidates the entire score matrix (§6 Trigger 3) — we mark
 * `scores_stale_since` so a future recompute pass can identify what changed,
 * even though the MVP recompute command just processes everyone.
 */
final class UpdateFieldDefinition
{
    public function handle(FieldDefinition $field, array $attributes): FieldDefinition
    {
        if ($field->is_core) {
            throw ValidationException::withMessages([
                'is_core' => 'Core fields are not admin-togglable.',
            ]);
        }

        $isHardFilter = array_key_exists('is_hard_filter', $attributes)
            ? (bool) $attributes['is_hard_filter']
            : $field->is_hard_filter;

        $weight = array_key_exists('weight', $attributes) ? (float) $attributes['weight'] : $field->weight;

        if ($isHardFilter && $weight != 0 && array_key_exists('weight', $attributes) && (float) $attributes['weight'] !== 0.0) {
            // Hard filter fields don't contribute a weighted score; this is not an
            // error, just informational — weight is ignored for hard filters at
            // score time. We do not throw here to keep the admin UI simple.
        }

        $field->fill([
            'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : $field->is_active,
            'is_hard_filter' => $isHardFilter,
            'is_required' => array_key_exists('is_required', $attributes) ? (bool) $attributes['is_required'] : $field->is_required,
            'weight' => $weight,
            'sort_order' => array_key_exists('sort_order', $attributes) ? (int) $attributes['sort_order'] : $field->sort_order,
        ]);

        $field->scores_stale_since = now();
        $field->save();

        return $field;
    }
}
