<?php

namespace App\Domain\Patients\Support;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Every meaningful change to a patient's record is appended to their
 * timeline (roadmap Phase 2 — "Timeline"), instead of being reconstructed
 * later from scattered audit rows.
 */
class PatientTimelineRecorder
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function record(Patient $patient, string $type, string $description, array $metadata = []): PatientTimelineEvent
    {
        return PatientTimelineEvent::query()->create([
            'tenant_id' => $patient->tenant_id,
            'patient_id' => $patient->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'recorded_by' => Auth::id(),
            'occurred_at' => now(),
        ]);
    }
}
