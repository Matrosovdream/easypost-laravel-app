<?php

namespace App\Http\Controllers\Api\Settings;

use App\Actions\Settings\ListAuditLogsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly ListAuditLogsAction $list) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('action'),
            (int) $request->query('per_page', 50),
        ));
    }
}
