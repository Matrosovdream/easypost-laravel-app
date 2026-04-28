<?php

namespace App\Actions\Batches;

use App\Helpers\Batches\BatchHelper;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Support\Facades\Gate;

class GenerateBatchLabelsAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly BatchRepo $batches,
        private readonly BatchHelper $helper,
    ) {}

    public function execute(int $id, string $format = 'PDF'): array
    {
        $batch = $this->batches->getModel()->newQuery()->find($id);
        abort_if(! $batch, 404);
        Gate::authorize('update', $batch);

        if ($batch->ep_batch_id) {
            try {
                $resp = $this->ep->labelBatch($batch->ep_batch_id, $format);
                $url = $resp['label_url']
                    ?? ($resp['batch']['label_url'] ?? null);
                if ($url) {
                    $batch = $this->batches->updateState($batch, $batch->state, labelUrl: $url);
                }
            } catch (\Throwable) {
                // ignore — labels can be retried
            }
        }

        return $this->helper->toLabelResult($batch->fresh() ?? $batch);
    }
}
