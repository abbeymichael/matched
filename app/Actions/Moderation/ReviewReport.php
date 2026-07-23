<?php

namespace App\Actions\Moderation;

use App\Enums\ModerationAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Admin review of a filed report.
 *
 * Resolves the report with a status and optional action. If the action
 * is suspension or ban, the corresponding moderation action is delegated.
 */
final class ReviewReport
{
    public function __construct(
        private readonly SuspendUser $suspendUser,
        private readonly BanUser $banUser,
        private readonly RestoreUser $restoreUser,
    ) {}

    public function handle(
        User $admin,
        Report $report,
        ReportStatus $status,
        ModerationAction $action,
        ?string $adminNotes = null,
        ?string $banReason = null,
        ?int $suspensionDays = null,
    ): Report {
        if ($status === ReportStatus::Pending) {
            throw ValidationException::withMessages(['status' => 'Review must set a resolved status.']);
        }

        $reported = $report->reported;

        match ($action) {
            ModerationAction::Banned => $this->banUser->handle($reported, $banReason ?? $report->reason->label()),
            ModerationAction::Suspended => $this->suspendUser->handle($reported, $suspensionDays ?? 7),
            ModerationAction::Warned => $this->restoreUser->handle($reported), // no status change, just a warning
            ModerationAction::Dismissed => $this->restoreUser->handle($reported),
        };

        $report->fill([
            'status' => $status,
            'action_taken' => $action,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ])->save();

        return $report;
    }
}
