<?php

namespace App\Http\Controllers\Api\Shipments;

use App\Actions\Shipments\ApproveShipmentAction;
use App\Actions\Shipments\DeclineShipmentApprovalAction;
use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Shipment;
use App\Repositories\Shipping\ApprovalRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
    public function __construct(
        private readonly ApproveShipmentAction $approve,
        private readonly DeclineShipmentApprovalAction $decline,
        private readonly ApprovalRepo $approvals,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('approve', Shipment::class), 403);

        $page = $this->approvals->paginateForTeam(
            teamId: (int) $user->current_team_id,
            status: $request->query('status', 'pending'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Approval $a) => [
                'id' => $a->id,
                'status' => $a->status,
                'cost_cents' => $a->cost_cents,
                'reason' => $a->reason,
                'note' => $a->note,
                'rate_snapshot' => $a->rate_snapshot,
                'requested_by' => $a->requester ? ['id' => $a->requester->id, 'name' => $a->requester->name] : null,
                'approver' => $a->approver ? ['id' => $a->approver->id, 'name' => $a->approver->name] : null,
                'shipment_id' => $a->shipment_id,
                'shipment' => $a->shipment ? [
                    'id' => $a->shipment->id,
                    'reference' => $a->shipment->reference,
                    'status' => $a->shipment->status,
                    'to_address' => $a->shipment->toAddress ? [
                        'city' => $a->shipment->toAddress->city,
                        'state' => $a->shipment->toAddress->state,
                        'country' => $a->shipment->toAddress->country,
                    ] : null,
                ] : null,
                'created_at' => $a->created_at?->toIso8601String(),
                'resolved_at' => $a->resolved_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('approve', Shipment::class), 403);

        $approval = $this->approvals->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $approval, 404);

        $result = $this->approve->execute($user, $approval);

        return response()->json([
            'status' => 'approved',
            'buy_status' => $result['buy']['status'] ?? null,
            'shipment_id' => $result['shipment']->id,
        ]);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('approve', Shipment::class), 403);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $approval = $this->approvals->findInTeam((int) $user->current_team_id, $id);
        abort_if(! $approval, 404);

        $this->decline->execute($user, $approval, $request->input('reason'));

        return response()->json(['status' => 'declined']);
    }
}
