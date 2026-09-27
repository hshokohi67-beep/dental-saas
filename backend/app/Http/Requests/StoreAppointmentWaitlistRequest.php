<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentWaitlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::APPOINTMENTS_WAITLIST_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'string', Rule::exists('patients', 'id')],
            'branch_id' => ['required', 'string', Rule::exists('branches', 'id')],
            'staff_id' => ['nullable', 'string', Rule::exists('staff', 'id')],
            'dental_condition_catalog_id' => ['required', 'string', Rule::exists('dental_condition_catalog', 'id')],
            'preferred_from' => ['nullable', 'date'],
            'preferred_until' => ['nullable', 'date', 'after_or_equal:preferred_from'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
