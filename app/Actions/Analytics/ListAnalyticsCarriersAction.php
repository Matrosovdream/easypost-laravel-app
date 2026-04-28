<?php

namespace App\Actions\Analytics;

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Models\User;

class ListAnalyticsCarriersAction
{
    public function __construct(
        private readonly AnalyticsOverviewHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        return [
            'carriers' => $this->helper->carrierPerformance((int) $user->current_team_id),
        ];
    }
}
