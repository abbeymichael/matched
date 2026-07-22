<?php

namespace App\Livewire\Onboarding;

use App\Models\ProfilePhoto;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PhotoUpload extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|max:10240')]
    public $photo = null;

    public bool $hasPrimary = false;

    public function mount(): void
    {
        $this->hasPrimary = Auth::user()->photos()->where('is_primary', true)->exists();
    }

    public function updatedPhoto(): void
    {
        $this->validate();

        $this->savePhoto($this->photo);

        $this->photo = null;
    }

    private function savePhoto(TemporaryUploadedFile $file): void
    {
        $user = Auth::user();
        $service = app(ImageService::class);
        $path = $service->compressAndStore($file, "photos/{$user->id}");

        $isPrimary = ! $user->photos()->where('is_primary', true)->exists();
        $sortOrder = $user->photos()->max('sort_order') + 1;

        ProfilePhoto::create([
            'user_id' => $user->id,
            'path' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
            'original_size_kb' => (int) round($file->getSize() / 1024),
        ]);

        $this->hasPrimary = true;
    }

    public function setPrimary(string $photoId): void
    {
        $user = Auth::user();
        $user->photos()->where('is_primary', true)->update(['is_primary' => false]);
        $user->photos()->where('id', $photoId)->update(['is_primary' => true]);
    }

    public function deletePhoto(string $photoId): void
    {
        $photo = ProfilePhoto::findOrFail($photoId);

        if ($photo->user_id !== Auth::id()) {
            return;
        }

        $photo->delete();
    }

    public function continue(): void
    {
        if (! Auth::user()->photos()->where('is_primary', true)->exists()) {
            $this->addError('photo', 'Please upload at least one profile photo.');
            return;
        }

        $this->redirectRoute('onboarding.selfie', navigate: true);
    }

    public function render()
    {
        return view('livewire.onboarding.photo-upload', [
            'photos' => Auth::user()->photos()->orderBy('sort_order')->get(),
        ]);
    }
}
