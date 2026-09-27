<?php

namespace App\Http\Controllers;

use App\Application\Actions\RecordToothCondition;
use App\Application\Actions\VoidToothCondition;
use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Dental\Models\PatientToothCondition;
use App\Domain\Dental\Support\DentalChartAccessPolicy;
use App\Domain\Patients\Models\Patient;
use App\Http\Requests\StorePatientToothConditionRequest;
use App\Http\Resources\PatientToothConditionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientToothConditionController extends Controller
{
    public function store(StorePatientToothConditionRequest $request, Patient $patient, RecordToothCondition $recordToothCondition): PatientToothConditionResource
    {
        $condition = DentalConditionCatalog::query()->findOrFail($request->input('dental_condition_catalog_id'));

        $link = $recordToothCondition->execute($patient, $condition, $request->validated());

        return new PatientToothConditionResource($link);
    }

    public function destroy(Request $request, Patient $patient, PatientToothCondition $toothCondition, VoidToothCondition $voidToothCondition): Response
    {
        abort_unless(DentalChartAccessPolicy::canManage($request->user(), $patient), 403);
        abort_unless($toothCondition->patient_id === $patient->id, 404);

        $voidToothCondition->execute($toothCondition);

        return response()->noContent();
    }
}
