"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/Button";
import { fetchCurrentUser, logout } from "@/services/auth";
import type { AuthenticatedUser } from "@/types/auth";

export default function HomePage() {
  const router = useRouter();
  const [user, setUser] = useState<AuthenticatedUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchCurrentUser()
      .then(setUser)
      .catch(() => router.replace("/login"))
      .finally(() => setIsLoading(false));
  }, [router]);

  if (isLoading) {
    return (
      <main className="flex flex-1 items-center justify-center">
        <p className="text-muted">در حال بارگذاری...</p>
      </main>
    );
  }

  if (!user) return null;

  return (
    <main className="flex flex-1 flex-col items-center justify-center gap-4 p-6">
      <h1 className="text-xl font-bold">خوش آمدید، {user.name}</h1>
      <p className="text-muted">
        {user.is_super_admin
          ? "مدیر پلتفرم"
          : user.tenant
            ? `تنانت: ${user.tenant.name}`
            : "بدون تنانت"}
      </p>
      {user.tenant && (
        <Link href="/patients" className="text-sm text-primary underline">
          مدیریت بیماران
        </Link>
      )}
      <Button
        variant="ghost"
        onClick={() => logout().then(() => router.replace("/login"))}
      >
        خروج
      </Button>
    </main>
  );
}
