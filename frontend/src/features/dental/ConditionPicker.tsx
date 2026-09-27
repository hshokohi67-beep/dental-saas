"use client";

import { useMemo, useState } from "react";
import type { DentalCondition } from "@/types/dental";

interface ConditionPickerProps {
  conditions: DentalCondition[];
  value: string;
  onChange: (id: string) => void;
  placeholder?: string;
  className?: string;
}

const CATEGORY_LABELS: Record<string, string> = {
  treatment: "درمان",
  diagnostic: "تشخیصی",
  radiograph: "رادیوگرافی",
  consultation: "مشاوره",
  other: "سایر",
};

const CATEGORY_ORDER = ["treatment", "diagnostic", "radiograph", "consultation", "other"];

/**
 * Every applicable service is already on screen as a one-click chip,
 * grouped by category — after picking a tooth/quadrant/arch, recording a
 * finding never means typing into a search box or scrolling a hidden
 * dropdown first. The search field just narrows the chips further when the
 * list is long.
 */
export function ConditionPicker({ conditions, value, onChange, placeholder, className }: ConditionPickerProps) {
  const [query, setQuery] = useState("");

  const groups = useMemo(() => {
    const term = query.trim();
    const filtered = term === "" ? conditions : conditions.filter((condition) => condition.label.includes(term));

    const byCategory = new Map<string, DentalCondition[]>();
    for (const condition of filtered) {
      const list = byCategory.get(condition.category) ?? [];
      list.push(condition);
      byCategory.set(condition.category, list);
    }

    return [...byCategory.entries()].sort(([a], [b]) => {
      const orderA = CATEGORY_ORDER.indexOf(a);
      const orderB = CATEGORY_ORDER.indexOf(b);
      return (orderA === -1 ? 99 : orderA) - (orderB === -1 ? 99 : orderB);
    });
  }, [conditions, query]);

  function select(condition: DentalCondition) {
    onChange(condition.id === value ? "" : condition.id);
  }

  return (
    <div className={className}>
      {conditions.length > 6 && (
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder={placeholder ?? "جست‌وجو (اختیاری)..."}
          className="mb-2 w-full rounded-lg border border-border px-3 py-2 text-sm"
        />
      )}

      <div className="max-h-48 space-y-2 overflow-y-auto">
        {groups.length === 0 && <p className="text-xs text-muted">موردی یافت نشد.</p>}
        {groups.map(([category, items]) => (
          <div key={category}>
            <p className="mb-1 text-xs font-medium text-muted">{CATEGORY_LABELS[category] ?? category}</p>
            <div className="flex flex-wrap gap-1">
              {items.map((condition) => (
                <button
                  key={condition.id}
                  type="button"
                  onClick={() => select(condition)}
                  className={`rounded-full border px-2.5 py-1 text-xs ${
                    condition.id === value
                      ? "border-primary bg-primary/10 font-medium text-primary"
                      : "border-border hover:bg-black/5"
                  }`}
                >
                  {condition.label}
                </button>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
