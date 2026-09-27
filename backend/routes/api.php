<?php

use App\Http\Controllers\AppointmentAvailabilityController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\AppointmentWaitlistController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DentalConditionCatalogController;
use App\Http\Controllers\MedicalConditionController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientAllergyController;
use App\Http\Controllers\PatientChartModeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientMedicalConditionController;
use App\Http\Controllers\PatientMedicationController;
use App\Http\Controllers\PatientMergeController;
use App\Http\Controllers\PatientPrimaryDoctorController;
use App\Http\Controllers\PatientTimelineController;
use App\Http\Controllers\PatientToothConditionController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffLeaveController;
use App\Http\Controllers\StaffShiftController;
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

        Route::get('/medical-conditions', [MedicalConditionController::class, 'index']);

        Route::get('/patients/duplicates', [PatientController::class, 'duplicates']);
        Route::post('/patients/merge', [PatientMergeController::class, 'store']);
        Route::get('/patients', [PatientController::class, 'index']);
        Route::post('/patients', [PatientController::class, 'store']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
        Route::patch('/patients/{patient}', [PatientController::class, 'update']);

        Route::get('/patients/{patient}/timeline', [PatientTimelineController::class, 'index']);

        Route::post('/patients/{patient}/medical-conditions', [PatientMedicalConditionController::class, 'store']);
        Route::delete('/patients/{patient}/medical-conditions/{link}', [PatientMedicalConditionController::class, 'destroy']);

        Route::post('/patients/{patient}/allergies', [PatientAllergyController::class, 'store']);
        Route::delete('/patients/{patient}/allergies/{allergy}', [PatientAllergyController::class, 'destroy']);

        Route::post('/patients/{patient}/medications', [PatientMedicationController::class, 'store']);
        Route::patch('/patients/{patient}/medications/{medication}', [PatientMedicationController::class, 'update']);

        Route::get('/dental-conditions', [DentalConditionCatalogController::class, 'index']);

        Route::get('/patients/{patient}/odontogram', [OdontogramController::class, 'show']);
        Route::patch('/patients/{patient}/chart-mode', [PatientChartModeController::class, 'update']);
        Route::patch('/patients/{patient}/primary-doctor', [PatientPrimaryDoctorController::class, 'update']);

        Route::post('/patients/{patient}/tooth-conditions', [PatientToothConditionController::class, 'store']);
        Route::delete('/patients/{patient}/tooth-conditions/{toothCondition}', [PatientToothConditionController::class, 'destroy']);

        Route::get('/rooms', [RoomController::class, 'index']);
        Route::post('/rooms', [RoomController::class, 'store']);

        Route::get('/staff-shifts', [StaffShiftController::class, 'index']);
        Route::post('/staff-shifts', [StaffShiftController::class, 'store']);
        Route::delete('/staff-shifts/{shift}', [StaffShiftController::class, 'destroy']);

        Route::get('/staff-leaves', [StaffLeaveController::class, 'index']);
        Route::post('/staff-leaves', [StaffLeaveController::class, 'store']);
        Route::delete('/staff-leaves/{leave}', [StaffLeaveController::class, 'destroy']);

        Route::get('/appointments/availability', [AppointmentAvailabilityController::class, 'index']);
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::patch('/appointments/{appointment}/check-in', [AppointmentStatusController::class, 'checkIn']);
        Route::patch('/appointments/{appointment}/complete', [AppointmentStatusController::class, 'complete']);
        Route::patch('/appointments/{appointment}/cancel', [AppointmentStatusController::class, 'cancel']);
        Route::patch('/appointments/{appointment}/no-show', [AppointmentStatusController::class, 'noShow']);

        Route::get('/appointment-waitlist', [AppointmentWaitlistController::class, 'index']);
        Route::post('/appointment-waitlist', [AppointmentWaitlistController::class, 'store']);
    });
});
