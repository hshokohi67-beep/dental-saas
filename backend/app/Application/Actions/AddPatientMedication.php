<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientMedication;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddPatientMedication
{
    /**
     * @param  array{name: string, dosage?: ?string, frequency?: ?string, notes?: ?string, started_at?: ?string}  $data
     */
    public function execute(Patient $patient, array $data): PatientMedication
    {
        return DB::transaction(function () use ($patient, $data) {
            $medication = $patient->medications()->create([
                ...$data,
                'is_active' => true,
                'recorded_by' => Auth::id(),
            ]);

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_MEDICATION_ADDED,
                "داروی «{$medication->name}» به فهرست داروهای بیمار افزوده شد.",
                ['patient_medication_id' => $medication->id],
            );

            return $medication;
        });
    }
}
