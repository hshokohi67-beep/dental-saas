"use client";

import { useCallback, useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { getOdontogram, recordToothCondition, setChartMode } from "@/services/dental";
import type { ChartMode } from "@/types/patient";
import type { DentalCondition, Odontogram as OdontogramData, OdontogramTooth, SurfaceRegion } from "@/types/dental";
import { ActiveServiceBar } from "./ActiveServiceBar";
import { ChartScopeButton } from "./ChartScopeButton";
import { Odontogram } from "./Odontogram";
import { ToothConditionPanel } from "./ToothConditionPanel";
import { ToothShape } from "./ToothShape";

const SURFACE_LABELS: Record<SurfaceRegion, string> = {
  mesial: "M",
  distal: "D",
  occlusal: "O",
  buccal: "B",
  lingual: "L",
};

export function DentalChartTab({ patientId }: { patientId: string }) {
  const [odontogram, setOdontogram] = useState<OdontogramData | null>(null);
  const [selectedFdi, setSelectedFdi] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isForbidden, setIsForbidden] = useState(false);

  const [activeCondition, setActiveCondition] = useState<DentalCondition | null>(null);
  /** fdi -> surfaces chosen for that tooth (empty set = whole tooth). */
  const [pendingTeeth, setPendingTeeth] = useState<Map<number, Set<SurfaceRegion>>>(new Map());
  const [surfacePanelFdi, setSurfacePanelFdi] = useState<number | null>(null);
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
    setPendingTeeth(new Map());
    setSurfacePanelFdi(null);
    setBatchError(null);
    setSelectedFdi(null);
  }

  function handleSelectTooth(tooth: OdontogramTooth) {
    if (activeCondition?.scope === "tooth") {
      const isCurrentlyPending = pendingTeeth.has(tooth.fdi);
      setPendingTeeth((current) => {
        const next = new Map(current);
        if (isCurrentlyPending) next.delete(tooth.fdi);
        else next.set(tooth.fdi, new Set());
        return next;
      });
      setSurfacePanelFdi((currentPanelFdi) => {
        if (isCurrentlyPending) return currentPanelFdi === tooth.fdi ? null : currentPanelFdi;
        return tooth.fdi;
      });
      return;
    }
    setSelectedFdi(tooth.fdi === selectedFdi ? null : tooth.fdi);
  }

  function handleRemovePendingTooth(fdi: number) {
    setPendingTeeth((current) => {
      const next = new Map(current);
      next.delete(fdi);
      return next;
    });
    setSurfacePanelFdi((current) => (current === fdi ? null : current));
  }

  function handleToggleSurface(fdi: number, surface: SurfaceRegion) {
    setPendingTeeth((current) => {
      const next = new Map(current);
      const surfaces = new Set(next.get(fdi) ?? []);
      if (surfaces.has(surface)) surfaces.delete(surface);
      else surfaces.add(surface);
      next.set(fdi, surfaces);
      return next;
    });
  }

  async function handleConfirmBatch() {
    if (!activeCondition || !odontogram) return;
    setIsSubmittingBatch(true);
    setBatchError(null);
    try {
      const requests = Array.from(pendingTeeth.entries()).map(([fdi, surfaces]) => {
        const tooth = odontogram.teeth.find((candidate) => candidate.fdi === fdi);
        const normalizedSurfaces = Array.from(surfaces).map((surface) =>
          surface === "occlusal" && tooth?.is_anterior ? "incisal" : surface,
        );

        return recordToothCondition(patientId, {
          dental_condition_catalog_id: activeCondition.id,
          scope_type: "tooth",
          tooth_number: fdi,
          surfaces: normalizedSurfaces.length > 0 ? normalizedSurfaces : undefined,
        });
      });
      await Promise.all(requests);
      setPendingTeeth(new Map());
      setSurfacePanelFdi(null);
      reload();
    } catch (err) {
      setBatchError(err instanceof ApiError ? err.message : "خطا در ثبت.");
    } finally {
      setIsSubmittingBatch(false);
    }
  }

  function handleClearPending() {
    setPendingTeeth(new Map());
    setSurfacePanelFdi(null);
    setBatchError(null);
  }

  if (isForbidden) {
    return <p className="text-muted">شما به چارت دندانی این بیمار دسترسی ندارید.</p>;
  }

  if (!odontogram) {
    return <p className="text-muted">در حال بارگذاری...</p>;
  }

  const selectedTooth = odontogram.teeth.find((tooth) => tooth.fdi === selectedFdi) ?? null;
  const panelTooth = odontogram.teeth.find((tooth) => tooth.fdi === surfacePanelFdi) ?? null;

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center gap-2">
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

        <span className="mx-2 h-5 border-r border-border" />

        <ChartScopeButton
          patientId={patientId}
          label="کل دهان"
          catalogScope="whole_mouth"
          recordParams={{ scope_type: "whole_mouth" }}
          findings={odontogram.whole_mouth_findings}
          onChanged={reload}
        />
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <ActiveServiceBar activeConditionId={activeCondition?.id ?? ""} onSelect={handleSelectActive} />

      {batchError && <p className="text-sm text-red-600">{batchError}</p>}

      {activeCondition && pendingTeeth.size > 0 && (
        <div className="space-y-2 rounded-xl border border-primary/40 bg-primary/5 px-4 py-3 text-sm">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="flex flex-wrap items-center gap-1">
              <span className="ml-1">«{activeCondition.label}» روی:</span>
              {Array.from(pendingTeeth.entries()).map(([fdi, surfaces]) => {
                const tooth = odontogram.teeth.find((candidate) => candidate.fdi === fdi);
                const surfaceAbbrev = Array.from(surfaces)
                  .map((surface) => SURFACE_LABELS[surface])
                  .join("");
                return (
                  <button
                    key={fdi}
                    type="button"
                    onClick={() => setSurfacePanelFdi(fdi)}
                    className={`rounded-full border px-2 py-0.5 text-xs ${
                      surfacePanelFdi === fdi ? "border-primary bg-primary/10 text-primary" : "border-border"
                    }`}
                  >
                    {tooth?.display_label ?? fdi}
                    {surfaceAbbrev && ` (${surfaceAbbrev})`}
                    <span
                      className="mr-1 text-red-600"
                      onClick={(event) => {
                        event.stopPropagation();
                        handleRemovePendingTooth(fdi);
                      }}
                    >
                      ×
                    </span>
                  </button>
                );
              })}
            </div>
            <div className="flex gap-2">
              <Button type="button" disabled={isSubmittingBatch} onClick={handleConfirmBatch}>
                تأیید
              </Button>
              <Button type="button" variant="ghost" onClick={handleClearPending}>
                انصراف
              </Button>
            </div>
          </div>

          {panelTooth && (
            <div className="flex items-center gap-3 border-t border-primary/20 pt-2">
              <ToothShape
                arch={panelTooth.arch}
                screenSide={panelTooth.screen_side}
                size={90}
                label={panelTooth.display_label}
                isPrimary={panelTooth.dentition === "primary"}
                isAnterior={panelTooth.is_anterior}
                surfaceStatuses={panelTooth.surface_statuses}
                pendingSurfaces={pendingTeeth.get(panelTooth.fdi)}
                onToggleSurface={(surface) => handleToggleSurface(panelTooth.fdi, surface)}
              />
              <p className="text-xs text-muted">
                برای دندان {panelTooth.display_label}: روی تصویر کلیک کنید تا سطح موردنظر انتخاب شود (اختیاری؛ بدون
                انتخاب = کل دندان).
              </p>
            </div>
          )}
        </div>
      )}

      <Odontogram
        patientId={patientId}
        teeth={odontogram.teeth}
        quadrantFindings={odontogram.quadrant_findings}
        archFindings={odontogram.arch_findings}
        selectedFdi={selectedFdi}
        onSelectTooth={handleSelectTooth}
        onChanged={reload}
        activeCondition={activeCondition}
        pendingTeeth={pendingTeeth}
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
    </div>
  );
}
