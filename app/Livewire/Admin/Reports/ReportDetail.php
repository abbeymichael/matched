<?php

namespace App\Livewire\Admin\Reports;

use App\Actions\Moderation\ReviewReport;
use App\Enums\ModerationAction;
use App\Models\Report;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReportDetail extends Component
{
    public Report $report;
    public string $action = '';
    public string $adminNotes = '';
    public ?string $suspensionDays = null;

    public function mount(string $report): void
    {
        $this->report = Report::findOrFail($report);
    }

    public function submit(ReviewReport $action): void
    {
        $this->validate([
            'action' => 'required|string',
            'adminNotes' => 'nullable|string|max:5000',
            'suspensionDays' => 'nullable|integer',
        ]);

        try {
            $action->handle(
                $this->report,
                ModerationAction::from($this->action),
                $this->adminNotes,
                $this->suspensionDays ? (int) $this->suspensionDays : null
            );
        } catch (ValidationException $e) {
            $this->addError('action', $e->getMessage());
            return;
        }

        $this->dispatch('report-reviewed');
    }

    public function render()
    {
        return view('livewire.admin.reports.report-detail', [
            'history' => Report::where('reported_id', $this->report->reported_id)->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
