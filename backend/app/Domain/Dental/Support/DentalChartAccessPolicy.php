<?php

namespace App\Domain\Dental\Support;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\Patient;
use App\Models\User;

/**
 * Legacy IDOR fix, preserved on purpose (business rules §1.8): an admin or
 * branch manager may always edit any patient's chart, but a doctor may only
 * edit the chart of a patient they are the assigned primary doctor for.
 * Viewing has no such ownership restriction — only the `dental.chart.view`
 * permission (assistants/secretaries are still gated by that permission
 * itself, e.g. Reception has none at all).
 */
class DentalChartAccessPolicy
{
    private const ADMIN_TIER_ROLES = ['Tenant Manager', 'Branch Manager'];

    public static function canView(User $user, Patient $patient): bool
    {
        return $user->can(PermissionCatalog::DENTAL_CHART_VIEW);
    }

    public static function canManage(User $user, Patient $patient): bool
    {
        if (! $user->can(PermissionCatalog::DENTAL_CHART_MANAGE)) {
            return false;
        }

        if ($user->hasRole(self::ADMIN_TIER_ROLES)) {
            return true;
        }

        return $patient->primary_doctor_staff_id !== null
            && $user->staff !== null
            && $user->staff->id === $patient->primary_doctor_staff_id;
    }
}
