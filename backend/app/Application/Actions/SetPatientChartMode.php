<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\DB;

class SetPatientChartMode
{
    public function execute(Patient $patient, string $chartMode): Patient
    {
        return DB::transaction(function () use ($patient, $chartMode) {
            $patient->update(['chart_mode' => $chartMode]);

            $label = $chartMode === 'peds' ? 'اطفال (شیری+دائمی)' : 'بزرگسال (فقط دائمی)';

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_CHART_MODE_CHANGED,
                "حالت چارت دندانی به «{$label}» تغییر کرد.",
            );

            return $patient;
        });
    }
}
