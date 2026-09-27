<?php

namespace App\Http\Controllers;

use App\Application\Actions\MergePatients;
use App\Domain\Patients\Models\Patient;
use App\Http\Requests\MergePatientsRequest;
use App\Http\Resources\PatientDetailResource;

class PatientMergeController extends Controller
{
    public function store(MergePatientsRequest $request, MergePatients $mergePatients): PatientDetailResource
    {
        $survivor = Patient::query()->findOrFail($request->input('survivor_patient_id'));
        $duplicate = Patient::query()->findOrFail($request->input('duplicate_patient_id'));

        $merged = $mergePatients->execute($survivor, $duplicate);

        return new PatientDetailResource($merged->load(['branch', 'medicalConditions.condition', 'allergies', 'medications', 'timelineEvents']));
    }
}
