<?php

namespace App\Actions\Analytics;

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Models\User;

class ListAnalyticsCarriersAction
{
    public function execute(User $user): array
    {
        return [
            'carriers' => AnalyticsOverviewHelper::carrierPerformance((int) $user->current_team_id),
        ];
    }
}
