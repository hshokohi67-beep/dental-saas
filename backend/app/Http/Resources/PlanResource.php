<?php

namespace App\Http\Resources;

use App\Domain\Platform\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plan */
class PlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'limits' => $this->limits,
            'features' => $this->features,
        ];
    }
}
