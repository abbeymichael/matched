<?php

namespace App\Livewire\Onboarding;

use App\Actions\Onboarding\LockUserProfile;
use App\Models\FieldDefinition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReviewAndLock extends Component
{
    public bool $confirmed = false;

    public function mount(): void
    {
        if (Auth::user()->profile_locked) {
            $this->redirectRoute('dashboard');
        }
    }

    public function lock(LockUserProfile $action): void
    {
        if (! $this->confirmed) {
            $this->addError('confirmed', 'Please confirm that you understand this action is permanent.');

            return;
        }

        try {
            $action->handle(Auth::user());
        } catch (ValidationException $e) {
            $this->addError('lock', $e->getMessage());

            return;
        }

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.onboarding.review-and-lock', [
            'profile' => $user->profile,
            'preferences' => $user->preferences,
            'fieldValues' => $user->profileFieldValues->keyBy('field_key'),
            'preferenceValues' => $user->preferenceFieldValues->keyBy('field_key'),
            'fields' => FieldDefinition::activeOrdered()->where('is_core', false)->get(),
        ]);
    }
}
