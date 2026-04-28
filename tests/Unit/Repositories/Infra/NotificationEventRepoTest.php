<?php

use App\Repositories\Infra\NotificationEventRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new NotificationEventRepo();
});

it('tableExists returns true when notification_events migration ran', function () {
    expect($this->repo->tableExists())->toBeTrue();
});

it('record persists a notification_events row', function () {
    $this->seed();
    $teamId = (int) DB::table('teams')->first()->id;

    $event = $this->repo->record([
        'team_id' => $teamId,
        'shipment_id' => null,
        'channel' => 'email',
        'template' => 'shipment.delivered',
        'recipient' => 'stan@x.test',
        'subject' => 'Shipment update: delivered',
    ]);

    expect($event->id)->toBeInt();
    expect((int) DB::table('notification_events')->where('id', $event->id)->count())->toBe(1);
});
