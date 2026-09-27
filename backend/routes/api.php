<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant');
    });

    // Platform (super-admin only): manages tenants across the whole platform.
    Route::middleware('super_admin')->prefix('platform')->group(function () {
        Route::get('/plans', [PlanController::class, 'index']);
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
    });

    // Tenant-scoped: everything a clinic's own staff can see/do.
    Route::middleware('tenant')->group(function () {
        Route::get('/branches', [BranchController::class, 'index']);
        Route::post('/branches', [BranchController::class, 'store']);

        Route::get('/staff', [StaffController::class, 'index']);
        Route::post('/staff', [StaffController::class, 'store']);

        Route::get('/roles', [RoleController::class, 'index']);
    });
});
