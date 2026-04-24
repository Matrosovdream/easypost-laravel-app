<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Actions\Analytics\ListAnalyticsCarriersAction;
use App\Actions\Analytics\ListAnalyticsOverviewAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly ListAnalyticsOverviewAction $overview,
        private readonly ListAnalyticsCarriersAction $carriers,
    ) {}

    public function overview(Request $request): JsonResponse
    {
        abort_unless(in_array('analytics.view', $request->user()->rights(), true), 403);
        return response()->json($this->overview->execute($request->user()));
    }

    public function carriers(Request $request): JsonResponse
    {
        abort_unless(in_array('analytics.view', $request->user()->rights(), true), 403);
        return response()->json($this->carriers->execute($request->user()));
    }
}
