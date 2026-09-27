<?php

namespace App\Http\Controllers;

use App\Application\Actions\TransitionAppointmentStatus;
use App\Domain\Operations\Models\Appointment;
use App\Domain\Operations\Support\AppointmentAccessPolicy;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;

class AppointmentStatusController extends Controller
{
    public function checkIn(Request $request, Appointment $appointment, TransitionAppointmentStatus $transition): AppointmentResource
    {
        abort_unless(AppointmentAccessPolicy::canManage($request->user(), $appointment), 403);

        return new AppointmentResource($transition->checkIn($appointment));
    }

    public function complete(Request $request, Appointment $appointment, TransitionAppointmentStatus $transition): AppointmentResource
    {
        abort_unless(AppointmentAccessPolicy::canManage($request->user(), $appointment), 403);

        return new AppointmentResource($transition->complete($appointment)->load('dentalConditionCatalog'));
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment, TransitionAppointmentStatus $transition): AppointmentResource
    {
        $appointment = $transition->cancel($appointment, $request->string('reason')->toString() ?: null);

        return new AppointmentResource($appointment->load('dentalConditionCatalog'));
    }

    public function noShow(Request $request, Appointment $appointment, TransitionAppointmentStatus $transition): AppointmentResource
    {
        abort_unless(AppointmentAccessPolicy::canManage($request->user(), $appointment), 403);

        return new AppointmentResource($transition->markNoShow($appointment)->load('dentalConditionCatalog'));
    }
}
