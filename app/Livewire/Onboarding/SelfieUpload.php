<?php

namespace App\Livewire\Onboarding;

use App\Models\ProfilePhoto;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class SelfieUpload extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:10240')]
    public $selfie = null;

    public function submit(): void
    {
        $this->validate();

        $user = Auth::user();
        $service = app(ImageService::class);
        $path = $service->compressAndStore($this->selfie, "selfies/{$user->id}");

        ProfilePhoto::create([
            'user_id' => $user->id,
            'path' => $path,
            'is_primary' => false,
            'is_selfie' => true,
            'sort_order' => 999,
            'original_size_kb' => (int) round($this->selfie->getSize() / 1024),
        ]);

        $user->forceFill(['verification_status' => 'pending'])->save();

        $this->redirectRoute('onboarding.profile', navigate: true);
    }

    public function skip(): void
    {
        $this->redirectRoute('onboarding.profile', navigate: true);
    }

    public function render()
    {
        return view('livewire.onboarding.selfie-upload');
    }
}
