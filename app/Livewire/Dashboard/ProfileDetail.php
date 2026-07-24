<?php

namespace App\Livewire\Dashboard;

use App\Actions\Moderation\FileReport;
use App\Actions\Social\RegisterInterest;
use App\Enums\ReportReason;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProfileDetail extends Component
{
    public User $user;
    public bool $hasExpressedInterest = false;
    public bool $showReportModal = false;
    public string $reportReason = 'harassment';
    public string $reportDetails = '';
    public bool $reportSubmitted = false;

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

    public function openReportModal(): void
    {
        $this->showReportModal = true;
    }

    public function closeReportModal(): void
    {
        $this->showReportModal = false;
    }

    public function submitReport(FileReport $action): void
    {
        $this->validate([
            'reportReason' => 'required|string',
            'reportDetails' => 'nullable|string|max:1000',
        ]);

        try {
            $action->handle(Auth::user(), $this->user, ReportReason::from($this->reportReason), $this->reportDetails ?: null);
        } catch (ValidationException $e) {
            $this->addError('reportReason', $e->getMessage());
            return;
        }

        $this->showReportModal = false;
        $this->reportSubmitted = true;
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
