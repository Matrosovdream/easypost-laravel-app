<?php

namespace App\Http\Controllers\Rest\Webhooks;

use App\Actions\Webhooks\HandleEasyPostWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * EasyPost webhook intake. All processing — signature validation, event
 * persistence, and dispatch — lives in HandleEasyPostWebhookAction.
 */
class EasyPostWebhookController extends Controller
{
    public function __construct(private readonly HandleEasyPostWebhookAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->action->execute($request);

        return response()->json($result['body'], $result['_status']);
    }
}
