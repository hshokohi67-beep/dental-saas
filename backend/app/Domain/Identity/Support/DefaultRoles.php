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
            ],
            'Doctor' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::STAFF_VIEW,
            ],
            'Assistant' => [
                PermissionCatalog::BRANCHES_VIEW,
            ],
            'Reception' => [
                PermissionCatalog::BRANCHES_VIEW,
                PermissionCatalog::STAFF_VIEW,
            ],
            'Accounting' => [
                PermissionCatalog::BRANCHES_VIEW,
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
