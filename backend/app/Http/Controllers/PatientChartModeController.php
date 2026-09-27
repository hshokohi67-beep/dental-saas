<?php

namespace App\Http\Controllers;

use App\Application\Actions\SetPatientChartMode;
use App\Domain\Patients\Models\Patient;
use App\Http\Requests\UpdatePatientChartModeRequest;
use App\Http\Resources\PatientResource;

class PatientChartModeController extends Controller
{
    public function update(UpdatePatientChartModeRequest $request, Patient $patient, SetPatientChartMode $setPatientChartMode): PatientResource
    {
        $patient = $setPatientChartMode->execute($patient, $request->string('chart_mode')->toString());

        return new PatientResource($patient);
    }
}
