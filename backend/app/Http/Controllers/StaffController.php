<?php

namespace App\Http\Controllers;

use App\Application\Actions\CreateStaffMember;
use App\Domain\Identity\Models\Staff;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\StaffResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::STAFF_VIEW);

        return StaffResource::collection(Staff::query()->with('user')->get());
    }

    public function store(StoreStaffRequest $request, CreateStaffMember $createStaffMember): StaffResource
    {
        $staff = $createStaffMember->execute(
            user: [
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
            ],
            branchId: $request->input('branch_id'),
            title: (string) $request->input('title', ''),
            roleName: $request->string('role')->toString(),
        );

        return new StaffResource($staff->load('user'));
    }
}
