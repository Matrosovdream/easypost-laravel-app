<?php

namespace App\Http\Controllers\Rest\Webhooks;

use App\Actions\Webhooks\HandleStripeWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin handler that trusts Cashier's signature verification (run earlier in the
 * middleware stack). Persistence + plan-transition logic lives in HandleStripeWebhookAction.
 */
class StripeWebhookController extends Controller
{
    public function __construct(private readonly HandleStripeWebhookAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->action->execute($request));
    }
}
