<?php

namespace App\Domain\Patients\Support;

use App\Domain\Patients\Models\Patient;

/**
 * Clinical alerts are always derived from the patient's recorded conditions
 * and allergies — never a separately maintained field — so they can never
 * drift out of sync (legacy business rule §3.1: alerts must automatically
 * show up everywhere the patient is viewed).
 */
class ClinicalAlertGenerator
{
    /**
     * @return list<array{source: string, label: string, message: string}>
     */
    public static function forPatient(Patient $patient): array
    {
        $alerts = [];

        foreach ($patient->medicalConditions as $link) {
            $condition = $link->condition;

            if ($condition === null || ! $condition->is_active || blank($condition->alert_text)) {
                continue;
            }

            $alerts[] = [
                'source' => 'medical_condition',
                'label' => $condition->label,
                'message' => $condition->alert_text,
            ];
        }

        $severityLabels = ['moderate' => 'متوسط', 'severe' => 'شدید'];

        foreach ($patient->allergies as $allergy) {
            if ($allergy->severity === null || ! array_key_exists($allergy->severity, $severityLabels)) {
                continue;
            }

            $alerts[] = [
                'source' => 'allergy',
                'label' => $allergy->allergen,
                'message' => "حساسیت {$severityLabels[$allergy->severity]} به {$allergy->allergen}؛ در تجویز دارو و درمان احتیاط شود.",
            ];
        }

        return $alerts;
    }
}
