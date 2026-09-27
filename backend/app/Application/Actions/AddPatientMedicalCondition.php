<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\MedicalCondition;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientMedicalCondition;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddPatientMedicalCondition
{
    public function execute(Patient $patient, MedicalCondition $condition, ?string $notes = null): PatientMedicalCondition
    {
        return DB::transaction(function () use ($patient, $condition, $notes) {
            $link = PatientMedicalCondition::query()->firstOrCreate(
                ['patient_id' => $patient->id, 'medical_condition_id' => $condition->id],
                ['notes' => $notes, 'recorded_by' => Auth::id(), 'recorded_at' => now()],
            );

            if ($link->wasRecentlyCreated) {
                PatientTimelineRecorder::record(
                    $patient,
                    PatientTimelineEvent::TYPE_MEDICAL_CONDITION_ADDED,
                    "بیماری زمینه‌ای «{$condition->label}» ثبت شد.",
                    ['medical_condition_id' => $condition->id],
                );
            }

            return $link;
        });
    }
}
