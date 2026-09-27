import { apiFetch } from "@/lib/api";
import type {
  Appointment,
  AppointmentWaitlistEntry,
  AvailabilitySlot,
  Room,
  StaffLeave,
  StaffShift,
} from "@/types/operations";

interface Collection<T> {
  data: T[];
}

interface Resource<T> {
  data: T;
}

export function listRooms(branchId: string): Promise<Collection<Room>> {
  return apiFetch(`/api/rooms?branch_id=${branchId}`);
}

export function listStaffShifts(staffId: string): Promise<Collection<StaffShift>> {
  return apiFetch(`/api/staff-shifts?staff_id=${staffId}`);
}

export interface CreateShiftInput {
  staff_id: string;
  branch_id: string;
  room_id?: string | null;
  day_of_week: number;
  start_time: string;
  end_time: string;
  dental_condition_catalog_id?: string | null;
  daily_cap?: number | null;
  effective_from?: string | null;
  effective_until?: string | null;
}

export function createStaffShift(input: CreateShiftInput): Promise<Resource<StaffShift>> {
  return apiFetch("/api/staff-shifts", { method: "POST", body: JSON.stringify(input) });
}

export function deleteStaffShift(shiftId: string): Promise<void> {
  return apiFetch(`/api/staff-shifts/${shiftId}`, { method: "DELETE" });
}

export function listStaffLeaves(staffId: string): Promise<Collection<StaffLeave>> {
  return apiFetch(`/api/staff-leaves?staff_id=${staffId}`);
}

export function createStaffLeave(input: { staff_id: string; starts_at: string; ends_at: string; reason?: string }): Promise<Resource<StaffLeave>> {
  return apiFetch("/api/staff-leaves", { method: "POST", body: JSON.stringify(input) });
}

export function deleteStaffLeave(leaveId: string): Promise<void> {
  return apiFetch(`/api/staff-leaves/${leaveId}`, { method: "DELETE" });
}

export function getAvailability(staffId: string, dentalConditionCatalogId: string, date: string): Promise<Collection<AvailabilitySlot>> {
  const params = new URLSearchParams({ staff_id: staffId, dental_condition_catalog_id: dentalConditionCatalogId, date });
  return apiFetch(`/api/appointments/availability?${params.toString()}`);
}

export function listAppointments(filters: { staff_id?: string; branch_id?: string; patient_id?: string; date?: string }): Promise<Collection<Appointment>> {
  const params = new URLSearchParams();
  if (filters.staff_id) params.set("staff_id", filters.staff_id);
  if (filters.branch_id) params.set("branch_id", filters.branch_id);
  if (filters.patient_id) params.set("patient_id", filters.patient_id);
  if (filters.date) params.set("date", filters.date);

  return apiFetch(`/api/appointments?${params.toString()}`);
}

export interface BookAppointmentInput {
  patient_id: string;
  staff_id: string;
  dental_condition_catalog_id: string;
  scheduled_at: string;
  room_id?: string | null;
  notes?: string;
}

export function bookAppointment(input: BookAppointmentInput): Promise<Resource<Appointment>> {
  return apiFetch("/api/appointments", { method: "POST", body: JSON.stringify(input) });
}

export function checkInAppointment(appointmentId: string): Promise<Resource<Appointment>> {
  return apiFetch(`/api/appointments/${appointmentId}/check-in`, { method: "PATCH" });
}

export function completeAppointment(appointmentId: string): Promise<Resource<Appointment>> {
  return apiFetch(`/api/appointments/${appointmentId}/complete`, { method: "PATCH" });
}

export function cancelAppointment(appointmentId: string, reason?: string): Promise<Resource<Appointment>> {
  return apiFetch(`/api/appointments/${appointmentId}/cancel`, { method: "PATCH", body: JSON.stringify({ reason }) });
}

export function markAppointmentNoShow(appointmentId: string): Promise<Resource<Appointment>> {
  return apiFetch(`/api/appointments/${appointmentId}/no-show`, { method: "PATCH" });
}

export function listWaitlist(branchId: string): Promise<Collection<AppointmentWaitlistEntry>> {
  return apiFetch(`/api/appointment-waitlist?branch_id=${branchId}`);
}

export function joinWaitlist(input: {
  patient_id: string;
  branch_id: string;
  staff_id?: string | null;
  dental_condition_catalog_id: string;
  preferred_from?: string;
  preferred_until?: string;
  notes?: string;
}): Promise<Resource<AppointmentWaitlistEntry>> {
  return apiFetch("/api/appointment-waitlist", { method: "POST", body: JSON.stringify(input) });
}
