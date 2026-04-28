<?php

namespace App\Jobs;

use App\Helpers\Notifications\TrackingNotificationHelper;
use App\Repositories\Infra\NotificationEventRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;

/**
 * Emits a tracking status notification. Respects the recipient's
 * notification_prefs — if they've opted out of `email.shipment.delivered`
 * the job short-circuits.
 *
 * Currently writes to `notification_events`; real SMTP/SMS lands with the
 * mailer integrations step.
 */
class SendTrackingNotificationJob implements ShouldQueue
{
    use Queueable, QueueableTrait;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $shipmentId, public string $trackerStatus) {}

    public function handle(
        ShipmentRepo $shipments,
        NotificationEventRepo $events,
        TrackingNotificationHelper $helper,
    ): void {
        $shipment = $shipments->findUnscoped($this->shipmentId, ['client']);
        if (! $shipment) return;

        $template = $helper->templateForStatus($this->trackerStatus);
        if (! $template) return;

        $recipient = $helper->recipientFor($shipment);
        if (! $recipient) return;

        if (! $events->tableExists()) return;

        $events->record($helper->buildEventRow($shipment, $template, $this->trackerStatus, $recipient));
    }
}
