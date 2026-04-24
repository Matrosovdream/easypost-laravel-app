<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ShipmentListItemResource;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListShipmentsController extends Controller
{
    public function __construct(private readonly ShipmentRepo $shipments) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = $this->shipments->paginateScoped(
            filter: [
                'status' => $request->query('status'),
                'carrier' => $request->query('carrier'),
                'q' => $request->query('q'),
            ],
            with: ['toAddress', 'assignee', 'requester'],
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => ShipmentListItemResource::collection($page->items())->resolve(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
