<?php

namespace App\Services\DummyData\Importers;

use App\Models\Parcel;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class ParcelImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();

        foreach ($this->loader->load('parcels.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = Parcel::query()
                ->where('team_id', $teamId)
                ->where('ep_parcel_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            Parcel::create([
                'team_id' => $teamId,
                'ep_parcel_id' => $payload['id'],
                'predefined_package' => $payload['predefined_package'] ?? null,
                'length_in' => $payload['length'] ?? null,
                'width_in' => $payload['width'] ?? null,
                'height_in' => $payload['height'] ?? null,
                'weight_oz' => $payload['weight'] ?? 0,
                'line_items' => $payload['line_items'] ?? null,
            ]);
            $count++;
        }

        return $count;
    }
}
