<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'display_name' => $this->display_name,
            'age' => $this->date_of_birth?->age,
            'gender' => $this->gender,
            'city' => $this->city,
        ];
    }
}
