<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignPrimaryDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::DENTAL_CHART_ASSIGN_DOCTOR);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::id();

        return [
            'staff_id' => [
                'nullable', 'string',
                Rule::exists('staff', 'id')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
