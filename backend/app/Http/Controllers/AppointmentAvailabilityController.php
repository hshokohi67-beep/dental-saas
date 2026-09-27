<?php

namespace App\Http\Controllers;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\Appointment;
use App\Domain\Operations\Models\StaffLeave;
use App\Domain\Operations\Models\StaffShift;
use App\Domain\Operations\Support\SlotGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * A pure read: suggests bookable slots for a staff+service+date. The
 * server never trusts these back at face value — BookAppointment
 * independently re-checks the shift/cap/conflict/leave rules.
 */
class AppointmentAvailabilityController extends Controller
{
    public function index(Request $request): array
    {
        $this->authorize(PermissionCatalog::APPOINTMENTS_VIEW);

        $data = $request->validate([
            'staff_id' => ['required', 'string', 'exists:staff,id'],
            'dental_condition_catalog_id' => ['required', 'string', 'exists:dental_condition_catalog,id'],
            'date' => ['required', 'date'],
        ]);

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $service = DentalConditionCatalog::query()->findOrFail($data['dental_condition_catalog_id']);
        $date = Carbon::parse($data['date'])->startOfDay();

        if (! $service->booking_eligible || $service->duration_minutes === null) {
            return ['data' => []];
        }

        $dayStart = $date->clone();
        $dayEnd = $date->clone()->endOfDay();

        $shifts = StaffShift::query()
            ->where('staff_id', $staff->id)
            ->where('is_active', true)
            ->where('day_of_week', $date->dayOfWeek)
            ->where(function ($query) use ($service) {
                $query->whereNull('dental_condition_catalog_id')->orWhere('dental_condition_catalog_id', $service->id);
            })
            ->get()
            ->filter(fn (StaffShift $shift) => $shift->coversDate($date))
            ->map(fn (StaffShift $shift) => [
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'daily_cap' => $shift->daily_cap,
            ])
            ->values()
            ->all();

        $busyFromAppointments = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->get()
            ->map(fn (Appointment $appointment) => ['starts_at' => $appointment->scheduled_at, 'ends_at' => $appointment->endsAt()]);

        $busyFromLeaves = StaffLeave::query()
            ->where('staff_id', $staff->id)
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->get()
            ->map(fn (StaffLeave $leave) => ['starts_at' => $leave->starts_at, 'ends_at' => $leave->ends_at]);

        $busyIntervals = $busyFromAppointments->concat($busyFromLeaves)->values()->all();

        $bookedForService = Appointment::query()
            ->where('staff_id', $staff->id)
            ->where('dental_condition_catalog_id', $service->id)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->count();

        $slots = SlotGenerator::generate(
            $date,
            $shifts,
            $busyIntervals,
            $service->duration_minutes,
            $service->buffer_minutes,
            $bookedForService,
            $date->isToday() ? now() : null,
        );

        return [
            'data' => array_map(fn (array $slot) => [
                'starts_at' => $slot['starts_at']->toIso8601String(),
                'ends_at' => $slot['ends_at']->toIso8601String(),
            ], $slots),
        ];
    }
}
