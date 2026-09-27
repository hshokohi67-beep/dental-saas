import { describe, expect, it } from "vitest";
import { sortForDisplay } from "./Odontogram";
import type { OdontogramTooth } from "@/types/dental";

function tooth(position: number): OdontogramTooth {
  return {
    fdi: position,
    quadrant: 1,
    position,
    dentition: "permanent",
    arch: "upper",
    patient_side: "right",
    screen_side: "left",
    display_label: String(position),
    status: { code: "healthy", label: "سالم", color: "#FFFFFF", border: "#C8D4DC", is_dashed: false },
    conditions: [],
  };
}

describe("sortForDisplay", () => {
  // Business rules §1.2: tooth #1 (central) is always closest to the
  // midline. On the screen-left half, the midline sits at that box's right
  // edge, so reading left-to-right must go from the highest position down
  // to 1 (the most error-prone part of any odontogram implementation).
  it("orders the screen-left half from the highest position down to 1", () => {
    const teeth = [tooth(1), tooth(3), tooth(2)];

    const ordered = sortForDisplay(teeth, "left").map((t) => t.position);

    expect(ordered).toEqual([3, 2, 1]);
  });

  it("orders the screen-right half from 1 up to the highest position", () => {
    const teeth = [tooth(3), tooth(1), tooth(2)];

    const ordered = sortForDisplay(teeth, "right").map((t) => t.position);

    expect(ordered).toEqual([1, 2, 3]);
  });
});
