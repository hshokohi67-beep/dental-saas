<?php

namespace App\Http\Resources;

use App\Domain\Identity\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Staff */
class StaffResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'roles' => $this->user->getRoleNames(),
            ],
        ];
    }
}
