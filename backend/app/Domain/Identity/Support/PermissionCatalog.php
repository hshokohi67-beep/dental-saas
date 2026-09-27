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
        ];
    }
}
