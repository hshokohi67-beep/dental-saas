"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions, recordToothCondition, voidToothCondition } from "@/services/dental";
import type { DentalCondition, GroupedFinding, ToothConditionEntry } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";

const archLabels: Record<string, string> = { upper: "فک بالا", lower: "فک پایین" };

interface BaseProps {
  patientId: string;
  onChanged: () => void;
}

/** یافته‌های سطح یک فک کامل (بالا/پایین). */
export function ArchFindings({ patientId, onChanged, findings }: BaseProps & { findings: GroupedFinding[] }) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);
  const [arch, setArch] = useState<"upper" | "lower">("upper");
  const [conditionId, setConditionId] = useState("");
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    listDentalConditions({ scope: "arch" }).then((response) => setConditions(response.data));
  }, []);

  async function handleAdd() {
    if (!conditionId) return;
    setError(null);
    try {
      await recordToothCondition(patientId, { dental_condition_catalog_id: conditionId, scope_type: "arch", arch });
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
    <div className="rounded-xl border border-border p-4">
      <h3 className="mb-2 text-sm font-bold">یافته‌های سطح فک (بالا/پایین)</h3>

      <ul className="mb-3 space-y-1 text-sm">
        {findings.flatMap((group) =>
          group.conditions.map((finding) => (
            <li key={finding.id} className="flex items-center justify-between">
              <span>
                {archLabels[group.arch ?? ""] ?? group.arch} — {finding.label}
              </span>
              <button type="button" className="text-xs text-red-600" onClick={() => handleVoid(finding.id)}>
                حذف
              </button>
            </li>
          )),
        )}
        {findings.length === 0 && <li className="text-muted">ثبت نشده</li>}
      </ul>

      {error && <p className="mb-2 text-sm text-red-600">{error}</p>}

      {conditions.length === 0 ? (
        <p className="text-xs text-muted">
          فعلاً هیچ خدمتی در کاتالوگ برای سطح «کل یک فک» تعریف نشده — این سطح آماده است ولی نیاز به تعریف کد در کاتالوگ
          دارد.
        </p>
      ) : (
        <div className="space-y-2">
          <div className="flex items-center gap-2">
            <select
              value={arch}
              onChange={(event) => setArch(event.target.value as "upper" | "lower")}
              className="rounded-lg border border-border px-3 py-2 text-sm"
            >
              <option value="upper">فک بالا</option>
              <option value="lower">فک پایین</option>
            </select>
            <Button type="button" disabled={!conditionId} onClick={handleAdd}>
              افزودن
            </Button>
          </div>
          <ConditionPicker conditions={conditions} value={conditionId} onChange={setConditionId} />
        </div>
      )}
    </div>
  );
}

/** یافته‌های کل دهان. */
export function WholeMouthFindings({
  patientId,
  onChanged,
  findings,
}: BaseProps & { findings: ToothConditionEntry[] }) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);
  const [conditionId, setConditionId] = useState("");
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    listDentalConditions({ scope: "whole_mouth" }).then((response) => setConditions(response.data));
  }, []);

  async function handleAdd() {
    if (!conditionId) return;
    setError(null);
    try {
      await recordToothCondition(patientId, { dental_condition_catalog_id: conditionId, scope_type: "whole_mouth" });
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
    <div className="rounded-xl border border-border p-4">
      <h3 className="mb-2 text-sm font-bold">یافته‌های کل دهان</h3>

      <ul className="mb-3 space-y-1 text-sm">
        {findings.map((finding) => (
          <li key={finding.id} className="flex items-center justify-between">
            <span>{finding.label}</span>
            <button type="button" className="text-xs text-red-600" onClick={() => handleVoid(finding.id)}>
              حذف
            </button>
          </li>
        ))}
        {findings.length === 0 && <li className="text-muted">ثبت نشده</li>}
      </ul>

      {error && <p className="mb-2 text-sm text-red-600">{error}</p>}

      <div className="space-y-2">
        <ConditionPicker conditions={conditions} value={conditionId} onChange={setConditionId} />
        <Button type="button" disabled={!conditionId} onClick={handleAdd} className="w-full">
          افزودن
        </Button>
      </div>
    </div>
  );
}
