<?php

namespace App\Actions\Webhooks;

use App\Helpers\Webhooks\StripeWebhookHelper;
use App\Repositories\Infra\WebhookEventRepo;
use Illuminate\Http\Request;

class HandleStripeWebhookAction
{
    public function __construct(
        private readonly WebhookEventRepo $webhookEvents,
        private readonly StripeWebhookHelper $helper,
    ) {}

    public function execute(Request $request): array
    {
        $payload = $request->json()->all();
        $eventId = (string) ($payload['id'] ?? 'evt_unknown_'.uniqid());
        $type = (string) ($payload['type'] ?? 'unknown');

        $this->webhookEvents->store($this->helper->buildEventRow($eventId, $type, $payload));

        try {
            $obj = $payload['data']['object'] ?? [];

            match ($type) {
                'checkout.session.completed' => $this->helper->onCheckoutComplete($obj),
                'customer.subscription.created',
                'customer.subscription.updated' => $this->helper->onSubscriptionChange($obj),
                'customer.subscription.deleted' => $this->helper->onSubscriptionCancelled($obj),
                default => null,
            };

            $this->webhookEvents->markProcessed('stripe', $eventId);
        } catch (\Throwable $e) {
            $this->webhookEvents->markFailed('stripe', $eventId, $e->getMessage());
        }

        return ['ok' => true];
    }
}
