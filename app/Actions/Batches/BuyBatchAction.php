<?php

namespace App\Actions\Batches;

use App\Helpers\Batches\BatchHelper;
use App\Models\Batch;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Support\Facades\Gate;

class BuyBatchAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly BatchRepo $batches,
        private readonly BatchHelper $helper,
    ) {}

    public function execute(User $user, int $id): array
    {
        $batch = $this->batches->getModel()->newQuery()->find($id);
        abort_if(! $batch, 404);
        Gate::authorize('update', $batch);

        if ($batch->ep_batch_id) {
            try {
                $resp = $this->ep->buyBatch($batch->ep_batch_id);
                $batch = $this->batches->updateState(
                    $batch,
                    $resp['state'] ?? 'purchasing',
                    $resp['status'] ?? null,
                );
            } catch (\Throwable) {
                // leave state untouched; user can retry
            }
        } else {
            $batch = $this->batches->updateState($batch, 'purchased');
        }

        return $this->helper->toIdentity($batch);
    }
}
