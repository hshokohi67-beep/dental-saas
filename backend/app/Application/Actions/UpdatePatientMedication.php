<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\PatientMedication;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\DB;

class UpdatePatientMedication
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(PatientMedication $medication, array $data): PatientMedication
    {
        return DB::transaction(function () use ($medication, $data) {
            $medication->update($data);

            PatientTimelineRecorder::record(
                $medication->patient,
                PatientTimelineEvent::TYPE_MEDICATION_UPDATED,
                "وضعیت داروی «{$medication->name}» به‌روزرسانی شد.",
                ['patient_medication_id' => $medication->id],
            );

            return $medication;
        });
    }
}
