<?php

namespace App\Http\Controllers;

use App\Application\Actions\CreatePatient;
use App\Application\Actions\UpdatePatient;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Support\DuplicatePatientFinder;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientDetailResource;
use App\Http\Resources\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    private const DETAIL_RELATIONS = [
        'branch', 'medicalConditions.condition', 'allergies', 'medications', 'timelineEvents', 'primaryDoctor.user',
    ];

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::PATIENTS_VIEW);

        $search = trim((string) $request->query('q', ''));

        $patients = Patient::query()
            ->where('status', '!=', 'merged')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('mobile', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhereRaw("(first_name || ' ' || last_name) like ?", ["%{$search}%"]);
                });
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return PatientResource::collection($patients);
    }

    public function duplicates(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::PATIENTS_VIEW);

        $candidates = DuplicatePatientFinder::search([
            'mobile' => $request->query('mobile'),
            'national_id' => $request->query('national_id'),
            'first_name' => $request->query('first_name'),
            'last_name' => $request->query('last_name'),
            'date_of_birth' => $request->query('date_of_birth'),
        ], excludingPatientId: $request->query('excluding_patient_id'));

        return PatientResource::collection($candidates);
    }

    public function store(StorePatientRequest $request, CreatePatient $createPatient): PatientDetailResource
    {
        $patient = $createPatient->execute($request->validated());

        return new PatientDetailResource($patient->load(self::DETAIL_RELATIONS));
    }

    public function show(Patient $patient): PatientDetailResource
    {
        $this->authorize(PermissionCatalog::PATIENTS_VIEW);

        return new PatientDetailResource($patient->load(self::DETAIL_RELATIONS));
    }

    public function update(UpdatePatientRequest $request, Patient $patient, UpdatePatient $updatePatient): PatientDetailResource
    {
        $patient = $updatePatient->execute($patient, $request->validated());

        return new PatientDetailResource($patient->load(self::DETAIL_RELATIONS));
    }
}
