<?php

namespace App\Repositories\Infra;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Thin repo over the DB-level `audit_logs` table. The model layer is intentionally
 * absent here — audit rows are append-only, indexed for high-volume writes, and
 * never hydrated into an Eloquent instance.
 */
class AuditLogRepo
{
    public function record(array $data): int
    {
        return DB::table('audit_logs')->insertGetId(array_merge($data, [
            'created_at' => $data['created_at'] ?? now(),
        ]));
    }

    public function paginateForTeam(
        int $teamId,
        ?int $userIdScope = null,
        ?string $actionPrefix = null,
        int $perPage = 50,
    ): LengthAwarePaginator {
        $q = DB::table('audit_logs')
            ->leftJoin('users', 'users.id', '=', 'audit_logs.user_id')
            ->where('audit_logs.team_id', $teamId)
            ->select('audit_logs.*', 'users.name as user_name', 'users.email as user_email');
        if ($userIdScope !== null) $q->where('audit_logs.user_id', $userIdScope);
        if ($actionPrefix) $q->where('audit_logs.action', 'like', $actionPrefix.'%');
        return $q->orderByDesc('audit_logs.id')->paginate($perPage);
    }
}
