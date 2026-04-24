<?php

namespace App\Mixins\Integrations\EasyPost;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin, typed wrapper over EasyPost's REST API.
 *
 * Only the endpoints we actually use ship in this class; everything else lives
 * behind a generic get/post. Tests can swap this class via the container, but
 * Http::fake() is typically enough since we go through the HTTP facade.
 */
class EasyPostClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.easypost.com/v2',
    ) {}

    public function createShipment(array $payload): array
    {
        return $this->request()->post('/shipments', ['shipment' => $payload])->throw()->json();
    }

    public function buyShipment(string $shipmentId, string $rateId, ?int $insuranceCents = null): array
    {
        $body = ['rate' => ['id' => $rateId]];
        if ($insuranceCents !== null) {
            $body['insurance'] = number_format($insuranceCents / 100, 2, '.', '');
        }
        return $this->request()->post("/shipments/{$shipmentId}/buy", $body)->throw()->json();
    }

    public function rerateShipment(string $shipmentId): array
    {
        return $this->request()->get("/shipments/{$shipmentId}/rerate")->throw()->json();
    }

    public function refundShipment(string $shipmentId): array
    {
        return $this->request()->post("/shipments/{$shipmentId}/refund")->throw()->json();
    }

    public function getShipment(string $shipmentId): array
    {
        return $this->request()->get("/shipments/{$shipmentId}")->throw()->json();
    }

    public function createTracker(string $trackingCode, string $carrier): array
    {
        return $this->request()->post('/trackers', [
            'tracker' => ['tracking_code' => $trackingCode, 'carrier' => $carrier],
        ])->throw()->json();
    }

    public function createBatch(array $shipmentIds): array
    {
        return $this->request()->post('/batches', [
            'batch' => ['shipments' => array_map(fn ($id) => ['id' => $id], $shipmentIds)],
        ])->throw()->json();
    }

    public function buyBatch(string $batchId): array
    {
        return $this->request()->post("/batches/{$batchId}/buy")->throw()->json();
    }

    public function labelBatch(string $batchId, string $format = 'PDF'): array
    {
        return $this->request()->post("/batches/{$batchId}/label", ['file_format' => $format])->throw()->json();
    }

    public function createScanForm(array $shipmentIds): array
    {
        return $this->request()->post('/scan_forms', [
            'scan_form' => ['shipments' => array_map(fn ($id) => ['id' => $id], $shipmentIds)],
        ])->throw()->json();
    }

    public function createPickup(array $payload): array
    {
        return $this->request()->post('/pickups', ['pickup' => $payload])->throw()->json();
    }

    public function buyPickup(string $pickupId, string $carrier, string $service): array
    {
        return $this->request()->post("/pickups/{$pickupId}/buy", [
            'carrier' => $carrier,
            'service' => $service,
        ])->throw()->json();
    }

    public function cancelPickup(string $pickupId): array
    {
        return $this->request()->post("/pickups/{$pickupId}/cancel")->throw()->json();
    }

    public function createInsurance(array $payload): array
    {
        return $this->request()->post('/insurances', ['insurance' => $payload])->throw()->json();
    }

    public function refundInsurance(string $insuranceId): array
    {
        return $this->request()->post("/insurances/{$insuranceId}/refund")->throw()->json();
    }

    public function createClaim(array $payload): array
    {
        return $this->request()->post('/claims', ['claim' => $payload])->throw()->json();
    }

    public function cancelClaim(string $claimId): array
    {
        return $this->request()->post("/claims/{$claimId}/cancel")->throw()->json();
    }

    public function createReturnShipment(array $payload): array
    {
        $payload['is_return'] = true;
        return $this->request()->post('/shipments', ['shipment' => $payload])->throw()->json();
    }

    public function createAndVerifyAddress(array $payload, array $verify = ['delivery']): array
    {
        $body = ['address' => $payload, 'verify' => $verify];
        return $this->request()->post('/addresses', $body)->throw()->json();
    }

    public function verifyAddress(string $addressId): array
    {
        return $this->request()->get("/addresses/{$addressId}/verify")->throw()->json();
    }

    public function deleteTracker(string $trackerId): array
    {
        return $this->request()->delete("/trackers/{$trackerId}")->throw()->json();
    }

    public function raw(): PendingRequest
    {
        return $this->request();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->apiKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }
}
