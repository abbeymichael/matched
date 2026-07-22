<?php

namespace App\Livewire\Dashboard;

use App\Actions\Social\RegisterInterest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileDetail extends Component
{
    public User $user;
    public bool $hasExpressedInterest = false;

    public function mount(string $user): void
    {
        $this->user = User::findOrFail($user);
        $this->hasExpressedInterest = Auth::user()->sentInterests()->where('to_id', $this->user->id)->exists();
    }

    public function expressInterest(RegisterInterest $action): void
    {
        $action->handle(Auth::user(), $this->user);
        $this->hasExpressedInterest = true;
    }

    public function render()
    {
        $fields = \App\Models\FieldDefinition::activeOrdered()->where('is_core', false)->get();
        $fieldValues = $this->user->profileFieldValues->keyBy('field_key');

        return view('livewire.dashboard.profile-detail', [
            'fields' => $fields,
            'fieldValues' => $fieldValues,
            'isMutual' => Auth::user()->mutualMatches()
                ->where(function ($query) {
                    $query->where('user_a_id', $this->user->id)
                        ->orWhere('user_b_id', $this->user->id);
                })
                ->where('is_active', true)
                ->exists(),
        ]);
    }
}
