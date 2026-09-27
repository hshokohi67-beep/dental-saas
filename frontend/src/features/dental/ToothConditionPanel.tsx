"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions, recordToothCondition, voidToothCondition } from "@/services/dental";
import type { DentalCondition, OdontogramTooth } from "@/types/dental";

interface ToothConditionPanelProps {
  patientId: string;
  tooth: OdontogramTooth;
  onChanged: () => void;
  onClose: () => void;
}

export function ToothConditionPanel({ patientId, tooth, onChanged, onClose }: ToothConditionPanelProps) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);
  const [selectedConditionId, setSelectedConditionId] = useState("");
  const [notes, setNotes] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    listDentalConditions({ scope: "tooth", dentition: tooth.dentition }).then((response) => setConditions(response.data));
  }, [tooth.dentition]);

  async function handleAdd() {
    if (!selectedConditionId) return;
    setError(null);
    setIsSubmitting(true);
    try {
      await recordToothCondition(patientId, {
        dental_condition_catalog_id: selectedConditionId,
        scope_type: "tooth",
        tooth_number: tooth.fdi,
        notes: notes || undefined,
      });
      setSelectedConditionId("");
      setNotes("");
      onChanged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ثبت.");
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleVoid(conditionId: string) {
    setError(null);
    try {
      await voidToothCondition(patientId, conditionId);
      onChanged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در حذف.");
    }
  }

  return (
    <div className="space-y-3 rounded-xl border border-border p-4">
      <div className="flex items-center justify-between">
        <h3 className="text-sm font-bold">
          دندان {tooth.display_label} ({tooth.dentition === "primary" ? "شیری" : "دائمی"})
        </h3>
        <button type="button" onClick={onClose} className="text-xs text-muted">
          بستن
        </button>
      </div>

      <ul className="space-y-1 text-sm">
        {tooth.conditions.map((condition) => (
          <li key={condition.id} className="flex items-center justify-between">
            <span>{condition.label}</span>
            <button type="button" className="text-xs text-red-600" onClick={() => handleVoid(condition.id)}>
              حذف
            </button>
          </li>
        ))}
        {tooth.conditions.length === 0 && <li className="text-muted">ثبتی وجود ندارد.</li>}
      </ul>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="flex flex-col gap-2 sm:flex-row">
        <select
          value={selectedConditionId}
          onChange={(event) => setSelectedConditionId(event.target.value)}
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
        >
          <option value="">انتخاب خدمت/وضعیت...</option>
          {conditions.map((condition) => (
            <option key={condition.id} value={condition.id}>
              {condition.label}
            </option>
          ))}
        </select>
        <input
          value={notes}
          onChange={(event) => setNotes(event.target.value)}
          placeholder="یادداشت (اختیاری)"
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
        />
        <Button type="button" disabled={!selectedConditionId || isSubmitting} onClick={handleAdd}>
          افزودن
        </Button>
      </div>
    </div>
  );
}
