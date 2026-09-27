import { API_URL } from "./env";

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public errors?: Record<string, string[]>,
  ) {
    super(message);
  }
}

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : null;
}

async function ensureCsrfCookie(): Promise<void> {
  if (readCookie("XSRF-TOKEN")) return;
  await fetch(`${API_URL}/sanctum/csrf-cookie`, { credentials: "include" });
}

export async function apiFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  const method = (init.method ?? "GET").toUpperCase();

  if (method !== "GET" && method !== "HEAD") {
    await ensureCsrfCookie();
  }

  const headers = new Headers(init.headers);
  headers.set("Accept", "application/json");
  if (init.body) headers.set("Content-Type", "application/json");

  const xsrfToken = readCookie("XSRF-TOKEN");
  if (xsrfToken) headers.set("X-XSRF-TOKEN", xsrfToken);

  const response = await fetch(`${API_URL}${path}`, {
    ...init,
    method,
    headers,
    credentials: "include",
  });

  if (response.status === 204) return undefined as T;

  const data = await response.json().catch(() => null);

  if (!response.ok) {
    throw new ApiError(data?.message ?? "خطای غیرمنتظره رخ داد.", response.status, data?.errors);
  }

  return data as T;
}
