<?php

namespace App\Application\Actions;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Operations\Models\Appointment;
use App\Domain\Operations\Models\StaffLeave;
use App\Domain\Operations\Models\StaffShift;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Re-validates everything the availability endpoint already suggested —
 * the server never trusts a client-supplied time slot at face value (same
 * defense-in-depth posture as the dental chart's access checks).
 */
class BookAppointment
{
    /**
     * @param  array{room_id?: ?string, branch_id?: ?string, notes?: ?string}  $data
     */
    public function execute(Patient $patient, Staff $staff, DentalConditionCatalog $service, Carbon $scheduledAt, array $data = []): Appointment
    {
        if (! $service->booking_eligible || $service->duration_minutes === null) {
            throw ValidationException::withMessages([
                'dental_condition_catalog_id' => "خدمت «{$service->label}» برای نوبت‌دهی قابل رزرو نیست.",
            ]);
        }

        $duration = $service->duration_minutes;
        $endsAt = $scheduledAt->clone()->addMinutes($duration);
        $dayStart = $scheduledAt->clone()->startOfDay();
        $dayEnd = $scheduledAt->clone()->endOfDay();

        return DB::transaction(function () use ($patient, $staff, $service, $scheduledAt, $endsAt, $duration, $dayStart, $dayEnd, $data) {
            $shift = $this->findCoveringShift($staff, $service, $scheduledAt, $endsAt);

            if ($shift === null) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'این بازه در شیفت‌های پزشک برای این خدمت تعریف نشده است.',
                ]);
            }

            if ($shift->daily_cap !== null) {
                $bookedToday = Appointment::query()
                    ->where('staff_id', $staff->id)
                    ->where('dental_condition_catalog_id', $service->id)
                    ->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
                    ->count();

                if ($bookedToday >= $shift->daily_cap) {
                    throw ValidationException::withMessages([
                        'scheduled_at' => 'سقف روزانه‌ی این خدمت برای این پزشک پر شده است.',
                    ]);
                }
            }

            $existing = Appointment::query()
                ->where('staff_id', $staff->id)
                ->whereIn('status', Appointment::ACTIVE_STATUSES)
                ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
                ->lockForUpdate()
                ->get();

            foreach ($existing as $appointment) {
                if ($scheduledAt->lt($appointment->endsAt()) && $endsAt->gt($appointment->scheduled_at)) {
                    throw ValidationException::withMessages([
                        'scheduled_at' => 'این بازه با نوبت دیگری تداخل دارد.',
                    ]);
                }
            }

            $onLeave = StaffLeave::query()
                ->where('staff_id', $staff->id)
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $scheduledAt)
                ->exists();

            if ($onLeave) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'پزشک در این بازه مرخصی است.',
                ]);
            }

            $appointment = Appointment::query()->create([
                'tenant_id' => $patient->tenant_id,
                'branch_id' => $data['branch_id'] ?? $shift->branch_id,
                'patient_id' => $patient->id,
                'staff_id' => $staff->id,
                'room_id' => $data['room_id'] ?? $shift->room_id,
                'dental_condition_catalog_id' => $service->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $duration,
                'status' => Appointment::STATUS_BOOKED,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_APPOINTMENT_BOOKED,
                "نوبت «{$service->label}» با {$staff->user->name} برای {$scheduledAt->format('Y-m-d H:i')} ثبت شد.",
                ['appointment_id' => $appointment->id],
            );

            return $appointment->load(['staff.user', 'dentalConditionCatalog', 'room']);
        });
    }

    private function findCoveringShift(Staff $staff, DentalConditionCatalog $service, Carbon $scheduledAt, Carbon $endsAt): ?StaffShift
    {
        return StaffShift::query()
            ->where('staff_id', $staff->id)
            ->where('is_active', true)
            ->where('day_of_week', $scheduledAt->dayOfWeek)
            ->where('start_time', '<=', $scheduledAt->format('H:i:s'))
            ->where('end_time', '>=', $endsAt->format('H:i:s'))
            ->where(function ($query) use ($service) {
                $query->whereNull('dental_condition_catalog_id')->orWhere('dental_condition_catalog_id', $service->id);
            })
            ->get()
            ->first(fn (StaffShift $shift) => $shift->coversDate($scheduledAt));
    }
}
