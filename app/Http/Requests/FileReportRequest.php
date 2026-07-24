<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_id' => ['required', 'string', 'exists:users,id'],
            'reason' => ['required', 'string', 'in:harassment,threats,fake_profile,explicit_content,hate_speech,underage,other'],
            'details' => ['nullable', 'string', 'max:1000'],
            'message_id' => ['nullable', 'string', 'exists:messages,id'],
            'match_id' => ['nullable', 'string', 'exists:matches,id'],
        ];
    }
}
