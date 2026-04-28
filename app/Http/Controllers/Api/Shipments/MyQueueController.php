<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\ListMyQueueAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyQueueController extends Controller
{
    public function __construct(private readonly ListMyQueueAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->action->execute(
            $request->user(),
            (int) $request->query('per_page', 25),
        ));
    }
}
