<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientAllergy;
use App\Domain\Patients\Models\PatientMedicalCondition;
use App\Domain\Patients\Models\PatientMedication;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Merges a duplicate patient record into the surviving one (roadmap Phase 2
 * — "Merge"). No hard delete: the duplicate is kept, marked `merged`, and
 * pointed at the survivor via `merged_into_id` (target architecture §5 — no
 * hard delete for patient/clinical records, only reversal/archive).
 */
class MergePatients
{
    public function execute(Patient $survivor, Patient $duplicate): Patient
    {
        if ($survivor->id === $duplicate->id) {
            throw ValidationException::withMessages(['duplicate_patient_id' => 'بیمار اصلی و بیمار تکراری نمی‌توانند یکسان باشند.']);
        }

        if ($survivor->status === 'merged' || $duplicate->status === 'merged') {
            throw ValidationException::withMessages(['duplicate_patient_id' => 'یکی از این دو پرونده قبلاً ادغام شده است.']);
        }

        return DB::transaction(function () use ($survivor, $duplicate) {
            $this->moveMedicalConditions($survivor, $duplicate);
            $this->moveAllergies($survivor, $duplicate);

            PatientMedication::query()->where('patient_id', $duplicate->id)->update(['patient_id' => $survivor->id]);

            $this->fillGaps($survivor, $duplicate);

            PatientTimelineRecorder::record(
                $survivor,
                PatientTimelineEvent::TYPE_MERGED_FROM,
                "پرونده‌ی تکراری «{$duplicate->fullName()}» ({$duplicate->mobile}) در این پرونده ادغام شد.",
                ['duplicate_patient_id' => $duplicate->id],
            );

            PatientTimelineRecorder::record(
                $duplicate,
                PatientTimelineEvent::TYPE_MERGED_INTO,
                "این پرونده در پرونده‌ی «{$survivor->fullName()}» ({$survivor->mobile}) ادغام شد.",
                ['survivor_patient_id' => $survivor->id],
            );

            PatientTimelineEvent::query()->where('patient_id', $duplicate->id)->update(['patient_id' => $survivor->id]);

            $duplicate->update(['status' => 'merged', 'merged_into_id' => $survivor->id]);

            return $survivor->refresh();
        });
    }

    private function moveMedicalConditions(Patient $survivor, Patient $duplicate): void
    {
        $existingConditionIds = $survivor->medicalConditions()->pluck('medical_condition_id')->all();

        PatientMedicalCondition::query()->where('patient_id', $duplicate->id)
            ->whereIn('medical_condition_id', $existingConditionIds)
            ->delete();

        PatientMedicalCondition::query()->where('patient_id', $duplicate->id)->update(['patient_id' => $survivor->id]);
    }

    private function moveAllergies(Patient $survivor, Patient $duplicate): void
    {
        $existingAllergens = $survivor->allergies()->pluck('allergen')
            ->map(fn (string $allergen) => mb_strtolower(trim($allergen)))
            ->all();

        PatientAllergy::query()->where('patient_id', $duplicate->id)->get()
            ->each(function (PatientAllergy $allergy) use ($survivor, $existingAllergens) {
                if (in_array(mb_strtolower(trim($allergy->allergen)), $existingAllergens, true)) {
                    $allergy->delete();
                } else {
                    $allergy->update(['patient_id' => $survivor->id]);
                }
            });
    }

    private function fillGaps(Patient $survivor, Patient $duplicate): void
    {
        $gaps = [];

        foreach (['national_id', 'gender', 'date_of_birth', 'branch_id'] as $field) {
            if (blank($survivor->{$field}) && ! blank($duplicate->{$field})) {
                $gaps[$field] = $duplicate->{$field};
            }
        }

        if (blank($survivor->notes) && ! blank($duplicate->notes)) {
            $gaps['notes'] = $duplicate->notes;
        } elseif (! blank($duplicate->notes) && $duplicate->notes !== $survivor->notes) {
            $gaps['notes'] = trim(($survivor->notes ?? '')."\n".$duplicate->notes);
        }

        if ($gaps !== []) {
            $survivor->update($gaps);
        }
    }
}
