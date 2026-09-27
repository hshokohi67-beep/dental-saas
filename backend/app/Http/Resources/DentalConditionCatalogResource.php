<?php

namespace App\Http\Resources;

use App\Domain\Dental\Models\DentalConditionCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DentalConditionCatalog */
class DentalConditionCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'category' => $this->category,
            'scope' => $this->scope,
            'dentitions' => $this->dentitions,
            'status_color' => $this->status_color,
            'status_border' => $this->status_border,
            'booking_eligible' => $this->booking_eligible,
            'duration_minutes' => $this->duration_minutes,
            'buffer_minutes' => $this->buffer_minutes,
        ];
    }
}
