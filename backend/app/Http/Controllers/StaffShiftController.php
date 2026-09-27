<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\StaffShift;
use App\Http\Requests\StoreStaffShiftRequest;
use App\Http\Resources\StaffShiftResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StaffShiftController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::SCHEDULING_SHIFTS_VIEW);

        $shifts = StaffShift::query()
            ->with('dentalConditionCatalog')
            ->when($request->query('staff_id'), fn ($query, $staffId) => $query->where('staff_id', $staffId))
            ->when($request->query('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return StaffShiftResource::collection($shifts);
    }

    public function store(StoreStaffShiftRequest $request): StaffShiftResource
    {
        $shift = StaffShift::query()->create($request->validated());

        return new StaffShiftResource($shift->load('dentalConditionCatalog'));
    }

    public function destroy(Request $request, StaffShift $shift): Response
    {
        $this->authorize(PermissionCatalog::SCHEDULING_SHIFTS_MANAGE);

        $shift->update(['is_active' => false]);

        return response()->noContent();
    }
}
