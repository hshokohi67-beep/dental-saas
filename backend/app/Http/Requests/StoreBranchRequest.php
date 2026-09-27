<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::BRANCHES_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ];
    }
}
