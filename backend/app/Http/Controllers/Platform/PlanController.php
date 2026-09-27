<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(Plan::query()->where('is_active', true)->get());
    }
}
