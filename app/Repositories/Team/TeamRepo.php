<?php

namespace App\Repositories\Team;

use App\Models\Team;
use App\Repositories\AbstractRepo;

class TeamRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Team();
    }

    public function getByStripeCustomerId(string $customerId): ?Team
    {
        return Team::where('stripe_customer_id', $customerId)
            ->orWhere('stripe_id', $customerId)
            ->first();
    }

    public function updatePlan(int $id, string $plan, ?string $status = null): ?Team
    {
        $team = Team::find($id);
        if (! $team) return null;
        $payload = ['plan' => $plan];
        if ($status !== null) $payload['status'] = $status;
        $team->forceFill($payload)->save();
        return $team->fresh();
    }
}
