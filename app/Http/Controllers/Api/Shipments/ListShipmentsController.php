<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\ListShipmentsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListShipmentsController extends Controller
{
    public function __construct(private readonly ListShipmentsAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->action->execute(
            $request->query('status'),
            $request->query('carrier'),
            $request->query('q'),
            (int) $request->query('per_page', 25),
        ));
    }
}
