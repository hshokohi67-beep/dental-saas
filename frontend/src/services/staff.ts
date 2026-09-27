import { apiFetch } from "@/lib/api";
import type { Staff } from "@/types/staff";

export function listStaff(): Promise<{ data: Staff[] }> {
  return apiFetch("/api/staff");
}
