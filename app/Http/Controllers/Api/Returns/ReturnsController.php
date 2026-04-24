<?php

namespace App\Http\Controllers\Api\Returns;

use App\Actions\Returns\ApproveReturnAction;
use App\Actions\Returns\CreateReturnRequestAction;
use App\Actions\Returns\DeclineReturnAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Returns\CreateReturnRequest;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Repositories\Care\ReturnRequestRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    public function __construct(
        private readonly CreateReturnRequestAction $create,
        private readonly ApproveReturnAction $approve,
        private readonly DeclineReturnAction $decline,
        private readonly ReturnRequestRepo $returns,
        private readonly UserRepo $users,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReturnRequest::class);

        /** @var User $user */
        $user = $request->user();

        // Client-scoped narrowing: restrict to their own client_id via team_user pivot.
        $clientScope = null;
        if (in_array('client', $user->roles->pluck('slug')->all(), true)
            && ! in_array('returns.view.any', $user->rights(), true)
        ) {
            $membership = $this->users->teamMembershipForUser($user->id, (int) $user->current_team_id);
            $clientScope = $membership?->client_id ?? 0; // 0 → no match
        }

        $page = $this->returns->paginateForTeam(
            teamId: (int) $user->current_team_id,
            clientScope: $clientScope,
            status: $request->query('status'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (ReturnRequest $r) => $this->mapList($r))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $return = $this->returns->findWithDetails($id);
        abort_if(! $return, 404);
        $this->authorize('view', $return);

        return response()->json($this->mapDetail($return));
    }

    public function store(CreateReturnRequest $request): JsonResponse
    {
        $return = $this->create->execute($request->user(), $request->validated());
        return response()->json(['id' => $return->id, 'status' => $return->status], 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $return = $this->returns->findWithDetails($id);
        abort_if(! $return, 404);
        abort_unless($request->user()->can('approve', ReturnRequest::class), 403);

        try {
            $return = $this->approve->execute($request->user(), $return);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'id' => $return->id,
            'status' => $return->status,
            'return_shipment_id' => $return->return_shipment_id,
        ]);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $return = $this->returns->findWithDetails($id);
        abort_if(! $return, 404);
        abort_unless($request->user()->can('approve', ReturnRequest::class), 403);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        try {
            $return = $this->decline->execute($request->user(), $return, $request->input('reason'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['id' => $return->id, 'status' => $return->status]);
    }

    private function mapList(ReturnRequest $r): array
    {
        return [
            'id' => $r->id,
            'status' => $r->status,
            'reason' => $r->reason,
            'original_shipment_id' => $r->original_shipment_id,
            'return_shipment_id' => $r->return_shipment_id,
            'auto_refund' => $r->auto_refund,
            'created_by' => $r->creator ? ['id' => $r->creator->id, 'name' => $r->creator->name] : null,
            'created_at' => $r->created_at?->toIso8601String(),
        ];
    }

    private function mapDetail(ReturnRequest $r): array
    {
        return array_merge($this->mapList($r), [
            'items' => $r->items,
            'notes' => $r->notes,
            'approved_by' => $r->approver ? ['id' => $r->approver->id, 'name' => $r->approver->name] : null,
            'approved_at' => $r->approved_at?->toIso8601String(),
            'original_shipment' => $r->originalShipment ? [
                'id' => $r->originalShipment->id,
                'reference' => $r->originalShipment->reference,
                'tracking_code' => $r->originalShipment->tracking_code,
            ] : null,
            'return_shipment' => $r->returnShipment ? [
                'id' => $r->returnShipment->id,
                'reference' => $r->returnShipment->reference,
                'status' => $r->returnShipment->status,
                'tracking_code' => $r->returnShipment->tracking_code,
            ] : null,
        ]);
    }
}
