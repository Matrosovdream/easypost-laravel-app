<?php

namespace App\Services\DummyData\Importers;

use App\Models\Batch;
use App\Models\Shipment;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;
use Illuminate\Support\Facades\DB;

class BatchImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();

        $shipmentMap = Shipment::withoutGlobalScopes()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_shipment_id')
            ->pluck('id', 'ep_shipment_id')
            ->all();

        foreach ($this->loader->load('batches.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Batch::query()
                ->where('team_id', $teamId)
                ->where('ep_batch_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $batch = Batch::create([
                'team_id' => $teamId,
                'ep_batch_id' => $payload['id'],
                'reference' => $payload['reference'] ?? null,
                'state' => $payload['state'] ?? 'creating',
                'num_shipments' => $payload['num_shipments'] ?? count($payload['shipments'] ?? []),
                'label_pdf_s3_key' => $payload['label_url'] ?? null,
                'status_summary' => $payload['status'] ?? null,
                'created_by' => $picker->admin(),
            ]);

            foreach ($payload['shipments'] ?? [] as $entry) {
                $shipmentEpId = $entry['id'] ?? null;
                $shipmentId = $shipmentEpId ? ($shipmentMap[$shipmentEpId] ?? null) : null;
                if (!$shipmentId) {
                    continue;
                }

                DB::table('batch_shipment')->insert([
                    'batch_id' => $batch->id,
                    'shipment_id' => $shipmentId,
                    'batch_status' => $entry['batch_status'] ?? null,
                    'batch_message' => $entry['batch_message'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Shipment::withoutGlobalScopes()
                    ->where('id', $shipmentId)
                    ->whereNull('batch_id')
                    ->update(['batch_id' => $batch->id]);
            }

            $count++;
        }

        return $count;
    }
}
