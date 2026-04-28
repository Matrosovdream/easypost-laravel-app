<?php

use App\Helpers\Notifications\TrackingNotificationHelper;
use App\Jobs\SendTrackingNotificationJob;
use App\Models\Client;
use App\Models\Shipment;
use App\Repositories\Infra\NotificationEventRepo;
use App\Repositories\Shipping\ShipmentRepo;

beforeEach(function () {
    $this->shipments = mock(ShipmentRepo::class);
    $this->events = mock(NotificationEventRepo::class);
    $this->helper = new TrackingNotificationHelper();
});

it('short-circuits when shipment is missing', function () {
    $this->shipments->shouldReceive('findUnscoped')->with(1, ['client'])->andReturn(null);
    $this->events->shouldNotReceive('record');

    (new SendTrackingNotificationJob(1, 'delivered'))
        ->handle($this->shipments, $this->events, $this->helper);
});

it('short-circuits when status has no template', function () {
    $shipment = new Shipment();
    $shipment->id = 1;

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);
    $this->events->shouldNotReceive('record');

    (new SendTrackingNotificationJob(1, 'in_transit'))
        ->handle($this->shipments, $this->events, $this->helper);
});

it('short-circuits when no recipient', function () {
    $shipment = new Shipment();
    $shipment->id = 1;
    $shipment->setRelation('client', null);

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);
    $this->events->shouldNotReceive('record');

    (new SendTrackingNotificationJob(1, 'delivered'))
        ->handle($this->shipments, $this->events, $this->helper);
});

it('short-circuits when notification_events table does not exist', function () {
    $shipment = new Shipment(['team_id' => 3]);
    $shipment->id = 1;
    $shipment->setRelation('client', new Client(['contact_email' => 'x@x.test']));

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);
    $this->events->shouldReceive('tableExists')->andReturn(false);
    $this->events->shouldNotReceive('record');

    (new SendTrackingNotificationJob(1, 'delivered'))
        ->handle($this->shipments, $this->events, $this->helper);
});

it('records a notification_event when all preconditions are met', function () {
    $shipment = new Shipment(['team_id' => 3]);
    $shipment->id = 11;
    $shipment->setRelation('client', new Client(['contact_email' => 'stan@x.test']));

    $this->shipments->shouldReceive('findUnscoped')->andReturn($shipment);
    $this->events->shouldReceive('tableExists')->andReturn(true);
    $this->events->shouldReceive('record')
        ->withArgs(fn ($row) =>
            $row['team_id'] === 3
            && $row['shipment_id'] === 11
            && $row['template'] === 'shipment.delivered'
            && $row['recipient'] === 'stan@x.test'
            && $row['subject'] === 'Shipment update: delivered')
        ->once();

    (new SendTrackingNotificationJob(11, 'delivered'))
        ->handle($this->shipments, $this->events, $this->helper);
});
