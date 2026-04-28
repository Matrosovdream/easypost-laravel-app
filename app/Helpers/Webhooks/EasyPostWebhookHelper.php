<?php

namespace App\Helpers\Webhooks;

use App\Repositories\Shipping\ShipmentRepo;
use App\Repositories\Tracker\TrackerRepo;

class EasyPostWebhookHelper
{
    public function __construct(
        private readonly ShipmentRepo $shipments,
        private readonly TrackerRepo $trackers,
    ) {}

    /**
     * Resolve a team_id from an EasyPost webhook payload's `result` block by
     * matching against shipment / tracker records via EP id or tracking code.
     */
    public function resolveTeamId(array $result): ?int
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

    /**
     * Validate the HMAC-SHA256 signature against the configured shared secret.
     */
    public function isValidSignature(string $rawBody, string $signature): bool
    {
        $secret = (string) config('services.easypost.webhook_secret', '');
        if ($secret === '') return false;
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }

    public function buildEventRow(?int $teamId, string $eventId, string $description, bool $valid, array $payload, ?string $error = null): array
    {
        return array_filter([
            'team_id' => $teamId,
            'source' => 'easypost',
            'ep_event_id' => $eventId,
            'description' => substr($description, 0, 64),
            'signature_valid' => $valid,
            'payload' => json_encode($payload),
            'error' => $error,
        ], fn ($v) => $v !== null);
    }
}
