<?php

namespace App\Http\Controllers\Api\Pickups;

use App\Actions\Pickups\BuyPickupAction;
use App\Actions\Pickups\CancelPickupAction;
use App\Actions\Pickups\SchedulePickupAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Pickups\SchedulePickupRequest;
use App\Models\Pickup;
use App\Repositories\Operations\PickupRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PickupsController extends Controller
{
    public function __construct(
        private readonly SchedulePickupAction $schedule,
        private readonly BuyPickupAction $buy,
        private readonly CancelPickupAction $cancel,
        private readonly PickupRepo $pickups,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Pickup::class);

        $page = $this->pickups->paginateForTeam(
            teamId: (int) $user->current_team_id,
            status: $request->query('status'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Pickup $p) => [
                'id' => $p->id,
                'reference' => $p->reference,
                'status' => $p->status,
                'carrier' => $p->carrier,
                'service' => $p->service,
                'confirmation' => $p->confirmation,
                'min_datetime' => $p->min_datetime?->toIso8601String(),
                'max_datetime' => $p->max_datetime?->toIso8601String(),
                'cost_cents' => $p->cost_cents,
                'address' => $p->address ? [
                    'name' => $p->address->name,
                    'city' => $p->address->city,
                    'state' => $p->address->state,
                ] : null,
            ])->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        $this->authorize('view', $pickup);

        return response()->json([
            'id' => $pickup->id,
            'reference' => $pickup->reference,
            'status' => $pickup->status,
            'carrier' => $pickup->carrier,
            'service' => $pickup->service,
            'confirmation' => $pickup->confirmation,
            'min_datetime' => $pickup->min_datetime?->toIso8601String(),
            'max_datetime' => $pickup->max_datetime?->toIso8601String(),
            'cost_cents' => $pickup->cost_cents,
            'instructions' => $pickup->instructions,
            'rates' => $pickup->rates_snapshot,
            'address' => $pickup->address,
        ]);
    }

    public function store(SchedulePickupRequest $request): JsonResponse
    {
        $pickup = $this->schedule->execute($request->user(), $request->validated());
        return response()->json(['id' => $pickup->id, 'status' => $pickup->status, 'rates' => $pickup->rates_snapshot], 201);
    }

    public function buy(Request $request, int $id): JsonResponse
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        $this->authorize('view', $pickup);

        $request->validate([
            'carrier' => ['required', 'string', 'max:48'],
            'service' => ['required', 'string', 'max:48'],
        ]);

        $pickup = $this->buy->execute(
            $request->user(),
            $pickup,
            $request->string('carrier')->toString(),
            $request->string('service')->toString(),
        );

        return response()->json(['id' => $pickup->id, 'status' => $pickup->status, 'confirmation' => $pickup->confirmation]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $pickup = $this->pickups->findWithAddress($id);
        abort_if(! $pickup, 404);
        $this->authorize('cancel', $pickup);

        $pickup = $this->cancel->execute($request->user(), $pickup);
        return response()->json(['id' => $pickup->id, 'status' => $pickup->status]);
    }
}
