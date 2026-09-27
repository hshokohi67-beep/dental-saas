<?php

namespace Tests\Feature\Dental;

use App\Application\Actions\CreateTenant;
use App\Models\User;
use Database\Seeders\DentalConditionCatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The "Contextual Treatment Picker" (roadmap Phase 3): only codes applicable
 * to what was actually selected on the odontogram should come back.
 */
class DentalConditionCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(DentalConditionCatalogSeeder::class);

        $tenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک تست',
            slug: 'test-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر', 'email' => 'manager@test-clinic.example', 'password' => 'password'],
        );

        $this->manager = User::query()->where('tenant_id', $tenant->id)->firstOrFail();
    }

    public function test_lists_the_full_catalog_by_default(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/dental-conditions');

        $response->assertOk();
        $this->assertCount(46, $response->json('data'));
    }

    public function test_filters_to_only_tooth_scoped_codes(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/dental-conditions?scope=tooth');

        $response->assertOk();
        $scopes = collect($response->json('data'))->pluck('scope')->unique();
        $this->assertEquals(['tooth'], $scopes->all());
    }

    public function test_filters_to_only_codes_applicable_to_primary_teeth(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/dental-conditions?dentition=primary');

        $response->assertOk();
        $keys = collect($response->json('data'))->pluck('key');

        $this->assertTrue($keys->contains('pulpotomy'));
        $this->assertTrue($keys->contains('composite'));
        $this->assertFalse($keys->contains('veneer'));
        $this->assertFalse($keys->contains('implant'));
    }

    public function test_filters_to_only_half_arch_codes(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/dental-conditions?scope=half_arch');

        $response->assertOk();
        $keys = collect($response->json('data'))->pluck('key');

        $this->assertTrue($keys->contains('scaling_half'));
        $this->assertFalse($keys->contains('composite'));
    }
}
