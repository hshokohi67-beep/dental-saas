import type { ClinicalAlert } from "@/types/patient";

export function ClinicalAlerts({ alerts }: { alerts: ClinicalAlert[] }) {
  if (alerts.length === 0) return null;

  return (
    <div className="space-y-1 rounded-xl border border-red-300 bg-red-50 p-3">
      <p className="text-sm font-bold text-red-800">هشدارهای بالینی</p>
      <ul className="list-inside list-disc text-sm text-red-800">
        {alerts.map((alert, index) => (
          <li key={`${alert.source}-${alert.label}-${index}`}>
            <span className="font-medium">{alert.label}:</span> {alert.message}
          </li>
        ))}
      </ul>
    </div>
  );
}
