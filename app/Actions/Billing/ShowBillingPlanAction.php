<?php

namespace App\Actions\Billing;

use App\Helpers\Billing\BillingPlanHelper;
use App\Models\User;
use App\Repositories\Team\TeamRepo;
use App\Services\Billing\PlanCaps;

class ShowBillingPlanAction
{
    public function __construct(
        private readonly TeamRepo $teams,
        private readonly PlanCaps $caps,
        private readonly BillingPlanHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        $mapped = $this->teams->getByID($user->current_team_id);
        abort_if(! $mapped, 404);

        /** @var \App\Models\Team $team */
        $team = $mapped['Model'];

        $cap = $this->caps->capForPlan((string) $team->plan);
        $used = $this->caps->usageForTeamThisMonth($team->id);

        return $this->helper->toPlanPayload($team, $used, $cap);
    }
}
