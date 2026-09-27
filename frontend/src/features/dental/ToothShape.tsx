"use client";

import { useId } from "react";
import type { SurfaceRegion, ToothStatus } from "@/types/dental";

type SurfaceStatusMap = Record<SurfaceRegion, ToothStatus>;

const HEALTHY: ToothStatus = { code: "healthy", label: "سالم", color: "#FFFFFF", border: "#C8D4DC", is_dashed: false };

/**
 * Maps the tooth icon's four geometric regions (top/bottom/left/right
 * around the center) to the real dental surface they represent, given the
 * arch (buccal/lingual swap between upper and lower) and which half of the
 * screen this tooth is drawn on under the mirrored clinical convention
 * (mesial always faces the chart's vertical midline — business rules §1.2).
 */
function regionSurface(position: "top" | "bottom" | "left" | "right", arch: "upper" | "lower", screenSide: "left" | "right"): SurfaceRegion {
  if (position === "top") return arch === "upper" ? "buccal" : "lingual";
  if (position === "bottom") return arch === "upper" ? "lingual" : "buccal";
  if (position === "left") return screenSide === "left" ? "distal" : "mesial";
  return screenSide === "left" ? "mesial" : "distal";
}

interface ToothShapeProps {
  arch: "upper" | "lower";
  screenSide: "left" | "right";
  size: number;
  label: string;
  isPrimary?: boolean;
  isSelected?: boolean;
  onClick?: () => void;
  surfaceStatuses: SurfaceStatusMap;
  /** Which surfaces are toggled "on" for the condition currently being added (highlighted overlay). */
  pendingSurfaces?: Set<SurfaceRegion>;
  onToggleSurface?: (surface: SurfaceRegion) => void;
}

const REGIONS: { key: "top" | "bottom" | "left" | "right" | "center"; points: string }[] = [
  { key: "top", points: "6,6 94,6 68,32 32,32" },
  { key: "bottom", points: "6,94 94,94 68,68 32,68" },
  { key: "left", points: "6,6 6,94 32,68 32,32" },
  { key: "right", points: "94,6 94,94 68,68 68,32" },
  { key: "center", points: "32,32 68,32 68,68 32,68" },
];

/** A simplified anatomical crown silhouette — rounded dome top, tapering to a rounded base. */
const TOOTH_PATH = "M 8 34 C 8 12, 92 12, 92 34 L 88 78 C 88 96, 12 96, 12 78 Z";

export function ToothShape({
  arch,
  screenSide,
  size,
  label,
  isPrimary = false,
  isSelected = false,
  onClick,
  surfaceStatuses,
  pendingSurfaces,
  onToggleSurface,
}: ToothShapeProps) {
  const clipId = `tooth-crown-${useId()}`;

  return (
    <button
      type="button"
      onClick={onClick}
      className={`relative flex flex-col items-center ${onClick ? "cursor-pointer" : "cursor-default"}`}
      style={{ width: size }}
      title={label}
    >
      <svg
        viewBox="0 0 100 100"
        width={size}
        height={size}
        className={isSelected ? "rounded-md ring-2 ring-primary ring-offset-1" : ""}
      >
        <defs>
          {/* a rounded crown silhouette so the icon reads as a tooth, not a plain square */}
          <clipPath id={clipId}>
            <path d={TOOTH_PATH} />
          </clipPath>
        </defs>

        <g clipPath={`url(#${clipId})`}>
          {REGIONS.map((region) => {
            const surface = region.key === "center" ? "occlusal" : regionSurface(region.key, arch, screenSide);
            const status = surfaceStatuses[surface] ?? HEALTHY;
            const isPending = region.key !== "center" ? pendingSurfaces?.has(surface) : pendingSurfaces?.has("occlusal");

            return (
              <polygon
                key={region.key}
                points={region.points}
                fill={status.color ?? "#FFFFFF"}
                stroke={status.border ?? "#C8D4DC"}
                strokeWidth={1}
                strokeDasharray={status.is_dashed ? "3,2" : undefined}
                opacity={isPending ? 0.55 : 1}
                className={onToggleSurface ? "cursor-pointer" : ""}
                onClick={
                  onToggleSurface
                    ? (event) => {
                        event.stopPropagation();
                        onToggleSurface(surface);
                      }
                    : undefined
                }
              />
            );
          })}
        </g>

        <path
          d={TOOTH_PATH}
          fill="none"
          stroke={isPending(pendingSurfaces) ? "#2563eb" : "#94A3B8"}
          strokeWidth={1.5}
        />
      </svg>
      <span className={isPrimary ? "text-[10px]" : "text-xs"}>{label}</span>
    </button>
  );
}

function isPending(pendingSurfaces?: Set<SurfaceRegion>): boolean {
  return Boolean(pendingSurfaces && pendingSurfaces.size > 0);
}
