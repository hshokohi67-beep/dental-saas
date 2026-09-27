<?php

namespace App\Http\Resources;

use App\Domain\Operations\Models\AppointmentWaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentWaitlistEntry */
class AppointmentWaitlistResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'patient' => [
                'id' => $this->patient->id,
                'full_name' => $this->patient->fullName(),
                'mobile' => $this->patient->mobile,
            ],
            'staff_id' => $this->staff_id,
            'dental_condition_catalog' => new DentalConditionCatalogResource($this->dentalConditionCatalog),
            'preferred_from' => optional($this->preferred_from)->toDateString(),
            'preferred_until' => optional($this->preferred_until)->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
