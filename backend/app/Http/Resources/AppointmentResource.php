<?php

namespace App\Http\Resources;

use App\Domain\Operations\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
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
            'staff' => [
                'id' => $this->staff->id,
                'name' => $this->staff->user->name,
            ],
            'room_id' => $this->room_id,
            'dental_condition_catalog' => new DentalConditionCatalogResource($this->dentalConditionCatalog),
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'ends_at' => $this->endsAt()->toIso8601String(),
            'status' => $this->status,
            'notes' => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'checked_in_at' => optional($this->checked_in_at)->toIso8601String(),
            'completed_at' => optional($this->completed_at)->toIso8601String(),
            'cancelled_at' => optional($this->cancelled_at)->toIso8601String(),
        ];
    }
}
