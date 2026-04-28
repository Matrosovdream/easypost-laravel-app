<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\ApproveShipmentAction;
use App\Actions\Shipments\DeclineShipmentApprovalAction;
use App\Actions\Shipments\ListApprovalsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
    public function __construct(
        private readonly ListApprovalsAction $list,
        private readonly ApproveShipmentAction $approve,
        private readonly DeclineShipmentApprovalAction $decline,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('status', 'pending'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return response()->json($this->approve->execute($request->user(), $id));
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return response()->json($this->decline->execute(
            $request->user(),
            $id,
            $request->input('reason'),
        ));
    }
}
