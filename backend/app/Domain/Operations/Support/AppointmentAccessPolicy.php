<?php

namespace App\Domain\Operations\Support;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\Appointment;
use App\Models\User;

/**
 * Mirrors DentalChartAccessPolicy's shape: admin-tier and front-desk roles
 * may manage any appointment, but a doctor may only manage their own —
 * they shouldn't be able to cancel or check in another doctor's patient.
 */
class AppointmentAccessPolicy
{
    private const ADMIN_TIER_ROLES = ['Tenant Manager', 'Branch Manager'];

    public static function canView(User $user): bool
    {
        return $user->can(PermissionCatalog::APPOINTMENTS_VIEW);
    }

    public static function canBook(User $user): bool
    {
        return $user->can(PermissionCatalog::APPOINTMENTS_MANAGE);
    }

    public static function canManage(User $user, Appointment $appointment): bool
    {
        if (! $user->can(PermissionCatalog::APPOINTMENTS_MANAGE)) {
            return false;
        }

        if ($user->hasRole(self::ADMIN_TIER_ROLES) || ! $user->hasRole('Doctor')) {
            return true;
        }

        return $user->staff !== null && $user->staff->id === $appointment->staff_id;
    }
}
