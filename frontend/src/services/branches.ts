import { apiFetch } from "@/lib/api";
import type { Branch } from "@/types/branch";

export function listBranches(): Promise<{ data: Branch[] }> {
  return apiFetch("/api/branches");
}
