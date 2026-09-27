"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions } from "@/services/dental";
import { createStaffShift, deleteStaffShift, listStaffShifts } from "@/services/operations";
import type { DentalCondition } from "@/types/dental";
import type { StaffShift } from "@/types/operations";
import { DAY_LABELS } from "./dayLabels";

interface ShiftManagerProps {
  staffId: string;
  branchId: string;
}

/**
 * "Saturday 9–12 is root-canal only, cap 3 a day" — a shift is a weekly
 * block that can optionally restrict itself to one bookable service, so
 * BookAppointment/SlotGenerator only ever offer that block for that service.
 * Leaving the service as "بدون محدودیت" keeps a single-doctor clinic's setup
 * to just a time range, per the earlier agreement not to force complexity.
 */
export function ShiftManager({ staffId, branchId }: ShiftManagerProps) {
  const [shifts, setShifts] = useState<StaffShift[]>([]);
  const [services, setServices] = useState<DentalCondition[]>([]);
  const [dayOfWeek, setDayOfWeek] = useState(6);
  const [startTime, setStartTime] = useState("09:00");
  const [endTime, setEndTime] = useState("13:00");
  const [serviceId, setServiceId] = useState("");
  const [dailyCap, setDailyCap] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function reload() {
    listStaffShifts(staffId).then((response) => setShifts(response.data));
  }

  useEffect(() => {
    reload();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [staffId]);

  useEffect(() => {
    listDentalConditions().then((response) => setServices(response.data.filter((condition) => condition.booking_eligible)));
  }, []);

  async function handleCreate() {
    setError(null);
    setIsSubmitting(true);
    try {
      await createStaffShift({
        staff_id: staffId,
        branch_id: branchId,
        day_of_week: dayOfWeek,
        start_time: startTime,
        end_time: endTime,
        dental_condition_catalog_id: serviceId || null,
        daily_cap: dailyCap ? Number(dailyCap) : null,
      });
      setDailyCap("");
      reload();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ثبت شیفت.");
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDelete(shiftId: string) {
    await deleteStaffShift(shiftId);
    reload();
  }

  return (
    <div className="space-y-3 rounded-xl border border-border p-4">
      <h3 className="text-sm font-bold">شیفت‌های این پزشک</h3>

      <ul className="space-y-1 text-sm">
        {shifts.map((shift) => (
          <li key={shift.id} className="flex items-center justify-between rounded-lg border border-border px-3 py-2">
            <span className="flex items-center gap-2">
              <span className="font-medium">{DAY_LABELS[shift.day_of_week]}</span>
              <span dir="ltr" className="text-muted">
                {shift.start_time}–{shift.end_time}
              </span>
              {shift.dental_condition_catalog ? (
                <span
                  className="rounded-full border px-2 py-0.5 text-xs"
                  style={{
                    backgroundColor: shift.dental_condition_catalog.status_color ?? undefined,
                    borderColor: shift.dental_condition_catalog.status_border ?? undefined,
                  }}
                >
                  {shift.dental_condition_catalog.label}
                </span>
              ) : (
                <span className="text-xs text-muted">بدون محدودیت خدمت</span>
              )}
              {shift.daily_cap && <span className="text-xs text-muted">سقف روزانه: {shift.daily_cap}</span>}
            </span>
            <button type="button" className="text-xs text-red-600" onClick={() => handleDelete(shift.id)}>
              حذف
            </button>
          </li>
        ))}
        {shifts.length === 0 && <li className="text-muted">شیفتی تعریف نشده.</li>}
      </ul>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
        <select
          value={dayOfWeek}
          onChange={(event) => setDayOfWeek(Number(event.target.value))}
          className="rounded-lg border border-border px-3 py-2 text-sm"
        >
          {DAY_LABELS.map((label, index) => (
            <option key={index} value={index}>
              {label}
            </option>
          ))}
        </select>
        <input
          type="time"
          value={startTime}
          onChange={(event) => setStartTime(event.target.value)}
          className="rounded-lg border border-border px-3 py-2 text-sm"
        />
        <input
          type="time"
          value={endTime}
          onChange={(event) => setEndTime(event.target.value)}
          className="rounded-lg border border-border px-3 py-2 text-sm"
        />
        <select
          value={serviceId}
          onChange={(event) => setServiceId(event.target.value)}
          className="col-span-2 rounded-lg border border-border px-3 py-2 text-sm sm:col-span-1"
        >
          <option value="">بدون محدودیت خدمت</option>
          {services.map((service) => (
            <option key={service.id} value={service.id}>
              {service.label}
            </option>
          ))}
        </select>
        <input
          type="number"
          min={1}
          value={dailyCap}
          onChange={(event) => setDailyCap(event.target.value)}
          placeholder="سقف روزانه (اختیاری)"
          className="rounded-lg border border-border px-3 py-2 text-sm"
        />
        <Button type="button" disabled={isSubmitting} onClick={handleCreate}>
          افزودن شیفت
        </Button>
      </div>
    </div>
  );
}
