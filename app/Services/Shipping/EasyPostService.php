<?php

namespace App\Services\Shipping;

use App\Mixins\Integrations\EasyPost\EasyPostClient;
use Illuminate\Http\Client\Response;

/**
 * Application-level orchestration over EasyPostClient. Lives between the Actions
 * and the raw integration — handles payload shaping for shipment-related calls.
 *
 * Like the underlying client, methods return the raw `Response`. Actions decode
 * via `->json()` so JSON parsing stays at the call site.
 */
class EasyPostService
{
    public function __construct(private readonly EasyPostClient $client) {}

    public function createShipmentWithRates(array $from, array $to, array $parcel, array $options = []): Response
    {
        return $this->client->createShipment([
            'to_address' => $to,
            'from_address' => $from,
            'parcel' => $parcel,
            'options' => $options,
        ]);
    }

    public function buy(string $epShipmentId, string $rateId, ?int $insuranceCents = null): Response
    {
        return $this->client->buyShipment($epShipmentId, $rateId, $insuranceCents);
    }

    public function refund(string $epShipmentId): Response
    {
        return $this->client->refundShipment($epShipmentId);
    }

    public function rerate(string $epShipmentId): Response
    {
        return $this->client->rerateShipment($epShipmentId);
    }

    public function getShipment(string $epShipmentId): Response
    {
        return $this->client->getShipment($epShipmentId);
    }
}
