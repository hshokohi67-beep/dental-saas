<?php

namespace App\Http\Resources;

use App\Domain\Patients\Models\PatientMedication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientMedication */
class PatientMedicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'started_at' => $this->started_at?->toDateString(),
            'ended_at' => $this->ended_at?->toDateString(),
        ];
    }
}
