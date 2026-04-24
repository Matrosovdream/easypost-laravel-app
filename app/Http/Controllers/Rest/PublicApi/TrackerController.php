<?php

namespace App\Http\Controllers\Rest\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rest\PublicApi\PublicTrackerResource;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Http\JsonResponse;

class TrackerController extends Controller
{
    public function __construct(private readonly TrackerRepo $repo) {}

    public function __invoke(string $code): JsonResponse|PublicTrackerResource
    {
        $tracker = $this->repo->getByTrackingCode($code);

        if (! $tracker) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new PublicTrackerResource($tracker);
    }
}
