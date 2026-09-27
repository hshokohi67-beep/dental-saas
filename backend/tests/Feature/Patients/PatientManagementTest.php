<?php

namespace Tests\Feature\Patients;

use App\Application\Actions\CreateTenant;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

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
    }

    public function test_tenant_manager_can_create_a_patient(): void
    {
        $response = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'mobile' => '09121234567',
            'national_id' => '1234567890',
            'gender' => 'male',
            'date_of_birth' => '1990-05-01',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.full_name', 'علی رضایی');
        $response->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('patients', [
            'tenant_id' => $this->tenant->id,
            'mobile' => '09121234567',
        ]);

        $this->assertDatabaseHas('patient_timeline_events', [
            'type' => 'created',
        ]);
    }

    public function test_creating_a_patient_cannot_be_spoofed_to_a_different_tenant(): void
    {
        $otherTenant = app(CreateTenant::class)->execute(
            tenantName: 'کلینیک دیگر',
            slug: 'other-clinic',
            planKey: 'practice',
            owner: ['name' => 'مدیر دو', 'email' => 'owner2@other-clinic.example', 'password' => 'password'],
        );

        $response = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'سارا',
            'last_name' => 'احمدی',
            'mobile' => '09129998877',
            'tenant_id' => $otherTenant->id,
        ]);

        $response->assertCreated();

        $patient = Patient::withoutTenantScope()->where('mobile', '09129998877')->firstOrFail();
        $this->assertSame($this->tenant->id, $patient->tenant_id);
    }

    public function test_patients_can_be_searched_by_mobile_or_name(): void
    {
        $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی', 'last_name' => 'رضایی', 'mobile' => '09121110000',
        ])->assertCreated();

        $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'مریم', 'last_name' => 'کریمی', 'mobile' => '09122220000',
        ])->assertCreated();

        $byMobile = $this->actingAs($this->manager)->getJson('/api/patients?q=09121110000');
        $byMobile->assertOk();
        $this->assertCount(1, $byMobile->json('data'));
        $byMobile->assertJsonPath('data.0.full_name', 'علی رضایی');

        $byName = $this->actingAs($this->manager)->getJson('/api/patients?q=کریمی');
        $byName->assertOk();
        $this->assertCount(1, $byName->json('data'));
    }

    public function test_a_role_without_patients_manage_permission_cannot_create_a_patient(): void
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'انباردار', 'email' => 'inventory@test-clinic.example', 'password' => 'password', 'role' => 'Inventory',
        ])->assertCreated();

        $inventoryUser = User::query()->where('email', 'inventory@test-clinic.example')->firstOrFail();

        $response = $this->actingAs($inventoryUser)->postJson('/api/patients', [
            'first_name' => 'رضا', 'last_name' => 'محمدی', 'mobile' => '09123334444',
        ]);

        $response->assertForbidden();
    }

    public function test_a_patient_from_another_tenant_is_not_reachable_by_id(): void
    {
        $otherTenantOwnerEmail = 'owner2@other-clinic.example';
        app(CreateTenant::class)->execute(
            tenantName: 'کلینیک دیگر',
            slug: 'other-clinic-2',
            planKey: 'practice',
            owner: ['name' => 'مدیر دو', 'email' => $otherTenantOwnerEmail, 'password' => 'password'],
        );
        $otherManager = User::query()->where('email', $otherTenantOwnerEmail)->firstOrFail();

        $created = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'نگار', 'last_name' => 'صادقی', 'mobile' => '09125556666',
        ])->assertCreated();

        $patientId = $created->json('data.id');

        $this->actingAs($otherManager)->getJson("/api/patients/{$patientId}")->assertNotFound();
    }

    public function test_updating_a_patient_records_a_timeline_event(): void
    {
        $created = $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'حسین', 'last_name' => 'یزدانی', 'mobile' => '09127778888',
        ])->assertCreated();

        $patientId = $created->json('data.id');

        $response = $this->actingAs($this->manager)->patchJson("/api/patients/{$patientId}", [
            'notes' => 'بیمار حساس به درد است.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.notes', 'بیمار حساس به درد است.');

        $this->assertDatabaseHas('patient_timeline_events', [
            'patient_id' => $patientId,
            'type' => 'updated',
        ]);
    }
}
