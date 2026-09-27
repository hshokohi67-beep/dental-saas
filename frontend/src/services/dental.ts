import { apiFetch } from "@/lib/api";
import type { ChartMode } from "@/types/patient";
import type { DentalCondition, Odontogram, RecordToothConditionInput, ToothConditionEntry } from "@/types/dental";

interface Collection<T> {
  data: T[];
}

interface Resource<T> {
  data: T;
}

export function getOdontogram(patientId: string): Promise<Resource<Odontogram>> {
  return apiFetch(`/api/patients/${patientId}/odontogram`);
}

export function setChartMode(patientId: string, chartMode: ChartMode): Promise<unknown> {
  return apiFetch(`/api/patients/${patientId}/chart-mode`, {
    method: "PATCH",
    body: JSON.stringify({ chart_mode: chartMode }),
  });
}

export function assignPrimaryDoctor(patientId: string, staffId: string | null): Promise<unknown> {
  return apiFetch(`/api/patients/${patientId}/primary-doctor`, {
    method: "PATCH",
    body: JSON.stringify({ staff_id: staffId }),
  });
}

export function listDentalConditions(filters: { scope?: string; dentition?: string } = {}): Promise<Collection<DentalCondition>> {
  const params = new URLSearchParams();
  if (filters.scope) params.set("scope", filters.scope);
  if (filters.dentition) params.set("dentition", filters.dentition);

  return apiFetch(`/api/dental-conditions?${params.toString()}`);
}

export function recordToothCondition(
  patientId: string,
  input: RecordToothConditionInput,
): Promise<Resource<ToothConditionEntry>> {
  return apiFetch(`/api/patients/${patientId}/tooth-conditions`, {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export function voidToothCondition(patientId: string, toothConditionId: string): Promise<void> {
  return apiFetch(`/api/patients/${patientId}/tooth-conditions/${toothConditionId}`, { method: "DELETE" });
}
