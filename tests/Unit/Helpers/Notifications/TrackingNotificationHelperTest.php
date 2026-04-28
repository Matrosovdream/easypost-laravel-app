<?php

use App\Helpers\Notifications\TrackingNotificationHelper;
use App\Models\Client;
use App\Models\Shipment;

beforeEach(function () {
    $this->helper = new TrackingNotificationHelper();
});

it('maps delivered → shipment.delivered template', function () {
    expect($this->helper->templateForStatus('delivered'))->toBe('shipment.delivered');
});

it('maps out_for_delivery → shipment.out_for_delivery template', function () {
    expect($this->helper->templateForStatus('out_for_delivery'))->toBe('shipment.out_for_delivery');
});

it('maps failure and return_to_sender → shipment.exception template', function () {
    expect($this->helper->templateForStatus('failure'))->toBe('shipment.exception');
    expect($this->helper->templateForStatus('return_to_sender'))->toBe('shipment.exception');
});

it('returns null for statuses that do not warrant a notification', function () {
    expect($this->helper->templateForStatus('pre_transit'))->toBeNull();
    expect($this->helper->templateForStatus('in_transit'))->toBeNull();
    expect($this->helper->templateForStatus('unknown'))->toBeNull();
});

it('recipientFor returns the client contact_email when present', function () {
    $shipment = new Shipment();
    $client = new Client(['contact_email' => 'stan@acme.test']);
    $shipment->setRelation('client', $client);

    expect($this->helper->recipientFor($shipment))->toBe('stan@acme.test');
});

it('recipientFor returns null when no client', function () {
    $shipment = new Shipment();
    $shipment->setRelation('client', null);

    expect($this->helper->recipientFor($shipment))->toBeNull();
});

it('buildEventRow shapes the persisted row', function () {
    $shipment = new Shipment(['team_id' => 3]);
    $shipment->id = 11;

    $row = $this->helper->buildEventRow($shipment, 'shipment.delivered', 'delivered', 'stan@x.test');

    expect($row)->toBe([
        'team_id' => 3,
        'shipment_id' => 11,
        'channel' => 'email',
        'template' => 'shipment.delivered',
        'recipient' => 'stan@x.test',
        'subject' => 'Shipment update: delivered',
    ]);
});
