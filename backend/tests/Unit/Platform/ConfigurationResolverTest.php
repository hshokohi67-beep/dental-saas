<?php

namespace Tests\Unit\Platform;

use App\Application\Actions\CreateTenant;
use App\Domain\Platform\Support\ConfigurationResolver;
use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Support\TenantContext;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_most_specific_scope_wins(): void
    {
        $this->seed(PlanSeeder::class);

        $tenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک تنظیمات',
            slug: 'config-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر', 'email' => 'owner@config-clinic.example', 'password' => 'password'],
        );
        $branch = Branch::withoutTenantScope()->where('tenant_id', $tenant->id)->firstOrFail();

        $resolver = app(ConfigurationResolver::class);
        $key = 'appointment.default_duration_minutes';

        // Nothing set anywhere yet: falls back to the given default.
        $this->assertSame(30, $resolver->resolve($key, tenant: $tenant, default: 30));

        // Tenant-level override.
        $resolver->set('tenant', $tenant->id, $key, 45);
        $this->assertSame(45, $resolver->resolve($key, tenant: $tenant, default: 30));

        // Branch-level override wins over tenant-level.
        $resolver->set('branch', $branch->id, $key, 60);
        $this->assertSame(60, $resolver->resolve($key, branch: $branch, tenant: $tenant, default: 30));

        // Once we ask without the branch in scope, tenant-level applies again.
        $this->assertSame(45, $resolver->resolve($key, tenant: $tenant, default: 30));
    }

    protected function tearDown(): void
    {
        TenantContext::reset();
        parent::tearDown();
    }
}
