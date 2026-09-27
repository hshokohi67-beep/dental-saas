"use client";

import { useCallback, useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { getOdontogram, setChartMode } from "@/services/dental";
import type { ChartMode } from "@/types/patient";
import type { Odontogram as OdontogramData, OdontogramTooth } from "@/types/dental";
import { ArchFindings, QuadrantFindings, WholeMouthFindings } from "./NonToothFindings";
import { Odontogram } from "./Odontogram";
import { ToothConditionPanel } from "./ToothConditionPanel";

export function DentalChartTab({ patientId }: { patientId: string }) {
  const [odontogram, setOdontogram] = useState<OdontogramData | null>(null);
  const [selectedFdi, setSelectedFdi] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isForbidden, setIsForbidden] = useState(false);

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

  if (isForbidden) {
    return <p className="text-muted">شما به چارت دندانی این بیمار دسترسی ندارید.</p>;
  }

  if (!odontogram) {
    return <p className="text-muted">در حال بارگذاری...</p>;
  }

  const selectedTooth = odontogram.teeth.find((tooth) => tooth.fdi === selectedFdi) ?? null;

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

      <Odontogram
        teeth={odontogram.teeth}
        selectedFdi={selectedFdi}
        onSelectTooth={(tooth: OdontogramTooth) => setSelectedFdi(tooth.fdi === selectedFdi ? null : tooth.fdi)}
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

      <QuadrantFindings patientId={patientId} findings={odontogram.quadrant_findings} onChanged={reload} />
      <ArchFindings patientId={patientId} findings={odontogram.arch_findings} onChanged={reload} />
      <WholeMouthFindings patientId={patientId} findings={odontogram.whole_mouth_findings} onChanged={reload} />
    </div>
  );
}
