<?php

namespace Tests\Feature\Tenancy;

use App\Application\Actions\CreateTenant;
use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Support\TenantContext;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every new tenant-owned API endpoint must be covered here (see backend CLAUDE.md convention).
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenantWithOwner(string $slug): User
    {
        $tenant = app(CreateTenant::class)->execute(
            tenantName: "کلینیک {$slug}",
            slug: $slug,
            planKey: 'practice',
            owner: ['name' => "مدیر {$slug}", 'email' => "owner-{$slug}@example.com", 'password' => 'password'],
        );

        return User::query()->where('tenant_id', $tenant->id)->firstOrFail();
    }

    protected function setUp(): void
    {
        parent::setUp();
        TenantContext::reset();
        $this->seed(PlanSeeder::class);
    }

    public function test_user_can_only_see_their_own_tenants_branches_via_api(): void
    {
        $ownerA = $this->makeTenantWithOwner('alpha');
        $ownerB = $this->makeTenantWithOwner('beta');

        Branch::withoutTenantScope()->create(['tenant_id' => $ownerB->tenant_id, 'name' => 'شعبه دوم بتا']);

        $response = $this->actingAs($ownerA)->getJson('/api/branches');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('شعبه اصلی'));
        $this->assertFalse($names->contains('شعبه دوم بتا'));
        $this->assertCount(1, $names);
    }

    public function test_direct_eloquent_queries_are_scoped_to_the_current_tenant_context(): void
    {
        $ownerA = $this->makeTenantWithOwner('alpha');
        $ownerB = $this->makeTenantWithOwner('beta');

        TenantContext::set($ownerA->tenant);
        $visible = Branch::query()->pluck('tenant_id')->unique();

        $this->assertEquals([$ownerA->tenant_id], $visible->all());

        TenantContext::set($ownerB->tenant);
        $visible = Branch::query()->pluck('tenant_id')->unique();

        $this->assertEquals([$ownerB->tenant_id], $visible->all());
    }

    public function test_a_user_without_a_tenant_is_rejected_from_tenant_scoped_routes(): void
    {
        $superAdmin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => false]);

        $response = $this->actingAs($superAdmin)->getJson('/api/branches');

        $response->assertForbidden();
    }

    public function test_creating_a_branch_auto_fills_the_current_tenant_and_cannot_be_spoofed(): void
    {
        $ownerA = $this->makeTenantWithOwner('alpha');
        $ownerB = $this->makeTenantWithOwner('beta');

        $response = $this->actingAs($ownerA)->postJson('/api/branches', [
            'name' => 'شعبه جدید آلفا',
            // even if a client tried to send tenant_id, StoreBranchRequest doesn't accept it as fillable input.
            'tenant_id' => $ownerB->tenant_id,
        ]);

        $response->assertCreated();

        $branch = Branch::withoutTenantScope()->where('name', 'شعبه جدید آلفا')->firstOrFail();
        $this->assertSame($ownerA->tenant_id, $branch->tenant_id);
    }
}
