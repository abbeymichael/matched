<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\VerifyOtp;
use Livewire\Attributes\Validate;
use Livewire\Component;

class OtpVerification extends Component
{
    #[Validate('required|string|size:6')]
    public string $code = '';

    public string $phone = '';

    public function mount(): void
    {
        $this->phone = (string) session('auth.phone');

        if (! $this->phone) {
            $this->redirectRoute('login');
        }
    }

    public function submit(VerifyOtp $action): void
    {
        $this->validate();

        $user = $action->handle($this->phone, $this->code);

        if ($user->profile_locked) {
            $this->redirectRoute('dashboard', navigate: true);
        } else {
            $this->redirectRoute('onboarding.profile', navigate: true);
        }
    }

    public function resend(SendOtp $action): void
    {
        $action->handle($this->phone);
        $this->dispatch('otp-resent');
    }

    public function render()
    {
        return view('livewire.auth.otp-verification');
    }
}
