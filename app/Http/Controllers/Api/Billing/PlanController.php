<?php

namespace App\Http\Controllers\Api\Billing;

use App\Actions\Billing\ShowBillingPlanAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(private readonly ShowBillingPlanAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->action->execute($request->user()));
    }
}
