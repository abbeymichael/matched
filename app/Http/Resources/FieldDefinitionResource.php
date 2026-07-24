<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Server-driven onboarding (§11.4): mobile clients render their wizard from
 * this shape, never from a hardcoded field list in the app binary.
 */
class FieldDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'field_type' => $this->field_type->value,
            'is_required' => $this->is_required,
            'sort_order' => $this->sort_order,
            'config' => $this->config ?? [],
            'options' => FieldOptionResource::collection($this->options),
        ];
    }
}
