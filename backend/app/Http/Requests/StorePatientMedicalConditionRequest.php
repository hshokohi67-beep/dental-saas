<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientMedicalConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::PATIENTS_MEDICAL_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'medical_condition_id' => ['required', 'string', Rule::exists('medical_conditions', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
