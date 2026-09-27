<?php

namespace App\Http\Resources;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Support\ClinicalAlertGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The Patient 360 payload (roadmap Phase 2): demographics plus everything a
 * clinician needs at a glance — medical history, allergies, medications and
 * the alerts derived from them, and the most recent timeline entries.
 *
 * @mixin Patient
 */
class PatientDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewMedical = $request->user()?->can(PermissionCatalog::PATIENTS_MEDICAL_VIEW) ?? false;

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullName(),
            'mobile' => $this->mobile,
            'national_id' => $this->national_id,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'notes' => $this->notes,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->branch?->name,
            'created_at' => $this->created_at?->toIso8601String(),
            'merged_from_count' => $this->mergedFrom()->count(),
            'medical' => $canViewMedical ? [
                'conditions' => PatientMedicalConditionResource::collection($this->medicalConditions->loadMissing('condition')),
                'allergies' => PatientAllergyResource::collection($this->allergies),
                'medications' => PatientMedicationResource::collection($this->medications),
                'alerts' => ClinicalAlertGenerator::forPatient($this->resource),
            ] : null,
            'recent_timeline' => PatientTimelineEventResource::collection($this->timelineEvents->take(10)),
        ];
    }
}
