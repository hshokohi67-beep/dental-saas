"use client";

import { useState } from "react";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { listPatients, mergePatients } from "@/services/patients";
import type { Patient } from "@/types/patient";

interface MergePatientDialogProps {
  survivor: Patient;
  onMerged: () => void;
  onCancel: () => void;
}

export function MergePatientDialog({ survivor, onMerged, onCancel }: MergePatientDialogProps) {
  const [query, setQuery] = useState("");
  const [results, setResults] = useState<Patient[]>([]);
  const [selected, setSelected] = useState<Patient | null>(null);
  const [isSearching, setIsSearching] = useState(false);
  const [isMerging, setIsMerging] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function search() {
    if (!query.trim()) return;
    setIsSearching(true);
    try {
      const response = await listPatients(query.trim());
      setResults(response.data.filter((patient) => patient.id !== survivor.id));
    } finally {
      setIsSearching(false);
    }
  }

  async function confirmMerge() {
    if (!selected) return;
    setError(null);
    setIsMerging(true);
    try {
      await mergePatients(survivor.id, selected.id);
      onMerged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ادغام پرونده‌ها.");
    } finally {
      setIsMerging(false);
    }
  }

  return (
    <div className="space-y-4 rounded-xl border border-border p-4">
      <h2 className="text-base font-bold">ادغام با پرونده‌ی تکراری</h2>
      <p className="text-sm text-muted">
        پرونده‌ی جست‌وجو شده در این پرونده («{survivor.full_name}») ادغام خواهد شد و به‌عنوان تکراری علامت‌گذاری
        می‌شود؛ اطلاعات پزشکی و تاریخچه‌ی آن حفظ و منتقل می‌شود.
      </p>

      <div className="flex gap-2">
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder="جست‌وجو با نام یا موبایل بیمار تکراری"
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
        />
        <Button type="button" onClick={search} disabled={isSearching}>
          جست‌وجو
        </Button>
      </div>

      {results.length > 0 && (
        <ul className="divide-y divide-border rounded-lg border border-border">
          {results.map((patient) => (
            <li key={patient.id}>
              <button
                type="button"
                onClick={() => setSelected(patient)}
                className={`w-full px-3 py-2 text-right text-sm ${
                  selected?.id === patient.id ? "bg-primary/10" : ""
                }`}
              >
                {patient.full_name} — {patient.mobile}
              </button>
            </li>
          ))}
        </ul>
      )}

      {selected && (
        <p className="text-sm">
          پرونده‌ی «{selected.full_name}» در «{survivor.full_name}» ادغام می‌شود.
        </p>
      )}

      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="flex gap-2">
        <Button type="button" disabled={!selected || isMerging} onClick={confirmMerge}>
          {isMerging ? "در حال ادغام..." : "تأیید ادغام"}
        </Button>
        <Button type="button" variant="ghost" onClick={onCancel}>
          انصراف
        </Button>
      </div>
    </div>
  );
}
