"use client";

import type { DentalCondition, GroupedFinding, OdontogramTooth, SurfaceRegion } from "@/types/dental";
import { ChartScopeButton } from "./ChartScopeButton";
import { ToothShape } from "./ToothShape";

interface OdontogramProps {
  patientId: string;
  teeth: OdontogramTooth[];
  quadrantFindings: GroupedFinding[];
  archFindings: GroupedFinding[];
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
  onChanged: () => void;
  activeCondition: DentalCondition | null;
  pendingTeeth: Map<number, Set<SurfaceRegion>>;
}

export function sortForDisplay(teeth: OdontogramTooth[], screenSide: "left" | "right"): OdontogramTooth[] {
  const sorted = [...teeth].sort((a, b) => a.position - b.position);
  // Tooth #1 (central) is always closest to the midline: for the screen-left
  // half that means rightmost (descending order left-to-right); for the
  // screen-right half it means leftmost (ascending order, already sorted).
  return screenSide === "left" ? sorted.reverse() : sorted;
}

function rowTeeth(teeth: OdontogramTooth[], arch: "upper" | "lower", dentition: "permanent" | "primary"): OdontogramTooth[] {
  const matching = teeth.filter((tooth) => tooth.arch === arch && tooth.dentition === dentition);
  const left = sortForDisplay(matching.filter((tooth) => tooth.screen_side === "left"), "left");
  const right = sortForDisplay(matching.filter((tooth) => tooth.screen_side === "right"), "right");
  return [...left, ...right];
}

function findingsForQuadrant(quadrantFindings: GroupedFinding[], quadrant: number) {
  return quadrantFindings.find((group) => group.quadrant === quadrant)?.conditions ?? [];
}

function findingsForArch(archFindings: GroupedFinding[], arch: "upper" | "lower") {
  return archFindings.find((group) => group.arch === arch)?.conditions ?? [];
}

/** Front teeth (incisors/canines) are anatomically narrower than molars — smaller here too, and it buys back row width. */
function toothSize(tooth: OdontogramTooth): number {
  if (tooth.dentition === "primary") return tooth.is_anterior ? 18 : 24;
  return tooth.is_anterior ? 24 : 32;
}

function ToothRow({
  teeth,
  selectedFdi,
  activeCondition,
  pendingTeeth,
  onSelectTooth,
}: {
  teeth: OdontogramTooth[];
  selectedFdi: number | null;
  activeCondition: DentalCondition | null;
  pendingTeeth: Map<number, Set<SurfaceRegion>>;
  onSelectTooth: (tooth: OdontogramTooth) => void;
}) {
  if (teeth.length === 0) return null;

  return (
    <div className="flex items-end justify-center gap-0.5">
      {teeth.map((tooth) => {
        const isSelected = activeCondition?.scope === "tooth" ? pendingTeeth.has(tooth.fdi) : selectedFdi === tooth.fdi;
        return (
          <ToothShape
            key={tooth.fdi}
            arch={tooth.arch}
            screenSide={tooth.screen_side}
            size={toothSize(tooth)}
            label={tooth.display_label}
            isPrimary={tooth.dentition === "primary"}
            isAnterior={tooth.is_anterior}
            isSelected={isSelected}
            surfaceStatuses={tooth.surface_statuses}
            onClick={() => onSelectTooth(tooth)}
          />
        );
      })}
    </div>
  );
}

const LEGEND: { label: string; color: string; border: string }[] = [
  { label: "کشیده‌شده / ایمپلنت", color: "#F2F3F4", border: "#B0BEC5" },
  { label: "ایمپلنت", color: "#E8F8F5", border: "#4DB6AC" },
  { label: "عصب‌کشی", color: "#EDE7F6", border: "#9B7FD4" },
  { label: "روکش", color: "#FEF9E7", border: "#F0C040" },
  { label: "بریج", color: "#EBF5FB", border: "#78C0E0" },
  { label: "لامینیت/ونیر", color: "#FEF0FB", border: "#D98FD6" },
  { label: "ترمیم", color: "#E8F4FC", border: "#5DADE2" },
  { label: "سالم", color: "#FFFFFF", border: "#C8D4DC" },
];

