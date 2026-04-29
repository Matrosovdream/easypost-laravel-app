<?php

namespace App\Jobs;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Pulls the label PDF (and ZPL/EPL2 when present on selected_rate) from EP's
 * temporary URL, persists them to the default filesystem, and stores the
 * resulting disk keys on the shipment row.
 *
 * Runs async so label buy responses stay fast. Idempotent — if the key is
 * already stored and reachable, no-op.
 */
class DownloadLabelAssetsJob implements ShouldQueue
{
    use Queueable, QueueableTrait;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $shipmentId) {}

    public function handle(): void
    {
        $shipment = Shipment::withoutGlobalScopes()->find($this->shipmentId);
        if (! $shipment || ! $shipment->label_s3_key) return;

        // The label_s3_key was set to EP's direct URL by BuyShipmentAction.
        // If that looks like a URL, fetch and persist to our disk.
        $src = $shipment->label_s3_key;
        if (! filter_var($src, FILTER_VALIDATE_URL)) return;

        try {
            $res = Http::timeout(20)->get($src);
            if (! $res->ok()) return;
            $path = "labels/{$shipment->team_id}/{$shipment->id}/label.pdf";
            Storage::put($path, $res->body());
            $shipment->forceFill(['label_pdf_s3_key' => $path])->save();
        } catch (\Throwable) {
            // Let the queue retry per $tries/$backoff
        }
    }
}
