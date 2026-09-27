"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { listDentalConditions } from "@/services/dental";
import type { DentalCondition } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";

interface ActiveServiceBarProps {
  activeConditionId: string;
  onSelect: (condition: DentalCondition | null) => void;
  onRecordWholeMouth: (condition: DentalCondition) => void;
  isBusy: boolean;
}

/**
 * "Lock" a service once here, then apply it to as many teeth/quadrants/jaws
 * as needed on the chart below without reopening this picker each time —
 * picking a different chip swaps the active service and the chart clears
 * whatever was mid-selection for the previous one.
 */
export function ActiveServiceBar({ activeConditionId, onSelect, onRecordWholeMouth, isBusy }: ActiveServiceBarProps) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);

  useEffect(() => {
    listDentalConditions().then((response) => setConditions(response.data));
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
            {active.scope === "whole_mouth" && (
              <Button type="button" onClick={() => onRecordWholeMouth(active)} disabled={isBusy}>
                ثبت برای کل دهان
              </Button>
            )}
            <button type="button" className="text-muted" onClick={() => onSelect(null)}>
              لغو
            </button>
          </div>
        )}
      </div>

      <ConditionPicker conditions={conditions} value={activeConditionId} onChange={handleChange} />
    </div>
  );
}
