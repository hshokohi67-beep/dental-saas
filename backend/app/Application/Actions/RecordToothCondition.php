<?php

namespace App\Application\Actions;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Dental\Models\PatientToothCondition;
use App\Domain\Dental\Support\ToothNumber;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordToothCondition
{
    /**
     * @param  array{scope_type: string, tooth_number?: ?int, quadrant?: ?int, arch?: ?string, surfaces?: ?array, notes?: ?string}  $data
     */
    public function execute(Patient $patient, DentalConditionCatalog $condition, array $data): PatientToothCondition
    {
        $this->assertScopeMatchesCatalog($condition, $data['scope_type']);
        $this->assertDentitionMatches($condition, $data);

        return DB::transaction(function () use ($patient, $condition, $data) {
            $link = $patient->toothConditions()->create([
                'dental_condition_catalog_id' => $condition->id,
                'scope_type' => $data['scope_type'],
                'tooth_number' => $data['tooth_number'] ?? null,
                'quadrant' => $data['quadrant'] ?? null,
                'arch' => $data['arch'] ?? null,
                'surfaces' => $data['surfaces'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
                'recorded_at' => now(),
            ]);

            $where = match ($data['scope_type']) {
                'tooth' => "دندان {$data['tooth_number']}",
                'quadrant' => "کوادرانت {$data['quadrant']}",
                'arch' => $data['arch'] === 'upper' ? 'فک بالا' : 'فک پایین',
                default => 'کل دهان',
            };

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_TOOTH_CONDITION_RECORDED,
                "«{$condition->label}» روی {$where} ثبت شد.",
                ['patient_tooth_condition_id' => $link->id, 'condition_key' => $condition->key],
            );

            return $link->load('condition');
        });
    }

    private function assertScopeMatchesCatalog(DentalConditionCatalog $condition, string $scopeType): void
    {
        if (! $condition->isApplicableToScope($scopeType)) {
            throw ValidationException::withMessages([
                'scope_type' => "کد «{$condition->key}» برای این نوع scope ({$scopeType}) قابل ثبت نیست.",
            ]);
        }
    }

    /**
     * @param  array{scope_type: string, tooth_number?: ?int}  $data
     */
    private function assertDentitionMatches(DentalConditionCatalog $condition, array $data): void
    {
        if ($data['scope_type'] !== 'tooth') {
            return;
        }

        $dentition = ToothNumber::fromFdi($data['tooth_number'])->dentition();

        if (! $condition->isApplicableToDentition($dentition)) {
            throw ValidationException::withMessages([
                'dental_condition_catalog_id' => "کد «{$condition->label}» برای دندان‌های {$dentition} کاربرد ندارد.",
            ]);
        }
    }
}
