<?php

namespace App\Actions\Billing;

use App\Helpers\Billing\BillingPlanHelper;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

class OpenBillingPortalAction
{
    public function __construct(
        private readonly TeamRepo $teams,
        private readonly BillingPlanHelper $helper,
    ) {}

    public function execute(User $user): array
    {
        abort_unless(in_array('billing.manage', $user->rights(), true), 403);

        $mapped = $this->teams->getByID($user->current_team_id);
        abort_if(! $mapped, 404);
        /** @var \App\Models\Team $team */
        $team = $mapped['Model'];

        try {
            $url = $team->billingPortalUrl(config('billing.success_url'));
            return ['_status' => 200, 'body' => ['url' => $url]];
        } catch (\Throwable $e) {
            if (app()->environment(['local', 'testing'])) {
                return ['_status' => 200, 'body' => $this->helper->toPortalSimulated()];
            }
            return ['_status' => 502, 'body' => ['message' => 'Could not open portal: '.$e->getMessage()]];
        }
    }
}
