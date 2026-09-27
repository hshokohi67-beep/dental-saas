<?php

namespace App\Http\Controllers;

use App\Application\Actions\AddPatientAllergy;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientAllergy;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use App\Http\Requests\StorePatientAllergyRequest;
use App\Http\Resources\PatientAllergyResource;
use Illuminate\Http\Response;

class PatientAllergyController extends Controller
{
    public function store(StorePatientAllergyRequest $request, Patient $patient, AddPatientAllergy $addPatientAllergy): PatientAllergyResource
    {
        $allergy = $addPatientAllergy->execute($patient, $request->validated());

        return new PatientAllergyResource($allergy);
    }

    public function destroy(Patient $patient, PatientAllergy $allergy): Response
    {
        $this->authorize(PermissionCatalog::PATIENTS_MEDICAL_MANAGE);
        abort_unless($allergy->patient_id === $patient->id, 404);

        $allergen = $allergy->allergen;
        $allergy->delete();

        PatientTimelineRecorder::record(
            $patient,
            PatientTimelineEvent::TYPE_ALLERGY_REMOVED,
            "حساسیت دارویی «{$allergen}» از پرونده حذف شد.",
        );

        return response()->noContent();
    }
}
