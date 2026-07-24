<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reporter_id' => $this->reporter_id,
            'reported_id' => $this->reported_id,
            'reason' => $this->reason->value,
            'severity' => $this->severity->value,
            'status' => $this->status->value,
            'details' => $this->details,
            'created_at' => $this->created_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'action_taken' => $this->action_taken,
        ];
    }
}
