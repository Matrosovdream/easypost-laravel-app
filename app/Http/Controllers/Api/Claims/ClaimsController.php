<?php

namespace App\Http\Controllers\Api\Claims;

use App\Actions\Claims\ApproveClaimAction;
use App\Actions\Claims\CloseClaimAction;
use App\Actions\Claims\MarkClaimPaidAction;
use App\Actions\Claims\OpenClaimAction;
use App\Actions\Claims\SubmitClaimAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Claims\OpenClaimRequest;
use App\Models\Claim;
use App\Repositories\Care\ClaimRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClaimsController extends Controller
{
    public function __construct(
        private readonly OpenClaimAction $open,
        private readonly SubmitClaimAction $submit,
        private readonly ApproveClaimAction $approve,
        private readonly MarkClaimPaidAction $pay,
        private readonly CloseClaimAction $close,
        private readonly ClaimRepo $claims,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Claim::class);
        $user = $request->user();

        $page = $this->claims->paginateForTeam(
            teamId: (int) $user->current_team_id,
            state: $request->query('state'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Claim $c) => $this->mapList($c))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $claim = $this->claims->findInTeam((int) $request->user()->current_team_id, $id);
        abort_if(! $claim, 404);
        $this->authorize('view', $claim);

        return response()->json(array_merge($this->mapList($claim), [
            'description' => $claim->description,
            'timeline' => $claim->timeline,
            'ep_claim_id' => $claim->ep_claim_id,
        ]));
    }

    public function store(OpenClaimRequest $request): JsonResponse
    {
        try {
            $claim = $this->open->execute($request->user(), $request->validated());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json(['id' => $claim->id, 'state' => $claim->state], 201);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $claim = $this->claims->findInTeam((int) $request->user()->current_team_id, $id);
        abort_if(! $claim, 404);
        $this->authorize('view', $claim);

        try {
            $claim = $this->submit->execute($request->user(), $claim);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json(['id' => $claim->id, 'state' => $claim->state]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $claim = $this->claims->findInTeam((int) $request->user()->current_team_id, $id);
        abort_if(! $claim, 404);
        abort_unless($request->user()->can('approve', $claim), 403);

        $request->validate(['recovered_cents' => ['nullable', 'integer', 'min:0']]);

        try {
            $claim = $this->approve->execute(
                $request->user(),
                $claim,
                $request->integer('recovered_cents') ?: null,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json(['id' => $claim->id, 'state' => $claim->state, 'recovered_cents' => $claim->recovered_cents]);
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $claim = $this->claims->findInTeam((int) $request->user()->current_team_id, $id);
        abort_if(! $claim, 404);
        abort_unless($request->user()->can('approve', $claim), 403);

        $request->validate(['recovered_cents' => ['nullable', 'integer', 'min:0']]);

        try {
            $claim = $this->pay->execute(
                $request->user(),
                $claim,
                $request->integer('recovered_cents') ?: null,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json(['id' => $claim->id, 'state' => $claim->state]);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $claim = $this->claims->findInTeam((int) $request->user()->current_team_id, $id);
        abort_if(! $claim, 404);
        $this->authorize('view', $claim);

        $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $claim = $this->close->execute($request->user(), $claim, $request->input('reason'));
        return response()->json(['id' => $claim->id, 'state' => $claim->state]);
    }

    private function mapList(Claim $c): array
    {
        return [
            'id' => $c->id,
            'state' => $c->state,
            'type' => $c->type,
            'amount_cents' => $c->amount_cents,
            'recovered_cents' => $c->recovered_cents,
            'shipment_id' => $c->shipment_id,
            'shipment' => $c->shipment ? [
                'id' => $c->shipment->id,
                'reference' => $c->shipment->reference,
                'tracking_code' => $c->shipment->tracking_code,
            ] : null,
            'assignee' => $c->assignee ? ['id' => $c->assignee->id, 'name' => $c->assignee->name] : null,
            'approver' => $c->approver ? ['id' => $c->approver->id, 'name' => $c->approver->name] : null,
            'paid_at' => $c->paid_at?->toIso8601String(),
            'closed_at' => $c->closed_at?->toIso8601String(),
            'created_at' => $c->created_at?->toIso8601String(),
        ];
    }
}
