<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentListItemResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyQueueController extends Controller
{
    public function __construct(private readonly ShipmentRepo $shipments) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $this->shipments->paginateAssignedTo(
            userId: (int) $user->id,
            statuses: ['purchased', 'packed'],
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => ShipmentListItemResource::collection($page->items())->resolve(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
