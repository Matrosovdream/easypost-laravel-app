<?php

namespace App\Mixins\Integrations\EasyPost;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin, typed wrapper over EasyPost's REST API.
 *
 * Each method is a pure transport — fires the HTTP call, throws on non-2xx,
 * and returns the raw `Response`. Callers (Actions) decode the body themselves
 * via `->json()` or `->json('field')`. The integration never decodes JSON.
 */
class EasyPostClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.easypost.com/v2',
    ) {}

    public function createShipment(array $payload): Response
    {
        return $this->request()->post('/shipments', ['shipment' => $payload])->throw();
    }

    public function buyShipment(string $shipmentId, string $rateId, ?int $insuranceCents = null): Response
    {
        $body = ['rate' => ['id' => $rateId]];
        if ($insuranceCents !== null) {
            $body['insurance'] = number_format($insuranceCents / 100, 2, '.', '');
        }
        return $this->request()->post("/shipments/{$shipmentId}/buy", $body)->throw();
    }

    public function rerateShipment(string $shipmentId): Response
    {
        return $this->request()->get("/shipments/{$shipmentId}/rerate")->throw();
    }

    public function refundShipment(string $shipmentId): Response
    {
        return $this->request()->post("/shipments/{$shipmentId}/refund")->throw();
    }

    public function getShipment(string $shipmentId): Response
    {
        return $this->request()->get("/shipments/{$shipmentId}")->throw();
    }

    public function createTracker(string $trackingCode, string $carrier): Response
    {
        return $this->request()->post('/trackers', [
            'tracker' => ['tracking_code' => $trackingCode, 'carrier' => $carrier],
        ])->throw();
    }

    public function createBatch(array $shipmentIds): Response
    {
        return $this->request()->post('/batches', [
            'batch' => ['shipments' => array_map(fn ($id) => ['id' => $id], $shipmentIds)],
        ])->throw();
    }

    public function buyBatch(string $batchId): Response
    {
        return $this->request()->post("/batches/{$batchId}/buy")->throw();
    }

    public function labelBatch(string $batchId, string $format = 'PDF'): Response
    {
        return $this->request()->post("/batches/{$batchId}/label", ['file_format' => $format])->throw();
    }

    public function createScanForm(array $shipmentIds): Response
    {
        return $this->request()->post('/scan_forms', [
            'scan_form' => ['shipments' => array_map(fn ($id) => ['id' => $id], $shipmentIds)],
        ])->throw();
    }

    public function createPickup(array $payload): Response
    {
        return $this->request()->post('/pickups', ['pickup' => $payload])->throw();
    }

    public function buyPickup(string $pickupId, string $carrier, string $service): Response
    {
        return $this->request()->post("/pickups/{$pickupId}/buy", [
            'carrier' => $carrier,
            'service' => $service,
        ])->throw();
    }

    public function cancelPickup(string $pickupId): Response
    {
        return $this->request()->post("/pickups/{$pickupId}/cancel")->throw();
    }

    public function createInsurance(array $payload): Response
    {
        return $this->request()->post('/insurances', ['insurance' => $payload])->throw();
    }

    public function refundInsurance(string $insuranceId): Response
    {
        return $this->request()->post("/insurances/{$insuranceId}/refund")->throw();
    }

    public function createClaim(array $payload): Response
    {
        return $this->request()->post('/claims', ['claim' => $payload])->throw();
    }

    public function cancelClaim(string $claimId): Response
    {
        return $this->request()->post("/claims/{$claimId}/cancel")->throw();
    }

    public function createReturnShipment(array $payload): Response
    {
        $payload['is_return'] = true;
        return $this->request()->post('/shipments', ['shipment' => $payload])->throw();
    }

    public function createAndVerifyAddress(array $payload, array $verify = ['delivery']): Response
    {
        $body = ['address' => $payload, 'verify' => $verify];
        return $this->request()->post('/addresses', $body)->throw();
    }

    public function verifyAddress(string $addressId): Response
    {
        return $this->request()->get("/addresses/{$addressId}/verify")->throw();
    }

    public function deleteTracker(string $trackerId): Response
    {
        return $this->request()->delete("/trackers/{$trackerId}")->throw();
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
