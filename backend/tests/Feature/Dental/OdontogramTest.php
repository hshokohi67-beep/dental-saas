<?php

namespace Tests\Feature\Dental;

use App\Application\Actions\CreateTenant;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\DentalConditionCatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdontogramTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private string $patientId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(DentalConditionCatalogSeeder::class);

        $this->tenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک تست',
            slug: 'test-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر', 'email' => 'manager@test-clinic.example', 'password' => 'password'],
        );

        $this->manager = User::query()->where('tenant_id', $this->tenant->id)->firstOrFail();

        $created = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
        ])->assertCreated();

        $this->patientId = $created->json('data.id');
    }

    public function test_adult_mode_odontogram_has_only_the_32_permanent_teeth(): void
    {
        $response = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");

        $response->assertOk();
        $response->assertJsonPath('data.chart_mode', 'adult');
        $this->assertCount(32, $response->json('data.teeth'));

        $dentitions = collect($response->json('data.teeth'))->pluck('dentition')->unique();
        $this->assertEquals(['permanent'], $dentitions->all());

        $everyToothIsHealthyByDefault = collect($response->json('data.teeth'))->every(
            fn (array $tooth) => $tooth['status']['code'] === 'healthy'
        );
        $this->assertTrue($everyToothIsHealthyByDefault);
    }

    public function test_switching_to_peds_mode_shows_all_52_teeth(): void
    {
        $this->actingAs($this->manager)->patchJson("/api/patients/{$this->patientId}/chart-mode", [
            'chart_mode' => 'peds',
        ])->assertOk();

        $response = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");

        $response->assertOk();
        $response->assertJsonPath('data.chart_mode', 'peds');
        $this->assertCount(52, $response->json('data.teeth'));

        $dentitions = collect($response->json('data.teeth'))->pluck('dentition')->unique()->sort()->values();
        $this->assertEquals(['permanent', 'primary'], $dentitions->all());
    }

    public function test_every_tooth_is_clickable_ie_carries_its_fdi_number_and_a_display_label(): void
    {
        $response = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");

        $tooth11 = collect($response->json('data.teeth'))->firstWhere('fdi', 11);

        $this->assertNotNull($tooth11);
        $this->assertSame('11', $tooth11['display_label']);
        $this->assertSame('left', $tooth11['screen_side']);
    }

    public function test_the_odontogram_is_not_reachable_for_a_patient_in_another_tenant(): void
    {
        $otherTenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک دیگر',
            slug: 'other-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر دو', 'email' => 'owner2@other-clinic.example', 'password' => 'password'],
        );
        $otherManager = User::query()->where('tenant_id', $otherTenant->id)->firstOrFail();

        $response = $this->actingAs($otherManager)->getJson("/api/patients/{$this->patientId}/odontogram");

        $response->assertNotFound();
    }
}
