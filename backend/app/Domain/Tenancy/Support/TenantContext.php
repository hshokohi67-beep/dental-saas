<?php

namespace App\Domain\Tenancy\Support;

use App\Domain\Tenancy\Models\Tenant;

/**
 * The single source of truth for "which tenant is this request operating as".
 * Never resolved from client input — only ever set by ResolveTenant middleware
 * (from the authenticated user) or explicitly by console/queue jobs.
 */
class TenantContext
{
    private static ?Tenant $tenant = null;

    private static bool $bypassed = false;

    public static function set(?Tenant $tenant): void
    {
        self::$tenant = $tenant;
    }

    public static function get(): ?Tenant
    {
        return self::$tenant;
    }

    public static function id(): ?string
    {
        return self::$tenant?->id;
    }

    public static function check(): bool
    {
        return self::$tenant !== null;
    }

    public static function isBypassed(): bool
    {
        return self::$bypassed;
    }

    /**
     * Run a callback with tenant scoping disabled, for the rare legitimate
     * cross-tenant query (platform admin, scheduled jobs). Always call with
     * a comment at the call site explaining why.
     */
    public static function bypass(callable $callback): mixed
    {
        $previous = self::$bypassed;
        self::$bypassed = true;

        try {
            return $callback();
        } finally {
            self::$bypassed = $previous;
        }
    }

    /**
     * Test/console helper to reset state between runs.
     */
    public static function reset(): void
    {
        self::$tenant = null;
        self::$bypassed = false;
    }
}
