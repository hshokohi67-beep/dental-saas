<?php

namespace App\Domain\Dental\Support;

use App\Domain\Dental\Models\PatientToothCondition;
use Illuminate\Support\Collection;

/**
 * A tooth's displayed status is always *derived* from its recorded
 * conditions, never read from a stored "current status" column — that
 * column existed in legacy but was almost always stale/default (business
 * rules §1.3), so the new system computes it fresh, server-side, every time.
 */
class ToothStatusResolver
{
    private const HEALTHY = [
        'code' => 'healthy',
        'label' => 'سالم',
        'color' => '#FFFFFF',
        'border' => '#C8D4DC',
        'is_dashed' => false,
    ];

    private const NO_DEDICATED_COLOR = [
        'code' => null,
        'label' => 'خدمتی بدون رنگ اختصاصی ثبت شده',
        'color' => null,
        'border' => '#B0BEC5',
        'is_dashed' => true,
    ];

    /**
     * @param  Collection<int, PatientToothCondition>  $activeConditions  active (non-voided) conditions for one tooth, with `condition` eager loaded.
     * @return array{code: ?string, label: string, color: ?string, border: ?string, is_dashed: bool}
     */
    public static function resolve(Collection $activeConditions): array
    {
        if ($activeConditions->isEmpty()) {
            return self::HEALTHY;
        }

        $winner = $activeConditions
            ->map(fn (PatientToothCondition $link) => $link->condition)
            ->filter(fn ($condition) => $condition !== null && $condition->status_priority !== null)
            ->sortBy(fn ($condition) => $condition->status_priority)
            ->first();

        if ($winner === null) {
            return self::NO_DEDICATED_COLOR;
        }

        return [
            'code' => $winner->key,
            'label' => $winner->label,
            'color' => $winner->status_color,
            'border' => $winner->status_border,
            'is_dashed' => false,
        ];
    }
}
