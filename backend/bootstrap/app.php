<?php

use App\Domain\Tenancy\Http\Middleware\EnsureSuperAdmin;
use App\Domain\Tenancy\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'super_admin' => EnsureSuperAdmin::class,
        ]);

        // Route model binding (SubstituteBindings) must never resolve a
        // tenant-scoped model before ResolveTenant has set the acting tenant
        // — otherwise implicit bindings like {patient} run against a stale
        // or absent TenantContext instead of the current request's tenant.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: ResolveTenant::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
