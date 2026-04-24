<?php

namespace App\Actions\Batches;

use App\Models\Batch;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\BatchRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Support\Facades\DB;

class CreateBatchAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly BatchRepo $batches,
        private readonly ShipmentRepo $shipments,
    ) {}

    public function execute(User $user, array $shipmentIds, ?string $reference = null): Batch
    {
        $teamId = (int) $user->current_team_id;

        return DB::transaction(function () use ($user, $teamId, $shipmentIds, $reference) {
            $shipments = $this->shipments->inTeam($teamId, ['id' => $shipmentIds]);

            $epIds = $shipments->pluck('ep_shipment_id')->filter()->values()->all();

            $epBatch = null;
            try {
                if (! empty($epIds)) {
                    $epBatch = $this->ep->createBatch($epIds);
                }
            } catch (\Throwable) {
                // EP unavailable; batch stays in 'creating' and we can retry later
            }

            /** @var Batch $batch */
            $batch = $this->batches->create([
                'team_id' => $teamId,
                'ep_batch_id' => $epBatch['id'] ?? null,
                'reference' => $reference,
                'state' => $epBatch['state'] ?? 'creating',
                'num_shipments' => $shipments->count(),
                'status_summary' => $epBatch['status'] ?? null,
                'created_by' => $user->id,
            ])['Model'];

            $pivotRows = $shipments->mapWithKeys(fn ($s) => [
                $s->id => [
                    'batch_status' => 'queued',
                    'batch_message' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ])->all();

            $batch->shipments()->sync($pivotRows);

            return $batch->fresh(['shipments']);
        });
    }
}
