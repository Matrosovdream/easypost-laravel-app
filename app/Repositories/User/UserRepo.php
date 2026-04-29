<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\AbstractRepo;

class UserRepo extends AbstractRepo
{
    protected $withRelations = ['roles', 'roles.rights'];

    public function __construct()
    {
        $this->model = new User();
    }

    public function getByEmail(string $email)
    {
        $item = $this->model
            ->where('email', $email)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    /**
     * Look up an active user by their HMAC-SHA256 PIN hash.
     * The caller must pre-compute hash_hmac('sha256', $pin, config('app.pin_pepper')).
     */
    public function getByPinHash(string $pinHash)
    {
        $item = $this->model
            ->where('pin_hash', $pinHash)
            ->where('is_active', true)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function getByRoleSlug(string $slug, $paginate = 20)
    {
        $query = $this->model
            ->with($this->withRelations)
            ->whereHas('roles', fn ($q) => $q->where('slug', $slug));

        return $this->mapItems($query->paginate($paginate));
    }

    public function getByFreshdeskContactId(int $fdContactId)
    {
        $item = $this->model
            ->where('freshdesk_contact_id', $fdContactId)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function touchLastLogin(int $id): void
    {
        $this->model->where('id', $id)->update(['last_login_at' => now()]);
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $user = $this->model->find($userId);
        $user?->roles()->sync($roleIds);
    }

    public function pinHashInUseByOther(string $hash, int $excludeUserId): bool
    {
        return $this->model->newQuery()
            ->where('pin_hash', $hash)
            ->where('id', '!=', $excludeUserId)
            ->exists();
    }

    public function setPinHash(int $userId, string $hash): void
    {
        $this->model->newQuery()->where('id', $userId)->update(['pin_hash' => $hash]);
    }

    public function setActive(int $userId, bool $active): void
    {
        $this->model->newQuery()->where('id', $userId)->update(['is_active' => $active]);
    }

    /**
     * Pivot-table helpers for role_user. Keeps raw DB access out of Actions while
     * avoiding the overhead of a dedicated Pivot model.
     */
    public function countAdminsInTeam(int $teamId, int $adminRoleId): int
    {
        return \Illuminate\Support\Facades\DB::table('role_user')
            ->where('team_id', $teamId)
            ->where('role_id', $adminRoleId)
            ->distinct('user_id')
            ->count('user_id');
    }

    public function hasRoleInTeam(int $userId, int $teamId, int $roleId): bool
    {
        return \Illuminate\Support\Facades\DB::table('role_user')
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    public function replaceTeamRole(int $userId, int $teamId, int $newRoleId, int $assignedBy): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $teamId, $newRoleId, $assignedBy) {
            \Illuminate\Support\Facades\DB::table('role_user')
                ->where('team_id', $teamId)
                ->where('user_id', $userId)
                ->delete();
            \Illuminate\Support\Facades\DB::table('role_user')->insert([
                'team_id' => $teamId,
                'user_id' => $userId,
                'role_id' => $newRoleId,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function teamMembershipForUser(int $userId, int $teamId): ?object
    {
        return \Illuminate\Support\Facades\DB::table('team_user')
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->first();
    }

    public function hasActiveTeamMembership(int $userId): bool
    {
        return \Illuminate\Support\Facades\DB::table('team_user')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    public function touchLastLoginNow(int $userId): void
    {
        $this->model->newQuery()->where('id', $userId)->update(['last_login_at' => now()]);
    }

    public function listUsersByRoleWithStats(int $teamId, string $roleSlug): \Illuminate\Support\Collection
    {
        $thirty = now()->subDays(30);

        $base = \Illuminate\Support\Facades\DB::table('users')
            ->join('team_user', 'team_user.user_id', '=', 'users.id')
            ->join('role_user', function ($j) use ($teamId) {
                $j->on('role_user.user_id', '=', 'users.id')->where('role_user.team_id', $teamId);
            })
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('team_user.team_id', $teamId)
            ->where('roles.slug', $roleSlug)
            ->whereNull('users.deleted_at')
            ->select(
                'users.id', 'users.name', 'users.email', 'users.is_active',
                'users.last_login_at', 'users.created_at',
                'team_user.joined_at', 'team_user.status as membership_status',
                'team_user.client_id',
            );

        switch ($roleSlug) {
            case 'manager':
                $base
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.assigned_to = users.id and shipments.deleted_at is null) as shipments_assigned', [$teamId])
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.approved_by = users.id and shipments.deleted_at is null and shipments.approved_at >= ?) as shipments_approved_30d', [$teamId, $thirty])
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.approved_by = users.id and shipments.deleted_at is null) as shipments_approved_total', [$teamId])
                    ->selectRaw("(select count(*) from approvals where approvals.team_id = ? and approvals.approver_id = users.id and approvals.status = 'pending') as approvals_pending", [$teamId]);
                break;
            case 'shipper':
                $base
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.assigned_to = users.id and shipments.packed_at is null and shipments.deleted_at is null) as shipments_assigned_open', [$teamId])
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.assigned_to = users.id and shipments.packed_at >= ? and shipments.deleted_at is null) as shipments_packed_30d', [$teamId, $thirty]);
                break;
            case 'cs_agent':
                $base
                    ->selectRaw('(select count(*) from returns where returns.team_id = ? and returns.approved_by = users.id and returns.approved_at >= ? and returns.deleted_at is null) as returns_handled_30d', [$teamId, $thirty])
                    ->selectRaw('(select count(*) from claims where claims.team_id = ? and claims.assigned_to = users.id and claims.deleted_at is null) as claims_assigned', [$teamId])
                    ->selectRaw("(select count(*) from claims where claims.team_id = ? and claims.assigned_to = users.id and claims.state = 'open' and claims.deleted_at is null) as claims_open", [$teamId]);
                break;
            case 'client':
                $base
                    ->selectRaw('(select count(*) from shipments where shipments.team_id = ? and shipments.client_id = team_user.client_id and shipments.created_at >= ? and shipments.deleted_at is null) as shipments_30d', [$teamId, $thirty])
                    ->selectRaw("(select count(*) from returns where returns.team_id = ? and returns.client_id = team_user.client_id and returns.status not in ('refunded','closed') and returns.deleted_at is null) as returns_open", [$teamId]);
                break;
            // viewer: no extra stats
        }

        return $base->orderBy('users.name')->get();
    }

    public function listManagersWithStats(int $teamId): \Illuminate\Support\Collection
    {
        return $this->listUsersByRoleWithStats($teamId, 'manager');
    }

    public function listTeamMembers(int $teamId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('users')
            ->join('team_user', 'team_user.user_id', '=', 'users.id')
            ->leftJoin('role_user', function ($j) use ($teamId) {
                $j->on('role_user.user_id', '=', 'users.id')->where('role_user.team_id', $teamId);
            })
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('team_user.team_id', $teamId)
            ->whereNull('users.deleted_at')
            ->select(
                'users.id', 'users.name', 'users.email', 'users.is_active', 'users.last_login_at',
                'roles.slug as role_slug', 'roles.name as role_name',
                'team_user.spending_cap_cents', 'team_user.daily_cap_cents', 'team_user.status as membership_status',
                'team_user.client_id',
            )
            ->orderBy('users.name')
            ->get();
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id'                   => $item->id,
            'email'                => $item->email,
            'name'                 => $item->name,
            'phone'                => $item->phone,
            'avatar'               => $item->avatar,
            'is_active'            => (bool) $item->is_active,
            'freshdesk_contact_id' => $item->freshdesk_contact_id,
            'last_login_at'        => $item->last_login_at,
            'roles'                => $item->relationLoaded('roles')
                ? $item->roles->map(fn ($r) => [
                    'id'   => $r->id,
                    'slug' => $r->slug,
                    'name' => $r->name,
                ])->values()->toArray()
                : [],
            'rights'               => $item->relationLoaded('roles') ? $item->rights() : [],
            'created_at'           => $item->created_at,
            'Model'                => $item,
        ];
    }
}
