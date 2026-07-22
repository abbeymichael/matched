<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendOtp;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PhoneEntry extends Component
{
    #[Validate('required|string|max:20')]
    public string $phone = '';

    public function submit(SendOtp $action): void
    {
        $this->validate();

        $normalized = $action->handle($this->phone);
        session()->put('auth.phone', $normalized);

        $this->redirectRoute('otp.show', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.phone-entry');
    }
}
