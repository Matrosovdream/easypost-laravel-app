<?php

namespace App\Actions\Analytics;

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ListAnalyticsOverviewAction
{
    public function execute(User $user): array
    {
        $teamId = (int) $user->current_team_id;
        return Cache::remember("analytics.overview.team.{$teamId}", 60, function () use ($teamId) {
            $byStatus = AnalyticsOverviewHelper::byStatus($teamId);
            $totals = AnalyticsOverviewHelper::totalsFromByStatus($byStatus);

            return [
                'total_shipments' => $totals['total_shipments'],
                'total_cost_cents' => $totals['total_cost_cents'],
                'by_status' => $byStatus,
                'by_carrier' => AnalyticsOverviewHelper::byCarrier($teamId),
                'daily_30d' => AnalyticsOverviewHelper::daily($teamId, 30),
            ];
        });
    }
}
