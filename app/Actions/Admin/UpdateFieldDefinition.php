<?php

namespace App\Actions\Admin;

use App\Models\FieldDefinition;
use Illuminate\Validation\ValidationException;

/**
 * Update an admin-configurable field definition.
 *
 * Changing a field's active/hard-filter/weight/sort order can change every
 * user's score, so we mark the field as stale. The nightly command or an admin
 * action can recompute scores afterward.
 */
final class UpdateFieldDefinition
{
    public function handle(FieldDefinition $field, array $data): FieldDefinition
    {
        $data = $this->validate($data, $field);

        $changed = $this->fieldChangesScores($field, $data);

        $field->fill([
            'label' => $data['label'] ?? $field->label,
            'description' => $data['description'] ?? $field->description,
            'category' => $data['category'] ?? $field->category,
            'is_active' => $data['is_active'] ?? $field->is_active,
            'is_hard_filter' => $data['is_hard_filter'] ?? $field->is_hard_filter,
            'is_required' => $data['is_required'] ?? $field->is_required,
            'weight' => $data['weight'] ?? $field->weight,
            'sort_order' => $data['sort_order'] ?? $field->sort_order,
            'config' => $data['config'] ?? $field->config,
        ])->save();

        if ($changed) {
            $field->scores_stale_since = now();
            $field->save();
        }

        return $field;
    }

    private function validate(array $data, FieldDefinition $field): array
    {
        $rules = [
            'label' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
            'is_hard_filter' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
            'weight' => ['sometimes', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer'],
            'config' => ['sometimes', 'array'],
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    private function fieldChangesScores(FieldDefinition $field, array $data): bool
    {
        $scoreKeys = ['is_active', 'is_hard_filter', 'weight', 'sort_order', 'config'];

        foreach ($scoreKeys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== $field->getAttribute($key)) {
                return true;
            }
        }

        return false;
    }
}
