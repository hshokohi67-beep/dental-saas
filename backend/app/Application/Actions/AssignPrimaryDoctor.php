<?php

namespace App\Application\Actions;

use App\Domain\Identity\Models\Staff;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\DB;

/**
 * Assigns the doctor responsible for a patient's chart — this is what
 * `DentalChartAccessPolicy` checks for the legacy IDOR fix (business rules
 * §1.8): a doctor may only edit the chart of a patient they are assigned to.
 */
class AssignPrimaryDoctor
{
    public function execute(Patient $patient, ?Staff $doctor): Patient
    {
        return DB::transaction(function () use ($patient, $doctor) {
            $patient->update(['primary_doctor_staff_id' => $doctor?->id]);

            $description = $doctor !== null
                ? "پزشک مسئول پرونده به «{$doctor->user->name}» تغییر کرد."
                : 'پزشک مسئول پرونده حذف شد.';

            PatientTimelineRecorder::record($patient, PatientTimelineEvent::TYPE_PRIMARY_DOCTOR_ASSIGNED, $description);

            return $patient;
        });
    }
}
