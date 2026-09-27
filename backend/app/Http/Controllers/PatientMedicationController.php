<?php

namespace App\Http\Controllers;

use App\Application\Actions\AddPatientMedication;
use App\Application\Actions\UpdatePatientMedication;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientMedication;
use App\Http\Requests\StorePatientMedicationRequest;
use App\Http\Requests\UpdatePatientMedicationRequest;
use App\Http\Resources\PatientMedicationResource;

class PatientMedicationController extends Controller
{
    public function store(StorePatientMedicationRequest $request, Patient $patient, AddPatientMedication $addPatientMedication): PatientMedicationResource
    {
        $medication = $addPatientMedication->execute($patient, $request->validated());

        return new PatientMedicationResource($medication);
    }

    public function update(UpdatePatientMedicationRequest $request, Patient $patient, PatientMedication $medication, UpdatePatientMedication $updatePatientMedication): PatientMedicationResource
    {
        abort_unless($medication->patient_id === $patient->id, 404);

        $medication = $updatePatientMedication->execute($medication, $request->validated());

        return new PatientMedicationResource($medication);
    }
}
