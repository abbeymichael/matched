<?php

namespace App\Actions\Moderation;

use App\Enums\ModerationAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Moderator review actions (§12.3): dismiss/warn/suspend/ban. Each action is
 * implemented here so both the Livewire admin UI and the API controller call
 * the same logic (§11.5 parity rule).
 */
final class ReviewReport
{
    public function __construct(
        private readonly SuspendUser $suspendUser,
        private readonly BanUser $banUser,
        private readonly RestoreUser $restoreUser,
    ) {}

    public function handle(Report $report, ModerationAction $action, ?string $adminNotes = null, ?int $suspensionDays = null): Report
    {
        if ($report->status !== ReportStatus::Pending) {
            throw ValidationException::withMessages(['status' => 'This report has already been reviewed.']);
        }

        $reported = $report->reported;

        match ($action) {
            ModerationAction::Dismissed => $this->restoreUser->handle($reported),
            ModerationAction::Warned => $this->warn($reported),
            ModerationAction::Suspended => $this->suspendUser->handle($reported, $suspensionDays ?? 7),
            ModerationAction::Banned => $this->banUser->handle($reported, $adminNotes ?? 'Zero-tolerance violation confirmed by moderator.'),
        };

        $report->forceFill([
            'status' => $action === ModerationAction::Dismissed ? ReportStatus::ReviewedDismissed : ReportStatus::ReviewedActioned,
            'admin_notes' => $adminNotes,
            'action_taken' => $action->value,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ])->save();

        return $report->refresh();
    }

    private function warn(\App\Models\User $user): void
    {
        $this->restoreUser->handle($user);
        $user->increment('strike_count');
    }
}
