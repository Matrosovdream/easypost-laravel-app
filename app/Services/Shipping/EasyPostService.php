<?php

namespace App\Services\Shipping;

use App\Mixins\Integrations\EasyPost\EasyPostClient;

/**
 * Application-level orchestration over EasyPostClient. Lives between the Actions
 * and the raw integration. Keeps payload shaping + response normalization in one place.
 */
class EasyPostService
{
    public function __construct(private readonly EasyPostClient $client) {}

    public function createShipmentWithRates(array $from, array $to, array $parcel, array $options = []): array
    {
        return $this->client->createShipment([
            'to_address' => $to,
            'from_address' => $from,
            'parcel' => $parcel,
            'options' => $options,
        ]);
    }

    public function buy(string $epShipmentId, string $rateId, ?int $insuranceCents = null): array
    {
        return $this->client->buyShipment($epShipmentId, $rateId, $insuranceCents);
    }

    public function refund(string $epShipmentId): array
    {
        return $this->client->refundShipment($epShipmentId);
    }

    public function rerate(string $epShipmentId): array
    {
        return $this->client->rerateShipment($epShipmentId);
    }

    public function getShipment(string $epShipmentId): array
    {
        return $this->client->getShipment($epShipmentId);
    }
}
