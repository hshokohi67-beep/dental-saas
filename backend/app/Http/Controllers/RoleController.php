<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Support\TenantContext;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::ROLES_MANAGE);

        $roles = Role::query()
            ->where('tenant_id', TenantContext::id())
            ->with('permissions')
            ->get();

        return RoleResource::collection($roles);
    }
}
