export type ToothScope = "tooth" | "quadrant" | "arch" | "whole_mouth";

export type ToothSurface = "mesial" | "distal" | "occlusal" | "incisal" | "buccal" | "lingual";

export interface DentalCondition {
  id: string;
  key: string;
  label: string;
  category: string;
  scope: "tooth" | "half_arch" | "arch" | "whole_mouth";
  dentitions: Array<"permanent" | "primary">;
}

export interface ToothConditionEntry {
  id: string;
  code: string;
  label: string;
  surfaces: ToothSurface[] | null;
  notes: string | null;
  recorded_by: string | null;
  recorded_at: string | null;
}

export interface ToothStatus {
  code: string | null;
  label: string;
  color: string | null;
  border: string | null;
  is_dashed: boolean;
}

export type SurfaceRegion = "mesial" | "distal" | "occlusal" | "buccal" | "lingual";

export interface OdontogramTooth {
  fdi: number;
  quadrant: number;
  position: number;
  dentition: "permanent" | "primary";
  arch: "upper" | "lower";
  patient_side: "left" | "right";
  screen_side: "left" | "right";
  display_label: string;
  is_anterior: boolean;
  status: ToothStatus;
  surface_statuses: Record<SurfaceRegion, ToothStatus>;
  conditions: ToothConditionEntry[];
}

export interface GroupedFinding {
  quadrant?: number;
  arch?: string;
  conditions: ToothConditionEntry[];
}

export interface Odontogram {
  chart_mode: "adult" | "peds";
  teeth: OdontogramTooth[];
  quadrant_findings: GroupedFinding[];
  arch_findings: GroupedFinding[];
  whole_mouth_findings: ToothConditionEntry[];
}

export interface RecordToothConditionInput {
  dental_condition_catalog_id: string;
  scope_type: ToothScope;
  tooth_number?: number;
  quadrant?: number;
  arch?: "upper" | "lower";
  surfaces?: ToothSurface[];
  notes?: string;
}
