<?php

namespace Tests\Feature\Dental;

use App\Application\Actions\CreateTenant;
use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\DentalConditionCatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToothConditionTest extends TestCase
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

    private function conditionId(string $key): string
    {
        return DentalConditionCatalog::query()->where('key', $key)->firstOrFail()->id;
    }

    public function test_a_composite_filling_can_be_recorded_on_a_permanent_tooth(): void
    {
        $response = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('composite'),
            'scope_type' => 'tooth',
            'tooth_number' => 16,
            'surfaces' => ['occlusal', 'mesial'],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.condition.key', 'composite');

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $tooth16 = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 16);

        $this->assertSame('composite', $tooth16['status']['code']);
        $this->assertSame('#E8F4FC', $tooth16['status']['color']);

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'tooth_condition_recorded',
        ]);
    }

    public function test_a_crown_takes_priority_over_an_earlier_composite_on_the_same_tooth(): void
    {
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('composite'),
            'scope_type' => 'tooth',
            'tooth_number' => 21,
        ])->assertCreated();

        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('crown'),
            'scope_type' => 'tooth',
            'tooth_number' => 21,
        ])->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $tooth21 = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 21);

        $this->assertSame('crown', $tooth21['status']['code']);
        $this->assertCount(2, $tooth21['conditions']);
    }

    public function test_a_tooth_with_only_diagnostic_codes_gets_the_dashed_no_color_state_not_healthy(): void
    {
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('suspect_endo'),
            'scope_type' => 'tooth',
            'tooth_number' => 26,
        ])->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $tooth26 = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 26);

        $this->assertNull($tooth26['status']['code']);
        $this->assertTrue($tooth26['status']['is_dashed']);
    }

    public function test_voiding_a_condition_returns_the_tooth_to_its_previous_status(): void
    {
        $created = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('composite'),
            'scope_type' => 'tooth',
            'tooth_number' => 17,
        ])->assertCreated();

        $linkId = $created->json('data.id');

        $this->actingAs($this->manager)
            ->deleteJson("/api/patients/{$this->patientId}/tooth-conditions/{$linkId}")
            ->assertNoContent();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $tooth17 = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 17);

        $this->assertSame('healthy', $tooth17['status']['code']);

        $this->assertDatabaseMissing('patient_tooth_conditions', ['id' => $linkId, 'voided_at' => null]);
        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'tooth_condition_voided',
        ]);
    }

    public function test_surface_specific_conditions_only_color_their_own_surfaces(): void
    {
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('composite'),
            'scope_type' => 'tooth',
            'tooth_number' => 26,
            'surfaces' => ['mesial', 'occlusal'],
        ])->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $surfaces = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 26)['surface_statuses'];

        $this->assertSame('composite', $surfaces['mesial']['code']);
        $this->assertSame('composite', $surfaces['occlusal']['code']);
        $this->assertSame('healthy', $surfaces['distal']['code']);
        $this->assertSame('healthy', $surfaces['buccal']['code']);
        $this->assertSame('healthy', $surfaces['lingual']['code']);
    }

    public function test_a_whole_tooth_condition_with_no_surfaces_colors_every_surface(): void
    {
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('crown'),
            'scope_type' => 'tooth',
            'tooth_number' => 27,
        ])->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $surfaces = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 27)['surface_statuses'];

        foreach (['mesial', 'distal', 'occlusal', 'buccal', 'lingual'] as $surface) {
            $this->assertSame('crown', $surfaces[$surface]['code']);
        }
    }

    public function test_an_incisal_surface_is_treated_as_the_occlusal_center_region(): void
    {
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('composite'),
            'scope_type' => 'tooth',
            'tooth_number' => 11,
            'surfaces' => ['incisal'],
        ])->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $tooth = collect($odontogram->json('data.teeth'))->firstWhere('fdi', 11);

        $this->assertTrue($tooth['is_anterior']);
        $this->assertSame('composite', $tooth['surface_statuses']['occlusal']['code']);
        $this->assertSame('healthy', $tooth['surface_statuses']['mesial']['code']);
    }

    public function test_a_permanent_only_code_cannot_be_recorded_on_a_primary_tooth(): void
    {
        $response = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('veneer'),
            'scope_type' => 'tooth',
            'tooth_number' => 61,
        ]);

        $response->assertUnprocessable();
    }

    public function test_a_half_arch_finding_only_needs_a_quadrant_not_a_tooth(): void
    {
        $response = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('scaling_half'),
            'scope_type' => 'quadrant',
            'quadrant' => 2,
        ]);

        $response->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $this->assertCount(1, $odontogram->json('data.quadrant_findings'));
        $this->assertSame(2, $odontogram->json('data.quadrant_findings.0.quadrant'));
    }

    public function test_a_whole_mouth_finding_needs_neither_tooth_nor_quadrant(): void
    {
        $response = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('panoramic'),
            'scope_type' => 'whole_mouth',
        ]);

        $response->assertCreated();

        $odontogram = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patientId}/odontogram");
        $this->assertCount(1, $odontogram->json('data.whole_mouth_findings'));
    }

    public function test_a_whole_mouth_only_code_cannot_be_recorded_against_a_single_tooth(): void
    {
        $response = $this->actingAs($this->manager)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $this->conditionId('panoramic'),
            'scope_type' => 'tooth',
            'tooth_number' => 11,
        ]);

        $response->assertUnprocessable();
    }
}
