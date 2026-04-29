<?php

namespace App\Repositories\Infra;

use Illuminate\Support\Facades\DB;

/**
 * Lightweight repo over the `invitations` table. Like audit logs, we don't hydrate
 * these into models — they are short-lived tokens consumed by the accept-invite
 * flow and queried by unique constraint.
 */
class InvitationRepo
{
    public function create(array $data): int
    {
        return DB::table('invitations')->insertGetId(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    public function findByToken(string $token): ?object
    {
        $row = DB::table('invitations')->where('token', $token)->first();
        return $row ?: null;
    }

    public function markAccepted(string $token): void
    {
        DB::table('invitations')->where('token', $token)->update([
            'accepted_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function revoke(int $id): void
    {
        DB::table('invitations')->where('id', $id)->update([
            'revoked_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
