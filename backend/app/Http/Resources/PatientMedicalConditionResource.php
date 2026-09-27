<?php

namespace App\Http\Resources;

use App\Domain\Patients\Models\PatientMedicalCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientMedicalCondition */
class PatientMedicalConditionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medical_condition_id' => $this->medical_condition_id,
            'label' => $this->condition?->label,
            'alert_text' => $this->condition?->alert_text,
            'notes' => $this->notes,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
