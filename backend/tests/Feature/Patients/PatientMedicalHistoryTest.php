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

class PatientMedicalHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private User $doctor;

    private User $secretary;

    private Patient $patient;

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

        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دکتر رضایی', 'email' => 'doctor@test-clinic.example', 'password' => 'password', 'role' => 'Doctor',
        ])->assertCreated();
        $this->doctor = User::query()->where('email', 'doctor@test-clinic.example')->firstOrFail();

        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'منشی', 'email' => 'secretary@test-clinic.example', 'password' => 'password', 'role' => 'Reception',
        ])->assertCreated();
        $this->secretary = User::query()->where('email', 'secretary@test-clinic.example')->firstOrFail();

        $created = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
        ])->assertCreated();

        $this->patient = Patient::query()->findOrFail($created->json('data.id'));
    }

    public function test_doctor_can_record_medical_condition_allergy_and_medication(): void
    {
        $diabetes = MedicalCondition::query()->where('key', 'diabetes')->firstOrFail();

        $this->actingAs($this->doctor)->postJson("/api/patients/{$this->patient->id}/medical-conditions", [
            'medical_condition_id' => $diabetes->id,
        ])->assertCreated();

        $this->actingAs($this->doctor)->postJson("/api/patients/{$this->patient->id}/allergies", [
            'allergen' => 'پنی‌سیلین', 'severity' => 'severe',
        ])->assertCreated();

        $this->actingAs($this->doctor)->postJson("/api/patients/{$this->patient->id}/medications", [
            'name' => 'آموکسی‌سیلین', 'dosage' => '500mg',
        ])->assertCreated();

        $response = $this->actingAs($this->doctor)->getJson("/api/patients/{$this->patient->id}");

        $response->assertOk();
        $response->assertJsonPath('data.medical.conditions.0.label', 'دیابت');
        $response->assertJsonPath('data.medical.allergies.0.allergen', 'پنی‌سیلین');
        $response->assertJsonPath('data.medical.medications.0.name', 'آموکسی‌سیلین');

        $alerts = collect($response->json('data.medical.alerts'))->pluck('message');
        $this->assertTrue($alerts->contains('کنترل قند خون قبل از درمان'));
        $this->assertTrue($alerts->contains(fn ($message) => str_contains($message, 'پنی‌سیلین')));
    }

    public function test_secretary_cannot_see_or_manage_the_medical_tab(): void
    {
        $response = $this->actingAs($this->secretary)->getJson("/api/patients/{$this->patient->id}");

        $response->assertOk();
        $this->assertNull($response->json('data.medical'));

        $diabetes = MedicalCondition::query()->where('key', 'diabetes')->firstOrFail();

        $this->actingAs($this->secretary)->postJson("/api/patients/{$this->patient->id}/medical-conditions", [
            'medical_condition_id' => $diabetes->id,
        ])->assertForbidden();
    }

    public function test_removing_a_medical_condition_records_a_timeline_event(): void
    {
        $diabetes = MedicalCondition::query()->where('key', 'diabetes')->firstOrFail();

        $link = $this->actingAs($this->doctor)->postJson("/api/patients/{$this->patient->id}/medical-conditions", [
            'medical_condition_id' => $diabetes->id,
        ])->assertCreated();

        $linkId = $link->json('data.id');

        $this->actingAs($this->doctor)
            ->deleteJson("/api/patients/{$this->patient->id}/medical-conditions/{$linkId}")
            ->assertNoContent();

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patient->id,
            'type' => 'medical_condition_removed',
        ]);
    }
}