function ColorLegend() {
  return (
    <div className="flex flex-wrap justify-center gap-x-4 gap-y-1 border-t border-border pt-3 text-xs text-muted">
      {LEGEND.map((item) => (
        <span key={item.label} className="flex items-center gap-1">
          <span
            className="inline-block h-3 w-3 rounded-sm border"
            style={{ backgroundColor: item.color, borderColor: item.border }}
          />
          {item.label}
        </span>
      ))}
      <span className="flex items-center gap-1">
        <span className="inline-block h-3 w-3 rounded-sm border border-dashed border-[#B0BEC5]" />
        فقط تشخیصی (بدون رنگ اختصاصی)
      </span>
    </div>
  );
}

/**
 * Rendered in the clinical/mirrored convention (business rules §1.2): the
 * patient's right side is drawn on the screen's left half. This container
 * is forced to `dir="ltr"` regardless of the app's RTL layout, because
 * left/right here means the physical screen side, not text direction.
 */
export function Odontogram({
  patientId,
  teeth,
  quadrantFindings,
  archFindings,
  selectedFdi,
  onSelectTooth,
  onChanged,
  activeCondition,
  pendingTeeth,
}: OdontogramProps) {
  const hasPrimary = teeth.some((tooth) => tooth.dentition === "primary");

  return (
    <div dir="ltr" className="space-y-2 rounded-xl border border-border bg-white p-4">
      <ChartScopeButton
        patientId={patientId}
        label="فک بالا"
        catalogScope="arch"
        recordParams={{ scope_type: "arch", arch: "upper" }}
        findings={findingsForArch(archFindings, "upper")}
        onChanged={onChanged}
        fullWidth
      />

      <div className="flex items-center justify-between gap-2">
        <ChartScopeButton
          patientId={patientId}
          label="نیم‌فک راست بالا"
          catalogScope="half_arch"
          recordParams={{ scope_type: "quadrant", quadrant: 1 }}
          findings={findingsForQuadrant(quadrantFindings, 1)}
          onChanged={onChanged}
        />
        <ChartScopeButton
          patientId={patientId}
          label="نیم‌فک چپ بالا"
          catalogScope="half_arch"
          recordParams={{ scope_type: "quadrant", quadrant: 2 }}
          findings={findingsForQuadrant(quadrantFindings, 2)}
          onChanged={onChanged}
        />
      </div>

      <div className="overflow-x-auto">
        <div className="mx-auto flex w-max flex-col items-center gap-1">
          {hasPrimary && (
            <ToothRow
              teeth={rowTeeth(teeth, "upper", "primary")}
              selectedFdi={selectedFdi}
              activeCondition={activeCondition}
              pendingTeeth={pendingTeeth}
              onSelectTooth={onSelectTooth}
            />
          )}
          <ToothRow
            teeth={rowTeeth(teeth, "upper", "permanent")}
            selectedFdi={selectedFdi}
            activeCondition={activeCondition}
            pendingTeeth={pendingTeeth}
            onSelectTooth={onSelectTooth}
          />
        </div>
      </div>

      <div className="h-px w-full border-t border-dashed border-border" />

      <div className="overflow-x-auto">
        <div className="mx-auto flex w-max flex-col items-center gap-1">
          <ToothRow
            teeth={rowTeeth(teeth, "lower", "permanent")}
            selectedFdi={selectedFdi}
            activeCondition={activeCondition}
            pendingTeeth={pendingTeeth}
            onSelectTooth={onSelectTooth}
          />
          {hasPrimary && (
            <ToothRow
              teeth={rowTeeth(teeth, "lower", "primary")}
              selectedFdi={selectedFdi}
              activeCondition={activeCondition}
              pendingTeeth={pendingTeeth}
              onSelectTooth={onSelectTooth}
            />
          )}
        </div>
      </div>

      <div className="flex items-center justify-between gap-2">
        <ChartScopeButton
          patientId={patientId}
          label="نیم‌فک راست پایین"
          catalogScope="half_arch"
          recordParams={{ scope_type: "quadrant", quadrant: 4 }}
          findings={findingsForQuadrant(quadrantFindings, 4)}
          onChanged={onChanged}
        />
        <ChartScopeButton
          patientId={patientId}
          label="نیم‌فک چپ پایین"
          catalogScope="half_arch"
          recordParams={{ scope_type: "quadrant", quadrant: 3 }}
          findings={findingsForQuadrant(quadrantFindings, 3)}
          onChanged={onChanged}
        />
      </div>

      <ChartScopeButton
        patientId={patientId}
        label="فک پایین"
        catalogScope="arch"
        recordParams={{ scope_type: "arch", arch: "lower" }}
        findings={findingsForArch(archFindings, "lower")}
        onChanged={onChanged}
        fullWidth
      />

      <ColorLegend />
    </div>
  );
}
