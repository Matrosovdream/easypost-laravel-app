<?php

namespace App\Helpers\Users;

use Illuminate\Support\Collection;

class UserManagementHelper
{
    public function toListItem(object $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'email' => $r->email,
            'role_slug' => $r->role_slug,
            'role_name' => $r->role_name,
            'is_active' => (bool) $r->is_active,
            'last_login_at' => $r->last_login_at,
            'spending_cap_cents' => $r->spending_cap_cents,
            'daily_cap_cents' => $r->daily_cap_cents,
            'client_id' => $r->client_id,
            'membership_status' => $r->membership_status,
        ];
    }

    public function toListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn ($r) => $this->toListItem($r))->values(),
        ];
    }
}
