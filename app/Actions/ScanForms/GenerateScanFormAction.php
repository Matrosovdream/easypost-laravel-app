<?php

namespace App\Actions\ScanForms;

use App\Helpers\ScanForms\ScanFormHelper;
use App\Models\ScanForm;
use App\Models\User;
use App\Mixins\Integrations\EasyPost\EasyPostClient;
use App\Repositories\Operations\ScanFormRepo;
use App\Repositories\Shipping\ShipmentRepo;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class GenerateScanFormAction
{
    public function __construct(
        private readonly EasyPostClient $ep,
        private readonly ScanFormRepo $scanForms,
        private readonly ShipmentRepo $shipments,
        private readonly ScanFormHelper $helper,
    ) {}

    /**
     * Validates that all shipments share the same carrier + from_address, then requests
     * a scan form from EasyPost and persists it.
     */
    public function execute(User $user, array $shipmentIds): array
    {
        Gate::authorize('create', ScanForm::class);

        $teamId = (int) $user->current_team_id;

        $shipments = $this->shipments->inTeam($teamId, ['id' => $shipmentIds])
            ->filter(fn ($s) => $s->tracking_code && $s->ep_shipment_id)
            ->values();

        if ($shipments->isEmpty()) {
            throw new RuntimeException('No qualifying shipments (must be purchased with a tracking code).');
        }
        if ($shipments->pluck('carrier')->unique()->count() !== 1) {
            throw new RuntimeException('All shipments must share the same carrier.');
        }
        if ($shipments->pluck('from_address_id')->unique()->count() !== 1) {
            throw new RuntimeException('All shipments must ship from the same address.');
        }

        $first = $shipments->first();

        $epResp = null;
        try {
            $epResp = $this->ep->createScanForm($shipments->pluck('ep_shipment_id')->values()->all())->json();
        } catch (\Throwable) {
            // leave status 'creating'
        }

        /** @var ScanForm $scanForm */
        $scanForm = $this->scanForms->create([
            'team_id' => $teamId,
            'ep_scan_form_id' => $epResp['id'] ?? null,
            'carrier' => $first->carrier,
            'from_address_id' => $first->from_address_id,
            'form_pdf_s3_key' => $epResp['form_url'] ?? null,
            'tracking_codes' => $shipments->pluck('tracking_code')->values()->all(),
            'status' => $epResp ? ($epResp['status'] ?? 'created') : 'creating',
            'created_by' => $user->id,
        ])['Model'];

        return $this->helper->toCreatedPayload($scanForm);
    }
}
