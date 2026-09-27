"use client";

import { useCallback, useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { getOdontogram, recordToothCondition, setChartMode } from "@/services/dental";
import type { ChartMode } from "@/types/patient";
import type { DentalCondition, Odontogram as OdontogramData, OdontogramTooth } from "@/types/dental";
import { ActiveServiceBar } from "./ActiveServiceBar";
import { ArchFindings, WholeMouthFindings } from "./NonToothFindings";
import { Odontogram } from "./Odontogram";
import { ToothConditionPanel } from "./ToothConditionPanel";

export function DentalChartTab({ patientId }: { patientId: string }) {
  const [odontogram, setOdontogram] = useState<OdontogramData | null>(null);
  const [selectedFdi, setSelectedFdi] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isForbidden, setIsForbidden] = useState(false);

  const [activeCondition, setActiveCondition] = useState<DentalCondition | null>(null);
  const [pendingTeeth, setPendingTeeth] = useState<Set<number>>(new Set());
  const [pendingQuadrants, setPendingQuadrants] = useState<Set<number>>(new Set());
  const [pendingArches, setPendingArches] = useState<Set<"upper" | "lower">>(new Set());
  const [isSubmittingBatch, setIsSubmittingBatch] = useState(false);
  const [batchError, setBatchError] = useState<string | null>(null);

  const reload = useCallback(() => {
    getOdontogram(patientId)
      .then((response) => setOdontogram(response.data))
      .catch((err) => {
        if (err instanceof ApiError && err.status === 403) {
          setIsForbidden(true);
        } else {
          setError(err instanceof ApiError ? err.message : "خطا در بارگذاری چارت.");
        }
      });
  }, [patientId]);

  useEffect(() => {
    reload();
  }, [reload]);

  async function handleChartModeChange(mode: ChartMode) {
    setError(null);
    try {
      await setChartMode(patientId, mode);
      setSelectedFdi(null);
      reload();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در تغییر حالت چارت.");
    }
  }

  function handleSelectActive(condition: DentalCondition | null) {
    setActiveCondition(condition);
    setPendingTeeth(new Set());
    setPendingQuadrants(new Set());
    setPendingArches(new Set());
    setBatchError(null);
    setSelectedFdi(null);
  }

  function handleSelectTooth(tooth: OdontogramTooth) {
    if (activeCondition?.scope === "tooth") {
      setPendingTeeth((current) => toggleInSet(current, tooth.fdi));
      return;
    }
    setSelectedFdi(tooth.fdi === selectedFdi ? null : tooth.fdi);
  }

  function handleToggleQuadrant(quadrant: number) {
    setPendingQuadrants((current) => toggleInSet(current, quadrant));
  }

  function handleToggleArch(arch: "upper" | "lower") {
    setPendingArches((current) => toggleInSet(current, arch));
  }

  function handleClearPending() {
    setPendingTeeth(new Set());
    setPendingQuadrants(new Set());
    setPendingArches(new Set());
    setBatchError(null);
  }

  async function handleConfirmBatch() {
    if (!activeCondition) return;
    setIsSubmittingBatch(true);
    setBatchError(null);
    try {
      const requests: Promise<unknown>[] = [];
      for (const fdi of pendingTeeth) {
        requests.push(
          recordToothCondition(patientId, {
            dental_condition_catalog_id: activeCondition.id,
            scope_type: "tooth",
            tooth_number: fdi,
          }),
        );
      }
      for (const quadrant of pendingQuadrants) {
        requests.push(
          recordToothCondition(patientId, {
            dental_condition_catalog_id: activeCondition.id,
            scope_type: "quadrant",
            quadrant,
          }),
        );
      }
      for (const arch of pendingArches) {
        requests.push(
          recordToothCondition(patientId, {
            dental_condition_catalog_id: activeCondition.id,
            scope_type: "arch",
            arch,
          }),
        );
      }
      await Promise.all(requests);
      setPendingTeeth(new Set());
      setPendingQuadrants(new Set());
      setPendingArches(new Set());
      reload();
    } catch (err) {
      setBatchError(err instanceof ApiError ? err.message : "خطا در ثبت.");
    } finally {
      setIsSubmittingBatch(false);
    }
  }

  async function handleRecordWholeMouth(condition: DentalCondition) {
    setIsSubmittingBatch(true);
    setBatchError(null);
    try {
      await recordToothCondition(patientId, { dental_condition_catalog_id: condition.id, scope_type: "whole_mouth" });
      reload();
    } catch (err) {
      setBatchError(err instanceof ApiError ? err.message : "خطا در ثبت.");
    } finally {
      setIsSubmittingBatch(false);
    }
  }

  if (isForbidden) {
    return <p className="text-muted">شما به چارت دندانی این بیمار دسترسی ندارید.</p>;
  }

  if (!odontogram) {
    return <p className="text-muted">در حال بارگذاری...</p>;
  }

  const selectedTooth = odontogram.teeth.find((tooth) => tooth.fdi === selectedFdi) ?? null;
  const totalPending = pendingTeeth.size + pendingQuadrants.size + pendingArches.size;

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <span className="text-sm text-muted">حالت چارت:</span>
        <Button
          type="button"
          variant={odontogram.chart_mode === "adult" ? "primary" : "ghost"}
          onClick={() => handleChartModeChange("adult")}
        >
          بزرگسال
        </Button>
        <Button
          type="button"
          variant={odontogram.chart_mode === "peds" ? "primary" : "ghost"}
          onClick={() => handleChartModeChange("peds")}
        >
          اطفال (شیری+دائمی)
        </Button>
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <ActiveServiceBar
        activeConditionId={activeCondition?.id ?? ""}
        onSelect={handleSelectActive}
        onRecordWholeMouth={handleRecordWholeMouth}
        isBusy={isSubmittingBatch}
      />

      {batchError && <p className="text-sm text-red-600">{batchError}</p>}

      {activeCondition && activeCondition.scope !== "whole_mouth" && totalPending > 0 && (
        <div className="flex items-center justify-between rounded-xl border border-primary/40 bg-primary/5 px-4 py-2 text-sm">
          <span>
            {totalPending} مورد برای «{activeCondition.label}» انتخاب شده
          </span>
          <div className="flex gap-2">
            <Button type="button" disabled={isSubmittingBatch} onClick={handleConfirmBatch}>
              تأیید
            </Button>
            <Button type="button" variant="ghost" onClick={handleClearPending}>
              انصراف
            </Button>
          </div>
        </div>
      )}

      <Odontogram
        patientId={patientId}
        teeth={odontogram.teeth}
        quadrantFindings={odontogram.quadrant_findings}
        selectedFdi={selectedFdi}
        onSelectTooth={handleSelectTooth}
        onChanged={reload}
        activeCondition={activeCondition}
        pendingTeeth={pendingTeeth}
        pendingQuadrants={pendingQuadrants}
        pendingArches={pendingArches}
        onToggleQuadrant={handleToggleQuadrant}
        onToggleArch={handleToggleArch}
      />

      {selectedTooth && (
        <ToothConditionPanel
          key={selectedTooth.fdi}
          patientId={patientId}
          tooth={selectedTooth}
          onChanged={reload}
          onClose={() => setSelectedFdi(null)}
        />
      )}

      <ArchFindings patientId={patientId} findings={odontogram.arch_findings} onChanged={reload} />
      <WholeMouthFindings patientId={patientId} findings={odontogram.whole_mouth_findings} onChanged={reload} />
    </div>
  );
}

function toggleInSet<T>(set: Set<T>, value: T): Set<T> {
  const next = new Set(set);
  if (next.has(value)) next.delete(value);
  else next.add(value);
  return next;
}
