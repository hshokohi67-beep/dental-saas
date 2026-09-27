"use client";

import type { GroupedFinding, OdontogramTooth } from "@/types/dental";
import { QuadrantButton } from "./QuadrantButton";
import { ToothShape } from "./ToothShape";

interface OdontogramProps {
  patientId: string;
  teeth: OdontogramTooth[];
  quadrantFindings: GroupedFinding[];
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
  onChanged: () => void;
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

function findingsFor(quadrantFindings: GroupedFinding[], quadrant: number) {
  return quadrantFindings.find((group) => group.quadrant === quadrant)?.conditions ?? [];
}

function ToothRow({
  teeth,
  selectedFdi,
  onSelectTooth,
}: {
  teeth: OdontogramTooth[];
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
}) {
  if (teeth.length === 0) return null;

  return (
    <div className="flex justify-center gap-1">
      {teeth.map((tooth) => (
        <ToothShape
          key={tooth.fdi}
          arch={tooth.arch}
          screenSide={tooth.screen_side}
          size={tooth.dentition === "primary" ? 30 : 42}
          label={tooth.display_label}
          isPrimary={tooth.dentition === "primary"}
          isSelected={selectedFdi === tooth.fdi}
          surfaceStatuses={tooth.surface_statuses}
          onClick={() => onSelectTooth(tooth)}
        />
      ))}
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
export function Odontogram({ patientId, teeth, quadrantFindings, selectedFdi, onSelectTooth, onChanged }: OdontogramProps) {
  const hasPrimary = teeth.some((tooth) => tooth.dentition === "primary");

  return (
    <div dir="ltr" className="space-y-3 rounded-xl border border-border bg-white p-4">
      <div className="rounded-lg bg-muted/10 py-1 text-center text-xs font-bold text-muted">فک بالا</div>

      <div className="flex items-center justify-between gap-2">
        <QuadrantButton
          patientId={patientId}
          quadrant={1}
          label="نیم‌فک راست بالا"
          findings={findingsFor(quadrantFindings, 1)}
          onChanged={onChanged}
        />
        <div className="min-w-0 flex-1 overflow-x-auto">
          <div className="mx-auto flex w-max flex-col items-center gap-1">
            {hasPrimary && <ToothRow teeth={rowTeeth(teeth, "upper", "primary")} selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />}
            <ToothRow teeth={rowTeeth(teeth, "upper", "permanent")} selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
          </div>
        </div>
        <QuadrantButton
          patientId={patientId}
          quadrant={2}
          label="نیم‌فک چپ بالا"
          findings={findingsFor(quadrantFindings, 2)}
          onChanged={onChanged}
        />
      </div>

      <div className="h-px w-full border-t border-dashed border-border" />

      <div className="flex items-center justify-between gap-2">
        <QuadrantButton
          patientId={patientId}
          quadrant={4}
          label="نیم‌فک راست پایین"
          findings={findingsFor(quadrantFindings, 4)}
          onChanged={onChanged}
        />
        <div className="min-w-0 flex-1 overflow-x-auto">
          <div className="mx-auto flex w-max flex-col items-center gap-1">
            <ToothRow teeth={rowTeeth(teeth, "lower", "permanent")} selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
            {hasPrimary && <ToothRow teeth={rowTeeth(teeth, "lower", "primary")} selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />}
          </div>
        </div>
        <QuadrantButton
          patientId={patientId}
          quadrant={3}
          label="نیم‌فک چپ پایین"
          findings={findingsFor(quadrantFindings, 3)}
          onChanged={onChanged}
        />
      </div>

      <div className="rounded-lg bg-muted/10 py-1 text-center text-xs font-bold text-muted">فک پایین</div>

      <ColorLegend />
    </div>
  );
}
