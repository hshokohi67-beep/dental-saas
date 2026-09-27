<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::STAFF_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'branch_id' => [
                'nullable', 'string',
                Rule::exists('branches', 'id')->where('tenant_id', $tenantId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'role' => [
                'required', 'string',
                Rule::exists('roles', 'name')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
