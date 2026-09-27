<?php

namespace App\Domain\Dental\Support;

use App\Domain\Dental\Models\PatientToothCondition;
use App\Domain\Patients\Models\Patient;
use Illuminate\Support\Collection;

/**
 * Assembles the full "Patient 360 → odontogram" payload: every tooth
 * applicable to the patient's chart mode (adult = permanent only, peds =
 * permanent + primary together, business rules §1.7), each with its
 * derived status, plus findings that are not tied to a single tooth
 * (quadrant/arch/whole-mouth — the clean scope model from roadmap Phase 3,
 * replacing legacy's overloaded tooth_number encoding, business rules §1.6).
 */
class OdontogramBuilder
{
    /**
     * @return array{chart_mode: string, teeth: list<array<string, mixed>>, quadrant_findings: list<array<string, mixed>>, arch_findings: list<array<string, mixed>>, whole_mouth_findings: list<array<string, mixed>>}
     */
    public static function build(Patient $patient): array
    {
        $teeth = $patient->chart_mode === 'peds' ? ToothNumber::all() : ToothNumber::permanentTeeth();

        $activeConditions = $patient->toothConditions()->active()->with('condition', 'recordedBy')->get();

        $byTooth = $activeConditions->where('scope_type', 'tooth')->groupBy('tooth_number');

        return [
            'chart_mode' => $patient->chart_mode,
            'teeth' => collect($teeth)
                ->map(fn (ToothNumber $tooth) => self::toothPayload($tooth, $byTooth->get($tooth->fdi, new Collection)))
                ->values()
                ->all(),
            'quadrant_findings' => self::groupedFindings($activeConditions->where('scope_type', 'quadrant'), 'quadrant'),
            'arch_findings' => self::groupedFindings($activeConditions->where('scope_type', 'arch'), 'arch'),
            'whole_mouth_findings' => $activeConditions->where('scope_type', 'whole_mouth')
                ->map(fn (PatientToothCondition $link) => self::conditionPayload($link))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, PatientToothCondition>  $conditionsForTooth
     * @return array<string, mixed>
     */
    private static function toothPayload(ToothNumber $tooth, Collection $conditionsForTooth): array
    {
        return [
            'fdi' => $tooth->fdi,
            'quadrant' => $tooth->quadrant,
            'position' => $tooth->position,
            'dentition' => $tooth->dentition(),
            'arch' => $tooth->arch(),
            'patient_side' => $tooth->patientSide(),
            'screen_side' => $tooth->screenSide(),
            'display_label' => $tooth->displayLabel(),
            'is_anterior' => $tooth->isAnterior(),
            'status' => ToothStatusResolver::resolve($conditionsForTooth),
            'surface_statuses' => ToothStatusResolver::resolveSurfaces($conditionsForTooth),
            'conditions' => $conditionsForTooth->map(fn (PatientToothCondition $link) => self::conditionPayload($link))->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, PatientToothCondition>  $conditions
     * @return list<array<string, mixed>>
     */
    private static function groupedFindings(Collection $conditions, string $groupKey): array
    {
        return $conditions->groupBy($groupKey)
            ->map(fn (Collection $group, $key) => [
                $groupKey => $key,
                'conditions' => $group->map(fn (PatientToothCondition $link) => self::conditionPayload($link))->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function conditionPayload(PatientToothCondition $link): array
    {
        return [
            'id' => $link->id,
            'code' => $link->condition->key,
            'label' => $link->condition->label,
            'surfaces' => $link->surfaces,
            'notes' => $link->notes,
            'recorded_by' => $link->recordedBy?->name,
            'recorded_at' => $link->recorded_at?->toIso8601String(),
        ];
    }
}
