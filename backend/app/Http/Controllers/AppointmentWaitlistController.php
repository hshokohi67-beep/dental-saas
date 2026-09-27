<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\AppointmentWaitlistEntry;
use App\Http\Requests\StoreAppointmentWaitlistRequest;
use App\Http\Resources\AppointmentWaitlistResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class AppointmentWaitlistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::APPOINTMENTS_VIEW);

        $entries = AppointmentWaitlistEntry::query()
            ->with(['patient', 'dentalConditionCatalog'])
            ->when($request->query('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->where('status', AppointmentWaitlistEntry::STATUS_WAITING)
            ->orderBy('created_at')
            ->get();

        return AppointmentWaitlistResource::collection($entries);
    }

    public function store(StoreAppointmentWaitlistRequest $request): AppointmentWaitlistResource
    {
        $entry = AppointmentWaitlistEntry::query()->create([
            ...$request->validated(),
            'status' => AppointmentWaitlistEntry::STATUS_WAITING,
            'created_by' => Auth::id(),
        ]);

        return new AppointmentWaitlistResource($entry->load(['patient', 'dentalConditionCatalog']));
    }
}
