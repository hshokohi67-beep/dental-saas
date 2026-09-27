"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { Button } from "@/components/Button";
import { PatientForm } from "@/features/patients/PatientForm";
import { listPatients } from "@/services/patients";
import type { Patient } from "@/types/patient";

export default function PatientsPage() {
  const [query, setQuery] = useState("");
  const [patients, setPatients] = useState<Patient[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const load = useCallback((search: string) => {
    return listPatients(search)
      .then((response) => setPatients(response.data))
      .finally(() => setIsLoading(false));
  }, []);

  useEffect(() => {
    void load("");
  }, [load]);

  return (
    <main className="mx-auto w-full max-w-3xl flex-1 space-y-6 p-6">
      <div className="flex items-center justify-between">
        <h1 className="text-lg font-bold">بیماران</h1>
        <Button onClick={() => setIsCreating((current) => !current)}>
          {isCreating ? "بستن فرم" : "بیمار جدید"}
        </Button>
      </div>

      {isCreating && (
        <PatientForm
          onCreated={() => {
            setIsCreating(false);
            load(query);
          }}
          onCancel={() => setIsCreating(false)}
        />
      )}

      <form
        onSubmit={(event) => {
          event.preventDefault();
          setIsLoading(true);
          void load(query);
        }}
        className="flex gap-2"
      >
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder="جست‌وجو با نام، موبایل یا کد ملی"
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
        />
        <Button type="submit">جست‌وجو</Button>
      </form>

      {isLoading ? (
        <p className="text-muted">در حال بارگذاری...</p>
      ) : patients.length === 0 ? (
        <p className="text-muted">بیماری یافت نشد.</p>
      ) : (
        <ul className="divide-y divide-border rounded-xl border border-border">
          {patients.map((patient) => (
            <li key={patient.id}>
              <Link
                href={`/patients/${patient.id}`}
                className="flex items-center justify-between px-4 py-3 text-sm hover:bg-black/5"
              >
                <span className="font-medium">{patient.full_name}</span>
                <span className="text-muted" dir="ltr">
                  {patient.mobile}
                </span>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </main>
  );
}
