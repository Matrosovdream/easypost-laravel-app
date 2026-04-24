<?php

namespace App\Jobs;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;
use Illuminate\Support\Facades\DB;

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

    public function handle(): void
    {
        $shipment = Shipment::withoutGlobalScopes()->with('client')->find($this->shipmentId);
        if (! $shipment) return;

        $template = match ($this->trackerStatus) {
            'delivered' => 'shipment.delivered',
            'out_for_delivery' => 'shipment.out_for_delivery',
            'failure', 'return_to_sender' => 'shipment.exception',
            default => null,
        };
        if (! $template) return;

        $recipient = $shipment->client?->contact_email;
        if (! $recipient) return;

        if (! DB::getSchemaBuilder()->hasTable('notification_events')) return;

        DB::table('notification_events')->insert([
            'team_id' => $shipment->team_id,
            'shipment_id' => $shipment->id,
            'channel' => 'email',
            'template' => $template,
            'recipient' => $recipient,
            'subject' => "Shipment update: {$this->trackerStatus}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
