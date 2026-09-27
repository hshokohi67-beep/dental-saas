<?php

namespace Tests\Feature\Operations;

use App\Application\Actions\CreateTenant;
use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\DentalConditionCatalogSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private User $doctorA;

    private User $doctorB;

    private User $receptionist;

    private string $patientId;

    private string $appointmentId;

    private string $date = '2026-10-03';

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
        $branchId = $this->actingAs($this->manager)->getJson('/api/branches')->json('data.0.id');

        $doctorAStaff = $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دکتر یک', 'email' => 'doctorA@test-clinic.example', 'password' => 'password',
            'branch_id' => $branchId, 'title' => 'دندانپزشک', 'role' => 'Doctor',
        ])->assertCreated()->json('data');
        $this->doctorA = User::query()->where('email', 'doctorA@test-clinic.example')->firstOrFail();

        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دکتر دو', 'email' => 'doctorB@test-clinic.example', 'password' => 'password',
            'branch_id' => $branchId, 'title' => 'دندانپزشک', 'role' => 'Doctor',
        ])->assertCreated();
        $this->doctorB = User::query()->where('email', 'doctorB@test-clinic.example')->firstOrFail();

        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'منشی', 'email' => 'reception@test-clinic.example', 'password' => 'password',
            'branch_id' => $branchId, 'title' => 'پذیرش', 'role' => 'Reception',
        ])->assertCreated();
        $this->receptionist = User::query()->where('email', 'reception@test-clinic.example')->firstOrFail();

        $patient = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
        ])->assertCreated();
        $this->patientId = $patient->json('data.id');

        $this->actingAs($this->manager)->postJson('/api/staff-shifts', [
            'staff_id' => $doctorAStaff['id'],
            'branch_id' => $branchId,
            'day_of_week' => Carbon::parse($this->date)->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ])->assertCreated();

        $appointment = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $doctorAStaff['id'],
            'dental_condition_catalog_id' => DentalConditionCatalog::query()->where('key', 'composite')->firstOrFail()->id,
            'scheduled_at' => "{$this->date} 09:00",
        ])->assertCreated();
        $this->appointmentId = $appointment->json('data.id');
    }

    public function test_the_assigned_doctor_can_check_in_and_complete_their_own_appointment(): void
    {
        $this->actingAs($this->doctorA)->patchJson("/api/appointments/{$this->appointmentId}/check-in")
            ->assertOk()
            ->assertJsonPath('data.status', 'checked_in');

        $this->actingAs($this->doctorA)->patchJson("/api/appointments/{$this->appointmentId}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'appointment_completed',
        ]);
    }

    public function test_a_different_doctor_cannot_manage_this_appointment(): void
    {
        $this->actingAs($this->doctorB)->patchJson("/api/appointments/{$this->appointmentId}/check-in")
            ->assertForbidden();
    }

    public function test_reception_can_manage_any_doctors_appointment(): void
    {
        $this->actingAs($this->receptionist)->patchJson("/api/appointments/{$this->appointmentId}/check-in")
            ->assertOk();
    }

    public function test_cancelling_records_the_reason_and_a_timeline_event(): void
    {
        $this->actingAs($this->receptionist)->patchJson("/api/appointments/{$this->appointmentId}/cancel", [
            'reason' => 'درخواست بیمار',
        ])->assertOk()->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('appointments', [
            'id' => $this->appointmentId,
            'status' => 'cancelled',
            'cancelled_reason' => 'درخواست بیمار',
        ]);

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'appointment_cancelled',
        ]);
    }

    public function test_a_terminal_appointment_cannot_be_transitioned_again(): void
    {
        $this->actingAs($this->receptionist)->patchJson("/api/appointments/{$this->appointmentId}/cancel")->assertOk();

        $this->actingAs($this->receptionist)->patchJson("/api/appointments/{$this->appointmentId}/check-in")
            ->assertUnprocessable();
    }

    public function test_marking_no_show_records_a_timeline_event(): void
    {
        $this->actingAs($this->receptionist)->patchJson("/api/appointments/{$this->appointmentId}/no-show")
            ->assertOk()
            ->assertJsonPath('data.status', 'no_show');

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'appointment_no_show',
        ]);
    }
}
