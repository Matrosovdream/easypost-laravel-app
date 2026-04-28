<?php

namespace App\Actions\Analytics;

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ListAnalyticsOverviewAction
{
    public function __construct(
        private readonly AnalyticsOverviewHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        $teamId = (int) $user->current_team_id;
        return Cache::remember("analytics.overview.team.{$teamId}", 60, function () use ($teamId) {
            $byStatus = $this->helper->byStatus($teamId);
            $totals = $this->helper->totalsFromByStatus($byStatus);

            return [
                'total_shipments' => $totals['total_shipments'],
                'total_cost_cents' => $totals['total_cost_cents'],
                'by_status' => $byStatus,
                'by_carrier' => $this->helper->byCarrier($teamId),
                'daily_30d' => $this->helper->daily($teamId, 30),
            ];
        });
    }
}
