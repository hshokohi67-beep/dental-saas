<?php

namespace Tests\Feature\Patients;

use App\Application\Actions\CreateTenant;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientDuplicateDetectionTest extends TestCase
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

        $this->actingAs($this->manager)->postJson('/api/patients', [
            'first_name' => 'علی',
            'last_name' => 'رضایی',
            'mobile' => '09121234567',
            'date_of_birth' => '1990-05-01',
        ])->assertCreated();
    }

    public function test_finds_a_candidate_by_exact_mobile_match(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/patients/duplicates?mobile=09121234567');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonPath('data.0.mobile', '09121234567');
    }

    public function test_finds_a_candidate_by_same_name_and_birth_date(): void
    {
        $response = $this->actingAs($this->manager)->getJson(
            '/api/patients/duplicates?first_name=علی&last_name=رضایی&date_of_birth=1990-05-01'
        );

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_returns_nothing_for_an_unrelated_patient(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/patients/duplicates?mobile=09990000000');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_excludes_the_given_patient_id_from_its_own_duplicate_search(): void
    {
        $patient = Patient::query()->where('mobile', '09121234567')->firstOrFail();

        $response = $this->actingAs($this->manager)->getJson(
            "/api/patients/duplicates?mobile=09121234567&excluding_patient_id={$patient->id}"
        );

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
