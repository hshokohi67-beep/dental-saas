<?php

namespace App\Http\Resources;

use App\Domain\Dental\Models\DentalConditionCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DentalConditionCatalog */
class DentalConditionCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'category' => $this->category,
            'scope' => $this->scope,
            'dentitions' => $this->dentitions,
        ];
    }
}
