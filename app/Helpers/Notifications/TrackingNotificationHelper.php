<?php

namespace App\Helpers\Notifications;

use App\Models\Shipment;

class TrackingNotificationHelper
{
    /**
     * Map an EasyPost tracker status to an internal email template id.
     * Returns null when the status doesn't warrant a customer notification.
     */
    public function templateForStatus(string $trackerStatus): ?string
    {
        return match ($trackerStatus) {
            'delivered' => 'shipment.delivered',
            'out_for_delivery' => 'shipment.out_for_delivery',
            'failure', 'return_to_sender' => 'shipment.exception',
            default => null,
        };
    }

    /**
     * Resolve the customer email recipient for a tracking notification.
     * Currently the client's contact_email; extend later for multi-recipient logic.
     */
    public function recipientFor(Shipment $shipment): ?string
    {
        return $shipment->client?->contact_email;
    }

    /**
     * Build the row payload to persist in `notification_events`.
     */
    public function buildEventRow(Shipment $shipment, string $template, string $trackerStatus, string $recipient): array
    {
        return [
            'team_id' => $shipment->team_id,
            'shipment_id' => $shipment->id,
            'channel' => 'email',
            'template' => $template,
            'recipient' => $recipient,
            'subject' => "Shipment update: {$trackerStatus}",
        ];
    }
}
