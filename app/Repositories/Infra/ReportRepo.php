<?php

namespace App\Repositories\Infra;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * `reports` rows hold EP-side report lifecycle state. No Eloquent model — the
 * shape is flat and we only ever list + insert.
 */
class ReportRepo
{
    public function listForTeam(int $teamId, int $limit = 50): Collection
    {
        return DB::table('reports')
            ->where('team_id', $teamId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): int
    {
        return DB::table('reports')->insertGetId(array_merge($data, [
            'status' => $data['status'] ?? 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
