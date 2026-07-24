<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single row on the viewer's match list (§6 Trigger 2 query shape).
 */
class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $target = $this->target;
        $primaryPhoto = $target?->photos?->firstWhere('is_primary', true);

        return [
            'user_id' => $target?->id,
            'display_name' => $target?->profile?->display_name,
            'age' => $target?->profile?->date_of_birth?->age,
            'city' => $target?->profile?->city,
            'photo_url' => $primaryPhoto?->url(),
            'score' => $this->score,
        ];
    }
}
