<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Tenancy\Models\Branch;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::BRANCHES_VIEW);

        return BranchResource::collection(Branch::query()->get());
    }

    public function store(StoreBranchRequest $request): BranchResource
    {
        $branch = Branch::query()->create($request->validated());

        return new BranchResource($branch);
    }
}
