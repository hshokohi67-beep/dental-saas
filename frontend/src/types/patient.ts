export type Gender = "male" | "female" | "other";

export type AllergySeverity = "mild" | "moderate" | "severe";

export type ChartMode = "adult" | "peds";

export interface Patient {
  id: string;
  first_name: string;
  last_name: string;
  full_name: string;
  mobile: string;
  national_id: string | null;
  gender: Gender | null;
  date_of_birth: string | null;
  branch_id: string | null;
  status: "active" | "archived" | "merged";
  chart_mode: ChartMode;
  primary_doctor_staff_id: string | null;
  created_at: string;
}

export interface ClinicalAlert {
  source: "medical_condition" | "allergy";
  label: string;
  message: string;
}

export interface MedicalCondition {
  id: string;
  key: string;
  label: string;
  alert_text: string | null;
}

export interface PatientMedicalConditionLink {
  id: string;
  medical_condition_id: string;
  label: string;
  alert_text: string | null;
  notes: string | null;
  recorded_at: string | null;
}

export interface PatientAllergy {
  id: string;
  allergen: string;
  severity: AllergySeverity | null;
  reaction: string | null;
  notes: string | null;
  recorded_at: string | null;
}

export interface PatientMedication {
  id: string;
  name: string;
  dosage: string | null;
  frequency: string | null;
  is_active: boolean;
  notes: string | null;
  started_at: string | null;
  ended_at: string | null;
}

export interface PatientTimelineEvent {
  id: string;
  type: string;
  description: string;
  metadata: Record<string, unknown> | null;
  recorded_by: string | null;
  occurred_at: string;
}

export interface PatientMedical {
  conditions: PatientMedicalConditionLink[];
  allergies: PatientAllergy[];
  medications: PatientMedication[];
  alerts: ClinicalAlert[];
}

export interface PatientDetail extends Patient {
  notes: string | null;
  branch_name: string | null;
  primary_doctor_name: string | null;
  merged_from_count: number;
  medical: PatientMedical | null;
  recent_timeline: PatientTimelineEvent[];
}

export interface CreatePatientInput {
  first_name: string;
  last_name: string;
  mobile: string;
  national_id?: string;
  gender?: Gender;
  date_of_birth?: string;
  notes?: string;
  branch_id?: string;
}

export interface DuplicateSearchCriteria {
  mobile?: string;
  national_id?: string;
  first_name?: string;
  last_name?: string;
  date_of_birth?: string;
  excluding_patient_id?: string;
}
