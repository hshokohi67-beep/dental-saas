<?php

namespace App\Http\Controllers\Platform;

use App\Application\Actions\CreateTenant;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Support\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Http\Resources\TenantResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $tenants = TenantContext::bypass(fn () => Tenant::query()->latest()->paginate());

        return TenantResource::collection($tenants);
    }

    public function store(StoreTenantRequest $request, CreateTenant $createTenant): TenantResource
    {
        $tenant = $createTenant->execute(
            tenantName: $request->string('name')->toString(),
            slug: $request->string('slug')->toString(),
            planKey: $request->string('plan_key')->toString(),
            owner: [
                'name' => $request->string('owner_name')->toString(),
                'email' => $request->string('owner_email')->toString(),
                'password' => $request->string('owner_password')->toString(),
            ],
        );

        return new TenantResource($tenant);
    }
}
