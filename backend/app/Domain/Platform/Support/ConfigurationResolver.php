<?php

namespace App\Domain\Platform\Support;

use App\Domain\Platform\Models\ConfigurationValue;
use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Support\TenantContext;
use App\Models\User;

/**
 * Configuration Override Engine (master prompt §11): Global < Plan < Tenant <
 * Branch < User, most specific wins. Changing a setting never rewrites
 * historical clinical/financial records — those store their own values at
 * the time they were created, they don't read through this resolver later.
 */
class ConfigurationResolver
{
    public function resolve(string $key, ?User $user = null, ?Branch $branch = null, ?Tenant $tenant = null, mixed $default = null): mixed
    {
        if ($user) {
            $value = $this->lookup('user', (string) $user->id, $key);
            if ($value !== null) {
                return $value;
            }
        }

        if ($branch) {
            $value = $this->lookup('branch', $branch->id, $key);
            if ($value !== null) {
                return $value;
            }
        }

        if ($tenant === null && $branch !== null) {
            $tenant = $branch->tenant;
        }
        if ($tenant === null && $user !== null) {
            $tenant = $user->tenant;
        }

        if ($tenant) {
            $value = $this->lookup('tenant', $tenant->id, $key);
            if ($value !== null) {
                return $value;
            }

            $planId = TenantContext::bypass(fn () => $tenant->subscription?->plan_id);
            if ($planId) {
                $value = $this->lookup('plan', $planId, $key);
                if ($value !== null) {
                    return $value;
                }
            }
        }

        return $this->lookup('global', null, $key) ?? $default;
    }

    public function set(string $scopeType, ?string $scopeId, string $key, mixed $value): void
    {
        ConfigurationValue::query()->updateOrCreate(
            ['scope_type' => $scopeType, 'scope_id' => $scopeId, 'key' => $key],
            ['value' => $value],
        );
    }

    private function lookup(string $scopeType, ?string $scopeId, string $key): mixed
    {
        $row = ConfigurationValue::query()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->where('key', $key)
            ->first();

        return $row?->value;
    }
}
