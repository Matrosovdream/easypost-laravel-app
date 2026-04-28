<?php

use App\Mixins\Integrations\EasyPost\EasyPostClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->client = new EasyPostClient(apiKey: 'EZTK_TEST', baseUrl: 'https://api.easypost.com/v2');
});

it('createShipment returns a Response (not a decoded array) and POSTs the wrapped payload', function () {
    Http::fake(['api.easypost.com/v2/shipments' => Http::response(['id' => 'shp_x'], 200)]);

    $resp = $this->client->createShipment(['to_address' => ['id' => 'adr_1']]);

    expect($resp)->toBeInstanceOf(Response::class);
    expect($resp->json('id'))->toBe('shp_x');

    Http::assertSent(fn ($req) =>
        $req->method() === 'POST'
        && str_ends_with($req->url(), '/shipments')
        && $req->data() === ['shipment' => ['to_address' => ['id' => 'adr_1']]]);
});

it('buyShipment formats insurance amount as decimal string', function () {
    Http::fake(['*/buy' => Http::response(['status' => 'purchased'])]);

    $this->client->buyShipment('shp_1', 'rate_1', insuranceCents: 1234);

    Http::assertSent(fn ($req) =>
        str_contains($req->url(), '/shipments/shp_1/buy')
        && $req->data() === ['rate' => ['id' => 'rate_1'], 'insurance' => '12.34']);
});

it('buyShipment omits insurance when null', function () {
    Http::fake(['*/buy' => Http::response(['status' => 'purchased'])]);

    $this->client->buyShipment('shp_1', 'rate_1');

    Http::assertSent(fn ($req) =>
        $req->data() === ['rate' => ['id' => 'rate_1']]);
});

it('createTracker wraps payload under tracker key', function () {
    Http::fake(['*/trackers' => Http::response(['id' => 'trk_x'])]);

    $this->client->createTracker('EZ123', 'USPS');

    Http::assertSent(fn ($req) =>
        $req->data() === ['tracker' => ['tracking_code' => 'EZ123', 'carrier' => 'USPS']]);
});

it('createBatch maps shipment ids to {id: $id} array entries', function () {
    Http::fake(['*/batches' => Http::response(['id' => 'batch_x'])]);

    $this->client->createBatch(['shp_1', 'shp_2']);

    Http::assertSent(fn ($req) =>
        $req->data() === ['batch' => ['shipments' => [['id' => 'shp_1'], ['id' => 'shp_2']]]]);
});

it('buyBatch hits the batch buy endpoint', function () {
    Http::fake(['*/batches/batch_1/buy' => Http::response(['state' => 'purchasing'])]);

    $resp = $this->client->buyBatch('batch_1');
    expect($resp->json('state'))->toBe('purchasing');
});

it('labelBatch sends the file_format payload', function () {
    Http::fake(['*/batches/batch_1/label' => Http::response(['label_url' => 's3://x'])]);

    $this->client->labelBatch('batch_1', 'ZPL');

    Http::assertSent(fn ($req) => $req->data() === ['file_format' => 'ZPL']);
});

it('createScanForm wraps shipment ids in scan_form.shipments', function () {
    Http::fake(['*/scan_forms' => Http::response(['id' => 'sf_x'])]);

    $this->client->createScanForm(['shp_1']);

    Http::assertSent(fn ($req) =>
        $req->data() === ['scan_form' => ['shipments' => [['id' => 'shp_1']]]]);
});

it('createPickup wraps payload under pickup key', function () {
    Http::fake(['*/pickups' => Http::response(['id' => 'pick_x'])]);

    $this->client->createPickup(['min_datetime' => '2026-01-01']);

    Http::assertSent(fn ($req) =>
        $req->data() === ['pickup' => ['min_datetime' => '2026-01-01']]);
});

it('buyPickup sends carrier+service body', function () {
    Http::fake(['*/pickups/pk_1/buy' => Http::response(['confirmation' => 'CNF'])]);

    $this->client->buyPickup('pk_1', 'USPS', 'Priority');

    Http::assertSent(fn ($req) =>
        $req->data() === ['carrier' => 'USPS', 'service' => 'Priority']);
});

it('cancelPickup posts to cancel endpoint', function () {
    Http::fake(['*/pickups/pk_1/cancel' => Http::response(['ok' => true])]);

    $resp = $this->client->cancelPickup('pk_1');

    expect($resp)->toBeInstanceOf(Response::class);
    Http::assertSent(fn ($req) =>
        $req->method() === 'POST'
        && str_ends_with($req->url(), '/pickups/pk_1/cancel'));
});

it('createInsurance wraps payload under insurance key', function () {
    Http::fake(['*/insurances' => Http::response(['id' => 'ins_x'])]);

    $this->client->createInsurance(['amount' => '10.00']);

    Http::assertSent(fn ($req) =>
        $req->data() === ['insurance' => ['amount' => '10.00']]);
});

it('createReturnShipment forces is_return=true on the payload', function () {
    Http::fake(['*/shipments' => Http::response(['id' => 'shp_ret'])]);

    $this->client->createReturnShipment(['to_address' => ['id' => 'adr']]);

    Http::assertSent(fn ($req) =>
        ($req->data()['shipment']['is_return'] ?? null) === true);
});

it('createAndVerifyAddress includes the verify array', function () {
    Http::fake(['*/addresses' => Http::response(['id' => 'adr_x'])]);

    $this->client->createAndVerifyAddress(['street1' => '1 Main', 'country' => 'US']);

    Http::assertSent(fn ($req) =>
        $req->data() === ['address' => ['street1' => '1 Main', 'country' => 'US'], 'verify' => ['delivery']]);
});

it('verifyAddress hits GET on the verify subresource', function () {
    Http::fake(['*/addresses/adr_1/verify' => Http::response(['verifications' => []])]);

    $this->client->verifyAddress('adr_1');

    Http::assertSent(fn ($req) =>
        $req->method() === 'GET' && str_ends_with($req->url(), '/addresses/adr_1/verify'));
});

it('deleteTracker hits DELETE', function () {
    Http::fake(['*/trackers/trk_1' => Http::response([], 200)]);

    $this->client->deleteTracker('trk_1');

    Http::assertSent(fn ($req) =>
        $req->method() === 'DELETE' && str_ends_with($req->url(), '/trackers/trk_1'));
});

it('throws on 4xx/5xx responses', function () {
    Http::fake(['*' => Http::response(['error' => 'bad request'], 422)]);

    expect(fn () => $this->client->createShipment([]))
        ->toThrow(\Illuminate\Http\Client\RequestException::class);
});

it('uses basic auth with the api key as the username', function () {
    Http::fake(['*' => Http::response([])]);

    $this->client->getShipment('shp_1');

    Http::assertSent(function ($req) {
        $auth = $req->header('Authorization');
        return ! empty($auth) && str_contains($auth[0], 'Basic ');
    });
});

it('raw() returns a PendingRequest for ad-hoc calls', function () {
    expect($this->client->raw())->toBeInstanceOf(\Illuminate\Http\Client\PendingRequest::class);
});
