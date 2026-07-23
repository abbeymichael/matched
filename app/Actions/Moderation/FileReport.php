<?php

namespace App\Actions\Moderation;

use App\Enums\ReportReason;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Models\Message;
use App\Models\MutualMatch;
use App\Models\Report;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * File a user or message report.
 *
 * The report severity is suggested from the report reason but can be
 * overridden by the caller. The reported user is not immediately actioned;
 * an admin must review the report (ReviewReport action).
 */
final class FileReport
{
    public function handle(
        User $reporter,
        User $reported,
        ReportReason $reason,
        ?string $details = null,
        ?ReportSeverity $severity = null,
        ?Message $message = null,
        ?MutualMatch $match = null,
    ): Report {
        if ($reporter->id === $reported->id) {
            throw ValidationException::withMessages(['reported' => 'You cannot report yourself.']);
        }

        if ($reported->is_admin) {
            throw ValidationException::withMessages(['reported' => 'Cannot report an admin user.']);
        }

        $severity ??= $reason->defaultSeverity();

        return Report::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $reported->id,
            'reason' => $reason,
            'details' => $details,
            'severity' => $severity,
            'message_id' => $message?->id,
            'match_id' => $match?->id,
            'status' => ReportStatus::Pending,
            'created_at' => now(),
        ]);
    }
}
