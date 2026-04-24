<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class PlanCaps
{
    public const CAPS = [
        'starter' => 100,
        'team' => 1000,
        'business' => 5000,
        '3pl' => 15000,
        'enterprise' => null, // unlimited
    ];

    public function capForPlan(string $plan): ?int
    {
        return self::CAPS[$plan] ?? self::CAPS['starter'];
    }

    public function usageForTeamThisMonth(int $teamId): int
    {
        return (int) DB::table('shipments')
            ->where('team_id', $teamId)
            ->whereIn('status', ['purchased', 'packed', 'delivered', 'in_transit', 'out_for_delivery'])
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
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
