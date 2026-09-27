"use client";

import { useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions, recordToothCondition, voidToothCondition } from "@/services/dental";
import type { DentalCondition, RecordToothConditionInput, ToothConditionEntry } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";

interface ChartScopeButtonProps {
  patientId: string;
  label: string;
  /** The catalog's own scope name (`DentalCondition.scope`), used to fetch the right services. */
  catalogScope: "half_arch" | "arch" | "whole_mouth";
  /** Everything `recordToothCondition` needs besides the catalog id — e.g. `{ scope_type: "arch", arch: "upper" }`. */
  recordParams: Omit<RecordToothConditionInput, "dental_condition_catalog_id">;
  findings: ToothConditionEntry[];
  onChanged: () => void;
  fullWidth?: boolean;
}

/**
 * A click-to-open quick-action button for a non-tooth scope (half-jaw, jaw,
 * or whole mouth): shows what's already recorded at that scope and lets you
 * add more, right where that scope lives on the chart — not in a separate
 * list section far below it.
 */
export function ChartScopeButton({
  patientId,
  label,
  catalogScope,
  recordParams,
  findings,
  onChanged,
  fullWidth = false,
}: ChartScopeButtonProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [conditions, setConditions] = useState<DentalCondition[] | null>(null);
  const [conditionId, setConditionId] = useState("");
  const [error, setError] = useState<string | null>(null);

  function open() {
    setIsOpen(true);
    if (conditions === null) {
      listDentalConditions({ scope: catalogScope }).then((response) => setConditions(response.data));
    }
  }

  async function handleAdd() {
    if (!conditionId) return;
    setError(null);
    try {
      await recordToothCondition(patientId, { dental_condition_catalog_id: conditionId, ...recordParams });
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
    <div className={`relative ${fullWidth ? "w-full" : ""}`}>
      <button
        type="button"
        onClick={() => (isOpen ? setIsOpen(false) : open())}
        className={`rounded-lg border py-1 text-xs ${fullWidth ? "w-full" : "whitespace-nowrap px-2"} ${
          findings.length > 0
            ? "border-primary font-medium text-primary"
            : "border-border text-muted"
        }`}
      >
        {label}
        {findings.length > 0 && ` (${findings.length})`}
      </button>

      {isOpen && (
        <div className="absolute z-10 mt-1 w-72 space-y-2 rounded-lg border border-border bg-white p-3 text-right shadow-md">
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

          {conditions === null ? (
            <p className="text-muted">در حال بارگذاری خدمات...</p>
          ) : conditions.length === 0 ? (
            <p className="text-muted">
              فعلاً هیچ خدمتی در کاتالوگ برای این سطح تعریف نشده — نیاز به تعریف کد در کاتالوگ دارد.
            </p>
          ) : (
            <>
              <ConditionPicker conditions={conditions} value={conditionId} onChange={setConditionId} className="text-xs" />
              <Button type="button" disabled={!conditionId} onClick={handleAdd} className="w-full text-xs">
                افزودن
              </Button>
            </>
          )}
        </div>
      )}
    </div>
  );
}
