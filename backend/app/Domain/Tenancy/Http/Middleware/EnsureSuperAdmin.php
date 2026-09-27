<?php

namespace App\Domain\Tenancy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_super_admin) {
            abort(403, 'دسترسی محدود به مدیر پلتفرم است.');
        }

        return $next($request);
    }
}
