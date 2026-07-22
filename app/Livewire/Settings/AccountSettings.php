<?php

namespace App\Livewire\Settings;

use App\Actions\Onboarding\ResetUserProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AccountSettings extends Component
{
    public int $threshold = 60;
    public bool $confirmReset = false;

    public function mount(): void
    {
        $this->threshold = Auth::user()->match_threshold;
    }

    public function updateThreshold(): void
    {
        Auth::user()->forceFill(['match_threshold' => max(0, min(100, (int) $this->threshold))])->save();
        $this->dispatch('threshold-updated');
    }

    public function resetProfile(ResetUserProfile $action): void
    {
        if (! $this->confirmReset) {
            $this->addError('confirmReset', 'Please confirm you want to reset.');
            return;
        }

        $action->handle(Auth::user());
        $this->redirectRoute('onboarding.profile', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.account-settings');
    }
}
