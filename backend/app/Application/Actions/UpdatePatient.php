<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\DB;

class UpdatePatient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            $patient->update($data);

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_UPDATED,
                'اطلاعات پرونده‌ی بیمار به‌روزرسانی شد.',
                ['fields' => array_keys($data)],
            );

            return $patient;
        });
    }
}
