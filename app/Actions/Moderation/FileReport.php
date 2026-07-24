<?php

namespace App\Actions\Moderation;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\UserStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Creates a Report, auto-assigns severity from the reason (§12.2), and
 * auto-suspends the reported user immediately for zero-tolerance categories
 * — no moderator action required to trigger the suspension, only to resolve
 * it. Also auto-escalates a user with 3+ pending standard reports.
 */
final class FileReport
{
    public function __construct(private readonly SuspendUser $suspend) {}

    public function handle(User $reporter, User $reported, ReportReason $reason, ?string $details = null, ?string $messageId = null, ?string $matchId = null): Report
    {
        if ($reporter->id === $reported->id) {
            throw ValidationException::withMessages(['reported_id' => 'You cannot report yourself.']);
        }

        $severity = $reason->defaultSeverity();

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $reported->id,
            'reason' => $reason,
            'details' => $details,
            'message_id' => $messageId,
            'match_id' => $matchId,
            'status' => ReportStatus::Pending,
            'severity' => $severity,
        ]);

        if ($severity === \App\Enums\ReportSeverity::ZeroTolerance) {
            $this->suspend->handle($reported, null, UserStatus::UnderReview->value);
        } else {
            $pendingStandardCount = Report::where('reported_id', $reported->id)
                ->where('status', ReportStatus::Pending)
                ->where('severity', \App\Enums\ReportSeverity::Standard)
                ->count();

            $threshold = config('matchlock.standard_report_escalation_count', 3);
            if ($pendingStandardCount >= $threshold) {
                $this->suspend->handle($reported, null, UserStatus::UnderReview->value);
            }
        }

        return $report;
    }
}
