import { apiFetch } from "@/lib/api";
import type { AuthenticatedUser } from "@/types/auth";

export function login(email: string, password: string): Promise<{ user: AuthenticatedUser }> {
  return apiFetch("/api/login", {
    method: "POST",
    body: JSON.stringify({ email, password }),
  });
}

export function logout(): Promise<void> {
  return apiFetch("/api/logout", { method: "POST" });
}

export function fetchCurrentUser(): Promise<AuthenticatedUser> {
  return apiFetch("/api/user");
}
