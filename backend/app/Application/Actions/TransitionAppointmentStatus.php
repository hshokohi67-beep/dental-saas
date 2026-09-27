<?php

namespace App\Application\Actions;

use App\Domain\Operations\Models\Appointment;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use Illuminate\Validation\ValidationException;

class TransitionAppointmentStatus
{
    private const TERMINAL_STATUSES = [
        Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW,
    ];

    public function checkIn(Appointment $appointment): Appointment
    {
        $this->assertNotTerminal($appointment);
        $appointment->update(['status' => Appointment::STATUS_CHECKED_IN, 'checked_in_at' => now()]);

        return $appointment;
    }

    public function complete(Appointment $appointment): Appointment
    {
        $this->assertNotTerminal($appointment);
        $appointment->update(['status' => Appointment::STATUS_COMPLETED, 'completed_at' => now()]);

        PatientTimelineRecorder::record(
            $appointment->patient,
            PatientTimelineEvent::TYPE_APPOINTMENT_COMPLETED,
            "نوبت «{$appointment->dentalConditionCatalog->label}» انجام شد.",
            ['appointment_id' => $appointment->id],
        );

        return $appointment;
    }

    public function cancel(Appointment $appointment, ?string $reason): Appointment
    {
        $this->assertNotTerminal($appointment);
        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_reason' => $reason,
        ]);

        PatientTimelineRecorder::record(
            $appointment->patient,
            PatientTimelineEvent::TYPE_APPOINTMENT_CANCELLED,
            "نوبت «{$appointment->dentalConditionCatalog->label}» لغو شد.",
            ['appointment_id' => $appointment->id, 'reason' => $reason],
        );

        return $appointment;
    }

    public function markNoShow(Appointment $appointment): Appointment
    {
        $this->assertNotTerminal($appointment);
        $appointment->update(['status' => Appointment::STATUS_NO_SHOW]);

        PatientTimelineRecorder::record(
            $appointment->patient,
            PatientTimelineEvent::TYPE_APPOINTMENT_NO_SHOW,
            "بیمار در نوبت «{$appointment->dentalConditionCatalog->label}» حاضر نشد.",
            ['appointment_id' => $appointment->id],
        );

        return $appointment;
    }

    private function assertNotTerminal(Appointment $appointment): void
    {
        if (in_array($appointment->status, self::TERMINAL_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'این نوبت قبلاً به وضعیت نهایی رسیده و قابل تغییر نیست.',
            ]);
        }
    }
}
