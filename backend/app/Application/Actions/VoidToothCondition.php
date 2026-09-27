<?php

namespace App\Application\Actions;

use App\Domain\Dental\Models\PatientToothCondition;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Voids (never hard-deletes) a recorded tooth condition — the target
 * architecture forbids hard delete of clinical records.
 */
class VoidToothCondition
{
    public function execute(PatientToothCondition $condition): PatientToothCondition
    {
        return DB::transaction(function () use ($condition) {
            $condition->loadMissing('patient', 'condition');

            $condition->update(['voided_at' => now(), 'voided_by' => Auth::id()]);

            PatientTimelineRecorder::record(
                $condition->patient,
                PatientTimelineEvent::TYPE_TOOTH_CONDITION_VOIDED,
                "«{$condition->condition->label}» باطل شد.",
                ['patient_tooth_condition_id' => $condition->id],
            );

            return $condition;
        });
    }
}
