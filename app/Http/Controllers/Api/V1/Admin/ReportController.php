<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Moderation\ReviewReport;
use App\Enums\ModerationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::query()->with(['reported', 'reporter'])
            ->orderByRaw("severity = 'zero_tolerance' DESC")
            ->orderBy('created_at', 'desc');

        if ($status = $request->query('status', 'pending')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        return response()->json(ReportResource::collection($query->paginate(50)->items()));
    }

    public function show(string $report): JsonResponse
    {
        $report = Report::with(['reported', 'reporter'])->findOrFail($report);
        $history = Report::where('reported_id', $report->reported_id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'report' => new ReportResource($report),
            'history' => ReportResource::collection($history),
        ]);
    }

    public function review(Request $request, string $report, ReviewReport $action): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:dismissed,warned,suspended,banned'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'suspension_days' => ['nullable', 'integer'],
        ]);

        $updated = $action->handle(
            Report::findOrFail($report),
            ModerationAction::from($data['action']),
            $data['admin_notes'] ?? null,
            $data['suspension_days'] ?? null,
        );

        return response()->json(new ReportResource($updated));
    }
}
