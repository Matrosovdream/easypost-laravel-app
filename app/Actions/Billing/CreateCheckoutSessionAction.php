<?php

namespace App\Actions\Billing;

use App\Helpers\Billing\BillingPlanHelper;
use App\Models\User;
use App\Repositories\Team\TeamRepo;

class CreateCheckoutSessionAction
{
    public function __construct(
        private readonly TeamRepo $teams,
        private readonly BillingPlanHelper $helper,
    ) {}

    /**
     * Returns either a {url} payload or an envelope with _status for the controller
     * to emit (e.g. 422 for unknown plan, 502 on Stripe failure).
     */
    public function execute(User $user, string $plan): array
    {
        abort_unless(in_array('billing.manage', $user->rights(), true), 403);

        $priceId = config("billing.prices.{$plan}");

        if ($this->helper->isPlaceholderPriceId($priceId)) {
            if (app()->environment(['local', 'testing'])) {
                return ['_status' => 200, 'body' => $this->helper->toCheckoutSimulated($plan)];
            }
            return ['_status' => 422, 'body' => ['message' => 'Unknown plan.']];
        }

        $mapped = $this->teams->getByID($user->current_team_id);
        abort_if(! $mapped, 404);
        /** @var \App\Models\Team $team */
        $team = $mapped['Model'];

        try {
            $session = $team->newSubscription('default', $priceId)
                ->checkout([
                    'success_url' => config('billing.success_url'),
                    'cancel_url' => config('billing.cancel_url'),
                ]);

            return ['_status' => 200, 'body' => ['url' => $session->url]];
        } catch (\Throwable $e) {
            return ['_status' => 502, 'body' => ['message' => 'Checkout failed: '.$e->getMessage()]];
        }
    }
}
