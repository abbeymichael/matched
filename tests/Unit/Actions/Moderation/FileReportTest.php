<?php

namespace Tests\Unit\Actions\Moderation;

use App\Actions\Moderation\FileReport;
use App\Actions\Moderation\SuspendUser;
use App\Enums\ReportReason;
use App\Enums\ReportSeverity;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_tolerance_reason_auto_suspends_reported_user(): void
    {
        $reporter = User::factory()->create();
        $reported = User::factory()->create(['status' => 'active']);

        $action = new FileReport(new SuspendUser());
        $report = $action->handle($reporter, $reported, ReportReason::Threats, 'threatening message');

        $this->assertSame(ReportSeverity::ZeroTolerance, $report->severity);
        $reported->refresh();
        $this->assertSame(UserStatus::UnderReview->value, $reported->status);
    }

    public function test_standard_reason_does_not_immediately_suspend(): void
    {
        $reporter = User::factory()->create();
        $reported = User::factory()->create(['status' => 'active']);

        $action = new FileReport(new SuspendUser());
        $report = $action->handle($reporter, $reported, ReportReason::FakeProfile);

        $this->assertSame(ReportSeverity::Standard, $report->severity);
        $reported->refresh();
        $this->assertSame('active', $reported->status);
    }

    public function test_three_pending_standard_reports_escalate_to_under_review(): void
    {
        $reported = User::factory()->create(['status' => 'active']);
        $action = new FileReport(new SuspendUser());

        $action->handle(User::factory()->create(), $reported, ReportReason::Other);
        $action->handle(User::factory()->create(), $reported, ReportReason::Other);
        $action->handle(User::factory()->create(), $reported, ReportReason::Other);

        $reported->refresh();
        $this->assertSame(UserStatus::UnderReview->value, $reported->status);
    }

    public function test_cannot_report_self(): void
    {
        $user = User::factory()->create();
        $action = new FileReport(new SuspendUser());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $action->handle($user, $user, ReportReason::Other);
    }
}
