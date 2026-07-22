<?php

namespace App\Livewire\Admin\Users;

use App\Actions\Moderation\BanUser;
use App\Actions\Moderation\RestoreUser;
use App\Actions\Moderation\SuspendUser;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $editing = '';
    public string $verificationStatus = '';
    public string $status = '';
    public ?int $suspensionDays = null;

    public function edit(string $id): void
    {
        $user = User::findOrFail($id);
        $this->editing = $id;
        $this->verificationStatus = $user->verification_status;
        $this->status = $user->status;
    }

    public function cancel(): void
    {
        $this->editing = '';
        $this->verificationStatus = '';
        $this->status = '';
        $this->suspensionDays = null;
    }

    public function saveVerification(string $id): void
    {
        $this->validate([
            'verificationStatus' => 'required|string',
        ]);

        User::findOrFail($id)->forceFill(['verification_status' => $this->verificationStatus])->save();
        $this->editing = '';
    }

    public function suspend(string $id, SuspendUser $action): void
    {
        try {
            $action->handle(User::findOrFail($id), $this->suspensionDays ?? 7);
        } catch (ValidationException $e) {
            $this->addError('status', $e->getMessage());
            return;
        }
        $this->cancel();
    }

    public function ban(string $id, BanUser $action): void
    {
        try {
            $action->handle(User::findOrFail($id), 'Manual admin ban');
        } catch (ValidationException $e) {
            $this->addError('status', $e->getMessage());
            return;
        }
        $this->cancel();
    }

    public function restore(string $id, RestoreUser $action): void
    {
        try {
            $action->handle(User::findOrFail($id));
        } catch (ValidationException $e) {
            $this->addError('status', $e->getMessage());
            return;
        }
        $this->cancel();
    }

    public function render()
    {
        $query = User::query()
            ->with('profile')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where('phone', 'like', '%'.$this->search.'%')
                ->orWhereHas('profile', fn ($q) => $q->where('display_name', 'like', '%'.$this->search.'%'));
        }

        return view('livewire.admin.users.user-list', [
            'users' => $query->paginate(50),
        ]);
    }
}
