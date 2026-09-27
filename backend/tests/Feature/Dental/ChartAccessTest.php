<?php

namespace Tests\Feature\Dental;

use App\Application\Actions\CreateTenant;
use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\DentalConditionCatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Preserves the legacy IDOR fix on purpose (business rules §1.8): a doctor
 * may only edit the chart of a patient they are the assigned primary doctor
 * for; an admin/branch manager always can; a secretary has no clinical
 * access to the chart at all, not even to view it.
 */
class ChartAccessTest extends TestCase
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

    private function createDoctor(string $email): User
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => $email, 'email' => $email, 'password' => 'password', 'role' => 'Doctor',
        ])->assertCreated();

        return User::query()->where('email', $email)->firstOrFail();
    }

    private function recordAttempt(User $actor): TestResponse
    {
        $conditionId = DentalConditionCatalog::query()->where('key', 'composite')->firstOrFail()->id;

        return $this->actingAs($actor)->postJson("/api/patients/{$this->patientId}/tooth-conditions", [
            'dental_condition_catalog_id' => $conditionId,
            'scope_type' => 'tooth',
            'tooth_number' => 11,
        ]);
    }

    public function test_a_doctor_not_assigned_to_the_patient_cannot_edit_the_chart(): void
    {
        $doctor = $this->createDoctor('doctor-a@test-clinic.example');

        $this->recordAttempt($doctor)->assertForbidden();
    }

    public function test_a_doctor_assigned_to_the_patient_can_edit_the_chart(): void
    {
        $doctor = $this->createDoctor('doctor-b@test-clinic.example');
        $staffId = Staff::query()->where('user_id', $doctor->id)->firstOrFail()->id;

        $this->actingAs($this->manager)->patchJson("/api/patients/{$this->patientId}/primary-doctor", [
            'staff_id' => $staffId,
        ])->assertOk();

        $this->recordAttempt($doctor)->assertCreated();
    }

    public function test_reassigning_the_primary_doctor_revokes_the_previous_doctors_edit_access(): void
    {
        $doctorA = $this->createDoctor('doctor-c@test-clinic.example');
        $staffAId = Staff::query()->where('user_id', $doctorA->id)->firstOrFail()->id;

        $doctorB = $this->createDoctor('doctor-d@test-clinic.example');
        $staffBId = Staff::query()->where('user_id', $doctorB->id)->firstOrFail()->id;

        $this->actingAs($this->manager)->patchJson("/api/patients/{$this->patientId}/primary-doctor", [
            'staff_id' => $staffAId,
        ])->assertOk();
        $this->recordAttempt($doctorA)->assertCreated();

        $this->actingAs($this->manager)->patchJson("/api/patients/{$this->patientId}/primary-doctor", [
            'staff_id' => $staffBId,
        ])->assertOk();

        $this->recordAttempt($doctorA)->assertForbidden();
        $this->recordAttempt($doctorB)->assertCreated();
    }

    public function test_the_tenant_manager_can_always_edit_the_chart_regardless_of_assignment(): void
    {
        $this->recordAttempt($this->manager)->assertCreated();
    }

    public function test_a_secretary_has_no_access_to_the_chart_at_all(): void
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'منشی', 'email' => 'secretary@test-clinic.example', 'password' => 'password', 'role' => 'Reception',
        ])->assertCreated();
        $secretary = User::query()->where('email', 'secretary@test-clinic.example')->firstOrFail();

        $this->actingAs($secretary)->getJson("/api/patients/{$this->patientId}/odontogram")->assertForbidden();
        $this->recordAttempt($secretary)->assertForbidden();
    }

    public function test_an_assistant_can_view_but_not_edit_the_chart(): void
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دستیار', 'email' => 'assistant@test-clinic.example', 'password' => 'password', 'role' => 'Assistant',
        ])->assertCreated();
        $assistant = User::query()->where('email', 'assistant@test-clinic.example')->firstOrFail();

        $this->actingAs($assistant)->getJson("/api/patients/{$this->patientId}/odontogram")->assertOk();
        $this->recordAttempt($assistant)->assertForbidden();
    }
}
