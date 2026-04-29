<?php

namespace App\Helpers\Navigation;

use App\Helpers\Analytics\AnalyticsOverviewHelper;
use App\Models\User;

class NavigationCountsHelper
{
    public function __construct(
        private readonly AnalyticsOverviewHelper $analytics,
    ) {}

    public function defaultCounts(): array
    {
        return [
            'approvalsCount' => 0,
            'exceptionsCount' => 0,
            'returnsCount' => 0,
            'claimsCount' => 0,
            'queueCount' => 0,
            'printReady' => 0,
        ];
    }

    public function buildForUser(User $user): array
    {
        $counts = $this->defaultCounts();
        $teamId = $user->current_team_id;
        if (! $teamId) {
            return $counts;
        }

        $rights = $user->rights();

        if (in_array('shipments.approve', $rights, true)) {
            $counts['approvalsCount'] = $this->analytics->pendingApprovalsCount($teamId);
        }
        if (in_array('trackers.view', $rights, true)) {
            $counts['exceptionsCount'] = $this->analytics->trackerExceptionsCount($teamId);
        }
        if (in_array('labels.print', $rights, true)) {
            $counts['printReady'] = $this->analytics->printReadyCount($teamId);
        }

        return $counts;
    }
}
