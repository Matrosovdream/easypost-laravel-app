<?php

namespace App\Services\DummyData\Importers;

use App\Models\Address;
use App\Models\ScanForm;
use App\Models\Shipment;
use App\Services\DummyData\AssignmentPicker;
use App\Services\DummyData\DummyDataLoader;

class ScanFormImporter
{
    public function __construct(private DummyDataLoader $loader) {}

    public function import(AssignmentPicker $picker): int
    {
        $count = 0;
        $teamId = $picker->teamId();

        $addressMap = Address::query()
            ->where('team_id', $teamId)
            ->whereNotNull('ep_address_id')
            ->pluck('id', 'ep_address_id')
            ->all();

        foreach ($this->loader->load('scan_forms.json') as $payload) {
            if (empty($payload['id'])) {
                continue;
            }

            $existing = ScanForm::query()
                ->where('team_id', $teamId)
                ->where('ep_scan_form_id', $payload['id'])
                ->first();
            if ($existing) {
                continue;
            }

            $addrEpId = $payload['address']['id'] ?? null;
            $fromAddressId = $addrEpId ? ($addressMap[$addrEpId] ?? null) : null;
            if (!$fromAddressId) {
                continue;
            }

            $codes = $payload['tracking_codes'] ?? [];
            $scanForm = ScanForm::create([
                'team_id' => $teamId,
                'ep_scan_form_id' => $payload['id'],
                'carrier' => $payload['carrier'] ?? 'USPS',
                'from_address_id' => $fromAddressId,
                'form_pdf_s3_key' => $payload['form_url'] ?? null,
                'tracking_codes' => $codes,
                'status' => $payload['status'] ?? 'creating',
                'created_by' => $picker->shipper() ?? $picker->admin(),
            ]);

            if (!empty($codes)) {
                Shipment::withoutGlobalScopes()
                    ->where('team_id', $teamId)
                    ->whereIn('tracking_code', $codes)
                    ->whereNull('scan_form_id')
                    ->update(['scan_form_id' => $scanForm->id]);
            }

            $count++;
        }

        return $count;
    }
}
