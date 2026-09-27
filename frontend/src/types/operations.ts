import type { DentalCondition } from "./dental";

export interface Room {
  id: string;
  branch_id: string;
  name: string;
  is_default: boolean;
}

export interface StaffShift {
  id: string;
  staff_id: string;
  branch_id: string;
  room_id: string | null;
  day_of_week: number; // 0 (Sunday) .. 6 (Saturday), matches Carbon
  start_time: string; // "HH:MM"
  end_time: string; // "HH:MM"
  dental_condition_catalog: DentalCondition | null;
  daily_cap: number | null;
  effective_from: string | null;
  effective_until: string | null;
  is_active: boolean;
}

export interface StaffLeave {
  id: string;
  staff_id: string;
  starts_at: string;
  ends_at: string;
  reason: string | null;
}

export type AppointmentStatus = "booked" | "confirmed" | "checked_in" | "completed" | "cancelled" | "no_show";

export interface AvailabilitySlot {
  starts_at: string;
  ends_at: string;
}

export interface Appointment {
  id: string;
  branch_id: string;
  patient: { id: string; full_name: string; mobile: string };
  staff: { id: string; name: string };
  room_id: string | null;
  dental_condition_catalog: DentalCondition;
  scheduled_at: string;
  duration_minutes: number;
  ends_at: string;
  status: AppointmentStatus;
  notes: string | null;
  cancelled_reason: string | null;
  checked_in_at: string | null;
  completed_at: string | null;
  cancelled_at: string | null;
}

export interface AppointmentWaitlistEntry {
  id: string;
  branch_id: string;
  patient: { id: string; full_name: string; mobile: string };
  staff_id: string | null;
  dental_condition_catalog: DentalCondition;
  preferred_from: string | null;
  preferred_until: string | null;
  status: string;
  notes: string | null;
}
