<?php

namespace App\Domain\Patients\Support;

use App\Domain\Patients\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

/**
 * The legacy system used mobile number as the patient's one true identity
 * key (business rules §"ایجاد/بازیابی پرونده‌ی بیمار"). The new system keeps
 * that as a strong signal but surfaces candidates instead of silently
 * reusing/blocking, so staff can decide to merge (roadmap Phase 2 —
 * "Duplicate Detection").
 *
 * @phpstan-type Criteria array{mobile?: ?string, national_id?: ?string, first_name?: ?string, last_name?: ?string, date_of_birth?: ?string}
 */
class DuplicatePatientFinder
{
    /**
     * @param  Criteria  $criteria
     * @return Collection<int, Patient>
     */
    public static function search(array $criteria, ?string $excludingPatientId = null): Collection
    {
        $mobile = trim((string) ($criteria['mobile'] ?? ''));
        $nationalId = trim((string) ($criteria['national_id'] ?? ''));
        $firstName = trim((string) ($criteria['first_name'] ?? ''));
        $lastName = trim((string) ($criteria['last_name'] ?? ''));
        $dateOfBirth = $criteria['date_of_birth'] ?? null;

        if ($mobile === '' && $nationalId === '' && ($firstName === '' || $lastName === '')) {
            return new Collection;
        }

        return Patient::query()
            ->where('status', '!=', 'merged')
            ->when($excludingPatientId, fn ($query) => $query->where('id', '!=', $excludingPatientId))
            ->where(function ($query) use ($mobile, $nationalId, $firstName, $lastName, $dateOfBirth) {
                if ($mobile !== '') {
                    $query->orWhere('mobile', $mobile);
                }

                if ($nationalId !== '') {
                    $query->orWhere('national_id', $nationalId);
                }

                if ($firstName !== '' && $lastName !== '') {
                    $query->orWhere(function ($nameQuery) use ($firstName, $lastName, $dateOfBirth) {
                        $nameQuery->whereRaw('lower(first_name) = ?', [mb_strtolower($firstName)])
                            ->whereRaw('lower(last_name) = ?', [mb_strtolower($lastName)]);

                        if ($dateOfBirth) {
                            $nameQuery->whereDate('date_of_birth', $dateOfBirth);
                        }
                    });
                }
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
}
