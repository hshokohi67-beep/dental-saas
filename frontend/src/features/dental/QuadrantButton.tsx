"use client";

import { useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions, recordToothCondition, voidToothCondition } from "@/services/dental";
import type { DentalCondition, ToothConditionEntry } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";

interface QuadrantButtonProps {
  patientId: string;
  quadrant: 1 | 2 | 3 | 4;
  label: string;
  findings: ToothConditionEntry[];
  onChanged: () => void;
  /** When a half-arch service is locked in the active-service bar, clicking just toggles this quadrant as a target instead of opening the popover. */
  quickMode?: boolean;
  isPending?: boolean;
  onQuickToggle?: () => void;
}

/**
 * A half-jaw (نیم‌فک) quick-action button flanking the chart — matches the
 * reference clinic software's layout (quadrant controls sit beside the
 * teeth they cover, not in a separate section far below the chart).
 */
export function QuadrantButton({
  patientId,
  quadrant,
  label,
  findings,
  onChanged,
  quickMode = false,
  isPending = false,
  onQuickToggle,
}: QuadrantButtonProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [conditions, setConditions] = useState<DentalCondition[]>([]);
  const [conditionId, setConditionId] = useState("");
  const [error, setError] = useState<string | null>(null);

  function open() {
    setIsOpen(true);
    if (conditions.length === 0) {
      listDentalConditions({ scope: "half_arch" }).then((response) => setConditions(response.data));
    }
  }

  async function handleAdd() {
    if (!conditionId) return;
    setError(null);
    try {
      await recordToothCondition(patientId, { dental_condition_catalog_id: conditionId, scope_type: "quadrant", quadrant });
      setConditionId("");
      onChanged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ثبت.");
    }
  }

  async function handleVoid(id: string) {
    setError(null);
    try {
      await voidToothCondition(patientId, id);
      onChanged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در حذف.");
    }
  }

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => {
          if (quickMode) {
            onQuickToggle?.();
            return;
          }
          if (isOpen) setIsOpen(false);
          else open();
        }}
        className={`whitespace-nowrap rounded-lg border px-2 py-1 text-xs ${
          isPending
            ? "border-primary bg-primary/10 font-medium text-primary ring-1 ring-primary"
            : findings.length > 0
              ? "border-primary text-primary"
              : "border-border text-muted"
        }`}
      >
        {label}
        {findings.length > 0 && ` (${findings.length})`}
      </button>

      {!quickMode && isOpen && (
        <div className="absolute z-10 mt-1 w-72 space-y-2 rounded-lg border border-border bg-white p-3 shadow-md">
          <ul className="space-y-1 text-xs">
            {findings.map((finding) => (
              <li key={finding.id} className="flex items-center justify-between">
                <span>{finding.label}</span>
                <button type="button" className="text-red-600" onClick={() => handleVoid(finding.id)}>
                  حذف
                </button>
              </li>
            ))}
            {findings.length === 0 && <li className="text-muted">ثبت نشده</li>}
          </ul>

          {error && <p className="text-red-600">{error}</p>}

          <ConditionPicker conditions={conditions} value={conditionId} onChange={setConditionId} className="text-xs" />
          <Button type="button" disabled={!conditionId} onClick={handleAdd} className="w-full text-xs">
            افزودن
          </Button>
        </div>
      )}
    </div>
  );
}
