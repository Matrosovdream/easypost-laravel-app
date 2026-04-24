<?php

namespace App\Http\Controllers\Rest\Webhooks;

use App\Actions\Webhooks\ProcessEasypostWebhookAction;
use App\Http\Controllers\Controller;
use App\Repositories\Infra\WebhookEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Tracker\TrackerRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * EasyPost webhook intake. Verifies HMAC-SHA256 signature against the shared
 * secret configured in services.easypost.webhook_secret, persists every event
 * via WebhookEventRepo (idempotent on source+ep_event_id), and dispatches to
 * the relevant handler.
 */
class EasyPostWebhookController extends Controller
{
    public function __construct(
        private readonly ProcessEasypostWebhookAction $process,
        private readonly WebhookEventRepo $webhookEvents,
        private readonly ShipmentRepo $shipments,
        private readonly TrackerRepo $trackers,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.easypost.webhook_secret', '');
        $signature = $request->header('X-Hmac-Signature', '');
        $raw = $request->getContent();

        $expected = hash_hmac('sha256', $raw, $secret);
        $valid = $secret !== '' && hash_equals($expected, (string) $signature);

        $payload = $request->json()->all() ?: json_decode($raw, true) ?? [];
        $eventId = (string) ($payload['id'] ?? '');
        $description = (string) ($payload['description'] ?? 'unknown');
        $result = $payload['result'] ?? [];
        $teamId = $this->resolveTeamId($result);

        if (! $valid) {
            $this->webhookEvents->store([
                'team_id' => $teamId,
                'source' => 'easypost',
                'ep_event_id' => $eventId ?: 'unsigned_'.uniqid(),
                'description' => substr($description, 0, 64),
                'signature_valid' => false,
                'payload' => json_encode($payload),
                'error' => 'Bad signature',
            ]);
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $this->webhookEvents->store([
            'team_id' => $teamId,
            'source' => 'easypost',
            'ep_event_id' => $eventId,
            'description' => substr($description, 0, 64),
            'signature_valid' => true,
            'payload' => json_encode($payload),
        ]);

        try {
            $this->process->execute($description, $result, $teamId);
            $this->webhookEvents->markProcessed('easypost', $eventId);
        } catch (\Throwable $e) {
            $this->webhookEvents->markFailed('easypost', $eventId, $e->getMessage());
            // Always 200 on valid-signature webhooks so EP doesn't retry forever
        }

        return response()->json(['ok' => true]);
    }

    private function resolveTeamId(array $result): ?int
    {
        if (! empty($result['id']) && str_starts_with((string) $result['id'], 'shp_')) {
            $shipment = $this->shipments->findByEpShipmentId((string) $result['id']);
            if ($shipment) return (int) $shipment->team_id;
        }
        if (! empty($result['id']) && str_starts_with((string) $result['id'], 'trk_')) {
            $tracker = $this->trackers->findByEpIdOrCode((string) $result['id'], null);
            if ($tracker) return (int) $tracker->team_id;
        }
        if (! empty($result['tracking_code'])) {
            $tracker = $this->trackers->getByTrackingCode((string) $result['tracking_code']);
            if ($tracker) return (int) $tracker->team_id;
            $shipment = $this->shipments->findByTrackingCode((string) $result['tracking_code']);
            if ($shipment) return (int) $shipment->team_id;
        }
        return null;
    }
}
