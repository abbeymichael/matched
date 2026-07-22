<?php

namespace App\Livewire\Onboarding;

use App\Actions\Onboarding\SavePreferenceStep;
use App\Models\City;
use App\Models\FieldDefinition;
use App\Models\Preferences;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PreferenceWizard extends Component
{
    public array $fields = [];
    public array $data = [];
    public int $step = 0;
    public int $ageMin = 18;
    public int $ageMax = 50;
    public array $acceptedGenders = [];
    public int $maxDistanceKm = 50;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->profile_locked) {
            $this->redirectRoute('dashboard');
            return;
        }

        $this->fields = FieldDefinition::activeOrdered()->where('is_core', false)->values()->toArray();
        $prefs = $user->preferences;

        if ($prefs) {
            $this->ageMin = $prefs->age_min;
            $this->ageMax = $prefs->age_max;
            $this->acceptedGenders = $prefs->accepted_genders ?? [];
            $this->maxDistanceKm = $prefs->max_distance_km;
        }

        foreach ($user->preferenceFieldValues as $value) {
            $this->data[$value->field_key] = $value->value;
        }
    }

    public function saveStep(SavePreferenceStep $action): void
    {
        $user = Auth::user();
        $payload = [
            'age_min' => $this->ageMin,
            'age_max' => $this->ageMax,
            'accepted_genders' => $this->acceptedGenders,
            'max_distance_km' => $this->maxDistanceKm,
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
            $this->redirectRoute('onboarding.review', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.onboarding.preference-wizard', [
            'currentField' => $this->fields[$this->step - 1] ?? null,
            'totalSteps' => count($this->fields) + 1,
        ]);
    }
}
