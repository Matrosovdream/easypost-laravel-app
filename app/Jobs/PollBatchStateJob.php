<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as QueueableTrait;
use Illuminate\Support\Facades\Http;

/**
 * Polls EP for batch state transitions. EP also sends webhooks on batch.updated,
 * but we poll every 30s for 10 minutes as a safety net in case a webhook is lost.
 * Self-reschedules until terminal state.
 */
class PollBatchStateJob implements ShouldQueue
{
    use Queueable, QueueableTrait;

    public int $tries = 20; // 20 * 30s = 10 minutes

    public function __construct(public int $batchId) {}

    public function handle(EasyPostClient $ep): void
    {
        $batch = Batch::find($this->batchId);
        if (! $batch || ! $batch->ep_batch_id) return;

        if (in_array($batch->state, ['purchased', 'failed', 'purchase_failed'], true)) return;

        try {
            $resp = $ep->raw()->get("/batches/{$batch->ep_batch_id}")->json();
            $batch->forceFill([
                'state' => $resp['state'] ?? $batch->state,
                'status_summary' => $resp['status'] ?? $batch->status_summary,
                'label_pdf_s3_key' => $resp['label_url'] ?? $batch->label_pdf_s3_key,
            ])->save();
        } catch (\Throwable) {
            // keep retrying
        }

        if (! in_array($batch->fresh()->state, ['purchased', 'failed', 'purchase_failed'], true)) {
            self::dispatch($this->batchId)->delay(now()->addSeconds(30));
        }
    }
}
