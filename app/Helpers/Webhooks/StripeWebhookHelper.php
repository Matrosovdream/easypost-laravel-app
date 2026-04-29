<?php

namespace App\Helpers\Webhooks;

use App\Models\Team;
use App\Repositories\Team\TeamRepo;

class StripeWebhookHelper
{
    public function __construct(
        private readonly TeamRepo $teams,
    ) {}

    public function onCheckoutComplete(array $session): void
    {
        $customerId = $session['customer'] ?? null;
        if (! $customerId) return;

        $team = $this->teams->getByStripeCustomerId($customerId);
        if (! $team) return;

        $this->teams->update($team->id, [
            'stripe_subscription_id' => $session['subscription'] ?? $team->stripe_subscription_id,
        ]);
    }

    public function onSubscriptionChange(array $sub): void
    {
        $customerId = $sub['customer'] ?? null;
        $priceId = $sub['items']['data'][0]['price']['id'] ?? null;
        if (! $customerId || ! $priceId) return;

        $plan = collect(config('billing.prices'))->search($priceId);
        if (! $plan) return;

        $team = $this->teams->getByStripeCustomerId($customerId);
        if (! $team) return;

        $this->teams->update($team->id, [
            'plan' => $plan,
            'status' => $this->resolveStatus($sub, $team),
            'stripe_subscription_id' => $sub['id'] ?? $team->stripe_subscription_id,
        ]);
    }

    public function onSubscriptionCancelled(array $sub): void
    {
        $customerId = $sub['customer'] ?? null;
        if (! $customerId) return;

        $team = $this->teams->getByStripeCustomerId($customerId);
        if (! $team) return;

        $this->teams->update($team->id, [
            'plan' => 'starter',
            'stripe_subscription_id' => null,
        ]);
    }

    public function buildEventRow(string $eventId, string $type, array $payload): array
    {
        return [
            'source' => 'stripe',
            'ep_event_id' => $eventId,
            'description' => substr($type, 0, 64),
            'signature_valid' => true,
            'payload' => json_encode($payload),
        ];
    }

    private function resolveStatus(array $sub, Team $team): string
    {
        if (($sub['status'] ?? null) === 'active') return 'active';
        if (in_array($sub['status'] ?? '', ['past_due', 'unpaid'], true)) return 'locked';
        return $team->status;
    }
}
