<?php

namespace Tests\Feature\Patients;

use App\Application\Actions\CreateTenant;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTimelineTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

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

        $this->patient = Patient::query()->findOrFail($created->json('data.id'));
    }

    public function test_creating_and_updating_a_patient_appends_to_its_timeline(): void
    {
        $this->travel(1)->seconds();

        $this->actingAs($this->manager)->patchJson("/api/patients/{$this->patient->id}", [
            'notes' => 'یادداشت جدید',
        ])->assertOk();

        $response = $this->actingAs($this->manager)->getJson("/api/patients/{$this->patient->id}/timeline");

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type');

        $this->assertEquals(['updated', 'created'], $types->all());
    }

    public function test_timeline_is_not_reachable_for_a_patient_in_another_tenant(): void
    {
        $otherTenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک دیگر',
            slug: 'other-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر دو', 'email' => 'owner2@other-clinic.example', 'password' => 'password'],
        );
        $otherManager = User::query()->where('tenant_id', $otherTenant->id)->firstOrFail();

        $response = $this->actingAs($otherManager)->getJson("/api/patients/{$this->patient->id}/timeline");

        $response->assertNotFound();
    }
}
