<?php

namespace Tests\Feature\Platform;

use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_super_admin_can_create_a_tenant_with_owner_and_default_roles(): void
    {
        $superAdmin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);

        $response = $this->actingAs($superAdmin)->postJson('/api/platform/tenants', [
            'name' => 'کلینیک نمونه',
            'slug' => 'namooneh',
            'plan_key' => 'practice',
            'owner_name' => 'دکتر نمونه',
            'owner_email' => 'owner@namooneh.example',
            'owner_password' => 'password123',
        ]);

        $response->assertCreated();

        $tenant = Tenant::query()->where('slug', 'namooneh')->firstOrFail();
        $owner = User::query()->where('email', 'owner@namooneh.example')->firstOrFail();

        $this->assertSame($tenant->id, $owner->tenant_id);
        $this->assertTrue($owner->hasRole('Tenant Manager'));
    }

    public function test_a_regular_tenant_user_cannot_create_tenants(): void
    {
        $regular = User::factory()->create(['tenant_id' => null, 'is_super_admin' => false]);

        $response = $this->actingAs($regular)->postJson('/api/platform/tenants', [
            'name' => 'کلینیک نمونه',
            'slug' => 'namooneh-2',
            'plan_key' => 'practice',
            'owner_name' => 'دکتر نمونه',
            'owner_email' => 'owner2@namooneh.example',
            'owner_password' => 'password123',
        ]);

        $response->assertForbidden();
    }
}
