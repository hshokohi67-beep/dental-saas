"use client";

import type { OdontogramTooth } from "@/types/dental";

interface OdontogramProps {
  teeth: OdontogramTooth[];
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
}

type CellKey = "ul" | "ur" | "ll" | "lr";

function cellKeyFor(tooth: OdontogramTooth): CellKey {
  const vertical = tooth.arch === "upper" ? "u" : "l";
  const horizontal = tooth.screen_side === "left" ? "l" : "r";
  return `${vertical}${horizontal}` as CellKey;
}

export function sortForDisplay(teeth: OdontogramTooth[], screenSide: "left" | "right"): OdontogramTooth[] {
  const sorted = [...teeth].sort((a, b) => a.position - b.position);
  // Tooth #1 (central) is always closest to the midline: for the screen-left
  // half that means rightmost (descending order left-to-right); for the
  // screen-right half it means leftmost (ascending order, already sorted).
  return screenSide === "left" ? sorted.reverse() : sorted;
}

function ToothButton({
  tooth,
  isSelected,
  onSelect,
}: {
  tooth: OdontogramTooth;
  isSelected: boolean;
  onSelect: () => void;
}) {
  const size = tooth.dentition === "primary" ? "h-8 w-8 text-xs" : "h-10 w-10 text-sm";

  return (
    <button
      type="button"
      onClick={onSelect}
      title={tooth.status.label}
      className={`flex ${size} items-center justify-center rounded-md border-2 font-medium transition ${
        isSelected ? "ring-2 ring-primary ring-offset-1" : ""
      } ${tooth.status.is_dashed ? "border-dashed" : "border-solid"}`}
      style={{
        backgroundColor: tooth.status.color ?? "#FFFFFF",
        borderColor: tooth.status.border ?? "#C8D4DC",
      }}
    >
      {tooth.display_label}
    </button>
  );
}

function QuadrantCell({
  teeth,
  screenSide,
  selectedFdi,
  onSelectTooth,
}: {
  teeth: OdontogramTooth[];
  screenSide: "left" | "right";
  selectedFdi: number | null;
  onSelectTooth: (tooth: OdontogramTooth) => void;
}) {
  const permanent = sortForDisplay(teeth.filter((tooth) => tooth.dentition === "permanent"), screenSide);
  const primary = sortForDisplay(teeth.filter((tooth) => tooth.dentition === "primary"), screenSide);

  return (
    <div className="flex flex-col gap-1">
      {permanent.length > 0 && (
        <div className="flex gap-1">
          {permanent.map((tooth) => (
            <ToothButton
              key={tooth.fdi}
              tooth={tooth}
              isSelected={selectedFdi === tooth.fdi}
              onSelect={() => onSelectTooth(tooth)}
            />
          ))}
        </div>
      )}
      {primary.length > 0 && (
        <div className="flex gap-1">
          {primary.map((tooth) => (
            <ToothButton
              key={tooth.fdi}
              tooth={tooth}
              isSelected={selectedFdi === tooth.fdi}
              onSelect={() => onSelectTooth(tooth)}
            />
          ))}
        </div>
      )}
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
  const cells: Record<CellKey, OdontogramTooth[]> = { ul: [], ur: [], ll: [], lr: [] };
  for (const tooth of teeth) {
    cells[cellKeyFor(tooth)].push(tooth);
  }

  return (
    <div dir="ltr" className="space-y-2 rounded-xl border border-border p-4">
      <div className="flex justify-center gap-6 border-b border-dashed border-border pb-2">
        <QuadrantCell teeth={cells.ul} screenSide="left" selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
        <QuadrantCell teeth={cells.ur} screenSide="right" selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
      </div>
      <div className="flex justify-center gap-6 pt-2">
        <QuadrantCell teeth={cells.ll} screenSide="left" selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
        <QuadrantCell teeth={cells.lr} screenSide="right" selectedFdi={selectedFdi} onSelectTooth={onSelectTooth} />
      </div>
    </div>
  );
}
