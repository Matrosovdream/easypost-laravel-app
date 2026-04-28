<?php

namespace App\Actions\Batches;

use App\Helpers\Batches\BatchHelper;
use App\Repositories\Operations\BatchRepo;
use Illuminate\Support\Facades\Gate;

class ShowBatchAction
{
    public function __construct(
        private readonly BatchRepo $batches,
        private readonly BatchHelper $helper,
    ) {}

    public function execute(int $id): array
    {
        $batch = $this->batches->findWithShipments($id);
        abort_if(! $batch, 404);
        Gate::authorize('view', $batch);

        return $this->helper->toDetail($batch);
    }
}
