<?php

namespace App\Actions\Analytics;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ListAnalyticsCarriersAction
{
    public function execute(User $user): array
    {
        $teamId = (int) $user->current_team_id;
        $rows = DB::table('shipments')
            ->where('team_id', $teamId)
            ->whereNotNull('carrier')
            ->selectRaw('carrier,
                count(*) as total,
                sum(case when status = \'delivered\' then 1 else 0 end) as delivered,
                sum(case when status = \'voided\' then 1 else 0 end) as voided,
                coalesce(sum(cost_cents), 0) as cost,
                coalesce(avg(cost_cents), 0)::int as avg_cost')
            ->groupBy('carrier')
            ->orderByDesc('total')
            ->get();

        return [
            'carriers' => $rows->map(fn ($r) => [
                'carrier' => $r->carrier,
                'total' => (int) $r->total,
                'delivered' => (int) $r->delivered,
                'voided' => (int) $r->voided,
                'delivery_rate_pct' => $r->total > 0 ? round(100.0 * ((int) $r->delivered) / ((int) $r->total), 1) : 0,
                'cost_cents' => (int) $r->cost,
                'avg_cost_cents' => (int) $r->avg_cost,
            ])->values(),
        ];
    }
}
