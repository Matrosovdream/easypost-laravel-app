<?php

namespace App\Http\Controllers\Api\Demo;

use App\Events\FeaturesPageVisited;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class FeaturesVisitController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        event(new FeaturesPageVisited(
            visitorId:  (string) Str::uuid(),
            userAgent:  $request->userAgent(),
            occurredAt: now()->toIso8601String(),
        ));

        return response()->json(['ok' => true]);
    }
}
