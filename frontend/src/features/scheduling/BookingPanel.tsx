"use client";

import { useEffect, useState } from "react";
import { ConditionPicker } from "@/features/dental/ConditionPicker";
import { ApiError } from "@/lib/api";
import { listDentalConditions } from "@/services/dental";
import { bookAppointment, getAvailability } from "@/services/operations";
import { listPatients } from "@/services/patients";
import type { DentalCondition } from "@/types/dental";
import type { AvailabilitySlot } from "@/types/operations";
import type { Patient } from "@/types/patient";

interface BookingPanelProps {
  staffId: string;
  onBooked: () => void;
}

/** Pick a service once, then just tap an open slot — the same one-click spirit as the chart's active-service mode. */
export function BookingPanel({ staffId, onBooked }: BookingPanelProps) {
  const [patientQuery, setPatientQuery] = useState("");
  const [patientMatches, setPatientMatches] = useState<Patient[]>([]);
  const [selectedPatient, setSelectedPatient] = useState<Patient | null>(null);

  const [services, setServices] = useState<DentalCondition[]>([]);
  const [serviceId, setServiceId] = useState("");
  const [date, setDate] = useState(() => new Date().toISOString().slice(0, 10));
  const [slots, setSlots] = useState<AvailabilitySlot[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [isBooking, setIsBooking] = useState(false);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  useEffect(() => {
    listDentalConditions().then((response) => setServices(response.data.filter((condition) => condition.booking_eligible)));
  }, []);

  useEffect(() => {
    if (!patientQuery.trim()) return;
    const handle = setTimeout(() => {
      listPatients(patientQuery).then((response) => setPatientMatches(response.data));
    }, 250);
    return () => clearTimeout(handle);
  }, [patientQuery]);

  useEffect(() => {
    if (!staffId || !serviceId || !date) return;
    getAvailability(staffId, serviceId, date).then((response) => {
      setSlots(response.data);
      setSuccessMessage(null);
    });
  }, [staffId, serviceId, date]);

  async function handleBook(slot: AvailabilitySlot) {
    if (!selectedPatient) return;
    setError(null);
    setIsBooking(true);
    try {
      await bookAppointment({
        patient_id: selectedPatient.id,
        staff_id: staffId,
        dental_condition_catalog_id: serviceId,
        scheduled_at: slot.starts_at,
      });
      setSuccessMessage(`نوبت برای ${selectedPatient.full_name} ثبت شد.`);
      setSlots((current) => current.filter((s) => s.starts_at !== slot.starts_at));
      onBooked();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ثبت نوبت.");
    } finally {
      setIsBooking(false);
    }
  }

  return (
    <div className="space-y-3 rounded-xl border border-border p-4">
      <h3 className="text-sm font-bold">رزرو نوبت جدید</h3>

      {!selectedPatient ? (
        <div className="relative">
          <input
            value={patientQuery}
            onChange={(event) => setPatientQuery(event.target.value)}
            placeholder="جست‌وجوی بیمار با نام یا موبایل..."
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
          {patientQuery.trim() !== "" && patientMatches.length > 0 && (
            <ul className="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-border bg-white text-sm shadow-md">
              {patientMatches.map((patient) => (
                <li key={patient.id}>
                  <button
                    type="button"
                    className="block w-full px-3 py-2 text-right hover:bg-black/5"
                    onClick={() => {
                      setSelectedPatient(patient);
                      setPatientMatches([]);
                      setPatientQuery("");
                    }}
                  >
                    {patient.full_name} — <span dir="ltr">{patient.mobile}</span>
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      ) : (
        <div className="flex items-center justify-between rounded-lg bg-muted/10 px-3 py-2 text-sm">
          <span>
            بیمار: <b>{selectedPatient.full_name}</b> — <span dir="ltr">{selectedPatient.mobile}</span>
          </span>
          <button type="button" className="text-xs text-muted" onClick={() => setSelectedPatient(null)}>
            تغییر
          </button>
        </div>
      )}

      <input
        type="date"
        value={date}
        onChange={(event) => setDate(event.target.value)}
        className="rounded-lg border border-border px-3 py-2 text-sm"
      />

      <ConditionPicker conditions={services} value={serviceId} onChange={setServiceId} placeholder="جست‌وجوی خدمت..." />

      {error && <p className="text-sm text-red-600">{error}</p>}
      {successMessage && <p className="text-sm text-green-700">{successMessage}</p>}

      {serviceId && (
        <div className="flex flex-wrap gap-1">
          {slots.length === 0 && <p className="text-xs text-muted">بازه‌ی خالی برای این روز/خدمت وجود ندارد.</p>}
          {slots.map((slot) => (
            <button
              key={slot.starts_at}
              type="button"
              data-testid="availability-slot"
              disabled={!selectedPatient || isBooking}
              onClick={() => handleBook(slot)}
              className="rounded-full border border-border px-2.5 py-1 text-xs hover:bg-black/5 disabled:opacity-50"
            >
              {new Date(slot.starts_at).toLocaleTimeString("fa-IR", { hour: "2-digit", minute: "2-digit" })}
            </button>
          ))}
        </div>
      )}
      {serviceId && !selectedPatient && <p className="text-xs text-muted">برای رزرو، اول بیمار را انتخاب کنید.</p>}
    </div>
  );
}
