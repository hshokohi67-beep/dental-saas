"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listDentalConditions, recordToothCondition, voidToothCondition } from "@/services/dental";
import type { DentalCondition, OdontogramTooth, SurfaceRegion } from "@/types/dental";
import { ConditionPicker } from "./ConditionPicker";
import { ToothShape } from "./ToothShape";

interface ToothConditionPanelProps {
  patientId: string;
  tooth: OdontogramTooth;
  onChanged: () => void;
  onClose: () => void;
}

const surfaceLabels: Record<SurfaceRegion, string> = {
  mesial: "مزیال (طرف میانی)",
  distal: "دیستال (طرف انتهایی)",
  occlusal: "جونده/برشی",
  buccal: "باکال/لبی",
  lingual: "لینگوال/کامی",
};

export function ToothConditionPanel({ patientId, tooth, onChanged, onClose }: ToothConditionPanelProps) {
  const [conditions, setConditions] = useState<DentalCondition[]>([]);
  const [selectedConditionId, setSelectedConditionId] = useState("");
  const [notes, setNotes] = useState("");
  const [pendingSurfaces, setPendingSurfaces] = useState<Set<SurfaceRegion>>(new Set());
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    listDentalConditions({ scope: "tooth", dentition: tooth.dentition }).then((response) => setConditions(response.data));
  }, [tooth.dentition]);

  function toggleSurface(surface: SurfaceRegion) {
    setPendingSurfaces((current) => {
      const next = new Set(current);
      if (next.has(surface)) next.delete(surface);
      else next.add(surface);
      return next;
    });
  }

  async function handleAdd() {
    if (!selectedConditionId) return;
    setError(null);
    setIsSubmitting(true);
    try {
      const surfaces = Array.from(pendingSurfaces).map((surface) =>
        surface === "occlusal" && tooth.is_anterior ? "incisal" : surface,
      );
      await recordToothCondition(patientId, {
        dental_condition_catalog_id: selectedConditionId,
        scope_type: "tooth",
        tooth_number: tooth.fdi,
        notes: notes || undefined,
        surfaces: surfaces.length > 0 ? surfaces : undefined,
      });
      setSelectedConditionId("");
      setNotes("");
      setPendingSurfaces(new Set());
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

      <div className="flex items-start gap-4">
        <ToothShape
          arch={tooth.arch}
          screenSide={tooth.screen_side}
          size={110}
          label={tooth.display_label}
          isPrimary={tooth.dentition === "primary"}
          surfaceStatuses={tooth.surface_statuses}
          pendingSurfaces={pendingSurfaces}
          onToggleSurface={toggleSurface}
        />
        <div className="space-y-1 text-xs text-muted">
          <p>سطوحی که خدمت روی آن‌ها ثبت می‌شود را روی تصویر دندان انتخاب کنید (اختیاری؛ بدون انتخاب = کل دندان).</p>
          {Array.from(pendingSurfaces).map((surface) => (
            <p key={surface} className="font-medium text-foreground">
              • {surfaceLabels[surface]}
            </p>
          ))}
        </div>
      </div>

      <div className="flex flex-col gap-2 sm:flex-row">
        <ConditionPicker
          conditions={conditions}
          value={selectedConditionId}
          onChange={setSelectedConditionId}
          placeholder="جست‌وجوی خدمت/وضعیت..."
          className="w-full"
        />
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
