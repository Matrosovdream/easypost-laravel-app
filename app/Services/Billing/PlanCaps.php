<?php

namespace App\Services\Billing;

use App\Helpers\Analytics\AnalyticsOverviewHelper;

class PlanCaps
{
    public const CAPS = [
        'starter' => 100,
        'team' => 1000,
        'business' => 5000,
        '3pl' => 15000,
        'enterprise' => null, // unlimited
    ];

    public function __construct(
        private readonly AnalyticsOverviewHelper $analytics,
    ) {}

    public function capForPlan(string $plan): ?int
    {
        return self::CAPS[$plan] ?? self::CAPS['starter'];
    }

    public function usageForTeamThisMonth(int $teamId): int
    {
        return $this->analytics->monthlyUsageCount($teamId, [
            'purchased', 'packed', 'delivered', 'in_transit', 'out_for_delivery',
        ]);
    }

    public function remaining(int $teamId, string $plan): ?int
    {
        $cap = $this->capForPlan($plan);
        if ($cap === null) return null; // unlimited
        return max(0, $cap - $this->usageForTeamThisMonth($teamId));
    }

    public function isOverCap(int $teamId, string $plan): bool
    {
        $cap = $this->capForPlan($plan);
        if ($cap === null) return false;
        return $this->usageForTeamThisMonth($teamId) >= $cap;
    }
}
