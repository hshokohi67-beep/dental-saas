<?php

namespace App\Http\Resources;

use App\Domain\Patients\Models\PatientAllergy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientAllergy */
class PatientAllergyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'allergen' => $this->allergen,
            'severity' => $this->severity,
            'reaction' => $this->reaction,
            'notes' => $this->notes,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
