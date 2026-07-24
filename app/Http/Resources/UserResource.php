<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Never return raw Eloquent models (§11.3) — internal fields like
 * strike_count, ban_reason are excluded from this non-admin resource.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'profile_locked' => $this->profile_locked,
            'verification_status' => $this->verification_status,
            'match_threshold' => $this->match_threshold,
            'reset_used' => $this->reset_used,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
        ];
    }
}
