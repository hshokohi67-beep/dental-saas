<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\Patient;
use App\Http\Resources\PatientTimelineEventResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientTimelineController extends Controller
{
    public function index(Patient $patient): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::PATIENTS_VIEW);

        return PatientTimelineEventResource::collection(
            $patient->timelineEvents()->with('recordedBy')->get()
        );
    }
}
