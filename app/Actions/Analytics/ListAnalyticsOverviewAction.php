<?php

namespace App\Actions\Analytics;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ListAnalyticsOverviewAction
{
    public function execute(User $user): array
    {
        $teamId = (int) $user->current_team_id;
        return Cache::remember("analytics.overview.team.{$teamId}", 60, function () use ($teamId) {
            $byStatus = DB::table('shipments')
                ->where('team_id', $teamId)
                ->selectRaw('status, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost')
                ->groupBy('status')
                ->get()
                ->map(fn ($r) => ['status' => $r->status, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
                ->values();

            $byCarrier = DB::table('shipments')
                ->where('team_id', $teamId)
                ->whereNotNull('carrier')
                ->selectRaw('carrier, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost')
                ->groupBy('carrier')
                ->orderByDesc('cnt')
                ->get()
                ->map(fn ($r) => ['carrier' => $r->carrier, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
                ->values();

            $totalShipments = DB::table('shipments')->where('team_id', $teamId)->count();
            $totalCost = (int) DB::table('shipments')->where('team_id', $teamId)->sum('cost_cents');

            $dailyLast30 = DB::table('shipments')
                ->where('team_id', $teamId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as d, count(*) as cnt, coalesce(sum(cost_cents), 0) as cost")
                ->groupByRaw("to_char(created_at, 'YYYY-MM-DD')")
                ->orderBy('d')
                ->get()
                ->map(fn ($r) => ['date' => $r->d, 'count' => (int) $r->cnt, 'cost_cents' => (int) $r->cost])
                ->values();

            return [
                'total_shipments' => $totalShipments,
                'total_cost_cents' => $totalCost,
                'by_status' => $byStatus,
                'by_carrier' => $byCarrier,
                'daily_30d' => $dailyLast30,
            ];
        });
    }
}
