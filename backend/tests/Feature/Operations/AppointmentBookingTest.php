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

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    private string $branchId;

    private string $staffId;

    private string $patientId;

    /** A fixed future Saturday so shift day-of-week math never depends on "today". */
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
        $this->branchId = $this->actingAs($this->manager)->getJson('/api/branches')->json('data.0.id');

        $doctor = $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دکتر یک', 'email' => 'doctor1@test-clinic.example', 'password' => 'password',
            'branch_id' => $this->branchId, 'title' => 'دندانپزشک', 'role' => 'Doctor',
        ])->assertCreated();
        $this->staffId = $doctor->json('data.id');

        $patient = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121234567',
        ])->assertCreated();
        $this->patientId = $patient->json('data.id');
    }

    private function serviceId(string $key): string
    {
        return DentalConditionCatalog::query()->where('key', $key)->firstOrFail()->id;
    }

    private function createShift(array $overrides = []): array
    {
        $payload = array_merge([
            'staff_id' => $this->staffId,
            'branch_id' => $this->branchId,
            'day_of_week' => Carbon::parse($this->date)->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ], $overrides);

        return $this->actingAs($this->manager)->postJson('/api/staff-shifts', $payload)
            ->assertCreated()
            ->json('data');
    }

    public function test_availability_reflects_the_configured_shift(): void
    {
        $this->createShift();

        $response = $this->actingAs($this->manager)->getJson(
            "/api/appointments/availability?staff_id={$this->staffId}&dental_condition_catalog_id={$this->serviceId('composite')}&date={$this->date}"
        );

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
        $this->assertSame('09:00', Carbon::parse($response->json('data.0.starts_at'))->format('H:i'));
    }

    public function test_a_service_with_no_configured_duration_has_no_availability(): void
    {
        $this->createShift();

        $response = $this->actingAs($this->manager)->getJson(
            "/api/appointments/availability?staff_id={$this->staffId}&dental_condition_catalog_id={$this->serviceId('specialist_tx')}&date={$this->date}"
        );

        $response->assertOk();
        $this->assertSame([], $response->json('data'));
    }

    public function test_booking_a_suggested_slot_succeeds(): void
    {
        $this->createShift();

        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 09:00",
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'booked');

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $this->patientId,
            'type' => 'appointment_booked',
        ]);
    }

    public function test_booking_rejects_an_overlapping_slot(): void
    {
        $this->createShift();

        $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 09:00",
        ])->assertCreated();

        // composite is 30 minutes — 09:15 overlaps the 09:00-09:30 appointment
        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 09:15",
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('scheduled_at');
    }

    public function test_booking_rejects_a_time_outside_any_shift(): void
    {
        $this->createShift(); // 09:00-12:00

        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 14:00",
        ]);

        $response->assertUnprocessable();
    }

    public function test_booking_rejects_a_service_the_shift_restricts_to_something_else(): void
    {
        $this->createShift(['dental_condition_catalog_id' => $this->serviceId('rct')]);

        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 09:00",
        ]);

        $response->assertUnprocessable();
    }

    public function test_booking_allows_the_shifts_own_restricted_service(): void
    {
        $this->createShift(['dental_condition_catalog_id' => $this->serviceId('rct')]);

        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('rct'),
            'scheduled_at' => "{$this->date} 09:00",
        ]);

        $response->assertCreated();
    }

    public function test_booking_enforces_the_shifts_daily_cap(): void
    {
        $this->createShift(['dental_condition_catalog_id' => $this->serviceId('rct'), 'daily_cap' => 1]);

        $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('rct'),
            'scheduled_at' => "{$this->date} 09:00",
        ])->assertCreated();

        // A different, non-overlapping time — should still be blocked by the daily cap, not a conflict.
        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('rct'),
            'scheduled_at' => "{$this->date} 10:30",
        ]);

        $response->assertUnprocessable();
    }

    public function test_booking_rejects_a_staff_member_on_leave_for_that_window(): void
    {
        $this->createShift();

        $this->actingAs($this->manager)->postJson('/api/staff-leaves', [
            'staff_id' => $this->staffId,
            'starts_at' => "{$this->date} 08:00",
            'ends_at' => "{$this->date} 10:00",
        ])->assertCreated();

        $response = $this->actingAs($this->manager)->postJson('/api/appointments', [
            'patient_id' => $this->patientId,
            'staff_id' => $this->staffId,
            'dental_condition_catalog_id' => $this->serviceId('composite'),
            'scheduled_at' => "{$this->date} 09:00",
        ]);

        $response->assertUnprocessable();
    }
}
