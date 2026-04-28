<?php

namespace App\Http\Controllers\Api\Trackers;

use App\Actions\Trackers\CreateStandaloneTrackerAction;
use App\Actions\Trackers\DeleteTrackerAction;
use App\Actions\Trackers\ListTrackersAction;
use App\Actions\Trackers\ShowTrackerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Trackers\CreateTrackerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackersController extends Controller
{
    public function __construct(
        private readonly ListTrackersAction $list,
        private readonly ShowTrackerAction $show,
        private readonly CreateStandaloneTrackerAction $create,
        private readonly DeleteTrackerAction $delete,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('status'),
            $request->query('carrier'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(CreateTrackerRequest $request): JsonResponse
    {
        return response()->json($this->create->execute(
            $request->user(),
            $request->string('tracking_code')->toString(),
            $request->string('carrier')->toString(),
        ), 201);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json($this->delete->execute($id));
    }
}
