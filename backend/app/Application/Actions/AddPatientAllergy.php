<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientAllergy;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddPatientAllergy
{
    /**
     * @param  array{allergen: string, severity?: ?string, reaction?: ?string, notes?: ?string}  $data
     */
    public function execute(Patient $patient, array $data): PatientAllergy
    {
        return DB::transaction(function () use ($patient, $data) {
            $allergy = $patient->allergies()->create([
                ...$data,
                'recorded_by' => Auth::id(),
                'recorded_at' => now(),
            ]);

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_ALLERGY_ADDED,
                "حساسیت دارویی «{$allergy->allergen}» ثبت شد.",
                ['patient_allergy_id' => $allergy->id],
            );

            return $allergy;
        });
    }
}
