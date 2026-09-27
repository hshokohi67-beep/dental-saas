<?php

namespace App\Domain\Identity\Support;

/**
 * The single source of truth for permission keys. New domains add their keys
 * here as they're built — do not stringly-type permission names elsewhere.
 */
class PermissionCatalog
{
    public const BRANCHES_VIEW = 'branches.view';

    public const BRANCHES_MANAGE = 'branches.manage';

    public const STAFF_VIEW = 'staff.view';

    public const STAFF_MANAGE = 'staff.manage';

    public const ROLES_MANAGE = 'roles.manage';

    public const PATIENTS_VIEW = 'patients.view';

    public const PATIENTS_MANAGE = 'patients.manage';

    public const PATIENTS_MERGE = 'patients.merge';

    public const PATIENTS_MEDICAL_VIEW = 'patients.medical.view';

    public const PATIENTS_MEDICAL_MANAGE = 'patients.medical.manage';

    public const DENTAL_CHART_VIEW = 'dental.chart.view';

    public const DENTAL_CHART_MANAGE = 'dental.chart.manage';

    public const DENTAL_CHART_ASSIGN_DOCTOR = 'dental.chart.assign_doctor';

    public const SCHEDULING_ROOMS_MANAGE = 'scheduling.rooms.manage';

    public const SCHEDULING_SHIFTS_VIEW = 'scheduling.shifts.view';

    public const SCHEDULING_SHIFTS_MANAGE = 'scheduling.shifts.manage';

    public const APPOINTMENTS_VIEW = 'appointments.view';

    public const APPOINTMENTS_MANAGE = 'appointments.manage';

    public const APPOINTMENTS_WAITLIST_MANAGE = 'appointments.waitlist.manage';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::BRANCHES_VIEW,
            self::BRANCHES_MANAGE,
            self::STAFF_VIEW,
            self::STAFF_MANAGE,
            self::ROLES_MANAGE,
            self::PATIENTS_VIEW,
            self::PATIENTS_MANAGE,
            self::PATIENTS_MERGE,
            self::PATIENTS_MEDICAL_VIEW,
            self::PATIENTS_MEDICAL_MANAGE,
            self::DENTAL_CHART_VIEW,
            self::DENTAL_CHART_MANAGE,
            self::DENTAL_CHART_ASSIGN_DOCTOR,
            self::SCHEDULING_ROOMS_MANAGE,
            self::SCHEDULING_SHIFTS_VIEW,
            self::SCHEDULING_SHIFTS_MANAGE,
            self::APPOINTMENTS_VIEW,
            self::APPOINTMENTS_MANAGE,
            self::APPOINTMENTS_WAITLIST_MANAGE,
        ];
    }
}
