<?php

namespace App\Http\Controllers\Api\Billing;

use App\Actions\Billing\CreateCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Billing\CheckoutRequest;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly CreateCheckoutSessionAction $action) {}

    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        $result = $this->action->execute($request->user(), $request->validated()['plan']);

        return response()->json($result['body'], $result['_status']);
    }
}
