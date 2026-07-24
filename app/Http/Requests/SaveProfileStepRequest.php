<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProfileStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(config('matchlock.minimum_age', 18))->toDateString()],
            'gender' => ['required', 'string'],
            'city' => ['required', 'string'],
            'field_key' => ['nullable', 'string'],
            'field_value' => ['nullable'],
        ];
    }
}
