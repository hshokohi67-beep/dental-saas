<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Patients\Models\MedicalCondition;
use App\Http\Resources\MedicalConditionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MedicalConditionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::PATIENTS_MEDICAL_VIEW);

        $conditions = MedicalCondition::query()->where('is_active', true)->orderBy('label')->get();

        return MedicalConditionResource::collection($conditions);
    }
}
