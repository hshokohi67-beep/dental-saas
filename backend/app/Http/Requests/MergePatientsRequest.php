<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergePatientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::PATIENTS_MERGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::id();

        return [
            'survivor_patient_id' => [
                'required', 'string', 'different:duplicate_patient_id',
                Rule::exists('patients', 'id')->where('tenant_id', $tenantId),
            ],
            'duplicate_patient_id' => [
                'required', 'string',
                Rule::exists('patients', 'id')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
