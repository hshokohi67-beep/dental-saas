<?php

namespace App\Http\Controllers;

use App\Domain\Identity\Support\PermissionCatalog;
use App\Domain\Operations\Models\Room;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize(PermissionCatalog::SCHEDULING_SHIFTS_VIEW);

        $rooms = Room::query()
            ->when($request->query('branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request): RoomResource
    {
        $room = Room::query()->create($request->validated());

        return new RoomResource($room);
    }
}
