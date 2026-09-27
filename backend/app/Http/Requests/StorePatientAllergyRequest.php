<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientAllergyRequest extends FormRequest
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
            'allergen' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', Rule::in(['mild', 'moderate', 'severe'])],
            'reaction' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
