<?php

namespace App\Http\Requests;

use App\Domain\Operations\Support\AppointmentAccessPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AppointmentAccessPolicy::canBook($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'string', Rule::exists('patients', 'id')],
            'staff_id' => ['required', 'string', Rule::exists('staff', 'id')],
            'dental_condition_catalog_id' => ['required', 'string', Rule::exists('dental_condition_catalog', 'id')],
            'scheduled_at' => ['required', 'date'],
            'room_id' => ['nullable', 'string', Rule::exists('rooms', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
