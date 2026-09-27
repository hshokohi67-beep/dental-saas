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

/**
 * A searchable picker instead of a plain long <select> — with 46+ catalog
 * codes, scrolling through a native dropdown to find one is slow; typing a
 * few letters narrows it to one or two matches immediately.
 */
export function ConditionPicker({ conditions, value, onChange, placeholder, className }: ConditionPickerProps) {
  const [query, setQuery] = useState("");
  const [isOpen, setIsOpen] = useState(false);

  const selected = conditions.find((condition) => condition.id === value) ?? null;

  const matches = useMemo(() => {
    const term = query.trim();
    if (term === "") return conditions;
    return conditions.filter((condition) => condition.label.includes(term));
  }, [conditions, query]);

  function select(condition: DentalCondition) {
    onChange(condition.id);
    setQuery(condition.label);
    setIsOpen(false);
  }

  function clear() {
    onChange("");
    setQuery("");
  }

  return (
    <div className={`relative ${className ?? ""}`}>
      <div className="relative">
        <input
          value={isOpen ? query : (selected?.label ?? "")}
          onChange={(event) => {
            setQuery(event.target.value);
            if (selected) onChange("");
          }}
          onFocus={() => {
            setQuery(selected?.label ?? "");
            setIsOpen(true);
          }}
          onBlur={() => setIsOpen(false)}
          placeholder={placeholder ?? "جست‌وجوی خدمت..."}
          className="w-full rounded-lg border border-border px-3 py-2 text-sm"
        />
        {(selected || (isOpen && query)) && (
          <button
            type="button"
            onMouseDown={(event) => event.preventDefault()}
            onClick={clear}
            className="absolute inset-y-0 left-2 text-xs text-muted"
          >
            ×
          </button>
        )}
      </div>

      {isOpen && (
        <ul className="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-border bg-white text-sm shadow-md">
          {matches.length === 0 && <li className="px-3 py-2 text-muted">موردی یافت نشد.</li>}
          {matches.map((condition) => (
            <li key={condition.id}>
              <button
                type="button"
                onMouseDown={(event) => event.preventDefault()}
                onClick={() => select(condition)}
                className={`block w-full px-3 py-2 text-right hover:bg-black/5 ${
                  condition.id === value ? "bg-primary/10 font-medium" : ""
                }`}
              >
                {condition.label}
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
