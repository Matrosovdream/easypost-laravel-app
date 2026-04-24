<?php

namespace App\Http\Controllers\Rest\Webhooks;

use App\Http\Controllers\Controller;
use App\Repositories\Infra\WebhookEventRepo;
use App\Repositories\Team\TeamRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin handler that trusts Cashier's signature verification (run earlier in the
 * middleware stack). Persists every event via WebhookEventRepo and reflects plan
 * transitions onto `teams.plan` via TeamRepo.
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly TeamRepo $teams,
        private readonly WebhookEventRepo $webhookEvents,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        $eventId = (string) ($payload['id'] ?? 'evt_unknown_'.uniqid());
        $type = (string) ($payload['type'] ?? 'unknown');

        $this->webhookEvents->store([
            'source' => 'stripe',
            'ep_event_id' => $eventId,
            'description' => substr($type, 0, 64),
            'signature_valid' => true,
            'payload' => json_encode($payload),
        ]);

        try {
            $obj = $payload['data']['object'] ?? [];

            match ($type) {
                'checkout.session.completed' => $this->onCheckoutComplete($obj),
                'customer.subscription.created',
                'customer.subscription.updated' => $this->onSubscriptionChange($obj),
                'customer.subscription.deleted' => $this->onSubscriptionCancelled($obj),
                default => null,
            };

            $this->webhookEvents->markProcessed('stripe', $eventId);
        } catch (\Throwable $e) {
            $this->webhookEvents->markFailed('stripe', $eventId, $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    private function onCheckoutComplete(array $session): void
    {
        $customerId = $session['customer'] ?? null;
        if (! $customerId) return;

        $team = $this->teams->getByStripeCustomerId($customerId);
        if (! $team) return;

        $this->teams->update($team->id, [
            'stripe_subscription_id' => $session['subscription'] ?? $team->stripe_subscription_id,
        ]);
    }

    private function onSubscriptionChange(array $sub): void
    {
        $customerId = $sub['customer'] ?? null;
        $priceId = $sub['items']['data'][0]['price']['id'] ?? null;
        if (! $customerId || ! $priceId) return;

        $plan = collect(config('billing.prices'))->search($priceId);
        if (! $plan) return;

        $team = $this->teams->getByStripeCustomerId($customerId);
        if (! $team) return;

        $status = $sub['status'] === 'active'
            ? 'active'
            : (in_array($sub['status'] ?? '', ['past_due', 'unpaid'], true) ? 'locked' : $team->status);

        $this->teams->update($team->id, [
            'plan' => $plan,
            'status' => $status,
            'stripe_subscription_id' => $sub['id'] ?? $team->stripe_subscription_id,
        ]);
    }

    private function onSubscriptionCancelled(array $sub): void
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
}
