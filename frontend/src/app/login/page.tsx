"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/Button";
import { ApiError } from "@/lib/api";
import { login } from "@/services/auth";

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      await login(email, password);
      router.push("/");
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "خطا در ورود به سیستم.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex flex-1 items-center justify-center p-6">
      <form
        onSubmit={handleSubmit}
        className="w-full max-w-sm space-y-4 rounded-xl border border-border p-6"
      >
        <h1 className="text-lg font-bold">ورود به سیستم</h1>

        <div className="space-y-1">
          <label htmlFor="email" className="text-sm text-muted">
            ایمیل
          </label>
          <input
            id="email"
            type="email"
            required
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
        </div>

        <div className="space-y-1">
          <label htmlFor="password" className="text-sm text-muted">
            رمز عبور
          </label>
          <input
            id="password"
            type="password"
            required
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 text-sm"
          />
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}

        <Button type="submit" disabled={isSubmitting} className="w-full">
          {isSubmitting ? "در حال ورود..." : "ورود"}
        </Button>
      </form>
    </main>
  );
}
