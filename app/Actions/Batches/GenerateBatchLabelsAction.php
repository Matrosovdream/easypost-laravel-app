<?php

namespace App\Actions\Batches;

use App\Models\Batch;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\BatchRepo;

class GenerateBatchLabelsAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly BatchRepo $batches,
    ) {}

    public function execute(Batch $batch, string $format = 'PDF'): Batch
    {
        if (! $batch->ep_batch_id) {
            return $batch;
        }
        try {
            $resp = $this->ep->labelBatch($batch->ep_batch_id, $format);
            $url = $resp['label_url']
                ?? ($resp['batch']['label_url'] ?? null);
            if ($url) {
                $this->batches->updateState($batch, $batch->state, labelUrl: $url);
            }
        } catch (\Throwable) {
            // ignore — labels can be retried
        }
        return $batch->fresh();
    }
}
