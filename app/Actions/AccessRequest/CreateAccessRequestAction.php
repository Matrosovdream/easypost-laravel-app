<?php

namespace App\Actions\AccessRequest;

use App\Models\User;
use App\Repositories\Infra\AccessRequestRepo;

class CreateAccessRequestAction
{
    public function __construct(private readonly AccessRequestRepo $accessRequests) {}

    public function execute(User $user, string $permission, ?string $targetUrl): int
    {
        return $this->accessRequests->create([
            'team_id' => $user->current_team_id,
            'user_id' => $user->id,
            'requested_permission' => $permission,
            'target_url' => $targetUrl,
        ]);
    }
}
