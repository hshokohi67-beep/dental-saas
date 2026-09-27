<?php

namespace App\Http\Resources;

use App\Domain\Patients\Models\PatientTimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientTimelineEvent */
class PatientTimelineEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'recorded_by' => $this->recordedBy?->name,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
        ];
    }
}
