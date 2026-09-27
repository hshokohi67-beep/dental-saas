<?php

namespace App\Domain\Identity\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The initial role set every new tenant gets (master prompt §14). Tenants may
 * add further custom roles later — this class only seeds the starting point.
 */
class DefaultRoles
{
    /**
     * @return array<string, list<string>>
     */
    public static function map(): array
    {
        return [
            'Tenant Manager' => PermissionCatalog::all(),
            'Branch Manager' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::STAFF_VIEW,
                PermissionCatalog::STAFF_MANAGE,
                PermissionCatalog::PATIENTS_VIEW,
                PermissionCatalog::PATIENTS_MANAGE,
                PermissionCatalog::PATIENTS_MERGE,
                PermissionCatalog::DENTAL_CHART_VIEW,
                PermissionCatalog::DENTAL_CHART_MANAGE,
                PermissionCatalog::DENTAL_CHART_ASSIGN_DOCTOR,
            ],
            // Doctor gets the chart permissions, but DentalChartAccessPolicy still
            // requires them to be the patient's assigned doctor to actually edit
            // (legacy IDOR fix, business rules §1.8) — the permission alone is not enough.
            'Doctor' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::STAFF_VIEW,
                PermissionCatalog::PATIENTS_VIEW,
                PermissionCatalog::PATIENTS_MANAGE,
                PermissionCatalog::PATIENTS_MEDICAL_VIEW,
                PermissionCatalog::PATIENTS_MEDICAL_MANAGE,
                PermissionCatalog::DENTAL_CHART_VIEW,
                PermissionCatalog::DENTAL_CHART_MANAGE,
            ],
            'Assistant' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::PATIENTS_VIEW,
                PermissionCatalog::PATIENTS_MEDICAL_VIEW,
                PermissionCatalog::DENTAL_CHART_VIEW,
            ],
            // Secretary: intentionally excludes patients.medical.* — the front-desk role
            // never sees clinical tabs (master prompt / legacy business rule §1.8).
            'Reception' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::STAFF_VIEW,
                PermissionCatalog::PATIENTS_VIEW,
                PermissionCatalog::PATIENTS_MANAGE,
            ],
            'Accounting' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::PATIENTS_VIEW,
            ],
            'Inventory' => [
                PermissionCatalog::BRANCHES_VIEW,
            ],
            'Laboratory' => [
                PermissionCatalog::BRANCHES_VIEW,
            ],
        ];
    }

    /**
     * Seed the default roles + their permissions scoped to one tenant (team).
     * Permissions are global (guard-scoped, not team-scoped) — only roles and
     * a model's role/permission assignments are team-scoped by spatie's teams feature.
     */
    public static function seedForTenant(string $tenantId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::map() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }
}
