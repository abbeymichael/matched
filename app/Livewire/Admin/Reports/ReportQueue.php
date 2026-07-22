<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Report;
use Livewire\Component;
use Livewire\WithPagination;

class ReportQueue extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public function render()
    {
        $query = Report::query()
            ->with(['reported', 'reporter'])
            ->orderByRaw("severity = 'zero_tolerance' DESC")
            ->orderBy('created_at', 'desc');

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.admin.reports.report-queue', [
            'reports' => $query->paginate(50),
        ]);
    }
}
