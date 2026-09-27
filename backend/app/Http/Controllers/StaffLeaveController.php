<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\StaffLeave;
use App\Http\Requests\StoreStaffLeaveRequest;
use App\Http\Resources\StaffLeaveResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StaffLeaveController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::SCHEDULING_SHIFTS_VIEW);

        $leaves = StaffLeave::query()
            ->when($request->query('staff_id'), fn ($query, $staffId) => $query->where('staff_id', $staffId))
            ->orderBy('starts_at')
            ->get();

        return StaffLeaveResource::collection($leaves);
    }

    public function store(StoreStaffLeaveRequest $request): StaffLeaveResource
    {
        $leave = StaffLeave::query()->create($request->validated());

        return new StaffLeaveResource($leave);
    }

    public function destroy(Request $request, StaffLeave $leave): Response
    {
        $this->authorize(PermissionCatalog::SCHEDULING_SHIFTS_MANAGE);

        $leave->delete();

        return response()->noContent();
    }
}
