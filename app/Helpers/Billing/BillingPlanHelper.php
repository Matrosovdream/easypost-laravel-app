<?php

namespace App\Helpers\Billing;

use App\Models\Team;

class BillingPlanHelper
{
    public function toPlanPayload(Team $team, int $used, ?int $cap): array
    {
        return [
            'plan' => $team->plan,
            'status' => $team->status,
            'mode' => $team->mode,
            'trial_ends_at' => $team->trial_ends_at?->toIso8601String(),
            'stripe_customer_id' => $team->stripe_customer_id,
            'usage' => [
                'used' => $used,
                'cap' => $cap,
                'remaining' => $cap === null ? null : max(0, $cap - $used),
                'reset_at' => now()->startOfMonth()->addMonth()->toIso8601String(),
            ],
            'available_plans' => array_keys(config('billing.prices')),
        ];
    }

    public function toCheckoutSimulated(string $plan): array
    {
        return [
            'url' => url("/dashboard/settings/billing?checkout=simulated&plan={$plan}"),
            'simulated' => true,
        ];
    }

    public function toPortalSimulated(): array
    {
        return [
            'url' => url('/dashboard/settings/billing?portal=simulated'),
            'simulated' => true,
        ];
    }

    public function isPlaceholderPriceId(?string $priceId): bool
    {
        return ! $priceId || str_contains((string) $priceId, 'placeholder');
    }
}
