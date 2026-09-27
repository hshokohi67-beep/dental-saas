<?php

namespace App\Http\Resources;

use App\Domain\Operations\Models\StaffShift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StaffShift */
class StaffShiftResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'branch_id' => $this->branch_id,
            'room_id' => $this->room_id,
            'day_of_week' => $this->day_of_week,
            'start_time' => substr((string) $this->start_time, 0, 5),
            'end_time' => substr((string) $this->end_time, 0, 5),
            'dental_condition_catalog' => $this->whenLoaded(
                'dentalConditionCatalog',
                fn () => $this->dentalConditionCatalog ? new DentalConditionCatalogResource($this->dentalConditionCatalog) : null,
            ),
            'daily_cap' => $this->daily_cap,
            'effective_from' => optional($this->effective_from)->toDateString(),
            'effective_until' => optional($this->effective_until)->toDateString(),
            'is_active' => $this->is_active,
        ];
    }
}
