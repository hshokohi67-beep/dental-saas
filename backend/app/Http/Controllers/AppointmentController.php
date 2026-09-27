<?php

namespace App\Http\Controllers;

use App\Application\Actions\BookAppointment;
use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\Appointment;
use App\Domain\Patients\Models\Patient;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::APPOINTMENTS_VIEW);

        $appointments = Appointment::query()
            ->with(['patient', 'staff.user', 'dentalConditionCatalog'])
            ->when($request->query('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($request->query('staff_id'), fn ($query, $staffId) => $query->where('staff_id', $staffId))
            ->when($request->query('patient_id'), fn ($query, $patientId) => $query->where('patient_id', $patientId))
            ->when($request->query('date'), function ($query, $date) {
                $day = Carbon::parse($date)->startOfDay();
                $query->whereBetween('scheduled_at', [$day, $day->clone()->endOfDay()]);
            })
            ->orderBy('scheduled_at')
            ->get();

        return AppointmentResource::collection($appointments);
    }

    public function store(StoreAppointmentRequest $request, BookAppointment $bookAppointment): AppointmentResource
    {
        $data = $request->validated();

        $patient = Patient::query()->findOrFail($data['patient_id']);
        $staff = Staff::query()->findOrFail($data['staff_id']);
        $service = DentalConditionCatalog::query()->findOrFail($data['dental_condition_catalog_id']);

        $appointment = $bookAppointment->execute(
            $patient,
            $staff,
            $service,
            Carbon::parse($data['scheduled_at']),
            [
                'room_id' => $data['room_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ],
        );

        return new AppointmentResource($appointment);
    }
}
