"use client";

import { useCallback, useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { Button } from "@/components/Button";
import { ClinicalAlerts } from "@/features/patients/ClinicalAlerts";
import { MergePatientDialog } from "@/features/patients/MergePatientDialog";
import { ApiError } from "@/lib/api";
import {
  addAllergy,
  addMedicalCondition,
  addMedication,
  getPatient,
  listMedicalConditions,
  listPatientTimeline,
  removeAllergy,
  removeMedicalCondition,
  updatePatient,
} from "@/services/patients";
import type { MedicalCondition, PatientDetail, PatientTimelineEvent } from "@/types/patient";

type Tab = "info" | "medical" | "timeline";

const genderLabels: Record<string, string> = { male: "مرد", female: "زن", other: "سایر" };

export default function PatientDetailPage() {
  const params = useParams<{ id: string }>();
  const patientId = params.id;

  const [patient, setPatient] = useState<PatientDetail | null>(null);
  const [tab, setTab] = useState<Tab>("info");
  const [isMerging, setIsMerging] = useState(false);
  const [notes, setNotes] = useState("");

  const reload = useCallback(() => {
    getPatient(patientId).then((response) => {
      setPatient(response.data);
      setNotes(response.data.notes ?? "");
    });
  }, [patientId]);

  useEffect(() => {
    reload();
  }, [reload]);

  if (!patient) {
    return (
      <main className="flex flex-1 items-center justify-center">
        <p className="text-muted">در حال بارگذاری...</p>
      </main>
    );
  }

  return (
    <main className="mx-auto w-full max-w-3xl flex-1 space-y-6 p-6">
      <div className="space-y-1">
        <h1 className="text-lg font-bold">{patient.full_name}</h1>
        <p className="text-sm text-muted" dir="ltr">
          {patient.mobile}
        </p>
      </div>

      <ClinicalAlerts alerts={patient.medical?.alerts ?? []} />

      <nav className="flex gap-2 border-b border-border">
        {(
          [
            ["info", "اطلاعات"],
            ["medical", "پرونده‌ی پزشکی"],
            ["timeline", "تایم‌لاین"],
          ] as [Tab, string][]
        ).map(([key, label]) => (
          <button
            key={key}
            onClick={() => setTab(key)}
            className={`px-3 py-2 text-sm ${tab === key ? "border-b-2 border-primary font-medium" : "text-muted"}`}
          >
            {label}
          </button>
        ))}
      </nav>

      {tab === "info" && (
        <InfoTab
          patient={patient}
          notes={notes}
          onNotesChange={setNotes}
          onSaved={reload}
          onMergeRequested={() => setIsMerging(true)}
        />
      )}

      {tab === "medical" && <MedicalTab patient={patient} onChanged={reload} />}

      {tab === "timeline" && <TimelineTab patientId={patient.id} />}

      {isMerging && (
        <MergePatientDialog
          survivor={patient}
          onMerged={() => {
            setIsMerging(false);
            reload();
          }}
          onCancel={() => setIsMerging(false)}
        />
      )}
    </main>
  );
}

function InfoTab({
  patient,
  notes,
  onNotesChange,
  onSaved,
  onMergeRequested,
}: {
  patient: PatientDetail;
  notes: string;
  onNotesChange: (value: string) => void;
  onSaved: () => void;
  onMergeRequested: () => void;
}) {
  const [isSaving, setIsSaving] = useState(false);

  async function saveNotes() {
    setIsSaving(true);
    try {
      await updatePatient(patient.id, { notes });
      onSaved();
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <div className="space-y-4 rounded-xl border border-border p-4">
      <dl className="grid grid-cols-2 gap-3 text-sm">
        <Field label="کد ملی" value={patient.national_id ?? "—"} />
        <Field label="جنسیت" value={patient.gender ? genderLabels[patient.gender] : "—"} />
        <Field label="تاریخ تولد" value={patient.date_of_birth ?? "—"} />
        <Field label="شعبه" value={patient.branch_name ?? "—"} />
        <Field label="وضعیت" value={patient.status === "active" ? "فعال" : patient.status} />
        {patient.merged_from_count > 0 && (
          <Field label="پرونده‌های ادغام‌شده" value={String(patient.merged_from_count)} />
        )}
      </dl>

      <div className="space-y-1">
        <label className="text-sm text-muted" htmlFor="notes">
          یادداشت
        </label>
        <textarea
          id="notes"
          value={notes}
          onChange={(event) => onNotesChange(event.target.value)}
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          rows={3}
        />
      </div>

      <div className="flex gap-2">
        <Button onClick={saveNotes} disabled={isSaving}>
          {isSaving ? "در حال ذخیره..." : "ذخیره"}
        </Button>
        <Button variant="ghost" onClick={onMergeRequested}>
          ادغام با بیمار دیگر
        </Button>
      </div>
    </div>
  );
}

function Field({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <dt className="text-muted">{label}</dt>
      <dd className="font-medium">{value}</dd>
    </div>
  );
}

function MedicalTab({ patient, onChanged }: { patient: PatientDetail; onChanged: () => void }) {
  const [conditions, setConditions] = useState<MedicalCondition[]>([]);
  const [selectedConditionId, setSelectedConditionId] = useState("");
  const [allergen, setAllergen] = useState("");
  const [severity, setSeverity] = useState("moderate");
  const [medicationName, setMedicationName] = useState("");
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    listMedicalConditions().then((response) => setConditions(response.data));
  }, []);

  if (!patient.medical) {
    return <p className="text-muted">شما به پرونده‌ی پزشکی این بیمار دسترسی ندارید.</p>;
  }

  const medical = patient.medical;

  async function handle(action: () => Promise<unknown>) {
    setError(null);
    try {
      await action();
      onChanged();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا رخ داد.");
    }
  }

  return (
    <div className="space-y-6">
      {error && <p className="text-sm text-red-600">{error}</p>}

      <section className="space-y-2 rounded-xl border border-border p-4">
        <h2 className="text-sm font-bold">بیماری‌های زمینه‌ای</h2>
        <ul className="space-y-1 text-sm">
          {medical.conditions.map((condition) => (
            <li key={condition.id} className="flex items-center justify-between">
              <span>{condition.label}</span>
              <button
                className="text-xs text-red-600"
                onClick={() => handle(() => removeMedicalCondition(patient.id, condition.id))}
              >
                حذف
              </button>
            </li>
          ))}
          {medical.conditions.length === 0 && <li className="text-muted">ثبت نشده</li>}
        </ul>
        <div className="flex gap-2">
          <select
            value={selectedConditionId}
            onChange={(event) => setSelectedConditionId(event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          >
            <option value="">انتخاب بیماری زمینه‌ای...</option>
            {conditions.map((condition) => (
              <option key={condition.id} value={condition.id}>
                {condition.label}
              </option>
            ))}
          </select>
          <Button
            type="button"
            disabled={!selectedConditionId}
            onClick={() =>
              handle(() => addMedicalCondition(patient.id, selectedConditionId)).then(() =>
                setSelectedConditionId(""),
              )
            }
          >
            افزودن
          </Button>
        </div>
      </section>

      <section className="space-y-2 rounded-xl border border-border p-4">
        <h2 className="text-sm font-bold">حساسیت‌های دارویی</h2>
        <ul className="space-y-1 text-sm">
          {medical.allergies.map((allergy) => (
            <li key={allergy.id} className="flex items-center justify-between">
              <span>
                {allergy.allergen} {allergy.severity ? `(${allergy.severity})` : ""}
              </span>
              <button
                className="text-xs text-red-600"
                onClick={() => handle(() => removeAllergy(patient.id, allergy.id))}
              >
                حذف
              </button>
            </li>
          ))}
          {medical.allergies.length === 0 && <li className="text-muted">ثبت نشده</li>}
        </ul>
        <div className="flex gap-2">
          <input
            value={allergen}
            onChange={(event) => setAllergen(event.target.value)}
            placeholder="نام دارو یا ماده‌ی حساسیت‌زا"
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
          <select
            value={severity}
            onChange={(event) => setSeverity(event.target.value)}
            className="rounded-lg border border-border px-3 py-2 text-sm"
          >
            <option value="mild">خفیف</option>
            <option value="moderate">متوسط</option>
            <option value="severe">شدید</option>
          </select>
          <Button
            type="button"
            disabled={!allergen.trim()}
            onClick={() =>
              handle(() => addAllergy(patient.id, { allergen, severity })).then(() => setAllergen(""))
            }
          >
            افزودن
          </Button>
        </div>
      </section>

      <section className="space-y-2 rounded-xl border border-border p-4">
        <h2 className="text-sm font-bold">داروهای مصرفی</h2>
        <ul className="space-y-1 text-sm">
          {medical.medications.map((medication) => (
            <li key={medication.id}>
              {medication.name} {medication.dosage ? `— ${medication.dosage}` : ""}
              {!medication.is_active && " (غیرفعال)"}
            </li>
          ))}
          {medical.medications.length === 0 && <li className="text-muted">ثبت نشده</li>}
        </ul>
        <div className="flex gap-2">
          <input
            value={medicationName}
            onChange={(event) => setMedicationName(event.target.value)}
            placeholder="نام دارو"
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
          <Button
            type="button"
            disabled={!medicationName.trim()}
            onClick={() =>
              handle(() => addMedication(patient.id, { name: medicationName })).then(() =>
                setMedicationName(""),
              )
            }
          >
            افزودن
          </Button>
        </div>
      </section>
    </div>
  );
}

function TimelineTab({ patientId }: { patientId: string }) {
  const [events, setEvents] = useState<PatientTimelineEvent[] | null>(null);

  useEffect(() => {
    listPatientTimeline(patientId).then((response) => setEvents(response.data));
  }, [patientId]);

  if (!events) return <p className="text-muted">در حال بارگذاری...</p>;
  if (events.length === 0) return <p className="text-muted">رویدادی ثبت نشده است.</p>;

  return (
    <ol className="space-y-3 border-r-2 border-border pr-4">
      {events.map((event) => (
        <li key={event.id} className="text-sm">
          <p>{event.description}</p>
          <p className="text-xs text-muted">
            {new Date(event.occurred_at).toLocaleString("fa-IR")}
            {event.recorded_by ? ` — ${event.recorded_by}` : ""}
          </p>
        </li>
      ))}
    </ol>
  );
}
