<?php

namespace App\Http\Controllers\Api\Ops;

use App\Actions\Ops\ListPrintQueueAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintQueueController extends Controller
{
    public function __construct(private readonly ListPrintQueueAction $list) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->list->execute($request->user()));
    }
}
