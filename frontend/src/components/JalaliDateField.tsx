"use client";

import DateObject from "react-date-object";
import gregorian from "react-date-object/calendars/gregorian";
import persian from "react-date-object/calendars/persian";
import persian_fa from "react-date-object/locales/persian_fa";
import DatePicker from "react-multi-date-picker";
import "react-multi-date-picker/styles/colors/teal.css";

const ISO_FORMAT = "YYYY-MM-DD";

interface JalaliDateFieldProps {
  /** Gregorian ISO date (`YYYY-MM-DD`), matching what the API stores and expects — same contract as `<input type="date">`. */
  value: string | null | undefined;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
}

/**
 * A date field that displays and is navigated in the Jalali (Shamsi) calendar
 * — required per the target architecture ("Persian-first, Jalali") — while
 * keeping the Gregorian ISO string contract the API and the rest of the app
 * use. Native `<input type="date">` can't do this: browsers always render it
 * in the Gregorian calendar regardless of locale.
 */
export function JalaliDateField({ value, onChange, placeholder, className = "" }: JalaliDateFieldProps) {
  const jalaliValue = value
    ? new DateObject({ date: value, calendar: gregorian, format: ISO_FORMAT }).convert(persian, persian_fa)
    : "";

  return (
    <DatePicker
      value={jalaliValue}
      onChange={(date) => onChange(date ? (date as DateObject).convert(gregorian).format(ISO_FORMAT) : "")}
      calendar={persian}
      locale={persian_fa}
      format="YYYY/MM/DD"
      inputClass={`w-full rounded-lg border border-border px-3 py-2 text-sm ${className}`}
      placeholder={placeholder}
      calendarPosition="bottom-right"
    />
  );
}
