<?php

namespace App\Livewire\Admin\Verification;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class VerificationQueue extends Component
{
    use WithPagination;

    public function approve(string $id): void
    {
        User::findOrFail($id)->forceFill(['verification_status' => 'approved'])->save();
    }

    public function reject(string $id): void
    {
        User::findOrFail($id)->forceFill(['verification_status' => 'rejected'])->save();
    }

    public function render()
    {
        return view('livewire.admin.verification.verification-queue', [
            'users' => User::where('verification_status', 'pending')
                ->with('photos')
                ->orderBy('created_at', 'asc')
                ->paginate(50),
        ]);
    }
}
