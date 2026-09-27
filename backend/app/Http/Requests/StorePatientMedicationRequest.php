<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientMedicationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'frequency' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date'],
        ];
    }
}
