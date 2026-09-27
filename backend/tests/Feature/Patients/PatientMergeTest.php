<?php

namespace Tests\Feature\Patients;

use App\Application\Actions\CreateTenant;
use App\Domain\Patients\Models\MedicalCondition;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\MedicalConditionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientMergeTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private Patient $survivor;

    private Patient $duplicate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(MedicalConditionSeeder::class);

        $this->tenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک تست',
            slug: 'test-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر', 'email' => 'manager@test-clinic.example', 'password' => 'password'],
        );

        $this->manager = User::query()->where('tenant_id', $this->tenant->id)->firstOrFail();

        $survivorResponse = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
        ])->assertCreated();
        $this->survivor = Patient::query()->findOrFail($survivorResponse->json('data.id'));

        $duplicateResponse = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
            'national_id' => '1112223334',
        ])->assertCreated();
        $this->duplicate = Patient::query()->findOrFail($duplicateResponse->json('data.id'));

        $diabetes = MedicalCondition::query()->where('key', 'diabetes')->firstOrFail();
        $this->actingAs($this->manager)->postJson("/api/patients/{$this->duplicate->id}/medical-conditions", [
            'medical_condition_id' => $diabetes->id,
        ])->assertCreated();

        $this->actingAs($this->manager)->postJson("/api/patients/{$this->duplicate->id}/allergies", [
            'allergen' => 'پنی‌سیلین', 'severity' => 'severe',
        ])->assertCreated();
    }

    public function test_manager_can_merge_a_duplicate_patient_into_the_survivor(): void
    {
        $response = $this->actingAs($this->manager)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $this->survivor->id,
            'duplicate_patient_id' => $this->duplicate->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.national_id', '1112223334');
        $response->assertJsonPath('data.medical.conditions.0.label', 'دیابت');
        $response->assertJsonPath('data.medical.allergies.0.allergen', 'پنی‌سیلین');

        $this->assertDatabaseHas('patients', [
            'id' => $this->duplicate->id,
            'status' => 'merged',
            'merged_into_id' => $this->survivor->id,
        ]);

        $this->assertDatabaseHas('patient_medical_conditions', [
            'patient_id' => $this->survivor->id,
        ]);

        $this->assertDatabaseMissing('patient_medical_conditions', [
            'patient_id' => $this->duplicate->id,
        ]);

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->survivor->id,
            'type' => 'merged_from',
        ]);
    }

    public function test_a_merged_patient_is_no_longer_reachable_as_an_active_record_in_search(): void
    {
        $this->actingAs($this->manager)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $this->survivor->id,
            'duplicate_patient_id' => $this->duplicate->id,
        ])->assertOk();

        $response = $this->actingAs($this->manager)->getJson('/api/patients?q=09121234567');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonPath('data.0.id', $this->survivor->id);
    }

    public function test_a_patient_cannot_be_merged_into_itself(): void
    {
        $response = $this->actingAs($this->manager)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $this->survivor->id,
            'duplicate_patient_id' => $this->survivor->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_an_already_merged_patient_cannot_be_merged_again(): void
    {
        $this->actingAs($this->manager)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $this->survivor->id,
            'duplicate_patient_id' => $this->duplicate->id,
        ])->assertOk();

        $thirdResponse = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'رضا', 'last_name' => 'احمدی', 'mobile' => '09123456789',
        ])->assertCreated();
        $third = Patient::query()->findOrFail($thirdResponse->json('data.id'));

        $response = $this->actingAs($this->manager)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $third->id,
            'duplicate_patient_id' => $this->duplicate->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_a_role_without_patients_merge_permission_cannot_merge(): void
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'منشی', 'email' => 'secretary@test-clinic.example', 'password' => 'password', 'role' => 'Reception',
        ])->assertCreated();
        $secretary = User::query()->where('email', 'secretary@test-clinic.example')->firstOrFail();

        $response = $this->actingAs($secretary)->postJson('/api/patients/merge', [
            'survivor_patient_id' => $this->survivor->id,
            'duplicate_patient_id' => $this->duplicate->id,
        ]);

        $response->assertForbidden();
    }
}
