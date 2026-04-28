<?php

namespace App\Http\Controllers\Api\Billing;

use App\Actions\Billing\OpenBillingPortalAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(private readonly OpenBillingPortalAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->action->execute($request->user());

        return response()->json($result['body'], $result['_status']);
    }
}
