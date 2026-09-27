<?php

namespace App\Http\Resources;

use App\Domain\Patients\Models\MedicalCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicalCondition */
class MedicalConditionResource extends JsonResource
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
            'alert_text' => $this->alert_text,
        ];
    }
}
