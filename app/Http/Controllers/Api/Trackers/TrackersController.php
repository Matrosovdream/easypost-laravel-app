<?php

namespace App\Http\Controllers\Api\Trackers;

use App\Actions\Trackers\CreateStandaloneTrackerAction;
use App\Actions\Trackers\DeleteTrackerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Trackers\CreateTrackerRequest;
use App\Models\Tracker;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackersController extends Controller
{
    public function __construct(
        private readonly CreateStandaloneTrackerAction $create,
        private readonly DeleteTrackerAction $delete,
        private readonly TrackerRepo $trackers,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tracker::class);

        $page = $this->trackers->paginateForTeam(
            teamId: (int) $request->user()->current_team_id,
            status: $request->query('status'),
            carrier: $request->query('carrier'),
            perPage: (int) $request->query('per_page', 25),
        );

        return response()->json([
            'data' => collect($page->items())->map(fn (Tracker $t) => $this->map($t))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tracker = $this->trackers->findWithEvents($id);
        abort_if(! $tracker, 404);
        $this->authorize('view', $tracker);

        return response()->json(array_merge($this->map($tracker), [
            'events' => $tracker->events->map(fn ($e) => [
                'status' => $e->status,
                'status_detail' => $e->status_detail,
                'message' => $e->message,
                'source' => $e->source,
                'event_datetime' => $e->event_datetime?->toIso8601String(),
                'location' => $e->location,
            ])->values(),
        ]));
    }

    public function store(CreateTrackerRequest $request): JsonResponse
    {
        $tracker = $this->create->execute(
            $request->user(),
            $request->string('tracking_code')->toString(),
            $request->string('carrier')->toString(),
        );
        return response()->json($this->map($tracker), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tracker = $this->trackers->findWithEvents($id);
        abort_if(! $tracker, 404);
        $this->authorize('delete', $tracker);

        $this->delete->execute($tracker);
        return response()->json(['ok' => true]);
    }

    private function map(Tracker $t): array
    {
        return [
            'id' => $t->id,
            'tracking_code' => $t->tracking_code,
            'carrier' => $t->carrier,
            'status' => $t->status,
            'status_detail' => $t->status_detail,
            'est_delivery_date' => $t->est_delivery_date?->toIso8601String(),
            'last_event_at' => $t->last_event_at?->toIso8601String(),
            'public_url' => $t->public_url,
            'shipment_id' => $t->shipment_id,
            'created_at' => $t->created_at?->toIso8601String(),
        ];
    }
}
