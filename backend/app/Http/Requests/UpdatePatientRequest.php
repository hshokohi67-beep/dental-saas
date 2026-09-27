<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::PATIENTS_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::id();

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'mobile' => ['sometimes', 'required', 'string', 'max:20'],
            'national_id' => ['nullable', 'digits:10'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'branch_id' => [
                'nullable', 'string',
                Rule::exists('branches', 'id')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
