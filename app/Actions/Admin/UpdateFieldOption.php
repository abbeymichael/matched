<?php

namespace App\Actions\Admin;

use App\Models\FieldDefinition;
use App\Models\ProfileFieldOption;
use Illuminate\Validation\ValidationException;

/**
 * Update an option for a field library option list.
 *
 * Changing an option label is cosmetic. Changing active state or sort order
 * can affect scores, so we mark the parent field as stale.
 */
final class UpdateFieldOption
{
    public function handle(ProfileFieldOption $option, array $data): ProfileFieldOption
    {
        $data = $this->validate($data);

        $option->fill([
            'label' => $data['label'] ?? $option->label,
            'value' => $data['value'] ?? $option->value,
            'sort_order' => $data['sort_order'] ?? $option->sort_order,
            'is_active' => $data['is_active'] ?? $option->is_active,
        ])->save();

        if (array_key_exists('is_active', $data) || array_key_exists('value', $data) || array_key_exists('sort_order', $data)) {
            FieldDefinition::query()
                ->where('key', $option->field_key)
                ->update(['scores_stale_since' => now()]);
        }

        return $option;
    }

    private function validate(array $data): array
    {
        $rules = [
            'label' => ['sometimes', 'string', 'max:120'],
            'value' => ['sometimes', 'string', 'max:60'],
            'sort_order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }
}
