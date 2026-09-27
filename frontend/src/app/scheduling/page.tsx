"use client";

import { useEffect, useState } from "react";
import { BookingPanel } from "@/features/scheduling/BookingPanel";
import { DayQueue } from "@/features/scheduling/DayQueue";
import { ShiftManager } from "@/features/scheduling/ShiftManager";
import { listBranches } from "@/services/branches";
import { listStaff } from "@/services/staff";
import type { Branch } from "@/types/branch";
import type { Staff } from "@/types/staff";

export default function SchedulingPage() {
  const [branches, setBranches] = useState<Branch[]>([]);
  const [staff, setStaff] = useState<Staff[]>([]);
  const [staffId, setStaffId] = useState("");
  const [date, setDate] = useState(() => new Date().toISOString().slice(0, 10));
  const [refreshToken, setRefreshToken] = useState(0);

  useEffect(() => {
    listBranches().then((response) => setBranches(response.data));
    listStaff().then((response) => {
      const doctors = response.data.filter((member) => member.user.roles.includes("Doctor"));
      setStaff(doctors);
      if (doctors.length > 0) setStaffId(doctors[0].id);
    });
  }, []);

  const selectedStaff = staff.find((member) => member.id === staffId);
  const branchId = selectedStaff?.branch_id ?? branches[0]?.id ?? "";

  return (
    <main className="mx-auto w-full max-w-3xl flex-1 space-y-6 p-6">
      <h1 className="text-lg font-bold">نوبت‌دهی</h1>

      <div className="flex flex-wrap gap-2">
        <select
          value={staffId}
          onChange={(event) => setStaffId(event.target.value)}
          className="rounded-lg border border-border px-3 py-2 text-sm"
        >
          {staff.length === 0 && <option value="">پزشکی ثبت نشده</option>}
          {staff.map((member) => (
            <option key={member.id} value={member.id}>
              {member.user.name}
            </option>
          ))}
        </select>
        <input
          type="date"
          value={date}
          onChange={(event) => setDate(event.target.value)}
          className="rounded-lg border border-border px-3 py-2 text-sm"
        />
      </div>

      {!staffId ? (
        <p className="text-muted">اول یک پزشک با نقش «Doctor» ثبت کنید.</p>
      ) : (
        <>
          <ShiftManager staffId={staffId} branchId={branchId} />
          <BookingPanel staffId={staffId} onBooked={() => setRefreshToken((current) => current + 1)} />
          <DayQueue staffId={staffId} date={date} refreshToken={refreshToken} />
        </>
      )}
    </main>
  );
}
