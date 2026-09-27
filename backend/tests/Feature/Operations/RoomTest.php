<?php

namespace Tests\Feature\Operations;

use App\Application\Actions\CreateTenant;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
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

    public function test_a_new_branch_gets_a_default_room_automatically(): void
    {
        $branchId = $this->actingAs($this->manager)->getJson('/api/branches')->json('data.0.id');

        $rooms = $this->actingAs($this->manager)->getJson("/api/rooms?branch_id={$branchId}")->assertOk();

        $this->assertCount(1, $rooms->json('data'));
        $this->assertTrue($rooms->json('data.0.is_default'));
    }
}
