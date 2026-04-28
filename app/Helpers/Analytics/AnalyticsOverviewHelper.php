<?php

namespace App\Helpers\Analytics;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewHelper
{
    private static function baseQuery(int $teamId): Builder
    {
        return DB::table('shipments')->where('team_id', $teamId);
    }

    public static function byStatus(int $teamId): Collection
    {
        return self::baseQuery($teamId)
            ->selectRaw('status, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost')
            ->groupBy('status')
            ->get()
            ->map(fn ($r) => ['status' => $r->status, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
            ->values();
    }

    public static function byCarrier(int $teamId): Collection
    {
        return self::baseQuery($teamId)
            ->whereNotNull('carrier')
            ->selectRaw('carrier, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost')
            ->groupBy('carrier')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => ['carrier' => $r->carrier, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
            ->values();
    }

    public static function daily(int $teamId, int $days = 30): Collection
    {
        return self::baseQuery($teamId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as d, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost")
            ->groupByRaw("to_char(created_at, 'YYYY-MM-DD')")
            ->orderBy('d')
            ->get()
            ->map(fn ($r) => ['date' => $r->d, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
            ->values();
    }

    /**
     * Derive totals from a byStatus collection — avoids two extra full-table scans.
     */
    public static function totalsFromByStatus(Collection $byStatus): array
    {
        return [
            'total_shipments' => (int) $byStatus->sum('count'),
            'total_cost_cents' => (int) $byStatus->sum('cost_cents'),
        ];
    }

    public static function carrierPerformance(int $teamId): Collection
    {
        return self::baseQuery($teamId)
            ->whereNotNull('carrier')
            ->selectRaw('carrier,
                count(*) as total,
                sum(case when status = \'delivered\' then 1 else 0 end) as delivered,
                sum(case when status = \'voided\' then 1 else 0 end) as voided,
                coalesce(sum(cost_cents), 0) as cost,
                coalesce(avg(cost_cents), 0)::int as avg_cost')
            ->groupBy('carrier')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'carrier' => $r->carrier,
                'total' => (int) $r->total,
                'delivered' => (int) $r->delivered,
                'voided' => (int) $r->voided,
                'delivery_rate_pct' => $r->total > 0 ? round(100.0 * ((int) $r->delivered) / ((int) $r->total), 1) : 0,
                'cost_cents' => (int) $r->cost,
                'avg_cost_cents' => (int) $r->avg_cost,
            ])
            ->values();
    }

    public static function printReadyCount(int $teamId): int
    {
        return self::baseQuery($teamId)
            ->where('status', 'purchased')
            ->whereNull('packed_at')
            ->count();
    }

    public static function monthlyUsageCount(int $teamId, array $statuses): int
    {
        return (int) self::baseQuery($teamId)
            ->whereIn('status', $statuses)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }
}
