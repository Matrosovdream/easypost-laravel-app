<?php

namespace App\Models\Scopes;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Team-isolation scope: every shipment query is bound to the viewer's current team
 * and further narrowed by role:
 *   - admin/manager/cs_agent/viewer: see everything in the team
 *   - client: only their own client_id
 *   - shipper: only shipments assigned to them (MyQueue default)
 * The scope is a no-op when called outside an authenticated request (console, jobs),
 * so internal consumers must apply team scoping themselves.
 */
class ShipmentTeamScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $teamId = $user->current_team_id;
        if (! $teamId) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->getTable().'.team_id', $teamId);

        $user->loadMissing('roles');
        $roles = $user->roles->pluck('slug')->all();

        if (in_array('client', $roles, true)) {
            $membership = $user->teams()
                ->where('teams.id', $teamId)
                ->first()
                ?->pivot;
            $clientId = $membership?->client_id;
            if ($clientId) {
                $builder->where($model->getTable().'.client_id', $clientId);
            } else {
                $builder->whereRaw('1 = 0');
            }
            return;
        }

        if (in_array('shipper', $roles, true)
            && ! array_intersect($roles, ['admin', 'manager'])
        ) {
            $builder->where($model->getTable().'.assigned_to', $user->id);
        }
    }
}
