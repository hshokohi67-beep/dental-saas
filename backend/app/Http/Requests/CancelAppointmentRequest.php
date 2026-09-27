<?php

namespace App\Http\Requests;

use App\Domain\Operations\Support\AppointmentAccessPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return AppointmentAccessPolicy::canManage($this->user(), $this->route('appointment'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
