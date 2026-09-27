<?php

namespace Tests\Feature\Tenancy;

use App\Application\Actions\CreateTenant;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAndRolesTest extends TestCase
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

    public function test_tenant_manager_can_list_default_roles_with_permissions(): void
    {
        $response = $this->actingAs($this->manager)->getJson('/api/roles');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Tenant Manager'));
        $this->assertTrue($names->contains('Doctor'));
        $this->assertTrue($names->contains('Reception'));
    }

    public function test_tenant_manager_can_create_a_staff_member_with_a_role(): void
    {
        $response = $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دکتر رضایی',
            'email' => 'doctor@test-clinic.example',
            'password' => 'password',
            'title' => 'دندانپزشک عمومی',
            'role' => 'Doctor',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.user.roles.0', 'Doctor');

        $doctor = User::query()->where('email', 'doctor@test-clinic.example')->firstOrFail();
        $this->assertTrue($doctor->hasRole('Doctor'));
        $this->assertFalse($doctor->can('staff.manage'));
    }

    public function test_a_role_without_staff_manage_permission_cannot_create_staff(): void
    {
        $this->actingAs($this->manager)->postJson('/api/staff', [
            'name' => 'دستیار یک',
            'email' => 'assistant@test-clinic.example',
            'password' => 'password',
            'role' => 'Assistant',
        ])->assertCreated();

        $assistant = User::query()->where('email', 'assistant@test-clinic.example')->firstOrFail();

        $response = $this->actingAs($assistant)->postJson('/api/staff', [
            'name' => 'دستیار دو',
            'email' => 'assistant2@test-clinic.example',
            'password' => 'password',
            'role' => 'Assistant',
        ]);

        $response->assertForbidden();
    }
}
