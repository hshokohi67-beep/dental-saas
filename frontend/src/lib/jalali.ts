import DateObject from "react-date-object";
import gregorian from "react-date-object/calendars/gregorian";
import persian from "react-date-object/calendars/persian";
import persian_fa from "react-date-object/locales/persian_fa";

/** Formats a Gregorian ISO date (`YYYY-MM-DD`, optionally with a time part) as a Jalali display string. */
export function formatJalaliDate(isoDate: string | null | undefined, format = "YYYY/MM/DD"): string {
  if (!isoDate) return "—";

  return new DateObject({ date: isoDate.slice(0, 10), calendar: gregorian, format: "YYYY-MM-DD" })
    .convert(persian, persian_fa)
    .format(format);
}
