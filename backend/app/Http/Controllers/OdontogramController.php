<?php

namespace App\Http\Controllers;

use App\Domain\Dental\Support\DentalChartAccessPolicy;
use App\Domain\Dental\Support\OdontogramBuilder;
use App\Domain\Patients\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OdontogramController extends Controller
{
    public function show(Request $request, Patient $patient): JsonResponse
    {
        abort_unless(DentalChartAccessPolicy::canView($request->user(), $patient), 403);

        return response()->json(['data' => OdontogramBuilder::build($patient)]);
    }
}
