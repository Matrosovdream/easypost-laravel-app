<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = $this->resource;

        /** @var \App\Models\User $model */
        $model = $data['Model'];
        $model->loadMissing('roles.rights', 'currentTeam');

        $roles = $model->roles->map(fn ($r) => [
            'id'   => $r->id,
            'slug' => $r->slug,
            'name' => $r->name,
        ])->values();

        return [
            'id'            => $data['id'],
            'email'         => $data['email'] ?? $model->email,
            'name'          => $data['name'] ?? $model->name,
            'phone'         => $model->phone,
            'avatar'        => $model->avatar_s3_key,
            'locale'        => $model->locale,
            'timezone'      => $model->timezone,
            'is_active'     => (bool) $model->is_active,
            'current_team'  => $model->currentTeam ? [
                'id'    => $model->currentTeam->id,
                'name'  => $model->currentTeam->name,
                'plan'  => $model->currentTeam->plan,
                'mode'  => $model->currentTeam->mode,
                'status' => $model->currentTeam->status,
            ] : null,
            'roles'         => $roles,
            'permissions'   => $model->rights(),
            'last_login_at' => $model->last_login_at?->toIso8601String(),
            'created_at'    => $model->created_at?->toIso8601String(),
        ];
    }
}
