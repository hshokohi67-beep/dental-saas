"use client";

import { useState } from "react";

const SURFACES: { code: string; name: string; where: string }[] = [
  { code: "M", name: "مزیال (Mesial)", where: "سطحی که رو به خط وسط قوس دندانی است (نزدیک‌تر به دندان جلو)" },
  { code: "D", name: "دیستال (Distal)", where: "سطحی که رو به عقب دهان است (دورتر از خط وسط، سمت دندان عقل)" },
  { code: "O", name: "اکلوزال (Occlusal)", where: "سطح جونده — فقط در دندان‌های آسیا و پرمولار (پشتی)" },
  { code: "I", name: "اینسایزال (Incisal)", where: "لبه‌ی برشی — همون نقش اکلوزال ولی در دندان‌های جلو (پیشین)" },
  { code: "B", name: "باکال/لبیال (Buccal/Facial)", where: "سطح رو به لپ/لب — یعنی سطح بیرونی، رو به بیرون دهان" },
  { code: "L", name: "لینگوال/پالاتال (Lingual/Palatal)", where: "سطح رو به زبان/کام — یعنی سطح داخلی، رو به داخل دهان" },
];

/**
 * A quick reference for how a doctor's shorthand (e.g. "یه MOD کامپوزیت
 * بزن") maps to the surface picker on the tooth panel — mesial/distal
 * flip sides between the chart's screen-left and screen-right halves
 * (mirrored convention), so this spells that out instead of assuming it's
 * obvious.
 */
export function SurfaceLegend() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <div className="rounded-xl border border-border p-3 text-sm">
      <button type="button" onClick={() => setIsOpen((current) => !current)} className="flex w-full items-center justify-between">
        <span className="font-bold">راهنمای نواحی سطح دندان (مثلاً یعنی چی وقتی می‌گن «MOD بزن»)</span>
        <span className="text-muted">{isOpen ? "بستن ▲" : "نمایش ▼"}</span>
      </button>

      {isOpen && (
        <div className="mt-3 space-y-3">
          <ul className="grid grid-cols-1 gap-2 sm:grid-cols-2">
            {SURFACES.map((surface) => (
              <li key={surface.code} className="flex gap-2">
                <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-border text-xs font-bold">
                  {surface.code}
                </span>
                <span>
                  <span className="font-medium">{surface.name}</span>
                  <span className="block text-xs text-muted">{surface.where}</span>
                </span>
              </li>
            ))}
          </ul>

          <div className="rounded-lg bg-muted/10 p-2 text-xs text-muted">
            <p>
              روی چارت، هر دندان از دید پزشکِ روبه‌روی بیمار رسم شده (دید آینه‌ای): در دندان‌های نیمه‌ی <b>چپِ صفحه</b>،
              مزیال سمت راستِ تصویر دندان و دیستال سمت چپ آن است؛ در نیمه‌ی <b>راستِ صفحه</b> برعکس — مزیال سمت چپ و
              دیستال سمت راستِ تصویر است. باکال/لبیال همیشه ناحیه‌ی نزدیک‌تر به لپ یا لب (بیرونی) و لینگوال/پالاتال
              نزدیک‌تر به زبان یا کام (داخلی) است.
            </p>
            <p className="mt-1">
              اختصارهای رایج: <b>MO</b> = مزیال+اکلوزال، <b>DO</b> = دیستال+اکلوزال، <b>MOD</b> = مزیال+اکلوزال+دیستال
              (هر سه سطح با هم).
            </p>
          </div>
        </div>
      )}
    </div>
  );
}
