<?php

namespace App\Actions\Batches;

use App\Models\Batch;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\BatchRepo;

class BuyBatchAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly BatchRepo $batches,
    ) {}

    public function execute(User $user, Batch $batch): Batch
    {
        if ($batch->ep_batch_id) {
            try {
                $resp = $this->ep->buyBatch($batch->ep_batch_id);
                $this->batches->updateState(
                    $batch,
                    $resp['state'] ?? 'purchasing',
                    $resp['status'] ?? null,
                );
            } catch (\Throwable) {
                // leave state untouched; user can retry
            }
        } else {
            $this->batches->updateState($batch, 'purchased');
        }

        return $batch->fresh(['shipments']);
    }
}
