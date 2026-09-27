<?php

namespace App\Application\Actions;

use App\Domain\Identity\Models\Staff;
use App\Domain\Tenancy\Support\TenantContext;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds a new staff member (user + staff record + role) to the *current*
 * tenant. Must run inside a request that already went through ResolveTenant.
 */
class CreateStaffMember
{
    /**
     * @param  array{name: string, email: string, password: string}  $user
     */
    public function execute(array $user, ?string $branchId, string $title, string $roleName): Staff
    {
        $tenant = TenantContext::get();
        abort_unless($tenant !== null, 500, 'تنانت جاری مشخص نیست.');

        return DB::transaction(function () use ($user, $branchId, $title, $roleName, $tenant) {
            $createdUser = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
            ]);

            $staff = Staff::query()->create([
                'user_id' => $createdUser->id,
                'branch_id' => $branchId,
                'title' => $title,
                'status' => 'active',
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
            $createdUser->assignRole($roleName);

            return $staff;
        });
    }
}
