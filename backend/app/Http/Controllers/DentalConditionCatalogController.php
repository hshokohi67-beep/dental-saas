<?php

namespace App\Http\Controllers;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Support\PermissionCatalog;
use App\Http\Resources\DentalConditionCatalogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The "Contextual Treatment Picker" (roadmap Phase 3): given what the user
 * selected on the odontogram (a tooth's dentition, or a quadrant/arch/whole
 * mouth scope), only the codes that are actually applicable are returned —
 * never the full, undifferentiated 46-code list.
 */
class DentalConditionCatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::DENTAL_CHART_VIEW);

        $conditions = DentalConditionCatalog::query()
            ->where('is_active', true)
            ->when($request->query('scope'), fn ($query, $scope) => $query->where('scope', $scope))
            ->when(
                $request->query('dentition'),
                fn ($query, $dentition) => $query->whereJsonContains('dentitions', $dentition)
            )
            ->orderBy('category')
            ->orderBy('label')
            ->get();

        return DentalConditionCatalogResource::collection($conditions);
    }
}
