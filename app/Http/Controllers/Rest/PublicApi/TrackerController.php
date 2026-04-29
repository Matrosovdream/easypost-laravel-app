<?php

namespace App\Http\Controllers\Rest\PublicApi;

use App\Actions\Trackers\GetPublicTrackerAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Rest\PublicApi\PublicTrackerResource;
use Illuminate\Http\JsonResponse;

class TrackerController extends Controller
{
    public function __construct(private readonly GetPublicTrackerAction $action) {}

    public function __invoke(string $code): JsonResponse|PublicTrackerResource
    {
        return $this->action->execute($code);
    }
}
