"use client";

import { useEffect, useState } from "react";
import { ApiError } from "@/lib/api";
import {
  cancelAppointment,
  checkInAppointment,
  completeAppointment,
  listAppointments,
  markAppointmentNoShow,
} from "@/services/operations";
import type { Appointment, AppointmentStatus } from "@/types/operations";

interface DayQueueProps {
  staffId: string;
  date: string;
  refreshToken: number;
}

const STATUS_LABELS: Record<AppointmentStatus, string> = {
  booked: "رزروشده",
  confirmed: "تأییدشده",
  checked_in: "حاضر",
  completed: "انجام‌شده",
  cancelled: "لغوشده",
  no_show: "عدم حضور",
};

/** Today's appointments for the selected doctor — reception's check-in/complete/cancel/no-show queue. */
export function DayQueue({ staffId, date, refreshToken }: DayQueueProps) {
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [error, setError] = useState<string | null>(null);

  function reload() {
    listAppointments({ staff_id: staffId, date }).then((response) => setAppointments(response.data));
  }

  useEffect(() => {
    if (!staffId || !date) return;
    reload();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [staffId, date, refreshToken]);

  async function handle(action: () => Promise<unknown>) {
    setError(null);
    try {
      await action();
      reload();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا رخ داد.");
    }
  }

  return (
    <div className="space-y-3 rounded-xl border border-border p-4">
      <h3 className="text-sm font-bold">نوبت‌های امروز</h3>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <ul className="space-y-2 text-sm">
        {appointments.map((appointment) => (
          <li key={appointment.id} className="flex items-center justify-between rounded-lg border border-border px-3 py-2">
            <div className="flex items-center gap-2">
              <span dir="ltr" className="font-medium">
                {new Date(appointment.scheduled_at).toLocaleTimeString("fa-IR", { hour: "2-digit", minute: "2-digit" })}
              </span>
              <span
                className="rounded-full border px-2 py-0.5 text-xs"
                style={{
                  backgroundColor: appointment.dental_condition_catalog.status_color ?? undefined,
                  borderColor: appointment.dental_condition_catalog.status_border ?? undefined,
                }}
              >
                {appointment.dental_condition_catalog.label}
              </span>
              <span>{appointment.patient.full_name}</span>
              <span className="text-xs text-muted">({STATUS_LABELS[appointment.status]})</span>
            </div>

            <div className="flex gap-1 text-xs">
              {(appointment.status === "booked" || appointment.status === "confirmed") && (
                <>
                  <button className="text-primary" onClick={() => handle(() => checkInAppointment(appointment.id))}>
                    چک‌این
                  </button>
                  <button className="text-red-600" onClick={() => handle(() => markAppointmentNoShow(appointment.id))}>
                    عدم حضور
                  </button>
                  <button className="text-muted" onClick={() => handle(() => cancelAppointment(appointment.id))}>
                    لغو
                  </button>
                </>
              )}
              {appointment.status === "checked_in" && (
                <>
                  <button className="text-primary" onClick={() => handle(() => completeAppointment(appointment.id))}>
                    تکمیل
                  </button>
                  <button className="text-muted" onClick={() => handle(() => cancelAppointment(appointment.id))}>
                    لغو
                  </button>
                </>
              )}
            </div>
          </li>
        ))}
        {appointments.length === 0 && <li className="text-muted">نوبتی برای این روز ثبت نشده.</li>}
      </ul>
    </div>
  );
}
