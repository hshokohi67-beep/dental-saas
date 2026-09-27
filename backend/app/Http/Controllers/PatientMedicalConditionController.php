<?php

namespace App\Http\Controllers;

use App\Application\Actions\AddPatientMedicalCondition;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\MedicalCondition;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientMedicalCondition;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use App\Http\Requests\StorePatientMedicalConditionRequest;
use App\Http\Resources\PatientMedicalConditionResource;
use Illuminate\Http\Response;

class PatientMedicalConditionController extends Controller
{
    public function store(StorePatientMedicalConditionRequest $request, Patient $patient, AddPatientMedicalCondition $addPatientMedicalCondition): PatientMedicalConditionResource
    {
        $condition = MedicalCondition::query()->findOrFail($request->input('medical_condition_id'));

        $link = $addPatientMedicalCondition->execute($patient, $condition, $request->input('notes'));

        return new PatientMedicalConditionResource($link->load('condition'));
    }

    public function destroy(Patient $patient, PatientMedicalCondition $link): Response
    {
        $this->authorize(PermissionCatalog::PATIENTS_MEDICAL_MANAGE);
        abort_unless($link->patient_id === $patient->id, 404);

        $link->loadMissing('condition');
        $label = $link->condition?->label ?? $link->medical_condition_id;
        $link->delete();

        PatientTimelineRecorder::record(
            $patient,
            PatientTimelineEvent::TYPE_MEDICAL_CONDITION_REMOVED,
            "بیماری زمینه‌ای «{$label}» از پرونده حذف شد.",
        );

        return response()->noContent();
    }
}
