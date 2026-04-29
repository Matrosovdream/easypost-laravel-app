<?php

namespace App\Services\DummyData;

use App\Services\DummyData\Importers\AddressImporter;
use App\Services\DummyData\Importers\BatchImporter;
use App\Services\DummyData\Importers\ClaimImporter;
use App\Services\DummyData\Importers\InsuranceImporter;
use App\Services\DummyData\Importers\ParcelImporter;
use App\Services\DummyData\Importers\PickupImporter;
use App\Services\DummyData\Importers\ReturnImporter;
use App\Services\DummyData\Importers\ScanFormImporter;
use App\Services\DummyData\Importers\ShipmentImporter;
use App\Services\DummyData\Importers\TrackerImporter;

class DummyDataMigrationService
{
    public function __construct(
        private AddressImporter $addressImporter,
        private ParcelImporter $parcelImporter,
        private ShipmentImporter $shipmentImporter,
        private TrackerImporter $trackerImporter,
        private BatchImporter $batchImporter,
        private PickupImporter $pickupImporter,
        private ScanFormImporter $scanFormImporter,
        private InsuranceImporter $insuranceImporter,
        private ClaimImporter $claimImporter,
        private ReturnImporter $returnImporter,
    ) {}

    public function run(): array
    {
        $picker = new AssignmentPicker();

        return [
            'addresses'  => $this->addressImporter->import($picker),
            'parcels'    => $this->parcelImporter->import($picker),
            'shipments'  => $this->shipmentImporter->import($picker),
            'trackers'   => $this->trackerImporter->import($picker),
            'batches'    => $this->batchImporter->import($picker),
            'pickups'    => $this->pickupImporter->import($picker),
            'scan_forms' => $this->scanFormImporter->import($picker),
            'insurances' => $this->insuranceImporter->import($picker),
            'claims'     => $this->claimImporter->import($picker),
            'returns'    => $this->returnImporter->import($picker),
        ];
    }
}
