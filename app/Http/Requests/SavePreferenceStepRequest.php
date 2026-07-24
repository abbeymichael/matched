<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePreferenceStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'age_min' => ['required', 'integer', 'min:18', 'max:100'],
            'age_max' => ['required', 'integer', 'min:18', 'max:100', 'gte:age_min'],
            'accepted_genders' => ['required', 'array', 'min:1'],
            'max_distance_km' => ['required', 'integer', 'min:1', 'max:1000'],
            'field_key' => ['nullable', 'string'],
            'field_value' => ['nullable'],
        ];
    }
}
