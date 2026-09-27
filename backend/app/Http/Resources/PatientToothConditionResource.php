<?php

namespace App\Http\Resources;

use App\Domain\Dental\Models\PatientToothCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientToothCondition */
class PatientToothConditionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scope_type' => $this->scope_type,
            'tooth_number' => $this->tooth_number,
            'quadrant' => $this->quadrant,
            'arch' => $this->arch,
            'surfaces' => $this->surfaces,
            'notes' => $this->notes,
            'condition' => [
                'key' => $this->condition->key,
                'label' => $this->condition->label,
            ],
            'is_voided' => $this->isVoided(),
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
