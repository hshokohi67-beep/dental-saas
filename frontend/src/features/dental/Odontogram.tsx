"use client";

import type { OdontogramTooth } from "@/types/dental";
import { ToothShape } from "./ToothShape";

interface OdontogramProps {
  teeth: OdontogramTooth[];
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
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

/**
 * Positions a row of teeth along the natural dental arch curve (business
 * rules didn't specify this — it's a readability upgrade): front teeth sit
 * closest to the bite line (the gap between the two arches), and molars at
 * either end curve away from it, like a real jaw viewed from the front.
 */
function ArchRow({
  teeth,
  arch,
  size,
  amplitude,
  awayFromGapIsUp,
  selectedFdi,
  onSelectTooth,
}: {
  teeth: OdontogramTooth[];
  arch: "upper" | "lower";
  size: number;
  amplitude: number;
  awayFromGapIsUp: boolean;
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
}) {
  const slot = size + 6;
  const n = teeth.length;
  if (n === 0) return null;

  const width = n * slot;

  return (
    <div className="relative" style={{ width, height: size + amplitude + 20 }}>
      {teeth.map((tooth, index) => {
        const t = n > 1 ? (index - (n - 1) / 2) / ((n - 1) / 2) : 0;
        const x = index * slot;
        const curve = amplitude * t * t;
        const y = awayFromGapIsUp ? amplitude - curve : curve;

        return (
          <div key={tooth.fdi} className="absolute" style={{ left: x, top: y }}>
            <ToothShape
              arch={arch}
              screenSide={tooth.screen_side}
              size={size}
              label={tooth.display_label}
              isPrimary={tooth.dentition === "primary"}
              isSelected={selectedFdi === tooth.fdi}
              surfaceStatuses={tooth.surface_statuses}
              onClick={() => onSelectTooth(tooth)}
            />
          </div>
        );
      })}
    </div>
  );
}

/**
 * Rendered in the clinical/mirrored convention (business rules §1.2): the
 * patient's right side is drawn on the screen's left half. This container
 * is forced to `dir="ltr"` regardless of the app's RTL layout, because
 * left/right here means the physical screen side, not text direction.
 */
export function Odontogram({ teeth, selectedFdi, onSelectTooth }: OdontogramProps) {
  const hasPrimary = teeth.some((tooth) => tooth.dentition === "primary");

  return (
    <div dir="ltr" className="flex flex-col items-center gap-3 rounded-xl border border-border bg-white p-6">
      {hasPrimary && (
        <ArchRow
          teeth={rowTeeth(teeth, "upper", "primary")}
          arch="upper"
          size={30}
          amplitude={16}
          awayFromGapIsUp
          selectedFdi={selectedFdi}
          onSelectTooth={onSelectTooth}
        />
      )}
      <ArchRow
        teeth={rowTeeth(teeth, "upper", "permanent")}
        arch="upper"
        size={42}
        amplitude={36}
        awayFromGapIsUp
        selectedFdi={selectedFdi}
        onSelectTooth={onSelectTooth}
      />

      <div className="my-1 h-px w-2/3 border-t border-dashed border-border" />

      <ArchRow
        teeth={rowTeeth(teeth, "lower", "permanent")}
        arch="lower"
        size={42}
        amplitude={36}
        awayFromGapIsUp={false}
        selectedFdi={selectedFdi}
        onSelectTooth={onSelectTooth}
      />
      {hasPrimary && (
        <ArchRow
          teeth={rowTeeth(teeth, "lower", "primary")}
          arch="lower"
          size={30}
          amplitude={16}
          awayFromGapIsUp={false}
          selectedFdi={selectedFdi}
          onSelectTooth={onSelectTooth}
        />
      )}
    </div>
  );
}
