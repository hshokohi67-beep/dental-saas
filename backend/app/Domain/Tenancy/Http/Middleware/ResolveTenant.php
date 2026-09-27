<?php

namespace App\Domain\Tenancy\Http\Middleware;

use App\Domain\Tenancy\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the acting tenant from the authenticated user — never from a
 * client-supplied header, query param, or route segment. Must run after
 * auth:sanctum. Super-admin users (tenant_id null) are rejected here; they
 * use the separate `platform` route group instead.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id) {
            abort(403, 'این درخواست نیازمند عضویت در یک تنانت است.');
        }

        TenantContext::set($user->tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        return $next($request);
    }
}
