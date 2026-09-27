<?php

namespace App\Application\Actions;

use App\Domain\Identity\Models\Staff;
use App\Domain\Identity\Support\DefaultRoles;
use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Models\Subscription;
use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Onboards a brand-new tenant: the tenant itself, its main branch, a trial
 * subscription, the default role set, and its first user (Tenant Manager).
 */
class CreateTenant
{
    /**
     * @param  array{name: string, email: string, password: string}  $owner
     */
    public function execute(string $tenantName, string $slug, string $planKey, array $owner): Tenant
    {
        return DB::transaction(function () use ($tenantName, $slug, $planKey, $owner) {
            $tenant = Tenant::query()->create([
                'name' => $tenantName,
                'slug' => $slug,
                'status' => 'active',
            ]);

            $mainBranch = Branch::withoutTenantScope()->create([
                'tenant_id' => $tenant->id,
                'name' => 'شعبه اصلی',
                'is_main' => true,
            ]);

            $plan = Plan::query()->where('key', $planKey)->firstOrFail();

            Subscription::withoutTenantScope()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'starts_at' => now(),
            ]);

            DefaultRoles::seedForTenant($tenant->id);

            $user = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $owner['name'],
                'email' => $owner['email'],
                'password' => $owner['password'],
            ]);

            Staff::withoutTenantScope()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'branch_id' => $mainBranch->id,
                'title' => 'مدیر کلینیک',
                'status' => 'active',
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
            $user->assignRole('Tenant Manager');

            return $tenant;
        });
    }
}
