<?php

namespace App\Livewire\Onboarding;

use App\Actions\Onboarding\SaveProfileStep;
use App\Models\City;
use App\Models\FieldDefinition;
use App\Models\Profile;
use App\Models\ProfilePhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProfileWizard extends Component
{
    public array $fields = [];
    public array $data = [];
    public int $step = 0;
    public string $displayName = '';
    public string $dateOfBirth = '';
    public string $gender = '';
    public string $city = '';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->profile_locked) {
            $this->redirectRoute('dashboard');
            return;
        }

        $this->fields = FieldDefinition::activeOrdered()->where('is_core', false)->values()->toArray();
        $profile = $user->profile;

        if ($profile) {
            $this->displayName = $profile->display_name;
            $this->dateOfBirth = $profile->date_of_birth?->format('Y-m-d');
            $this->gender = $profile->gender;
            $this->city = $profile->city;
        }

        foreach ($user->profileFieldValues as $value) {
            $this->data[$value->field_key] = $value->value;
        }
    }

    public function saveStep(SaveProfileStep $action): void
    {
        $user = Auth::user();
        $payload = [
            'display_name' => $this->displayName,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'city' => $this->city,
        ];

        if ($this->step > 0) {
            $field = $this->fields[$this->step - 1] ?? null;
            if ($field) {
                $payload['field_key'] = $field['key'];
                $payload['field_value'] = $this->data[$field['key']] ?? null;
            }
        }

        try {
            $action->handle($user, $payload);
        } catch (ValidationException $e) {
            $this->addError('step', $e->getMessage());
            return;
        }

        if ($this->step < count($this->fields)) {
            $this->step++;
        } else {
            $this->redirectRoute('onboarding.preferences', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.onboarding.profile-wizard', [
            'cities' => City::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'currentField' => $this->fields[$this->step - 1] ?? null,
            'totalSteps' => count($this->fields) + 1,
        ]);
    }
}
