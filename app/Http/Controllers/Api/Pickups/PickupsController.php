<?php

namespace App\Http\Controllers\Api\Pickups;

use App\Actions\Pickups\BuyPickupAction;
use App\Actions\Pickups\CancelPickupAction;
use App\Actions\Pickups\ListPickupsAction;
use App\Actions\Pickups\SchedulePickupAction;
use App\Actions\Pickups\ShowPickupAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Pickups\BuyPickupRequest;
use App\Http\Requests\Api\Pickups\SchedulePickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PickupsController extends Controller
{
    public function __construct(
        private readonly ListPickupsAction $list,
        private readonly ShowPickupAction $show,
        private readonly SchedulePickupAction $schedule,
        private readonly BuyPickupAction $buy,
        private readonly CancelPickupAction $cancel,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->list->execute(
            $request->user(),
            $request->query('status'),
            (int) $request->query('per_page', 25),
        ));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->show->execute($id));
    }

    public function store(SchedulePickupRequest $request): JsonResponse
    {
        return response()->json($this->schedule->execute($request->user(), $request->validated()), 201);
    }

    public function buy(BuyPickupRequest $request, int $id): JsonResponse
    {
        return response()->json($this->buy->execute(
            $request->user(),
            $id,
            $request->string('carrier')->toString(),
            $request->string('service')->toString(),
        ));
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return response()->json($this->cancel->execute($request->user(), $id));
    }
}
