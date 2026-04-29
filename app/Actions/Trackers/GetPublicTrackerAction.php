<?php

namespace App\Actions\Trackers;

use App\Http\Resources\Rest\PublicApi\PublicTrackerResource;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Http\JsonResponse;

class GetPublicTrackerAction
{
    public function __construct(
        private readonly TrackerRepo $trackers,
    ) {}

    public function execute(string $code): JsonResponse|PublicTrackerResource
    {
        $tracker = $this->trackers->getByTrackingCode($code);

        if (! $tracker) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new PublicTrackerResource($tracker);
    }
}
