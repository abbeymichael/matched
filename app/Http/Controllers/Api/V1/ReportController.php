<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Moderation\FileReport;
use App\Enums\ReportReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function store(FileReportRequest $request, FileReport $action): JsonResponse
    {
        $data = $request->validated();
        $reported = User::findOrFail($data['reported_id']);

        $report = $action->handle(
            $request->user(),
            $reported,
            ReportReason::from($data['reason']),
            $data['details'] ?? null,
            $data['message_id'] ?? null,
            $data['match_id'] ?? null,
        );

        return response()->json(new ReportResource($report), 201);
    }
}
