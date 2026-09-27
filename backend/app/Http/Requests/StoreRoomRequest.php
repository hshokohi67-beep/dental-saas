<?php

namespace App\Http\Requests;

use App\Domain\Identity\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionCatalog::SCHEDULING_ROOMS_MANAGE);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'string', Rule::exists('branches', 'id')],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
