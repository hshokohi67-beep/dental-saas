"use client";

import { useState } from "react";
import { Button } from "@/components/Button";
import { JalaliDateField } from "@/components/JalaliDateField";
import { ApiError } from "@/lib/api";
import { createPatient, findDuplicatePatients } from "@/services/patients";
import type { CreatePatientInput, Patient } from "@/types/patient";

interface PatientFormProps {
  onCreated: (patient: { id: string; full_name: string }) => void;
  onCancel: () => void;
}

export function PatientForm({ onCreated, onCancel }: PatientFormProps) {
  const [form, setForm] = useState<CreatePatientInput>({
    first_name: "",
    last_name: "",
    mobile: "",
    national_id: "",
    date_of_birth: "",
  });
  const [error, setError] = useState<string | null>(null);
  const [duplicates, setDuplicates] = useState<Patient[] | null>(null);
  const [isChecking, setIsChecking] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function update<K extends keyof CreatePatientInput>(key: K, value: CreatePatientInput[K]) {
    setForm((current) => ({ ...current, [key]: value }));
    setDuplicates(null);
  }

  async function checkDuplicates(): Promise<boolean> {
    setIsChecking(true);
    try {
      const result = await findDuplicatePatients({
        mobile: form.mobile,
        national_id: form.national_id,
        first_name: form.first_name,
        last_name: form.last_name,
        date_of_birth: form.date_of_birth,
      });
      setDuplicates(result.data);
      return result.data.length > 0;
    } catch {
      return false;
    } finally {
      setIsChecking(false);
    }
  }

  async function submit(force = false) {
    setError(null);

    if (!force) {
      const hasDuplicates = await checkDuplicates();
      if (hasDuplicates) return;
    }

    setIsSubmitting(true);
    try {
      const response = await createPatient(form);
      onCreated({ id: response.data.id, full_name: response.data.full_name });
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ثبت بیمار.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <form
      onSubmit={(event) => {
        event.preventDefault();
        submit();
      }}
      className="space-y-4 rounded-xl border border-border p-4"
    >
      <h2 className="text-base font-bold">ثبت بیمار جدید</h2>

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <Field label="نام">
          <input
            required
            value={form.first_name}
            onChange={(event) => update("first_name", event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
        </Field>
        <Field label="نام خانوادگی">
          <input
            required
            value={form.last_name}
            onChange={(event) => update("last_name", event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
        </Field>
        <Field label="موبایل">
          <input
            required
            value={form.mobile}
            onChange={(event) => update("mobile", event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
            dir="ltr"
          />
        </Field>
        <Field label="کد ملی (اختیاری)">
          <input
            value={form.national_id}
            onChange={(event) => update("national_id", event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
            dir="ltr"
          />
        </Field>
        <Field label="تاریخ تولد (اختیاری)">
          <JalaliDateField value={form.date_of_birth} onChange={(value) => update("date_of_birth", value)} />
        </Field>
      </div>

      {duplicates && duplicates.length > 0 && (
        <div className="space-y-2 rounded-lg border border-amber-400 bg-amber-50 p-3 text-sm text-amber-900">
          <p className="font-medium">
            {duplicates.length} پرونده‌ی مشابه پیدا شد. لطفاً بررسی کنید که این بیمار قبلاً ثبت نشده باشد:
          </p>
          <ul className="list-inside list-disc">
            {duplicates.map((duplicate) => (
              <li key={duplicate.id}>
                {duplicate.full_name} — {duplicate.mobile}
              </li>
            ))}
          </ul>
          <Button type="button" variant="ghost" disabled={isSubmitting} onClick={() => submit(true)}>
            با این حال، بیمار جدید ثبت شود
          </Button>
        </div>
      )}

      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="flex gap-2">
        <Button type="submit" disabled={isSubmitting || isChecking}>
          {isChecking ? "در حال بررسی تکراری بودن..." : isSubmitting ? "در حال ثبت..." : "ثبت بیمار"}
        </Button>
        <Button type="button" variant="ghost" onClick={onCancel}>
          انصراف
        </Button>
      </div>
    </form>
  );
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="space-y-1 text-sm">
      <span className="text-muted">{label}</span>
      {children}
    </label>
  );
}
