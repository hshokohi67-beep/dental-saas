<?php

namespace App\Http\Controllers;

use App\Application\Actions\AssignPrimaryDoctor;
use App\Domain\Identity\Models\Staff;
use App\Domain\Patients\Models\Patient;
use App\Http\Requests\AssignPrimaryDoctorRequest;
use App\Http\Resources\PatientResource;

class PatientPrimaryDoctorController extends Controller
{
    public function update(AssignPrimaryDoctorRequest $request, Patient $patient, AssignPrimaryDoctor $assignPrimaryDoctor): PatientResource
    {
        $doctor = $request->filled('staff_id') ? Staff::query()->findOrFail($request->input('staff_id')) : null;

        $patient = $assignPrimaryDoctor->execute($patient, $doctor);

        return new PatientResource($patient);
    }
}
