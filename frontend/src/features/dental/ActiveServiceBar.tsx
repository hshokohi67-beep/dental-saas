"use client";

import { useEffect, useState } from "react";
import { listDentalConditions } from "@/services/dental";
import type { DentalCondition } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";

interface ActiveServiceBarProps {
  activeConditionId: string;
  onSelect: (condition: DentalCondition | null) => void;
}

/**
 * "Lock" a tooth-level service once here, then apply it to as many teeth as
 * needed on the chart below without reopening this picker each time —
 * picking a different chip swaps the active service and the chart clears
 * whatever was mid-selection for the previous one. Only tooth-scoped
 * services live here; quadrant/jaw/whole-mouth services have their own
 * click-to-open button right where they belong on the chart.
 */
export function ActiveServiceBar({ activeConditionId, onSelect }: ActiveServiceBarProps) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);

  useEffect(() => {
    listDentalConditions({ scope: "tooth" }).then((response) => setConditions(response.data));
  }, []);

  const active = conditions.find((condition) => condition.id === activeConditionId) ?? null;

  function handleChange(id: string) {
    onSelect(conditions.find((condition) => condition.id === id) ?? null);
  }

  return (
    <div className="rounded-xl border border-border p-3">
      <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
        <h3 className="text-sm font-bold">خدمت</h3>
        {active && (
          <div className="flex items-center gap-2 text-xs">
            <span className="rounded-full border border-primary bg-primary/10 px-2 py-1 font-medium text-primary">
              فعال: {active.label}
            </span>
            <button type="button" className="text-muted" onClick={() => onSelect(null)}>
              لغو
            </button>
          </div>
        )}
      </div>

      <p className="mb-2 text-xs text-muted">
        برای ثبت سریع‌تر: ابتدا خدمت موردنظر را از فهرست زیر انتخاب کنید (قفل می‌شود)، سپس دندان(های) موردنظر را روی
        چارت انتخاب و «تأیید» را بزنید. برای پایان دادن به حالت ثبت سریع، دوباره روی همان خدمت کلیک کنید.
      </p>

      <ConditionPicker conditions={conditions} value={activeConditionId} onChange={handleChange} />
    </div>
  );
}
