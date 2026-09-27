import { apiFetch } from "@/lib/api";
import type {
  CreatePatientInput,
  DuplicateSearchCriteria,
  MedicalCondition,
  Patient,
  PatientAllergy,
  PatientDetail,
  PatientMedication,
  PatientMedicalConditionLink,
  PatientTimelineEvent,
} from "@/types/patient";

interface Collection<T> {
  data: T[];
}

interface Resource<T> {
  data: T;
}

export function listPatients(query: string): Promise<Collection<Patient>> {
  const params = query ? `?q=${encodeURIComponent(query)}` : "";
  return apiFetch(`/api/patients${params}`);
}

export function findDuplicatePatients(criteria: DuplicateSearchCriteria): Promise<Collection<Patient>> {
  const params = new URLSearchParams();
  Object.entries(criteria).forEach(([key, value]) => {
    if (value) params.set(key, value);
  });

  return apiFetch(`/api/patients/duplicates?${params.toString()}`);
}

export function createPatient(input: CreatePatientInput): Promise<Resource<PatientDetail>> {
  return apiFetch("/api/patients", { method: "POST", body: JSON.stringify(input) });
}

export function getPatient(id: string): Promise<Resource<PatientDetail>> {
  return apiFetch(`/api/patients/${id}`);
}

export function updatePatient(id: string, input: Partial<CreatePatientInput>): Promise<Resource<PatientDetail>> {
  return apiFetch(`/api/patients/${id}`, { method: "PATCH", body: JSON.stringify(input) });
}

export function mergePatients(survivorId: string, duplicateId: string): Promise<Resource<PatientDetail>> {
  return apiFetch("/api/patients/merge", {
    method: "POST",
    body: JSON.stringify({ survivor_patient_id: survivorId, duplicate_patient_id: duplicateId }),
  });
}

export function listPatientTimeline(id: string): Promise<Collection<PatientTimelineEvent>> {
  return apiFetch(`/api/patients/${id}/timeline`);
}

export function listMedicalConditions(): Promise<Collection<MedicalCondition>> {
  return apiFetch("/api/medical-conditions");
}

export function addMedicalCondition(
  patientId: string,
  medicalConditionId: string,
  notes?: string,
): Promise<Resource<PatientMedicalConditionLink>> {
  return apiFetch(`/api/patients/${patientId}/medical-conditions`, {
    method: "POST",
    body: JSON.stringify({ medical_condition_id: medicalConditionId, notes }),
  });
}

export function removeMedicalCondition(patientId: string, linkId: string): Promise<void> {
  return apiFetch(`/api/patients/${patientId}/medical-conditions/${linkId}`, { method: "DELETE" });
}

export function addAllergy(
  patientId: string,
  input: { allergen: string; severity?: string; reaction?: string; notes?: string },
): Promise<Resource<PatientAllergy>> {
  return apiFetch(`/api/patients/${patientId}/allergies`, { method: "POST", body: JSON.stringify(input) });
}

export function removeAllergy(patientId: string, allergyId: string): Promise<void> {
  return apiFetch(`/api/patients/${patientId}/allergies/${allergyId}`, { method: "DELETE" });
}

export function addMedication(
  patientId: string,
  input: { name: string; dosage?: string; frequency?: string; notes?: string },
): Promise<Resource<PatientMedication>> {
  return apiFetch(`/api/patients/${patientId}/medications`, { method: "POST", body: JSON.stringify(input) });
}

export function updateMedication(
  patientId: string,
  medicationId: string,
  input: { is_active?: boolean; ended_at?: string },
): Promise<Resource<PatientMedication>> {
  return apiFetch(`/api/patients/${patientId}/medications/${medicationId}`, {
    method: "PATCH",
    body: JSON.stringify(input),
  });
}
